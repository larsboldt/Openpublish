<?php
defined('_OP') or die('Access denied');
/**
 *  Copyright (C) 2009 Lars Boldt
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class opAuthentication implements SplSubject {
    protected $loginAttemptInformation, $observers, $protectionArea;
    public $db, $secretKey;

    /**
     * @param PDO $pdo
     * @param string $secretKey
     */
    public function __construct(PDO $pdo, $secretKey = false) {
        $this->secretKey = $secretKey;
        $this->db = $pdo;
        $this->protectionArea = get_class($this);
        $this->observers = array();
        $this->loginAttemptInformation = array();
    }

    /**
     * @return bool Returns true if username/password authenticates, false if not.
     */
    public function login($username, $password) {
        # Regenerate session id
        session_regenerate_id(true);

        # Set login session
        $_SESSION[$this->protectionArea] = array('username' => $this->sanitize($username),
                                                 'password' => $this->hashStr($this->sanitize($password)));

        # Authenticate
        $isAuthenticated = $this->authenticate();

        # Log login attemp and notify observers
        $this->loginAttemptInformation['is_authenticated'] = $isAuthenticated;
        $this->loginAttemptInformation['username']        = $username;
        $this->loginAttemptInformation['password']        = $password;
        $this->loginAttemptInformation['remote_addr']     = $_SERVER['REMOTE_ADDR'];
        $this->loginAttemptInformation['remote_port']     = $_SERVER['REMOTE_PORT'];
        $this->loginAttemptInformation['referer']         = $_SERVER['HTTP_REFERER'];
        $this->loginAttemptInformation['user_agent']      = $_SERVER['HTTP_USER_AGENT'];
        $this->loginAttemptInformation['request_uri']     = $_SERVER['REQUEST_URI'];
        $this->notify();

        # Return authenticate status
        return $isAuthenticated;
    }

    public function getLoginAttemptInformation() {
        return $this->loginAttemptInformation;
    }

    /**
     * Destroys the $_SESSION with username/password
     * @return bool Returns true if $_SESSION is set and unset, false if $_SESSION is not set.
     */
    public function logout() {
        if (isset($_SESSION[$this->protectionArea])) {
            unset($_SESSION[$this->protectionArea]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Reset user password, auto-generate a new one
     * @param string $keycode
     * @return bool|array Updates database and returns new password and user id
     */
    public function resetPassword($keycode) {
        if (strpos($keycode, ':') > 0) {
            list($uid, $key, $hash) = explode(':', $keycode);
            if ($this->hashStr($uid.$key) == $hash) {
                if ($this->confirmPasswordResetKey($key)) {
                    $password = $this->generatePassword();
                    $rVal = $this->db->prepare('UPDATE op_admin_users SET password = :pwd WHERE id = :id');
                    $rVal->execute(array('id' => $uid, 'pwd' => $this->hashStr($password)));

                    $this->deletePasswordResetRegistration($key);

                    return array($password, $uid);
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Set new password for user
     * @param opUser $user, string $password
     */
    public function setPassword(opUser $user, $password) {
        if (is_numeric($user->getId()) && isset($_SESSION[$this->protectionArea])) {
            // update db
            $rVal = $this->db->prepare('UPDATE op_admin_users SET password = :pwd WHERE id = :id');
            $pwd = $this->hashStr($this->sanitize($password));
            $rVal->execute(array('id' => $user->getId(), 'pwd' => $pwd));
            // update session so user doesn't get logged out
            $sessionData = $_SESSION[$this->protectionArea];
            $sessionData['password'] = $pwd;
            $_SESSION[$this->protectionArea] = $sessionData;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets answer to secret question in database
     * @param opUser $user, string $answer
     * @return bool Returns true if answer was updated, false if not.
     */
    public function setAnswerToSecretQuestion(opUser $user, $answer) {
        if (is_numeric($user->getId())) {
            $answer = $this->hashStr($this->sanitize($answer));
            $rVal = $this->db->prepare('UPDATE op_admin_users SET secretQuestion = :answer WHERE id = :id');
            $rVal->execute(array('answer' => $answer, 'id' => $user->getId()));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validate answer with answer in database
     * @param opUser $user, string $answer
     * @return bool Returns true if $answer matches hashed answer in database, false if not
     */
    public function checkSecretQuestion(opUser $user, $answer) {
        if (is_numeric($user->getId())) {
            $answer = $this->hashStr($this->sanitize($answer));
            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_admin_users WHERE id = :id AND secretQuestion = :answer');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $user->getId(), 'answer' => $answer));
            if ($rVal->fetchColumn() > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Compares username/password in $_SESSION against username/password in database.
     * @return bool Returns true if they match, false if $_SESSION is not set or there's a mismatch.
     */
    public function authenticate() {
        if (isset($_SESSION[$this->protectionArea])) {
            $sessionData = $_SESSION[$this->protectionArea];

            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_admin_users WHERE username = :usr AND password = :pwd');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('usr' => $sessionData['username'], 'pwd' => $sessionData['password']));
            if ($rVal->fetchColumn() > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function registerPasswordReset(opUser $user) {
        if (is_numeric($user->getId())) {
            $key = substr($this->hashStr($user->getId().microtime().rand(1,100000000)), 0, 12);
            $rVal = $this->db->prepare('INSERT INTO op_password_reset (keycode, stamp) VALUES (:keycode, NOW())');
            $rVal->execute(array('keycode' => $key));
            return $user->getId().":".$key.":".$this->hashStr($user->getId().$key);
        } else {
            return false;
        }
    }

    public function attach(SplObserver $observer) {
        $this->observers[] = $observer;
    }

    public function detach(SplObserver $observer) {
        if ($idx = array_search($observer, $this->observers, true)) {
            unset($this->observers[$idx]);
        }
    }

    public function notify() {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    /**
     * Returns an 8 character password
     * @return string
     */
    protected function generatePassword() {
        $pwdChar = 'aeyuAEYU@$%#BDGHJLMNPQRSTVWXZbdghjmnpqrstvz23456789';
        $pwd = '';
        for ($i = 0; $i < 8; $i++) {
            $pwd .= $pwdChar[rand() % strlen($pwdChar)];
        }
        return $pwd;
    }

    /**
     * Hashes string
     * @param string String to be hashed
     * @return string Hashed string
     */
    protected function hashStr($str) {
        return hash_hmac('sha256', $str, $this->secretKey);
    }

    /**
     * Sanitize input
     * @return string
     */
    protected function sanitize($data) {
        return strip_tags(trim($data));
    }

    protected function confirmPasswordResetKey($key) {
        $this->deletePasswordResetRegistrationTimeouts();
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_password_reset WHERE keycode = :keycode');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('keycode' => $key));
        if ($rVal->fetchColumn() > 0) {
            return true;
        } else {
            return false;
        }
    }

    protected function deletePasswordResetRegistration($key) {
        $rVal = $this->db->prepare('DELETE FROM op_password_reset WHERE keycode = :keycode');
        $rVal->execute(array('keycode' => $key));
    }

    protected function deletePasswordResetRegistrationTimeouts() {
        $this->db->query('DELETE FROM op_password_reset WHERE DATE_SUB(NOW(), INTERVAL 1 DAY) > stamp');
    }
}
?>