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
class opThemes extends opPluginBase {
    protected $themeMapper;

    protected function initialize() {
        $this->themeMapper = new opFormDataMapper(opSystem::getDatabaseInstance());
        $this->themeMapper->setTable('op_themes');
        $this->themeMapper->setFieldIDName('path');
    }

    public static function getConfig() {
        return simplexml_load_file(self::getFullPath(__CLASS__).'opThemes.xml');
    }

    public function adminIndex() {
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opThemes.index.php');

        $rVal = $this->db->query('SELECT * FROM op_themes');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template->set('opThemes', $rVal->fetchAll());

        $rVal = $this->db->query('SELECT * FROM op_theme_templates ORDER BY parent ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template->set('opTemplates', $rVal->fetchAll());

        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('opPluginName', get_class($this));
        return $template;
    }

    public function refresh() {
        $actualThemes = array();

        foreach ($this->findFiles(DOCUMENT_ROOT.'themes/', array('xml')) as $file) {
            $path = pathinfo($file);
            if ($path['basename'] == 'theme.xml') {
                $themePath = str_replace(DOCUMENT_ROOT, '', $path['dirname']).'/';

                if ($themePath != 'themes/opAdmin/') {
                    # Update themes
                    $themeId = false;
                    $this->themeMapper->setRowID($themePath);
                    $themeData = $this->themeMapper->fetchRow();
                    if ($themeData === false) {
                        $element = new opFormElementHidden('path', 'path');
                        $element->setValue($themePath);
                        $this->themeMapper->addElement($element);

                        $element = new opFormElementHidden('active', 'active');
                        $element->setValue(1);
                        $this->themeMapper->addElement($element);

                        $themeId = $this->themeMapper->insert();
                    } else {
                        $themeId = $themeData->id;
                    }

                    $actualThemes[] = $themePath;

                    # Update new and changed templates
                    $actualTemplates = array();
                    $xml = simplexml_load_file($file);
                    foreach ($xml->templates->template as $template) {
                        $templateSrc  = (isset($template['src'])) ? $template['src'] : false;
                        $templateName = (isset($template['name'])) ? $template['name'] : false;

                        if ($templateName !== false && $templateSrc !== false) {
                            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_theme_templates WHERE parent = :parent AND filepath = :filepath');
                            $rVal->setFetchMode(PDO::FETCH_ASSOC);
                            $rVal->execute(array('parent' => $themeId, 'filepath' => $templateSrc));
                            if ($rVal->fetchColumn() > 0) {
                                $rVal = $this->db->prepare('UDPATE op_theme_templates SET name = :name WHERE filepath = :filepath AND parent = :parent');
                                $rVal->execute(array('parent' => $themeId, 'filepath' => $templateSrc, 'name' => $templateName));
                            } else {
                                $rVal = $this->db->prepare('INSERT INTO op_theme_templates (name, filepath, parent) VALUES (:name, :filepath, :parent)');
                                $rVal->execute(array('parent' => $themeId, 'filepath' => $templateSrc, 'name' => $templateName));
                            }

                            $actualTemplates[] = (string)$templateSrc;
                        }
                    }

                    # Remove templates
                    $rVal = $this->db->prepare('SELECT * FROM op_theme_templates WHERE parent = :parent');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal->execute(array('parent' => $themeId));
                    foreach ($rVal->fetchAll() as $template) {
                        if (!in_array($template['filepath'], $actualTemplates, true)) {
                            $this->removeTemplate($template['id']);
                        }
                    }
                }
            }
        }
        
        opSystem::Msg(opTranslation::getTranslation('_themes_updated', get_class($this)), opSystem::SUCCESS_MSG);
        opSystem::redirect('/opThemes');
    }

    public function themeDelete() {
        if (isset($this->args[0]) && is_numeric($this->args[0])) {
            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_themes WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $this->args[0]));
            if ($rVal->fetchColumn() > 0) {
                $rVal = $this->db->prepare('DELETE FROM op_themes WHERE id = :id');
                $rVal->execute(array('id' => $this->args[0]));

                $rVal = $this->db->prepare('SELECT * FROM op_theme_templates WHERE parent = :parent');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('parent' => $this->args[0]));
                foreach ($rVal->fetchAll() as $k => $v) {
                    $this->removeTemplate($v['id']);
                }

                opSystem::Msg(opTranslation::getTranslation('_theme_delete_msg', get_class($this)), opSystem::INFORM_MSG);
                opSystem::redirect('/opThemes');
            } else {
                opSystem::Msg(opTranslation::getTranslation('_unknown_theme_id', get_class($this)), opSystem::ERROR_MSG);
                opSystem::redirect('/opThemes');
            }
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_theme_id', get_class($this)), opSystem::ERROR_MSG);
            opSystem::redirect('/opThemes');
        }
    }

    public static function install() {
        $sqlImport = new opSQLImport(opSystem::getDatabaseInstance());

        # Import tables
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opThemes.install.sql')) { return false; };

        return true;
    }

    protected function findFiles($dir, $filters = array(), $files = array()) {
        if (is_dir($dir)) {
            foreach(scandir($dir) as $d) {
                if ($d != '.' && $d != '..') {
                    if (is_dir($dir.$d)) {
                        $this->findFiles($dir.$d.'/', $filters, &$files);
                    } else {
                        if (count($filters) > 0) {
                            $pathinfo = pathinfo($dir.$d);
                            if (in_array($pathinfo['extension'], $filters, true)) {
                                $files[] = $dir.$d;
                            }
                        } else {
                            $files[] = $dir.$d;
                        }
                    }
                }
            }
        }
        return $files;
    }

    protected function removeTemplate($templateID) {
        $rVal = $this->db->prepare('DELETE FROM op_theme_templates WHERE id = :id');
        $rVal->execute(array('id' => $templateID));

        # Remove layouts & collections
        $rVal = $this->db->prepare('SELECT * FROM op_layouts WHERE theme_template = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $templateID));
        foreach ($rVal->fetchAll() as $k => $v) {
            # Delete collections
            $rVal = $this->db->prepare('DELETE FROM op_layout_collections WHERE parent = :id');
            $rVal->execute(array('id' => $v['id']));

            # Remove menu urls, bridges and redirect urls
            $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE layout_id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $templateID));
            foreach ($rVal->fetchAll() as $menuItem) {
                # Delete redirects
                $rVal = $this->db->prepare('DELETE FROM op_menu_item_redirects WHERE parent = :id');
                $rVal->execute(array('id' => $menuItem['id']));

                # Delete menu bridge
                $rVal = $this->db->prepare('DELETE FROM op_menu_bridge WHERE menu_to = :id');
                $rVal->execute(array('id' => $menuItem['id']));

                # Delete menu item
                $rVal = $this->db->prepare('DELETE FROM op_menu_items WHERE id = :id');
                $rVal->execute(array('id' => $menuItem['id']));
            }
            
            # Delete layout
            $rVal = $this->db->prepare('DELETE FROM op_layouts WHERE id = :id');
            $rVal->execute(array('id' => $v['id']));
        }
    }
}
?>