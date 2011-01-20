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
class opUsers extends opPluginBase {
    public static function getConfig() {
        return simplexml_load_file(self::getFullPath(__CLASS__).'opUsers.xml');
    }

    public function adminIndex() {
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opUsers.index.php');
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));

        $currentUser = new opUser($this->db, $_SESSION['opAdmin']['username'], null);
        if ($currentUser->isSuperAdmin()) {
            $rVal = $this->db->query('SELECT * FROM op_admin_users');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
        } else {
            $rVal = $this->db->query('SELECT * FROM op_admin_users WHERE superadmin = 0');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
        }
        $template->set('opUsers', $rVal->fetchAll());
        $template->set('opPluginName', get_class($this));

        return $template;
    }

    public function userNew() {
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/users.png', opTranslation::getTranslation('_new_user', get_class($this)).' | '.opTranslation::getTranslation('_users', get_class($this)));
        $aForm->setAction('/admin/opUsers/userNew');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opUsers');
        
        # Credentials
        $hBox = new opFormElementTextheader('user_credentials', opTranslation::getTranslation('_user_information', get_class($this)));
        $aForm->addElement($hBox);
        $tBox = new opFormElementTextbox('firstname', opTranslation::getTranslation('_first_name', get_class($this)), 30);
        $tBox->addValidator(new opFormValidateStringLength(2, 30));
        $aForm->addElement($tBox);
        
        $tBox = new opFormElementTextbox('lastname', opTranslation::getTranslation('_last_name', get_class($this)), 30);
        $tBox->addValidator(new opFormValidateStringLength(2, 30));
        $aForm->addElement($tBox);

        $tBox = new opFormElementTextbox('email', opTranslation::getTranslation('_email', get_class($this)), 30);
        $tBox->addValidator(new opFormValidateEmailWithTLD());
        $aForm->addElement($tBox);

        $pBox = new opFormElementPassword('password', opTranslation::getTranslation('_password', get_class($this)), 12, true, self::getRelativePath(__CLASS__).'icons/arrow-circle-double.png');
        $pBox->addValidator(new opFormValidateStringLength(6, 12));
        $aForm->addElement($pBox);

        if (isset($_POST['firstname'])) {
            $validForm = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($validForm) {
                # Check if the selected username is already in use
                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_admin_users WHERE username = :usr');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('usr' => $_POST['email']));
                if ($rVal->fetchColumn() > 0) {
                    opSystem::Msg(opTranslation::getTranslation('_email_in_use_error', get_class($this)), opSystem::ERROR_MSG);
                } else {
                    $rVal = $this->db->prepare('INSERT INTO op_admin_users (firstname, lastname, username, password) VALUES (:fn, :ln, :usr, :pwd)');
                    $rVal->execute(array('fn' => $_POST['firstname'], 'ln' => $_POST['lastname'], 'usr' => $_POST['email'], 'pwd' => $this->hashStr($_POST['password'])));

                    opSystem::Msg(opTranslation::getTranslation('_user_added_msg', get_class($this)), opSystem::SUCCESS_MSG);
                    opSystem::redirect('/opUsers');
                }
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }

        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opUsers.userNew.js'));

        return $template;
    }

    public function userEdit() {
        $currentUser = new opUser($this->db, $_SESSION['opAdmin']['username'], null);
        $userID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_admin_users WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $userID));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_admin_users WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $userID));
            $userData = $rVal->fetch();
            # Make sure non-superadmins can't edit superadmin
            if ($userData['superadmin'] == 1 && !$currentUser->isSuperAdmin()) {
                opSystem::Msg(opTranslation::getTranslation('_access_denied_error', get_class($this)), opSystem::ERROR_MSG);
                opSystem::redirect('/opUsers');
            } else {
                $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/users.png', opTranslation::getTranslation('_edit_user', get_class($this)).' | '.opTranslation::getTranslation('_users', get_class($this)));
                $aForm->setAction('/admin/opUsers/userEdit/'.$userID);
                $aForm->setMethod('post');
                $aForm->setCancelLink('/admin/opUsers');

                # Credentials
                $hBox = new opFormElementTextheader('user_credentials', opTranslation::getTranslation('_user_information', get_class($this)));
                $aForm->addElement($hBox);
                $tBox = new opFormElementTextbox('firstname', opTranslation::getTranslation('_first_name', get_class($this)), 30);
                $tBox->setValue($userData['firstname']);
                $tBox->addValidator(new opFormValidateStringLength(2, 30));
                $aForm->addElement($tBox);

                $tBox = new opFormElementTextbox('lastname', opTranslation::getTranslation('_last_name', get_class($this)), 30);
                $tBox->setValue($userData['lastname']);
                $tBox->addValidator(new opFormValidateStringLength(2, 30));
                $aForm->addElement($tBox);

                $tBox = new opFormElementTextbox('email', opTranslation::getTranslation('_email', get_class($this)), 30);
                $tBox->setValue($userData['username']);
                $tBox->addValidator(new opFormValidateEmailWithTLD());
                $aForm->addElement($tBox);

                $pBox = new opFormElementPassword('password', opTranslation::getTranslation('_password_edit', get_class($this)), 12, true, self::getRelativePath(__CLASS__).'icons/arrow-circle-double.png');
                $aForm->addElement($pBox);

                if (isset($_POST['firstname'])) {
                    $validForm = $aForm->isValid($_POST);
                    $template = new opHtmlTemplate($aForm->render());
                    if ($validForm) {
                        $updateUsers = true;
                        # Check if the selected username is already in use
                        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_admin_users WHERE id != :id AND username = :usr');
                        $rVal->setFetchMode(PDO::FETCH_ASSOC);
                        $rVal->execute(array('id' => $userID, 'usr' => $_POST['email']));
                        if ($rVal->fetchColumn() > 0) {
                            opSystem::Msg(opTranslation::getTranslation('_email_in_use_error', get_class($this)), opSystem::ERROR_MSG);
                        } else {
                            # Check password and update
                            $pwd = trim($_POST['password']);
                            if (!empty($pwd)) {
                                if (mb_strlen($pwd) >= 6 && mb_strlen($pwd) <= 12) {
                                    $rVal = $this->db->prepare('UPDATE op_admin_users SET password = :pwd WHERE id = :id');
                                    $rVal->execute(array('pwd' => $this->hashStr($pwd), 'id' => $userID));
                                } else {
                                    $updateUsers = false;
                                    opSystem::Msg(sprintf(opTranslation::getTranslation('_password_length_error', get_class($this)),6,12), opSystem::ERROR_MSG);
                                }
                            }
                            # Update users
                            if ($updateUsers) {
                                $rVal = $this->db->prepare('UPDATE op_admin_users SET firstname = :fn, lastname = :ln, username = :usr WHERE id = :id');
                                $rVal->execute(array('fn' => $_POST['firstname'], 'ln' => $_POST['lastname'], 'usr' => $_POST['email'], 'id' => $userID));
                                opSystem::Msg(opTranslation::getTranslation('_user_updated_msg', get_class($this)), opSystem::SUCCESS_MSG);
                            }
                            opSystem::redirect('/opUsers');
                        }
                    }
                } else {
                    $template = new opHtmlTemplate($aForm->render());
                }
            }

            $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opUsers.userNew.js'));
            return $template;
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_user_id', get_class($this)), opSystem::ERROR_MSG);
            opSystem::redirect('/opUsers');
        }
    }

    public function userDelete() {
        foreach ($_POST as $k => $v) {
            if (is_numeric($k)) {
                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_admin_users WHERE id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $k));
                if ($rVal->fetchColumn() > 0) {
                    $rVal = $this->db->prepare('SELECT * FROM op_admin_users WHERE id = :id');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal->execute(array('id' => $k));
                    $userData = $rVal->fetch();
                    # Make sure superadmin doesn't get deleted
                    if ($userData['superadmin'] != 1) {
                        $rVal = $this->db->prepare('DELETE FROM op_admin_users WHERE id = :id');
                        $rVal->execute(array('id' => $k));
                    }
                }
            }
        }
        opSystem::redirect('/opUsers');
    }

    public static function install() {
        $sqlImport = new opSQLImport(opSystem::getDatabaseInstance());

        # Import tables
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opUsers.install.sql')) { return false; };

        return true;
    }

    /**
     * Hashes string using sha256
     * @param string String to be hashed
     * @return string Hashed string
     */
    protected function hashStr($str) {
        return hash_hmac('sha256', $str, opSystem::getSecretKey());
    }
}
?>