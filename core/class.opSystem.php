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
class opSystem {
    const ERROR_MSG     = 0;
    const SUCCESS_MSG   = 1;
    const INFORM_MSG    = 2;
    const MAJOR         = 0.9;
    const MINOR         = 9;
    const REVISION      = 0;

    # SESSSION VAR
    static private $SESSION_MSG = 'opSystem_messages';

    # DATABASE
    static private $DB          = null;

    # FILE LOCATOR
    static private $FL          = null;

    # SYSTEM CONFIGURATION
    static private $SC          = null;
    
    # SECRET KEY
    static private $SK          = null;

    # VIRTUAL CONTROLLER
    static private $VC          = null;

    # REDIRECT CONTROLLER
    static private $RC          = null;

    # ROUTER
    static private $RO          = null;

    private function __construct() {}

    static public function getVersion() {
        return self::MAJOR.'.'.self::MINOR.'.'.self::REVISION;
    }

    static public function Msg($msg, $type) {
        switch ($type) {
            case 0:
                $msg = '<div id="system_message_error" class="system_message error"><span class="error-heading">'.opTranslation::getTranslation('_error').'</span> '.$msg.'</div>';
                break;
            case 1:
                $msg = '<div id="system_message_success_wrapper"><div id="system_message_success" class="system_message success"><span class="success-heading"><img src="/themes/opAdmin/images/icons/tick.png" width="16" height="16" border="0" /></span> <p>'.$msg.'</p></div></div>';
                break;
            case 2:
                $msg = '<div id="system_message_inform" class="system_message inform"><span class="inform-heading">'.opTranslation::getTranslation('_information').'</span> '.$msg.'</div>';
                break;
            default:
                throw new Exception('Invalid opSystem::Msg type');
        }
        $sessionData = (isset($_SESSION[self::$SESSION_MSG])) ? $_SESSION[self::$SESSION_MSG] : array();
        $sessionData[] = $msg;
        $_SESSION[self::$SESSION_MSG] = $sessionData;
    }

    static public function getMessages() {
        $sessionData = (isset($_SESSION[self::$SESSION_MSG])) ? $_SESSION[self::$SESSION_MSG] : array();
        unset($_SESSION[self::$SESSION_MSG]);
        return $sessionData;
    }

    static public function redirect($url) {
        header('Location: '.opURL::getUrl('/admin'.$url));
        exit();
    }

    static public function setSystemConfiguration(stdClass $obj) {
        if (is_null(self::$SC)) {
            self::$SC = $obj;
        }
    }

    static public function getSystemConfiguration() {
        return self::$SC;
    }

    static public function setDatabaseInstance(PDO $db) {
        if (is_null(self::$DB)) {
            self::$DB = $db;
        }
    }

    static public function getDatabaseInstance() {
        return self::$DB;
    }

    static public function setFileLocatorInstance(opFileLocator $fl) {
        if (is_null(self::$FL)) {
            self::$FL = $fl;
        }
    }

    static public function getFileLocatorInstance() {
        return self::$FL;
    }

    static public function setSecretKey($str) {
        if (is_null(self::$SK)) {
            self::$SK = $str;
        }
    }

    static public function getSecretKey() {
        return self::$SK;
    }

    static public function setVirtualControllerInstance(opVirtualController $vc) {
        if (is_null(self::$VC)) {
            self::$VC = $vc;
        }
    }

    static public function getVirtualControllerInstance() {
        return self::$VC;
    }

    static public function setRedirectControllerInstance(opRedirectController $rc) {
        if (is_null(self::$RC)) {
            self::$RC = $rc;
        }
    }

    static public function getRedirectControllerInstance() {
        return self::$RC;
    }

    static public function setRouterInstance(opRouter $ro) {
        if (is_null(self::$RO)) {
            self::$RO = $ro;
        }
    }

    static public function getRouterInstance() {
        return self::$RO;
    }

    public static function _set($key, $value, $class) {
        $key   = substr($key, 0, 254);
        $class = substr($class, 0, 254);
        $db    = self::getDatabaseInstance();
        if (self::_get($key, $class) === false) {
            $rVal = $db->prepare('INSERT INTO op_system_variables (k, v, c) VALUES (:k, :v, :c)');
        } else {
            $rVal = $db->prepare('UPDATE op_system_variables SET v = :v WHERE k = :k AND c = :c');
        }
        $rVal->execute(array('k' => $key, 'v' => $value, 'c' => $class));
    }

    public static function _get($key, $class) {
        $key   = substr($key, 0, 254);
        $class = substr($class, 0, 254);
        $db    = opSystem::getDatabaseInstance();
        $rVal  = $db->prepare('SELECT COUNT(*) FROM op_system_variables WHERE k = :k AND c = :c');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('k' => $key, 'c' => $class));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $db->prepare('SELECT * FROM op_system_variables WHERE k = :k AND c = :c');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('k' => $key, 'c' => $class));
            $variable = $rVal->fetch();
            return $variable['v'];
        }
        return false;
    }

    public static function _unset($key, $class) {
        $key   = substr($key, 0, 254);
        $class = substr($class, 0, 254);
        $db    = opSystem::getDatabaseInstance();
        $rVal  = $db->prepare('SELECT COUNT(*) FROM op_system_variables WHERE k = :k AND c = :c');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('k' => $key, 'c' => $class));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $db->prepare('DELETE FROM op_system_variables WHERE k = :k AND c = :c');
            $rVal->execute(array('k' => $key, 'c' => $class));
            return true;
        }
        return false;
    }
}
?>