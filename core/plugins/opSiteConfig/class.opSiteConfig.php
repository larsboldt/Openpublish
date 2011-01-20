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
class opSiteConfig extends opPluginBase {
    public static function getConfig() {
        return simplexml_load_file(self::getFullPath(__CLASS__).'opSiteConfig.xml');
    }

    public function adminIndex() {
        $formDataMapper = new opFormDataMapper($this->db);
        $formDataMapper->setTable('op_site_config');
        $formDataMapper->addElementTypeToSkip(new opFormElementTabGroup(null, null));
        $formDataMapper->addElementTypeToSkip(new opFormElementTabContent(null, null));
        $formDataMapper->addElementTypeToSkip(new opFormElementTabContentEnd(null, null));

        $configData = $formDataMapper->fetchAll();
        $configData = $configData[0];

        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/gear.png', opTranslation::getTranslation('_configuration', get_class($this)));
        $aForm->setMethod('post');
        $aForm->setAction('/admin/opSiteConfig');

        $tabGroup = new opFormElementTabGroup('tabGroup', 'tabGroup');
        $tabGroup->addTab('generalContent', opTranslation::getTranslation('_general', get_class($this)));
        $tabGroup->addTab('aestheticsContent', opTranslation::getTranslation('_aesthetics', get_class($this)));
        $tabGroup->addTab('filesContent', opTranslation::getTranslation('_files', get_class($this)));
        $tabGroup->addTab('cachingContent', opTranslation::getTranslation('_caching', get_class($this)));
        $tabGroup->addTab('securityContent', opTranslation::getTranslation('_security', get_class($this)));
        $aForm->addElement($tabGroup);

        $tabContent = new opFormElementTabContent('generalContent', 'generalContent');
        $aForm->addElement($tabContent);
        $tBox = new opFormElementTextbox('site_name', opTranslation::getTranslation('_site_name', get_class($this)), 150);
        $tBox->addValidator(new opFormValidateStringLength(2, 150));
        $tBox->setValue($configData->site_name);
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextbox('site_url', opTranslation::getTranslation('_site_url', get_class($this)), 100);
        $tBox->addValidator(new opFormValidateStringLength(2, 100));
        $tBox->setValue($configData->site_url);
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextbox('date_format', opTranslation::getTranslation('_date_format', get_class($this)), 20);
        $tBox->addValidator(new opFormValidateStringLength(1, 20));
        $tBox->setValue($configData->date_format);
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextbox('time_format', opTranslation::getTranslation('_time_format', get_class($this)), 20);
        $tBox->addValidator(new opFormValidateStringLength(1, 20));
        $tBox->setValue($configData->time_format);
        $aForm->addElement($tBox);

        $checked = ($configData->site_status == 1) ? true : false;
        $cBox = new opFormElementCheckbox('site_status', opTranslation::getTranslation('_site_offline', get_class($this)), $checked);
        $cBox->setValue(1);
        $aForm->addElement($cBox);

        $tabContent = new opFormElementTabContentEnd('generalEnd', 'generalEnd');
        $aForm->addElement($tabContent);

        # Aesthetics
        $tabContent = new opFormElementTabContent('aestheticsContent', 'aestheticsContent');
        $aForm->addElement($tabContent);

        $tBox = new opFormElementTextbox('title_separator', opTranslation::getTranslation('_title_separator', get_class($this)), 10);
        $tBox->addValidator(new opFormValidateStringLength(1, 10));
        $tBox->setValue($configData->title_separator);
        $aForm->addElement($tBox);

        $checked = ($configData->title_breadcrumb == 1) ? true : false;
        $cBox = new opFormElementCheckbox('title_breadcrumb', opTranslation::getTranslation('_title_breadcrumb', get_class($this)), $checked);
        $cBox->setValue(1);
        $aForm->addElement($cBox);

        $tBox = new opFormElementTextbox('title_breadcrumb_separator', opTranslation::getTranslation('_title_breadcrumb_separator', get_class($this)), 10);
        $tBox->addValidator(new opFormValidateStringLength(1, 10));
        $tBox->setValue($configData->title_breadcrumb_separator);
        $aForm->addElement($tBox);

        $checked = ($configData->force_url_lowercase == 1) ? true : false;
        $cBox = new opFormElementCheckbox('force_url_lowercase', opTranslation::getTranslation('_force_lowercase_url', get_class($this)), $checked);
        $cBox->setValue(1);
        $aForm->addElement($cBox);

        $tabContent = new opFormElementTabContentEnd('aestheticsEnd', 'aestheticsEnd');
        $aForm->addElement($tabContent);

        # Files
        $tabContent = new opFormElementTabContent('filesContent', 'filesContent');
        $aForm->addElement($tabContent);

        $tBox = new opFormElementTextbox('file_permission', opTranslation::getTranslation('_file_permissions', get_class($this)), 4);
        $tBox->addValidator(new opFormValidateStringLength(4, 4));
        $tBox->addValidator(new opFormValidateNumeric());
        $tBox->setValue($configData->file_permission);
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextbox('dir_permission', opTranslation::getTranslation('_dir_permissions', get_class($this)), 4);
        $tBox->addValidator(new opFormValidateStringLength(4, 4));
        $tBox->addValidator(new opFormValidateNumeric());
        $tBox->setValue($configData->dir_permission);
        $aForm->addElement($tBox);

        $tabContent = new opFormElementTabContentEnd('filesEnd', 'filesEnd');
        $aForm->addElement($tabContent);

        # Caching
        $tabContent = new opFormElementTabContent('cachingContent', 'cachingContent');
        $aForm->addElement($tabContent);

        $checked = ($configData->caching == 1) ? true : false;
        $cBox = new opFormElementCheckbox('caching', opTranslation::getTranslation('_enable_server_caching', get_class($this)), $checked);
        $cBox->setValue(1);
        $aForm->addElement($cBox);

        $tBox = new opFormElementTextbox('cache_ttl', opTranslation::getTranslation('_server_cache_ttl', get_class($this)), 6);
        $tBox->addValidator(new opFormValidateStringLength(1, 6));
        $tBox->addValidator(new opFormValidateNumeric());
        $tBox->setValue($configData->cache_ttl);
        $aForm->addElement($tBox);

        $checked = ($configData->local_caching == 1) ? true : false;
        $cBox = new opFormElementCheckbox('local_caching', opTranslation::getTranslation('_enable_local_caching', get_class($this)), $checked);
        $cBox->setValue(1);
        $aForm->addElement($cBox);

        $tBox = new opFormElementTextbox('local_cache_ttl', opTranslation::getTranslation('_local_cache_ttl', get_class($this)), 6);
        $tBox->addValidator(new opFormValidateStringLength(1, 6));
        $tBox->addValidator(new opFormValidateNumeric());
        $tBox->setValue($configData->local_cache_ttl);
        $aForm->addElement($tBox);

        $checked = ($configData->compress_css == 1) ? true : false;
        $cBox = new opFormElementCheckbox('compress_css', opTranslation::getTranslation('_compress_css', get_class($this)), $checked);
        $cBox->setValue(1);
        $aForm->addElement($cBox);

        $checked = ($configData->compress_js == 1) ? true : false;
        $cBox = new opFormElementCheckbox('compress_js', opTranslation::getTranslation('_compress_js', get_class($this)), $checked);
        $cBox->setValue(1);
        $aForm->addElement($cBox);

        $tabContent = new opFormElementTabContentEnd('cachingEnd', 'cachingEnd');
        $aForm->addElement($tabContent);

        # Security
        $tabContent = new opFormElementTabContent('securityContent', 'securityContent');
        $aForm->addElement($tabContent);
        
        $checked = ($configData->disable_captcha == 1) ? true : false;
        $cBox = new opFormElementCheckbox('disable_captcha', opTranslation::getTranslation('_disable_captcha', get_class($this)), $checked);
        $cBox->setValue(1);
        $aForm->addElement($cBox);

        $rBox = new opFormElementRadio('login_protection', opTranslation::getTranslation('_login_protection', get_class($this)));
        $rBox->addOption(0, opTranslation::getTranslation('_none', get_class($this)), 0, ($configData->login_protection == 0) ? true : false);
        $rBox->addOption(1, opTranslation::getTranslation('_blacklist', get_class($this)), 1, ($configData->login_protection == 1) ? true : false);
        $rBox->addOption(2, opTranslation::getTranslation('_whitelist', get_class($this)), 2, ($configData->login_protection == 2) ? true : false);
        $aForm->addElement($rBox);

        $tBox = new opFormElementTextarea('blacklist', opTranslation::getTranslation('_blacklist', get_class($this)));
        $tBox->setValue($configData->blacklist);
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextarea('whitelist', opTranslation::getTranslation('_whitelist', get_class($this)));
        $tBox->setValue($configData->whitelist);
        $aForm->addElement($tBox);

        $checked = ($configData->hammer_protection == 1) ? true : false;
        $cBox = new opFormElementCheckbox('hammer_protection', opTranslation::getTranslation('_hammer_protection', get_class($this)), $checked);
        $cBox->setValue(1);
        $aForm->addElement($cBox);

        $tBox = new opFormElementTextarea('hammer_intervals', opTranslation::getTranslation('_hammer_intervals', get_class($this)));
        $tBox->setValue($configData->hammer_intervals);
        $aForm->addElement($tBox);

        $tabContent = new opFormElementTabContentEnd('securityEnd', 'securityEnd');
        $aForm->addElement($tabContent);

        if (isset($_POST['site_name'])) {
            $validForm = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($validForm) {
                $formDataMapper->addElements($aForm->getElements());
                $formDataMapper->updateAllRows();
                
                opSystem::Msg(opTranslation::getTranslation('_configuration_updated', get_class($this)), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opSiteConfig');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }
        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opSiteConfig.index.js'));

        return $template;
    }

    public static function install() {
        $sqlImport = new opSQLImport(opSystem::getDatabaseInstance());

        # Import tables
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opSiteConfig.install.sql')) { return false; };

        return true;
    }
}
?>