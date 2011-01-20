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

# Is install data set?
if (! $installData) {
    header('Location: /?step=2');
    exit();
}

# Test & create database connection
$dbStatus = false;
$dbInsert = true;
$db = false;
$dbMsg = '';
try {
    $db = new PDO($installData['database'].':host='.$installData['dbhost'].';dbname='.$installData['dbname'], $installData['dbuser'], $installData['dbpass']);
    $db->exec('SET NAMES utf8');
    //$db->exec('SET CHARACTER_SET utf8');
    $dbStatus = true;

    # Create sqlImport
    $sqlImport = new opSQLImport($db);
    if ($sqlImport->import(DOCUMENT_ROOT.'/install/sql/opCore.install.sql')) {
        # Install plugins
        $corePluginSQL = array('opCreate', 'opDocuments', 'opFileManager', 'opLayout', 'opMenu', 'opPlugin', 'opSiteConfig', 'opThemes', 'opUsers', 'opTranslation', 'opGallery', 'opSocialSharer');
        foreach ($corePluginSQL as $sql) {
            if (file_exists(DOCUMENT_ROOT.'/core/plugins/'.$sql.'/sql/'.$sql.'.install.sql')) {
                if (! $sqlImport->import(DOCUMENT_ROOT.'/core/plugins/'.$sql.'/sql/'.$sql.'.install.sql')) {
                    $dbInsert = false;
                    break;
                }
            }
            if (file_exists(DOCUMENT_ROOT.'/core/plugins/'.$sql.'/sql/'.$sql.'.data.sql')) {
                if (! $sqlImport->import(DOCUMENT_ROOT.'/core/plugins/'.$sql.'/sql/'.$sql.'.data.sql')) {
                    $dbInsert = false;
                    break;
                }
            }
        }
    } else {
        $dbInsert = false;
    }
    if (! $sqlImport->import(DOCUMENT_ROOT.'/install/sql/opCore.demoData.sql')) {
        $dbInsert = false;
    }
} catch (PDOException $e) {
    $dbMsg = $e->getMessage();
    $dbStatus = false;
}

# Write htaccess
$htaccess_content = "Options -Indexes\n\r
<Files \"opConfig.php\">\n
    order allow,deny\n
    deny from all\n
</Files>\n\r
ServerSignature Off\n\r
RewriteEngine on\n\r
RewriteCond %{QUERY_STRING} http\: [OR]\n
RewriteCond %{QUERY_STRING} \[ [OR]\n
RewriteCond %{QUERY_STRING} \] [OR]\n
RewriteCond %{QUERY_STRING} (\<|%3C).*script.*(\>|%3E) [NC,OR]\n
RewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]\n
RewriteCond %{QUERY_STRING} _REQUEST(=|\[|\%[0-9A-Z]{0,2})\n
RewriteRule ^.*$ - [F,L]\n\r
RewriteCond %{REQUEST_FILENAME} !-f\n
RewriteCond %{REQUEST_FILENAME} !-d\n
RewriteRule ^(.*)$ index.php?route=$1 [L,QSA]";
$htaccess = new opCreateFile(DOCUMENT_ROOT, '.htaccess', $htaccess_content);
if (! $htaccess->write()) {
    $manual_htaccess = 'You must manually create a .htaccess file with the following contents:<br /><br />
                        Options -Indexes<br /><br />
                        RewriteEngine on<br /><br />
                        RewriteCond %{REQUEST_FILENAME} !-f<br />
                        RewriteCond %{REQUEST_FILENAME} !-d<br />
                        RewriteRule ^(.*)$ index.php?route=$1 [L,QSA]";';
    $htaccessStatus = false;
} else {
    $htaccessStatus = true;
}

# Write config
$secretKey = md5(rand(1,10).microtime().rand(11,100).rand(1000, 2000));
$config_content = '<?php
//error_reporting(E_ALL | E_STRICT);
error_reporting(0);
date_default_timezone_set(\''.$installData['timezone'].'\');

$opConfig = array();

# Database
$opConfig[\'dbDSN\']        = \''.$installData['database'].':host='.$installData['dbhost'].';dbname='.$installData['dbname'].'\';
$opConfig[\'dbUSER\']       = \''.$installData['dbuser'].'\';
$opConfig[\'dbPASSWORD\']   = \''.$installData['dbpass'].'\';

# secretKey
$opConfig[\'secretKey\']  = \''.$secretKey.'\';
?>';
$config = new opCreateFile(DOCUMENT_ROOT, 'opConfig.php', $config_content);
if (! $config->write()) {
    $manual_config = 'You must manually create a opConfig.php file with the following contents:<br /><br />
                      <?php<br />
                      error_reporting(E_ALL | E_STRICT);<br />
                      date_default_timezone_set(\'Europe/Oslo\');<br />
                      <br />
                      $opConfig = array();<br />
                      <br />
                      # Database<br />
                      $opConfig[\'dbDSN\'] 	= \''.$installData['database'].':host='.$installData['dbhost'].';dbname='.$installData['dbname'].'\';<br />
                      $opConfig[\'dbUSER\']	= \''.$installData['dbuser'].'\';<br />
                      $opConfig[\'dbPASSWORD\'] = \''.$installData['dbpass'].'\';<br />
                      <br />
                      # secretKey<br />
                      $opConfig[\'secretKey\']  = \''.$secretKey.'\';<br />
                      ?>';
    $configStatus = false;
} else {
    $configStatus = true;
}
if ($db) {
    # Write DB
    $password = hash_hmac('sha256', $installData['adminpwd'], $secretKey);
    $rVal = $db->prepare('INSERT INTO op_admin_users (username, password, firstname, lastname, superadmin) VALUES (:usr, :pwd, :fn, :ln, 1)');
    $rVal->execute(array('usr' => $installData['adminemail'], 'pwd' => $password, 'fn' => $installData['adminfn'], 'ln' => $installData['adminln']));

    # site config
    $rVal = $db->prepare('INSERT INTO op_site_config
                         (site_name, site_url, date_format, time_format, file_permission, dir_permission, title_separator,
                          title_breadcrumb, title_breadcrumb_separator, caching, cache_ttl, local_caching, local_cache_ttl, site_status, force_url_lowercase,
                          disable_captcha, compress_css, compress_js, login_protection, blacklist, whitelist, hammer_protection,
                          hammer_intervals)
                         VALUES
                         (:sn, :su, :df, :tf, :fp, :dp, "|", 1, "&raquo;", 1, 3600, 1, 60, 0, 1, 0, 1, 1, 0, "", "", 1, "3:1:10")');
    $rVal->execute(array('sn' => $installData['sitename'], 'su' => $installData['domain'], 'df' => 'd-m-Y', 'tf' => 'H:i:s', 'fp' => '0644', 'dp' => '0755'));
}
# Create folders
$folderArr = array('store', 'cache', 'temp');
foreach ($folderArr as $folder) {
    if (! is_dir(DOCUMENT_ROOT.'/files/'.$folder)) {
        mkdir(DOCUMENT_ROOT.'/files/'.$folder);
        chmod(DOCUMENT_ROOT.'/files/'.$folder, 0755);
    }
}
?>
<div id="container">

    <h3>Installation wizard | 3 of 3
        <span class="heading-icon"><img src="/themes/opAdmin/images/icons/box.png" width="16" height="16" alt="" title="" class="table-icon" /></span>
    </h3>

    <div id="content-plugin">

        <form id="adminForm" action="/install?step=2" method="post">
            <h5 class="installStatus">Installing...</h5>
            <ul id="installList">
            	<li>Connecting to database... <?php echo ($dbStatus) ? '<span class="installOk">OK</span>' : '<span class="installAbort">Failed - '.$dbMsg.'</span>' ; ?></li>
            	<li>Setting up database tables... <?php echo ($dbInsert) ? '<span class="installOk">OK</span>' : '<span class="installAbort">Failed</span>' ; ?></li>
            	<li>Writing .htaccess... <?php echo ($htaccessStatus) ? '<span class="installOk">OK</span>' : '<span class="installAbort">'.$manual_htaccess.'</span>' ; ?></li>
            	<li>Writing config file... <?php echo ($configStatus) ? '<span class="installOk">OK</span>' : '<span class="installAbort">'.$manual_config.'</span>' ; ?></li>
            </ul>
        </form>
        <div>If you got all "OK" please delete the "install" folder before pressing finish.</div>
        <div id="page-btn-back">
            <a class="form_btn" href="/install/index.php?step=2"><span><img src="/themes/opAdmin/images/icons/arrow-180-medium.png" width="16" height="16" border="0" alt="" class="table-icon" /> Back</span></a>
        </div>

        <div id="page-btn-next">
            <a class="form_btn" href="/"><span><img src="/themes/opAdmin/images/icons/tick.png" width="16" height="16" border="0" alt="" class="table-icon" /> Finish</span></a>
        </div>

    </div><!--END content-plugin-->

</div><!--END container-->