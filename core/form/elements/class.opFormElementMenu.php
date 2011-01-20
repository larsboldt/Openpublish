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
class opFormElementMenu extends opFormElement {
    protected $db;
    protected $itemId;

    public function __construct($name = null, $label = null, $itemId = false) {
        $this->setName($name);
        $this->setLabel($label);
        $this->addClass('form_txt');
        $this->itemId = $itemId;

        $this->db = opSystem::getDatabaseInstance();
    }
  
    public function getHtml() {
        $autoBtn = '<script>$(document).ready(function() { $(\'#opMenuDialog_'.$this->elementName.'\').dialog({modal: true, autoOpen: false, width: 500, height: 400}); });</script><span class="btn-refresh"><a href="#" onclick="$(\'#opMenuDialog_'.$this->elementName.'\').dialog(\'open\');" class="form_btn_input" title="'.opTranslation::getTranslation('_select_menu').'"><span><img src="/themes/opAdmin/images/icons/menu.png" width="16" height="16" border="0" alt="'.opTranslation::getTranslation('_select_menu').'" /></span></a></span>';
        return '<label for="' . $this->elementName . '">' . $this->elementLabel . '</label><span class="input-shadow"><input type="hidden" id="'.$this->elementName.'" name="'.$this->elementName.'" value="' . (($this->sanitizeFormData($this->elementValue) <= 0) ? '' : $this->sanitizeFormData($this->elementValue)) . '" /><input type="text" class="'.implode(' ', $this->elementClass).'" disabled="true" id="fake_' . $this->elementName . '" name="fake_' . $this->elementName . '" value="' . $this->sanitizeFormData($this->getMenuName($this->elementValue)) . '"/></span><div id="opMenuDialog_'.$this->elementName.'" style="display:none;" title="'.opTranslation::getTranslation('_menu_dialog').'">'.$this->getMenuData($this->elementName).'</div>'.$autoBtn;
    }

    protected function getMenuName($id) {
        if (strpos($id, ':') > 0) {
            list($menuParent, $menuItem) = explode(':', $id);
            if ($menuItem == 0) {
                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_menus WHERE id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $menuParent));
                if ($rVal->fetchColumn() > 0) {
                    $rVal = $this->db->prepare('SELECT * FROM op_menus WHERE id = :id');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal->execute(array('id' => $id));
                    $menuData = $rVal->fetch();

                    return $menuData['name'];
                }
            } else {
                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_menu_items WHERE id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $menuItem));
                if ($rVal->fetchColumn() > 0) {
                    $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE id = :id');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal->execute(array('id' => $menuItem));
                    $menuItemData = $rVal->fetch();

                    return $menuItemData['name'];
                }
            }
        }
        return false;
    }

    protected function getMenuData($element) {
        # Menus
        $menus = $this->db->query('SELECT * FROM op_menus ORDER BY name ASC');
        $menus = $menus->fetchAll();

        $i = 0;
        $menuMap = '<ul id="opMenuMap">';
        foreach ($menus as $menu) {
            # Menu items
            $menuitems = $this->db->prepare('SELECT * FROM op_menu_items WHERE menu_parent = :m_parent AND parent = 0 ORDER BY position ASC');
            $menuitems->setFetchMode(PDO::FETCH_ASSOC);
            $menuitems->execute(array('m_parent' => $menu['id']));
            $menuitems = $menuitems->fetchAll();

            $menuMap .= '<li'.((($i+1) == count($menus)) ? ' class="lastChild"' : '' ).'><img src="/themes/opAdmin/images/icons/menu.png" alt="menu" /> <a href="#" onclick="$(\'#'.$element.'\').attr(\'value\', \''.$menu['id'].':0\');$(\'#fake_'.$element.'\').attr(\'value\', \''.$menu['name'].'\');$(\'#opMenuDialog_'.$element.'\').dialog(\'close\');" title="'.$menu['name'].'">'.$menu['name'].'</a>';
            if (count($menuitems) > 0) {
                $menuMap .= '<ul class="hasChildren">';
                $menuMap .= $this->buildMenuList($menu['id'], 0, $element);
                $menuMap .= '</ul>';
            }
            $menuMap .= '</li>';
            $i++;
        }
        $menuMap .= '</ul>';

        return $menuMap;
    }

    protected function buildMenuList($menuparent, $parent, $element) {
        $menuMap = '';

        $parentitems = $this->db->prepare('SELECT COUNT(*) FROM op_menu_items WHERE menu_parent = :mp AND parent = :parent');
        $parentitems->setFetchMode(PDO::FETCH_ASSOC);
        $parentitems->execute(array('mp' => $menuparent, 'parent' => $parent));
        if ($parentitems->fetchColumn() > 0) {
            $parentitems = $this->db->prepare('SELECT * FROM op_menu_items WHERE menu_parent = :mp AND parent = :parent ORDER BY parent ASC, position ASC');
            $parentitems->setFetchMode(PDO::FETCH_ASSOC);
            $parentitems->execute(array('mp' => $menuparent, 'parent' => $parent));
            $parentitems = $parentitems->fetchAll();
            $j = 0;
            foreach ($parentitems as $menuitem) {
                $menuMap .= '<li'.((($j+1) == count($parentitems)) ? ' class="lastChild"' : '' ).'>';
                if ($menuitem['id'] != $this->itemId && !$this->isChildOfEditItem($menuitem['id'])) {
                    $menuMap .= '<a href="#" onclick="$(\'#'.$element.'\').attr(\'value\', \''.$menuparent.':'.$menuitem['id'].'\');$(\'#fake_'.$element.'\').attr(\'value\', \''.$menuitem['name'].'\');$(\'#opMenuDialog_'.$element.'\').dialog(\'close\');" title="'.$menuitem['name'].'">'.$menuitem['name'].'</a>';
                } else {
                    $menuMap .= $menuitem['name'];
                }
                $items = $this->db->prepare('SELECT COUNT(*) FROM op_menu_items WHERE menu_parent = :mp AND parent = :parent');
                $items->setFetchMode(PDO::FETCH_ASSOC);
                $items->execute(array('mp' => $menuparent, 'parent' => $menuitem['id']));
                if ($items->fetchColumn() > 0) {
                    $menuMap .= '<ul class="hasChildren">';
                    $menuMap .= $this->buildMenuList($menuparent, $menuitem['id'], $element);
                    $menuMap .= '</ul>';
                } else {
                    $menuMap .= '</li>';
                }
                $j++;
            }
        }

        return $menuMap;
    }

    protected function isChildOfEditItem($menuItemId) {
        if ($this->itemId !== false) {
            $rVal = $this->db->prepare('SELECT * FROM op_menu_items WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_OBJ);
            $rVal->execute(array('id' => $menuItemId));
            $itemData = $rVal->fetch();
            if ($itemData->parent == $this->itemId) {
                return true;
            } else if ($itemData->parent > 0) {
                if ($this->isChildOfEditItem($itemData->parent)) {
                    return true;
                }
            }
        }

        return false;
    }
}
?>