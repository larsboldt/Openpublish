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
class opMenu extends opPluginBase {
    protected $internalUrlManager, $externalUrlManager, $rc, $systemConfiguration;

    protected function initialize() {
        $this->internalUrlManager   = new opMenuURLManager();
        $this->externalUrlManager   = new opMenuExternalURLManager($this->db);
        $this->rc                   = opSystem::getRedirectControllerInstance();
        $this->systemConfiguration  = opSystem::getSystemConfiguration();
    }

    public static function controller($url) {
        $internalUrlManager = new opMenuURLManager();
        $db                 = opSystem::getDatabaseInstance();
        if ($internalUrlManager->isRegistered($url)) {
            $urlID = $internalUrlManager->getID($url);
            $rVal = $db->prepare('SELECT * FROM op_menu_items WHERE url = :urlID AND type = 1');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('urlID' => $urlID));
            $rVal = $rVal->fetch();
            if ($rVal['enabled'] > 0) {
                return $rVal['layout_id'];
            } else {
                return 503;
            }
        } else {
            return 404;
        }
    }

    public static function getPageTitle($url) {
        $internalUrlManager = new opMenuURLManager();
        $db                 = opSystem::getDatabaseInstance();
        if ($internalUrlManager->isRegistered($url)) {
            $urlID = $internalUrlManager->getID($url);
            $rVal = $db->prepare('SELECT * FROM op_menu_items WHERE url = :urlID');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('urlID' => $urlID));
            $rVal = $rVal->fetch();
            return self::buildPageTitle($rVal['name'], $rVal['parent'], $rVal['menu_parent']);
        } else {
            return false;
        }
    }

    public static function getConfig() {
        return simplexml_load_file(self::getFullPath(__CLASS__).'opMenu.xml');
    }

    public static function getIcon() {
        return self::getRelativePath(__CLASS__).'icons/menu.png';
    }

    public static function getContentList() {
        $db = opSystem::getDatabaseInstance();
        $rVal = $db->query('SELECT id, name FROM op_menus ORDER BY name');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $contentList = new opContentList();
        foreach ($rVal->fetchAll() as $k => $v) {
            $contentList->addElement(new opContentElement($v['id'], $v['name']));
        }
        $contentList->addElement(new opContentElement(-2, opTranslation::getTranslation('_breadcrumb', __CLASS__)));
        $contentList->addElement(new opContentElement(-1, opTranslation::getTranslation('_sitemap', __CLASS__)));
        return $contentList;
    }

    public static function getContentNameById($id) {
        if (is_numeric($id)) {
            if ($id == -1) {
                return opTranslation::getTranslation('_sitemap', get_class($this));
            } else if ($id == -2) {
                return opTranslation::getTranslation('_breadcrumb', get_class($this));
            } else {
                $db = opSystem::getDatabaseInstance();
                $rVal = $db->prepare('SELECT COUNT(*) FROM op_menus WHERE id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $id));
                if ($rVal->fetchColumn() > 0) {
                    $rVal = $db->prepare('SELECT name FROM op_menus WHERE id = :id');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal->execute(array('id' => $id));
                    $rVal = $rVal->fetch();
                    return $rVal['name'];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public function getOutput($requestID, $renderMode) {
        if (is_numeric($requestID)) {
            if ($requestID == -1) {
                return opSitemap::getSitemapAsUL();
            } else if ($requestID == -2) {
                return opBreadcrumb::getBreadcrumb();
            } else {
                switch ($renderMode) {
                    case 'rss2':
                    case 'rss1':
                    case 'atom':
                        return sprintf(opTranslation::getTranslation('_no_feed_output', get_class($this)), get_class($this));
                        break;
                    default:
                        $rVal = $this->db->prepare('SELECT * FROM op_menus WHERE id = :id');
                        $rVal->setFetchMode(PDO::FETCH_ASSOC);
                        $rVal->execute(array('id' => $requestID));
                        $rVal = $rVal->fetch();
                        $menuID = $rVal['menu_id'];
                        $menuClass = $rVal['menu_class'];
                        $menuActiveClass = $rVal['menu_active_class'];
                        $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE enabled = 1 AND hide = 0 AND menu_parent = :parent ORDER BY parent ASC, position ASC');
                        $rVal->setFetchMode(PDO::FETCH_ASSOC);
                        $rVal->execute(array('parent' => $requestID));
                        $ulmenu = new opMenuULRender();
                        return $ulmenu->generateUL($rVal->fetchAll(), $menuID, $menuClass, $menuActiveClass, $this->findActiveLinks());
                }
            }
        } else {
            return false;
        }
    }

    public function adminIndex() {
        $sort = (isset($_SESSION['opMenu_sort'])) ? $_SESSION['opMenu_sort'] : 0;
        if (isset($_POST['deleteItem'])) {
            if ($this->executeDelete($_POST['deleteItem'])) {
                opSystem::Msg(opTranslation::getTranslation('_item_deleted', get_class($this)), opSystem::SUCCESS_MSG);
            } else {
                opSystem::Msg(opTranslation::getTranslation('_unknown_item_id', get_class($this)), opSystem::ERROR_MSG);
            }
        }
        $rVal = $this->db->query('SELECT COUNT(*) FROM op_menu_items WHERE home = 1');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        if ($rVal->fetchColumn() <= 0) {
            opSystem::Msg(opTranslation::getTranslation('_no_home_link_warn_msg', get_class($this)), opSystem::INFORM_MSG);
        }

        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opMenu.index.php');
        if ($sort <= 0) {
            $rVal = $this->db->query('SELECT op_menus.name AS menuName, op_menu_items.* FROM op_menu_items LEFT JOIN op_menus ON op_menus.id = op_menu_items.menu_parent WHERE op_menu_items.parent = 0 ORDER BY op_menu_items.menu_parent ASC, op_menu_items.parent ASC, op_menu_items.position ASC');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
        } else {
            $rVal = $this->db->prepare('SELECT op_menus.name AS menuName, op_menu_items.* FROM op_menu_items LEFT JOIN op_menus ON op_menus.id = op_menu_items.menu_parent WHERE op_menu_items.menu_parent = :sort AND op_menu_items.parent = 0 ORDER BY op_menu_items.parent ASC, op_menu_items.position ASC');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('sort' => $sort));
        }
        $template->set('menuItems', $this->recursiveArray($rVal->fetchAll()));

        $rVal = $this->db->query('SELECT * FROM op_menus ORDER BY name ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template->set('opMenus', $rVal->fetchAll());
        $template->set('opSort', $sort);

        $template->set('externalURLManager', new opMenuExternalURLManager($this->db));

        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('opPluginName', get_class($this));
        $this->theme->addCSS(new opCSSFile(self::getRelativePath(__CLASS__).'css/opMenu.index.css'));
        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opAccordion.js'));
        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opMenu.index.js'));

        return $template;
    }

    public function adminIndexSort() {
        if (isset($this->args[0]) && is_numeric($this->args[0])) {
            $_SESSION['opMenu_sort'] = $this->args[0];
        }
        opSystem::redirect('/opMenu');
    }

    public function menuIndex() {
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opMenu.menuIndex.php');
        $rVal = $this->db->query('SELECT * FROM op_menus ORDER BY name ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template->set('menus', $rVal->fetchAll());
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('opPluginName', get_class($this));

        return $template;
    }

    public function menuNew() {
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/menu.png', opTranslation::getTranslation('_new_menu', get_class($this)).' | '.opTranslation::getTranslation('_menus', get_class($this)));
        $aForm->setAction("/admin/opMenu/menuNew");
        $aForm->setMethod('post');
        $aForm->setCancelLink("/admin/opMenu/menuIndex");

        $tabGroup = new opFormElementTabGroup('tabGroup', 'tabGroup');
        $tabGroup->addTab('generalContent', opTranslation::getTranslation('_general', get_class($this)));
        $tabGroup->addTab('cssContent', opTranslation::getTranslation('_css', get_class($this)));
        $aForm->addElement($tabGroup);

        $tabContent = new opFormElementTabContent('generalContent', 'generalContent');
        $aForm->addElement($tabContent);
        $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_name', get_class($this)), 40);
        $tBox->addValidator(new opFormValidateStringLength(3,40));
        $aForm->addElement($tBox);
        $tabContent = new opFormElementTabContentEnd('generalEnd', 'generalEnd');
        $aForm->addElement($tabContent);

        $tabContent = new opFormElementTabContent('cssContent', 'cssContent');
        $aForm->addElement($tabContent);
        $tBox = new opFormElementTextbox('menu_id', opTranslation::getTranslation('_menu_id', get_class($this)), 30);
        $tBox->addValidator(new opFormValidateStringLength(0,30));
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextbox('menu_class', opTranslation::getTranslation('_menu_class', get_class($this)), 30);
        $tBox->addValidator(new opFormValidateStringLength(0,30));
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextbox('menu_active_class', opTranslation::getTranslation('_active_class', get_class($this)), 30);
        $tBox->addValidator(new opFormValidateStringLength(0,30));
        $aForm->addElement($tBox);

        $cBox = new opFormElementCheckbox('menu_active_class_parents', opTranslation::getTranslation('_put_active_class', get_class($this)), false);
        $cBox->setValue(1);
        $aForm->addElement($cBox);
        $tabContent = new opFormElementTabContentEnd('cssEnd', 'cssEnd');
        $aForm->addElement($tabContent);

        if (!empty($_POST)) {
            $validForm = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($validForm) {
                $rVal = $this->db->prepare('INSERT INTO op_menus (name, menu_id, menu_class, menu_active_class, menu_active_class_parents) VALUES (:name, :menu_id, :menu_class, :menu_active_class, :menu_active_class_parents)');
                $rVal->execute(array('name' => $_POST['name'], 'menu_id' => $_POST['menu_id'], 'menu_class' => $_POST['menu_class'], 'menu_active_class' => $_POST['menu_active_class'], 'menu_active_class_parents' => (isset($_POST['menu_active_class_parents']) ? 1 : 0)));

                opSystem::Msg(opTranslation::getTranslation('_menu_added', get_class($this)), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opMenu/menuIndex');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }
        
        return $template;
    }

    public function menuEdit() {
        if (!empty($_POST)) {
            $editID = (isset($_POST['formEditID']) && is_numeric($_POST['formEditID'])) ? $_POST['formEditID'] : 0;
        } else {
            $editID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        }
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_menus WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $editID));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_menus WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $editID));
            $rVal = $rVal->fetch();

            $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/menu.png', opTranslation::getTranslation('_edit_menu', get_class($this)).' | '.opTranslation::getTranslation('_menus', get_class($this)));
            $aForm->setAction("/admin/opMenu/menuEdit");
            $aForm->setMethod('post');
            $aForm->setCancelLink("/admin/opMenu/menuIndex");

            $tabGroup = new opFormElementTabGroup('tabGroup', 'tabGroup');
            $tabGroup->addTab('generalContent', opTranslation::getTranslation('_general', get_class($this)));
            $tabGroup->addTab('cssContent', opTranslation::getTranslation('_css', get_class($this)));
            $aForm->addElement($tabGroup);

            $tabContent = new opFormElementTabContent('generalContent', 'generalContent');
            $aForm->addElement($tabContent);
            $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_name', get_class($this)), 40);
            $tBox->addValidator(new opFormValidateStringLength(3,40));
            $tBox->setValue($rVal['name']);
            $aForm->addElement($tBox);
            $tabContent = new opFormElementTabContentEnd('generalEnd', 'generalEnd');
            $aForm->addElement($tabContent);

            $tabContent = new opFormElementTabContent('cssContent', 'cssContent');
            $aForm->addElement($tabContent);
            $tBox = new opFormElementTextbox('menu_id', opTranslation::getTranslation('_menu_id', get_class($this)), 30);
            $tBox->addValidator(new opFormValidateStringLength(0,30));
            $tBox->setValue($rVal['menu_id']);
            $aForm->addElement($tBox);

            $tBox = new opFormElementTextbox('menu_class', opTranslation::getTranslation('_menu_class', get_class($this)), 30);
            $tBox->addValidator(new opFormValidateStringLength(0,30));
            $tBox->setValue($rVal['menu_class']);
            $aForm->addElement($tBox);

            $tBox = new opFormElementTextbox('menu_active_class', opTranslation::getTranslation('_active_class', get_class($this)), 30);
            $tBox->addValidator(new opFormValidateStringLength(0,30));
            $tBox->setValue($rVal['menu_active_class']);
            $aForm->addElement($tBox);

            $checked = ($rVal['menu_active_class_parents'] == 1) ? true : false;
            $cBox = new opFormElementCheckbox('menu_active_class_parents', opTranslation::getTranslation('_put_active_class', get_class($this)), $checked);
            $cBox->setValue(1);
            $aForm->addElement($cBox);
            $tabContent = new opFormElementTabContentEnd('cssEnd', 'cssEnd');
            $aForm->addElement($tabContent);

            $hBox = new opFormElementHidden('formEditID', 'formEditID');
            $hBox->setValue($rVal['id']);
            $aForm->addElement($hBox);

            if (!empty($_POST)) {
                $validForm = $aForm->isValid($_POST);
                $template = new opHtmlTemplate($aForm->render());
                if ($validForm) {
                    $rVal = $this->db->prepare('UPDATE op_menus SET name = :name, menu_id = :menu_id, menu_class = :menu_class, menu_active_class = :menu_active_class, menu_active_class_parents = :menu_active_class_parents WHERE id = :id');
                    $rVal->execute(array('name' => $_POST['name'], 'menu_id' => $_POST['menu_id'], 'menu_class' => $_POST['menu_class'], 'menu_active_class' => $_POST['menu_active_class'], 'menu_active_class_parents' => (isset($_POST['menu_active_class_parents']) ? 1 : 0), 'id' => $_POST['formEditID']));
                    
                    opSystem::Msg(opTranslation::getTranslation('_menu_updated', get_class($this)), opSystem::SUCCESS_MSG);
                    opSystem::redirect('/opMenu/menuIndex');
                }
            } else {
                $template = new opHtmlTemplate($aForm->render());
            }

            return $template;
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_menu', get_class($this)), opSystem::ERROR_MSG);
            opSystem::redirect('/opMenu/menuIndex');
        }
    }

    public function menuDelete() {
        $mIDs = (isset($_POST['delete'])) ? $_POST['delete'] : array();
        if (count($mIDs) > 0) {
            foreach ($mIDs as $m) {
                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_menus WHERE id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $m));
                if ($rVal->fetchColumn() > 0) {
                    # Clear menu
                    $rVal = $this->db->prepare('DELETE FROM op_menus WHERE id = :id');
                    $rVal->execute(array('id' => $m));

                    # Clear bridge
                    $rVal = $this->db->prepare('DELETE FROM op_menu_bridge WHERE menu_from = :id');
                    $rVal->execute(array('id' => $m));
                    
                    # Clear menu from any layout it was assigned to
                    $rVal = $this->db->prepare('DELETE FROM op_layout_collections WHERE plugin_id = :pid AND plugin_child_id = :pcid');
                    $rVal->execute(array('pid' => opPlugin::getIdByName(get_class($this)), 'pcid' => $m));

                    # Clear items
                    $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE menu_parent = :id');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal->execute(array('id' => $m));
                    foreach ($rVal->fetchAll() as $k => $v) {
                        $this->itemDelete_db($v['id']);
                    }

                    # Update last modified
                    $this->updateLastModified(opPlugin::getIdByName(get_class($this)), $m);
                }
            }
            opSystem::Msg(opTranslation::getTranslation('_menus_deleted', get_class($this)), opSystem::SUCCESS_MSG);
        } else {
            opSystem::Msg(opTranslation::getTranslation('_no_menu_selected', get_class($this)), opSystem::ERROR_MSG);
        }
        opSystem::redirect('/opMenu/menuIndex');
    }

    public function menuSort() {
        if (isset($_POST['serialized'])) {
            if (! empty($_POST['serialized'])) {
                $serialized = (isset($_POST['serialized'])) ? explode(',', $_POST['serialized']) : array();
                $i = 0;
                $menuParent = 0;
                foreach ($serialized as $k => $v) {
                    if ($i == 0) {
                        $rVal = $this->db->prepare('SELECT menu_parent FROM op_menu_items WHERE id = :id');
                        $rVal->setFetchMode(PDO::FETCH_ASSOC);
                        $rVal->execute(array('id' => $v));
                        $rVal = $rVal->fetch();
                        $menuParent = $rVal['menu_parent'];
                    }

                    $rVal = $this->db->prepare('UPDATE op_menu_items SET position = :pos WHERE id = :id');
                    $rVal->execute(array('pos' => $i, 'id' => $v));
                    $i++;
                }
                # Notify observer that menu changed so that cache can be updated
                $this->updateLastModified(opPlugin::getIdByName(get_class($this)), $menuParent);

                opSystem::Msg(opTranslation::getTranslation('_menu_order_saved', get_class($this)), opSystem::SUCCESS_MSG);
            } else {
                opSystem::Msg(opTranslation::getTranslation('_no_menu_selected', get_class($this)), opSystem::ERROR_MSG);
            }
        }
        $menuID     = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $parentID   = (isset($this->args[1]) && is_numeric($this->args[1])) ? $this->args[1] : 0;
        $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE parent = :parent AND menu_parent = :menu_parent ORDER BY position ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('parent' => $parentID, 'menu_parent' => $menuID));

        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opMenu.menuSort.php');
        $template->set('parentSelected', $parentID);
        $template->set('menuID', $menuID);
        $template->set('childsOfParent', $rVal->fetchAll());
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('opThemePath', $this->theme->getThemePath());

        $rVal = $this->db->query('SELECT * FROM op_menus ORDER BY name ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template->set('menus', $rVal->fetchAll());
        $template->set('menuSelected', $menuID);

        $rVal = $this->db->prepare('SELECT op_menu_items2.name AS parentName, op_menu_items.* FROM op_menu_items INNER JOIN op_menu_items AS op_menu_items2 ON op_menu_items2.id = op_menu_items.parent WHERE op_menu_items.menu_parent = :menu_parent GROUP BY op_menu_items.parent ORDER BY op_menu_items.parent ASC, op_menu_items.position ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('menu_parent' => $menuID));
        $template->set('parentCategories', $rVal->fetchAll());
        $template->set('opPluginName', get_class($this));
        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opMenu.menuSort.js'));

        return $template;
    }

    public function bridgeIndex() {
        if (isset($_POST['from']) && isset($_POST['to']) && is_numeric($_POST['from']) && is_numeric($_POST['to'])) {
            if ($_POST['from'] > 0 && $_POST['to'] > 0) {
                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_menu_bridge WHERE menu_from = :mf AND menu_to = :mt');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('mf' => $_POST['from'], 'mt' => $_POST['to']));
                if ($rVal->fetchColumn() <= 0) {
                    $rVal = $this->db->prepare('INSERT INTO op_menu_bridge (menu_from, menu_to) VALUES (:from, :to)');
                    $rVal->execute(array('from' => $_POST['from'], 'to' => $_POST['to']));

                    $this->rebuildURLs(0, $_POST['from']);

                    opSystem::Msg(opTranslation::getTranslation('_bridge_created', get_class($this)), opSystem::SUCCESS_MSG);
                } else {
                    opSystem::Msg(opTranslation::getTranslation('_bridge_already_exists', get_class($this)), opSystem::ERROR_MSG);
                }
            } else {
                opSystem::Msg(opTranslation::getTranslation('_cannot_create_bridge_link', get_class($this)), opSystem::ERROR_MSG);
            }
            opSystem::redirect('/opMenu/bridgeIndex');
        }
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opMenu.bridgeIndex.php');
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));

        $rVal = $this->db->query('SELECT * FROM op_menus ORDER BY name ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $menuArr = array();
        foreach ($rVal->fetchAll() as $v) {
            $rVal = $this->db->query('SELECT COUNT(*) FROM op_menu_bridge WHERE menu_from = '.$v['id']);
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            if ($rVal->fetchColumn() > 0) {
                continue;
            } else {
                $menuArr[] = $v;
            }
        }
        $template->set('menu', $menuArr);

        $rVal = $this->db->query('SELECT op_menus.name as menuName, op_menu_bridge.* FROM op_menu_bridge LEFT JOIN op_menus ON op_menus.id = op_menu_bridge.menu_from');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $linkArr = array();
        foreach ($rVal->fetchAll() as $v) {
            $getMenuName = $this->db->prepare('SELECT op_menus.name as menuName, op_menu_items.* FROM op_menu_items LEFT JOIN op_menus ON op_menus.id = op_menu_items.menu_parent WHERE op_menu_items.id = :id');
            $getMenuName->setFetchMode(PDO::FETCH_ASSOC);
            $getMenuName->execute(array('id' => $v['menu_to']));
            $getMenuName = $getMenuName->fetch();
            $linkArr[] = array($v['id'], $v['menuName'], $this->generateBreadcrumb($getMenuName['menuName'], $v['menu_to']));
        }
        $template->set('links', $linkArr);
        $template->set('opPluginName', get_class($this));
        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opMenu.bridgeIndex.js'));

        return $template;
    }

    public function bridgeRemove() {
        $itemID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $rVal = $this->db->prepare('SELECT * FROM op_menu_bridge WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $itemID));
        $bridgeData = $rVal->fetch();

        $rVal = $this->db->prepare('DELETE FROM op_menu_bridge WHERE id = :id');
        $rVal->execute(array('id' => $itemID));

        $this->rebuildURLs(0, $bridgeData['menu_from']);

        opSystem::Msg(opTranslation::getTranslation('_bridge_removed', get_class($this)), opSystem::SUCCESS_MSG);
        opSystem::redirect('/opMenu/bridgeIndex');
    }

    public function itemNew() {
        $itemType = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/menu.png', opTranslation::getTranslation('_new_item', get_class($this)).' | '.opTranslation::getTranslation('_menus', get_class($this)));
        $aForm->setAction('/admin/opMenu/itemNew/'.$itemType);
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opMenu');

        $tabGroup = new opFormElementTabGroup('tabGroup', 'tabGroup');
        $tabGroup->addTab('generalContent', opTranslation::getTranslation('_general', get_class($this)));
        $tabGroup->addTab('advancedContent', opTranslation::getTranslation('_advanced', get_class($this)));
        $aForm->addElement($tabGroup);

        $tabContent = new opFormElementTabContent('generalContent', 'generalContent');
        $aForm->addElement($tabContent);
        $sBox = new opFormElementSelect('type', opTranslation::getTranslation('_menu_item_type', get_class($this)));
        $sBox->addOption(1, opTranslation::getTranslation('_internal', get_class($this)));
        $sBox->addOption(2, opTranslation::getTranslation('_external', get_class($this)));
        $sBox->addOption(0, opTranslation::getTranslation('_empty', get_class($this)));
        $sBox->setValue($itemType);
        $sBox->addValidator(new opFormValidateNumeric());
        $aForm->addElement($sBox);

        $mBox = new opFormElementMenu('menu_item_parent', opTranslation::getTranslation('_menu_item_parent', get_class($this)));
        $mBox->addValidator(new opFormValidateStringLength(3, 20));
        $aForm->addElement($mBox);

        $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_item_name', get_class($this)), 100);
        $tBox->addValidator(new opFormValidateStringLength(1, 100));
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextbox('hint', opTranslation::getTranslation('_item_title', get_class($this)), 100);
        $aForm->addElement($tBox);

        if ($itemType == 1) {
            $cBox = new opFormElementCheckbox('home', opTranslation::getTranslation('_home', get_class($this)), false);
            $cBox->setValue(1);
            $aForm->addElement($cBox);

            $hBox = new opFormElementTextheader('internal', opTranslation::getTranslation('_internal', get_class($this)));
            $aForm->addElement($hBox);

            $lBox = new opFormElementLayout('layout_id', opTranslation::getTranslation('_layout', get_class($this)));
            $lBox->addValidator(new opFormValidateStringLength(3, 20));
            $aForm->addElement($lBox);
        } else if ($itemType == 2) {
            $hBox = new opFormElementTextheader('external', opTranslation::getTranslation('_external', get_class($this)));
            $aForm->addElement($hBox);

            $tBox = new opFormElementTextbox('url', opTranslation::getTranslation('_url', get_class($this)), 100);
            $tBox->addValidator(new opFormValidateStringLength(5, 100));
            $aForm->addElement($tBox);

            $sBox = new opFormElementSelect('target', opTranslation::getTranslation('_target', get_class($this)));
            $sBox->addOption('_self', opTranslation::getTranslation('_target_self', get_class($this)));
            $sBox->addOption('_blank', opTranslation::getTranslation('_target_blank', get_class($this)));
            $aForm->addElement($sBox);
        }
        $tabContent = new opFormElementTabContentEnd('generalEnd', 'generalEnd');
        $aForm->addElement($tabContent);

        $tabContent = new opFormElementTabContent('advancedContent', 'advancedContent');
        $aForm->addElement($tabContent);

        if ($itemType == 1) {
            $cBox = new opFormElementCheckbox('alias_override', opTranslation::getTranslation('_item_alias_override', get_class($this)), false);
            $cBox->setValue(1);
            $aForm->addElement($cBox);

            $tBox = new opFormElementTextbox('alias', opTranslation::getTranslation('_item_alias', get_class($this)), 100);
            $aForm->addElement($tBox);
        }
        
        $cBox = new opFormElementCheckbox('hide', opTranslation::getTranslation('_item_hide', get_class($this)), false);
        $cBox->setValue(1);
        $aForm->addElement($cBox);

        $tabContent = new opFormElementTabContentEnd('advancedEnd', 'advancedEnd');
        $aForm->addElement($tabContent);

        if (isset($_POST['name'])) {
            $validForm = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($validForm) {
                list($menu, $menuitem) = explode(':', $_POST['menu_item_parent']);
                $rVal = $this->db->prepare('SELECT position FROM op_menu_items WHERE menu_parent = :mid AND parent = :id ORDER BY position DESC LIMIT 0,1');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('mid' => $menu, 'id' => $menuitem));
                $rVal = $rVal->fetch();
                $position = $rVal['position'];
                $hide = (isset($_POST['hide'])) ? 1 : 0;
                switch ($itemType) {
                    case 0:
                        $rVal = $this->db->prepare('INSERT INTO op_menu_items (name, hint, parent, menu_parent, position, hide, created) VALUES (:name, :hint, :parent, :menu_parent, :position, :hide, NOW())');
                        $rVal->execute(array('name' => $_POST['name'], 'hint' => $_POST['hint'], 'parent' => $menuitem, 'menu_parent' => $menu, 'position' => $position+1, 'hide' => $hide));
                        break;
                    case 1:
                        $home        = (isset($_POST['home'])) ? 1 : 0;
                        $itemEnabled = ($home) ? 1 : 0;
                        $itemURL     = (isset($_POST['alias_override']) && $_POST['alias_override'] == 1) ? $_POST['alias'] : $_POST['name'];
                        $itemAlias   = self::buildURL($menuitem, 0, $itemURL, $menu, true);
                        $url         = ($home) ? '/' : self::buildURL($menuitem, 0, $itemURL, $menu);
                        $urlID       = (!$home) ? $this->internalUrlManager->registerURL($url) : 0;
                        list($templateId, $layoutId) = explode(':', $_POST['layout_id']);

                        $rVal = $this->db->prepare('INSERT INTO op_menu_items (name, hint, alias_override, alias, parent, menu_parent, position, type, home, url, layout_id, enabled, hide, created) VALUES (:name, :hint, :alias_override, :alias, :parent, :menu_parent, :position, :type, :home, :url, :layout_id, :enabled, :hide, NOW())');
                        $rVal->execute(array('name' => $_POST['name'], 'hint' => $_POST['hint'], 'alias_override' => (isset($_POST['alias_override']) ? 1 : 0), 'alias' => $itemAlias, 'parent' => $menuitem, 'menu_parent' => $menu, 'position' => $position+1, 'type' => $itemType, 'home' => $home, 'url' => $urlID, 'layout_id' => $layoutId, 'enabled' => $itemEnabled, 'hide' => $hide));

                        if ($home == 1) {
                            $this->clearHome($this->db->lastInsertId());
                        }
                        break;
                    case 2:
                        $urlID = $this->externalUrlManager->registerURL($_POST['url']);
                        $rVal = $this->db->prepare('INSERT INTO op_menu_items (name, hint, parent, menu_parent, position, type, url, target, hide, created) VALUES (:name, :hint, :parent, :menu_parent, :position, :type, :url, :target, :hide, NOW())');
                        $rVal->execute(array('name' => $_POST['name'], 'hint' => $_POST['hint'], 'parent' => $menuitem, 'menu_parent' => $menu, 'position' => $position+1, 'type' => $itemType, 'url' => $urlID, 'target' => $_POST['target'], 'hide' => $hide));
                        break;
                    default:
                        die(opTranslation::getTranslation('_unknown_item_type', get_class($this)));
                }
                opSystem::Msg(opTranslation::getTranslation('_item_added', get_class($this)), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opMenu');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }

        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opMenu.itemNew.js'));

        return $template;
    }

    public function itemEdit() {
        $itemID             = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $itemTypeOverride   = (isset($this->args[1]) && is_numeric($this->args[1])) ? $this->args[1] : false;
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_menu_items WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $itemID));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $itemID));
            $itemData = $rVal->fetch();
            $itemData['originalType'] = $itemData['type'];
            if ($itemTypeOverride !== false) {
                $itemData['type'] = $itemTypeOverride;
            }

            $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/menu.png', opTranslation::getTranslation('_item_edit', get_class($this)).' | '.opTranslation::getTranslation('_menus', get_class($this)));
            if ($itemTypeOverride !== false) {
                $aForm->setAction('/admin/opMenu/itemEdit/'.$itemID.'/'.$itemData['type']);
            } else {
                $aForm->setAction('/admin/opMenu/itemEdit/'.$itemID);
            }
            $aForm->setMethod('post');
            $aForm->setCancelLink('/admin/opMenu');

            $tabGroup = new opFormElementTabGroup('tabGroup', 'tabGroup');
            $tabGroup->addTab('generalContent', opTranslation::getTranslation('_general', get_class($this)));
            $tabGroup->addTab('advancedContent', opTranslation::getTranslation('_advanced', get_class($this)));
            $aForm->addElement($tabGroup);

            $tabContent = new opFormElementTabContent('generalContent', 'generalContent');
            $aForm->addElement($tabContent);
            $hBox = new opFormElementHidden('itemID', 'itemID');
            $hBox->setValue($itemData['id']);
            $aForm->addElement($hBox);

            $sBox = new opFormElementSelect('type', opTranslation::getTranslation('_menu_item_type', get_class($this)));
            $sBox->addOption(1, opTranslation::getTranslation('_internal', get_class($this)));
            $sBox->addOption(2, opTranslation::getTranslation('_external', get_class($this)));
            $sBox->addOption(0, opTranslation::getTranslation('_empty', get_class($this)));
            $sBox->setValue($itemData['type']);
            $sBox->addValidator(new opFormValidateNumeric());
            $aForm->addElement($sBox);

            $mBox = new opFormElementMenu('menu_item_parent', opTranslation::getTranslation('_menu_item_parent', get_class($this)), $itemID);
            $mBox->addValidator(new opFormValidateStringLength(3, 20));
            $mBox->setValue($itemData['menu_parent'].':'.$itemData['parent']);
            $aForm->addElement($mBox);

            $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_item_name', get_class($this)), 100);
            $tBox->addValidator(new opFormValidateStringLength(1, 100));
            $tBox->setValue($itemData['name']);
            $aForm->addElement($tBox);

            $tBox = new opFormElementTextbox('hint', opTranslation::getTranslation('_item_title', get_class($this)), 100);
            $tBox->setValue($itemData['hint']);
            $aForm->addElement($tBox);

            if ($itemData['type'] == 1) {
                $checked = ($itemData['home'] == 1) ? true : false;
                $cBox = new opFormElementCheckbox('home', opTranslation::getTranslation('_home', get_class($this)), $checked);
                $cBox->setValue(1);
                $aForm->addElement($cBox);

                $hBox = new opFormElementTextheader('internal', opTranslation::getTranslation('_layout', get_class($this)));
                $aForm->addElement($hBox);

                $lBox = new opFormElementLayout('layout_id', opTranslation::getTranslation('_layout', get_class($this)));
                $lBox->addValidator(new opFormValidateStringLength(3, 20));
                $lBox->setValue($itemData['layout_id']);
                $aForm->addElement($lBox);
            } else if ($itemData['type'] == 2) {
                $hBox = new opFormElementTextheader('external', opTranslation::getTranslation('_external', get_class($this)));
                $aForm->addElement($hBox);

                $tBox = new opFormElementTextbox('url', opTranslation::getTranslation('_url', get_class($this)), 100);
                $tBox->addValidator(new opFormValidateStringLength(5, 100));
                $tBox->setValue($this->externalUrlManager->getURL($itemData['url']));
                $aForm->addElement($tBox);

                $sBox = new opFormElementSelect('target', opTranslation::getTranslation('_target', get_class($this)));
                $sBox->addOption('_self', opTranslation::getTranslation('_target_self', get_class($this)));
                $sBox->addOption('_blank', opTranslation::getTranslation('_target_blank', get_class($this)));
                $sBox->setValue($itemData['target']);
                $aForm->addElement($sBox);
            }
            $tabContent = new opFormElementTabContentEnd('generalEnd', 'generalEnd');
            $aForm->addElement($tabContent);

            $tabContent = new opFormElementTabContent('advancedContent', 'advancedContent');
            $aForm->addElement($tabContent);

            if ($itemData['type'] == 1) {
                $checked = ($itemData['alias_override'] == 1) ? true : false;
                $cBox = new opFormElementCheckbox('alias_override', opTranslation::getTranslation('_item_alias_override', get_class($this)), $checked);
                $cBox->setValue(1);
                $aForm->addElement($cBox);

                $tBox = new opFormElementTextbox('alias', opTranslation::getTranslation('_item_alias', get_class($this)), 100);
                $tBox->setValue($itemData['alias']);
                $aForm->addElement($tBox);
            }

            $checked = ($itemData['hide'] == 1) ? true : false;
            $cBox = new opFormElementCheckbox('hide', opTranslation::getTranslation('_item_hide', get_class($this)), $checked);
            $cBox->setValue(1);
            $aForm->addElement($cBox);

            $tabContent = new opFormElementTabContentEnd('advancedEnd', 'advancedEnd');
            $aForm->addElement($tabContent);
            
            if (isset($_POST['name'])) {
                $validForm = $aForm->isValid($_POST);
                $template = new opHtmlTemplate($aForm->render());
                if ($validForm) {
                    list($menu, $menuitem) = explode(':', $_POST['menu_item_parent']);
                    $hide = (isset($_POST['hide'])) ? 1 : 0;
                    switch ($_POST['type']) {
                        case 0:
                            if ($itemData['url'] > 0 && $itemData['originalType'] == 1) {
                                $this->internalUrlManager->unregisterURL($itemData['url']);
                            } else if ($itemData['url'] > 0 && $itemData['originalType'] == 2) {
                                $this->externalUrlManager->unregisterURL($itemData['url']);
                            }
                            $rVal = $this->db->prepare('UPDATE op_menu_items SET name = :name, hint = :hint, parent = :parent, menu_parent = :menu_parent, type = :type, target = \'\', layout_id = 0, url = 0, hide = :hide WHERE id = :id');
                            $rVal->execute(array('name' => $_POST['name'], 'hint' => $_POST['hint'], 'parent' => $menuitem, 'menu_parent' => $menu, 'type' => $_POST['type'], 'hide' => $hide, 'id' => $itemID));
                            break;
                        case 1:
                            # Check if name/alias is the same or has changed
                            $linkURL    = (isset($_POST['alias_override'])) ? $_POST['alias'] : $_POST['name'];
                            $linkAlias  = self::buildURL($menuitem, $itemID, $linkURL, $menu, true);
                            if (isset($_POST['alias_override'])) {
                                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_menu_items WHERE id = :id AND alias = :alias');
                                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                                $rVal->execute(array('id' => $itemID, 'alias' => $linkAlias));
                            } else {
                                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_menu_items WHERE id = :id AND name = :name');
                                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                                $rVal->execute(array('id' => $itemID, 'name' => $_POST['name']));
                            }
                            $updateURL = ($rVal->fetchColumn() > 0 && $itemData['originalType'] == 1 && $itemData['parent'] == $menuitem && $itemData['alias_override'] == (isset($_POST['alias_override']) ? 1 : 0)) ? false : true;
                            list($templateId, $layoutId) = explode(':', $_POST['layout_id']);

                            $home = (isset($_POST['home'])) ? 1 : 0;
                            $rVal = $this->db->prepare('UPDATE op_menu_items SET name = :name, hint = :hint, alias_override = :alias_override, parent = :parent, menu_parent = :menu_parent, home = :home, layout_id = :layout_id, type = :type, target = \'\', hide = :hide WHERE id = :id');
                            $rVal->execute(array('name' => $_POST['name'], 'hint' => $_POST['hint'], 'alias_override' => (isset($_POST['alias_override']) ? 1 : 0), 'parent' => $menuitem, 'menu_parent' => $menu, 'home' => $home, 'layout_id' => $layoutId, 'type' => $_POST['type'], 'hide' => $hide, 'id' => $itemID));

                            if ($home == 1) {
                                $this->clearHome($itemID);
                                if ($itemData['url'] > 0) {
                                    $this->internalUrlManager->unregisterURL($itemData['url']);
                                }
                            } else if ($updateURL) {
                                $url    = self::buildURL($menuitem, $itemID, $linkURL, $menu);
                                if (!$this->internalUrlManager->isRegistered($url)) {
                                    if ($urlID = $this->internalUrlManager->registerURL($url)) {
                                        $rVal = $this->db->prepare('UPDATE op_menu_items SET url = :url, alias = :alias WHERE id = :id');
                                        $rVal->execute(array('url' => $urlID, 'alias' => $linkAlias, 'id' => $itemID));

                                        if ($itemData['url'] > 0 && $itemData['originalType'] == 1) {
                                            $this->rc->registerRedirectURL($this->internalUrlManager->getURL($itemData['url']), $url);
                                            $this->internalUrlManager->unregisterURL($itemData['url']);
                                        } else if ($itemData['url'] > 0 && $itemData['originalType'] == 2) {
                                            $this->externalUrlManager->unregisterURL($itemData['url']);
                                        }
                                    } else {
                                        opSystem::Msg('Unable to register URL, please report this bug', opSystem::ERROR_MSG);
                                    }
                                } else {
                                    opSystem::Msg('Unable to update URL, URL already exists, please report this bug', opSystem::ERROR_MSG);
                                }
                            } else if (!$home && $itemData['home'] == 1) {
                                $url = self::buildURL($menuitem, 0, $_POST['name'], $menu);
                                if (!$this->internalUrlManager->isRegistered($url)) {
                                    if ($urlID = $this->internalUrlManager->registerURL($url)) {
                                        $rVal = $this->db->prepare('UPDATE op_menu_items SET url = :url WHERE id = :id');
                                        $rVal->execute(array('url' => $urlID, 'id' => $itemID));
                                    } else {
                                        opSystem::Msg('Unable to register URL, please report this bug', opSystem::ERROR_MSG);
                                    }
                                } else {
                                    opSystem::Msg('Unable to update URL, URL already exists, please report this bug', opSystem::ERROR_MSG);
                                }
                            }
                            break;
                        case 2:
                            if ($itemData['url'] > 0 && $itemTypeOverride === false) {
                                $this->externalUrlManager->unregisterURL($itemData['url']);
                            } else if ($itemData['url'] > 0) {
                                    $this->internalUrlManager->unregisterURL($itemData['url']);
                                }
                            $urlID = $this->externalUrlManager->registerURL($_POST['url']);
                            $rVal = $this->db->prepare('UPDATE op_menu_items SET name = :name, hint = :hint, parent = :parent, menu_parent = :menu_parent, url = :url, target = :target, type = :type, layout_id = 0, hide = :hide WHERE id = :id');
                            $rVal->execute(array('name' => $_POST['name'], 'hint' => $_POST['hint'], 'parent' => $menuitem, 'menu_parent' => $menu, 'url' => $urlID, 'target' => $_POST['target'], 'type' => $_POST['type'], 'hide' => $hide, 'id' => $itemID));
                            break;
                        default:
                            die(opTranslation::getTranslation('_unknown_item_type', get_class($this)));
                    }
                    $this->rebuildURLs($itemID, $menu);

                    # Notify observer that menu changed so that cache can be updated
                    $this->updateLastModified(opPlugin::getIdByName(get_class($this)), $menu);

                    opSystem::Msg(opTranslation::getTranslation('_item_updated', get_class($this)), opSystem::SUCCESS_MSG);
                    opSystem::redirect('/opMenu');
                }
            } else {
                $template = new opHtmlTemplate($aForm->render());
            }

            $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opMenu.itemEdit.js'));

            return $template;
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_item_id', get_class($this)), opSystem::ERROR_MSG);
            opSystem::redirect('/opMenu');
        }
    }

    public function itemDelete() {
        $itemID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_menu_items WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $itemID));
        if ($rVal->fetchColumn() > 0) {
            $this->itemDelete_db($itemID);
            $this->itemDeleteRecursive($itemID);
            
            opSystem::Msg(opTranslation::getTranslation('_item_deleted', get_class($this)), opSystem::SUCCESS_MSG);
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_item_id', get_class($this)), opSystem::ERROR_MSG);
        }
        opSystem::redirect('/opMenu');
    }

    public function itemToggle() {
        $itemID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_menu_items WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $itemID));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $itemID));
            $rVal = $rVal->fetch();
            $toggle = $rVal['enabled'];
            $name = $rVal['name'];
            $menuParent = $rVal['menu_parent'];
            if ($rVal['home'] == 1) {
                opSystem::Msg(sprintf(opTranslation::getTranslation('_item_toggle_home_warn_msg', get_class($this)), $name), opSystem::INFORM_MSG);
            } else if ($toggle == 1) {
                    $rVal = $this->db->prepare('UPDATE op_menu_items SET enabled = 0 WHERE id = :id');
                    $rVal->execute(array('id' => $itemID));
                    opSystem::Msg(sprintf(opTranslation::getTranslation('_item_disabled', get_class($this)), $name), opSystem::SUCCESS_MSG);
                } else {
                    $rVal = $this->db->prepare('UPDATE op_menu_items SET enabled = 1 WHERE id = :id');
                    $rVal->execute(array('id' => $itemID));
                    opSystem::Msg(sprintf(opTranslation::getTranslation('_item_enabled', get_class($this)), $name), opSystem::SUCCESS_MSG);
                }
            # Notify observer that menu changed so that cache can be updated
            $this->updateLastModified(opPlugin::getIdByName(get_class($this)), $menuParent);
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_item_id', get_class($this)), opSystem::ERROR_MSG);
        }
        opSystem::redirect('/opMenu');
    }

    public function ajax() {
        if (isset($this->args[0])) {
            switch ($this->args[0]) {
                case 'getLinkItems':
                    $menuID = (isset($this->args[1]) && is_numeric($this->args[1])) ? $this->args[1] : 0;
                    if ($menuID > 0) {
                        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_menu_items WHERE menu_parent != :id');
                        $rVal->setFetchMode(PDO::FETCH_ASSOC);
                        $rVal->execute(array('id' => $menuID));
                        if ($rVal->fetchColumn() > 0) {
                            $menuInUse = array();
                            $rVal = $this->db->query('SELECT op_menu_items.menu_parent, op_menu_bridge.* FROM op_menu_bridge LEFT JOIN op_menu_items ON op_menu_items.id = op_menu_bridge.menu_to');
                            $rVal->setFetchMode(PDO::FETCH_ASSOC);
                            foreach ($rVal->fetchAll() as $v) {
                                $menuInUse[] = array($v['menu_parent'], $v['menu_from']);
                            }

                            $rVal = $this->db->prepare('SELECT op_menus.name as menuName, op_menu_items.* FROM op_menu_items LEFT JOIN op_menus ON op_menus.id = op_menu_items.menu_parent WHERE op_menu_items.menu_parent != :id ORDER BY op_menu_items.menu_parent ASC, op_menu_items.parent ASC, op_menu_items.position ASC');
                            $rVal->setFetchMode(PDO::FETCH_ASSOC);
                            $rVal->execute(array('id' => $menuID));
                            $tree = $this->generateTree($rVal->fetchAll(), 0, 0, array());
                            $optGroups = array();
                            foreach ($tree as $v) {
                                $add = true;
                                foreach ($menuInUse as $mV) {
                                    if ($mV[0] == $menuID && $mV[1] == $v[0]['menu_parent']) {
                                        $add = false;
                                        break;
                                    }
                                }
                                if ($add) {
                                    $optGroups[$v[0]['menuName']][] = $v;
                                }
                            }
                            if (count($optGroups) > 0) {
                                $html = '';
                                foreach ($optGroups as $k => $v) {
                                    $html .= '<optgroup label="'.$k.'">';
                                    foreach ($v as $item) {
                                        $html .= '<option value="'.$item[0]['id'].'">'.$item[1].$item[0]['name'].'</option>';
                                    }
                                    $html .= '</optgroup>';
                                }
                            } else {
                                $html = '<option value="0">'.opTranslation::getTranslation('_no_possible_connection_exist', get_class($this)).'</option>';
                            }
                            echo $html;
                            exit();
                        } else {
                            echo '<option value="0">'.opTranslation::getTranslation('_no_possible_connection_exist', get_class($this)).'</option>';
                            exit();
                        }
                    } else {
                        echo 'id error';
                        exit();
                    }
                    break;
                default:
                    echo 'unknown method error';
                    exit();
            }
        } else {
            echo 'unset method error';
            exit();
        }
    }

    public function breadcrumbIndex() {
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/sitemap-image.png', opTranslation::getTranslation('_breadcrumb', get_class($this)));
        $aForm->setAction('/admin/opMenu/breadcrumbIndex');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opMenu');

        $tBox = new opFormElementTextbox('separator', opTranslation::getTranslation('_breadcrumb_separator', get_class($this)), 20);
        $tBox->addValidator(new opFormValidateStringLength(1, 20));
        $val = (! opSystem::_get('separator', 'opBreadcrumb')) ? '/' : opSystem::_get('separator', 'opBreadcrumb');
        $tBox->setValue($val);
        $aForm->addElement($tBox);

        if (isset($_POST['separator'])) {
            $isValid  = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($isValid) {
                foreach ($_POST as $key => $value) {
                    opSystem::_set($key, $value, 'opBreadcrumb');
                }
                opSystem::Msg(opTranslation::getTranslation('_breadcrumb_updated', get_class($this)), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opMenu');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }

        return $template;
    }

    public static function install() {
        $sqlImport = new opSQLImport(opSystem::getDatabaseInstance());

        # Import tables
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opMenu.install.sql')) { return false; };

        return true;
    }

    public static function isLayoutAssigned($layoutID) {
        $db = opSystem::getDatabaseInstance();
        $rVal = $db->prepare('SELECT COUNT(*) FROM op_menu_items WHERE layout_id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $layoutID));
        if ($rVal->fetchColumn() > 0) {
            return true;
        } else {
            return false;
        }
    }

    protected function itemDeleteRecursive($itemParent) {
        $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE parent = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $itemParent));
        foreach ($rVal->fetchAll() as $v) {
            $this->itemDeleteRecursive($v['id']);
            $this->itemDelete_db($v['id']);
            # Notify observer that menu changed so that cache can be updated
            $this->updateLastModified(opPlugin::getIdByName(get_class($this)), $v['menu_parent']);
        }
    }

    protected function itemDelete_db($itemID) {
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_menu_items WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $itemID));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $itemID));
            $itemData = $rVal->fetch();

            # Unregister/delete url + redirects
            if ($itemData['url'] > 0 && $itemData['type'] == 1) {
                $this->internalUrlManager->unregisterURL($itemData['url']);
            } else if ($itemData['url'] > 0 && $itemData['type'] == 2) {
                $this->externalUrlManager->unregisterURL($itemData['url']);
            }

            # Clear bridge
            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_menu_bridge WHERE menu_to = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $itemID));
            if ($rVal->fetchColumn() > 0) {
                $rVal = $this->db->prepare('SELECT * FROM op_menu_bridge WHERE menu_to = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $itemID));
                $menuFrom = $rVal->fetch();
                $menuFrom = $menuFrom['menu_from'];

                $rVal = $this->db->prepare('DELETE FROM op_menu_bridge WHERE menu_to = :id');
                $rVal->execute(array('id' => $itemID));

                $this->rebuildURLs(0, $menuFrom);
            }

            $rVal = $this->db->prepare('DELETE FROM op_menu_items WHERE id = :id');
            $rVal->execute(array('id' => $itemID));
            return true;
        }
        return false;
    }

    protected static function buildPageTitle($pageName, $pageParent, $menuParent) {
        $db = opSystem::getDatabaseInstance();
        $pageTitleArr = array();
        $pageTitleArr[] = $pageName;
        if ($pageParent == 0) {
            $pageParent = self::checkForBridge($menuParent);
        }
        while ($pageParent > 0) {
            $rVal = $db->prepare('SELECT * FROM op_menu_items WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $pageParent));
            $rVal = $rVal->fetch();
            $pageParent = $rVal['parent'];
            $pageTitleArr[] = $rVal['name'];

            if ($pageParent == 0) {
                $pageParent = self::checkForBridge($rVal['menu_parent']);
            }
        }
        return array_reverse($pageTitleArr);
    }

    protected static function checkForBridge($menuParent, $reverse = false) {
        $db = opSystem::getDatabaseInstance();
        if ($reverse) {
            $rVal = $db->prepare('SELECT COUNT(*) FROM op_menu_bridge WHERE menu_to = :mt');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('mt' => $menuParent));
            if ($rVal->fetchColumn() > 0) {
                $rVal = $db->prepare('SELECT * FROM op_menu_bridge WHERE menu_to = :mt');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('mt' => $menuParent));
                $rVal = $rVal->fetch();
                return $rVal['menu_from'];
            } else {
                return 0;
            }
        } else {
            $rVal = $db->prepare('SELECT COUNT(*) FROM op_menu_bridge WHERE menu_from = :mf');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('mf' => $menuParent));
            if ($rVal->fetchColumn() > 0) {
                $rVal = $db->prepare('SELECT * FROM op_menu_bridge WHERE menu_from = :mf');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('mf' => $menuParent));
                $rVal = $rVal->fetch();
                return $rVal['menu_to'];
            } else {
                return 0;
            }
        }
    }

    public static function buildURL($linkParent, $linkID, $linkName, $menuParent, $excludeParent = false) {
        $db                  = opSystem::getDatabaseInstance();
        $systemConfiguration = opSystem::getSystemConfiguration();
        $internalUrlManager  = new opMenuURLManager();

        $linkParent = ($linkParent == 0) ? self::checkForBridge($menuParent) : $linkParent;
        $parentPath = '/';
        while ($linkParent > 0) {
            $rVal = $db->prepare('SELECT * FROM op_menu_items WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $linkParent));
            $rVal = $rVal->fetch();
            $linkParent = $rVal['parent'];
            $linkURL = opMenuConvertToUrl::convert($rVal['name']);
            $linkURL = (!$linkURL) ? $rVal['id'] : $linkURL;
            $parentPath = '/'.$linkURL.$parentPath;

            if ($linkParent == 0) {
                $linkParent = self::checkForBridge($rVal['menu_parent']);
            }
        }

        $linkURL = opMenuConvertToUrl::convert($linkName);
        $linkURL = (!$linkURL) ? $linkID : $linkURL;
        $linkURL = ($systemConfiguration->force_url_lowercase) ? strtolower($linkURL) : $linkURL;

        $finalURL = $parentPath.$linkURL.'/';
        $finalURL = ($systemConfiguration->force_url_lowercase) ? strtolower($finalURL) : $finalURL;

        # Prevent duplicate URLs, add numbers to the end of duplicate
        $i = 0;
        if ($internalUrlManager->isRegistered($finalURL)) {
            $rVal = $db->prepare('SELECT * FROM op_menu_items WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $linkID));
            $itemData = $rVal->fetch();
            if ($internalUrlManager->getID($finalURL) != $itemData['url']) {
                for ($i = 2; $i < 1000; $i++) {
                    $finalURL = $parentPath.$linkURL.'-'.$i.'/';
                    if (! $internalUrlManager->isRegistered($finalURL)) {
                        break;
                    }
                }
            }
        }
        if (! $excludeParent) {
            return $finalURL;
        } else {
            if ($i > 0) {
                return $linkURL.'-'.$i;
            } else {
                return $linkURL;
            }
        }
    }

    protected function rebuildURLs($parentID, $menuParent) {
        $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE parent = :parent AND menu_parent = :menu_parent');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('parent' => $parentID, 'menu_parent' => $menuParent));
        foreach ($rVal->fetchAll() as $k => $v) {
            $this->rebuildURLs($v['id'], $menuParent);

            if ($v['type'] == 1) {
                $newURL = self::buildURL($v['parent'], $v['id'], $v['name'], $v['menu_parent']);
                if ($newURL != $this->internalUrlManager->getURL($v['url'])) {
                    $urlID = $this->internalUrlManager->registerURL($newURL);
                    $this->rc->registerRedirectURL($this->internalUrlManager->getURL($v['url']), $newURL);
                    $this->internalUrlManager->unregisterURL($v['url']);

                    $updateLink = $this->db->prepare('UPDATE op_menu_items SET url = :url WHERE id = :id');
                    $updateLink->execute(array('url' => $urlID, 'id' => $v['id']));
                }
            }
        }
        $bridgeID = self::checkForBridge($parentID, true);
        if ($bridgeID > 0) {
            $this->rebuildURLs(0, $bridgeID);
        }
        # Notify observer that document changed so that cache can be updated
        $this->updateLastModified(opPlugin::getIdByName(get_class($this)), $menuParent);
    }

    protected function clearHome($id) {
    # Rebuild url for old home
        $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE home = 1 AND id != :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $id));
        foreach ($rVal->fetchAll() as $v) {
            $url = self::buildURL($v['parent'], $v['id'], $v['name'], $v['menu_parent']);
            $urlID = $this->internalUrlManager->registerURL($url);
            if ($urlID) {
                $linkVal = $this->db->prepare('UPDATE op_menu_items SET url = :url WHERE id = :id');
                $linkVal->execute(array('url' => $urlID, 'id' => $v['id']));
            } else {
                opSystem::Msg('Unable to rebuild URL for old home link, url is probably already registered, this is a bug, report it.', opSystem::ERROR_MSG);
            }
        }

        # Remove home flag for old home
        $rVal = $this->db->prepare('UPDATE op_menu_items SET home = 0 WHERE id != :id');
        $rVal->execute(array('id' => $id));
    }

    protected function generateTree($menuArr, $parent, $spaces, $tree) {
        foreach ($menuArr as $v) {
            if ($v['parent'] != $parent) {
                continue;
            }
            $tree[] = array($v, $this->generateSpaces($spaces));
            foreach ($menuArr as $vC) {
                if ($vC['parent'] == $v['id']) {
                    $this->generateTree($menuArr, $v['id'], $spaces+4, &$tree);
                    break;
                }
            }
        }
        return $tree;
    }

    protected function generateSpaces($n) {
        $spaces = "";
        for ($i = 0; $i <= $n; $i++) {
            $spaces .= "&nbsp;";
        }
        return $spaces;
    }

    protected function generateBreadcrumb($menuName, $linkParent) {
        $parentPath = '';
        while ($linkParent > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $linkParent));
            $rVal = $rVal->fetch();
            $linkParent = $rVal['parent'];
            $parentPath = ' &raquo; '.$rVal['name'].$parentPath;
        }
        return $menuName.$parentPath;
    }

    protected function findActiveLinks() {
        $activeLinks = array();
        $router = opSystem::getRouterInstance();
        $systemConfiguration = opSystem::getSystemConfiguration();
        $urlPieces = $router->getArgs();
        $externalURL = (count($urlPieces) > 0) ? 'http://'.str_ireplace(array('http://', '/'), '', $systemConfiguration->site_url).'/'.$urlPieces[0] : '';
        $url = '/'.implode('/', $urlPieces).'/';
        if ($this->internalUrlManager->isRegistered($url)) {
            $urlID = $this->internalUrlManager->getID($url);

            $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE url = :urlID AND type = 1');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('urlID' => $urlID));
            $itemData = $rVal->fetch();
            $activeLinks[] = $itemData['id'];

            $rVal = $this->db->prepare('SELECT * FROM op_menus WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $itemData['menu_parent']));
            $menuData = $rVal->fetch();
            if ($itemData['parent'] > 0 && $menuData['menu_active_class_parents'] == 1) {
                $itemParent = $itemData['parent'];
                while ($itemParent > 0) {
                    $activeLinks[] = $itemParent;

                    $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE id = :id');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal->execute(array('id' => $itemParent));
                    $rVal = $rVal->fetch();
                    $itemParent = $rVal['parent'];
                }
            }

            $cfb = self::checkForBridge($itemData['menu_parent']);
            while ($cfb > 0) {
                $activeLinks[] = $cfb;

                $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $cfb));
                $bridgeData = $rVal->fetch();
                $cfb = self::checkForBridge($bridgeData['menu_parent']);

                $rVal = $this->db->prepare('SELECT * FROM op_menus WHERE id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $bridgeData['menu_parent']));
                $menuData = $rVal->fetch();
                if ($bridgeData['parent'] > 0 && $menuData['menu_active_class_parents'] == 1) {
                    $itemParent = $bridgeData['parent'];
                    while ($itemParent > 0) {
                        $activeLinks[] = $itemParent;

                        $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE id = :id');
                        $rVal->setFetchMode(PDO::FETCH_ASSOC);
                        $rVal->execute(array('id' => $itemParent));
                        $rVal = $rVal->fetch();
                        $itemParent = $rVal['parent'];
                    }
                }
            }
        } else if ($this->externalUrlManager->isRegistered($externalURL)) {
                $urlID = $this->externalUrlManager->getID($externalURL);
                $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE url = :urlID AND type = 2');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('urlID' => $urlID));
                $itemData = $rVal->fetch();
                $activeLinks[] = $itemData['id'];
            }

        return $activeLinks;
    }

    public static function getBreadcrumb() {
        $router = opSystem::getRouterInstance();
        $url    = implode('/', $router->getArgs());
        $db     = opSystem::getDatabaseInstance();
        $ium    = new opMenuURLManager();
        $uid    = $ium->getID('/'.$url.'/');

        $rVal = $db->prepare('SELECT * FROM op_menu_items WHERE url = :url');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('url' => $uid));
        $menuItemData = $rVal->fetch();
        $menuParent = $menuItemData['parent'];
        $breadcrumb = array(array($menuItemData['name'], '/'.$url.'/'));
        if ($menuParent == 0) {
            $menuParent = self::checkForBridge($menuItemData['menu_parent']);
        }
        while ($menuParent > 0) {
            $rVal = $db->prepare('SELECT * FROM op_menu_items WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $menuParent));
            $rVal = $rVal->fetch();
            $menuParent = $rVal['parent'];
            $breadcrumb[] = array($rVal['name'], ($rVal['url'] == 0) ? false : $ium->getURL($rVal['url']));

            if ($menuParent == 0) {
                $menuParent = self::checkForBridge($rVal['menu_parent']);
            }
        }
        return array_reverse($breadcrumb);
    }

    public static function getSitemap() {
        $db = opSystem::getDatabaseInstance();
        $rVal = $db->query('SELECT * FROM op_menus');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $sArr = array();
        foreach ($rVal->fetchAll() as $menu) {
            if (opLayout::isContentAssigned('opMenu', $menu['id']) && !self::checkForBridge($menu['id'])) {
                $rVal = $db->prepare('SELECT * FROM op_menu_items WHERE menu_parent = :id AND parent = 0 AND enabled = 1 ORDER BY position ASC');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $menu['id']));
                foreach ($rVal->fetchAll() as $itemData) {
                    $sArr[$menu['name']][] = self::buildSitemapTree($itemData);
                }
            }
        }
        return $sArr;
    }

    protected static function buildSitemapTree($itemData) {
        $db = opSystem::getDatabaseInstance();
        $sTree = array();

        # Find childs
        $childArr = array();

        $rVal = $db->prepare('SELECT COUNT(*) FROM op_menu_items WHERE enabled = 1 AND parent = :parent');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('parent' => $itemData['id']));
        $bridgeID = self::checkForBridge($itemData['id'], true);
        $itemCount = $rVal->fetchColumn();
        if ($itemCount > 0 || $bridgeID > 0) {
            if ($itemCount > 0) {
                $rVal = $db->prepare('SELECT * FROM op_menu_items WHERE enabled = 1 AND parent = :parent');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('parent' => $itemData['id']));
                foreach ($rVal->fetchAll() as $v) {
                    $childArr[] = self::buildSitemapTree($v);
                }
            } else if ($bridgeID > 0) {
                $rVal = $db->prepare('SELECT * FROM op_menu_items WHERE menu_parent = :mp AND parent = 0 AND enabled = 1 ORDER BY position ASC');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('mp' => $bridgeID));
                foreach ($rVal->fetchAll() as $v) {
                    $childArr[] = self::buildSitemapTree($v);
                }
            }
        }

        # Add item
        if ($itemData['type'] == 1) {
            $iURLManager = new opMenuURLManager();
            $sTree[$itemData['id']] = array('name'    => $itemData['name'],
                                            'title'   => $itemData['hint'],
                                            'url'     => ((strlen($iURLManager->getURL($itemData['url'])) > 0) ? $iURLManager->getURL($itemData['url']) : '/'),
                                            'target'  => $itemData['target'],
                                            'hide'    => $itemData['hide'],
                                            'childs'  => $childArr);
        } else if ($itemData['type'] == 2) {
            $eURLManager = new opMenuExternalURLManager($db);
            $sTree[$itemData['id']] = array('name'    => $itemData['name'],
                                            'title'   => $itemData['hint'],
                                            'url'     => $eURLManager->getURL($itemData['url']),
                                            'target'  => $itemData['target'],
                                            'hide'    => $itemData['hide'],
                                            'childs'  => $childArr);
        } else {
            $sTree[$itemData['id']] = array('name'    => $itemData['name'],
                                            'title'   => false,
                                            'url'     => false,
                                            'target'  => false,
                                            'hide'    => $itemData['hide'],
                                            'childs'  => $childArr);
        }
        return $sTree;
    }

    protected function recursiveArray($source) {
        $destination = array();
        foreach ($source as $item) {
            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_menu_items WHERE parent = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $item['id']));
            if ($rVal->fetchColumn() > 0) {
                $rVal = $this->db->prepare('SELECT op_menus.name AS menuName, op_menu_items.* FROM op_menu_items LEFT JOIN op_menus ON op_menus.id = op_menu_items.menu_parent WHERE op_menu_items.parent = :id ORDER BY op_menu_items.menu_parent ASC, op_menu_items.parent ASC, op_menu_items.position ASC');
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