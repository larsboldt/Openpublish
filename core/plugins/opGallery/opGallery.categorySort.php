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
<h3><?php echo opTranslation::getTranslation('_sort_categories', $opPluginName) ?> | <?php echo opTranslation::getTranslation('_gallery', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/images.png" width="16" height="16" alt="" class="table-icon" /></span>
</h3>
<div id="content-plugin">
    <form id="adminForm" method="post" action="/admin/opGallery/categorySort">
        <div class="opAdminFormItem">
            <label for="parentSelect" id="parentSelectLabel"><?php echo opTranslation::getTranslation('_select_category_to_sort', $opPluginName) ?></label>
            <span class="input-shadow"><select id="parentSelect" onchange="window.location='/admin/opGallery/categorySort/' + this.value"><option value="0">- <?php echo opTranslation::getTranslation('_root', $opPluginName) ?> -</option>
                <?php
                foreach ($parentCategories as $k => $v) {
                    $selected = ($parentSelected == $v['id']) ? ' selected="true"' : '';
                    echo '<option value="'.$v['id'].'"'.$selected.'>'.$v['name'].'</option>';
                }
                ?>
            </select></span>
        </div>
        <h5 class="list"><?php echo opTranslation::getTranslation('_drag_items', $opPluginName) ?></h5>
        <ul id="sortList">
            <?php
            foreach ($childsOfParent as $k => $v) {
                echo '<li id="'.$v['id'].'">
                        <span class="sortGrab"><img src="'.$opPluginPath.'icons/arrow-move.png" id="hnd" style="cursor:move;" /></span>
                        <span class="sortTitle">'.$v['name'].'</span>
                  </li>';
            }
            ?>
        </ul>
        <div id="btn" style="margin-top:20px;"><input type="hidden" id="serialized" name="serialized" value="" /><a class="form_btn" href="#" onclick="$('#serialized').attr('value', $('#sortList').sortable('toArray'));$('#adminForm').submit();" title="<?php echo opTranslation::getTranslation('_save', $opPluginName) ?>"><span><img src="<?php echo $opThemePath ?>images/icons/tick.png" width="16" height="16" border="0" alt="<?php echo opTranslation::getTranslation('_save', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_save', $opPluginName) ?></span></a><a class="form_btn" href="/admin/opGallery/categoryIndex" title="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>"><span><img src="<?php echo $opThemePath ?>images/icons/arrow-180-medium.png" width="16" height="16" border="0" alt="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_back', $opPluginName) ?></span></a></div>
    </form>
</div>