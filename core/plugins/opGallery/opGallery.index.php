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
$activeImage = (isset($_GET['i']) && is_numeric($_GET['i'])) ? $_GET['i'] : 0;
$activeImage = ($activeImage < 0) ? 0 : $activeImage;
$maxCount    = (is_array($activeAlbumImageList)) ? count($activeAlbumImageList) : 0;
$activeImage = ($activeImage > $maxCount-1 && $maxCount-1 >= 0) ? $maxCount-1 : $activeImage;

if (is_array($activeAlbumImageList) && count($activeAlbumImageList) > 0) {
    $imageId = $activeAlbumImageList[$activeImage]->image_id;
    $rVal = $db->prepare('SELECT * FROM op_filemanager_filemap WHERE id = :id');
    $rVal->setFetchMode(PDO::FETCH_OBJ);
    $rVal->execute(array('id' => $imageId));
    $imageData = $rVal->fetch();
}

function recursiveAlbumCategoryRenderer($albumCategories, $parent, $albumList, $activeAlbumId, $opPluginName, $opPluginPath) {
    foreach ($albumCategories as $category) {
        if ($category->parent == $parent) {
            echo '<li class="albumCategory" id="albumCategory_'.$category->id.'"><img src="'.$opPluginPath.'icons/clear-folder-open.png" class="table-icon" /> '.$category->name.'<ul class="albumList">';
            recursiveAlbumCategoryRenderer($albumCategories, $category->id, $albumList, $activeAlbumId, $opPluginName, $opPluginPath);
            foreach ($albumList as $album) {
                if ($album->parent == $category->id) {
                    $css = ($activeAlbumId == $album->id) ? 'class="activeElement"' : '';
                    echo '<li>
                            <input type="checkbox" name="delete[]" value="'.$album->id.'" />
                            <a class="editGallery" href="/admin/opGallery/albumEdit/'.$album->id.'" title="'.opTranslation::getTranslation('_edit_album', $opPluginName).'"><img src="'.$opPluginPath.'icons/image--pencil.png" alt="'.opTranslation::getTranslation('_edit_album', $opPluginName).'" /></a>
                            <a href="/admin/opGallery/albumPictures/'.$album->id.'" title="'.opTranslation::getTranslation('_show_pictures', $opPluginName).'"'.$css.'>'.$album->name.'</a>
                          </li>';
                }
            }
            echo '</ul></li>';
        }
    }
}
?>
<h3><?php echo opTranslation::getTranslation('_gallery', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/images.png" width="16" height="16" alt="" class="table-icon" /></span>
    <span class="action-right-btns">
        <a class="btnnewdoc" href="/admin/opGallery/albumNew" title="<?php echo opTranslation::getTranslation('_new_album', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/image--plus.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_new_album', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_new_album', $opPluginName) ?></span></a>
        <a class="btndeldoc" href="javascript:$('#deleteForm').submit();" onclick="return confirm('<?php echo opTranslation::getTranslation('_delete_albums_warn_msg', $opPluginName) ?>')" title="<?php echo opTranslation::getTranslation('_delete_albums', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/images--minus.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_delete_albums', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_delete_albums', $opPluginName) ?></span></a>
        <a class="btndeldoc" href="/admin/opGallery/categoryIndex" title="<?php echo opTranslation::getTranslation('_categories', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/clear-folders-stack.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_categories', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_categories', $opPluginName) ?></span></a>
        <a class="btndeldoc" href="/admin/opGallery/settings" title="<?php echo opTranslation::getTranslation('_settings', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/gear.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_settings', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_settings', $opPluginName) ?></span></a>
        <a class="btnback" href="/admin/opCreate" title="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/arrow-180-medium.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_back', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_back', $opPluginName) ?></span></a>
    </span>
</h3>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table-filemanager">
    <tr>
        <td width="240" valign="top" class="table-data-shadow">
            <div class="filesHead"><?php echo opTranslation::getTranslation('_albums', $opPluginName) ?></div>
            <form method="post" action="/admin/opGallery/albumDelete" id="deleteForm">
                <ul id="albumCategoryList" class="<?php echo $activeAlbumParent ?>">
                    <li class="albumCategory" id="albumCategory_0"><img src="<?php echo $opPluginPath ?>icons/clear-folder-open.png" class="table-icon" /> <em><?php echo opTranslation::getTranslation('_uncategorized', $opPluginName) ?></em>
                        <ul class="albumList">
                            <?php
                            foreach ($albumList as $album) {
                                if ($album->parent == 0) {
                                    $css = ($activeAlbumId == $album->id) ? 'class="activeElement"' : '';
                                    echo '<li>
                                            <input type="checkbox" name="delete[]" value="'.$album->id.'" />
                                            <a class="editGallery" href="/admin/opGallery/albumEdit/'.$album->id.'" title="'.opTranslation::getTranslation('_edit_album', $opPluginName).'"><img src="'.$opPluginPath.'icons/image--pencil.png" alt="'.opTranslation::getTranslation('_edit_album', $opPluginName).'" /></a>
                                            <a href="/admin/opGallery/albumPictures/'.$album->id.'" title="'.opTranslation::getTranslation('_show_pictures', $opPluginName).'"'.$css.'>'.$album->name.'</a>
                                          </li>';
                                }
                            }
                            ?>
                        </ul>
                    </li>
                    <?php recursiveAlbumCategoryRenderer($albumCategories, 0, $albumList, $activeAlbumId, $opPluginName, $opPluginPath); ?>
                </ul>
            </form>
        </td>
        <td valign="top">
            <table cellpadding="0" cellspacing="0" border="0" class="scheme-single">
                <thead>
                    <tr>
                        <td align="left"><?php echo ($activeAlbumId > 0) ? sprintf(opTranslation::getTranslation('_pictures', $opPluginName), '&quot;'.$activeAlbumName.'&quot;') : opTranslation::getTranslation('_add_album', $opPluginName); ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($activeAlbumId > 0) {
                    ?>
                    <tr>
                        <td align="left">
                            <?php
                            if (is_array($activeAlbumImageList) && count($activeAlbumImageList) > 0) {
                                echo '<div class="opGalleryItemToolbar">';
                                if ($activeImage-1 >= 0) {
                                    echo '<div class="prevArrow"><a href="/admin/opGallery?i='.($activeImage-1).'">'.opTranslation::getTranslation('_previous', $opPluginName).'</a></div>';
                                }
                                echo '<div class="countText">'.sprintf(opTranslation::getTranslation('_picture_n_of_n', $opPluginName), $activeImage+1, $maxCount).'</div>';
                                if ($activeImage+1 <= $maxCount-1) {
                                    echo '<div class="nextArrow"><a href="/admin/opGallery?i='.($activeImage+1).'">'.opTranslation::getTranslation('_next', $opPluginName).'</a></div>';
                                }
                                echo '</div>';
                            } else {
                                echo '';
                            }
                            if (is_array($activeAlbumImageList) && count($activeAlbumImageList) > 0) {
                                $rVal = $db->prepare('SELECT * FROM op_filemanager_filemap WHERE id = :id');
                                $rVal->setFetchMode(PDO::FETCH_OBJ);
                                $rVal->execute(array('id' => $imageId));
                                $imageData = $rVal->fetch();
                                $imageFile = opFileFactory::identify($imageData->filepath.$imageData->filename);
                                $imageResize = $imageFile->getThumbnail(320);
                                echo '<div class="opGalleryItem">';
                                echo '<div><img src="'.$imageResize.'" alt="'.$imageData->filename.'" /></div>';
                                echo '</div>';
                            } else {
                                echo '<div class="inform">';
                                echo '<p><strong>'.opTranslation::getTranslation('_empty_album', $opPluginName).'</strong></p>';
                                echo '<p>'.opTranslation::getTranslation('_add_pictures', $opPluginName).'</p>';
                                echo '</div>';
                            }
                            if (is_array($activeAlbumImageList) && count($activeAlbumImageList) > 0) {
                                echo '<div class="opGalleryItemForm">';
                                echo '<form id="pictureEdit" method="post" action="/admin/opGallery/pictureEdit/'.$activeAlbumImageList[$activeImage]->id.'">';
                                echo '<input type="hidden" name="activeImage" value="'.$activeImage.'" /><ul id="pictureToolbar">';
                                echo '<li><label for="title">'.opTranslation::getTranslation('_title', $opPluginName).'</label><span class="input-shadow"><input class="form_txt" type="text" id="title" name="title" value="'.$activeAlbumImageList[$activeImage]->title.'" /></span></li>';
                                echo '<li><label for="description">'.opTranslation::getTranslation('_description', $opPluginName).'</label><span class="input-shadow"><textarea class="form_txtarea" id="description" name="description">'.$activeAlbumImageList[$activeImage]->description.'</textarea></span></li>';
                                echo '</ul>';
                                echo '<a class="form_btn" href="javascript:$(\'#pictureEdit\').submit();" title="'.opTranslation::getTranslation('_save', $opPluginName).'"><span><img src="'.$opThemePath.'images/icons/tick.png" width="16" height="16" border="0" alt="'.opTranslation::getTranslation('_save', $opPluginName).'" class="table-icon" /> '.opTranslation::getTranslation('_save', $opPluginName).'</span></a>';
                                echo '</form>';
                                echo '</div>';
                            } else {
                                echo '';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="cell">
                            <form id="adminForm" method="post" action="/admin/opGallery/albumModify/<?php echo $activeAlbumId ?>">
                                <?php
                                $element = new opFormElementImage('imageList', opTranslation::getTranslation('_add_picture', $opPluginName));
                                $elementValue = array();
                                if (is_array($activeAlbumImageList)) {
                                    foreach ($activeAlbumImageList as $image) {
                                        $elementValue[] = $image->image_id;
                                    }
                                }
                                $element->setValue($elementValue);
                                echo $element->getHtml();
                                ?>
                                <div id="btn" style="margin-top:20px;"><a class="form_btn" href="javascript:$('#adminForm').submit();" title="<?php echo opTranslation::getTranslation('_save', $opPluginName) ?>"><span><img src="<?php echo $opThemePath ?>images/icons/tick.png" width="16" height="16" border="0" alt="<?php echo opTranslation::getTranslation('_save', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_save', $opPluginName) ?></span></a></div>
                            </form>
                        </td>
                    </tr>
                    <?php
                    } else {
                        echo '<tr><td><div class="inform"><p><strong>'.opTranslation::getTranslation('_add_album', $opPluginName).'</strong></p></div></td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </td>
    </tr>
</table>