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
class opSimpleBrowser {
    protected $baseDir;
    protected $tempDir;
    protected $cacheDir;
    protected $storeDir;
    protected $iUrlManager;
    protected $eUrlManager;
    protected $router;
    protected $db;

    public function __construct() {
        $this->db          = opSystem::getDatabaseInstance();
        $this->router      = opSystem::getRouterInstance();
        $this->iUrlManager = new opMenuURLManager();
        $this->eUrlManager = new opMenuExternalURLManager($this->db);
    }
    
    public function render() {
        if ($this->authenticate()) {
            $this->baseDir  = '/files/';
            $this->tempDir  = DOCUMENT_ROOT.$this->baseDir.'temp/';
            $this->cacheDir = DOCUMENT_ROOT.$this->baseDir.'cache/';
            $this->storeDir = DOCUMENT_ROOT.$this->baseDir.'store/';
            
            $route = $this->router->getArgs();

            if (isset($route[2])) {
                switch ($route[2]) {
                    case 'configure':
                        unset($_SESSION['adminsb_type']);
                        unset($_SESSION['adminsb_form']);
                        unset($_SESSION['adminsb_element']);
                        $type    = (isset($route[3])) ? $route[3] : false;
                        $form    = (isset($route[4]) && $route[4] == 'true') ? true : false;
                        $element = (isset($route[5])) ? $route[5] : false;
                        $this->configure($type, $form, $element);
                        break;
                    case 'sort':
                        $_SESSION['opSimpleBrowser_folder'] = (isset($route[3]) && is_numeric($route[3])) ? $route[3] : 0;
                        opSystem::redirect('/simplebrowser');
                }
            }

            $folderID = (isset($_SESSION['opSimpleBrowser_folder'])) ? $_SESSION['opSimpleBrowser_folder'] : 0;
            $folderID = ($folderID == -1 && isset($_SESSION['adminsb_type']) && ($_SESSION['adminsb_type'] == 'media' || $_SESSION['adminsb_type'] == 'image')) ? 0 : $folderID;

            $rVal = $this->db->query('SELECT * FROM op_filemanager_folders ORDER BY parent ASC, position ASC');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $opFolders = $this->orderRecursiveAsULForIndex($rVal->fetchAll(), 0, $folderID);

            $rVal = $this->db->prepare('SELECT * FROM op_filemanager_filemap WHERE parent = :parent');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('parent' => $folderID));
            $opFiles = $rVal->fetchAll();

            # SITEMAP
            $opSitemap = '<ul id="sitemap">';
            foreach (opSitemap::getSitemap() as $plugin) {
                if (is_array($plugin)) {
                    $i = 0;
                    foreach ($plugin as $itemName => $item) {
                        $opSitemap .= '<li'.(((count($plugin)-1) == $i) ? ' class="lastChild"' : '').'><span>'.$itemName.'</span><ul'.((count($item) > 1) ? ' class="hasChildren"' : '').'>';
                        $opSitemap .= $this->sitemapToUL($item);
                        $opSitemap .= '</ul></li>';
                        $i++;
                    }
                }
            }
            $opSitemap .= '</ul>';
            # SITEMAP END

            $fileType = (isset($_SESSION['adminsb_type'])) ? $_SESSION['adminsb_type'] : false;

            ob_start();
            include_once(DOCUMENT_ROOT.'/core/simplebrowser/index.php');
            echo ob_get_clean();
        } else {
            die('Access denied');
        }
    }

    protected function sitemapToUL($sitemap) {
        $sitemapUL = '';
        $i = 0;
        foreach ($sitemap as $itemKey => $itemValue) {
            if (is_array($itemValue)) {
                foreach ($itemValue as $childKey => $childValue) {
                    if (isset($childValue['url']) && $childValue['url'] !== false) {
                        $childLink = '<a href="javascript:FileBrowserDialogue.mySubmit(\''.$childValue['url'].'\');" title="'.$childValue['name'].'">'.$childValue['name'].'</a>';
                    } else {
                        $childLink = '<span>'.$childValue['name'].'</span>';
                    }
                    $sitemapUL .= '<li'.(((count($sitemap)-1) == $i) ? ' class="lastChild"' : '').'>'.$childLink;
                    if (is_array($childValue['childs']) && count($childValue['childs']) > 0) {
                        $sitemapUL .= '<ul'.((count($childValue['childs']) > 1) ? ' class="hasChildren"' : '').'>';
                        $sitemapUL .= $this->sitemapToUL($childValue['childs']);
                        $sitemapUL .= '</ul>';
                    }
                    $sitemapUL .= '</li>';
                }
            }
            $i++;
        }
        return $sitemapUL;
    }

    protected function orderRecursiveAsULForIndex($arr, $parent, $sortBy, &$retVal = '', $padding = 20) {
        foreach ($arr as $v) {
            if ($v['parent'] == $parent) {
                $retVal .= '<div class="droppable" id="'.$v['id'].'" style="padding-left:'.$padding.'px;"><img src="/core/simplebrowser/icons/clear-folder'.(($sortBy == $v['id']) ? '-open' : '').'.png" class="table-icon" /> <a href="/admin/simplebrowser/sort/'.$v['id'].'"'.(($sortBy == $v['id']) ? ' style="font-weight:bold;"' : '').'>'.$v['name'].'</a></div>';
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

    private function configure($type, $form, $element) {
        $_SESSION['adminsb_type']    = $type;
        $_SESSION['adminsb_form']    = $form;
        $_SESSION['adminsb_element'] = $element;
        opSystem::redirect('/simplebrowser');
    }

    private function authenticate() {
        $auth = new opAdminAuthentication($this->db, opSystem::getSecretKey());
        return $auth->authenticate();
    }
}
?>