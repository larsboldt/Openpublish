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
class opPlugin extends opPluginBase {
    private $pageListingLimit = 20;

    public static function getConfig() {
        return simplexml_load_file(self::getFullPath(__CLASS__).'opPlugin.xml');
    }

    public function adminIndex() {
        //$this->updatePluginRepository();

        # Fetch plugin settings
        $rVal = $this->db->query('SELECT * FROM op_plugin_config');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal = $rVal->fetch();
        $this->pageListingLimit = $rVal['page_listing_limit'];

        # Create template
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opPlugin.index.php');

        # Get installed plugins
        $rVal = $this->db->query('SELECT * FROM op_plugins WHERE core = 0 ORDER BY plugin_name');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template->set('opPlugins', $rVal->fetchAll());

        # Get plugin categories
        $rVal = $this->db->query('SELECT category FROM op_plugin_repo GROUP BY category');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $template->set('opCategories', $rVal->fetchAll());

        # Determine current page, orderBy and filter status
        $orderBy        = (isset($_SESSION['opPlugin_orderBy'])) ? $_SESSION['opPlugin_orderBy'] : false;
        $filter         = (isset($_SESSION['opPlugin_filter'])) ? $_SESSION['opPlugin_filter'] : false;
        $currentPage    = (isset($_SESSION['opPlugin_currentPage'])) ? $_SESSION['opPlugin_currentPage'] : 0;
        $currentPage    = ($currentPage < 0) ? 0 : $currentPage;

        # Determine search mode
        $pluginListing = false;
        if (isset($_SESSION['opPlugin_keyword'])) {
            # Search mode
            $pFTS = new opPluginFullTextSearch($currentPage, null, $_SESSION['opPlugin_keyword'], $orderBy, $this->db, $this->pageListingLimit);
            $template->set('opPluginRepo', $pFTS->getLimitedResult());
            $template->set('opKeyword', htmlentities($_SESSION['opPlugin_keyword'], ENT_QUOTES, 'UTF-8', false));
            $pluginListing = $pFTS->getFullResult();
        } else {
            # Full listing mode
            $args = array();
            if ($filter) {
                $sqlFilter = ' WHERE category = :category';
                $args['category'] = $filter;
            } else {
                $sqlFilter = '';
            }
            $sqlOrder = ($orderBy) ? ' ORDER BY '.$orderBy : '';

            # Dirty total count
            $rVal = $this->db->prepare('SELECT * FROM op_plugin_repo'.$sqlFilter.$sqlOrder);
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute($args);
            $pluginListing = $rVal->fetchAll();
            # Prevent showing a page that doesn't exist
            $totalPages = (count($pluginListing) > 0) ? ceil(count($pluginListing)/$this->pageListingLimit) : 0;
            $currentPage = ($currentPage > $totalPages-1) ? $totalPages-1 : $currentPage;

            # Real query with limit
            $rVal = $this->db->prepare('SELECT * FROM op_plugin_repo'.$sqlFilter.$sqlOrder.' LIMIT '.($this->pageListingLimit*$currentPage).','.$this->pageListingLimit);
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute($args);

            $template->set('opPluginRepo', $rVal->fetchAll());
            $template->set('opKeyword', false);
        }

        # Set some useful variables for the template page
        $template->set('opPluginTotal', count($pluginListing));
        $template->set('opPluginPages', ((count($pluginListing) > 0) ? ceil(count($pluginListing)/$this->pageListingLimit) : 0));
        $template->set('opCurrentPage', $currentPage);
        $template->set('opPageListingLimit', $this->pageListingLimit);
        $template->set('opFilter', (isset($_SESSION['opPlugin_filter']) ? $_SESSION['opPlugin_filter'] : false));
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('theme', $this->theme);
        $template->set('opPluginName', get_class($this));
        return $template;
    }

    public function pluginManage() {
        $pluginID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_plugins WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $pluginID));
        if ($rVal->fetchColumn() > 0) {
            $template = new opFileTemplate(self::getFullPath(__CLASS__).'opPlugin.pluginManage.php');
            $template->set('opPluginPath', self::getRelativePath(__CLASS__));
            $template->set('pluginID', $pluginID);

            $rVal = $this->db->prepare('SELECT * FROM op_plugins WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $pluginID));
            $pluginData = $rVal->fetch();

            $p = new $pluginData['plugin_name']($this->theme, null);
            $c = $p->getConfig();
            if ($c) {
                $template->set('pName', $c->name);
                $template->set('pDescription', $c->description);
                $template->set('pVersion', $c->version);
                $template->set('pAuthor', $c->author);
                $template->set('pProcessing', $c->processing_position);
                $template->set('pHasAdmin', $c->hasAdmin);
                $template->set('pAssignToLayout', $c->assignToLayout);
            } else {
                $template->set('pName', opTranslation::getTranslation('_unset', get_class($this)));
                $template->set('pDescription', opTranslation::getTranslation('_unset', get_class($this)));
                $template->set('pVersion', opTranslation::getTranslation('_unset', get_class($this)));
                $template->set('pAuthor', opTranslation::getTranslation('_unset', get_class($this)));
                $template->set('pProcessing', opTranslation::getTranslation('_unset', get_class($this)));
                $template->set('pHasAdmin', opTranslation::getTranslation('_unset', get_class($this)));
                $template->set('pAssignToLayout', opTranslation::getTranslation('_unset', get_class($this)));
            }
            $template->set('pluginCategory', $pluginData['cat_id']);
            $rVal = $this->db->query('SELECT * FROM op_create_categories ORDER BY position ASC');
            $template->set('pluginCategories', $rVal->fetchAll());
            $template->set('opPluginName', get_class($this));
            return $template;
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_plugin_id', get_class($this)), opSystem::ERROR_MSG);
            opSystem::redirect('/opPlugin');
        }
    }

    public function pluginCategorize() {
        $pluginID   = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $categoryID = (isset($this->args[1]) && is_numeric($this->args[1])) ? $this->args[1] : 0;
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_plugins WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $pluginID));
        if ($rVal->fetchColumn() > 0 && $categoryID >= 0) {
            $rVal = $this->db->prepare('UPDATE op_plugins SET cat_id = :cat_id WHERE id = :id');
            $rVal->execute(array('cat_id' => $categoryID, 'id' => $pluginID));

            opSystem::Msg(opTranslation::getTranslation('_category_updated', get_class($this)), opSystem::SUCCESS_MSG);
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_plugin_id', get_class($this)), opSystem::ERROR_MSG);
            opSystem::redirect('/opPlugin');
        }
        opSystem::redirect('/opPlugin/pluginManage/'.$pluginID);
    }

    public function pluginView() {
        return new opHtmlTemplate('view');
    }

    public function pluginSettings() {
        # Fetch plugin settings
        $rVal = $this->db->query('SELECT * FROM op_plugin_config');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal = $rVal->fetch();

        # Build form
        $aForm = new opAdminForm(self::getRelativePath(__CLASS__).'icons/puzzle.png', opTranslation::getTranslation('_settings', get_class($this)).' | '.opTranslation::getTranslation('_plugins', get_class($this)));
        $aForm->setAction('/admin/opPlugin/pluginSettings');
        $aForm->setMethod('post');
        $aForm->setCancelLink('/admin/opPlugin');

        # General settings
        $hBox = new opFormElementTextheader('General', opTranslation::getTranslation('_general', get_class($this)));
        $aForm->addElement($hBox);
        $tBox = new opFormElementTextbox('page_listing_limit', opTranslation::getTranslation('_listings_per_page', get_class($this)),3);
        $tBox->addValidator(new opFormValidateNumeric());
        $tBox->setValue($rVal['page_listing_limit']);
        $aForm->addElement($tBox);

        if (isset($_POST['page_listing_limit'])) {
            $validForm = $aForm->isValid($_POST);
            $template = new opHtmlTemplate($aForm->render());
            if ($validForm) {
                $rVal = $this->db->prepare('UPDATE op_plugin_config SET page_listing_limit = :pll');
                $rVal->execute(array('pll' => $_POST['page_listing_limit']));

                opSystem::Msg(opTranslation::getTranslation('_settings_updated', get_class($this)), opSystem::SUCCESS_MSG);
            }
        } else {
            $template = new opHtmlTemplate($aForm->render());
        }
        
        return $template;
    }

    public function pluginPage() {
        $currentPage = (isset($this->args[0])) ? $this->args[0] : 0;
        if ($currentPage) {
            $_SESSION['opPlugin_currentPage'] = $currentPage;
        } else {
            unset($_SESSION['opPlugin_currentPage']);
        }
        opSystem::redirect('/opPlugin');
    }

    public function pluginOrderBy() {
        $column = 'name';
        if (isset($this->args[0])) {
            switch ($this->args[0]) {
                case 'name':
                    $column = 'name';
                    break;
                case 'rating':
                    $column = 'rating';
                    break;
                case 'downloads':
                    $column = 'downloads';
                    break;
                default:
                    $column = 'name';
            }
        }
        $mode = (isset($this->args[1]) && strtolower($this->args[1]) == 'desc') ? 'DESC' : 'ASC';
        $_SESSION['opPlugin_orderBy'] = $column.' '.$mode;
        opSystem::redirect('/opPlugin');
    }

    public function pluginFilter() {
        $category = (isset($this->args[0])) ? $this->args[0] : false;
        if ($category) {
            $_SESSION['opPlugin_filter'] = $category;
        } else {
            unset($_SESSION['opPlugin_filter']);
        }
        unset($_SESSION['opPlugin_currentPage']);
        opSystem::redirect('/opPlugin');
    }

    public function pluginSearch() {
        $keyword = (isset($this->args[0])) ? urldecode($this->args[0]) : false;
        if ($keyword) {
            $_SESSION['opPlugin_keyword'] = $keyword;
        } else {
            unset($_SESSION['opPlugin_keyword']);
        }
        opSystem::redirect('/opPlugin');
    }

    public function pluginAdvancedControl() {
        # Check for sort post
        if (isset($_POST['serialized'])) {
            if (! empty($_POST['serialized'])) {
                $serialized = (isset($_POST['serialized'])) ? explode(',', $_POST['serialized']) : array();
                $i = 0;
                foreach ($serialized as $k => $v) {
                    $rVal = $this->db->prepare('UPDATE op_plugins SET position = :pos WHERE id = :id');
                    $rVal->execute(array('pos' => $i, 'id' => $v));
                    $i++;
                }

                opSystem::Msg(opTranslation::getTranslation('_plugin_order_saved', get_class($this)), opSystem::SUCCESS_MSG);
            } else {
                opSystem::Msg(opTranslation::getTranslation('_no_category_selected', get_class($this)), opSystem::ERROR_MSG);
            }
            # Plugin process to sort
            $pluginProcess = (isset($_POST['parentSelect']) && is_numeric($_POST['parentSelect'])) ? $_POST['parentSelect'] : 1;
        } else {
            # Plugin process to sort
            $pluginProcess = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 1;
        }
        # Force only pre/post categories
        $pluginProcess = ($pluginProcess != 1 && $pluginProcess != 2) ? 1 : $pluginProcess;

        # Create template
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opPlugin.advancedControl.php');
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('opThemePath', $this->theme->getThemePath());
        $template->set('pluginProcess', $pluginProcess);

        # Get plugins from database
        $rVal = $this->db->prepare('SELECT * FROM op_plugins WHERE processing_position = :position ORDER BY position ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('position' => $pluginProcess));
        $template->set('childsOfParent', $rVal->fetchAll());

        # Add js
        $this->theme->addJS(new opJSFile(self::getRelativePath(__CLASS__).'js/opPlugin.advancedControl.js'));
        $template->set('opPluginName', get_class($this));
        return $template;
    }

    public function pluginManualInstall() {
        # Check install
        if (isset($_POST['class_name'])) {
            if (!empty($_POST['class_name'])) {
                $pluginName = $_POST['class_name'];
                if (is_dir(DOCUMENT_ROOT.'/plugins/'.$pluginName) || is_dir(DOCUMENT_ROOT.'/core/plugins/'.$pluginName)) {
                    if (is_file(DOCUMENT_ROOT.'/plugins/'.$pluginName.'/class.'.$pluginName.'.php') || is_file(DOCUMENT_ROOT.'/core/plugins/'.$pluginName.'/class.'.$pluginName.'.php')) {
                        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_plugins WHERE plugin_name = :pn');
                        $rVal->setFetchMode(PDO::FETCH_ASSOC);
                        $rVal->execute(array('pn' => $pluginName));
                        if ($rVal->fetchColumn() <= 0) {
                            if (call_user_func(array($pluginName, 'install'))) {
                                $c = call_user_func(array($pluginName, 'getConfig'));
                                $pp = ($c && isset($c->processing_position)) ? $c->processing_position : 0;

                                $rVal = $this->db->prepare('INSERT INTO op_plugins (plugin_name, processing_position, position, cat_id, core, stamp) VALUES (:name, :pp, 0, 0, 0, NOW())');
                                $rVal->execute(array('name' => $pluginName, 'pp' => $pp));

                                opSystem::Msg(opTranslation::getTranslation('_plugin_installed', get_class($this)), opSystem::SUCCESS_MSG);
                            } else {
                                opSystem::Msg(opTranslation::getTranslation('_plugin_install_fail_function', get_class($this)), opSystem::ERROR_MSG);
                            }
                            opSystem::redirect('/opPlugin');
                        } else {
                            opSystem::Msg(opTranslation::getTranslation('_plugin_install_fail_name_exists', get_class($this)), opSystem::ERROR_MSG);
                        }
                    } else {
                        opSystem::Msg(sprintf(opTranslation::getTranslation('_plugin_install_fail_file_not_found', get_class($this)), '&quot;/plugins/'.htmlspecialchars($pluginName).'/class.'.htmlspecialchars($pluginName).'.php&quot;'), opSystem::ERROR_MSG);
                    }
                } else {
                    opSystem::Msg(sprintf(opTranslation::getTranslation('_plugin_install_fail_dir_not_found', get_class($this)), '&quot;/plugins/'.htmlspecialchars($pluginName).'&quot;'), opSystem::ERROR_MSG);
                }
            } else {
                opSystem::Msg(opTranslation::getTranslation('_plugin_install_fail_class_name', get_class($this)), opSystem::ERROR_MSG);
            }
        }

        # Create template
        $template = new opFileTemplate(self::getFullPath(__CLASS__).'opPlugin.manualInstall.php');
        $template->set('opPluginPath', self::getRelativePath(__CLASS__));
        $template->set('opThemePath', $this->theme->getThemePath());
        $template->set('opPluginName', get_class($this));
        return $template;
    }

    public function pluginInstall() {
        opSystem::redirect('/opPlugin');
    }

    public function pluginUninstall() {
        $pluginID = (isset($this->args[0]) && is_numeric($this->args[0])) ? $this->args[0] : 0;
        $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_plugins WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $pluginID));
        if ($rVal->fetchColumn() > 0) {         
            $rVal = $this->db->prepare('SELECT * FROM op_plugins WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $pluginID));
            $pluginData = $rVal->fetch();

            $p = new $pluginData['plugin_name']($this->theme, null);
            if ($p->uninstall()) {

                $rVal = $this->db->prepare('DELETE FROM op_layout_collections WHERE plugin_id = :id');
                $rVal->execute(array('id' => $pluginID));

                $rVal = $this->db->prepare('DELETE FROM op_virtual_controller WHERE plugin_id = :id');
                $rVal->execute(array('id' => $pluginID));

                $rVal = $this->db->prepare('DELETE FROM op_plugins WHERE id = :id');
                $rVal->execute(array('id' => $pluginID));

                $rVal = $this->db->prepare('DELETE FROM op_system_variables WHERE c = :c');
                $rVal->execute(array('c' => $pluginData['plugin_name']));

                opSystem::Msg(opTranslation::getTranslation('_plugin_removed', get_class($this)), opSystem::INFORM_MSG);
            } else {
                opSystem::Msg(opTranslation::getTranslation('_plugin_uninstall_fail_function', get_class($this)), opSystem::ERROR_MSG);
            }
        } else {
            opSystem::Msg(opTranslation::getTranslation('_unknown_plugin_id', get_class($this)), opSystem::ERROR_MSG);
        }
        opSystem::redirect('/opPlugin');
    }

    public static function install() {
        $sqlImport = new opSQLImport(opSystem::getDatabaseInstance());

        # Import tables
        if (! $sqlImport->import(self::getFullPath(__CLASS__).'sql/opPlugin.install.sql')) { return false; };

        return true;
    }

    public static function getIdByName($pluginName) {
        $db = opSystem::getDatabaseInstance();
        $rVal = $db->prepare('SELECT COUNT(*) FROM op_plugins WHERE plugin_name = :pn');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('pn' => $pluginName));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $db->prepare('SELECT * FROM op_plugins WHERE plugin_name = :pn');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('pn' => $pluginName));
            $rVal = $rVal->fetch();
            return $rVal['id'];
        } else {
            return false;
        }
    }

    public static function getNameById($id) {
        $db = opSystem::getDatabaseInstance();
        $rVal = $db->prepare('SELECT COUNT(*) FROM op_plugins WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $id));
        if ($rVal->fetchColumn() > 0) {
            $rVal = $db->prepare('SELECT * FROM op_plugins WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $id));
            $rVal = $rVal->fetch();
            return $rVal['plugin_name'];
        } else {
            return false;
        }
    }

    protected function updatePluginRepository() {
        $fA = new opFeedAggregator('http://services.openpublish.org/repository/repository.xml');
        $pluginFeed = $fA->getFeedAsSimpleXML();
        if ($pluginFeed) {
            $this->db->query('DELETE FROM op_plugin_repo');
            foreach ($pluginFeed->item as $i) {
                $rVal = $this->db->prepare('INSERT INTO op_plugin_repo (name, description, author, version, rating, downloads, pid, category, dependency, upgrade) VALUES (:name, :description, :author, :version, :rating, :downloads, :pid, :category, :dependency, :upgrade)');
                $rVal->execute(array('name' => $i->name,
                    'description' => $i->description,
                    'author' => $i->author,
                    'version' => $i->version,
                    'rating' => $i->rating,
                    'downloads' => $i->downloads,
                    'pid' => $i->pluginID,
                    'category' => $i->category,
                    'dependency' => $i->dependency,
                    'upgrade' => $i->upgrade));
            }
        }
    }
}
?>