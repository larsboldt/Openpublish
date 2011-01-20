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
class opSocialSharer extends opPluginBase {
    public function adminIndex() {
        $aForm = new opAdminForm(self::getIcon(), opTranslation::getTranslation('_social_sharer', __CLASS__));
        $aForm->setAction('/admin/opSocialSharer');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opCreate');

        $element = new opFormElementCodebox('code', opTranslation::getTranslation('_code', __CLASS__));
        $element->setSanitize(false);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_title', __CLASS__));
        $btn->setCode('{pageTitle}');
        $btn->setIcon(self::getRelativePath(__CLASS__).'icons/document-hf-delete.png');
        $element->addBtn($btn);

        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_url', __CLASS__));
        $btn->setCode('{pageUrl}');
        $btn->setIcon(self::getRelativePath(__CLASS__).'icons/document-globe.png');
        $element->addBtn($btn);
        $element->setValue(opSystem::_get('code', __CLASS__));
        $aForm->addElement($element);

        if (isset($_POST['code'])) {
            $isValid = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($isValid) {
                opSystem::_set('code', $_POST['code'], __CLASS__);

                $this->updateLastModified(opPlugin::getIdByName(__CLASS__), 0);

                opSystem::Msg(opTranslation::getTranslation('_sharer_updated', __CLASS__), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opSocialSharer');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }

        return $template;
    }

    public function getOutput($requestId, $requestMode) {
        $shareTemplate = opSystem::_get('code', __CLASS__);

        # Since OP Core cannot guarantee that the title is set at this stage
        # we need to insert a placeholder which will be replaced at a later
        # processing stage when the title has been set
        return str_ireplace(array('{pageTitle}', '{pageUrl}'),
                            array('{{{{opSocialSharerTitle}}}}', $this->getUrl()),
                            $shareTemplate);
    }

    public static function getIcon() {
        return self::getRelativePath(__CLASS__).'icons/balloon-twitter.png';
    }

    public static function getConfig() {
        return simplexml_load_file(self::getFullPath(__CLASS__).'opSocialSharer.xml');
    }

    public static function getContentList() {
        $contentList = new opContentList();
        $contentList->addElement(new opContentElement(0, opTranslation::getTranslation('_social_sharer', __CLASS__)));
        return $contentList;
    }

    public static function getContentNameById($id) {
         return opTranslation::getTranslation('_social_sharer', __CLASS__);
    }

    public static function install() {
        $sqlImport = new opSQLImport(opSystem::getDatabaseInstance());

        # Import data
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opSocialSharer.data.sql')) { return false; };

        return true;
    }

    protected function getUrl() {
        $protocol = (isset($_SERVER['HTTPS'])) ? 'https' : 'http';
        return rawurlencode($protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    }
}
?>