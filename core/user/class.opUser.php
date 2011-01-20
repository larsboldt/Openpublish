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
class opUser {
    private $id         = false;
    private $email      = false;
    private $firstName  = false;
    private $lastName   = false;
    private $superadmin = false;
    private $db         = false;
    private $locale     = false;

    public function __construct(PDO $pdo, $username, $uid) {
        $this->db = $pdo;
        $this->setUserData($username, $uid);
    }

    /**
     * Returns id of user, false if user isn't found
     * @return bool|int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Returns current locale of user
     * @return string
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * Returns email of user, false if user isn't found
     * @return bool|string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Returns firstname and lastname of user, false if user isn't found
     * @return bool|string
     */
    public function getFullName() {
        $firstName = $this->getFirstName();
        $lastName = $this->getLastName();
        if (! $firstName || ! $lastName) {
            return false;
        } else {
            return $firstName.' '.$lastName;
        }
    }

    /**
     * Returns firstname of user, false if user isn't found
     * @return bool|string
     */
    public function getFirstName() {
        return ucfirst(mb_strtolower($this->firstName));
    }

    /**
     * Returns lastname of user, false if user isn't found
     * @return bool|string
     */
    public function getLastName() {
        return ucfirst(mb_strtolower($this->lastName));
    }

    /**
     * Returns true if user is superadmin, false if not
     * @return bool
     */
    public function isSuperAdmin() {
        return $this->superadmin;
    }

    /**
     * Sets class variables with data from database
     * @return bool
     */
    private function setUserData($username, $uid) {
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_admin_users WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $uid));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_admin_users WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $uid));
        } else {
            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_admin_users WHERE username = :usr');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('usr' => $username));
            if ($rVal->fetchColumn() > 0) {
                $rVal = $this->db->prepare('SELECT * FROM op_admin_users WHERE username = :usr');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('usr' => $username));
            } else {
                return false;
            }
        }
        $rVal = $rVal->fetch();
        $this->id = $rVal['id'];
        $this->firstName = $rVal['firstname'];
        $this->lastName = $rVal['lastname'];
        $this->email = $rVal['username'];
        $this->superadmin = ($rVal['superadmin'] == 1) ? true : false;
        $this->locale = $rVal['locale'];
    }
}
?>
