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
class controller_admindm extends opControllerBase {
    protected $lID, $oColor, $lastInsertId, $uID, $router, $systemConfiguration;

    final protected function initialize() {
        $this->router = opSystem::getRouterInstance();
        $this->systemConfiguration = opSystem::getSystemConfiguration();
        # User
        $username = (isset($_SESSION['opAdmin'])) ? $_SESSION['opAdmin']['username'] : false;
        if ($username) {
            $u = new opUser($this->db, $username, null);
            $this->uID = $u->getId();

            # Get color from user account
            $rVal = $this->db->prepare('SELECT dm_color, dm_last_insert_id FROM op_admin_users WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $this->uID));
            $rVal = $rVal->fetch();

            $this->oColor       = $rVal['dm_color'];
            $this->lastInsertId = $rVal['dm_last_insert_id'];
        } else {
            opSystem::redirect('');
        }
        $route = $this->router->getArgs();
        if (isset($route[1]) && is_numeric($route[1]) && $route[1] > 0) {
            $this->lID = $route[1];
            $_SESSION['opDesignMode_layoutID'] = $this->lID;
            unset($_SESSION['opDesignMode_outlines']);
            unset($_SESSION['opDesignMode_tags']);
        } else {
            $this->lID = (isset($_SESSION['opDesignMode_layoutID'])) ? $_SESSION['opDesignMode_layoutID'] : false;
        }
    }

    public function index() {
        if ($this->authenticate()) {
            if ($this->lID && is_numeric($this->lID) && $this->lID > 0) {
                $rVal = $this->db->query('SELECT op_theme_templates.filepath, op_layouts.* FROM op_layouts LEFT JOIN op_theme_templates ON op_theme_templates.id = op_layouts.theme_template WHERE op_layouts.id = '.$this->lID);
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $layoutData = $rVal->fetch();

                $templateParent = $layoutData['id'];
                $templateInheritance = array();
                do {
                    $rVal = $this->db->query('SELECT op_layouts.name AS layoutName, op_layout_collections.* FROM op_layout_collections LEFT JOIN op_layouts ON op_layouts.id = op_layout_collections.parent WHERE op_layout_collections.parent = '.$templateParent.' ORDER BY op_layout_collections.position ASC');
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
                $theme = new opTheme($themeData['path'], $layoutData['filepath'], true, false, false);
                $templateFileContents = file_get_contents(DOCUMENT_ROOT.$themeData['path'].$layoutData['filepath']);
                $templateFileContents .= '<input type="hidden" id="layoutID" value="'.$this->lID.'" />';

                # No-cache
                header('Cache-Control: no-cache, must revalidate');
                header('Pragma: no-cache');

                # designMode
                if (! isset($_SESSION['opDesignMode_outlines'])) {
                    $templateFileContents = $this->addTags($templateFileContents);
                }

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
                $template = new opHtmlTemplate($theme->getThemePath());
                $templateCollection->addTemplate($template);
                $templateManager->addCollection($templateCollection);

                # Process PRE-PROCESSING plugins
                $prePlugins = $this->db->query('SELECT * FROM op_plugins WHERE processing_position = 1 ORDER BY position ASC');
                $prePlugins->setFetchMode(PDO::FETCH_ASSOC);
                foreach ($prePlugins->fetchAll() as $k => $v) {
                    $prePlugin = new $v['plugin_name']($theme, null);
                    $prePlugin->getOutput(null, false);
                }

                # Sort by template placeholders
                $templateCollectionsSorted = array();
                foreach ($templateInheritance as $k => $v) {
                    foreach ($v as $collectionKey => $collectionValue) {
                        $templateCollectionsSorted[$collectionValue['tagID']][] = array($collectionValue['plugin_id'], $collectionValue['plugin_child_id'], $collectionValue['layoutName'], $collectionValue['parent'], $collectionValue['id'], $collectionValue['position']);
                    }
                }

                foreach ($templateCollectionsSorted as $k => $v) {
                # Get plugin output
                    $templateCollection = new opTemplateCollection($k);

                    #sort $v by position
                    $sortedByPosition = array();
                    foreach ($v as $collectionKey => $collectionValue) {
                        if (! in_array($collectionValue[5], $sortedByPosition, true)) {
                            $sortedByPosition[] = $collectionValue[5];
                        }
                    }
                    sort($sortedByPosition);
                    foreach ($sortedByPosition as $sBP) {
                        foreach ($v as $collectionKey => $collectionValue) {
                            if ($sBP == $collectionValue[5]) {
                                $rVal = $this->db->prepare('SELECT * FROM op_plugins WHERE id = :id');
                                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                                $rVal->execute(array('id' => $collectionValue[0]));
                                $rVal = $rVal->fetch();
                                $p = opPluginFactory::produce($rVal['plugin_name'], $theme, null);
                                $pluginOutput = $p->getOutput($collectionValue[1], false);
                                if (strlen($pluginOutput) <= 0 || $pluginOutput === false) {
                                    $pluginOutput = '<p>- PLACEHOLDER FOR &quot;'.$rVal['plugin_name'].'&quot; - No output from plugin</p>';
                                }
                                if (! isset($_SESSION['opDesignMode_outlines'])) {
                                    $pluginOutput = $this->addToolbar($collectionValue[5], $collectionValue[4], $collectionValue[2], $collectionValue[3], $pluginOutput);
                                }
                                $templateCollection->addTemplate(new opHtmlTemplate($pluginOutput));
                            }
                        }
                    }
                    $templateManager->addCollection($templateCollection);
                }
                $theme->setBody($templateManager);

                $theme->setTitle('Administration | Openpublish CMS');

                # Process POST-PROCESSING plugins
                $postPlugins = $this->db->query('SELECT * FROM op_plugins WHERE processing_position = 2 ORDER BY position ASC');
                $postPlugins->setFetchMode(PDO::FETCH_ASSOC);
                foreach ($postPlugins->fetchAll() as $k => $v) {
                    $postPlugin = new $v['plugin_name']($theme, null);
                    $postPlugin->getOutput(null, false);
                }

                # designMode
                $designModeHeader   	= new opFileTemplate(DOCUMENT_ROOT.'/core/dm/designModeHeader.php');
                $designModeToolbar  	= new opFileTemplate(DOCUMENT_ROOT.'/core/dm/designModeToolbar.php');
                $designModeIconbar  	= new opFileTemplate(DOCUMENT_ROOT.'/core/dm/designModeIconbar.php');
                $designModeProperties   = new opFileTemplate(DOCUMENT_ROOT.'/core/dm/designModeProperties.php');
                $designModeHeader->set('layoutCrumb', $this->getLayoutCrumb());
                $designModeToolbar->set('pluginList', $this->getPluginList($theme, null));
                $designModeIconbar->set('oColor', $this->oColor);
                $theme->setRawBody('<div class="ui-layout-center">
                                        <div class="opLayoutInnerCenter">'.$theme->getBody().'</div>
                                        <div class="opLayoutInnerSouth">'.$designModeProperties->renderTemplate().'</div>
                                    </div>
                                    <div class="ui-layout-north">'.$designModeHeader->renderTemplate().'</div>
                                    <div class="ui-layout-west">'.$designModeIconbar->renderTemplate().'</div>
                                    <div class="ui-layout-east">'.$designModeToolbar->renderTemplate().'</div>');

                $jsFiles = $theme->getJS();
                $theme->removeAllJS();

                $theme->addCSS(new opCSSFile('/core/dm/css/designmode.css'));
                $theme->addCSS(new opCSSFile('/core/dm/css/colorpicker.css'));
                $theme->addJS(new opJSFile('/themes/opAdmin/js/jquery-1.3.2.min.js'));
                $theme->addJS(new opJSFile('/themes/opAdmin/js/jquery-ui-1.7.2.custom.min.js'));
                $theme->addJS(new opJSFile('/core/dm/js/jquery.layout.min-1.2.0.js'));
                $theme->addJS(new opJSFile('/core/dm/js/colorpicker.js'));
                $theme->addJS(new opJSFile('/core/dm/js/dmAccordion.js'));
                $theme->addJS(new opJSFile('/core/dm/js/designMode.js'));

                foreach ($jsFiles as $jsFile) {
                    $theme->addJS($jsFile);
                }
                $theme->setFavIcon('/favicon.ico');
                $theme->render();
            } else {
                opSystem::redirect('/opLayout');
            }
        } else {
            opSystem::redirect('');
        }
    }

    public function toggleOutlines() {
        if ($this->authenticate()) {
            if (isset($_SESSION['opDesignMode_outlines'])) {
                unset($_SESSION['opDesignMode_outlines']);
            } else {
                $_SESSION['opDesignMode_outlines'] = true;
            }
            opSystem::redirect('dm');
        } else {
            opSystem::redirect('');
        }
    }

    public function toggleTags() {
        if ($this->authenticate()) {
            if (isset($_SESSION['opDesignMode_tags'])) {
                unset($_SESSION['opDesignMode_tags']);
            } else {
                $_SESSION['opDesignMode_tags'] = true;
            }
            opSystem::redirect('dm');
        } else {
            opSystem::redirect('');
        }
    }

    public function assignContentTo() {
        if ($this->authenticate()) {
            $route = $this->router->getArgs();
            $positionID = (isset($route[2]) && is_numeric($route[2])) ? $route[2] : false;
            $pluginID   = (isset($route[3]) && is_numeric($route[3])) ? $route[3] : false;
            $contentID  = (isset($route[4]) && is_numeric($route[4])) ? $route[4] : false;
            if ($this->lID !== false && $positionID !== false && $pluginID !== false && $contentID !== false) {
                $rVal = $this->db->prepare('INSERT INTO op_layout_collections (tagID, parent, position, plugin_id, plugin_child_id) VALUES (:tagID, :parent, 0, :pid, :pcid)');
                $rVal->execute(array('tagID' => $positionID, 'parent' => $this->lID, 'pid' => $pluginID, 'pcid' => $contentID));

                $rVal = $this->db->prepare('UPDATE op_admin_users SET dm_last_insert_id = :dm_id WHERE id = :id');
                $rVal->execute(array('dm_id' => $pluginID.':'.$contentID, 'id' => $this->uID));

                $this->updateLastModified($this->lID);

                opSystem::redirect('dm');
            } else {
                die('Error on content assign');
            }
        } else {
            opSystem::redirect('');
        }
    }

    public function updateWeightById() {
        if ($this->authenticate()) {
            $route = $this->router->getArgs();
            $layoutID       = (isset($route[2]) && is_numeric($route[2])) ? $route[2] : 0;
            $collectionID   = (isset($route[3]) && is_numeric($route[3])) ? $route[3] : 0;
            $weight         = (isset($route[4]) && is_numeric($route[4])) ? $route[4] : 0;

            $rVal = $this->db->prepare('UPDATE op_layout_collections SET position = :pos WHERE id = :id');
            $rVal->execute(array('pos' => $weight, 'id' => $collectionID));

            $this->updateLastModified($this->lID);

            opSystem::redirect('dm');
        } else {
            opSystem::redirect('');
        }
    }

    public function removeCollectionById() {
        if ($this->authenticate()) {
            $route = $this->router->getArgs();
            $collectionID = (isset($route[2]) && is_numeric($route[2])) ? $route[2] : 0;

            $rVal = $this->db->prepare('DELETE FROM op_layout_collections WHERE id = :id');
            $rVal->execute(array('id' => $collectionID));

            $this->updateLastModified($this->lID);

            opSystem::redirect('dm');
        } else {
            opSystem::redirect('');
        }
    }

    public function setOutlineColor() {
        if ($this->authenticate()) {
            $route = $this->router->getArgs();

            # Color
            $hexColor = (isset($route[2]) && strlen($route[2]) == 6) ? $route[2] : 'f00000';

            # User
            $username = (isset($_SESSION['opAdmin'])) ? $_SESSION['opAdmin']['username'] : false;
            if ($username) {
                $u = new opUser($this->db, $username, null);

                # Set color on user account
                $rVal = $this->db->prepare('UPDATE op_admin_users SET dm_color = :color WHERE id = :id');
                $rVal->execute(array('color' => $hexColor, 'id' => $u->getId()));

                opSystem::redirect('dm');
            } else {
                opSystem::redirect('');
            }
        } else {
            opSystem::redirect('');
        }
    }

    public function getProperties() {
        if ($this->authenticate()) {
            $route = $this->router->getArgs();
            if (isset($route[2]) && is_numeric($route[2])) {
                $rVal = $this->db->prepare('SELECT op_layouts.name AS layoutName, op_plugins.plugin_name AS pluginName, op_layout_collections.* FROM op_layout_collections LEFT JOIN op_layouts ON op_layouts.id = op_layout_collections.parent LEFT JOIN op_plugins ON op_plugins.id = op_layout_collections.plugin_id WHERE op_layout_collections.id = :id');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $route[2]));
                $collectionData = $rVal->fetch();

                $c = call_user_func(array($collectionData['pluginName'], 'getConfig'));
                $collectionData['realPluginName'] = (string)$c->name;
                $collectionData['contentName'] = call_user_func_array(array($collectionData['pluginName'], 'getContentNameById'), array($collectionData['plugin_child_id']));
                $collectionData['contentEditPath'] = call_user_func(array($collectionData['pluginName'], 'getContentEditPath'));
                $collectionData['lockProperties'] = ($this->lID != $collectionData['parent']) ? '1' : '0';

                echo json_encode($collectionData);
                exit();
            } else {
                opSystem::redirect('');
            }
        } else {
            opSystem::redirect('');
        }
    }

    private function getLayoutCrumb() {
        $layoutCrumb = array();
        $rVal = $this->db->prepare('SELECT * FROM op_layouts WHERE id = :id');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute(array('id' => $this->lID));
        $layoutData = $rVal->fetch();
        $layoutCrumb[$layoutData['id']] = $layoutData['name'];

        $parentLayout = $layoutData['parent'];
        while ($parentLayout > 0) {
            $rVal = $this->db->prepare('SELECT * FROM op_layouts WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $parentLayout));
            $parentLayoutData = $rVal->fetch();
            $layoutCrumb[$parentLayoutData['id']] = $parentLayoutData['name'];
            $parentLayout = $parentLayoutData['parent'];
        }
        $layoutCrumb = array_reverse($layoutCrumb, true);
        $layoutCrumbHTML = '';
        $i = 0;
        foreach ($layoutCrumb as $id => $name) {
            if ($i < count($layoutCrumb)-1) {
                $layoutCrumbHTML .= '<a href="/admindm/'.$id.'" class="designModeLink">'.$name.'</a> &raquo; ';
            } else {
                $layoutCrumbHTML .= $name;
            }
            $i++;
        }

        return $layoutCrumbHTML;
    }

    private function addTags($fileContents) {
        $p = $this->getMTPositions($fileContents);
        $i = 0;
        foreach ($p as $k => $v) {
            $tArr = explode(':', $k);
            $uniqueID   = (isset($tArr[0]) && is_numeric($tArr[0])) ? $tArr[0] : false;
            $tag        = (isset($tArr[1])) ? $tArr[1] : false;
            $dimensions = (isset($tArr[2])) ? $tArr[2] : false;
            $outlineW   = false;
            $outlineH   = false;
            if ($dimensions) {
                if (strpos($dimensions, 'x') > 0) {
                    list($outlineW, $outlineH) = explode('x', $dimensions);
                    $outlineW = intval($outlineW);
                    $outlineH = intval($outlineH);
                } else {
                    $outlineW = intval($dimensions);
                }
            }

            if ($k != 'themePath' && $tag && $uniqueID && $tag != 'wrapStart' && $tag != 'wrapEnd' && $tag != 'wrapContent') {
                if ($outlineW && $outlineH) {
                    $setSize = ' style="width:'.$outlineW.'px;height:'.$outlineH.'px"';
                    $tag = $tag.':'.$outlineW.'x'.$outlineH;
                } else if ($outlineW) {
                    $setSize = ' style="width:'.$outlineW.'px;"';
                    $tag = $tag.':'.$outlineW;
                } else {
                    $setSize = '';
                }
                $spanTag = (! isset($_SESSION['opDesignMode_tags'])) ? '<span class="opDesignModeWrapperTitle">&#123;'.$tag.'&#125;</span>' : '';
                $fileContents = str_replace($v, '<div class="opDesignModeWrapper"'.$setSize.' id="'.$uniqueID.'">'.$spanTag.$v.'</div>', $fileContents);
            }
            $i++;
        }
        return $fileContents;
    }

    private function getMTPositions($masterTemplate) {
        $positions = array();
        $masterTemplate = explode('{', $masterTemplate);
        foreach ($masterTemplate as $k => $v) {
            if (strpos($v, '}') === false) {
                continue;
            } else {
                list($a, $b) = explode('}', $v);
                $positions[$a] = '{'.$a.'}';
            }
        }
        return $positions;
    }

    private function addToolbar($weight, $collectionID, $layoutTitle, $layoutID, $buffer) {
        return '<div class="opDesignModeContentItem" id="'.$collectionID.'">'.$buffer.'</div>';
    }

    private function getPluginList(opTheme $theme, $args) {
    # Make sure we only add plugins that are marked for menu integration
        $rVal = $this->db->query('SELECT * FROM op_plugins ORDER BY plugin_name ASC');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal = $rVal->fetchAll();
        $pluginList = '<ul class="opDesignModePalette">';
        foreach ($rVal as $k => $v) {
            $p = new $v['plugin_name']($theme, $args);
            $c = $p->getConfig();
            if ($c->assignToLayout == 'true') {
                $pluginIcon = ($p->getIcon()) ? $p->getIcon() : '/themes/opAdmin/images/icons/puzzle.png';
                $pluginList .= '<li id="'.$v['id'].'"><div class="opDesignModePaletteItem"><img src="'.$pluginIcon.'" class="opAdminToolbarIcon" /> '.$c->name.'</div><ul class="opDesignModePalette">';
                $this->buildContentList($p->getContentList(), $pluginList, $v['id'], $pluginIcon);
                $pluginList .= '</ul></li>';
            }
        }
        $pluginList .= '</ul>';
        return $pluginList;
    }

    private function buildContentList($contentList, &$pluginList, $pluginID, $pluginIcon) {
        if ($contentList instanceof opContentList || $contentList instanceof opContentGroup) {
            foreach ($contentList->getElements() as $cElement) {
                if ($cElement instanceof opContentElement) {
                    $idCombo = $pluginID.':'.$cElement->getValue();
                    $activeClass = ($idCombo == $this->lastInsertId) ? ' class="dmOpen"' : '';
                    $pluginList .= '<li style="padding-left: 20px;"'.$activeClass.'><span id="'.$idCombo.'" class="opDesignModeDraggable"><img src="'.$pluginIcon.'" class="opAdminToolbarIcon" /> '.$cElement->getText().'</span></li>';
                } else if ($cElement instanceof opContentGroup) {
                        $pluginList .= '<li style="padding-left: 20px;"><div><img src="/core/dm/icons/clear-folder-plus.png" class="dmAccordionToggleClosed opAdminToolbarIcon" /> '.$cElement->getLabel().'</div><ul class="opDesignModePalette">';
                        $this->buildContentList($cElement, &$pluginList, $pluginID, $pluginIcon);
                        $pluginList .= '</ul></li>';
                    }
            }
        }
    }

    private function updateLastModified($layoutID) {
        $lastModified = gmdate('D, d M Y H:i:s \G\M\T', time());
        $ETag = opLayout::generateETag($layoutID);
        $rVal = $this->db->prepare('UPDATE op_layouts SET last_modified = :lm, etag = :etag WHERE id = :id');
        $rVal->execute(array('lm' => $lastModified, 'etag' => $ETag, 'id' => $layoutID));
    }

    private function authenticate() {
        $auth = new opAdminAuthentication($this->db, opSystem::getSecretKey());
        return $auth->authenticate();
    }
}
?>