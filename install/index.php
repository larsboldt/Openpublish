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
define ('DOCUMENT_ROOT', rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/'));

//ini_set('mbstring.language', 'neutral');
//ini_set('mbstring.encoding_translation', 'On');
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('mbstring.http_input', 'auto');
ini_set('mbstring.http_output', 'UTF-8');
ini_set('mbstring.detect_order', 'auto');
ini_set('mbstring.substitute_character', 'none');
ini_set('default_charset', 'UTF-8');

require_once('../core/class.opSystem.php');
require_once('../core/class.opSQLImport.php');
require_once('class.opServerCheck.php');
require_once('class.opCreateFile.php');

# Check form submission at step 2
if (isset($_POST['sitename'])) {
    $_SESSION['opInstall_data'] = $_POST;
    header('Location: /install/index.php?step=3');
    exit();
}
if (isset($_SESSION['opInstall_data'])) {
    $installData = $_SESSION['opInstall_data'];
} else {
    $installData = false;
}

$opServerCheck = new opServerCheck();
$step = (isset($_GET['step'])) ? $_GET['step'] : 1;
$pass = $opServerCheck->passAll();
switch ($step) {
    case 'phpinfo':
        $step = 'phpinfo.php';
        break;
    case '2':
        $step = ($pass) ? 'step2.php' : 'step1.php';
        break;
    case '3':
        $step = ($pass) ? 'step3.php' : 'step1.php';
        break;
    default:
        $step = 'step1.php';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <title>Installation | Openpublish CMS</title>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <link rel="stylesheet" type="text/css" href="/themes/opAdmin/css/reset.css" />
        <link rel="stylesheet" type="text/css" href="/install/css/install.css" />
        <link rel="stylesheet" type="text/css" href="/themes/opAdmin/css/default.css" />
        <link rel="stylesheet" type="text/css" href="/themes/opAdmin/css/navigation.css" />
        <link rel="stylesheet" type="text/css" href="/themes/opAdmin/css/form.css" />
        <link rel="stylesheet" type="text/css" href="/themes/opAdmin/css/typography.css" />
        <link rel="stylesheet" type="text/css" href="/themes/opAdmin/css/messages.css" />
    </head>

    <body>

        <div id="wrapper">

            <div id="adminbar">

                <ul id="topbar-left">
                    <li><a href="#"><img src="/themes/opAdmin/images/adminbar_logo.png" width="117" height="40" border="0" alt="Openpublish" /></a></li>
                    <li style="margin-left:15px;"><?php echo 'v'.opSystem::getVersion() ?></li>
                </ul>

            </div><!--END adminbar-->

            <?php
            echo ($pass) ? '' : '<div class="error"><span class="error-heading">Install cannot continue</span> Openpublish can not be installed on this server until it meet the requirements.</div>';
            require_once($step);
            ?>

        </div><!--END wrapper-->

    </body>
</html>