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
class opGallery extends opPluginBase {
    protected $albumMapper, $categoryMapper;
    
    protected function initialize() {
        $this->albumMapper = new opFormDataMapper($this->db);
        $this->albumMapper->setTable('op_gallery');
        $this->albumMapper->setFieldIDName('id');
        $this->albumMapper->addElementTypeToSkip(new opFormElementTabGroup(null, null));
        $this->albumMapper->addElementTypeToSkip(new opFormElementTabContent(null, null));
        $this->albumMapper->addElementTypeToSkip(new opFormElementTabContentEnd(null, null));
        $this->albumMapper->addElementTypeToSkip(new opFormElementTextheader(null, null));

        $this->categoryMapper = new opFormDataMapper($this->db);
        $this->categoryMapper->setTable('op_gallery_categories');
        $this->categoryMapper->setFieldIDName('id');
    }

    public function adminIndex() {
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opGallery.index.php');
        $template->set('opThemePath', $this->theme->getThemePath());
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('opPluginName', __CLASS__);
        $template->set('db', $this->db);
        $rVal = $this->db->query('SELECT * FROM op_gallery ORDER BY name ASC');
        $rVal->setFetchMode(PDO::FETCH_OBJ);
        $template->set('albumList', $rVal->fetchAll());
        $template->set('albumPictures', array());
        $rVal = $this->db->query('SELECT * FROM op_gallery_categories ORDER BY parent ASC, position ASC');
        $rVal->setFetchMode(PDO::FETCH_OBJ);
        $template->set('albumCategories', $rVal->fetchAll());

        $activeAlbumId = (isset($_SESSION['opGallery_active_album'])) ? $_SESSION['opGallery_active_album'] : false;
        $this->albumMapper->setRowID($activeAlbumId);
        $albumData = $this->albumMapper->fetchRow();
        if ($albumData !== false) {
            $template->set('activeAlbumParent', $albumData->parent);
            $template->set('activeAlbumId', $activeAlbumId);
            $template->set('activeAlbumName', $albumData->name);
        } else {
            $rVal = $this->db->query('SELECT COUNT(*) FROM op_gallery');
            if ($rVal->fetchColumn() > 0) {
                $rVal = $this->db->query('SELECT * FROM op_gallery ORDER BY name ASC');
                $rVal->setFetchMode(PDO::FETCH_OBJ);
                $albumData = $rVal->fetch();
                $activeAlbumId = $albumData->id;
                $template->set('activeAlbumParent', $albumData->parent);
                $template->set('activeAlbumId', $albumData->id);
                $template->set('activeAlbumName', $albumData->name);
            } else {
                $template->set('activeAlbumParent', 0);
                $template->set('activeAlbumId', false);
                $template->set('activeAlbumName', false);
            }
        }

        if ($activeAlbumId !== false) {
            $rVal = $this->db->prepare('SELECT * FROM op_gallery_pictures WHERE parent = :parent ORDER BY position ASC');
            $rVal->setFetchMode(PDO::FETCH_OBJ);
            $rVal->execute(array('parent' => $activeAlbumId));
            $template->set('activeAlbumImageList', $rVal->fetchAll());
        } else {
            $template->set('activeAlbumImageList', false);
        }

        $this->theme->addCSS(new opCSSFile(self::getRelativePath(__CLASS__).'css/opGallery.index.css'));
        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opGallery.index.js'));
        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/categoryCollapse.js'));

        return $template;
    }

    public function albumPictures() {
        $albumId = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $this->albumMapper->setRowID($albumId);
        $albumData = $this->albumMapper->fetchRow();
        if ($albumData !== false) {
            $_SESSION['opGallery_active_album'] = $albumId;
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_album_id', __CLASS__), opSystem::ERROR_MSG);
        }
        opSystem::redirect('/opGallery');
    }

    public function albumNew() {
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/image--plus.png', opTranslation::getTranslation('_new_album', __CLASS__));
        $aForm->setAction('/admin/opGallery/albumNew');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opGallery');

        $element = new opFormElementTabGroup('tabGroup', 'tabGroup');
        $element->addTab('generalContent', opTranslation::getTranslation('_general', __CLASS__));
        $element->addTab('advancedContent', opTranslation::getTranslation('_advanced', __CLASS__));
        $aForm->addElement($element);

        $aForm->addElement(new opFormElementTabContent('generalContent', 'generalContent'));
        $sBox = new opFormElementSelect('parent', opTranslation::getTranslation('_album_parent', get_class($this)));
        $sBox->addOption(0, opTranslation::getTranslation('_none', get_class($this)));
        $rVal = $this->db->query('SELECT * FROM op_gallery_categories ORDER BY parent ASC, name ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($this->orderRecursive(0, $rVal->fetchAll(), 0, 0) as $v) {
            $sBox->addOption($v['id'], $v['name']);
        }
        $sBox->addValidator(new opFormValidateNumeric());
        $aForm->addElement($sBox);
        
        $element = new opFormElementTextbox('name', opTranslation::getTranslation('_album_name', __CLASS__), 100);
        $element->addValidator(new opFormValidateStringLength(2, 100));
        $aForm->addElement($element);
        $aForm->addElement(new opFormElementTabContentEnd('generalEnd', 'generalEnd'));

        $aForm->addElement(new opFormElementTabContent('advancedContent', 'advancedContent'));
        $aForm->addElement(new opFormElementTextheader('override', opTranslation::getTranslation('_override_default_settings', __CLASS__)));

        $element = new opFormElementTextbox('thumb_size', opTranslation::getTranslation('_thumb_size', __CLASS__), 3);
        $aForm->addElement($element);

        $element = new opFormElementTextbox('image_size', opTranslation::getTranslation('_image_size', __CLASS__), 4);
        $aForm->addElement($element);

        $element = new opFormElementCodebox('image_template', opTranslation::getTranslation('_image_template', __CLASS__));
        $element->setSanitize(false);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_original_image', __CLASS__));
        $btn->setCode('{originalImage}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_resized_image', __CLASS__));
        $btn->setCode('{resizedImage}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-resize.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_title', __CLASS__));
        $btn->setCode('{imageTitle}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_description', __CLASS__));
        $btn->setCode('{imageDescription}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit-image.png');
        $element->addBtn($btn);
        $aForm->addElement($element);

        $element = new opFormElementCodebox('thumb_template', opTranslation::getTranslation('_thumb_template', __CLASS__));
        $element->setSanitize(false);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_original_image', __CLASS__));
        $btn->setCode('{originalImage}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_thumb_image', __CLASS__));
        $btn->setCode('{thumbImage}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-small.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_title', __CLASS__));
        $btn->setCode('{imageTitle}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_description', __CLASS__));
        $btn->setCode('{imageDescription}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit-image.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_link', __CLASS__));
        $btn->setCode('{imageLink}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/chain.png');
        $element->addBtn($btn);
        $aForm->addElement($element);

        $element = new opFormElementCodebox('album_template', opTranslation::getTranslation('_album_template', __CLASS__));
        $element->setSanitize(false);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image', __CLASS__));
        $btn->setCode('{image}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_thumbs', __CLASS__));
        $btn->setCode('{thumbs}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/images-stack.png');
        $element->addBtn($btn);

        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_current_image_number', __CLASS__));
        $btn->setCode('{currentImageNumber}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-number.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_total_images_number', __CLASS__));
        $btn->setCode('{totalImagesNumber}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/images-number.png');
        $element->addBtn($btn);

        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_prev_image_thumb', __CLASS__));
        $btn->setCode('{prevImageThumb}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-small-prev.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_next_image_thumb', __CLASS__));
        $btn->setCode('{nextImageThumb}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-small-next.png');
        $element->addBtn($btn);

        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_prev_image_title', __CLASS__));
        $btn->setCode('{prevImageTitle}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit-prev.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_next_image_title', __CLASS__));
        $btn->setCode('{nextImageTitle}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit-next.png');
        $element->addBtn($btn);

        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_prev_image_link', __CLASS__));
        $btn->setCode('{prevImageLink}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/chain-prev.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_next_image_link', __CLASS__));
        $btn->setCode('{nextImageLink}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/chain-next.png');
        $element->addBtn($btn);

        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_if_prev_block', __CLASS__));
        $btn->setCode('{if:prev}{/if:prev}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/script-code-prev.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_if_next_block', __CLASS__));
        $btn->setCode('{if:next}{/if:next}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/script-code-next.png');
        $element->addBtn($btn);
        $aForm->addElement($element);

        $aForm->addElement(new opFormElementTabContentEnd('advancedEnd', 'advancedEnd'));

        if (isset($_POST['name'])) {
            $isValid  = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($isValid) {
                $this->albumMapper->addElements($aForm->getElements());
                $this->albumMapper->insert();

                opSystem::Msg(opTranslation::getTranslation('_album_created', __CLASS__), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opGallery');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }

        return $template;
    }

    public function albumEdit() {
        $albumId = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $this->albumMapper->setRowID($albumId);
        $albumData = $this->albumMapper->fetchRow();
        if ($albumData !== false) {
            $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/image--pencil.png', opTranslation::getTranslation('_edit_album', __CLASS__));
            $aForm->setAction('/admin/opGallery/albumEdit/'.$albumId);
            $aForm->setMethod('post');
            $aForm->setCancelLink('/admin/opGallery');

            $element = new opFormElementTabGroup('tabGroup', 'tabGroup');
            $element->addTab('generalContent', opTranslation::getTranslation('_general', __CLASS__));
            $element->addTab('advancedContent', opTranslation::getTranslation('_advanced', __CLASS__));
            $aForm->addElement($element);

            $aForm->addElement(new opFormElementTabContent('generalContent', 'generalContent'));
            $sBox = new opFormElementSelect('parent', opTranslation::getTranslation('_album_parent', get_class($this)));
            $sBox->addOption(0, opTranslation::getTranslation('_none', get_class($this)));
            $rVal = $this->db->query('SELECT * FROM op_gallery_categories ORDER BY parent ASC, name ASC');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($this->orderRecursive(0, $rVal->fetchAll(), 0, 0) as $v) {
                $sBox->addOption($v['id'], $v['name']);
            }
            $sBox->addValidator(new opFormValidateNumeric());
            $sBox->setValue($albumData->parent);
            $aForm->addElement($sBox);

            $element = new opFormElementTextbox('name', opTranslation::getTranslation('_album_name', __CLASS__), 100);
            $element->addValidator(new opFormValidateStringLength(2, 100));
            $element->setValue($albumData->name);
            $aForm->addElement($element);
            $aForm->addElement(new opFormElementTabContentEnd('generalEnd', 'generalEnd'));

            $aForm->addElement(new opFormElementTabContent('advancedContent', 'advancedContent'));
            $aForm->addElement(new opFormElementTextheader('override', opTranslation::getTranslation('_override_default_settings', __CLASS__)));

            $element = new opFormElementTextbox('thumb_size', opTranslation::getTranslation('_thumb_size', __CLASS__), 3);
            $element->setValue($albumData->thumb_size);
            $aForm->addElement($element);

            $element = new opFormElementTextbox('image_size', opTranslation::getTranslation('_image_size', __CLASS__), 4);
            $element->setValue($albumData->image_size);
            $aForm->addElement($element);

            $element = new opFormElementCodebox('image_template', opTranslation::getTranslation('_image_template', __CLASS__));
            $element->setSanitize(false);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_original_image', __CLASS__));
            $btn->setCode('{originalImage}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image.png');
            $element->addBtn($btn);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_resized_image', __CLASS__));
            $btn->setCode('{resizedImage}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-resize.png');
            $element->addBtn($btn);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_title', __CLASS__));
            $btn->setCode('{imageTitle}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit.png');
            $element->addBtn($btn);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_description', __CLASS__));
            $btn->setCode('{imageDescription}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit-image.png');
            $element->addBtn($btn);
            $element->setValue($albumData->image_template);
            $aForm->addElement($element);

            $element = new opFormElementCodebox('thumb_template', opTranslation::getTranslation('_thumb_template', __CLASS__));
            $element->setSanitize(false);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_original_image', __CLASS__));
            $btn->setCode('{originalImage}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image.png');
            $element->addBtn($btn);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_thumb_image', __CLASS__));
            $btn->setCode('{thumbImage}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-small.png');
            $element->addBtn($btn);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_title', __CLASS__));
            $btn->setCode('{imageTitle}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit.png');
            $element->addBtn($btn);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_description', __CLASS__));
            $btn->setCode('{imageDescription}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit-image.png');
            $element->addBtn($btn);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_link', __CLASS__));
            $btn->setCode('{imageLink}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/chain.png');
            $element->addBtn($btn);
            $element->setValue($albumData->thumb_template);
            $aForm->addElement($element);

            $element = new opFormElementCodebox('album_template', opTranslation::getTranslation('_album_template', __CLASS__));
            $element->setSanitize(false);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image', __CLASS__));
            $btn->setCode('{image}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image.png');
            $element->addBtn($btn);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_thumbs', __CLASS__));
            $btn->setCode('{thumbs}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/images-stack.png');
            $element->addBtn($btn);

            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_current_image_number', __CLASS__));
            $btn->setCode('{currentImageNumber}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-number.png');
            $element->addBtn($btn);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_total_images_number', __CLASS__));
            $btn->setCode('{totalImagesNumber}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/images-number.png');
            $element->addBtn($btn);

            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_prev_image_thumb', __CLASS__));
            $btn->setCode('{prevImageThumb}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-small-prev.png');
            $element->addBtn($btn);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_next_image_thumb', __CLASS__));
            $btn->setCode('{nextImageThumb}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-small-next.png');
            $element->addBtn($btn);

            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_prev_image_title', __CLASS__));
            $btn->setCode('{prevImageTitle}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit-prev.png');
            $element->addBtn($btn);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_next_image_title', __CLASS__));
            $btn->setCode('{nextImageTitle}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit-next.png');
            $element->addBtn($btn);

            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_prev_image_link', __CLASS__));
            $btn->setCode('{prevImageLink}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/chain-prev.png');
            $element->addBtn($btn);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_next_image_link', __CLASS__));
            $btn->setCode('{nextImageLink}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/chain-next.png');
            $element->addBtn($btn);

            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_if_prev_block', __CLASS__));
            $btn->setCode('{if:prev}{/if:prev}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/script-code-prev.png');
            $element->addBtn($btn);
            $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_if_next_block', __CLASS__));
            $btn->setCode('{if:next}{/if:next}');
            $btn->setIcon($this->getRelativePath(__CLASS__).'icons/script-code-next.png');
            $element->addBtn($btn);
            
            $element->setValue($albumData->album_template);
            $aForm->addElement($element);

            $aForm->addElement(new opFormElementTabContentEnd('advancedEnd', 'advancedEnd'));

            if (isset($_POST['name'])) {
                $isValid  = $aForm->isValid($_POST);
                $template = new opHtmlTemplate($aForm->render());
                if ($isValid) {
                    $this->albumMapper->addElements($aForm->getElements());
                    $this->albumMapper->update();

                    $this->updateLastModified(opPlugin::getIdByName(__CLASS__), $albumId);

                    opSystem::Msg(opTranslation::getTranslation('_album_updated', __CLASS__), opSystem::SUCCESS_MSG);
                    opSystem::redirect('/opGallery');
                }
            } else {
                $template = new opHtmlTemplate($aForm->render());
            }

            return $template;
        }

        opSystem::Msg(opTranslation::getTranslation('_unknown_album_id', __CLASS__), opSystem::ERROR_MSG);
        opSystem::redirect('/opGallery');
    }

    public function albumModify() {
        $albumId = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $this->albumMapper->setRowID($albumId);
        $albumData = $this->albumMapper->fetchRow();
        if ($albumData !== false) {
            $imageList = (isset($_POST['imageList'])) ? $_POST['imageList'] : false;
            if (is_array($imageList)) {
                $countStatement     = $this->db->prepare('SELECT COUNT(*) FROM op_gallery_pictures WHERE image_id = :image_id AND parent = :parent');
                $insertStatement    = $this->db->prepare('INSERT INTO op_gallery_pictures (image_id, parent, position) VALUES (:image_id, :parent, :position)');
                $updateStatement    = $this->db->prepare('UPDATE op_gallery_pictures SET position = :position WHERE image_id = :image_id AND parent = :parent');
                $i = 0;
                foreach ($imageList as $imageId) {
                    $countStatement->execute(array('image_id' => $imageId, 'parent' => $albumId));
                    if ($countStatement->fetchColumn() <= 0) {
                        $insertStatement->execute(array('image_id' => $imageId, 'parent' => $albumId, 'position' => $i));
                    } else {
                        $updateStatement->execute(array('image_id' => $imageId, 'parent' => $albumId, 'position' => $i));
                    }
                    $i++;
                }
                $rVal = $this->db->prepare('SELECT * FROM op_gallery_pictures WHERE parent = :parent');
                $rVal->setFetchMode(PDO::FETCH_OBJ);
                $rVal->execute(array('parent' => $albumId));
                $deleteStatement = $this->db->prepare('DELETE FROM op_gallery_pictures WHERE id = :id');
                foreach ($rVal->fetchAll() as $image) {
                    if (! in_array($image->image_id, $imageList, true)) {
                        $deleteStatement->execute(array('id' => $image->id));
                    }
                }

                opSystem::Msg(opTranslation::getTranslation('_album_updated', __CLASS__), opSystem::SUCCESS_MSG);
            } else {
                opSystem::Msg(opTranslation::getTranslation('_add_images_before_saving', __CLASS__), opSystem::ERROR_MSG);
            }
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_album_id', __CLASS__), opSystem::ERROR_MSG);
        }
        opSystem::redirect('/opGallery');
    }

    public function albumDelete() {
        if (isset($_POST['delete'])) {
            $deleteInfo = array();
            foreach ($_POST['delete'] as $albumId) {
                if (! opLayout::isContentAssigned(__CLASS__, $albumId)) {
                    $this->albumMapper->setRowID($albumId);
                    $albumData = $this->albumMapper->fetch();

                    $rVal = $this->db->prepare('DELETE * FROM op_gallery_pictures WHERE parent = :parent');
                    $rVal->execute(array('parent' => $albumId));

                    $deleteInfo[] = sprintf(opTranslation::getTranslation('_album_deleted', __CLASS__), '&quot;'.$albumData->name.'&quot;');

                    $this->albumMapper->delete();
                } else {
                    $deleteInfo[] = sprintf(opTranslation::getTranslation('_album_unassign_before_delete', __CLASS__), '&quot;'.$albumData->name.'&quot;');
                }
            }
            $deleteMsg = '<ul>';
            foreach ($deleteInfo as $ln) {
                $deleteMsg .= '<li>'.$ln.'</li>';
            }
            $deleteMsg .= '</ul>';
            opSystem::Msg($deleteMsg, opSystem::INFORM_MSG);
        } else {
            opSystem::Msg(opTranslation::getTranslation('_no_album_selected', __CLASS__), opSystem::ERROR_MSG);
        }

        opSystem::redirect('/opGallery');
    }

    public function pictureEdit() {
        $imageId = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : false;
        if ($imageId !== false) {
            $activeImage        = (isset($_POST['activeImage'])) ? $_POST['activeImage'] : 0;
            $pictureTitle       = (isset($_POST['title'])) ? $_POST['title'] : '';
            $pictureDescription = (isset($_POST['description'])) ? $_POST['description'] : '';

            $rVal = $this->db->prepare('UPDATE op_gallery_pictures SET title = :title, description = :description WHERE id = :id');
            $rVal->execute(array('title' => $pictureTitle, 'description' => $pictureDescription, 'id' => $imageId));

            opSystem::Msg(opTranslation::getTranslation('_picture_updated', __CLASS__), opSystem::SUCCESS_MSG);
            opSystem::redirect('/opGallery?i='.$activeImage);
        }
        opSystem::Msg(opTranslation::getTranslation('_unknown_picture_id', __CLASS__), opSystem::ERROR_MSG);
        opSystem::redirect('/opGallery');
    }

    public function categoryDelete() {
        if (isset($_POST['delete']) && is_array($_POST['delete'])) {
            $deleteMessages = array();
            foreach ($_POST['delete'] as $catID) {
                # Uncategorize all documents in this category if its empty and delete it
                $this->categoryMapper->setRowID($catID);
                $categoryData = $this->categoryMapper->fetchRow();

                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_gallery_categories WHERE parent = :parent');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('parent' => $catID));
                if ($rVal->fetchColumn() <= 0) {
                    $element = new opFormElementHidden('parent', 'parent');
                    $element->setValue('0');
                    $this->albumMapper->addElement($element);
                    $this->albumMapper->setFieldIDName('parent');
                    $this->albumMapper->setRowID($catID);
                    $this->albumMapper->update();

                    $this->categoryMapper->delete();
                } else {
                    $deleteMessages[] = sprintf(opTranslation::getTranslation('_category_delete_error_msg', get_class($this)), '&quot;'.$categoryData->name.'&quot;');
                }
            }

            if (count($deleteMessages) > 0) {
                $msg = '<ul>';
                foreach ($deleteMessages as $message) {
                    $msg .= '<li>'.$message.'</li>';
                }
                $msg .= '</li>';
                opSystem::Msg($msg, opSystem::INFORM_MSG);
            }
        } else {
            opSystem::Msg(opTranslation::getTranslation('_no_categories_selected', __CLASS__), opSystem::ERROR_MSG);
        }
        opSystem::redirect('/opGallery/categoryIndex');
    }

    public function categoryEdit() {
        $catID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : false;
        if ($catID !== false) {
            $this->categoryMapper->setRowID($catID);
            $categoryData = $this->categoryMapper->fetchRow();
            if ($categoryData !== false) {
                $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/images.png', opTranslation::getTranslation('_edit_category', get_class($this)).' | '.opTranslation::getTranslation('_gallery', get_class($this)));
                $aForm->setAction('/admin/opGallery/categoryEdit/'.$this->args[0]);
                $aForm->setMethod('post');
                $aForm->setCancelLink('/admin/opGallery/categoryIndex');

                $sBox = new opFormElementSelect('parent', opTranslation::getTranslation('_category_parent', get_class($this)));
                $sBox->addOption(0, opTranslation::getTranslation('_none', get_class($this)));
                $rVal = $this->db->query('SELECT * FROM op_gallery_categories ORDER BY parent ASC, name ASC');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                foreach ($this->orderRecursive($catID, $rVal->fetchAll(), 0, 0) as $v) {
                    $sBox->addOption($v['id'], $v['name']);
                }
                $sBox->setValue($categoryData->parent);
                $sBox->addValidator(new opFormValidateNumeric());
                $aForm->addElement($sBox);

                $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_category_name', get_class($this)), 40);
                $tBox->addValidator(new opFormValidateStringLength(1, 40));
                $tBox->setValue($categoryData->name);
                $aForm->addElement($tBox);

                if (isset($_POST['name'])) {
                    $valid = $aForm->isValid($_POST);
                    $template = new opHtmlTemplate($aForm->render());
                    if ($valid) {
                        $this->categoryMapper->addElements($aForm->getElements());
                        $this->categoryMapper->update();

                        opSystem::Msg(opTranslation::getTranslation('_category_saved', get_class($this)), opSystem::SUCCESS_MSG);
                        opSystem::redirect('/opGallery/categoryIndex');
                    }
                } else {
                    $template = new opHtmlTemplate($aForm->render());
                }

                return $template;
            }
        }
        opSystem::Msg(opTranslation::getTranslation('_unknown_category_id', get_class($this)), opSystem::ERROR_MSG);
        opSystem::redirect('/opGallery/categoryIndex');
    }

    public function categoryNew() {
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/images.png', opTranslation::getTranslation('_new_category', get_class($this)).' | '.opTranslation::getTranslation('_gallery', get_class($this)));
        $aForm->setAction('/admin/opGallery/categoryNew');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opGallery/categoryIndex');

        $sBox = new opFormElementSelect('parent', opTranslation::getTranslation('_category_parent', get_class($this)));
        $sBox->addOption(0, opTranslation::getTranslation('_none', get_class($this)));
        $rVal = $this->db->query('SELECT * FROM op_gallery_categories ORDER BY parent ASC, name ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($this->orderRecursive(0, $rVal->fetchAll(), 0, 0) as $v) {
            $sBox->addOption($v['id'], $v['name']);
        }
        $sBox->addValidator(new opFormValidateNumeric());
        $aForm->addElement($sBox);

        $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_category_name', get_class($this)), 40);
        $tBox->addValidator(new opFormValidateStringLength(1, 40));
        $aForm->addElement($tBox);

        if (isset($_POST['name'])) {
            $valid = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($valid) {
                $this->categoryMapper->addElements($aForm->getElements());
                $this->categoryMapper->insert();

                opSystem::Msg(opTranslation::getTranslation('_category_added', get_class($this)), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opGallery/categoryIndex');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }

        return $template;
    }

    public function categoryIndex() {
        $rVal = $this->db->query('SELECT * FROM op_gallery_categories ORDER BY position ASC, parent ASC, name ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opGallery.categoryIndex.php');
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('opPluginName', __CLASS__);
        $template->set('catList', $this->orderRecursiveAsULForCat($rVal->fetchAll(), 0));

        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opGallery.categoryIndex.js'));
        return $template;
    }

    public function categorySort() {
        if (isset($_POST['serialized'])) {
            if (! empty($_POST['serialized'])) {
                $serialized = (isset($_POST['serialized'])) ? explode(',', $_POST['serialized']) : array();
                $i = 0;
                foreach ($serialized as $catID) {
                    $element = new opFormElementHidden('position', 'position');
                    $element->setValue($i);
                    $this->categoryMapper->clearAllElements();
                    $this->categoryMapper->addElement($element);
                    $this->categoryMapper->setRowId($catID);
                    $this->categoryMapper->update();
                    $i++;
                }

                opSystem::Msg(opTranslation::getTranslation('_category_order_saved', get_class($this)), opSystem::SUCCESS_MSG);
            } else {
                opSystem::Msg(opTranslation::getTranslation('_no_category_selected', get_class($this)), opSystem::ERROR_MSG);
            }
        }
        $parentID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $rVal = $this->db->prepare('SELECT * FROM op_gallery_categories WHERE parent = :parent ORDER BY position ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('parent' => $parentID));

        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opGallery.categorySort.php');
        $template->set('parentSelected', $parentID);
        $template->set('childsOfParent', $rVal->fetchAll());
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('opThemePath', $this->theme->getThemePath());

        $parentCategories = array();
        $rVal = $this->db->query('SELECT * FROM op_gallery_categories WHERE parent > 0 GROUP BY parent');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($rVal->fetchAll() as $k => $v) {
            $pCat = $this->db->prepare('SELECT * FROM op_gallery_categories WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $pCat->execute(array('id' => $v['parent']));
            $pCat = $pCat->fetch();
            $parentCategories[] = $pCat;
        }
        $template->set('parentCategories', $parentCategories);
        $template->set('opPluginName', get_class($this));
        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opGallery.categorySort.js'));

        return $template;
    }

    public function settings() {
        $aForm = new opAdminForm($this->getRelativePath(__CLASS__).'icons/gear.png', opTranslation::getTranslation('_settings', __CLASS__).' | '.opTranslation::getTranslation('_gallery', __CLASS__));
        $aForm->setAction('/admin/opGallery/settings');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opGallery');

        $element = new opFormElementTextbox('url_var', opTranslation::getTranslation('_url_var', __CLASS__), 20);
        $element->addValidator(new opFormValidateStringLength(1, 20));
        $element->setValue(opSystem::_get('url_var', __CLASS__));
        $aForm->addElement($element);

        $element = new opFormElementTextbox('thumb_size', opTranslation::getTranslation('_thumb_size', __CLASS__), 3);
        $element->addValidator(new opFormValidateNumericNotZero());
        $element->setValue(opSystem::_get('thumb_size', __CLASS__));
        $aForm->addElement($element);

        $element = new opFormElementTextbox('image_size', opTranslation::getTranslation('_image_size', __CLASS__), 4);
        $element->addValidator(new opFormValidateNumericNotZero());
        $element->setValue(opSystem::_get('image_size', __CLASS__));
        $aForm->addElement($element);

        $element = new opFormElementCodebox('image_template', opTranslation::getTranslation('_image_template', __CLASS__));
        $element->setSanitize(false);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_original_image', __CLASS__));
        $btn->setCode('{originalImage}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_resized_image', __CLASS__));
        $btn->setCode('{resizedImage}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-resize.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_title', __CLASS__));
        $btn->setCode('{imageTitle}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_description', __CLASS__));
        $btn->setCode('{imageDescription}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit-image.png');
        $element->addBtn($btn);
        $element->setValue(opSystem::_get('image_template', __CLASS__));
        $aForm->addElement($element);

        $element = new opFormElementCodebox('thumb_template', opTranslation::getTranslation('_thumb_template', __CLASS__));
        $element->setSanitize(false);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_original_image', __CLASS__));
        $btn->setCode('{originalImage}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_thumb_image', __CLASS__));
        $btn->setCode('{thumbImage}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-small.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_title', __CLASS__));
        $btn->setCode('{imageTitle}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_description', __CLASS__));
        $btn->setCode('{imageDescription}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit-image.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image_link', __CLASS__));
        $btn->setCode('{imageLink}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/chain.png');
        $element->addBtn($btn);
        $element->setValue(opSystem::_get('thumb_template', __CLASS__));
        $aForm->addElement($element);

        $element = new opFormElementCodebox('album_template', opTranslation::getTranslation('_album_template', __CLASS__));
        $element->setSanitize(false);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_image', __CLASS__));
        $btn->setCode('{image}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_thumbs', __CLASS__));
        $btn->setCode('{thumbs}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/images-stack.png');
        $element->addBtn($btn);    

        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_current_image_number', __CLASS__));
        $btn->setCode('{currentImageNumber}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-number.png');
        $element->addBtn($btn);        
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_total_images_number', __CLASS__));
        $btn->setCode('{totalImagesNumber}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/images-number.png');
        $element->addBtn($btn);

        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_prev_image_thumb', __CLASS__));
        $btn->setCode('{prevImageThumb}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-small-prev.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_next_image_thumb', __CLASS__));
        $btn->setCode('{nextImageThumb}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/image-small-next.png');
        $element->addBtn($btn);

        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_prev_image_title', __CLASS__));
        $btn->setCode('{prevImageTitle}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit-prev.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_next_image_title', __CLASS__));
        $btn->setCode('{nextImageTitle}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/edit-next.png');
        $element->addBtn($btn);

        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_prev_image_link', __CLASS__));
        $btn->setCode('{prevImageLink}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/chain-prev.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_next_image_link', __CLASS__));
        $btn->setCode('{nextImageLink}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/chain-next.png');
        $element->addBtn($btn);

        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_if_prev_block', __CLASS__));
        $btn->setCode('{if:prev}{/if:prev}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/script-code-prev.png');
        $element->addBtn($btn);
        $btn = new opFormElementCodeboxBtn(opTranslation::getTranslation('_if_next_block', __CLASS__));
        $btn->setCode('{if:next}{/if:next}');
        $btn->setIcon($this->getRelativePath(__CLASS__).'icons/script-code-next.png');
        $element->addBtn($btn);

        $element->setValue(opSystem::_get('album_template', __CLASS__));
        $aForm->addElement($element);

        if (isset($_POST['thumb_size'])) {
            $isValid    = $aForm->isValid($_POST);
            $template   = new opHtmlTemplate($aForm->render());
            if ($isValid) {
                foreach ($_POST as $k => $v) {
                    opSystem::_set($k, $v, __CLASS__);
                }

                opSystem::Msg(opTranslation::getTranslation('_settings_updated', __CLASS__), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opGallery');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }

        return $template;
    }

    public function getOutput($requestId, $renderMode) {
        return $this->renderAlbum($requestId);
    }

    public static function getContentEditPath() {
        return '/admin/opGallery/albumEdit/';
    }

    public static function getConfig() {
        return simplexml_load_file(self::getFullPath(__CLASS__).'opGallery.xml');
    }

    public static function getContentList() {
        $db             = opSystem::getDatabaseInstance();
        $contentList    = new opContentList();

        $contentGroup = new opContentGroup(opTranslation::getTranslation('_uncategorized', __CLASS__));
        $rVal = $db->query('SELECT * FROM op_gallery WHERE parent = 0 ORDER BY name ASC');
        $rVal->setFetchMode(PDO::FETCH_OBJ);
        foreach ($rVal->fetchAll() as $album) {
            $contentGroup->addElement(new opContentElement($album->id, $album->name));
        }
        $contentList->addElement($contentGroup);

        $rVal = $db->query('SELECT * FROM op_gallery_categories WHERE parent = 0 ORDER BY position ASC');
        $rVal->setFetchMode(PDO::FETCH_OBJ);
        foreach ($rVal->fetchAll() as $category) {
            $contentGroup = new opContentGroup($category->name);
            $contentList->addElement(self::buildContentList($contentGroup, $category->id));
        }

        return $contentList;
    }

    public static function getContentNameById($id) {
        if ($id == 0) {
            return opTranslation::getTranslation('_album_index', __CLASS__);
        }

        $albumMapper = new opFormDataMapper(opSystem::getDatabaseInstance());
        $albumMapper->setTable('op_gallery');
        $albumMapper->setFieldIDName('id');
        $albumMapper->setRowID($id);
        $albumData = $albumMapper->fetchRow();
        if ($albumData !== false) {
            return $albumData->name;
        }

        return false;
    }

    public static function getIcon() {
        return self::getRelativePath(__CLASS__).'icons/images.png';
    }

    public static function install() {
        $sqlImport = new opSQLImport(opSystem::getDatabaseInstance());

        # Import tables
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opGallery.install.sql')) { return false; };

        # Import data
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opGallery.data.sql')) { return false; };

        return true;
    }

    public static function uninstall() {
        $sqlImport = new opSQLImport(opSystem::getDatabaseInstance());

        # Import tables
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opGallery.uninstall.sql')) { return false; };

        return true;
    }

    protected function renderAlbum($albumId) {
        $urlVar = opSystem::_get('url_var', __CLASS__).$albumId;
        $this->albumMapper->setRowID($albumId);
        $albumData  = $this->albumMapper->fetchRow();
        $albumIndex = ((isset($_GET[$urlVar])) && is_numeric($_GET[$urlVar])) ? floor($_GET[$urlVar]) : 0;
        if ($albumData !== false) {
            $rVal = $this->db->prepare('SELECT op_filemanager_filemap.filename, op_filemanager_filemap.filepath, op_gallery_pictures.* FROM op_gallery_pictures INNER JOIN op_filemanager_filemap ON op_filemanager_filemap.id = op_gallery_pictures.image_id WHERE op_gallery_pictures.parent = :parent ORDER BY op_gallery_pictures.position ASC');
            $rVal->setFetchMode(PDO::FETCH_OBJ);
            $rVal->execute(array('parent' => $albumId));
            $imageList = $rVal->fetchAll();

            $albumIndex = ($albumIndex < 0) ? 0 : $albumIndex;
            $albumIndex = ($albumIndex > count($imageList)-1) ? count($imageList)-1 : $albumIndex;

            $imageData = '';
            $thumbData = array();
            if (count($imageList) > 0) {
                $resizeSize  = (strlen($albumData->image_size) > 0 && is_numeric($albumData->image_size)) ? $albumData->image_size : opSystem::_get('image_size', __CLASS__);
                $imageFile   = opFileFactory::identify($imageList[$albumIndex]->filepath.$imageList[$albumIndex]->filename);
                $imageOrig   = $imageFile->getRelativePath();
                $imageResize = $imageFile->getThumbnail($resizeSize);
                $imageTitle  = $imageList[$albumIndex]->title;
                $imageDesc   = $imageList[$albumIndex]->description;

                $imageTemplate = (strlen($albumData->image_template) > 0) ? $albumData->image_template : opSystem::_get('image_template', __CLASS__);
                $imageData     = str_ireplace(array('{originalImage}', '{resizedImage}', '{imageTitle}', '{imageDescription}'),
                                              array($imageOrig, $imageResize, $imageTitle, $imageDesc),
                                              $imageTemplate);

                $thumbTemplate = (strlen($albumData->thumb_template) > 0) ? $albumData->thumb_template : opSystem::_get('thumb_template', __CLASS__);
                $thumbData = '';
                $thumbSize = (strlen($albumData->thumb_size) > 0 && is_numeric($albumData->thumb_size)) ? $albumData->thumb_size : opSystem::_get('thumb_size', __CLASS__);
                $i = 0;
                foreach ($imageList as $image) {
                    $imageFile = opFileFactory::identify($image->filepath.$image->filename);
                    $imageOrig   = $imageFile->getRelativePath();
                    $imageResize = $imageFile->getThumbnail($thumbSize);
                    $imageTitle  = $image->title;
                    $imageDesc   = $image->description;
                    if ($imageFile instanceof opGraphicsFile) {
                        $thumbData[] = str_ireplace(array('{originalImage}', '{thumbImage}', '{imageTitle}', '{imageDescription}', '{imageLink}'),
                                                    array($imageOrig, $imageResize, $imageTitle, $imageDesc, $this->generateUrl($albumId, $i)),
                                                    $thumbTemplate);
                        $i++;
                    }
                }
            }

            $albumTemplate  = (strlen($albumData->album_template) > 0) ? $albumData->album_template : opSystem::_get('album_template', __CLASS__);
            $prevImageLink  = false;
            $prevImageTitle = false;
            $prevImageThumb = false;
            $nextImageLink  = false;
            $nextImageTitle = false;
            $nextImageThumb = false;
  
            # Previous
            if ($albumIndex > 0) {
                $prevImageLink  = $this->generateUrl($albumId, $albumIndex-1);
                $prevImageTitle = $imageList[$albumIndex-1]->title;
                $imageFile      = opFileFactory::identify($imageList[$albumIndex-1]->filepath.$imageList[$albumIndex-1]->filename);
                $prevImageThumb = $imageFile->getThumbnail($thumbSize);
            }
            # Next
            if ($albumIndex < count($imageList)-1) {
                $nextImageLink  = $this->generateUrl($albumId, $albumIndex+1);
                $nextImageTitle = $imageList[$albumIndex+1]->title;
                $imageFile      = opFileFactory::identify($imageList[$albumIndex+1]->filepath.$imageList[$albumIndex+1]->filename);
                $nextImageThumb = $imageFile->getThumbnail($thumbSize);
            }
            
            if ($prevImageLink === false) {
                $ifPrevIndex    = strpos($albumTemplate, '{if:prev}');
                $ifPrevEndIndex = strpos($albumTemplate, '{/if:prev}');
                $albumTemplate  = substr($albumTemplate, 0, $ifPrevIndex).substr($albumTemplate, $ifPrevEndIndex+10);
            }
            if ($nextImageLink === false) {
                $ifNextIndex    = strpos($albumTemplate, '{if:next}');
                $ifNextEndIndex = strpos($albumTemplate, '{/if:next}');
                $albumTemplate  = substr($albumTemplate, 0, $ifNextIndex).substr($albumTemplate, $ifNextEndIndex+10);
            }
            return str_ireplace(array('{image}', '{thumbs}', '{currentImageNumber}', '{totalImagesNumber}', '{prevImageLink}', '{prevImageTitle}', '{prevImageThumb}', '{nextImageThumb}', '{nextImageLink}', '{nextImageTitle}', '{if:prev}', '{/if:prev}', '{if:next}', '{/if:next}'),
                                array($imageData, implode('', $thumbData), $albumIndex+1, count($imageList), $prevImageLink, $prevImageTitle, $prevImageThumb, $nextImageThumb, $nextImageLink, $nextImageTitle, '', '', '', ''),
                                $albumTemplate);
        } else {
            return false;
        }
    }

    protected function generateUrl($albumId, $albumIndex) {
        $urlVar = opSystem::_get('url_var', __CLASS__).$albumId;
        # Get current url
        $currentUrl     = $_SERVER['REQUEST_URI'];
        $separator      = (strpos($currentUrl, '?') === false) ? '?' : '&';
        # Clean urlVar
        $strIndex   = strpos($currentUrl, $urlVar);
        $separator  = (substr($currentUrl, $strIndex-1, 1) == '?') ? '?' : $separator;
        if ($strIndex !== false) {
            $ampIndex = strpos($currentUrl, '&', $strIndex);
            if ($ampIndex !== false) {
                return substr($currentUrl, 0, ($strIndex-1)).$separator.$urlVar.'='.$albumIndex.substr($currentUrl, $ampIndex);
            } else {
                return substr($currentUrl, 0, ($strIndex-1)).$separator.$urlVar.'='.$albumIndex;
            }
        } else {
            return $currentUrl.$separator.$urlVar.'='.$albumIndex;
        }
    }

    protected function orderRecursive($editId, $arr, $parent, $indent, &$retArr = array(), $indentIncrease = 3) {
        foreach ($arr as $v) {
            if ($v['id'] != $editId) {
                if ($v['parent'] == $parent) {
                    $v['name'] = str_repeat('&nbsp;', $indent).$v['name'];
                    $retArr[] = $v;
                    foreach ($arr as $r) {
                        if ($r['id'] != $editId && $v['id'] == $r['parent']) {
                            $this->orderRecursive($editId, $arr, $v['id'], $indent+$indentIncrease, $retArr);
                        }
                    }
                }
            }
        }
        return $retArr;
    }

    protected function orderRecursiveAsULForCat($arr, $parent, &$retVal = '', $padding = 0) {
        foreach ($arr as $v) {
            if ($v['parent'] == $parent) {
                $retVal .= '<li><span class="sortChk"><input type="checkbox" name="delete[]" value="'.$v['id'].'" /></span><span class="sortTitle"><a href="/admin/opGallery/categoryEdit/'.$v['id'].'" style="padding-left:'.$padding.'px;" title="'.sprintf(opTranslation::getTranslation('_category_edit', get_class($this)), '&quot;'.$v['name'].'&quot;').'">'.$v['name'].'</a></span>';
                foreach ($arr as $r) {
                    if ($v['id'] == $r['parent']) {
                        $retVal .= '<ul>';
                        $this->orderRecursiveAsULForCat($arr, $v['id'], $retVal, $padding+10);
                        $retVal .= '</ul>';
                        break;
                    }
                }
                $retVal .= '</li>';
            }
        }
        return $retVal;
    }

    protected static function buildContentList(opContentGroup $contentGroup, $groupId) {
        $db   = opSystem::getDatabaseInstance();
        $rVal = $db->prepare('SELECT * FROM op_gallery_categories WHERE parent = :parent ORDER BY position ASC');
        $rVal->setFetchMode(PDO::FETCH_OBJ);
        $rVal->execute(array('parent' => $groupId));
        foreach ($rVal->fetchAll() as $category) {
            $childGroup = new opContentGroup($category->name);
            $contentGroup->addElement(self::buildContentList($childGroup, $category->id));
        }
        $rVal = $db->prepare('SELECT * FROM op_gallery WHERE parent = :parent ORDER BY name ASC');
        $rVal->setFetchMode(PDO::FETCH_OBJ);
        $rVal->execute(array('parent' => $groupId));
        foreach ($rVal->fetchAll() as $album) {
            $contentGroup->addElement(new opContentElement($album->id, $album->name));
        }

        return $contentGroup;
    }
}
?>