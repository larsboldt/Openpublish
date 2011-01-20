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
class controller_admin extends opControllerBase implements SplSubject {
    protected $observers, $nonURLCallableMethods, $router, $systemConfiguration, $activeUser;

    protected function initialize() {
        $this->observers            = array();
        $this->router               = opSystem::getRouterInstance();
        $this->systemConfiguration  = opSystem::getSystemConfiguration();
        $this->nonURLCallableMethods = array('getIcon', 'getContentList', 'getOutput',
                                             'install', 'uninstall', 'getContentNameById',
                                             'getContentEditPath', 'getConfig', 'setRegistry',
                                             'controller', 'updateLastModified');
        if (isset($_SESSION['opAdmin'])) {
            $this->activeUser = new opUser($this->db, $_SESSION['opAdmin']['username'], null);
        } else {
            $this->activeUser = false;
        }
    }

    public function index() {
        if ($this->authenticate()) {
            $route = $this->router->getArgs();

            # Construct page
            $compressJS = ($this->systemConfiguration->caching) ? $this->systemConfiguration->compress_js : false;
            $compressCSS = ($this->systemConfiguration->caching) ? $this->systemConfiguration->compress_css : false;
            
            $theme = new opTheme('themes/opAdmin/', false, false, $compressJS, $compressCSS, $this->systemConfiguration->cache_ttl);

            # No-cache
            header('Cache-Control: no-cache, must revalidate');
            header('Pragma: no-cache');

            $templateManager = new opTemplateManager(file_get_contents(DOCUMENT_ROOT.$theme->getThemePath().'templates/backend.tpl'));

            # Title
            $theme->setTitle($this->systemConfiguration->site_name.' | Openpublish CMS Administration');

            # Version
            $templateCollection = new opTemplateCollection('version');
            $template = new opHtmlTemplate('v'.opSystem::getVersion());
            $templateCollection->addTemplate($template);
            $templateManager->addCollection($templateCollection);

            $templateCollection = new opTemplateCollection('language');
            $template = new opHtmlTemplate($this->getTranslationList());
            $templateCollection->addTemplate($template);
            $templateManager->addCollection($templateCollection);

            # User
            $templateCollection = new opTemplateCollection('siteadmin');
            $template = new opHtmlTemplate('<a href="'.opURL::getUrl('/admin/opUsers/userEdit/'.$this->activeUser->getId()).'" title="'.$this->activeUser->getFullName().'"><span><img src="/themes/opAdmin/images/icons/user.png" class="btnIcon" alt="'.$this->activeUser->getFullName().'" /> '.$this->activeUser->getFullName().'</span></a>');
            $templateCollection->addTemplate($template);
            $templateManager->addCollection($templateCollection);

            # Logout
            $templateCollection = new opTemplateCollection('logout');
            $template = new opHtmlTemplate('<a href="'.opURL::getUrl('/admin/logout').'" title="'.opTranslation::getTranslation('_logout').'"><span><img src="/themes/opAdmin/images/icons/lock-unlock.png" class="btnIcon" alt="'.opTranslation::getTranslation('_logout').'" /> '.opTranslation::getTranslation('_logout').'</span></a>');
            $templateCollection->addTemplate($template);
            $templateManager->addCollection($templateCollection);

            # Sitename
            $templateCollection = new opTemplateCollection('sitename');
            $template = new opHtmlTemplate($this->systemConfiguration->site_name);
            $templateCollection->addTemplate($template);
            $templateManager->addCollection($templateCollection);

            # SiteURL
            $templateCollection = new opTemplateCollection('siteurl');
            $template = new opHtmlTemplate('http://'.str_replace('http://', '', $this->systemConfiguration->site_url));
            $templateCollection->addTemplate($template);
            $templateManager->addCollection($templateCollection);

            # Menu template
            $templateCollection = new opTemplateCollection('menu');
            $template = new opHtmlTemplate($this->buildNav());
            $templateCollection->addTemplate($template);
            $templateManager->addCollection($templateCollection);

            # themePath
            $templateCollection = new opTemplateCollection('themePath');
            $template = new opHtmlTemplate($theme->getThemePath());
            $templateCollection->addTemplate($template);
            $templateManager->addCollection($templateCollection);

            #analyze qStr
            $requestType    = (isset($route[1])) ? $route[1] : false;
            $requestMethod  = (isset($route[2])) ? $route[2] : 'adminIndex';
            $requestArgs    = array();
            for ($x = 3; $x < count($route); $x++) {
                $requestArgs[] = $route[$x];
            }
            
            # Admin content
            if (! $requestType) {
                opSystem::redirect('/opCreate');
            } else {
                $templateCollection = new opTemplateCollection('adminContent');
                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_plugins WHERE plugin_name = :name');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('name' => $requestType));
                if ($rVal->fetchColumn() > 0) {
                    $rVal = $this->db->prepare('SELECT * FROM op_plugins WHERE plugin_name = :name');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal->execute(array('name' => $requestType));
                    $rVal = $rVal->fetch();
                    $plugin = opPluginFactory::produce($requestType, $theme, $requestArgs);
                    if ($requestMethod && method_exists($plugin, $requestMethod)) {
                        if (!in_array($requestMethod, $this->nonURLCallableMethods, true)) {
                            $pluginTemplate = $plugin->$requestMethod();

                            if ($pluginTemplate instanceof opTemplate) {
                                $templateCollection->addTemplate($pluginTemplate);
                            } else {
                                $templateCollection->addTemplate($this->exception($theme, '&quot;'.$requestMethod.'&quot; - '.opTranslation::getTranslation('_method_return')));
                            }
                        } else {
                            $templateCollection->addTemplate($this->exception($theme, '&quot;'.$requestMethod.'&quot; - '.opTranslation::getTranslation('_method_uncallable')));
                        }
                    } else {
                        $templateCollection->addTemplate($this->exception($theme, '&quot;'.$requestMethod.'&quot; - '.opTranslation::getTranslation('_method_not_found')));
                    }
                } else {
                    $templateCollection->addTemplate($this->exception($theme, '&quot;'.$requestType.'&quot; - '.opTranslation::getTranslation('_plugin_not_found')));
                }
            }
            $templateManager->addCollection($templateCollection);

            $systemMessages = opSystem::getMessages();
            if (count($systemMessages) > 0) {
                $templateCollection = new opTemplateCollection('message');
                foreach ($systemMessages as $message) {
                    $templateCollection->addTemplate(new opHtmlTemplate($message));
                }
                $templateManager->addCollection($templateCollection);
            }

            $theme->setBody($templateManager);
            $theme->render();
        } else {
            opSystem::redirect('/login');
        }
    }

    public function simplebrowser() {
        if ($this->authenticate()) {
            $sb = new opSimpleBrowser();
            $sb->render();
        } else {
            opSystem::redirect('/login');
        }
    }

    public function login() {
        # Notify observers
        $this->attach(new opAdminBlacklistObserver());
        $this->attach(new opAdminWhitelistObserver());
        $this->attach(new opAdminHammerObserver());
        $this->notify();

        # Login start
        $captchaEnabled = ($this->systemConfiguration->disable_captcha == 1) ? false : true;

        # Construct page
        $compressJS = ($this->systemConfiguration->caching) ? $this->systemConfiguration->compress_js : false;
        $compressCSS = ($this->systemConfiguration->caching) ? $this->systemConfiguration->compress_css : false;

        $theme = new opTheme('themes/opAdmin/', false, false, $compressJS, $compressCSS, $this->systemConfiguration->cache_ttl);

        $theme->addCSS(new opCSSFile($theme->getThemePath().'css/login.css'));
        $theme->addJS(new opJSFile($theme->getThemePath().'js/opLogin.js'));
        $templateManager = new opTemplateManager(file_get_contents(DOCUMENT_ROOT.$theme->getThemePath().'templates/blank.tpl'));
        
        # Title
        $theme->setTitle($this->systemConfiguration->site_name.' | Openpublish CMS Administration');

        # Content
        $templateCollection = new opTemplateCollection('adminContent');
        $template = new opFileTemplate(DOCUMENT_ROOT.$theme->getThemePath().'templates/login.tpl');
        $Cookie = new opCookie('opAdmin', true, opSystem::getSecretKey());
        if (isset($_POST['username'])) {
            $captchaPass = true;
            if ($captchaEnabled) {
                $Captcha = new opCaptcha();
                if (!isset($_POST['captcha']) || ! $Captcha->authenticate($_POST['captcha'])) {
                    $captchaPass = false;
                    $template->set('CaptchaFailed', true);
                }
            }
            if ($captchaPass) {
                $auth = new opAdminAuthentication($this->db, opSystem::getSecretKey());
                $auth->logout();
                if ($auth->login($_POST['username'], $_POST['password'])) {
                    if (isset($_POST['rememberme'])) {
                        $Cookie->set(array('username' => strip_tags(trim($_POST['username'])), 'password' => strip_tags(trim($_POST['password']))));
                    } else {
                        $Cookie->remove();
                    }
                    opSystem::redirect('');
                } else {
                    $template->set('LoginFailed', true);
                }
            }
        }
        $template->set('captchaEnabled', $captchaEnabled);
        $template->set('cookieData', $Cookie->get());
        $template->set('opThemePath', $theme->getThemePath());
        $template->set('opVersion', 'v'.opSystem::getVersion());
        $templateCollection->addTemplate($template);

        $templateManager->addCollection($templateCollection);
        $theme->setBody($templateManager);
        $theme->render();
    }

    public function logout() {
        $auth = new opAdminAuthentication($this->db, opSystem::getSecretKey());
        $auth->logout();
        opSystem::redirect('/login');
    }

    public function tools() {
        if ($this->authenticate()) {
            $args = $this->router->getArgs();
            $db   = opSystem::getDatabaseInstance();
            if (isset($args[2])) {
                switch ($args[2]) {
                    case 'getImageThumbnail':
                        $imageSize = (isset($args[3]) && is_numeric($args[3])) ? $args[3] : false;
                        $imageID   = (isset($args[4]) && is_numeric($args[4])) ? $args[4] : false;

                        if ($imageSize !== false && $imageID !== false) {
                            $rVal = $db->prepare('SELECT COUNT(*) FROM op_filemanager_filemap WHERE id = :id');
                            $rVal->setFetchMode(PDO::FETCH_ASSOC);
                            $rVal->execute(array('id' => $imageID));
                            if ($rVal->fetchColumn() > 0) {
                                $rVal = $db->prepare('SELECT * FROM op_filemanager_filemap WHERE id = :id');
                                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                                $rVal->execute(array('id' => $imageID));
                                $fileData = $rVal->fetch();

                                $file = opFileFactory::identify(DOCUMENT_ROOT.$fileData['filepath'].$fileData['filename']);
                                if ($file instanceof opGraphicsFile) {
                                    echo $file->getThumbnail($imageSize);
                                }
                            }
                        }
                        break;
                }
            }
        }
        exit();
    }

    public function resetPassword() {
        # Construct page
        $compressJS = ($this->systemConfiguration->caching) ? $this->systemConfiguration->compress_js : false;
        $compressCSS = ($this->systemConfiguration->caching) ? $this->systemConfiguration->compress_css : false;

        $theme = new opTheme('themes/opAdmin/', false, false, $compressJS, $compressCSS, $this->systemConfiguration->cache_ttl);

        # Title
        $theme->setTitle($this->systemConfiguration->site_name.' | Openpublish CMS Administration');
        $theme->addCSS(new opCSSFile($theme->getThemePath().'css/login.css'));
        $theme->addJS(new opJSFile($theme->getThemePath().'js/opLogin.js'));
        $templateManager = new opTemplateManager(file_get_contents(DOCUMENT_ROOT.$theme->getThemePath().'templates/blank.tpl'));
        $templateCollection = new opTemplateCollection('adminContent');
        $template = new opFileTemplate(DOCUMENT_ROOT.$theme->getThemePath().'templates/resetPassword.tpl');
        if (isset($_POST['username'])) {
            $Captcha = new opCaptcha();
            if ($Captcha->authenticate($_POST['captcha'])) {
                $userTo = new opUser($this->db, $_POST['username'], null);
                $auth = new opAdminAuthentication($this->db, opSystem::getSecretKey());
                $confirmKey = $auth->registerPasswordReset($userTo);
                if ($confirmKey != false) {
                    # Get superadmin account to use as sender
                    $rVal = $this->db->query('SELECT * FROM op_admin_users WHERE superadmin = 1');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal = $rVal->fetch();
                    $userFrom = new opUser($this->db, null, $rVal['id']);

                    # Create mail
                    $mail = new opMail();
                    $mail->setFrom(new opMailRecipient($userFrom->getFullName(), $userFrom->getEmail()));

                    $mail->setSubject('Password reset instructions');

                    $plainText  = ":userName:,\n";
                    $plainText .= "a request for a password reset on your account has been registered.\n\n";
                    $plainText .= "Your password has not changed yet!\n";
                    $plainText .= "If you still wish to reset your password please click the link below.\n";
                    $plainText .= "http://".$_SERVER['SERVER_NAME']."/admin/confirmReset/".$confirmKey."\n\n";
                    $plainText .= "If you do not wish to reset your password or you did not request a reset, you can safely ignore this email and continue working with your current password.\n\n";
                    $plainText .= $_SERVER['SERVER_NAME'];

                    $plainText = str_replace(array(':userName:'), array($userTo->getFullName()), $plainText);

                    $mail->setMessage($plainText);

                    $mail->addRecipient(new opMailRecipient($userTo->getFullName(), $userTo->getEmail()));
                    $mail->sendMail();

                    $template->set('ResetSuccess', true);
                } else {
                    $template->set('ResetFailed', true);
                }
            } else {
                $template->set('CaptchaFailed', true);
            }
        }
        $template->set('opThemePath', $theme->getThemePath());
        $template->set('opVersion', 'v'.opSystem::getVersion());
        $templateCollection->addTemplate($template);
        $templateManager->addCollection($templateCollection);
        $theme->setBody($templateManager);
        $theme->render();
    }

    public function confirmReset() {
        $route = $this->router->getArgs();

        # Construct page
        $compressJS = ($this->systemConfiguration->caching) ? $this->systemConfiguration->compress_js : false;
        $compressCSS = ($this->systemConfiguration->caching) ? $this->systemConfiguration->compress_css : false;

        $theme = new opTheme('themes/opAdmin/', false, false, $compressJS, $compressCSS, $this->systemConfiguration->cache_ttl);

        # Title
        $theme->setTitle($this->systemConfiguration->site_name.' | Openpublish CMS Administration');
        $theme->addCSS(new opCSSFile($theme->getThemePath().'css/login.css'));
        $theme->addJS(new opJSFile($theme->getThemePath().'js/opLogin.js'));
        $templateManager = new opTemplateManager(file_get_contents(DOCUMENT_ROOT.$theme->getThemePath().'templates/blank.tpl'));
        $templateCollection = new opTemplateCollection('adminContent');
        $template = new opFileTemplate(DOCUMENT_ROOT.$theme->getThemePath().'templates/confirmReset.tpl');
        $keyCode = (isset($route[2])) ? $route[2] : false;
        if ($keyCode != false) {
            $auth = new opAdminAuthentication($this->db, opSystem::getSecretKey());
            $dataArr = $auth->resetPassword($keyCode);
            if ($dataArr != false) {
                # New password
                $newPwd = $dataArr[0];
                
                # Create userTo
                $uid = $dataArr[1];
                $userTo = new opUser($this->db, null, $uid);

                # Get superadmin account to use as sender
                $rVal = $this->db->query('SELECT * FROM op_admin_users WHERE superadmin = 1');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal = $rVal->fetch();
                $userFrom = new opUser($this->db, null, $rVal['id']);

                # Create mail
                $mail = new opMail();                
                $mail->setFrom(new opMailRecipient($userFrom->getFullName(), $userFrom->getEmail()));

                $mail->setSubject('New password');

                $plainText = "Your password has been reset.\n";
                $plainText .= "Your new password is: ".$newPwd."\n\n";
                $plainText .= $_SERVER['SERVER_NAME'];

                $mail->setMessage($plainText);

                $mail->addRecipient(new opMailRecipient($userTo->getFullName(), $userTo->getEmail()));
                $mail->sendMail();

                $template->set('ResetSuccess', true);
            } else {
                $template->set('ResetFailed', true);
            }
        } else {
            opSystem::redirect('/login');
        }
        $template->set('opThemePath', $theme->getThemePath());
        $template->set('opVersion', 'v'.opSystem::getVersion());
        $templateCollection->addTemplate($template);
        $templateManager->addCollection($templateCollection);
        $theme->setBody($templateManager);
        $theme->render();
    }

    public function captcha() {
        $captcha = new opCaptcha();
        $captcha->render();
        exit();
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

    private function exception($theme, $msg) {
        $template = new opFileTemplate(DOCUMENT_ROOT.$theme->getThemePath().'templates/exception.tpl');
        $template->set('msg', htmlentities($msg, ENT_QUOTES, 'UTF-8', false));
        $template->set('opThemePath', $theme->getThemePath());

        return $template;
    }

    private function buildNav() {
        $activePage = $this->router->getArgs();
        $leftArr = array(opTranslation::getTranslation('_create') => 'opCreate', opTranslation::getTranslation('_layouts') => 'opLayout', opTranslation::getTranslation('_menu') => 'opMenu', opTranslation::getTranslation('_files') => 'opFileManager');
        $rightArr = array(opTranslation::getTranslation('_configuration') => 'opSiteConfig', opTranslation::getTranslation('_users') => 'opUsers', opTranslation::getTranslation('_plugins') => 'opPlugin', opTranslation::getTranslation('_themes') => 'opThemes');

        if (isset($activePage[1]) && in_array($activePage[1], $leftArr, true) || isset($activePage[1]) && in_array($activePage[1], $rightArr, true)) {
            $activateFirst = false;
        } else {
            $activateFirst = true;
        }

        $html = '<ul id="nav-left">';
        $i = 0;
        foreach ($leftArr as $k => $v) {
            if ($activateFirst && $i == 0) {
                $activeCSS = ' class="active"';
            } else {
                $activeCSS = (isset($activePage[1]) && $activePage[1] == $v) ? ' class="active"' : '';
            }
            $html .= '<li><a'.$activeCSS.' href="'.opURL::getUrl('/admin/'.$v).'"><span>'.$k.'</span></a></li>';
            $i++;
        }
        $html .= '</ul>';

        $html .= '<ul id="nav-right">';
        foreach ($rightArr as $k => $v) {
            $activeCSS = (isset($activePage[1]) && $activePage[1] == $v) ? ' class="active"' : '';
            $html .= '<li><a'.$activeCSS.' href="'.opURL::getUrl('/admin/'.$v).'"><span>'.$k.'</span></a></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    private function getTranslationList() {
        $rVal = $this->db->query('SELECT * FROM op_translations ORDER BY name_en');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $translations = $rVal->fetchAll();

        $userTranslation = false;
        $list = '';
        foreach ($translations as $translation) {
            if ($translation['code'] == $this->activeUser->getLocale()) {
                $list .= '<a href="#" class="languageSelect"><span><img src="/core/plugins/opTranslation/flags/'.$translation['code'].'.png" class="languageFlag" alt="'.$translation['name_na'].'" /> '.$translation['name_na'].'</span></a>';
                $userTranslation = true;
                break;
            }
        }
        if (! $userTranslation) {
            $list .= '<a href="#" class="languageSelect"><span>Unknown translation</span></a>';
        }

        $list .= '<ul id="languageList">';

        foreach ($translations as $translation) {
            $list .= '<li><a href="'.opURL::getUrl('/admin/opTranslation/setTranslation/'.$translation['code']).'"><img src="/core/plugins/opTranslation/flags/'.$translation['code'].'.png" class="languageFlag" alt="'.$translation['name_na'].'" /> '.$translation['name_na'].'</a></li>';
        }
        $list .= '<li><a href="'.opURL::getUrl('/admin/opTranslation').'"><img src="/core/plugins/opTranslation/icons/globe.png" class="translateFlag" alt="'.opTranslation::getTranslation('_translations').'" /> '.opTranslation::getTranslation('_translations').'</a></li>';
        $list .= '</ul>';

        return $list;
    }

    private function authenticate() {
        $auth = new opAdminAuthentication($this->db, opSystem::getSecretKey());
        return $auth->authenticate();
    }
}
?>