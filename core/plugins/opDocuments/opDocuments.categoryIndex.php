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
<h3><?php echo opTranslation::getTranslation('_categories', $opPluginName) ?> | <?php echo opTranslation::getTranslation('_documents', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/documents.png" width="16" height="16" alt="" class="table-icon" /></span>
    <span class="action-right-btns">
        <a href="/admin/opDocuments/categoryNew" title="<?php echo opTranslation::getTranslation('_new_category', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/clear-folder--plus.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_new_category', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_new_category', $opPluginName) ?></span></a>
        <a href="javascript:$('#adminForm').submit();" onclick="return confirm('<?php echo opTranslation::getTranslation('_delete_categories_warn_msg', $opPluginName) ?>');" title="<?php echo opTranslation::getTranslation('_delete_categories', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/clear-folders--minus.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_delete_categories', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_delete_categories', $opPluginName) ?></span></a>
        <a href="/admin/opDocuments/categorySort" title="<?php echo opTranslation::getTranslation('_sort_categories', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/sort-alphabet.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_sort_categories', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_sort_categories', $opPluginName) ?></span></a>
        <a href="/admin/opDocuments" title="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/arrow-180-medium.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_back', $opPluginName) ?></span></a>
    </span>
</h3>
<form method="post" action="/admin/opDocuments/categoryDelete" id="adminForm">
<div id="content-plugin">
	<h5 class="list"><?php echo opTranslation::getTranslation('_manage_categories', $opPluginName) ?></h5>
    <ul id="sortList">
    <?php echo $catList; ?>
    </ul>
</div>
</form>