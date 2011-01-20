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
<h3><?php echo opTranslation::getTranslation('_manage_plugin_txt', $opPluginName) ?> | <?php echo opTranslation::getTranslation('_plugins', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/puzzle.png" width="16" height="16" alt="" class="table-icon" /></span>
</h3>
<div id="content-plugin">
    <h5 class="list-heading"><?php echo opTranslation::getTranslation('_plugin_information', $opPluginName) ?></h5>
    <ul class="pluginData">
        <li><span><?php echo opTranslation::getTranslation('_name', $opPluginName) ?>:</span><?php echo $pName ?></li>
        <li><span><?php echo opTranslation::getTranslation('_description', $opPluginName) ?>:</span><?php echo $pDescription ?></li>
        <li><span><?php echo opTranslation::getTranslation('_version', $opPluginName) ?>:</span><?php echo $pVersion ?></li>
        <li><span><?php echo opTranslation::getTranslation('_author', $opPluginName) ?>:</span><?php echo $pAuthor ?></li>
        <li><span><?php echo opTranslation::getTranslation('_processing_position', $opPluginName) ?>:</span><?php echo $pProcessing ?></li>
        <li><span><?php echo opTranslation::getTranslation('_has_admin', $opPluginName) ?>:</span><?php echo $pHasAdmin ?></li>
        <li><span><?php echo opTranslation::getTranslation('_assign_to_layout', $opPluginName) ?>:</span><?php echo $pAssignToLayout ?></li>
        <li><span><?php echo opTranslation::getTranslation('_category', $opPluginName) ?>:</span><select onchange="window.location='/admin/opPlugin/pluginCategorize/<?php echo $pluginID ?>/' + this.value"><option value="0"><?php echo opTranslation::getTranslation('_others', $opPluginName) ?></option><?php foreach ($pluginCategories as $category) { echo '<option value="'.$category['id'].'"'.(($category['id'] == $pluginCategory) ? ' selected="true"' : '').'>'.$category['name'].'</option>'; } ?></select></li>
    </ul>
    <div id="btn" style="margin-top:20px;"><a class="form_btn" href="/admin/opPlugin/pluginUninstall/<?php echo $pluginID ?>" onclick="return confirm('<?php echo opTranslation::getTranslation('_uninstall_plugin_warn_msg', $opPluginName) ?>');" title="<?php echo opTranslation::getTranslation('_uninstall', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/puzzle--minus.png" width="16" height="16" border="0" alt="<?php echo opTranslation::getTranslation('_uninstall', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_uninstall', $opPluginName) ?></span></a><a class="form_btn" href="/admin/opPlugin" title="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/arrow-180-medium.png" width="16" height="16" border="0" alt="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_back', $opPluginName) ?></span></a></div>
</div>