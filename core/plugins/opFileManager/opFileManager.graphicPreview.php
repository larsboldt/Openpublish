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
<h3><?php echo opTranslation::getTranslation('_preview_image', $opPluginName) ?> | <?php echo opTranslation::getTranslation('_files', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/clear-folder-open-image.png" width="16" height="16" alt="" class="table-icon" /></span>
</h3>
<div id="plug-cols">
    <div id="plug-col-1">
        <div id="imageWrap">
            <img id="cropImage" src="<?php echo $tmpName ?>" />
        </div>
    </div>
    <div id="plug-col-2">
        <div class="sidebar-inner">
            <h5 class="h5-sidebar"><?php echo opTranslation::getTranslation('_image_info', $opPluginName) ?></h5>
            <form id="adminForm" method="post" action="/admin/opFileManager/graphicPreviewSave" style="padding-bottom:40px;">
                <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="75"><p><?php echo opTranslation::getTranslation('_name', $opPluginName) ?>:</p></td>
                        <td><p><span class="input-shadow"><input type="text" class="form_txt_small" name="newFilename" value="<?php echo $oldFile->getFilenameNoExt(); ?>" /></span>.<?php echo $newFile->getExtension(); ?></p></td>
                    </tr>
                    <tr>
                        <td width="75"><p><?php echo opTranslation::getTranslation('_width', $opPluginName) ?>:</p></td>
                        <td><p><?php echo $newFile->getWidth(); ?>px</p></td>
                    </tr>
                    <tr>
                        <td width="75"><p><?php echo opTranslation::getTranslation('_height', $opPluginName) ?>:</p></td>
                        <td><p><?php echo $newFile->getHeight(); ?>px</p></td>
                    </tr>
                    <tr>
                        <td width="75"><p><?php echo opTranslation::getTranslation('_file_size', $opPluginName) ?>:</p></td>
                        <td><p><?php echo $newFile->getSizeAsString(); ?></p></td>
                    </tr>
                </table>
                <input type="hidden" name="oldFile" value="<?php echo $fileID; ?>" />
                <input type="hidden" name="tmpFile" value="<?php echo $newFile->getFilename() ?>" />
                <?php $sender = (! $sender) ? 'browse' : $sender.'/'.$fileID; ?>
                <a href="javascript://" id="form_sbmt" onclick="$('#adminForm').submit()" title="<?php echo opTranslation::getTranslation('_save', $opPluginName) ?>" class="form_btn"><span><img src="<?php echo $opPluginPath ?>icons/image-resize.png" class="table-icon" alt="<?php echo opTranslation::getTranslation('_save', $opPluginName) ?>" /> <?php echo opTranslation::getTranslation('_save', $opPluginName) ?></span></a><a href="/admin/opFileManager/<?php echo $sender; ?>" class="form_btn" title="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/arrow-180-medium.png" class="table-icon" alt="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>" /> <?php echo opTranslation::getTranslation('_back', $opPluginName) ?></span></a>
            </form>
            <p>
                <?php echo opTranslation::getTranslation('_overwrite_warn_msg', $opPluginName) ?>
            </p>
        </div>
    </div>
</div>