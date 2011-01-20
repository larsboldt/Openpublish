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
class opGoogleAnalytics extends opPluginBase {
    public static function getConfig() {
        return simplexml_load_file(self::getFullPath(__CLASS__).'opGoogleAnalytics.xml');
    }

    public static function getIcon() {
        return self::getRelativePath(__CLASS__).'icons/google_16x16.png';
    }

    public function getOutput($requestID, $renderMode) {
        $gaID = opSystem::_get('gaid', get_class($this));
        if (opSystem::_get('active', get_class($this)) == 1) {
            $gaCode = '<script type="text/javascript">
    var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
    document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
    try{
        var pageTracker = _gat._getTracker("'.$gaID.'");
        pageTracker._trackPageview();
    } catch(err) {}
</script>';
            $this->theme->setRawBody($this->theme->getBody().$gaCode);
        }
        return false;
    }

    public function adminIndex() {
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/google_16x16.png', opTranslation::getTranslation('_google_analytics', __CLASS__));
        $aForm->setAction('/admin/opGoogleAnalytics');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opCreate');

        $hBox = new opFormElementTextheader('ga_account', opTranslation::getTranslation('_account_information', __CLASS__));

        $tBox = new opFormElementTextbox('gaid', opTranslation::getTranslation('_google_analytics_id', __CLASS__));
        $tBox->setValue(opSystem::_get('gaid', get_class($this)));
        $aForm->addElement($tBox);

        $checked = (opSystem::_get('active', get_class($this)) == 1) ? true : false;
        $cBox = new opFormElementCheckbox('active', opTranslation::getTranslation('_active', __CLASS__), $checked);
        $aForm->addElement($cBox);

        if (isset($_POST['gaid'])) {
            $isValid = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($isValid) {
                opSystem::_set('gaid', $_POST['gaid'], get_class($this));
                if (isset($_POST['active'])) {
                    opSystem::_set('active', 1, get_class($this));
                } else {
                    opSystem::_unset('active', get_class($this));
                }

                opSystem::Msg(opTranslation::getTranslation('_google_analytics_updated', __CLASS__), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opGoogleAnalytics');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }

        return $template;
    }
}
?>