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
<h3><?php echo opTranslation::getTranslation('_resize_image', $opPluginName) ?> | <?php echo opTranslation::getTranslation('_files', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/clear-folder-open-image.png" width="16" height="16" alt="" class="table-icon" /></span>
</h3>
<div id="plug-cols">
    <div id="plug-col-1">
        <div id="imageWrap">
            <img id="cropImage" src="<?php echo $file->getRelativePath().$file->getFilename(); ?>?cache=<?php echo $file->getMTime(); ?>" />
        </div>
    </div>
    <div id="plug-col-2">
        <div class="sidebar-inner">
            <h5 class="h5-sidebar"><?php echo opTranslation::getTranslation('_image_info', $opPluginName) ?></h5>
            <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td width="75"><p><?php echo opTranslation::getTranslation('_name', $opPluginName) ?>:</p></td>
                    <td><p><?php echo $file->getFilename(); ?></p></td>
                </tr>
                <tr>
                    <td width="75"><p><?php echo opTranslation::getTranslation('_width', $opPluginName) ?>:</p></td>
                    <td><p><span class="input-shadow"><input type="text" class="form_txt_small" id="imageWidth" name="imageWidth" size="4" style="text-align:right" value="<?php echo $file->getWidth(); ?>" /></span>px</p></td>
                </tr>
                <tr>
                    <td width="75"><p><?php echo opTranslation::getTranslation('_height', $opPluginName) ?>:</p></td>
                    <td><p><span class="input-shadow"><input type="text" class="form_txt_small" id="imageHeight" name="imageHeight" size="4" style="text-align:right" value="<?php echo $file->getHeight(); ?>" /></span>px</p></td>
                </tr>
                <tr>
                    <td colspan="2"><p><input type="checkbox" id="aspectRatio" name="aspectRatio" value="1" checked="true" /> <?php echo opTranslation::getTranslation('_maintain_aspect_ratio', $opPluginName) ?></p></td>
                </tr>
            </table>
            <?php
            if ($file instanceof opJPGFile) {
                echo '<p>'.opTranslation::getTranslation('_jpg_quality', $opPluginName).': <span id="jpg_quality">75</span>%<br /><div style="margin-bottom:10px;" id="jpg_quality_slider"></div></p>';
            }
            ?>
            <form id="adminForm" method="post" action="/admin/opFileManager/graphicResize/<?php echo $fileID ?>" style="padding-bottom:40px;">
                <input type="hidden" id="originalWidth" name="originalWidth" value="<?php echo $file->getWidth(); ?>" />
                <input type="hidden" id="originalHeight" name="originalHeight" value="<?php echo $file->getHeight(); ?>" />
                <input type="hidden" id="newWidth" name="newWidth" value="<?php echo $file->getWidth(); ?>" />
                <input type="hidden" id="newHeight" name="newHeight" value="<?php echo $file->getHeight(); ?>" />
                <input type="hidden" id="quality" name="quality" value="75" />
                <a href="javascript://" id="form_sbmt" class="form_btn" title="<?php echo opTranslation::getTranslation('_resize', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/image-resize.png" class="table-icon" alt="<?php echo opTranslation::getTranslation('_resize', $opPluginName) ?>" /> <?php echo opTranslation::getTranslation('_resize', $opPluginName) ?></span></a><a href="/admin/opFileManager" class="form_btn" title="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/arrow-180-medium.png" class="table-icon" alt="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>" /> <?php echo opTranslation::getTranslation('_back', $opPluginName) ?></span></a>
            </form>
        </div>
    </div>
</div>