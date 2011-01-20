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
<h3><?php echo opTranslation::getTranslation('_manage_bridges', $opPluginName) ?> | <?php echo opTranslation::getTranslation('_menus', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/menu-join.png" width="16" height="16" alt="" title="" class="table-icon" /></span>
    <span class="action-right-btns">
        <a class="btnback" href="/admin/opMenu/menuIndex" title="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/arrow-180-medium.png" width="16" height="16" border="0" alt="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_back', $opPluginName) ?></span></a>
    </span>
</h3>
<div id="content-plugin">
    <form id="adminForm" method="post" action="/admin/opMenu/bridgeIndex">
        <div class="opAdminFormItem">
            <label><?php echo opTranslation::getTranslation('_join', $opPluginName) ?></label>
            <select class="form_select" id="from" name="from"><option value="0"><?php echo opTranslation::getTranslation('_select_menu', $opPluginName) ?></option>
            <?php
            foreach ($menu as $v) {
                echo '<option value="'.$v['id'].'">'.$v['name'].'</option>';
            }
            ?>
            </select>
        </div>
    <div class="opAdminFormItem">
        <label><?php echo opTranslation::getTranslation('_with', $opPluginName) ?></label>
        <select class="form_select" id="to" name="to">

        </select>
    </div>
    <div class="btns listBelow"><a href="#" class="form_btn" onclick="$('#adminForm').submit();" title="<?php echo opTranslation::getTranslation('_make_bridge', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/arrow-join.png" width="16" height="16" border="0" alt="<?php echo opTranslation::getTranslation('_make_bridge', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_make_bridge', $opPluginName) ?></span></a></div>
      
    <h5 class="list"><?php echo opTranslation::getTranslation('_existing_bridges', $opPluginName) ?></h5>
    <ul id="sortList">
    <?php
    foreach ($links as $v) {
        echo '<li><span class="sortChk"><a href="/admin/opMenu/bridgeRemove/'.$v[0].'" title="'.opTranslation::getTranslation('_remove_bridge', $opPluginName).'"><img src="'.$opPluginPath.'icons/minus-circle.png" width="16" height="16" border="0" alt="'.opTranslation::getTranslation('_remove_bridge', $opPluginName).'" class="list-icon" /></a></span><span class="sortTitle">'.$v[2].' <span class="sortCol sortNoCol"><img src="'.$opPluginPath.'icons/arrow-join.png" width="16" height="16" border="0" alt="" class="table-icon" /></span> '.$v[1].'</span></li>';
    }
    ?>
    </ul>
    
    </form>
</div>