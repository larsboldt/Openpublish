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
class opTranslation extends opPluginBase {
    private static $activeUser;
    private static $translations = array();
    protected $translationMapper;

    protected function initialize() {
        $this->translationMapper = new opFormDataMapper($this->db);
        $this->translationMapper->setTable('op_translations');
        $this->translationMapper->setFieldIDName('id');

        self::getUser();
    }

    public static function getConfig() {
        return simplexml_load_file(self::getFullPath(__CLASS__).'opTranslation.xml');
    }

    public function adminIndex() {
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opTranslation.index.php');
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));

        $rVal = $this->db->query('SELECT * FROM op_translations ORDER BY name_en');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template->set('opTranslations', $rVal->fetchAll());
        $template->set('opPluginName', get_class($this));
        return $template;
    }

    public function setTranslation() {
        $tCode = (isset($this->args[0])) ? $this->args[0] : false;
        if ($tCode !== false) {
            $this->translationMapper->setFieldIDName('code');
            $this->translationMapper->setRowID(strtolower($tCode));
            $translationData = $this->translationMapper->fetchRow();
            if ($translationData !== false) {
                $rVal = $this->db->prepare('UPDATE op_admin_users SET locale = :code WHERE id = :id');
                $rVal->execute(array('code' => strtolower($tCode), 'id' => self::$activeUser->getId()));

                opSystem::Msg(sprintf(opTranslation::getTranslation('_translation_changed_to', get_class($this)), '&quot;'.$translationData->name_na.'&quot'), opSystem::SUCCESS_MSG);
                header('Location: '.$_SERVER['HTTP_REFERER']);
                exit();
            }
        }
        opSystem::Msg(opTranslation::getTranslation('_unknown_translation', get_class($this)), opSystem::ERROR_MSG);
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit();
    }

    public function translationAdd() {
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/globe--plus.png', opTranslation::getTranslation('_add_translation', get_class($this)).' | '.opTranslation::getTranslation('_translations', get_class($this)));
        $aForm->setAction('/admin/opTranslation/translationAdd');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opTranslation');

        $tBox = new opFormElementTextbox('name_en', opTranslation::getTranslation('_name_en', get_class($this)), 100);
        $tBox->addValidator(new opFormValidateStringLength(2, 100));
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextbox('name_na', opTranslation::getTranslation('_name_na', get_class($this)), 100);
        $tBox->addValidator(new opFormValidateStringLength(2, 100));
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextbox('code', opTranslation::getTranslation('_code', get_class($this)), 2);
        $tBox->addValidator(new opFormValidateStringLength(2, 2));
        $aForm->addElement($tBox);
        
        if (isset($_POST['name_en'])) {
            $is_valid = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($is_valid) {
                $this->translationMapper->addElements($aForm->getElements());
                $this->translationMapper->insert();

                opSystem::Msg(opTranslation::getTranslation('_translation_added', get_class($this)), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opTranslation');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }

        return $template;
    }

    public function translationEdit() {
        $tID = (isset($this->args[0])) ? $this->args[0] : false;
        if ($tID !== false) {
            $this->translationMapper->setRowID($tID);
            $translationData = $this->translationMapper->fetchRow();
            if ($translationData !== false) {
                $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/globe--plus.png', opTranslation::getTranslation('_edit_translation', get_class($this)).' | '.opTranslation::getTranslation('_translations', get_class($this)));
                $aForm->setAction('/admin/opTranslation/translationEdit/'.$tID);
                $aForm->setMethod('post');
                $aForm->setCancelLink('/admin/opTranslation');

                $tBox = new opFormElementTextbox('name_en', opTranslation::getTranslation('_name_en', get_class($this)), 100);
                $tBox->addValidator(new opFormValidateStringLength(2, 100));
                $tBox->setValue($translationData->name_en);
                $aForm->addElement($tBox);

                $tBox = new opFormElementTextbox('name_na', opTranslation::getTranslation('_name_na', get_class($this)), 100);
                $tBox->addValidator(new opFormValidateStringLength(2, 100));
                $tBox->setValue($translationData->name_na);
                $aForm->addElement($tBox);

                $tBox = new opFormElementTextbox('code', opTranslation::getTranslation('_code', get_class($this)), 2);
                $tBox->addValidator(new opFormValidateStringLength(2, 2));
                $tBox->setValue($translationData->code);
                $aForm->addElement($tBox);

                if (isset($_POST['name_en'])) {
                    $is_valid = $aForm->isValid($_POST);
                    $template = new opHtmlTemplate($aForm->render());
                    if ($is_valid) {
                        $this->translationMapper->addElements($aForm->getElements());
                        $this->translationMapper->update();

                        opSystem::Msg(opTranslation::getTranslation('_translation_updated', get_class($this)), opSystem::SUCCESS_MSG);
                        opSystem::redirect('/opTranslation');
                    }
                } else {
                    $template = new opHtmlTemplate($aForm->render());
                }

                return $template;
            }
        }
        opSystem::Msg(opTranslation::getTranslation('_unknown_translation', get_class($this)), opSystem::ERROR_MSG);
        opSystem::redirect('/opTranslation');
    }

    public function translationRemove() {
        $tID = (isset($this->args[0])) ? $this->args[0] : false;
        if ($tID !== false) {
            $this->translationMapper->setRowID($tID);
            $translationData = $this->translationMapper->fetchRow();
            if ($translationData !== false) {
                $rVal = $this->db->prepare('UPDATE op_admin_users SET locale = \'gb\' WHERE locale = :code');
                $rVal->execute(array('code' => $translationData->code));

                $this->translationMapper->delete();

                opSystem::Msg(sprintf(opTranslation::getTranslation('_translation_removed', get_class($this)), '&quot;'.$translationData->name_en.'&quot'), opSystem::SUCCESS_MSG);
            } else {
                opSystem::Msg(opTranslation::getTranslation('_unknown_translation', get_class($this)), opSystem::ERROR_MSG);
            }
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_translation', get_class($this)), opSystem::ERROR_MSG);
        }
        opSystem::redirect('/opTranslation');
    }

    public function translate() {
        $tID        = (isset($this->args[0])) ? $this->args[0] : false;
        $tPlugin    = (isset($this->args[1])) ? $this->args[1] : 'core';
        if ($tID !== false) {
            $this->translationMapper->setRowID($tID);
            $translationData = $this->translationMapper->fetchRow();
            if ($translationData !== false) {
                $translateTo    = (is_file(DOCUMENT_ROOT.'translations/'.$tPlugin.'/'.$translationData->code.'.xml')) ? simplexml_load_file(DOCUMENT_ROOT.'translations/'.$tPlugin.'/'.$translationData->code.'.xml') : false;
                $translateFrom  = (is_file(DOCUMENT_ROOT.'translations/'.$tPlugin.'/gb.xml')) ? simplexml_load_file(DOCUMENT_ROOT.'translations/'.$tPlugin.'/gb.xml') : false;

                if ($translateFrom !== false) {
                    $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/globe--arrow.png', opTranslation::getTranslation('_translate', get_class($this)).' | '.opTranslation::getTranslation('_translations', get_class($this)));
                    $aForm->setAction('/admin/opTranslation/translate/'.$tID.'/'.$tPlugin);
                    $aForm->setMethod('post');
                    $aForm->setCancelLink('/admin/opTranslation');

                    $hBox = new opFormElementHidden('tID', 'tID');
                    $hBox->setValue($tID);
                    $aForm->addElement($hBox);

                    $pluginName = 'Core';
                    if ($tPlugin != 'core') {
                        $p = new $tPlugin($this->theme, null);
                        $c = $p->getConfig();
                        $pluginName = (string)$c->name;
                    }
                    $hBox = new opFormElementTextheader('header', sprintf(opTranslation::getTranslation('_translation_for', get_class($this)), $translationData->name_na, '&quot;'.$pluginName.'&quot;'));
                    $aForm->addElement($hBox);
                    
                    $sBox = new opFormElementSelect('opPlugin', opTranslation::getTranslation('_translate', get_class($this)));
                    $sBox->addOption('core', 'Core');
                    $rVal = $this->db->query('SELECT * FROM op_plugins');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    foreach ($rVal->fetchAll() as $pDB) {
                        $p = new $pDB['plugin_name']($this->theme, null);
                        $c = $p->getConfig();
                        if ($c->hasAdmin == 'true') {
                            $sBox->addOption($pDB['plugin_name'], $c->name);
                        }
                    }
                    $sBox->setValue($tPlugin);
                    $aForm->addElement($sBox);
                    
                    $hBox = new opFormElementTextheader('translation', opTranslation::getTranslation('_translation', get_class($this)));
                    $aForm->addElement($hBox);

                    foreach ($translateFrom as $tag) {
                        $tagName        = (string)$tag['name'];
                        $tagTranslation = (string)$tag;

                        $tagTranslationNA = '';
                        if ($translateTo !== false) {
                            foreach ($translateTo as $tagTo) {
                                if ($tagTo['name'] == $tagName) {
                                    $tagTranslationNA = (string)$tagTo;
                                    break;
                                }
                            }
                        }

                        $tBox = new opFormElementTextbox($tagName, $tagTranslation.' ('.$tagName.')', 255);
                        $tBox->setValue($tagTranslationNA);
                        $tBox->addValidator(new opFormValidateStringLength(1, 255));
                        $aForm->addElement($tBox);
                    }

                    if (isset($_POST['tID'])) {
                        $is_valid = $aForm->isValid($_POST);
                        $template = new opHtmlTemplate($aForm->render());
                        if ($is_valid) {
                            $xmlFile = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<translation>\n";
                            foreach ($_POST as $k => $v) {
                                if (substr($k, 0, 1) == '_') {
                                    $xmlFile .= chr(9)."<tag name=\"".$k."\"><![CDATA[".trim($v)."]]></tag>\n";
                                }
                            }
                            $xmlFile .= '</translation>';
                            $fHnd = fopen(DOCUMENT_ROOT.'translations/'.$tPlugin.'/'.$translationData->code.'.xml', 'w+');
                            if ($fHnd !== false) {
                                fwrite($fHnd, $xmlFile);
                                fclose($fHnd);

                                opSystem::Msg(opTranslation::getTranslation('_translation_saved', get_class($this)), opSystem::SUCCESS_MSG);
                                opSystem::redirect('/opTranslation/translate/'.$tID.'/'.$tPlugin);
                            } else {
                                opSystem::Msg(opTranslation::getTranslation('_translation_save_error_msg', get_class($this)), opSystem::ERROR_MSG);
                            }
                        }
                    } else {
                        $template = new opHtmlTemplate($aForm->render());
                    }
                    
                    $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opTranslation.translate.js'));
                    return $template;
                } else {
                    opSystem::Msg(sprintf(opTranslation::getTranslation('_missing_translation', get_class($this)), $tPlugin), opSystem::ERROR_MSG);
                }
            } else {
                opSystem::Msg(opTranslation::getTranslation('_unknown_translation', get_class($this)), opSystem::ERROR_MSG);
            }
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_translation', get_class($this)), opSystem::ERROR_MSG);
        }
        opSystem::redirect('/opTranslation');
    }

    public static function install() {
        $sqlImport = new opSQLImport(opSystem::getDatabaseInstance());

        # Import tables
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opTranslation.install.sql')) { return false; };
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opTranslation.data.sql')) { return false; };

        return true;
    }

    public static function getTranslation($tag, $plugin = 'core') {
        self::getUser();
        self::getTranslations($plugin);
        $translationList = self::$translations[$plugin];
        if ($translationList !== false) {
            foreach ($translationList as $translationTag) {
                if ($tag == $translationTag['name']) {
                    return htmlentities((string)$translationTag, ENT_QUOTES, 'UTF-8');
                }
            }
        }
        return $tag;
    }

    public static function installTranslation($locale, $xml, $class) {
        if (!is_dir(DOCUMENT_ROOT.'translations/'.$class)) {
            if (is_writable(DOCUMENT_ROOT.'translations')) {
                mkdir(DOCUMENT_ROOT.'translations/'.$class);
            } else {
                return false;
            }
        }
        $fHnd = fopen(DOCUMENT_ROOT.'translations/'.$class.'/'.$locale.'.xml', 'w+');
        if ($fHnd !== false) {
            fwrite($fHnd, $xml);
            fclose($fHnd);
            return true;
        } else {
            return false;
        }
    }

    public static function uninstallTranslation($class) {
        if (is_dir(DOCUMENT_ROOT.'translations/'.$class)) {
            $dh = opendir(DOCUMENT_ROOT.'translations/'.$class);
            if ($dh !== false) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..') {
                        unlink(DOCUMENT_ROOT.'translations/'.$class.'/'.$file);
                    }
                }
                closedir($dh);
            }
            rmdir(DOCUMENT_ROOT.'translations/'.$class);
            return true;
        } else {
            return false;
        }
    }

    protected static function getTranslations($plugin) {
        if (! array_key_exists($plugin, self::$translations)) {
            if (is_file(DOCUMENT_ROOT.'translations/'.$plugin.'/'.self::$activeUser->getLocale().'.xml')) {
                self::$translations[$plugin] = simplexml_load_file(DOCUMENT_ROOT.'translations/'.$plugin.'/'.self::$activeUser->getLocale().'.xml');
            } else {
                self::$translations[$plugin] = false;
            }
        }
    }

    protected static function getUser() {
        if (is_null(self::$activeUser)) {
            $db = opSystem::getDatabaseInstance();
            $username = (isset($_SESSION['opAdmin'])) ? $_SESSION['opAdmin']['username'] : false;
            if ($username) {
                self::$activeUser = new opUser($db, $username, null);
            } else {
                die('Use of opTranslation in getOutput() is not supported');
            }
        }
    }
}
?>