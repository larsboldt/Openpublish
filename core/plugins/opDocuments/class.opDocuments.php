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
class opDocuments extends opPluginBase {
    protected $documentMapper, $categoryMapper;
    
    protected function initialize() {
        $this->documentMapper = new opFormDataMapper($this->db);
        $this->documentMapper->setTable('op_documents');
        $this->documentMapper->setFieldIDName('id');

        $this->categoryMapper = new opFormDataMapper($this->db);
        $this->categoryMapper->setTable('op_document_categories');
        $this->categoryMapper->setFieldIDName('id');
    }

    public function getOutput($requestID, $renderMode) {
        if (is_numeric($requestID)) {
            switch ($renderMode) {
                case 'rss2':
                case 'rss1':
                case 'atom':
                    return sprintf(opTranslation::getTranslation('_no_feed_output', __CLASS__), __CLASS__);
                    break;
                default:
                    $this->documentMapper->setRowID($requestID);
                    $documentData = $this->documentMapper->fetchRow();
                    if ($documentData !== false) {
                        return $documentData->html;
                    } else {
                        return false;
                    }
            }
        } else {
            return false;
        }
    }

    public function categoryDelete() {
        if (isset($_POST['delete']) && is_array($_POST['delete'])) {
            foreach ($_POST['delete'] as $catID) {
                # Uncategorize all documents in this category if its empty and delete it
                $this->categoryMapper->setRowID($catID);
                $categoryData = $this->categoryMapper->fetchRow();

                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_document_categories WHERE parent = :parent');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('parent' => $catID));
                if ($rVal->fetchColumn() <= 0) {
                    $element = new opFormElementHidden('cat_id', 'cat_id');
                    $element->setValue('0');
                    $this->documentMapper->addElement($element);
                    $this->documentMapper->setFieldIDName('cat_id');
                    $this->documentMapper->setRowID($catID);
                    $this->documentMapper->update();

                    $this->categoryMapper->delete();
                } else {
                    opSystem::Msg(sprintf(opTranslation::getTranslation('_category_delete_error_msg', __CLASS__), '&quot;'.$categoryData->name.'&quot;'), opSystem::INFORM_MSG);
                }
            }
        }
        opSystem::redirect('/opDocuments/categoryIndex');
    }

    public function categoryEdit() {
        $catID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : false;
        if ($catID !== false) {
            $this->categoryMapper->setRowID($catID);
            $categoryData = $this->categoryMapper->fetchRow();
            if ($categoryData !== false) {
                $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/documents.png', opTranslation::getTranslation('_edit_category', __CLASS__).' | '.opTranslation::getTranslation('_documents', __CLASS__));
                $aForm->setAction('/admin/opDocuments/categoryEdit/'.$this->args[0]);
                $aForm->setMethod('post');
                $aForm->setCancelLink('/admin/opDocuments/categoryIndex');

                $sBox = new opFormElementSelect('parent', opTranslation::getTranslation('_category_parent', __CLASS__));
                $sBox->addOption(0, opTranslation::getTranslation('_none', __CLASS__));
                $rVal = $this->db->query('SELECT * FROM op_document_categories ORDER BY parent ASC, name ASC');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                foreach ($this->orderRecursive($catID, $rVal->fetchAll(), 0, 0) as $v) {
                    $sBox->addOption($v['id'], $v['name']);
                }
                $sBox->setValue($categoryData->parent);
                $sBox->addValidator(new opFormValidateNumeric());
                $aForm->addElement($sBox);

                $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_category_name', __CLASS__), 40);
                $tBox->addValidator(new opFormValidateStringLength(1, 40));
                $tBox->setValue($categoryData->name);
                $aForm->addElement($tBox);

                if (isset($_POST['name'])) {
                    $valid = $aForm->isValid($_POST);
                    $template = new opHtmlTemplate($aForm->render());
                    if ($valid) {
                        $this->categoryMapper->addElements($aForm->getElements());
                        $this->categoryMapper->update();

                        opSystem::Msg(opTranslation::getTranslation('_category_saved', __CLASS__), opSystem::SUCCESS_MSG);
                        opSystem::redirect('/opDocuments/categoryIndex');
                    }
                } else {
                    $template = new opHtmlTemplate($aForm->render());
                }

                return $template;
            }
        }
        opSystem::Msg(opTranslation::getTranslation('_unknown_category_id', __CLASS__), opSystem::ERROR_MSG);
        opSystem::redirect('/opDocuments/categoryIndex');
    }

    public function categoryNew() {
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/documents.png', opTranslation::getTranslation('_new_category', __CLASS__).' | '.opTranslation::getTranslation('_documents', __CLASS__));
        $aForm->setAction('/admin/opDocuments/categoryNew');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opDocuments/categoryIndex');

        $sBox = new opFormElementSelect('parent', opTranslation::getTranslation('_category_parent', __CLASS__));
        $sBox->addOption(0, opTranslation::getTranslation('_none', __CLASS__));
        $rVal = $this->db->query('SELECT * FROM op_document_categories ORDER BY parent ASC, name ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($this->orderRecursive(0, $rVal->fetchAll(), 0, 0) as $v) {
            $sBox->addOption($v['id'], $v['name']);
        }
        $sBox->addValidator(new opFormValidateNumeric());
        $aForm->addElement($sBox);

        $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_category_name', __CLASS__), 40);
        $tBox->addValidator(new opFormValidateStringLength(1, 40));
        $aForm->addElement($tBox);

        if (isset($_POST['name'])) {
            $valid = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($valid) {
                $this->categoryMapper->addElements($aForm->getElements());
                $this->categoryMapper->insert();

                opSystem::Msg(opTranslation::getTranslation('_category_added', __CLASS__), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opDocuments/categoryIndex');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }

        return $template;
    }

    public function categoryIndex() {
        $rVal = $this->db->query('SELECT * FROM op_document_categories ORDER BY position ASC, parent ASC, name ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opDocuments.categoryIndex.php');
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('catList', $this->orderRecursiveAsULForCat($rVal->fetchAll(), 0));

        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opDocuments.categoryIndex.js'));
        $template->set('opPluginName', __CLASS__);
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

                opSystem::Msg(opTranslation::getTranslation('_category_order_saved', __CLASS__), opSystem::SUCCESS_MSG);
            } else {
                opSystem::Msg(opTranslation::getTranslation('_no_category_selected', __CLASS__), opSystem::ERROR_MSG);
            }
        }
        $parentID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $rVal = $this->db->prepare('SELECT * FROM op_document_categories WHERE parent = :parent ORDER BY position ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('parent' => $parentID));

        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opDocuments.categorySort.php');
        $template->set('parentSelected', $parentID);
        $template->set('childsOfParent', $rVal->fetchAll());
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('opThemePath', $this->theme->getThemePath());

        $parentCategories = array();
        $rVal = $this->db->query('SELECT * FROM op_document_categories WHERE parent > 0 GROUP BY parent');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($rVal->fetchAll() as $k => $v) {
            $pCat = $this->db->prepare('SELECT * FROM op_document_categories WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $pCat->execute(array('id' => $v['parent']));
            $pCat = $pCat->fetch();
            $parentCategories[] = $pCat;
        }
        $template->set('parentCategories', $parentCategories);
        $template->set('opPluginName', __CLASS__);
        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opDocuments.categorySort.js'));

        return $template;
    }

    public function adminIndexSort() {
        if (isset($this->args[0]) && is_numeric($this->args[0])) {
            $_SESSION['opDocuments_sort'] = $this->args[0];
        }
        opSystem::redirect('/opDocuments');
    }

    public function adminIndex() {
        $sortBy = (isset($_SESSION['opDocuments_sort'])) ? $_SESSION['opDocuments_sort'] : false;

        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opDocuments.index.php');

        $rVal = $this->db->query('SELECT * FROM op_document_categories ORDER BY position ASC, name ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $li = '<div id="0" class="droppable" style="padding-left:10px;'.((!$sortBy) ? 'font-weight:bold' : '').'"><img src="'.self::getRelativePath(__CLASS__).'icons/clear-folder'.((!$sortBy) ? '-open' : '').'.png" class="table-icon" /> <a href="/admin/opDocuments/adminIndexSort/0"><em>'.opTranslation::getTranslation('_uncategorized', __CLASS__).'</em></a></div>';
        $template->set('opCategories', $this->orderRecursiveAsULForIndex($rVal->fetchAll(), 0, $sortBy, $li));
        $template->set('opSort', $sortBy);

        if ($sortBy > 0) {
            $rVal = $this->db->prepare('SELECT op_document_categories.name AS catName, op_documents.* FROM op_documents LEFT JOIN op_document_categories ON op_document_categories.id = op_documents.cat_id WHERE op_documents.cat_id = :cat_id ORDER BY op_documents.name ASC');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('cat_id' => $sortBy));
        } else {
            $rVal = $this->db->query('SELECT op_document_categories.name AS catName, op_documents.* FROM op_documents LEFT JOIN op_document_categories ON op_document_categories.id = op_documents.cat_id WHERE op_documents.cat_id = 0 ORDER BY op_documents.name ASC');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
        }
        $docArr = array();
        foreach ($rVal->fetchAll() as $k => $v) {
            $assigned = opLayout::isContentAssigned(__CLASS__, $v['id']);
            $docArr[] = array($v, $assigned);
        }
        $template->set('opDocuments', $docArr);
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('opThemePath', $this->theme->getThemePath());
        $template->set('opPluginName', __CLASS__);
        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opDocuments.index.js'));

        return $template;
    }
    
    public function documentQuickPublish() {
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/document-export.png', opTranslation::getTranslation('_quickpublish', __CLASS__).' | '.opTranslation::getTranslation('_documents', __CLASS__));
        $aForm->setAction('/admin/opDocuments/documentQuickPublish');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opDocuments');

        $tBox = new opFormElementLayout('quickpublish_layout', opTranslation::getTranslation('_quickpublish_layout', __CLASS__), false, false, true);
        $tBox->addValidator(new opFormValidateStringLength(3, 20));
        $aForm->addElement($tBox);

        $tBox = new opFormElementMenu('quickpublish_menu', opTranslation::getTranslation('_quickpublish_menu', __CLASS__));
        $tBox->addValidator(new opFormValidateNumeric());
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_document_title', __CLASS__), 100);
        $tBox->addValidator(new opFormValidateStringLength(1, 100));
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextarea('html', '', false);
        $tBox->setSanitize(false);
        $aForm->addElement($tBox);

        $catID = (isset($_SESSION['opDocuments_sort'])) ? $_SESSION['opDocuments_sort'] : 0;
        $hBox = new opFormElementHidden('cat_id', 'cat_id');
        $hBox->setValue($catID);
        $aForm->addElement($hBox);

        if (isset($_POST['html'])) {
            $validForm = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($validForm) {
                # Create the document
                $this->documentMapper->addElementTypeToSkip(new opFormElementLayout('null','null'));
                $this->documentMapper->addElementTypeToSkip(new opFormElementMenu('null','null'));
                $this->documentMapper->addElements($aForm->getElements());
                $documentId = $this->documentMapper->insert();

                # Create layout
                list($templateId, $layoutId) = (strpos($_POST['quickpublish_layout'], ':') > 0) ? explode(':', $_POST['quickpublish_layout']) : array(0,0);
                $rVal = $this->db->prepare('INSERT INTO op_layouts (name, parent, theme_template, type, last_modified, etag, disable_local_cache, disable_meta_inheritance) VALUES (:name, :parent, :theme_template, :type, :lm, :etag, :cache, :meta)');
                $rVal->execute(array('name' => $_POST['name'], 'parent' => $layoutId, 'theme_template' => $templateId, 'type' => 0, 'lm' => gmdate('D, d M Y H:i:s \G\M\T', time()), 'etag' => opLayout::generateETag($_POST['name']), 'cache' => 0, 'meta' => 0));
                $newLayoutId = $this->db->lastInsertId();
                
                $rVal = $this->db->prepare('SELECT quickpublish FROM op_layouts WHERE id = :id');
                $rVal->setFetchMode(PDO::FETCH_OBJ);
                $rVal->execute(array('id' => $layoutId));
                $layoutData = $rVal->fetch();

                # Create layout collection
                $rVal = $this->db->prepare('INSERT INTO op_layout_collections (tagID, parent, position, plugin_id, plugin_child_id) VALUES (:tag, :parent, 0, :pid, :pcid)');
                $rVal->execute(array('tag' => $layoutData->quickpublish, 'parent' => $newLayoutId, 'pid' => opPlugin::getIdByName(__CLASS__), 'pcid' => $documentId));

                # Create menu item
                $internalUrlManager = new opMenuURLManager();
                list($menu, $menuitem) =  (strpos($_POST['quickpublish_menu'], '.') > 0) ? explode('.', $_POST['quickpublish_menu']) : array(0,0);
                $rVal = $this->db->prepare('SELECT position FROM op_menu_items WHERE menu_parent = :mid AND parent = :id ORDER BY position DESC LIMIT 0,1');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('mid' => $menu, 'id' => $menuitem));
                $rVal = $rVal->fetch();
                $position    = $rVal['position'];
                
                $itemURL     = $_POST['name'];
                $itemAlias   = opMenu::buildURL($menuitem, 0, $itemURL, $menu, true);
                $url         = opMenu::buildURL($menuitem, 0, $itemURL, $menu);
                $urlID       = $internalUrlManager->registerURL($url);

                $rVal = $this->db->prepare('INSERT INTO op_menu_items (name, hint, alias_override, alias, parent, menu_parent, position, type, home, url, layout_id, enabled, hide, created) VALUES (:name, :hint, :alias_override, :alias, :parent, :menu_parent, :position, :type, :home, :url, :layout_id, :enabled, :hide, NOW())');
                $rVal->execute(array('name' => $_POST['name'], 'hint' => $_POST['name'], 'alias_override' => 0, 'alias' => $itemAlias, 'parent' => $menuitem, 'menu_parent' => $menu, 'position' => $position+1, 'type' => 1, 'home' => 0, 'url' => $urlID, 'layout_id' => $newLayoutId, 'enabled' => 1, 'hide' => 0));

                opSystem::Msg(opTranslation::getTranslation('_document_published', __CLASS__), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opDocuments');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }

        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opDocuments.documentNew.js'));

        return $template;
    }

    public function documentNew() {
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/documents.png', opTranslation::getTranslation('_new_document', __CLASS__).' | '.opTranslation::getTranslation('_documents', __CLASS__));
        $aForm->setAction('/admin/opDocuments/documentNew');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opDocuments');

        $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_document_title', __CLASS__), 100);
        $tBox->addValidator(new opFormValidateStringLength(1, 100));
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextarea('html', '', false);
        $tBox->setSanitize(false);
        $aForm->addElement($tBox);

        $catID = (isset($_SESSION['opDocuments_sort'])) ? $_SESSION['opDocuments_sort'] : 0;
        $hBox = new opFormElementHidden('cat_id', 'cat_id');
        $hBox->setValue($catID);
        $aForm->addElement($hBox);

        if (isset($_POST['html'])) {
            $validForm = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($validForm) {
                $this->documentMapper->addElements($aForm->getElements());
                $this->documentMapper->insert();

                opSystem::Msg(opTranslation::getTranslation('_document_added', __CLASS__), opSystem::SUCCESS_MSG);
                opSystem::redirect('/opDocuments');
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }

        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opDocuments.documentNew.js'));

        return $template;
    }

    public function documentEdit() {
        if (isset($_POST['docID'])) {
            $docID = $_POST['docID'];
        } else {
            $docID = (isset($this->args[0])) ? $this->args[0] : 0;
        }

        $this->documentMapper->setRowID($docID);
        $documentData = $this->documentMapper->fetchRow();
        if ($documentData !== false) {
            $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/documents.png', opTranslation::getTranslation('_document_edit', __CLASS__).' | '.opTranslation::getTranslation('_documents', __CLASS__));
            $aForm->setAction('/admin/opDocuments/documentEdit/'.$docID);
            $aForm->setMethod('post');
            $aForm->setCancelLink('/admin/opDocuments');
            $aForm->setSaveAndClose(true);

            $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_document_title', __CLASS__), 100);
            $tBox->addValidator(new opFormValidateStringLength(1, 100));
            $tBox->setValue($documentData->name);
            $aForm->addElement($tBox);

            $tBox = new opFormElementTextarea('html', '', false);
            $tBox->setSanitize(false);
            $tBox->setValue($documentData->html);
            $aForm->addElement($tBox);

            if (isset($_POST['html'])) {
                $validForm = $aForm->isValid($_POST);
                $template = new opHtmlTemplate($aForm->render());
                if ($validForm) {
                    $this->documentMapper->addElements($aForm->getElements());
                    $this->documentMapper->update();

                    # Notify observer that document changed so that cache can be updated
                    $this->updateLastModified(opPlugin::getIdByName(__CLASS__), $docID);

                    opSystem::Msg(opTranslation::getTranslation('_document_saved', __CLASS__), opSystem::SUCCESS_MSG);
                    if ($aForm->saveAndClose()) {
                        opSystem::redirect('/opDocuments');
                    } else {
                        opSystem::redirect('/opDocuments/documentEdit/'.$docID);
                    }
                }
            } else {
                $template = new opHtmlTemplate($aForm->render());
            }

            $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opDocuments.documentNew.js'));

            return $template;
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_document_id', __CLASS__), opSystem::ERROR_MSG);
            opSystem::redirect('/opDocuments');
        }
    }

    public function documentCopy() {
        $docID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $this->documentMapper->setRowID($docID);
        $documentData = $this->documentMapper->fetchRow();
        if ($documentData !== false) {
            $element = new opFormElementHidden('name', 'name');
            $element->setValue($documentData->name.'_copy');
            $this->documentMapper->addElement($element);

            $element = new opFormElementHidden('html', 'html');
            $element->setValue($documentData->html);
            $this->documentMapper->addElement($element);

            $element = new opFormElementHidden('cat_id', 'cat_id');
            $element->setValue($documentData->cat_id);
            $this->documentMapper->addElement($element);

            $this->documentMapper->insert();

            opSystem::Msg(sprintf(opTranslation::getTranslation('_document_copied', __CLASS__), '&quot;'.$documentData->name.'&quot;'), opSystem::SUCCESS_MSG);
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_document_id', __CLASS__), opSystem::ERROR_MSG);
        }
        opSystem::redirect('/opDocuments');
    }

    public function documentDelete() {
        $deleteArr = array();
        if (isset($this->args[0]) && is_numeric($this->args[0])) {
            $deleteArr[$this->args[0]] = $this->args[0];
        } else if (isset($_POST)) {
            $deleteArr = $_POST;
        }
        foreach ($deleteArr as $k => $v) {
            if (is_numeric($k)) {
                $this->documentMapper->setRowID($k);
                $documentData = $this->documentMapper->fetchRow();
                if ($documentData !== false) {
                    if (! opLayout::isContentAssigned(__CLASS__, $k)) {
                        $this->documentMapper->delete();
                    }
                }
            }
        }
        opSystem::redirect('/opDocuments');
    }

    public function documentMove() {
        $catID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $docID = (isset($this->args[1]) && is_numeric($this->args[1])) ? $this->args[1] : 0;
        if ($catID >= 0 && $docID > 0) {
            $rVal = $this->db->prepare('SELECT op_document_categories.name AS catName, op_documents.* FROM op_documents LEFT JOIN op_document_categories ON op_document_categories.id = op_documents.cat_id WHERE op_documents.id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $docID));
            $documentData = $rVal->fetch();

            $rVal = $this->db->prepare('SELECT * FROM op_document_categories WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $catID));
            $categoryData = $rVal->fetch();

            $rVal = $this->db->prepare('UPDATE op_documents SET cat_id = :catID WHERE id = :id');
            $rVal->execute(array('catID' => $catID, 'id' => $docID));

            $catFrom = ($documentData['cat_id'] == 0) ? opTranslation::getTranslation('_uncategorized', __CLASS__) : $documentData['catName'];
            $catTo   = ($catID == 0) ? opTranslation::getTranslation('_uncategorized', __CLASS__) : $categoryData['name'];

            opSystem::Msg(sprintf(opTranslation::getTranslation('_document_moved', __CLASS__), '<strong>&quot;'.$documentData['name'].'&quot;</strong>', '<strong>&quot;'.$catFrom.'&quot;</strong>', '<strong>&quot;'.$catTo.'&quot;</strong>'), opSystem::SUCCESS_MSG);
        } else {
            opSystem::Msg(opTranslation::getTranslation('_document_move_error_msg', __CLASS__), opSystem::ERROR_MSG);
        }
        opSystem::redirect('/opDocuments');
    }
    
    public static function getContentNameById($id) {
        if (is_numeric($id)) {
            $documentMapper = new opFormDataMapper(opSystem::getDatabaseInstance());
            $documentMapper->setTable('op_documents');
            $documentMapper->setFieldIDName('id');
            $documentMapper->setRowID($id);
            $documentData = $documentMapper->fetchRow();
            if ($documentData !== false) {
                return $documentData->name;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function getContentEditPath() {
        return '/admin/opDocuments/documentEdit/';
    }

    public static function getContentList() {
        $db = opSystem::getDatabaseInstance();
        $contentList = new opContentList();
        $contentGroup = new opContentGroup('Uncategorized');
        $rVal = $db->query('SELECT * FROM op_documents WHERE cat_id = 0 ORDER BY name ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($rVal->fetchAll() as $v) {
            $contentGroup->addElement(new opContentElement($v['id'], $v['name']));
        }
        $contentList->addElement($contentGroup);

        $rVal = $db->query('SELECT * FROM op_document_categories WHERE parent = 0 ORDER BY position ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($rVal->fetchAll() as $v) {
            $contentGroup = new opContentGroup($v['name']);
            self::buildContentListTree($contentGroup, $v);
            $contentList->addElement($contentGroup);
        }
        return $contentList;
    }

    public static function getIcon() {
        return self::getRelativePath(__CLASS__).'icons/documents.png';
    }

    public static function getConfig() {
        return simplexml_load_file(self::getFullPath(__CLASS__).'opDocuments.xml');
    }

    public static function install() {
        $sqlImport = new opSQLImport(opSystem::getDatabaseInstance());

        # Import tables
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opDocuments.install.sql')) { return false; };

        return true;
    }

    protected function orderRecursive($editId, $arr, $parent, $indent, &$retArr = array(), $indentIncrease = 3) {
        foreach ($arr as $v) {
            if ($v['id'] != $editId) {
                if ($v['parent'] == $parent) {
                    $v['name'] = $this->makeSpaces($indent).$v['name'];
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

    protected function makeSpaces($n) {
        $spaces = '';
        for ($x = 0; $x <= $n; $x++) {
            $spaces .= '&nbsp;';
        }
        return $spaces;
    }

    protected function orderRecursiveAsULForCat($arr, $parent, &$retVal = '', $padding = 0) {
        foreach ($arr as $v) {
            if ($v['parent'] == $parent) {
                $retVal .= '<li><span class="sortChk"><input type="checkbox" name="delete[]" value="'.$v['id'].'" /></span><span class="sortTitle"><a href="/admin/opDocuments/categoryEdit/'.$v['id'].'" style="padding-left:'.$padding.'px;" title="'.sprintf(opTranslation::getTranslation('_category_edit', __CLASS__), '&quot;'.$v['name'].'&quot;').'">'.$v['name'].'</a></span>';
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

    protected function orderRecursiveAsULForIndex($arr, $parent, $sortBy, &$retVal = '', $padding = 10) {
        foreach ($arr as $v) {
            if ($v['parent'] == $parent) {
                $retVal .= '<div class="droppable" id="'.$v['id'].'" style="padding-left:'.$padding.'px;'.(($sortBy == $v['id']) ? 'font-weight:bold' : '').'"><img src="'.self::getRelativePath(__CLASS__).'icons/clear-folder'.(($sortBy == $v['id']) ? '-open' : '').'.png" class="table-icon" /> <a href="/admin/opDocuments/adminIndexSort/'.$v['id'].'">'.$v['name'].'</a></div>';
                foreach ($arr as $r) {
                    if ($v['id'] == $r['parent']) {
                        $retVal .= '<div class="wrap" id="wrap_'.$v['id'].'">';
                        $this->orderRecursiveAsULForIndex($arr, $v['id'], $sortBy, $retVal, $padding+10);
                        $retVal .= '</div>';
                        break;
                    }
                }
            }
        }
        return $retVal;
    }

    protected static function buildContentListTree(opContentGroup &$contentGroup, $item) {
        $db = opSystem::getDatabaseInstance();
        $rVal = $db->prepare('SELECT * FROM op_document_categories WHERE parent = :id ORDER BY position ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $item['id']));
        foreach ($rVal->fetchAll() as $v) {
            $category = new opContentGroup($v['name']);
            self::buildContentListTree($category, $v);
            $contentGroup->addElement($category);
        }
        
        $rVal = $db->prepare('SELECT * FROM op_documents WHERE cat_id = :id ORDER BY name ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $item['id']));
        foreach ($rVal->fetchAll() as $v) {
            $contentGroup->addElement(new opContentElement($v['id'], $v['name']));
        }
        return;
    }
}
?>