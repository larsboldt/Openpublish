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
class opTheme {
    protected $theme;
    protected $themePath;
    protected $template;
    protected $favicon;
    protected $doctype;
    protected $pageTitle;
    protected $metaTags;
    protected $cssFiles;
    protected $jsFiles;
    protected $head;
    protected $body;
    protected $designMode;
    protected $compressJS;
    protected $compressCSS;
    protected $cacheTTL;

    final public function __construct($themePath, $template = false, $designMode = false, $compressJS = false, $compressCSS = false, $cacheTTL = 3600) {
        # Args
        if (is_file(DOCUMENT_ROOT.$themePath.'theme.xml')) {
            $this->theme = simplexml_load_file(DOCUMENT_ROOT.$themePath.'theme.xml');
            $this->themePath = $themePath;
            $this->template  = $template;
        } else {
            die('Theme XML file not found');
        }
        $this->designMode        = $designMode;
        $this->compressCSS       = $compressCSS;
        $this->compressJS        = $compressJS;
        $this->cacheTTL          = $cacheTTL;

        # Default values
        $this->pageTitle        = false;
        $this->body             = false;
        $this->head             = '';
        $this->metaTags         = array();
        $this->jsFiles          = array();
        $this->cssFiles         = array();
      
        # Initialize
        $this->doctype = (isset($this->theme->doctype) && strlen((string)$this->theme->doctype) > 0) ? (string)$this->theme->doctype : '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        $this->favicon = (isset($this->theme->favicon) && strlen((string)$this->theme->favicon) > 0) ? (string)$this->theme->favicon : '/favicon.ico';
        $this->addMeta('http-equiv="Content-Type" content="text/html; charset=utf-8"');
        $this->addMeta('name="generator" content="Openpublish v'.opSystem::getVersion().' - Open Source Content Management System"');

        if (isset($this->theme->css)) {
            foreach ($this->theme->css->file as $element) {
                $this->addCSSFromXML($element);
            }
        }
        if (isset($this->theme->js)) {
            foreach ($this->theme->js->file as $element) {
                $this->addJSFromXML($element);
            }
        }
        if (isset($this->theme->templates)) {
            foreach ($this->theme->templates->template as $template) {
                if (isset($template['src']) && (string)$template['src'] == $this->template) {
                    if (isset($template->css)) {
                        foreach ($template->css->file as $element) {
                            $this->addCSSFromXML($element);
                        }
                    }
                    if (isset($template->js)) {
                        foreach ($template->js->file as $element) {
                            $this->addJSFromXML($element);
                        }
                    }
                }
            }
        }
    }

    public function getName() {
        if (isset($this->theme->name)) {
            return (string)$this->theme->name;
        } else {
            return false;
        }
    }

    public function setFavIcon($iconPath) {
        $this->favicon = $iconPath;
    }

    public function getFavIcon() {
        return $this->favicon;
    }

    public function setTitle($title) {
        $this->pageTitle = $title;
    }

    public function getTitle() {
        return $this->pageTitle;
    }

    public function addMeta($meta) {
        $this->metaTags[$meta] = '<meta '.$meta.' />';
    }

    public function getMeta() {
        return $this->metaTags;
    }

    public function addCSS(opCSSFile $cssFile) {
        $this->cssFiles[] = $cssFile;
    }

    public function removeCSS($idx) {
        if (is_numeric($idx)) {
            $i = 0;
            foreach ($this->cssFiles as $cssFile) {
                if ($i === $idx) {
                    unset($this->cssFiles[$cssFile]);
                }
            }
        }
    }

    public function removeAllCSS() {
        $this->cssFiles = array();
    }

    public function getCSS() {
        return $this->cssFiles;
    }

    public function addJS(opJSFile $jsFile) {
        $this->jsFiles[] = $jsFile;
    }

    public function removeJS($idx) {
        if (is_numeric($idx)) {
            $i = 0;
            foreach ($this->jsFiles as $jsFile) {
                if ($i === $idx) {
                    unset($this->jsFiles[$jsFile]);
                }
            }
        }
    }

    public function removeAllJS() {
        $this->jsFiles = array();
    }

    public function getJS() {
        return $this->jsFiles;
    }

    public function setBody(opTemplateManager $templateManager) {
        $this->body = $templateManager->render();
    }

    public function setRawBody($rawBody) {
        $this->body = $rawBody;
    }

    public function getBody() {
        return $this->body;
    }

    public function render() {
        $xhtml = $this->doctype;
        $xhtml .= chr(13).'<html xmlns="http://www.w3.org/1999/xhtml">';
        $xhtml .= chr(13).'<head>';
        $xhtml .= chr(13).implode(chr(13), $this->getMeta());
        $xhtml .= chr(13).'<title>'.$this->getTitle().'</title>';
        $xhtml .= chr(13).'<link rel="shortcut icon" type="image/ico" href="'.$this->favicon.'" />';
        foreach ($this->getCompressedCSS() as $cssFile) {
            if ($this->designMode && $cssFile->getDropInDesignMode()) {
                continue;
            } else {
                $xhtml .= chr(13).$cssFile->render();
            }
        }
        foreach ($this->getCompressedJS() as $jsFile) {
            if ($this->designMode && $jsFile->getDropInDesignMode()) {
                continue;
            } else {
                $xhtml .= chr(13).$jsFile->render();
            }
        }
        $xhtml .= chr(13).'</head>';
        $xhtml .= chr(13).'<body>';
        $xhtml .= chr(13).$this->body;
        $xhtml .= chr(13).'</body>';
        $xhtml .= chr(13).'</html>';
        echo $xhtml;
    }

    public function getThemePath() {
        return '/'.$this->themePath;
    }

    protected function addCSSFromXML(SimpleXMLElement $element) {
        if (isset($element['src'])) {
            $cssFile = new opCSSFile('/'.$this->themePath.$element['src']);
            if (isset($element['ddm'])) {
                $cssFile->setDropInDesignMode($element['ddm']);
            }
            if (isset($element['media']) && strlen(trim((string)$element['media'])) > 0) {
                $cssFile->setMedia($element['media']);
            }
            if (isset($element['conditional']) && strlen(trim((string)$element['conditional'])) > 0) {
                $cssFile->setConditional($element['conditional']);
            }
            $this->addCSS($cssFile);
        }
    }

    protected function addJSFromXML(SimpleXMLElement $element) {
        if (isset($element['src'])) {
            $jsFile = new opJSFile('/'.$this->themePath.$element['src']);
            if (isset($element['ddm'])) {
                $jsFile->setDropInDesignMode($element['ddm']);
            }
            $this->addJS($jsFile);
        }
    }

    final protected function getCompressedCSS() {
        if ($this->compressCSS) {
            $conditionalCSS = array();
            $mediaGroupedFiles = array();
            foreach ($this->cssFiles as $cssFile) {
                if ($cssFile->getConditional() !== false) {
                    $conditionalCSS[] = $cssFile;
                } else {
                    $media = ($cssFile->getMedia() !== false) ? $cssFile->getMedia() : 'none';
                    $mediaGroupedFiles[$media][] = $cssFile;
                }
            }
            $this->removeAllCSS();

            foreach ($mediaGroupedFiles as $media => $cssFiles) {
                $media = explode(',', $media);
                foreach ($media as $k => $m) {
                    $media[$k] = trim($m);
                }
                $media = implode('_', $media);

                $checksum = '';
                foreach ($cssFiles as $cssFile) {
                    $pathinfo = pathinfo($cssFile->getFile());
                    $checksum .= md5_file(DOCUMENT_ROOT.$cssFile->getFile());
                    $checksum .= md5($pathinfo['basename']);
                }
                $cssName = md5($checksum).'.css';

                $cache = new opCache($cssName, $this->cacheTTL, false);
                if (! $cache->isCache())  {
                    $buffer = '';
                    foreach ($cssFiles as $cssFile) {
                        if ($this->designMode && $cssFile->getDropInDesignMode()) {
                            continue;
                        } else {
                            if (is_file(DOCUMENT_ROOT.$cssFile->getFile())) {
                                $blockData = '';
                                foreach (file(DOCUMENT_ROOT.$cssFile->getFile()) as $line) {
                                    if (strlen(trim($line)) > 0) {
                                        $blockData .= $line;
                                        if (strpos($line, '}') !== false) {
                                            $blockData = preg_replace('/\s+/', ' ', $blockData);
                                            $blockData = preg_replace('/\/\*.*?\*\//', '', $blockData);
                                            $buffer .= trim($blockData)."\n";
                                            $blockData = '';
                                        }
                                    }
                                }
                            } else {
                                $buffer .= 'File not found';
                            }
                        }
                    }
                    $cache->writeCache($buffer);
                }
                $cssCache = new opCSSFile(str_replace(DOCUMENT_ROOT, '', $cache->cacheLocation).$cssName);
                if ($media != 'none') {
                    $media = explode('_', $media);
                    $cssCache->setMedia(implode(',', $media));
                }
                $this->addCSS($cssCache);
            }
            
            foreach ($conditionalCSS as $cssFile) {
                $this->addCSS($cssFile);
            }
        }
        
        return $this->getCSS();
    }

    final protected function getCompressedJS() {
        if ($this->compressJS) {
            $checksum = '';
            foreach ($this->getJS() as $jsFile) {
                $pathinfo = pathinfo($jsFile->getFile());
                $checksum .= md5_file(DOCUMENT_ROOT.$jsFile->getFile());
                $checksum .= md5($pathinfo['basename']);
            }
            $jsName = md5($checksum).'.js';

            $cache = new opCache($jsName, $this->cacheTTL, false);
            if (! $cache->isCache()) {
                $buffer = '';
                foreach ($this->getJS() as $jsFile) {
                    if ($this->designMode && $jsFile->getDropInDesignMode()) {
                        continue;
                    } else {
                        $buffer .= "\n/* ".$jsFile->getFile()."\n-------------------------------------------------- */\n\r";
                        if (is_file(DOCUMENT_ROOT.$jsFile->getFile())) {
                            $buffer .= file_get_contents(DOCUMENT_ROOT.$jsFile->getFile())."\n\r";
                        } else {
                            $buffer .= 'File not found';
                        }
                    }
                }
                $cache->writeCache($buffer);
            }
            $this->removeAllJS();
            $this->addJS(new opJSFile(str_replace(DOCUMENT_ROOT, '', $cache->cacheLocation).$jsName));
        }
        return $this->getJS();
    }
}
?>