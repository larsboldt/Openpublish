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
function loopRecursively($externalURLManager, $opPluginName, $arr, $opPluginPath, $padding = 0) {
    foreach ($arr as $item) {
        $childs = false;
        if (isset($item[1])) {
            $childs = $item[1];
            $item = $item[0];
        }
        # set type
        if ($item['type'] == 1) {
            $type = '<a href="/admindm/'.$item['layout_id'].'" title="'.sprintf(opTranslation::getTranslation('_enter_designmode', $opPluginName), '&quot;'.$item['name'].'&quot;').'"><img src="'.$opPluginPath.'icons/layout-header-footer-3.png" width="16" height="16" alt="'.sprintf(opTranslation::getTranslation('_enter_designmode', $opPluginName), '&quot;'.$item['name'].'&quot;').'" class="table-icon" /></a>';
        } else if ($item['type'] == 2) {
            $type = '<a href="'.$item['url'].'" target="_blank" title="'.sprintf(opTranslation::getTranslation('_go_to', $opPluginName), '&quot;'.$externalURLManager->getURL($item['url']).'&quot;').'"><img src="'.$opPluginPath.'icons/globe.png" width="16" height="16" alt="'.sprintf(opTranslation::getTranslation('_go_to', $opPluginName), '&quot;'.$externalURLManager->getURL($item['url']).'&quot;').'" class="table-icon" /></a>';
        } else {
            $type = '&nbsp';
        }

        # set toggle
        if ($item['home'] == 0) {
            $toggleIcon = ($item['enabled'] == 1) ? 'anchor' : 'anchor-disable';
            $toggleIcon = '<a href="/admin/opMenu/itemToggle/'.$item['id'].'" title="'.opTranslation::getTranslation('_toggle_enable_disable', $opPluginName).'"><img src="'.$opPluginPath.'icons/'.$toggleIcon.'.png" width="16" height="16" alt="'.opTranslation::getTranslation('_toggle_enable_disable', $opPluginName).'" class="table-icon" /></a>';
        } else {
            $toggleIcon = '<img src="'.$opPluginPath.'icons/home.png" width="16" height="16" alt="'.opTranslation::getTranslation('_item_home', $opPluginName).'" title="'.opTranslation::getTranslation('_item_home', $opPluginName).'" class="table-icon" />';
        }
   
        echo '<li>
                <dl>
                    <dt><div class="opAccordionHeader" style="padding-left: '.$padding.'px">'.$toggleIcon.'<a href="/admin/opMenu/itemEdit/'.$item['id'].'" title="'.sprintf(opTranslation::getTranslation('_edit_item', $opPluginName), '&quot;'.$item['name'].'&quot;').'">'.$item['name'].'</a></div></dt>
                    <dd class="listCol1">'.$item['menuName'].'</dd>
                    <dd class="listCol2">'.$type.'</dd>
                    <dd class="listCol3"><a href="/admin/opMenu/itemEdit/'.$item['id'].'" title="'.sprintf(opTranslation::getTranslation('_edit_item', $opPluginName), '&quot;'.$item['name'].'&quot;').'"><img src="'.$opPluginPath.'icons/pencil.png" width="16" height="16" alt="Edit &quot;'.$item['name'].'&quot;" class="table-icon" /></a><a href="/admin/opMenu/itemDelete/'.$item['id'].'" onclick="return confirm(\''.opTranslation::getTranslation('_delete_item_warn_msg', $opPluginName).'\');" title="'.sprintf(opTranslation::getTranslation('_delete_item', $opPluginName), '&quot;'.$item['name'].'&quot;').'"><img src="'.$opPluginPath.'icons/minus-circle.png" width="16" height="16" alt="'.sprintf(opTranslation::getTranslation('_delete_item', $opPluginName), '&quot;'.$item['name'].'&quot;').'" class="table-icon" /></a></dd>
                </dl>';
        if (is_array($childs)) {
            echo '<ul>';
            loopRecursively($externalURLManager, $opPluginName, $childs, $opPluginPath, $padding+20);
            echo '</ul>';
        }
        echo '</li>';
    }
}
 ?>
<h3><?php echo opTranslation::getTranslation('_menus', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/menu.png" width="16" height="16" alt="" class="table-icon" /></span>
    <span class="action-right-btns">
        <a href="/admin/opMenu/itemNew/1" title="<?php echo opTranslation::getTranslation('_new_item', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/menu-plus.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_new_item', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_new_item', $opPluginName) ?></span></a>
        <a href="/admin/opMenu/menuSort/<?php echo $opSort; ?>" title="<?php echo opTranslation::getTranslation('_sort_menu', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/sort-alphabet.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_sort_menu', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_sort_menu', $opPluginName) ?></span></a>
        <a href="/admin/opMenu/menuIndex" title="<?php echo opTranslation::getTranslation('_manage_menus', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/menu.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_manage_menus', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_manage_menus', $opPluginName) ?></span></a>
        <a href="/admin/opMenu/breadcrumbIndex" title="<?php echo opTranslation::getTranslation('_breadcrumb', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/sitemap-image.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_breadcrumb', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_breadcrumb', $opPluginName) ?></span></a>
    </span>
</h3>
<dl class="listHeader">
    <dt><?php echo opTranslation::getTranslation('_item_name', $opPluginName) ?></dt>
    <dd class="listCol1">
        <select name="menufilter" id="menufilter" onchange="sortBy($(this).attr('value'))">
            <option value="0"><?php echo opTranslation::getTranslation('_all_menus', $opPluginName) ?></option>
            <option value="-1">---------------</option>
            <?php
            foreach ($opMenus as $k => $item) {
                $s = ($opSort == $item['id']) ? ' selected="true"' : '';
                echo '<option value="'.$item['id'].'"'.$s.'>'.$item['name'].'</option>';
            }
            ?>
        </select>
    </dd>
    <dd class="listCol2">
        <?php echo opTranslation::getTranslation('_type', $opPluginName) ?>
    </dd>
    <dd class="listCol3"><?php echo opTranslation::getTranslation('_action', $opPluginName) ?></dd>
</dl>
<ul class="listBody">
    <?php
    loopRecursively($externalURLManager, $opPluginName, $menuItems, $opPluginPath);
    ?>
</ul>