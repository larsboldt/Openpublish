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
<h3><?php echo opTranslation::getTranslation('_folders', $opPluginName) ?> | <?php echo opTranslation::getTranslation('_files', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/clear-folder-open-image.png" width="16" height="16" alt="" class="table-icon" /></span>
    <span class="action-right-btns">
        <a href="/admin/opFileManager/folderNew" title="<?php echo opTranslation::getTranslation('_new_folder', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/clear-folder--plus.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_new_folder', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_new_folder', $opPluginName) ?></span></a>
        <a href="javascript:$('#adminForm').submit();" onclick="return confirm('<?php echo opTranslation::getTranslation('_delete_folder_warn_msg', $opPluginName) ?>');" title="<?php echo opTranslation::getTranslation('_delete_folders', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/clear-folders--minus.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_delete_folders', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_delete_folders', $opPluginName) ?></span></a>
        <a href="/admin/opFileManager/folderSort" title="<?php echo opTranslation::getTranslation('_sort_folders', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/clear-folders-stack.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_sort_folders', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_sort_folders', $opPluginName) ?></span></a>
        <a href="/admin/opFileManager" title="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/arrow-180-medium.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_back', $opPluginName) ?></span></a>
    </span>
</h3>
<div id="content-plugin">
    <form method="post" id="adminForm" action="/admin/opFileManager/folderDelete">
    <h5 class="list"><?php echo opTranslation::getTranslation('_manage_folders', $opPluginName) ?></h5>
    <ul id="sortList">
    <?php echo $opFolders; ?>
    </ul>
    </form>
</div>