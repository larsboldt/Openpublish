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
class controller_index extends opControllerBase {
    protected $vc, $rc, $route, $url, $systemConfiguration;

    protected function initialize() {
        # sys config
        $this->systemConfiguration = opSystem::getSystemConfiguration();
        # Get ?route as array
        $route = opSystem::getRouterInstance();
        $this->route = $route->getArgs();
        # Implode route array to a complete url
        $this->url   = '/'.implode('/',$this->route).'/';
        # Get virtualController
        $this->vc    = opSystem::getVirtualControllerInstance();
        # Get redirectController
        $this->rc    = opSystem::getRedirectControllerInstance();
        # If url is a registered redirect, redirect now...
        if ($this->rc->isRedirectRegistered($this->url)) {
            $this->rc->redirect($this->url);
        }
    }
    
    public function index() {
        $is404           = false;
        $is503           = false;
        $pageName        = false;
        $pluginPageTitle = false;
        $layoutID        = false;
        #check for offline status
        if ($this->systemConfiguration->site_status == 1) {
            $is503 = true;
            $layoutID = $this->getOffline();
        }

        if (! $is503) {       
            if (count($this->route) == 0) {
                # Who owns HOME??

                $rVal = $this->db->query('SELECT COUNT(*) FROM op_menu_items WHERE home = 1');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                if ($rVal->fetchColumn() > 0) {
                    $rVal = $this->db->query('SELECT * FROM op_menu_items WHERE home = 1');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal = $rVal->fetch();
                    $layoutID = $rVal['layout_id'];
                    $pluginPageTitle = array($rVal['name']);
                } else {
                    $is404 = true;
                    $layoutID = $this->get404();
                }


            } else if ($this->vc->isRegistered($this->route[0])) {
                $controllerOwner = $this->vc->getPluginID($this->route[0]);
                $controllerName  = opPlugin::getNameById($controllerOwner);
                if ($controllerName) {
                    $c = call_user_func_array(array($controllerName, 'controller'), $this->url);
                    $pluginPageTitle = call_user_func_array(array($controllerName, 'getPageTitle'), $this->url);
                    $pluginPageTitle = (is_array($pluginPageTitle)) ? $pluginPageTitle : array('Plugin pagetitle error');
                    switch ($c) {
                        case false:
                        case 404:
                            $is404 = true;
                            $layoutID = $this->get404();
                            break;
                        case 503:
                            $is503 = true;
                            $layoutID = $this->get503();
                            break;
                        default:
                            if (is_numeric($c)) {
                                $layoutID = $c;
                            } else {
                                $is404 = true;
                                $layoutID = $this->get404();
                            }
                    }
                } else {
                    $is404 = true;
                    $layoutID = $this->get404();
                }
            } else {
                $is404 = true;
                $layoutID = $this->get404();
            }
        }
        if ($is404) {
            $pageName = '404 - Not found';
        } else if ($is503) {
            $pageName = '503 - Service Temporarily Unavailable';
        }

        if ($layoutID) {
            $rVal = $this->db->query('SELECT op_theme_templates.filepath, op_layouts.* FROM op_layouts LEFT JOIN op_theme_templates ON op_theme_templates.id = op_layouts.theme_template WHERE op_layouts.id = '.$layoutID);
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $layoutData = $rVal->fetch();

            $templateParent = $layoutData['id'];
            $templateInheritance = array();
            do {
                $rVal = $this->db->query('SELECT * FROM op_layout_collections WHERE parent = '.$templateParent.' ORDER BY position ASC');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $templateInheritance[$templateParent] = $rVal->fetchAll();

                $rVal = $this->db->query('SELECT * FROM op_layouts WHERE id = '.$templateParent);
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal = $rVal->fetch();
                $templateParent = $rVal['parent'];
            } while ($templateParent > 0);

            $rVal = $this->db->query('SELECT * FROM op_theme_templates WHERE id = '.$layoutData['theme_template']);
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $templateData = $rVal->fetch();

            $rVal = $this->db->query('SELECT * FROM op_themes WHERE id = '.$templateData['parent']);
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $themeData = $rVal->fetch();

            # Construct page
            $compressJS = ($this->systemConfiguration->caching) ? $this->systemConfiguration->compress_js : false;
            $compressCSS = ($this->systemConfiguration->caching) ? $this->systemConfiguration->compress_css : false;
            
            # Create theme
            $theme = new opTheme($themeData['path'], $layoutData['filepath'], false, $compressJS, $compressCSS, $this->systemConfiguration->cache_ttl);
            $templateFileContents = file_get_contents(DOCUMENT_ROOT.$themeData['path'].$layoutData['filepath']);
            $templateManager = new opTemplateManager($templateFileContents);

            # Sitename
            $templateCollection = new opTemplateCollection('siteName');
            $template = new opHtmlTemplate($this->systemConfiguration->site_name);
            $templateCollection->addTemplate($template);
            $templateManager->addCollection($templateCollection);

            # SiteURL
            $templateCollection = new opTemplateCollection('siteURL');
            $template = new opHtmlTemplate($this->systemConfiguration->site_url);
            $templateCollection->addTemplate($template);
            $templateManager->addCollection($templateCollection);

            # themePath
            $templateCollection = new opTemplateCollection('themePath');
            $template = new opHtmlTemplate('/'.$themeData['path']);
            $templateCollection->addTemplate($template);
            $templateManager->addCollection($templateCollection);

            # metaTags
            # Get all layout parents for inheritance
            $layoutMetaParents = array($layoutID);
            $layoutMetaParent = $layoutData['parent'];
            $layoutMetaCollection = array('title' => true, 'description' => true, 'keywords' => true, 'author' => true, 'owner' => true, 'copyright' => true, 'robots' => true);
            if ($layoutData['disable_meta_inheritance'] == 0) {
                while ($layoutMetaParent > 0) {
                    $rVal = $this->db->prepare('SELECT * FROM op_layouts WHERE id = :parent');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal->execute(array('parent' => $layoutMetaParent));
                    $parentLayoutData = $rVal->fetch();
                    $layoutMetaParents[] = $parentLayoutData['id'];
                    if ($parentLayoutData['disable_meta_inheritance'] == 1) {
                        $layoutMetaParent = 0;
                    } else {
                        $layoutMetaParent = $parentLayoutData['parent'];
                    }
                }
            }
            foreach ($layoutMetaParents as $layoutMetaParent) {
                $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_layout_metatags WHERE parent = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $layoutMetaParent));
                if ($rVal->fetchColumn() > 0) {
                    $rVal = $this->db->prepare('SELECT * FROM op_layout_metatags WHERE parent = :id');
                    $rVal->setFetchMode(PDO::FETCH_ASSOC);
                    $rVal->execute(array('id' => $layoutMetaParent));
                    $metaData = $rVal->fetch();

                    foreach ($metaData as $metaKey => $metaValue) {
                        if (mb_strlen($metaValue) > 0 && array_key_exists($metaKey, $layoutMetaCollection)) {
                            if ($metaKey == 'robots') {
                                switch ($metaValue) {
                                    case 2:
                                        $theme->addMeta('name="robots" content="none"');
                                        break;
                                    case 3:
                                        $theme->addMeta('name="robots" content="noindex,follow"');
                                        break;
                                    case 4:
                                        $theme->addMeta('name="robots" content="index,nofollow"');
                                        break;
                                    default:
                                        $theme->addMeta('name="robots" content="all"');
                                }
                            } else {
                                $theme->addMeta('name="'.$metaKey.'" content="'.$metaValue.'"');
                            }
                            unset($layoutMetaCollection[$metaKey]);
                        }
                    }
                }
            }

            # header cache
            if ($this->systemConfiguration->local_caching && $layoutData['disable_local_cache'] == 0) {
                header('Last-Modified: '.$layoutData['last_modified']);
                header('Cache-Control: max-age='.$this->systemConfiguration->local_cache_ttl);
                header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', (time() + 2419200)));
                header('ETag: '.$layoutData['etag']);
                header('Pragma: ');
            } else {
                header('Cache-Control: no-cache, must revalidate');
                header('Pragma: no-cache');
            }

            # Set page title
            if (! $pageName) {
                if ($this->systemConfiguration->title_breadcrumb == 1) {
                    $pageTitle = implode(' '.$this->systemConfiguration->title_breadcrumb_separator.' ', $pluginPageTitle);
                } else {
                    $pageTitle = $pluginPageTitle[count($pluginPageTitle)-1];
                }
                if (! $theme->getTitle()) {
                    $theme->setTitle($pageTitle);
                }
            } else {
                if (! $theme->getTitle()) {
                    $theme->setTitle($pageName);
                }
            }
            $theme->setTitle($theme->getTitle().' '.$this->systemConfiguration->title_separator.' '.$this->systemConfiguration->site_name);

            # Process PRE-PROCESSING plugins
            $prePlugins = $this->db->query('SELECT * FROM op_plugins WHERE processing_position = 1 ORDER BY position ASC');
            $prePlugins->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($prePlugins->fetchAll() as $k => $v) {
                $prePlugin = new $v['plugin_name']($theme, null);
                $prePlugin->getOutput(null, $this->getRenderMode($layoutData['type']));
            }

            # Sort by template placeholders
            $templateCollectionsSorted = array();
            foreach ($templateInheritance as $k => $v) {
                foreach ($v as $collectionKey => $collectionValue) {
                    $templateCollectionsSorted[$collectionValue['tagID']][] = array($collectionValue['plugin_id'], $collectionValue['plugin_child_id'], $collectionValue['position']);
                }
            }

            foreach ($templateCollectionsSorted as $k => $v) {
                # Get plugin output
                $templateCollection = new opTemplateCollection($k);

                #sort $v by position
                $sortedByPosition = array();
                foreach ($v as $collectionKey => $collectionValue) {
                    if (! in_array($collectionValue[2], $sortedByPosition, true)) {
                        $sortedByPosition[] = $collectionValue[2];
                    }
                }
                sort($sortedByPosition);
                foreach ($sortedByPosition as $sBP) {
                    foreach ($v as $collectionKey => $collectionValue) {
                        if ($sBP == $collectionValue[2]) {
                            $rVal = $this->db->prepare('SELECT * FROM op_plugins WHERE id = :id');
                            $rVal->setFetchMode(PDO::FETCH_ASSOC);
                            $rVal->execute(array('id' => $collectionValue[0]));
                            $rVal = $rVal->fetch();
                            $p = opPluginFactory::produce($rVal['plugin_name'], $theme, null);
                            $c = $p->getConfig();
                            $forceCacheDisable = (isset($c->cache) && $c->cache == 0) ? true : false;
                            if ($this->systemConfiguration->caching && !$forceCacheDisable) {
                                $cache = new opCache(get_class($p).'_'.$collectionValue[1], $this->systemConfiguration->cache_ttl);
                                $cacheData = ($cache->isCache()) ? $cache->getCache() : $cache->writeCache($p->getOutput($collectionValue[1], $this->getRenderMode($layoutData['type'])));
                                $cacheData = ($cacheData === false) ? $p->getOutput($collectionValue[1], $this->getRenderMode($layoutData['type'])) : $cacheData;
                                $template = new opHtmlTemplate($cacheData);
                            } else {
                                $template = new opHtmlTemplate($p->getOutput($collectionValue[1], $this->getRenderMode($layoutData['type'])));
                            }
                            $templateCollection->addTemplate($template);
                        }
                    }
                }
                $templateManager->addCollection($templateCollection);
            }
            $theme->setBody($templateManager);

            # Process POST-PROCESSING plugins
            $postPlugins = $this->db->query('SELECT * FROM op_plugins WHERE processing_position = 2 ORDER BY position ASC');
            $postPlugins->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($postPlugins->fetchAll() as $k => $v) {
                $postPlugin = new $v['plugin_name']($theme, null);
                $postPlugin->getOutput(null, $this->getRenderMode($layoutData['type']));
            }
        }

        if ($is404) {
            ob_start();
            if ($layoutID) {
                $theme->render();
            }
            $this->render404();
        } else if ($is503) {
            ob_start();
            if ($layoutID) {
                $theme->render();
            }
            $this->render503();
        } else {
            switch ($this->getRenderMode($layoutData['type'])) {
                case 'rss2':
                    echo '<?xml version="1.0" encoding="utf-8" ?>';
                    echo '<rss  version="2.0">';
                    echo $templateManager->render();
                    echo '</rss>';
                    break;
                case 'rss1':
                    echo '<?xml version="1.0" encoding="utf-8" ?>';
                    echo '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/">';
                    echo $templateManager->render();
                    echo '</rdf:RDF>';
                    break;
                case 'atom':
                    echo '<?xml version="1.0" encoding="utf-8" ?>';
                    echo '<feed xmlns="http://www.w3.org/2005/Atom">';
                    echo $templateManager->render();
                    echo '</feed>';
                    break;
                default:
                    ob_start();
                    $theme->render();
                    $completePage = ob_get_clean();
                    header('Content-Length: '.strlen($completePage));
                    echo $completePage;
                    exit();
            }
        }
    }

    private function getRenderMode($mode) {
        switch ($mode) {
            case 4:
                return 'rss2';
                break;
            case 5:
                return 'rss1';
                break;
            case 6:
                return 'atom';
                break;
            default:
                return 'normal';
        }
    }

    private function get404() {
        $rVal = $this->db->query('SELECT COUNT(*) FROM op_layouts WHERE type = 1');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->query('SELECT id FROM op_layouts WHERE type = 1');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal = $rVal->fetch();
            return $rVal['id'];
        } else {
            return false;
        }
    }

    private function get503() {
        $rVal = $this->db->query('SELECT COUNT(*) FROM op_layouts WHERE type = 2');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->query('SELECT id FROM op_layouts WHERE type = 2');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal = $rVal->fetch();
            return $rVal['id'];
        } else {
            return false;
        }
    }

    private function getOffline() {
        $rVal = $this->db->query('SELECT COUNT(*) FROM op_layouts WHERE type = 3');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        if ($rVal->fetchColumn() > 0) {
            $rVal = $this->db->query('SELECT id FROM op_layouts WHERE type = 3');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal = $rVal->fetch();
            return $rVal['id'];
        } else {
            return false;
        }
    }

    private function render404() {
        header('HTTP/1.1 404 Not Found');
        echo ob_get_clean();
        exit();
    }

    private function render503() {
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        echo ob_get_clean();
        exit();
    }
}
?>