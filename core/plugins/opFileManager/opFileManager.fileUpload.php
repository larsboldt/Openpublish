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
<h3><?php echo opTranslation::getTranslation('_upload', $opPluginName) ?> | <?php echo opTranslation::getTranslation('_files', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/clear-folder-open-image.png" width="16" height="16" alt="" class="table-icon" /></span>
</h3>
<div id="content-plugin">
    <form id="adminForm" method="post" action="/admin/opFileManager/fileUpload" enctype="multipart/form-data">
        <h5><?php echo opTranslation::getTranslation('_file_upload', $opPluginName) ?></h5>
        <div style="margin:10px 0">
            <div class="opAdminFormItem">
                <label for="folder"><?php echo opTranslation::getTranslation('_folder', $opPluginName) ?></label>
                <select id="folder" name="folder" class="form_select">
                    <option value="0"><?php echo opTranslation::getTranslation('_fileserver', $opPluginName) ?></option>
                    <?php
                    foreach ($folders as $v) {
                        $s = ($v['id'] == $selectedFolder) ? ' selected="true"' : '';
                        echo '<option value="'.$v['id'].'"'.$s.'>'.$v['name'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="opAdminFormItem">
                <div id="uploadFields">
                    <div class="opAdminFormItem"><input type="file" id="file1" name="file1" size="30" /></div>
                </div>
            </div>
            <div id="uploadMsg" align="left">&nbsp;&nbsp;<?php echo opTranslation::getTranslation('_please_wait', $opPluginName) ?><br /><img src="<?php echo $opPluginPath ?>images/ajax-loader.gif" /></div>
        </div>       
        <div id="tagArea" class="clearfix">
        	<h6><?php echo opTranslation::getTranslation('_file_tags', $opPluginName) ?></h6>
            <ul id="tagList"></ul>
          <div class="tagIt">
            <div id="tagControl">
                <span class="tagShadow"><input type="text" id="tagText" maxlength="20" /></span><a class="tagBtn" href="javascript:addTag()" title="<?php echo opTranslation::getTranslation('_add_tag', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/tag.png" width="16" height="16" border="0" alt="<?php echo opTranslation::getTranslation('_add_tag', $opPluginName) ?>" class="btn-icon" /> </span></a>
            </div>
            <div id="tagControlMsg"><?php echo opTranslation::getTranslation('_tag_files_for_later_use', $opPluginName) ?></div>
          </div>
        </div>
        <div id="btn">
            <a class="form_btn" href="javascript:startUpload()" title="<?php echo opTranslation::getTranslation('_upload', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/drive-upload.png" width="16" height="16" border="0" alt="<?php echo opTranslation::getTranslation('_upload', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_upload', $opPluginName) ?></span></a>
            <a class="form_btn" href="javascript:addUpload()" title="<?php echo opTranslation::getTranslation('_add_file', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/drive--plus.png" width="16" height="16" border="0" alt="<?php echo opTranslation::getTranslation('_add_file', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_add_file', $opPluginName) ?></span></a>
            <a class="form_btn" href="/admin/opFileManager" title="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/arrow-180-medium.png" width="16" height="16" border="0" alt="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_back', $opPluginName) ?></span></a>
        </div>
    </form>
</div>