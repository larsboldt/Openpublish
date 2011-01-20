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
class opLayout extends opPluginBase {
    protected $metaMapper, $layoutMapper;

    protected function initialize() {
        $this->metaMapper = new opFormDataMapper($this->db);
        $this->metaMapper->setTable('op_layout_metatags');
        $this->metaMapper->setFieldIDName('id');

        $this->layoutMapper = new opFormDataMapper($this->db);
        $this->layoutMapper->setTable('op_layouts');
        $this->layoutMapper->setFieldIDName('id');
    }

    public static function getConfig() {
        return simplexml_load_file(self::getFullPath(__CLASS__).'opLayout.xml');
    }

    public function adminIndex() {
        $sortByTemplate = (isset($_SESSION['opLayout_sortByTemplate'])) ? $_SESSION['opLayout_sortByTemplate'] : 0;
        $sortByTheme    = (isset($_SESSION['opLayout_sortByTheme']))    ? $_SESSION['opLayout_sortByTheme']    : 0;

        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opLayout.index.php');

        if ($sortByTemplate > 0 && $sortByTheme > 0) {
            $rVal = $this->db->prepare('SELECT op_theme_templates.name AS template_name, op_layouts.* FROM op_layouts LEFT JOIN op_theme_templates ON op_theme_templates.id = op_layouts.theme_template WHERE op_layouts.parent = 0 AND op_layouts.theme_template = :templateID AND op_theme_templates.parent = :themeParent ORDER BY op_layouts.theme_template ASC, op_layouts.parent ASC, op_layouts.name ASC');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('templateID' => $sortByTemplate, 'themeParent' => $sortByTheme));
        } else if ($sortByTemplate > 0) {
            $rVal = $this->db->prepare('SELECT op_theme_templates.name AS template_name, op_layouts.* FROM op_layouts LEFT JOIN op_theme_templates ON op_theme_templates.id = op_layouts.theme_template WHERE op_layouts.parent = 0 AND op_layouts.theme_template = :templateID ORDER BY op_layouts.theme_template ASC, op_layouts.parent ASC, op_layouts.name ASC');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('templateID' => $sortByTemplate));
        } else if ($sortByTheme > 0) {
            $rVal = $this->db->prepare('SELECT op_theme_templates.name AS template_name, op_layouts.* FROM op_layouts LEFT JOIN op_theme_templates ON op_theme_templates.id = op_layouts.theme_template WHERE op_layouts.parent = 0 AND op_theme_templates.parent = :themeParent ORDER BY op_layouts.theme_template ASC, op_layouts.parent ASC, op_layouts.name ASC');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('themeParent' => $sortByTheme));
        } else {
            $rVal = $this->db->query('SELECT op_theme_templates.name AS template_name, op_layouts.* FROM op_layouts LEFT JOIN op_theme_templates ON op_theme_templates.id = op_layouts.theme_template WHERE op_layouts.parent = 0 ORDER BY op_layouts.theme_template ASC, op_layouts.parent ASC, op_layouts.name ASC');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
        }
        $template->set('opLayouts', $this->recursiveArray($rVal->fetchAll()));

        if ($sortByTheme > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_theme_templates WHERE parent = :id ORDER BY name ASC');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $sortByTheme));
        } else {
            $rVal = $this->db->query('SELECT * FROM op_theme_templates ORDER BY name ASC');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
        }
        $template->set('opTemplates', $rVal->fetchAll());
        $template->set('opTemplateSort', $sortByTemplate);

        $rVal = $this->db->query('SELECT * FROM op_themes');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template->set('opThemes', $rVal->fetchAll());
        $template->set('opThemeSort', $sortByTheme);

        $template->set('opPluginPath', self::getRelativePath(__CLASS__));

        $this->theme->addCSS(new opCSSFile(self::getRelativePath(__CLASS__).'css/opLayout.index.css'));
        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opLayout.index.js'));
        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opAccordion.js'));

        $template->set('opPluginName', __CLASS__);
        return $template;
    }

    public function adminIndexSortByTemplate() {
        $_SESSION['opLayout_sortByTemplate'] = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;

        opSystem::redirect('/opLayout');
    }

    public function adminIndexSortByTheme() {
        $_SESSION['opLayout_sortByTheme'] = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;

        opSystem::redirect('/opLayout');
    }

    public function layoutNew() {
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/layout-header-footer-3.png', opTranslation::getTranslation('_new_layout', __CLASS__).' | '.opTranslation::getTranslation('_layouts', __CLASS__));
        $aForm->setAction('/admin/opLayout/layoutNew');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opLayout');

        $tabGroup = new opFormElementTabGroup('tabGroup', 'tabGroup');
        $tabGroup->addTab('generalContent', opTranslation::getTranslation('_general', __CLASS__));
        $tabGroup->addTab('advancedContent', opTranslation::getTranslation('_advanced', __CLASS__));
        $aForm->addElement($tabGroup);

        $tabContent = new opFormElementTabContent('generalContent', 'generalContent');
        $aForm->addElement($tabContent);
        $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_layout_name', __CLASS__), 100);
        $tBox->addValidator(new opFormValidateStringLength(3, 100));
        $aForm->addElement($tBox);

        $lBox = new opFormElementLayout('layout', opTranslation::getTranslation('_parent_layout', __CLASS__), true);
        $lBox->addValidator(new opFormValidateStringLength(3, 20));
        $aForm->addElement($lBox);
        $tabContent = new opFormElementTabContentEnd('generalEnd', 'generalEnd');
        $aForm->addElement($tabContent);

        $tabContent = new opFormElementTabContent('advancedContent', 'advancedContent');
        $aForm->addElement($tabContent);
        $sBox = new opFormElementSelect('type', opTranslation::getTranslation('_layout_type', __CLASS__));
        $sBox->addOption(0, 'Normal');
        $sBox->addOption(7, 'Quickpublish');
        $sBox->addOption(4, 'RSS 2.0');
        $sBox->addOption(5, 'RSS 1.0');
        $sBox->addOption(6, 'Atom');
        $sBox->addOption(1, '404');
        $sBox->addOption(2, '503');
        $sBox->addOption(3, 'Site offline');
        $aForm->addElement($sBox);

        $tBox = new opFormElementTextbox('quickpublish', opTranslation::getTranslation('_quickpublish_zone_id', __CLASS__), 3);
        $tBox->addValidator(new opFormValidateStringLength(1, 3));
        $tBox->addValidator(new opFormValidateNumeric());
        $tBox->setValue('0');
        $aForm->addElement($tBox);
        
        $cBox = new opFormElementCheckbox('disable_local_cache', opTranslation::getTranslation('_disable_local_cache', __CLASS__), false);
        $cBox->setValue(1);
        $aForm->addElement($cBox);

        $cBox = new opFormElementCheckbox('disable_meta_inheritance', opTranslation::getTranslation('_disable_meta_inheritance', __CLASS__), false);
        $cBox->setValue(1);
        $aForm->addElement($cBox);     
        $tabContent = new opFormElementTabContentEnd('advancedEnd', 'advancedEnd');
        $aForm->addElement($tabContent);

        if (isset($_POST['name'])) {
            $validForm = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($validForm) {
                list($templateId, $layoutId) = (strpos($_POST['layout'], ':') > 0) ? explode(':', $_POST['layout']) : array(0,0);
                $rVal = $this->db->prepare('INSERT INTO op_layouts (name, parent, theme_template, type, quickpublish, last_modified, etag, disable_local_cache, disable_meta_inheritance) VALUES (:name, :parent, :theme_template, :type, :quickpublish, :lm, :etag, :cache, :meta)');
                $rVal->execute(array('name' => $_POST['name'], 'parent' => $layoutId, 'theme_template' => $templateId, 'type' => $_POST['type'], 'quickpublish' => $_POST['quickpublish'], 'lm' => gmdate('D, d M Y H:i:s \G\M\T', time()), 'etag' => self::generateETag($_POST['name']), 'cache' => (isset($_POST['disable_local_cache']) ? 1 : 0), 'meta' => (isset($_POST['disable_meta_inheritance']) ? 1 : 0)));

                $lID = $this->db->lastInsertId();
                if ($_POST['type'] == 1) {
                    $this->db->query('UPDATE op_layouts SET type = 0 WHERE type = 1 AND id != '.$lID);
                } else if ($_POST['type'] == 2) {
                    $this->db->query('UPDATE op_layouts SET type = 0 WHERE type = 2 AND id != '.$lID);
                } else if ($_POST['type'] == 3) {
                    $this->db->query('UPDATE op_layouts SET type = 0 WHERE type = 3 AND id != '.$lID);
                }

                opSystem::Msg(opTranslation::getTranslation('_layout_added', __CLASS__), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opLayout');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }
        
        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opLayout.layoutNew.js'));

        return $template;
    }

    public function layoutEdit() {
        $layoutID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_layouts WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $layoutID));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_layouts WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $layoutID));
            $layoutData = $rVal->fetch();

            $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/layout-header-footer-3.png', opTranslation::getTranslation('_edit_layout', __CLASS__).' | '.opTranslation::getTranslation('_layouts', __CLASS__));
            $aForm->setAction('/admin/opLayout/layoutEdit/'.$layoutID);
            $aForm->setMethod('post');
            $aForm->setCancelLink('/admin/opLayout');

            $tabGroup = new opFormElementTabGroup('tabGroup', 'tabGroup');
            $tabGroup->addTab('generalContent', opTranslation::getTranslation('_general', __CLASS__));
            $tabGroup->addTab('advancedContent', opTranslation::getTranslation('_advanced', __CLASS__));
            $aForm->addElement($tabGroup);

            $tabContent = new opFormElementTabContent('generalContent', 'generalContent');
            $aForm->addElement($tabContent);
            $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_layout_name', __CLASS__), 100);
            $tBox->addValidator(new opFormValidateStringLength(3, 100));
            $tBox->setValue($layoutData['name']);
            $aForm->addElement($tBox);

            $lBox = new opFormElementLayout('layout', opTranslation::getTranslation('_parent_layout', __CLASS__), true, true);
            $lBox->addValidator(new opFormValidateStringLength(3, 20));
            $lBox->setValue($layoutData['id']);
            $aForm->addElement($lBox);
            $tabContent = new opFormElementTabContentEnd('generalEnd', 'generalEnd');
            $aForm->addElement($tabContent);

            $tabContent = new opFormElementTabContent('advancedContent', 'advancedContent');
            $aForm->addElement($tabContent);
            $sBox = new opFormElementSelect('type', opTranslation::getTranslation('_layout_type', __CLASS__));
            $sBox->addOption(0, 'Normal');
            $sBox->addOption(7, 'Quickpublish');
            $sBox->addOption(4, 'RSS 2.0');
            $sBox->addOption(5, 'RSS 1.0');
            $sBox->addOption(6, 'Atom');
            $sBox->addOption(1, '404');
            $sBox->addOption(2, '503');
            $sBox->addOption(3, 'Site offline');
            $sBox->setValue($layoutData['type']);
            $aForm->addElement($sBox);

            $tBox = new opFormElementTextbox('quickpublish', opTranslation::getTranslation('_quickpublish_zone_id', __CLASS__), 3);
            $tBox->addValidator(new opFormValidateStringLength(1, 3));
            $tBox->addValidator(new opFormValidateNumeric());
            $tBox->setValue($layoutData['quickpublish']);
            $aForm->addElement($tBox);

            $checked = ($layoutData['disable_local_cache'] == 1) ? true : false;
            $cBox = new opFormElementCheckbox('disable_local_cache', opTranslation::getTranslation('_disable_local_cache', __CLASS__), $checked);
            $cBox->setValue(1);
            $aForm->addElement($cBox);

            $checked = ($layoutData['disable_meta_inheritance'] == 1) ? true : false;
            $cBox = new opFormElementCheckbox('disable_meta_inheritance', opTranslation::getTranslation('_disable_meta_inheritance', __CLASS__), $checked);
            $cBox->setValue(1);
            $aForm->addElement($cBox);
            $tabContent = new opFormElementTabContentEnd('advancedEnd', 'advancedEnd');
            $aForm->addElement($tabContent);

            if (isset($_POST['name'])) {
                $validForm = $aForm->isValid($_POST);
                $template = new opHtmlTemplate($aForm->render());
                if ($validForm) {
                    list($templateId, $parentId) = (strpos($_POST['layout'], ':') > 0) ? explode(':', $_POST['layout']) : array(0,$layoutData['parent']);
                    $rVal = $this->db->prepare('UPDATE op_layouts SET name = :name, parent = :parent, type = :type, quickpublish = :quickpublish, disable_local_cache = :cache, disable_meta_inheritance = :meta WHERE id = :id');
                    $rVal->execute(array('name' => $_POST['name'], 'parent' => $parentId, 'id' => $layoutID, 'type' => $_POST['type'], 'quickpublish' => $_POST['quickpublish'], 'cache' => (isset($_POST['disable_local_cache']) ? 1 : 0), 'meta' => (isset($_POST['disable_meta_inheritance']) ? 1 : 0)));

                    if ($_POST['type'] == 1) {
                        $this->db->query('UPDATE op_layouts SET type = 0 WHERE type = 1 AND id != '.$layoutID);
                    } else if ($_POST['type'] == 2) {
                        $this->db->query('UPDATE op_layouts SET type = 0 WHERE type = 2 AND id != '.$layoutID);
                    } else if ($_POST['type'] == 3) {
                        $this->db->query('UPDATE op_layouts SET type = 0 WHERE type = 3 AND id != '.$layoutID);
                    }

                    opSystem::Msg(opTranslation::getTranslation('_layout_saved', __CLASS__), opSystem::SUCCESS_MSG);
                    opSystem::redirect('/opLayout');
                }
            } else {
                $template = new opHtmlTemplate($aForm->render());
            }

            $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opLayout.layoutNew.js'));

            return $template;
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_layout_id', __CLASS__), opSystem::ERROR_MSG);
            opSystem::redirect('/opLayout');
        }
    }

    public function layoutMetaEdit() {
        $layoutID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        # metaTags
        # Get all layout parents for inheritance
        $this->layoutMapper->setRowID($layoutID);
        $layoutData = $this->layoutMapper->fetchRow();

        $layoutMetaParent = $layoutData->parent;
        $layoutMetaParents[] = array($layoutID, $layoutData->name);
        $layoutMetaCollection = array('title' => true, 'description' => true, 'keywords' => true, 'author' => true, 'owner' => true, 'copyright' => true, 'robots' => true);

        # Remove overridden tags
        $this->metaMapper->setFieldIDName('parent');
        $this->metaMapper->setRowID($layoutID);
        $metaData = $this->metaMapper->fetchRow();
        if ($metaData !== false) {
            foreach ($metaData as $metaKey => $metaValue) {
                if (strlen($metaValue) > 0 && array_key_exists($metaKey, $layoutMetaCollection)) {
                    unset($layoutMetaCollection[$metaKey]);
                }
            }
        }

        $layoutMetaInheritance = array();
        while ($layoutMetaParent > 0) {
            $this->layoutMapper->setRowID($layoutMetaParent);
            $parentLayoutData = $this->layoutMapper->fetchRow();
            $layoutMetaParent = $parentLayoutData->parent;
            $layoutMetaParents[] = array($parentLayoutData->id, $parentLayoutData->name);
        }
        foreach ($layoutMetaParents as $layoutMetaParent) {
            $this->metaMapper->setRowID($layoutMetaParent[0]);
            $metaData = $this->metaMapper->fetchRow();
            if ($metaData !== false) {
                foreach ($metaData as $metaKey => $metaValue) {
                    if (strlen($metaValue) > 0 && array_key_exists($metaKey, $layoutMetaCollection)) {
                        if ($metaKey == 'robots') {
                            switch ($metaValue) {
                                case 2:
                                    $layoutMetaInheritance[$metaKey] = array('none', $layoutMetaParent[1]);
                                    break;
                                case 3:
                                    $layoutMetaInheritance[$metaKey] = array('noindex,follow', $layoutMetaParent[1]);
                                    break;
                                case 4:
                                    $layoutMetaInheritance[$metaKey] = array('index,nofollow', $layoutMetaParent[1]);
                                    break;
                                default:
                                    $layoutMetaInheritance[$metaKey] = array('all', $layoutMetaParent[1]);
                            }
                        } else {
                            $layoutMetaInheritance[$metaKey] = array($metaValue, $layoutMetaParent[1]);
                        }
                        unset($layoutMetaCollection[$metaKey]);
                    }
                }
            }
        }
        $metaInformation = false;
        if (count($layoutMetaInheritance) > 0) {
            $metaInformation = '<h6 style="margin-bottom: 10px;">'.opTranslation::getTranslation('_meta_inherit_msg', __CLASS__).'</h6>';
            $metaInformation .= '<table cellpadding="0" cellspacing="0" style="line-height: 20px;"><tr><th width="100" align="left" valign="top">'.opTranslation::getTranslation('_tag', __CLASS__).'</th><th width="150" align="left" valign="top">'.opTranslation::getTranslation('_layout', __CLASS__).'</th><th align="left" valign="top">'.opTranslation::getTranslation('_value', __CLASS__).'</th></tr>';
            foreach ($layoutMetaInheritance as $k => $v) {
                $metaInformation .= '<tr><td align="left" valign="top">'.$k.'</td><td align="left" valign="top">'.$v[1].'</td><td align="left" valign="top">'.$v[0].'</td></tr>';
            }
            $metaInformation .= '</table>';
        }

        # FORM
        $this->metaMapper->setRowID($layoutID);
        $metaData = $this->metaMapper->fetchRow();
        
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/layout-header-footer-3.png', opTranslation::getTranslation('_edit_meta_tags', __CLASS__).' | '.opTranslation::getTranslation('_layouts', __CLASS__));
        $aForm->setAction('/admin/opLayout/layoutMetaEdit/'.$layoutID);
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opLayout');

        $hBox = new opFormElementTextheader('metatags', opTranslation::getTranslation('_meta_tags', __CLASS__));
        $aForm->addElement($hBox);

        $tBox = new opFormElementTextbox('title', opTranslation::getTranslation('_title', __CLASS__), 255);
        if ($metaData !== false) {
            $tBox->setValue($metaData->title);
        }
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextarea('description', opTranslation::getTranslation('_description', __CLASS__));
        if ($metaData !== false) {
            $tBox->setValue($metaData->description);
        }
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextarea('keywords', opTranslation::getTranslation('_keywords', __CLASS__));
        if ($metaData !== false) {
            $tBox->setValue($metaData->keywords);
        }
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextbox('owner', opTranslation::getTranslation('_owner', __CLASS__), 255);
        if ($metaData !== false) {
            $tBox->setValue($metaData->owner);
        }
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextbox('author', opTranslation::getTranslation('_author', __CLASS__), 255);
        if ($metaData !== false) {
            $tBox->setValue($metaData->author);
        }
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextbox('copyright', opTranslation::getTranslation('_copyright', __CLASS__), 255);
        if ($metaData !== false) {
            $tBox->setValue($metaData->copyright);
        }
        $aForm->addElement($tBox);

        $rBox = new opFormElementSelect('robots', opTranslation::getTranslation('_robots', __CLASS__));
        $rBox->addOption('1', opTranslation::getTranslation('_index_follow', __CLASS__));
        $rBox->addOption('2', opTranslation::getTranslation('_no_index_no_follow', __CLASS__));
        $rBox->addOption('3', opTranslation::getTranslation('_no_index', __CLASS__));
        $rBox->addOption('4', opTranslation::getTranslation('_no_follow', __CLASS__));
        if ($metaData !== false) {
            $rBox->setValue($metaData->robots);
        }
        $aForm->addElement($rBox);

        if (isset($_POST['title'])) {
            $isValid = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($isValid) {
                $this->metaMapper->addElements($aForm->getElements());
                $this->metaMapper->addElementTypeToSkip(new opFormElementTextheader(null,null));
                if ($metaData !== false) {
                    # Update
                    $this->metaMapper->update();
                } else {
                    # Insert
                    $element = new opFormElementHidden('parent', 'parent');
                    $element->setValue($layoutID);
                    $this->metaMapper->addElement($element);
                    $this->metaMapper->insert();
                }

                opSystem::Msg(opTranslation::getTranslation('_meta_updated', __CLASS__), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opLayout');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }
        if ($metaInformation !== false) {
            opSystem::Msg($metaInformation, opSystem::INFORM_MSG);
        }
        return $template;
    }

    public function layoutDelete() {
        if (isset($_POST['layoutID']) && is_numeric($_POST['layoutID'])) {
            if (! $this->recursiveLayoutDelete($_POST['layoutID'])) {
                opSystem::Msg(opTranslation::getTranslation('_layout_delete_warn_msg', __CLASS__), opSystem::ERROR_MSG);
            } else {
                opSystem::Msg(opTranslation::getTranslation('_layout_deleted', __CLASS__), opSystem::SUCCESS_MSG);
            }
        }
        opSystem::redirect('/opLayout');
    }

    public static function install() {
        $sqlImport = new opSQLImport($this->db);

        # Import tables
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opLayout.install.sql')) { return false; };

        return true;
    }

    public static function isContentAssigned($pluginName, $contentID) {
        $db = opSystem::getDatabaseInstance();
        $pID = opPlugin::getIdByName($pluginName);

        $rVal = $db->prepare('SELECT COUNT(*) FROM op_layout_collections WHERE plugin_id = :pid AND plugin_child_id = :pcid');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('pid' => $pID, 'pcid' => $contentID));
        if ($rVal->fetchColumn() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns all layoutID's that are in use by the pluginID -> contentID
     * @param int $pluginID
     * @param int $contentID
     * @return array
     */
    public static function getAssignedLayouts($pluginID, $contentID) {
        $db = opSystem::getDatabaseInstance();
        $rVal = $db->prepare('SELECT parent AS layoutID FROM op_layout_collections WHERE plugin_id = :pid AND plugin_child_id = :pcid');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('pid' => $pluginID, 'pcid' => $contentID));
        return $rVal->fetchAll();
    }

    public static function generateETag($salt) {
        $db = opSystem::getDatabaseInstance();
        $ETagLength = 12;
        do {
            $ETag = substr(md5(microtime().$salt.rand(4,400000000)), 0, $ETagLength);
            $rVal = $db->prepare('SELECT COUNT(*) FROM op_layouts WHERE etag = :etag');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('etag' => $ETag));
        } while ($rVal->fetchColumn() > 0);
        return $ETag;
    }

    public static function hasAssignedChilds($layoutID) {
        $db = opSystem::getDatabaseInstance();
        $rVal = $db->prepare('SELECT COUNT(*) FROM op_layouts WHERE parent = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $layoutID));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $db->prepare('SELECT * FROM op_layouts WHERE parent = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $layoutID));
            foreach ($rVal->fetchAll() as $layout) {
                $pArr = self::getPluginsAssignedToLayout($layout['id']);
                if (count($pArr) <= 0) {
                    if (self::hasAssignedChilds($layout['id'])) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }
        return false;
    }

    public static function getPluginsAssignedToLayout($layoutID) {
        $db = opSystem::getDatabaseInstance();
        $pluginArr = array();

        $rVal = $db->query('SELECT * FROM op_plugins');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($rVal->fetchAll() as $p) {
            # Temporary fix to get around a 500 internal server error when fileLocator is empty
            $obj = new ReflectionClass($p['plugin_name']);

            if (call_user_func_array(array($p['plugin_name'], 'isLayoutAssigned'), $layoutID)) {
                $pluginArr[$p['id']] = $p['plugin_name'];
            }
        }

        return $pluginArr;
    }

    protected function recursiveLayoutDelete($layoutID) {
        $rVal = $this->db->prepare('SELECT * FROM op_layouts WHERE parent = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $layoutID));
        foreach ($rVal->fetchAll() as $v) {
            $this->recursiveLayoutDelete($v['id']);
        }
        $pArr = self::getPluginsAssignedToLayout($layoutID);
        if (count($pArr) <= 0) {
            $rVal = $this->db->prepare('DELETE FROM op_layout_collections WHERE parent = :id');
            $rVal->execute(array('id' => $layoutID));
            $rVal = $this->db->prepare('DELETE FROM op_layouts WHERE id = :id');
            $rVal->execute(array('id' => $layoutID));
            $rVal = $this->db->prepare('DELETE FROM op_layout_metatags WHERE parent = :id');
            $rVal->execute(array('id' => $layoutID));
            return true;
        } else {
            return false;
        }
    }

    protected function recursiveArray($source) {
        $destination = array();
        foreach ($source as $item) {
            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_layouts WHERE parent = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $item['id']));
            if ($rVal->fetchColumn() > 0) {
                $rVal = $this->db->prepare('SELECT * FROM op_layouts WHERE parent = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $item['id']));
                $destination[$item['id']] = array($item, $this->recursiveArray($rVal->fetchAll()));
            } else {
                $destination[$item['id']] = $item;
            }
        }
        return $destination;
    }
}
?>