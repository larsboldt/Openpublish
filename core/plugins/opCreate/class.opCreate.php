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
class opCreate extends opPluginBase {
    protected $categoryMapper;

    protected function initialize() {
        $this->categoryMapper = new opFormDataMapper($this->db);
        $this->categoryMapper->setTable('op_create_categories');
        $this->categoryMapper->setFieldIDName('id');
    }

    public function categorySort() {
        if (isset($_POST['list'])) {
            $sortList = explode('&', $_POST['list']);
            $i = 0;
            foreach ($sortList as $catData) {
                list($garbage, $catID) = explode('=', $catData);
                $this->categoryMapper->clearAllElements();

                $element = new opFormElementHidden('position', 'position');
                $element->setValue($i);
                $this->categoryMapper->addElement($element);

                $this->categoryMapper->setRowID($catID);
                $this->categoryMapper->update();
                
                $i++;
            }
        }
        exit();
    }

    public function categoryDelete() {
        if (isset($_POST['delete'])) {
            if (is_array($_POST['delete'])) {
                foreach ($_POST['delete'] as $v) {
                    $rVal = $this->db->prepare('UPDATE op_plugins SET cat_id = 0 WHERE cat_id = :id');
                    $rVal->execute(array('id' => $v));

                    $this->categoryMapper->setRowID($v);
                    $this->categoryMapper->delete();
                }
            }
        }
        opSystem::redirect('/opCreate/categoryIndex');
    }

    public function categoryEdit() {
        $catID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $this->categoryMapper->setRowID($catID);

        $categoryData = $this->categoryMapper->fetchRow();
        if ($categoryData !== false) {
            $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/wand.png', opTranslation::getTranslation('_edit_category', get_class($this)).' | '.opTranslation::getTranslation('_create', get_class($this)));
            $aForm->setAction('/admin/opCreate/categoryEdit/'.$this->args[0]);
            $aForm->setMethod('post');
            $aForm->setCancelLink('/admin/opCreate/categoryIndex');

            $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_category_name', get_class($this)), 40);
            $tBox->addValidator(new opFormValidateStringLength(1, 40));
            $tBox->setValue($categoryData->name);
            $aForm->addElement($tBox);

            if (isset($_POST['name'])) {
                $valid = $aForm->isValid($_POST);
                $template = new opHtmlTemplate($aForm->render());
                if ($valid) {
                    $this->categoryMapper->addElements($aForm->getElements());
                    $this->categoryMapper->update();

                    opSystem::Msg(opTranslation::getTranslation('_category_updated', get_class($this)), opSystem::SUCCESS_MSG);
                    opSystem::redirect('/opCreate/categoryIndex');
                }
            } else {
                $template = new opHtmlTemplate($aForm->render());
            }

            return $template;
        }
        opSystem::Msg(opTranslation::getTranslation('_unknown_category_id', get_class($this)), opSystem::ERROR_MSG);
        opSystem::redirect('/opCreate/categoryIndex');
    }

    public function categoryNew() {
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/wand.png', opTranslation::getTranslation('_new_category', get_class($this)).' | '.opTranslation::getTranslation('_create', get_class($this)));
        $aForm->setAction('/admin/opCreate/categoryNew');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opCreate/categoryIndex');

        $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_category_name', get_class($this)), 40);
        $tBox->addValidator(new opFormValidateStringLength(1, 40));
        $aForm->addElement($tBox);

        $hBox = new opFormElementHidden('position', 'position');
        $hBox->setValue('0');
        $aForm->addElement($hBox);

        if (isset($_POST['name'])) {
            $valid = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($valid) {
                $this->categoryMapper->addElements($aForm->getElements());
                $this->categoryMapper->insert();

                opSystem::Msg(opTranslation::getTranslation('_category_added', get_class($this)), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opCreate/categoryIndex');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }

        return $template;
    }

    public function categoryIndex() {
        $rVal = $this->db->query('SELECT * FROM op_create_categories ORDER BY position ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opCreate.categoryIndex.php');
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('opPluginName', get_class($this));
        $template->set('cat', $rVal->fetchAll());

        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opCreate.categoryIndex.js'));

        return $template;
    }

    public function adminIndex() {
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opCreate.index.php');

        $rVal = $this->db->query('SELECT * FROM op_create_categories ORDER BY position ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template->set('opCategories', $rVal->fetchAll());

        $rVal = $this->db->query('SELECT * FROM op_plugins WHERE cat_id >= 0 ORDER BY cat_id ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $opPlugins = array();
        foreach ($rVal->fetchAll() as $k => $v) {
            $p = new $v['plugin_name']($this->theme, null);
            $c = $p->getConfig();
            if ($c->hasAdmin == 'true') {
                $opPlugins[] = array($v['cat_id'], $v['plugin_name'], $c->name, $p->getIcon());
            }
        }
        $template->set('opPlugins', $opPlugins);

        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('opThemePath', $this->theme->getThemePath());
        $template->set('opPluginName', get_class($this));

        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opCreate.index.js'));

        return $template;
    }

    public static function getConfig() {
        return simplexml_load_file(self::getFullPath(__CLASS__).'opCreate.xml');
    }

    public static function install() {
        $sqlImport = new opSQLImport(opSystem::getDatabaseInstance());

        # Import tables
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opCreate.install.sql')) { return false; };

        return true;
    }
}
?>