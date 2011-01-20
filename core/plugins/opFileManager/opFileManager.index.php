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
<h3><?php echo opTranslation::getTranslation('_files', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/clear-folder-open-image.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_files', $opPluginName) ?>" class="table-icon" /></span>
    <span class="action-right-btns">
        <a href="/admin/opFileManager/folderIndex" title="<?php echo opTranslation::getTranslation('_folders', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/clear-folders.png" class="table-icon" alt="<?php echo opTranslation::getTranslation('_folders', $opPluginName) ?>" /> <?php echo opTranslation::getTranslation('_folders', $opPluginName) ?></span></a>
        <a href="/admin/opFileManager/fileUpload" title="<?php echo opTranslation::getTranslation('_upload', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/drive-upload.png" class="table-icon" alt="<?php echo opTranslation::getTranslation('_upload', $opPluginName) ?>" /> <?php echo opTranslation::getTranslation('_upload', $opPluginName) ?></span></a>
    </span>
</h3>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table-filemanager">

    <tr>
        <td width="240" valign="top" class="table-data-shadow">
            <div class="filesHead"><?php echo opTranslation::getTranslation('_folders', $opPluginName) ?></div>
        <div id="folderList" class="jCollapse">
            <div class="droppable" id="0" style="padding-left:10px;<?php echo (!isset($folderID) || !$folderID) ? 'font-weight:bold;' : '' ?>"><img src="<?php echo $opPluginPath ?>icons/server.png" class="table-icon" /> <a href="/admin/opFileManager/adminIndexSort/0"><?php echo opTranslation::getTranslation('_fileserver', $opPluginName) ?></a></div>
            <?php echo $opFolders; ?>
        </div>
        </td>
        <td valign="top">
            <table cellpadding="0" cellspacing="0" border="0" width="80%" class="scheme-single">
            <thead>
                <td><?php echo opTranslation::getTranslation('_preview', $opPluginName) ?></td>
                <td><?php echo opTranslation::getTranslation('_file_name', $opPluginName) ?></td>
                <td align="right" width="100"><?php echo opTranslation::getTranslation('_file_size', $opPluginName) ?></td>
                <td align="center" width="100"><?php echo opTranslation::getTranslation('_file_dimensions', $opPluginName) ?></td>
                <td align="center" width="80"><?php echo opTranslation::getTranslation('_file_type', $opPluginName) ?></td>
                <td align="center" width="10%" colspan="5"><?php echo opTranslation::getTranslation('_file_action', $opPluginName) ?></td>
            </thead>
            <tbody>
                <?php
                $i = 0;
                foreach ($opFiles as $file) {
                    $f = opFileFactory::identify(DOCUMENT_ROOT.$file['filepath'].$file['filename']);
                    $css            = ($i % 2) ? 'odd' : 'even';

                    if ($f instanceof opGraphicsFile) {
                        if ($f->getWidth() >= $f->getHeight()) {
                            $previewWidth   = ($f->getWidth() > 640) ? 640 : $f->getWidth();
                            $nP = 640/$f->getWidth();
                            $nH = floor($f->getHeight()*$nP);
                            $previewHeight  = ($f->getWidth() > 640) ? $nH : $f->getHeight();
                        } else {
                            $previewHeight  = ($f->getHeight() > 500) ? 500 : $f->getHeight();
                            $nP = 500/$f->getHeight();
                            $nW = floor($f->getWidth()*$nP);
                            $previewWidth   = ($f->getHeight() > 500) ? $nW : $f->getWidth();
                        }
                    }

                    $fileName       = $f->getFilename();
                    $fileNameLink   = ($f instanceof opGraphicsFile) ? '<a href="javascript:imagePreview(\''.$f->getThumbnail(480).'?cache='.$f->getMTime().'\');" title="'.$f->getFilename().'">'.$fileName.'</a>' : $fileName;
                    $addCrop        = ($f instanceof opGraphicsFile) ? '<a href="/admin/opFileManager/graphicCrop/'.$file['id'].'" title="'.sprintf(opTranslation::getTranslation('_file_crop', $opPluginName), '&quot;'.$fileName.'&quot;').'"><img src="'.$opPluginPath.'icons/image-crop.png" alt="'.sprintf(opTranslation::getTranslation('_file_crop', $opPluginName), '&quot;'.$fileName.'&quot;').'" class="table-icon" /></a>' : '';
                    $addResize      = ($f instanceof opGraphicsFile) ? '<a href="/admin/opFileManager/graphicResize/'.$file['id'].'" title="'.sprintf(opTranslation::getTranslation('_file_resize', $opPluginName), '&quot;'.$fileName.'&quot;').'"><img src="'.$opPluginPath.'icons/image-resize.png" alt="'.sprintf(opTranslation::getTranslation('_file_resize', $opPluginName), '&quot;'.$fileName.'&quot;').'" class="table-icon" /></a>' : '';
                    $addCopy        = ($f instanceof opGraphicsFile) ? '<a href="/admin/opFileManager/graphicCopy/'.$file['id'].'" title="'.sprintf(opTranslation::getTranslation('_file_copy', $opPluginName), '&quot;'.$fileName.'&quot;').'"><img src="'.$opPluginPath.'icons/images--plus.png" alt="'.sprintf(opTranslation::getTranslation('_file_copy', $opPluginName), '&quot;'.$fileName.'&quot;').'" class="table-icon" /></a>' : '';
                    $addDeleteIcon  = ($f instanceof opGraphicsFile) ? 'image--minus.png' : 'document--minus.png';
                    $editIcon       = ($f instanceof opGraphicsFile) ? 'image--pencil.png' : 'document--pencil.png';
                    $addDimensions  = ($f instanceof opGraphicsFile) ? $f->getWidth().' x '.$f->getHeight().'px' : '&nbsp;';
                    $addPreview     = ($f instanceof opGraphicsFile) ? '<a href="javascript:imagePreview(\''.$f->getThumbnail(480).'?cache='.$f->getMTime().'\');" title="'.$f->getFilename().'"><img src="'.$f->getThumbnail(75).'?cache='.$f->getMTime().'" title="'.$fileName.'" alt="'.$fileName.'" class="draggable" id="'.$file['id'].'" /></a>' : '<img src="'.$opPluginPath.'icons/no_preview.jpg" title="'.opTranslation::getTranslation('_no_preview', $opPluginName).'" alt="'.opTranslation::getTranslation('_no_preview', $opPluginName).'" class="draggable" id="'.$file['id'].'" />';
                    echo '<tr class="'.$css.'">';
                    echo '<td valign="middle" align="center" width="75">'.$addPreview.'</td>';
                    echo '<td valign="middle">'.$fileNameLink.'</td>';
                    echo '<td valign="middle" align="right">'.$f->getSizeAsString().'</td>';
                    echo '<td valign="middle" align="center">'.$addDimensions.'</td>';
                    echo '<td valign="middle" align="center">'.$f->getExtension().'</td>';
                    echo '<td valign="middle">'.$addCrop.'</td>';
                    echo '<td valign="middle">'.$addResize.'</td>';
                    echo '<td valign="middle">'.$addCopy.'</td>';
                    echo '<td valign="middle"><a href="/admin/opFileManager/fileEdit/'.$file['id'].'" title="'.sprintf(opTranslation::getTranslation('_file_edit', $opPluginName), '&quot;'.$fileName.'&quot;').'"><img src="'.$opPluginPath.'icons/'.$editIcon.'" alt="'.sprintf(opTranslation::getTranslation('_file_edit', $opPluginName), '&quot;'.$fileName.'&quot;').'" class="table-icon" /></a></td>';
                    echo '<td valign="middle"><a href="/admin/opFileManager/fileDelete/'.$file['id'].'" onclick="return confirm(\''.opTranslation::getTranslation('_delete_file_warn_msg', $opPluginName).'\')" title="'.sprintf(opTranslation::getTranslation('_file_delete', $opPluginName), '&quot;'.$fileName.'&quot;').'"><img src="'.$opPluginPath.'icons/'.$addDeleteIcon.'" alt="'.sprintf(opTranslation::getTranslation('_file_delete', $opPluginName), '&quot;'.$fileName.'&quot;').'" class="table-icon" /></a></td>';
                    echo '</tr>';
                    $i++;
                }
                ?>
            </tbody>
        </table>
        </td>
    </tr>
</table>
<div id="opFileManagerImagePreviewDialog" title="Image preview"></div>