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
 ?>
<form id="adminForm" method="post" action="/admin/opMenu/menuDelete">
<h3><?php echo opTranslation::getTranslation('_manage_menus', $opPluginName) ?> | <?php echo opTranslation::getTranslation('_menus', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/menu.png" width="16" height="16" alt="" class="table-icon" /></span>
    <span class="action-right-btns">
        <a href="/admin/opMenu/menuNew" title="<?php echo opTranslation::getTranslation('_new_menu', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/menu-plus.png" class="table-icon" alt="<?php echo opTranslation::getTranslation('_new_menu', $opPluginName) ?>" /> <?php echo opTranslation::getTranslation('_new_menu', $opPluginName) ?></span></a>
        <a href="javascript:$('#adminForm').submit();" title="<?php echo opTranslation::getTranslation('_delete_menus', $opPluginName) ?>" onclick="return confirm('<?php echo opTranslation::getTranslation('_delete_menus_warn_msg', $opPluginName) ?>');"><span><img src="<?php echo $opPluginPath ?>icons/menu-minus.png" class="table-icon" alt="<?php echo opTranslation::getTranslation('_delete_menus', $opPluginName) ?>" /> <?php echo opTranslation::getTranslation('_delete_menus', $opPluginName) ?></span></a>
        <a href="/admin/opMenu/bridgeIndex" title="<?php echo opTranslation::getTranslation('_manage_bridges', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/menu-join.png" class="table-icon" alt="<?php echo opTranslation::getTranslation('_manage_bridges', $opPluginName) ?>" /> <?php echo opTranslation::getTranslation('_manage_bridges', $opPluginName) ?></span></a>
        <a href="/admin/opMenu" title="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/arrow-180-medium.png" class="table-icon" alt="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>" /> <?php echo opTranslation::getTranslation('_back', $opPluginName) ?></span></a>
    </span>
</h3>
<div id="content-plugin">
    <h5 class="list"><?php echo opTranslation::getTranslation('_menulist', $opPluginName) ?></h5>
    <ul id="sortList">
    <?php
    foreach ($menus as $k => $v) {
        echo '<li><span class="sortChk"><input type="checkbox" name="delete[]" value="'.$v['id'].'" /></span><span class="sortTitle"><a href="/admin/opMenu/menuEdit/'.$v['id'].'" title="'.sprintf(opTranslation::getTranslation('_edit_menu_var', $opPluginName), '&quot;'.$v['name'].'&quot;').'">'.$v['name'].'</a></span></li>';
    }
    ?>
    </ul>
</div>
</form>