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
class opFormElementImage extends opFormElement {
    public function __construct($name = null, $label = null) {
        $this->setName($name);
        $this->setLabel($label);
        $this->addClass('form_txt');
    }

    public function getValue() {
        if (is_array($this->elementValue)) {
            return implode(',',$this->elementValue);
        } else {
            return '';
        }
    }
  
    public function getHtml() {
        $db = opSystem::getDatabaseInstance();
        $images = '<li style="display:none;">&nbsp;</li>';
        if (is_array($this->elementValue)) {
            $rVal = $db->prepare('SELECT * FROM op_filemanager_filemap WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_OBJ);
            foreach ($this->elementValue as $imageId) {
                $rVal->execute(array('id' => $imageId));
                $imageData = $rVal->fetch();
                $imageFile = opFileFactory::identify($imageData->filepath.$imageData->filename);
                $imageResize = $imageFile->getThumbnail(100);
                $images .= '<li id="opImageItem_'.$this->elementName.'_'.$imageId.'"><div class="opImageDialogImageWrap"><input type="hidden" name="'.$this->elementName.'[]" value="'.$imageId.'" /><img class="opProdImageThumb" src="'.$imageResize.'"></div><div class="opProdBottomBar"><a href="javascript:removeImage_'.$this->elementName.'('.$imageId.');">x</a></div></li>';
            }
        }
        $autoBtn = '<script>
                    var imageList_'.$this->elementName.' = new Array('.$this->getValue().');
                    function removeImage_'.$this->elementName.'(id) {
                        for(var i = 0; i < imageList_'.$this->elementName.'.length; i++) {
                            if (imageList_'.$this->elementName.'[i] == id) {
                                delete imageList_'.$this->elementName.'[i];
                            }
                        }
                        $(\'#opImageItem_'.$this->elementName.'_\'+id).remove();
                    }
                    function opImageDialogAddImage_'.$this->elementName.'(id) {
                        for(var i = 0; i < imageList_'.$this->elementName.'.length; i++) {
                            if (imageList_'.$this->elementName.'[i] == id) {
                                $("#opImageDialog_'.$this->elementName.'").dialog("close");
                                return false;
                            }
                        }
                        imageList_'.$this->elementName.'[imageList_'.$this->elementName.'.length] = id;
                        $("#opImageDialogImageList_'.$this->elementName.'").append(\'<li id="opImageItem_'.$this->elementName.'_\' + id + \'"><div class="opImageDialogImageWrap"><input type="hidden" name="'.$this->elementName.'[]" value="\' + id + \'" /><img class="opProdImageThumb" id="thumbId_\' + id + \'" src="" /></div><div class="opProdBottomBar"><a href="javascript:removeImage_'.$this->elementName.'(\' + id + \');">x</a></div></li>\');
                        $.get("/admin/tools/getImageThumbnail/100/" + id, function(data) {
                            $(\'#thumbId_\'+id).attr(\'src\', data);
                        });
                        $("#opImageDialog_'.$this->elementName.'").dialog("close");
                        $("#opImageDialogImageList_'.$this->elementName.'").sortable("refresh");
                    };
                    $(document).ready(function() {
                        $("#opImageDialog_'.$this->elementName.'").dialog({modal: true, autoOpen: false, width: 800, resizable: false});
                        $("#opImageDialogImageList_'.$this->elementName.'").sortable({containment: "#opImageDialogImageList_'.$this->elementName.'", placeholder: "ui-state-highlight", tolerance: \'pointer\'});
                        $("#opImageDialogImageList_'.$this->elementName.'").disableSelection();
                    });
                    </script>
                    <span class="btn-refresh">
                        <a href="#" onclick="$(\'#opImageDialog_'.$this->elementName.'\').dialog(\'open\');" class="form_btn_input" title="'.opTranslation::getTranslation('_select_image').'">
                            <span>
                                <img src="/themes/opAdmin/images/icons/images-stack.png" width="16" height="16" border="0" alt="'.opTranslation::getTranslation('_select_image').'" />
                            </span>
                        </a>
                    </span>';
        return '<label for="' . $this->elementName . '">' . $this->elementLabel . '</label><span class="input-shadow"><input type="text" class="'.implode(' ', $this->elementClass).'" disabled="true" id="fake_' . $this->elementName . '" name="fake_' . $this->elementName . '" /></span><div id="opImageDialog_'.$this->elementName.'" style="display:none;" title="'.opTranslation::getTranslation('_image_dialog').'"><iframe src="/admin/simplebrowser/configure/image/true/'.$this->elementName.'" width="100%" height="400" frameborder="0" border="0"></iframe></div>'.$autoBtn.'<ul id="opImageDialogImageList_'.$this->elementName.'" class="opImageDialogImageList">'.$images.'</ul>';
    }
}
?>