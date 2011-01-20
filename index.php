<?php
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
session_start();

define('_OP', true);
define('DIRSEP', DIRECTORY_SEPARATOR);
define('DOCUMENT_ROOT', str_replace('\\', '/', realpath(dirname(__FILE__) ) . DIRSEP));

# From php.net manual on mbstring
//ini_set('mbstring.language', 'neutral');
//ini_set('mbstring.encoding_translation', 'On');
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('mbstring.http_input', 'auto');
ini_set('mbstring.http_output', 'UTF-8');
ini_set('mbstring.detect_order', 'auto');
ini_set('mbstring.substitute_character', 'none');
ini_set('default_charset', 'UTF-8');

# Redirect to install folder if it exists
if (is_dir(DOCUMENT_ROOT.'install')) {
    header('Location: /install');
    exit();
}

# Stripslashes | From php.net manual
function stripslashes_deep(&$value) {
    $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    return $value;
}
if ((function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) ||
    (ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase')) != "off"))) {
    stripslashes_deep($_GET);
    stripslashes_deep($_POST);
    stripslashes_deep($_COOKIE);
}
# Stripslashes END

require('core/class.opSystem.php');
require('core/class.opFileLocator.php');
require('opConfig.php');

try {
    $db = new PDO($opConfig['dbDSN'], $opConfig['dbUSER'], $opConfig['dbPASSWORD']);
    $db->exec('SET NAMES utf8');
    //$db->exec('SET CHARACTER_SET utf8');
    opSystem::setDatabaseInstance($db);
} catch (PDOException $e) {
    die('Unable to connect to database');
}
opSystem::setFileLocatorInstance(new opFileLocator(DOCUMENT_ROOT));

function autoload($class_name) {
    $fileLocator = opSystem::getFileLocatorInstance();
    if (! $fileLocator->findAndLoad($class_name)) {
        die($class_name.' not found.');
    }
}
spl_autoload_register('autoload');

# Set system configuration
$rVal = $db->query("SELECT * FROM op_site_config");
$rVal->setFetchMode(PDO::FETCH_OBJ);
$rVal = $rVal->fetch();
opSystem::setSystemConfiguration($rVal);

# Set secret key
opSystem::setSecretKey($opConfig['secretKey']);
# Null $opConfig
$opConfig = null;

# Load virtual controller
opSystem::setVirtualControllerInstance(new opVirtualController($db));
# Load redirect controller
opSystem::setRedirectControllerInstance(new opRedirectController($db));

# Load router
$router = new opRouter();
opSystem::setRouterInstance($router);
$router->setPath(DOCUMENT_ROOT.'core');
$router->delegate();
?>