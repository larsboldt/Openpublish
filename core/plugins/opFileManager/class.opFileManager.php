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
class opFileManager extends opPluginBase {
    protected $baseDir, $tempDir, $cacheDir, $storeDir, $systemConfiguration;

    protected function initialize() {
        $this->systemConfiguration = opSystem::getSystemConfiguration();
        
        $this->baseDir  = '/files/';
        $this->tempDir  = DOCUMENT_ROOT.$this->baseDir.'temp/';
        $this->cacheDir = DOCUMENT_ROOT.$this->baseDir.'cache/';
        $this->storeDir = DOCUMENT_ROOT.$this->baseDir.'store/';
        $this->performCleanUpAndRestore();
    }

    public static function getConfig() {
        return simplexml_load_file(self::getFullPath(__CLASS__).'opFileManager.xml');
    }

    public function adminIndex() {
        $folderID = (isset($_SESSION['opFileManager_folder'])) ? $_SESSION['opFileManager_folder'] : 0;

        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opFileManager.index.php');
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));

        $rVal = $this->db->query('SELECT * FROM op_filemanager_folders ORDER BY parent ASC, position ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template->set('opFolders', $this->orderRecursiveAsULForIndex($rVal->fetchAll(), 0, $folderID));

        $rVal = $this->db->prepare('SELECT * FROM op_filemanager_filemap WHERE parent = :parent');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('parent' => $folderID));
        $template->set('opFiles', $rVal->fetchAll());

        $template->set('folderID', $folderID);
        $template->set('opPluginName', get_class($this));

        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opFileManager.index.js'));

        return $template;
    }

    public function adminIndexSort() {
        $_SESSION['opFileManager_folder'] = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        opSystem::redirect('/opFileManager');
    }

    public function folderIndex() {
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opFileManager.folderIndex.php');
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));

        $rVal = $this->db->query('SELECT * FROM op_filemanager_folders ORDER BY parent ASC, position ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template->set('opFolders', $this->orderRecursiveAsULForFolders($rVal->fetchAll(), 0));

        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opFileManager.folderIndex.js'));
        $template->set('opPluginName', get_class($this));
        return $template;
    }

    public function folderNew() {
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/clear-folder-open-image.png', opTranslation::getTranslation('_new_folder', get_class($this)).' | '.opTranslation::getTranslation('_files', get_class($this)));
        $aForm->setAction('/admin/opFileManager/folderNew');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opFileManager/folderIndex');

        $sBox = new opFormElementSelect('parent', opTranslation::getTranslation('_parent_folder', get_class($this)));
        $sBox->addOption('0', opTranslation::getTranslation('_fileserver', get_class($this)));
        $rVal = $this->db->query('SELECT * FROM op_filemanager_folders ORDER BY parent ASC, position ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($this->orderRecursive($rVal->fetchAll(), 0, 3) as $v) {
            $sBox->addOption($v['id'], $v['name']);
        }
        $aForm->addElement($sBox);

        $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_folder_name', get_class($this)), 40);
        $tBox->addValidator(new opFormValidateStringLength(1,40));
        //$tBox->addValidator(new opFormValidateDirectoryName());
        $aForm->addElement($tBox);

        if (isset($_POST['name'])) {
            $isValid = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($isValid) {
                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_folders WHERE parent = :parent AND name = :name');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('parent' => $_POST['parent'], 'name' => $_POST['name']));
                if ($rVal->fetchColumn() <= 0) {
                    $rVal = $this->db->prepare('INSERT INTO op_filemanager_folders (name, parent, position) VALUES (:name, :parent, 0)');
                    $rVal->execute(array('parent' => $_POST['parent'], 'name' => $_POST['name']));
                    opSystem::Msg(opTranslation::getTranslation('_folder_created', get_class($this)), opSystem::SUCCESS_MSG);
                    opSystem::redirect('/opFileManager/folderIndex');
                } else {
                    opSystem::Msg(opTranslation::getTranslation('_folder_exists_warn_msg', get_class($this)), opSystem::ERROR_MSG);
                }
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }

        return $template;
    }

    public function folderEdit() {
        $folderID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_folders WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $folderID));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_filemanager_folders WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $folderID));
            $folderData = $rVal->fetch();

            $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/clear-folder-open-image.png', opTranslation::getTranslation('_edit_folder', get_class($this)).' | '.opTranslation::getTranslation('_files', get_class($this)));
            $aForm->setAction('/admin/opFileManager/folderEdit/'.$folderID);
            $aForm->setMethod('post');
            $aForm->setCancelLink('/admin/opFileManager/folderIndex');

            $sBox = new opFormElementSelect('parent', opTranslation::getTranslation('_parent_folder', get_class($this)));
            $sBox->addOption('0', opTranslation::getTranslation('_fileserver', get_class($this)));
            $rVal = $this->db->query('SELECT * FROM op_filemanager_folders ORDER BY parent ASC, position ASC');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($this->orderRecursive($rVal->fetchAll(), 0, 3) as $v) {
                $sBox->addOption($v['id'], $v['name']);
            }
            $sBox->setValue($folderData['parent']);
            $aForm->addElement($sBox);

            $tBox = new opFormElementTextbox('name', opTranslation::getTranslation('_folder_name', get_class($this)), 40);
            $tBox->addValidator(new opFormValidateStringLength(1,40));
            //$tBox->addValidator(new opFormValidateDirectoryName());
            $tBox->setValue($folderData['name']);
            $aForm->addElement($tBox);

            if (isset($_POST['name'])) {
                $isValid = $aForm->isValid($_POST);
                $template = new opHtmlTemplate($aForm->render());
                if ($isValid) {
                    $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_folders WHERE id != :id AND name = :name');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal->execute(array('id' => $folderID, 'name' => $_POST['name']));
                    if ($rVal->fetchColumn() <= 0) {
                        $rVal = $this->db->prepare('UPDATE op_filemanager_folders SET name = :name, parent = :parent WHERE id = :id');
                        $rVal->execute(array('parent' => $_POST['parent'], 'name' => $_POST['name'], 'id' => $folderID));
                        opSystem::Msg(opTranslation::getTranslation('_folder_updated', get_class($this)), opSystem::SUCCESS_MSG);
                        opSystem::redirect('/opFileManager/folderIndex');
                    } else {
                        opSystem::Msg(opTranslation::getTranslation('_folder_exists_warn_msg', get_class($this)), opSystem::ERROR_MSG);
                    }
                }
            } else {
                $template = new opHtmlTemplate($aForm->render());
            }
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_folder_id', get_class($this)), opSystem::ERROR_MSG);
            opSystem::redirect('/opFileManager/folderIndex');
        }
        return $template;
    }

    public function folderDelete() {
        if (isset($_POST['delete'])) {
            foreach ($_POST['delete'] as $v) {
                $this->deleteRecursively($v);
            }
            opSystem::Msg(opTranslation::getTranslation('_folders_deleted_msg', get_class($this)), opSystem::SUCCESS_MSG);
        } else {
            opSystem::Msg(opTranslation::getTranslation('_select_folders_before_delete', get_class($this)), opSystem::INFORM_MSG);
        }
        opSystem::redirect('/opFileManager/folderIndex');
    }

    public function folderSort() {
        if (isset($this->args[0]) && is_numeric($this->args[0])) {
            $selectedFolder = $this->args[0];
            $_SESSION['opFileManager_folder'] = $this->args[0];
        } else {
            $selectedFolder = 0;
        }
        if (isset($_POST['serialized'])) {
            if (! empty($_POST['serialized'])) {
                $serialized = (isset($_POST['serialized'])) ? explode(',', $_POST['serialized']) : array();
                $i = 0;
                foreach ($serialized as $k => $v) {
                    $rVal = $this->db->prepare('UPDATE op_filemanager_folders SET position = :pos WHERE id = :id');
                    $rVal->execute(array('pos' => $i, 'id' => $v));
                    $i++;
                }

                opSystem::Msg(opTranslation::getTranslation('_folder_order_saved', get_class($this)), opSystem::SUCCESS_MSG);
            } else {
                opSystem::Msg(opTranslation::getTranslation('_no_folder_selected', get_class($this)), opSystem::ERROR_MSG);
            }
        }

        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opFileManager.folderSort.php');
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));

        $rVal = $this->db->query('SELECT * FROM op_filemanager_folders WHERE parent > 0 GROUP BY parent');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $folderArr = array();
        foreach ($rVal->fetchAll() as $v) {
            $rVal = $this->db->query('SELECT * FROM op_filemanager_folders WHERE id = '.$v['parent']);
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $folderArr[] = $rVal->fetch();
        }
        $template->set('folders', $folderArr);

        $rVal = $this->db->prepare('SELECT * FROM op_filemanager_folders WHERE parent = :parent ORDER BY position ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('parent' => $selectedFolder));
        $template->set('childsOfParent', $rVal->fetchAll());
        $template->set('selectedFolder', $selectedFolder);

        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opFileManager.folderSort.js'));
        $template->set('opPluginName', get_class($this));
        return $template;
    }

    public function fileUpload() {
        if (isset($_POST['folder'])) {
            $msg = array();
            $tags = (isset($_POST['tags'])) ? $_POST['tags'] : false;
            foreach ($_FILES as $file) {
                if (!empty($file['name'])) {
                    $fileStatus = $this->storeFile($file, $_POST['folder'], $tags);
                    if ($fileStatus === 1) {
                        $msg[] = '&bull; '.sprintf(opTranslation::getTranslation('_file_upload_fail_dir', get_class($this)), '&quot;'.$file['name'].'&quot;');
                    } else if ($fileStatus === 2) {
                        $msg[] = '&bull; '.sprintf(opTranslation::getTranslation('_file_upload_fail_file_exists', get_class($this)), '&quot;'.$file['name'].'&quot;');
                    } else if ($fileStatus === 3) {
                        $msg[] = '&bull; '.sprintf(opTranslation::getTranslation('_file_upload_fail_move', get_class($this)), '&quot;'.$file['name'].'&quot;');
                    } else {
                        $msg[] = '&bull; '.sprintf(opTranslation::getTranslation('_file_upload_success', get_class($this)), '&quot;'.$file['name'].'&quot;');
                    }
                }
            }
            if (count($msg) > 0) {
                opSystem::Msg(implode('<br />', $msg), opSystem::INFORM_MSG);
            } else {
                opSystem::Msg(opTranslation::getTranslation('_no_file_selected_error_msg', get_class($this)), opSystem::ERROR_MSG);
            }
        }

        if (isset($this->args[0]) && is_numeric($this->args[0])) {
            $selectedFolder = $this->args[0];
            $_SESSION['opFileManager_folder'] = $this->args[0];
        } else {
            $selectedFolder = (isset($_SESSION['opFileManager_folder'])) ? $_SESSION['opFileManager_folder'] : 0;
        }
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opFileManager.fileUpload.php');
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));

        $rVal = $this->db->query('SELECT * FROM op_filemanager_folders ORDER BY parent ASC, position ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template->set('folders', $this->orderRecursive($rVal->fetchAll(), 0, 3));
        $template->set('opPluginName', get_class($this));
        $template->set('selectedFolder', $selectedFolder);

        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opFileManager.fileUpload.js'));

        return $template;
    }

    public function fileEdit() {
        $fID = (isset($this->args[0])) ? $this->args[0] : false;
        if ($fID !== false) {
            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_filemap WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $fID));
            if ($rVal->fetchColumn() > 0) {
                $rVal = $this->db->prepare('SELECT * FROM op_filemanager_filemap WHERE id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $fID));
                $fileData = $rVal->fetch();
                $file = pathinfo($fileData['filename']);

                $rVal = $this->db->prepare('SELECT op_filemanager_tags.tag, op_filemanager_tags_to_file.* FROM op_filemanager_tags_to_file LEFT JOIN op_filemanager_tags ON op_filemanager_tags.id = op_filemanager_tags_to_file.tag_id WHERE op_filemanager_tags_to_file.file_id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $fID));
                $tagData = $rVal->fetchAll();
                
                $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/image--pencil.png', opTranslation::getTranslation('_edit_file', get_class($this)).' | '.opTranslation::getTranslation('_files', get_class($this)));
                $aForm->setAction('/admin/opFileManager/fileEdit/'.$fID);
                $aForm->setMethod('post');
                $aForm->setCancelLink('/admin/opFileManager');

                $hBox = new opFormElementTextheader('_file_info', opTranslation::getTranslation('_file_info', get_class($this)).' ('.$fileData['filename'].')');
                $aForm->addElement($hBox);

                $tBox = new opFormElementTextbox('filename', opTranslation::getTranslation('_file_name', get_class($this)), 50);
                $tBox->addValidator(new opFormValidateStringLength(1, 50));
                $tBox->setValue($file['filename']);
                $aForm->addElement($tBox);

                $tBox = new opFormElementTagList('taglist', opTranslation::getTranslation('_file_tags', get_class($this)));
                $tBox->setTagBtnTitle(opTranslation::getTranslation('_add_tag', get_class($this)));
                $tBox->setTagMsg(opTranslation::getTranslation('_tag_files_for_later_use', get_class($this)));
                foreach ($tagData as $tag) {
                    $tBox->addTag($tag['tag']);
                }
                $aForm->addElement($tBox);

                if (isset($_POST['filename'])) {
                    $isValid = $aForm->isValid($_POST);
                    $template = new opHtmlTemplate($aForm->render());
                    if ($isValid) {
                        $fileName = opRegexLib::rewriteFileName($_POST['filename']).'.'.$file['extension'];
                        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_filemap WHERE filename = :fn AND parent = :parent AND id != :id');
                        $rVal->setFetchMode(PDO::FETCH_ASSOC);
                        $rVal->execute(array('fn' => $fileName, 'parent' => $fileData['parent'], 'id' => $fID));
                        if ($rVal->fetchColumn() <= 0) {
                            if (! file_exists(DOCUMENT_ROOT.$fileData['filepath'].$fileName.'.'.$file['extension'])) {
                                if (rename(DOCUMENT_ROOT.$fileData['filepath'].$fileData['filename'],
                                           DOCUMENT_ROOT.$fileData['filepath'].$fileName)) {
                                    $rVal = $this->db->prepare('UPDATE op_filemanager_filemap SET filename = :fn WHERE id = :id');
                                    $rVal->execute(array('fn' => $fileName, 'id' => $fID));

                                    $tags = (isset($_POST['taglist'])) ? $_POST['taglist'] : false;
                                    if (is_array($tags)) {
                                        foreach ($tagData as $tagDataVal) {
                                            if (! in_array($tagDataVal['tag'], $tags, true)) {
                                                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_tags_to_file WHERE tag_id = :id');
                                                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                                                $rVal->execute(array('id' => $tagDataVal['tag_id']));
                                                if ($rVal->fetchColumn() <= 1) {
                                                    $rVal = $this->db->prepare('DELETE FROM op_filemanager_tags WHERE id = :id');
                                                    $rVal->execute(array('id' => $tagDataVal['tag_id']));
                                                }
                                                $rVal = $this->db->prepare('DELETE FROM op_filemanager_tags_to_file WHERE tag_id = :tag_id AND file_id = :file_id');
                                                $rVal->execute(array('tag_id' => $tagDataVal['tag_id'], 'file_id' => $fID));
                                            }
                                        }
                                        foreach ($tags as $tag) {
                                            $tag = trim($tag);
                                            if (strlen($tag) > 0) {
                                                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_tags WHERE tag = :tag');
                                                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                                                $rVal->execute(array('tag' => $tag));
                                                if ($rVal->fetchColumn() > 0) {
                                                    $rVal = $this->db->prepare('SELECT * FROM op_filemanager_tags WHERE tag = :tag');
                                                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                                                    $rVal->execute(array('tag' => $tag));
                                                    $rVal = $rVal->fetch();
                                                    $tagID = $rVal['id'];

                                                    $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_tags_to_file WHERE tag_id = :tag_id AND file_id = :file_id');
                                                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                                                    $rVal->execute(array('tag_id' => $tagID, 'file_id' => $fID));
                                                    if ($rVal->fetchColumn() > 0) {
                                                        continue;
                                                    }
                                                } else {
                                                    $rVal = $this->db->prepare('INSERT INTO op_filemanager_tags (tag) VALUES (:tag)');
                                                    $rVal->execute(array('tag' => $tag));
                                                    $tagID = $this->db->lastInsertId();
                                                }
                                                $rVal = $this->db->prepare('INSERT INTO op_filemanager_tags_to_file (tag_id, file_id) VALUES (:tag_id, :file_id)');
                                                $rVal->execute(array('tag_id' => $tagID, 'file_id' => $fID));
                                            }
                                        }
                                    }

                                    opSystem::Msg(opTranslation::getTranslation('_file_updated', get_class($this)), opSystem::SUCCESS_MSG);
                                    opSystem::redirect('/opFileManager');
                                } else {
                                    opSystem::Msg(opTranslation::getTranslation('_file_rename_error_msg', get_class($this)), opSystem::ERROR_MSG);
                                }
                            } else {
                                opSystem::Msg(opTranslation::getTranslation('_file_rename_file_exists_error_msg', get_class($this)), opSystem::ERROR_MSG);
                            }
                        } else {
                            opSystem::Msg(opTranslation::getTranslation('_file_name_exists_in_folder_error_msg', get_class($this)), opSystem::ERROR_MSG);
                        }
                    }
                } else {
                    $template = new opHtmlTemplate($aForm->render());
                }

                return $template;
            } else {
                opSystem::Msg(opTranslation::getTranslation('_unknown_file_id', get_class($this)), opSystem::ERROR_MSG);
                opSystem::redirect('/opFileManager');
            }
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_file_id', get_class($this)), opSystem::ERROR_MSG);
            opSystem::redirect('/opFileManager');
        }
    }

    public function fileDelete() {
        $fileID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        if ($fileID > 0) {
            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_filemap WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $fileID));
            if ($rVal->fetchColumn() > 0) {
                $rVal = $this->db->prepare('SELECT * FROM op_filemanager_filemap WHERE id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $fileID));
                $fileData = $rVal->fetch();

                # DELETE TAGS
                $rVal = $this->db->prepare('SELECT op_filemanager_tags.tag, op_filemanager_tags_to_file.* FROM op_filemanager_tags_to_file LEFT JOIN op_filemanager_tags ON op_filemanager_tags.id = op_filemanager_tags_to_file.tag_id WHERE op_filemanager_tags_to_file.file_id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $fileID));
                $tagData = $rVal->fetchAll();

                foreach ($tagData as $tagDataVal) {
                    $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_tags_to_file WHERE tag_id = :id');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal->execute(array('id' => $tagDataVal['tag_id']));
                    if ($rVal->fetchColumn() <= 1) {
                        $rVal = $this->db->prepare('DELETE FROM op_filemanager_tags WHERE id = :id');
                        $rVal->execute(array('id' => $tagDataVal['tag_id']));
                    }
                    $rVal = $this->db->prepare('DELETE FROM op_filemanager_tags_to_file WHERE tag_id = :tag_id AND file_id = :file_id');
                    $rVal->execute(array('tag_id' => $tagDataVal['tag_id'], 'file_id' => $fileID));
                }

                # DELETE FILE
                if (is_file(DOCUMENT_ROOT.$fileData['filepath'].$fileData['filename'])) {
                    $f = opFileFactory::identify(DOCUMENT_ROOT.$fileData['filepath'].$fileData['filename']);
                    if ($f instanceof opGraphicsFile) {
                        $c = new opFileGraphicsCache($f, 0);
                        $c->deleteCachedFile();
                    } else {
                        $c = new opFileCache($f);
                        $c->deleteCachedFile();
                    }
                    @unlink(DOCUMENT_ROOT.$fileData['filepath'].$fileData['filename']);
                    opSystem::Msg(opTranslation::getTranslation('_file_deleted', get_class($this)), opSystem::SUCCESS_MSG);
                } else {
                    opSystem::Msg(opTranslation::getTranslation('_file_not_found', get_class($this)), opSystem::ERROR_MSG);
                }
            } else {
                opSystem::Msg(opTranslation::getTranslation('_unknown_file_id', get_class($this)), opSystem::ERROR_MSG);
            }
        }
        opSystem::redirect('/opFileManager');
    }

    public function fileMove() {
        $folderID   = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $fileID     = (isset($this->args[1]) && is_numeric($this->args[1])) ? $this->args[1] : 0;
        if ($folderID >= 0 && $fileID > 0) {
            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_filemap WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $fileID));
            if ($rVal->fetchColumn() > 0) {
                $rVal = $this->db->prepare('SELECT * FROM op_filemanager_filemap WHERE id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $fileID));
                $fileData = $rVal->fetch();

                if ($folderID == 0) {
                    $folderData = array('name' => opTranslation::getTranslation('_fileserver', get_class($this)));
                } else {
                    $rVal = $this->db->prepare('SELECT * FROM op_filemanager_folders WHERE id = :id');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal->execute(array('id' => $folderID));
                    $folderData = $rVal->fetch();
                }

                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_filemap WHERE filename = :filename AND parent = :parent');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('filename' => $fileData['filename'], 'parent' => $folderID));
                if ($rVal->fetchColumn() <= 0) {
                    $rVal = $this->db->prepare('UPDATE op_filemanager_filemap SET parent = :parent WHERE id = :id');
                    $rVal->execute(array('parent' => $folderID, 'id' => $fileID));
                    opSystem::Msg(sprintf(opTranslation::getTranslation('_file_moved', get_class($this)), '<strong>&quot;'.$fileData['filename'].'&quot;</strong>', '<strong>&quot;'.$folderData['name'].'&quot;</strong>'), opSystem::SUCCESS_MSG);
                } else {
                    opSystem::Msg(sprintf(opTranslation::getTranslation('_file_move_fail', get_class($this)), '<strong>&quot;'.$fileData['filename'].'&quot;</strong>', '<strong>&quot;'.$folderData['name'].'&quot;</strong>'), opSystem::ERROR_MSG);
                }
            } else {
                opSystem::Msg(opTranslation::getTranslation('_unknown_file', get_class($this)), opSystem::ERROR_MSG);
            }
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_file_folder_move', get_class($this)), opSystem::ERROR_MSG);
        }
        opSystem::redirect('/opFileManager');
    }

    public function graphicCrop() {
        $fileID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_filemap WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $fileID));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_filemanager_filemap WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $fileID));
            $fileData = $rVal->fetch();

            $template = new opFileTemplate(self::getFullPath(__CLASS__).'opFileManager.graphicCrop.php');
            $template->set('opPluginPath', self::getRelativePath(__CLASS__));

            $file = opFileFactory::identify(DOCUMENT_ROOT.$fileData['filepath'].$fileData['filename']);
            $template->set('file', $file);
            $template->set('fileID', $fileID);

            if (isset($_POST['cropX1'])) {
                $this->cropImage($_POST, $file, $fileID);
            }

            $this->theme->addCSS(new opCSSFile(self::getRelativePath(__CLASS__).'js/jquery.imgareaselect-0.9.1/css/imgareaselect-default.css'));
            $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/jquery.imgareaselect-0.9.1/scripts/jquery.imgareaselect.min.js'));
            $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opFileManager.graphicCrop.js'));
            $template->set('opPluginName', get_class($this));
            return $template;
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_file_id', get_class($this)), opSystem::ERROR_MSG);
            opSystem::redirect('/opFileManager');
        }
    }

    public function graphicResize() {
        $fileID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_filemap WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $fileID));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_filemanager_filemap WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $fileID));
            $fileData = $rVal->fetch();

            $template = new opFileTemplate(self::getFullPath(__CLASS__).'opFileManager.graphicResize.php');
            $template->set('opPluginPath', self::getRelativePath(__CLASS__));

            $file = opFileFactory::identify(DOCUMENT_ROOT.$fileData['filepath'].$fileData['filename']);
            $template->set('file', $file);
            $template->set('fileID', $fileID);

            if (isset($_POST['originalWidth'])) {
                $this->resizeImage($_POST, $file, $fileID);
            }

            $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opFileManager.graphicResize.js'));
            $template->set('opPluginName', get_class($this));
            return $template;
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_file_id', get_class($this)), opSystem::ERROR_MSG);
            opSystem::redirect('/opFileManager');
        }
    }

    public function graphicCopy() {
        $fileID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        if ($fileID > 0) {
            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_filemap WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $fileID));
            if ($rVal->fetchColumn() > 0) {
                $rVal = $this->db->prepare('SELECT * FROM op_filemanager_filemap WHERE id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $fileID));
                $fileData = $rVal->fetch();
                if (is_file(DOCUMENT_ROOT.$fileData['filepath'].$fileData['filename'])) {
                    $i = 1;
                    $fileInfo = pathinfo(DOCUMENT_ROOT.$fileData['filepath'].$fileData['filename']);
                    $cFile = $fileInfo['filename'].'-'.$i.'.'.$fileInfo['extension'];
                    while (is_file(DOCUMENT_ROOT.$fileData['filepath'].$cFile)) {
                        $i++;
                        $cFile = $fileInfo['filename'].'-'.$i.'.'.$fileInfo['extension'];
                    }
                    copy(DOCUMENT_ROOT.$fileData['filepath'].$fileData['filename'], DOCUMENT_ROOT.$fileData['filepath'].$cFile);
                    $rVal = $this->db->prepare('INSERT INTO op_filemanager_filemap (filename, filepath, parent) VALUES (:filename, :filepath, :parent)');
                    $rVal->execute(array('filename' => $cFile, 'filepath' => $fileData['filepath'], 'parent' => $fileData['parent']));
                    opSystem::Msg(opTranslation::getTranslation('_file_copied', get_class($this)), opSystem::SUCCESS_MSG);
                } else {
                    opSystem::Msg(opTranslation::getTranslation('_file_not_found', get_class($this)), opSystem::ERROR_MSG);
                }
            } else {
                opSystem::Msg(opTranslation::getTranslation('_unknown_file_id', get_class($this)), opSystem::ERROR_MSG);
            }
        }
        opSystem::redirect('/opFileManager');
    }

    public function graphicPreview() {
        $fileID     = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $tmpName    = (isset($this->args[1])) ? $this->args[1] : false;
        $sender     = (isset($this->args[2])) ? $this->args[2] : false;

        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opFileManager.graphicPreview.php');
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));

        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_filemap WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $fileID));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_filemanager_filemap WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $fileID));
            $fileData = $rVal->fetch();
            if (is_file(DOCUMENT_ROOT.$this->baseDir.'/temp/'.$tmpName) &&
                is_file(DOCUMENT_ROOT.$fileData['filepath'].$fileData['filename'])) {
                $rVal = $this->db->prepare('SELECT * FROM op_filemanager_filemap WHERE id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $fileID));
                $rVal = $rVal->fetch();
                $file = opFileFactory::identify(DOCUMENT_ROOT.$this->baseDir.'/temp/'.$tmpName);
                $template->set('newFile', $file);
                $template->set('oldFile', opFileFactory::identify(DOCUMENT_ROOT.$fileData['filepath'].$fileData['filename']));
                $template->set('fileID', $fileID);
                $template->set('tmpName', '/files/temp/'.$tmpName);
                switch ($sender) {
                    case 'graphicCrop':
                        $template->set('sender', 'graphicCrop');
                        break;
                    case 'graphicResize':
                        $template->set('sender', 'graphicResize');
                        break;
                    default:
                        $template->set('sender', false);
                        break;
                }
            } else {
                opSystem::Msg(opTranslation::getTranslation('_files_missing', get_class($this)), opSystem::ERROR_MSG);
                opSystem::redirect('/opFileManager');
            }
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_file_id', get_class($this)), opSystem::ERROR_MSG);
            opSystem::redirect('/opFileManager');
        }
        $template->set('opPluginName', get_class($this));
        return $template;
    }

    public function graphicPreviewSave() {
        $oldFileID   = (isset($_POST['oldFile']))     ? $_POST['oldFile']                                    : false;
        $newFilename = (isset($_POST['newFilename'])) ? opRegexLib::rewriteFileName($_POST['newFilename'])   : false;
        $tmpFile     = (isset($_POST['tmpFile']))     ? $_POST['tmpFile']                                    : false;

        if (is_numeric($oldFileID) && $newFilename && $tmpFile) {
            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_filemap WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $oldFileID));
            if ($rVal->fetchColumn() > 0) {
                $rVal = $this->db->prepare('SELECT * FROM op_filemanager_filemap WHERE id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $oldFileID));
                $fileData = $rVal->fetch();

                $originalFile = opFileFactory::identify(DOCUMENT_ROOT.$fileData['filepath'].$fileData['filename']);
                if (! file_exists(DOCUMENT_ROOT.$fileData['filepath'].$newFilename.'.'.$originalFile->getExtension())) {
                    $rVal = $this->db->prepare('INSERT INTO op_filemanager_filemap (filename, filepath, parent) VALUES (:filename, :filepath, :parent)');
                    $rVal->execute(array('filename' => $newFilename.'.'.$originalFile->getExtension(), 'filepath' => $fileData['filepath'], 'parent' => $fileData['parent']));
                } else {
                    $existingFile = opFileFactory::identify(DOCUMENT_ROOT.$fileData['filepath'].$newFilename.'.'.$originalFile->getExtension());
                    if ($existingFile instanceof opGraphicsFile) {
                        $fileCache = new opFileGraphicsCache($existingFile, 0);
                    } else {
                        $fileCache = new opFileCache($existingFile);
                    }
                    $fileCache->deleteCachedFile();
                }
                rename(DOCUMENT_ROOT.$this->baseDir.'temp/'.$tmpFile, DOCUMENT_ROOT.$fileData['filepath'].$newFilename.'.'.$originalFile->getExtension());
            }
        }

        opSystem::redirect('/opFileManager');
    }

    public function ajax() {
        $method = (isset($this->args[0])) ? $this->args[0] : false;
        if ($method !== false) {
            switch ($method) {
                case 'sanitizeTag':
                    $tagData = (isset($_POST['tagData'])) ? $_POST['tagData'] : false;
                    if ($tagData !== false) {
                        echo mb_substr(htmlspecialchars(trim($tagData), ENT_QUOTES, 'UTF-8'), 0, 30);
                    } else {
                        echo 'tagData missing';
                    }
                    break;
                default:
                    echo 'Unknown ajax request';
            }
        } else {
            echo 'Unknown ajax request';
        }
        exit();
    }

    public static function install() {
        $sqlImport = new opSQLImport(opSystem::getDatabaseInstance());

        # Import tables
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opFileManager.install.sql')) { return false; };

        return true;
    }

    protected function storeFile($file, $folder, $tags = false) {
        $pathinfo = pathinfo($file['name']);
        $newFileName = opRegexLib::rewriteFileName($pathinfo['filename']);
        $targetDir = $this->buildDir($pathinfo['extension'], $newFileName);
        if ($targetDir) {
            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_filemap WHERE filename = :filename AND parent = :parent');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('filename' => $newFileName.'.'.$pathinfo['extension'], 'parent' => $folder));
            if ($rVal->fetchColumn() <= 0) {
                if (move_uploaded_file($file['tmp_name'], $targetDir.$newFileName.'.'.$pathinfo['extension'])) {
                    chmod($targetDir.$newFileName.'.'.$pathinfo['extension'], octdec($this->systemConfiguration->file_permission));
                    $rVal = $this->db->prepare('INSERT INTO op_filemanager_filemap (filename, filepath, parent) VALUES (:filename, :filepath, :parent)');
                    $rVal->execute(array('filename' => $newFileName.'.'.$pathinfo['extension'], 'filepath' => str_replace(DOCUMENT_ROOT, '', $targetDir), 'parent' => $folder));

                    $fileID = $this->db->lastInsertId();

                    if (is_array($tags)) {
                        foreach ($tags as $tag) {
                            $tag = trim($tag);
                            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_tags WHERE tag = :tag');
                            $rVal->setFetchMode(PDO::FETCH_ASSOC);
                            $rVal->execute(array('tag' => $tag));
                            if ($rVal->fetchColumn() > 0) {
                                $rVal = $this->db->prepare('SELECT * FROM op_filemanager_tags WHERE tag = :tag');
                                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                                $rVal->execute(array('tag' => $tag));
                                $rVal = $rVal->fetch();
                                $tagID = $rVal['id'];

                                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_tags_to_file WHERE tag_id = :tag_id AND file_id = :file_id');
                                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                                $rVal->execute(array('tag_id' => $tagID, 'file_id' => $fileID));
                                if ($rVal->fetchColumn() > 0) {
                                    continue;
                                }
                            } else {
                                $rVal = $this->db->prepare('INSERT INTO op_filemanager_tags (tag) VALUES (:tag)');
                                $rVal->execute(array('tag' => $tag));
                                $tagID = $this->db->lastInsertId();
                            }
                            $rVal = $this->db->prepare('INSERT INTO op_filemanager_tags_to_file (tag_id, file_id) VALUES (:tag_id, :file_id)');
                            $rVal->execute(array('tag_id' => $tagID, 'file_id' => $fileID));
                        }
                    }

                    return $newFileName.'.'.$pathinfo['extension'];
                } else {
                    return 3;
                }
            } else {
                return 2;
            }
        } else {
            return 1;
        }
    }

    protected function buildDir($ext, $file) {
        $buildDir = DOCUMENT_ROOT.$this->baseDir.'store/'.$ext.'/';
        $year   = date('Y');
        $month  = date('m');
        $day    = date('d');
        if (is_writeable(DOCUMENT_ROOT.$this->baseDir.'store/')) {
        # Create extension dir
            if ($this->createDir($buildDir)) {
                if (is_writeable($buildDir)) {
                # Create year dir
                    if ($this->createDir($buildDir.$year)) {
                        $buildDir .= $year.'/';
                        if (is_writeable($buildDir)) {
                        # Create month dir
                            if ($this->createDir($buildDir.$month)) {
                                $buildDir .= $month.'/';
                                if (is_writeable($buildDir)) {
                                # Create day dir
                                    if ($this->createDir($buildDir.$day)) {
                                        $buildDir .= $day.'/';
                                        if (is_writeable($buildDir)) {
                                        # Check if a file with identical name exists, create numbered folder for new file to allow upload
                                            $origDir = $buildDir;
                                            $i = 1;
                                            while (is_file($buildDir.$file.'.'.$ext)) {
                                                if (! $this->createDir($origDir.$i)) {
                                                    return false;
                                                }
                                                $buildDir = $origDir.$i.'/';
                                                $i++;
                                            }

                                            if (is_writeable($buildDir)) {
                                                return $buildDir;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    protected function createDir($dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir)) {
                return chmod($dir, octdec($this->systemConfiguration->dir_permission));
            }
            return false;
        } else {
            return true;
        }
    }

    protected function cropImage($postArr, $file, $fileID) {
        $x1 = (isset($postArr['cropX1']))   ? $postArr['cropX1']  : false;
        $y1 = (isset($postArr['cropY1']))   ? $postArr['cropY1']  : false;
        $x2 = (isset($postArr['cropX2']))   ? $postArr['cropX2']  : false;
        $y2 = (isset($postArr['cropY2']))   ? $postArr['cropY2']  : false;
        $q  = (isseT($postArr['quality']))  ? $postArr['quality'] : 75;
        $x1 = ($x1 == 'false') ? false : $x1;
        $y1 = ($y1 == 'false') ? false : $y1;
        $x2 = ($x2 == 'false') ? false : $x2;
        $y2 = ($y2 == 'false') ? false : $y2;

        if ($x1 !== false && $x2 !== false && $y1 !== false && $y2 !== false) {
            $graphics = new opGraphics($file, $this->tempDir);
            $tmpName = $graphics->crop($x1, $x2, $y1, $y2, $q);
            if ($tmpName) {
                opSystem::redirect('/opFileManager/graphicPreview/'.$fileID.'/'.$tmpName.'/graphicCrop');
            } else {
                opSystem::Msg(opTranslation::getTranslation('_crop_action_failed', get_class($this)), opSystem::ERROR_MSG);
            }
        } else {
            opSystem::Msg(opTranslation::getTranslation('_crop_wrong_parameters', get_class($this)), opSystem::ERROR_MSG);
        }
    }

    protected function resizeImage($postArr, $file, $fileID) {
        $nW = (isset($postArr['newWidth']))  ? $postArr['newWidth']  : false;
        $nH = (isset($postArr['newHeight'])) ? $postArr['newHeight'] : false;
        $q  = (isset($postArr['quality']))   ? $postArr['quality']   : 75;

        if ($nW !== false && $nH !== false) {
            $graphics = new opGraphics($file, $this->tempDir);
            $tmpName = $graphics->resize($nW, $nH, $q);
            if ($tmpName) {
                opSystem::redirect('/opFileManager/graphicPreview/'.$fileID.'/'.$tmpName.'/graphicResize');
            } else {
                opSystem::Msg(opTranslation::getTranslation('_resize_action_failed', get_class($this)), opSystem::ERROR_MSG);
            }
        } else {
            opSystem::Msg(opTranslation::getTranslation('_resize_wrong_parameters', get_class($this)), opSystem::ERROR_MSG);
        }
    }

    protected function deleteRecursively($parent) {
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_folders WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $parent));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_filemanager_folders WHERE parent = :parent');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('parent' => $parent));
            foreach ($rVal->fetchAll() as $v) {
                $this->deleteRecursively($v['id']);
            }
            
            # DELETE FILE
            $rVal = $this->db->prepare('DELETE FROM op_filemanager_folders WHERE id = :id');
            $rVal->execute(array('id' => $parent));
            $rVal = $this->db->prepare('SELECT * FROM op_filemanager_filemap WHERE parent = :parent');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('parent' => $parent));
            foreach ($rVal->fetchAll() as $v) {
                unlink(DOCUMENT_ROOT.$v['filepath'].$v['filename']);

                # DELETE TAGS
                $rVal = $this->db->prepare('SELECT op_filemanager_tags.tag, op_filemanager_tags_to_file.* FROM op_filemanager_tags_to_file LEFT JOIN op_filemanager_tags ON op_filemanager_tags.id = op_filemanager_tags_to_file.tag_id WHERE op_filemanager_tags_to_file.file_id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $v['id']));
                $tagData = $rVal->fetchAll();

                foreach ($tagData as $tagDataVal) {
                    $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_filemanager_tags_to_file WHERE tag_id = :id');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal->execute(array('id' => $tagDataVal['tag_id']));
                    if ($rVal->fetchColumn() <= 1) {
                        $rVal = $this->db->prepare('DELETE FROM op_filemanager_tags WHERE id = :id');
                        $rVal->execute(array('id' => $tagDataVal['tag_id']));
                    }
                    $rVal = $this->db->prepare('DELETE FROM op_filemanager_tags_to_file WHERE tag_id = :tag_id AND file_id = :file_id');
                    $rVal->execute(array('tag_id' => $tagDataVal['tag_id'], 'file_id' => $fID));
                }
            }
        }
    }

    protected function orderRecursiveAsULForFolders($arr, $parent, &$retVal = '', $padding = 0) {
        foreach ($arr as $v) {
            if ($v['parent'] == $parent) {
                $retVal .= '<li><span class="sortChk"><input type="checkbox" class="folderParent" name="delete[]" value="'.$v['id'].'" /></span><span class="sortTitle"><a href="/admin/opFileManager/folderEdit/'.$v['id'].'" style="padding-left:'.$padding.'px;">'.$v['name'].'</a></span>';
                foreach ($arr as $r) {
                    if ($v['id'] == $r['parent']) {
                        $retVal .= '<ul>';
                        $this->orderRecursiveAsULForFolders($arr, $v['id'], $retVal, $padding+10);
                        $retVal .= '</ul>';
                        break;
                    }
                }
                $retVal .= '</li>';
            }
        }
        return $retVal;
    }

    protected function orderRecursiveAsULForIndex($arr, $parent, $sortBy, &$retVal = '', $padding = 20) {
        foreach ($arr as $v) {
            if ($v['parent'] == $parent) {
                $retVal .= '<div class="droppable" id="'.$v['id'].'" style="padding-left:'.$padding.'px;'.(($sortBy == $v['id']) ? 'font-weight:bold' : '').'"><img src="'.self::getRelativePath(__CLASS__).'icons/clear-folder'.(($sortBy == $v['id']) ? '-open' : '').'.png" class="table-icon" /> <a href="/admin/opFileManager/adminIndexSort/'.$v['id'].'">'.$v['name'].'</a></div>';
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

    protected function orderRecursive($arr, $parent, $indent, &$retArr = array(), $indentIncrease = 3) {
        foreach ($arr as $v) {
            if ($v['parent'] == $parent) {
                $v['name'] = $this->makeSpaces($indent).$v['name'];
                $retArr[] = $v;
                foreach ($arr as $r) {
                    if ($v['id'] == $r['parent']) {
                        $this->orderRecursive($arr, $v['id'], $indent+$indentIncrease, $retArr);
                        break;
                    }
                }
            }
        }
        return $retArr;
    }

    protected function makeSpaces($n) {
        $spaces = '';
        for ($x = 0; $x < $n; $x++) {
            $spaces .= '&nbsp;';
        }
        return $spaces;
    }

    protected function performCleanUpAndRestore() {
        # Check for actual files deleted and update filemap database and cache
        $rVal = $this->db->query('SELECT * FROM op_filemanager_filemap');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($rVal->fetchAll() as $f) {
            if (! is_file(DOCUMENT_ROOT.$f['filepath'].$f['filename'])) {
                $this->db->query('DELETE FROM op_filemanager_filemap WHERE id = '.$f['id']);
                @unlink(DOCUMENT_ROOT.'/files/cache/'.str_replace('/files/', '', $f['filepath']).$f['filename']);
            }
        }

        # Check for temp and cache dirs, create if missing
        if (is_writeable(DOCUMENT_ROOT.$this->baseDir)) {
            if (! $this->createDir(DOCUMENT_ROOT.$this->baseDir.'cache/')) {
                die(sprintf(opTranslation::getTranslation('_error_dir', get_class($this)), 'cache'));
            }
            if (! $this->createDir(DOCUMENT_ROOT.$this->baseDir.'temp/')) {
                die(sprintf(opTranslation::getTranslation('_error_dir', get_class($this)), 'temp'));
            }
            if (! $this->createDir(DOCUMENT_ROOT.$this->baseDir.'store/')) {
                die(sprintf(opTranslation::getTranslation('_error_dir', get_class($this)), 'store'));
            }
        }

        # Clean temp folder of files older than 3 hours
        if ($handle = opendir(DOCUMENT_ROOT.$this->baseDir.'temp/')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if (filemtime(DOCUMENT_ROOT.$this->baseDir.'temp/'.$file) < strtotime('-3 hours')) {
                        @unlink(DOCUMENT_ROOT.$this->baseDir.'temp/'.$file);
                    }
                }
            }
            closedir($handle);
        }
    }
}
?>