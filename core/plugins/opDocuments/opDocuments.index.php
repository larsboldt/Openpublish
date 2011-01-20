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
<h3><?php echo opTranslation::getTranslation('_documents', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/documents.png" width="16" height="16" alt="" class="table-icon" /></span>
    <span class="action-right-btns">
        <a class="btnnewdoc" href="/admin/opDocuments/documentQuickPublish" title="<?php echo opTranslation::getTranslation('_quickpublish', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/document-export.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_quickpublish', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_quickpublish', $opPluginName) ?></span></a>
        <a class="btnnewdoc" href="/admin/opDocuments/documentNew" title="<?php echo opTranslation::getTranslation('_new_document', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/document--plus.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_new_document', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_new_document', $opPluginName) ?></span></a>
        <a class="btndeldoc" href="javascript:$('#adminForm').submit();" onclick="return confirm('<?php echo opTranslation::getTranslation('_delete_documents_warn_msg', $opPluginName) ?>');" title="<?php echo opTranslation::getTranslation('_delete_documents', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/documents--minus.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_delete_documents', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_delete_documents', $opPluginName) ?></span></a>
        <a class="btncat" href="/admin/opDocuments/categoryIndex" title="<?php echo opTranslation::getTranslation('_categories', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/clear-folders.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_categories', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_categories', $opPluginName) ?></span></a>
        <a class="btnback" href="/admin/opCreate" title="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/arrow-180-medium.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_back', $opPluginName) ?></span></a>
    </span>
</h3>
<form method="post" action="/admin/opDocuments/documentDelete" id="adminForm">
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table-filemanager">
    <tr>
        <td width="240" valign="top" class="table-data-shadow">
            <div class="filesHead"><?php echo opTranslation::getTranslation('_categories', $opPluginName) ?></div>
            <div id="catList" class="jCollapse">
                <?php echo $opCategories; ?>
            </div>
        </td>
        <td valign="top">
            <table cellpadding="0" cellspacing="0" border="0" class="scheme-single">
                <thead>
                    <tr>
                        <td width="16">&nbsp;</td>
                        <td width="16">&nbsp;</td>
                        <td><?php echo opTranslation::getTranslation('_title', $opPluginName) ?></td>
                        <td width="70" align="center"><?php echo opTranslation::getTranslation('_assigned', $opPluginName) ?></td>
                        <td colspan="3" align="center"><?php echo opTranslation::getTranslation('_action', $opPluginName) ?></td>
                    </tr>
                </thead>
                <tbody>
                <?php
                $i = 0;
                foreach ($opDocuments as $k => $v) {
                    $documentName = htmlentities($v[0]['name'], ENT_QUOTES, 'UTF-8');
                    $css                = ($i % 2) ? 'odd' : 'even';
                    $delete             = (! $v[1]) ? '<a href="/admin/opDocuments/documentDelete/'.$v[0]['id'].'" onclick="return confirm(\''.opTranslation::getTranslation('_delete_document_warn_msg', $opPluginName).'\');" title="'.sprintf(opTranslation::getTranslation('_delete_document', $opPluginName), '&quot;'.$documentName.'&quot;').'"><img src="'.$opPluginPath.'icons/document--minus.png" width="16" height="16" alt="'.sprintf(opTranslation::getTranslation('_delete_document', $opPluginName), '&quot;'.$documentName.'&quot;').'" /></a>' : '<img src="'.$opPluginPath.'icons/document--minus--disable.png" width="16" height="16" alt="'.opTranslation::getTranslation('_cannot_delete_assigned_documents', $opPluginName).'" title="'.opTranslation::getTranslation('_cannot_delete_assigned_documents', $opPluginName).'" />';
                    $checkboxDisabled   = ($v[1]) ? ' disabled="true"' : '';
                    $assigned           = ($v[1]) ? '<img src="'.$opPluginPath.'icons/tick.png" width="16" height="16" />' : '&nbsp';
                    echo '<tr class="'.$css.'">
                            <td width="16"><img id="'.$v[0]['id'].'" class="opera-icon-fix draggable" src="'.$opPluginPath.'icons/document--move.png" /></td>
                            <td width="16"><input type="checkbox" name="'.$v[0]['id'].'" value="1" class="checkbox"'.$checkboxDisabled.' /></td>
                            <td><a href="/admin/opDocuments/documentEdit/'.$v[0]['id'].'" title="'.sprintf(opTranslation::getTranslation('_edit_document', $opPluginName), '&quot;'.$documentName.'&quot;').'">'.$documentName.'</a></td>
                            <td width="70" align="center">'.$assigned.'</td>
                            <td width="16"><a href="/admin/opDocuments/documentEdit/'.$v[0]['id'].'" title="'.sprintf(opTranslation::getTranslation('_edit_document', $opPluginName), '&quot;'.$documentName.'&quot;').'"><img src="'.$opPluginPath.'icons/document--pencil.png" width="16" height="16" alt="'.sprintf(opTranslation::getTranslation('_edit_document', $opPluginName), '&quot;'.$documentName.'&quot;').'" /></a></td>
                            <td width="16"><a href="/admin/opDocuments/documentCopy/'.$v[0]['id'].'" title="'.sprintf(opTranslation::getTranslation('_copy_document', $opPluginName), '&quot;'.$documentName.'&quot;').'"><img src="'.$opPluginPath.'icons/document-copy.png" width="16" height="16" alt="'.sprintf(opTranslation::getTranslation('_copy_document', $opPluginName), '&quot;'.$documentName.'&quot;').'" /></a></td>
                            <td width="16">'.$delete.'</td>
                          </tr>';
                    $i++;
                }
                ?>
                </tbody>
            </table>
        </td>
    </tr>
</table>
</form>