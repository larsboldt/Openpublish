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
class opAdminAuthenticationObserver implements SplObserver {
    protected $db, $secretKey;
    /**
     * Log failed login attempts
     * @param SplSubject $subject 
     */
    public function update(SplSubject $subject) {
        $this->db = $subject->db;
        $this->secretKey = $subject->secretKey;
        $this->registerLoginAttempt($subject->getLoginAttemptInformation());
    }

    protected function registerLoginAttempt(array $loginAttemptInformation) {
        $rVal = $this->db->prepare('INSERT INTO op_login_defense_log
                                    (username, password, remote_addr, remote_port, referer, user_agent, request_uri, stamp)
                                    VALUES
                                    (:usr, :pwd, :raddr, :rport, :ref, :uagent, :ruri, NOW())');
        $isAuth = ($loginAttemptInformation['is_authenticated']) ? 1 : 0;
        if (! $isAuth) {
            $rVal->execute(array('usr' => $this->encrypt($loginAttemptInformation['username']),
                                 'pwd' => $this->encrypt($loginAttemptInformation['password']),
                                 'raddr' => $loginAttemptInformation['remote_addr'],
                                 'rport' => $loginAttemptInformation['remote_port'],
                                 'ref' => $loginAttemptInformation['referer'],
                                 'uagent' => $loginAttemptInformation['user_agent'],
                                 'ruri' => $loginAttemptInformation['request_uri']));
        }
    }

    protected function encrypt($str) {
        // encrypt using RIJNDAEL_256
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        return mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->secretKey, $str, MCRYPT_MODE_ECB, $iv);
    }
}
?>
