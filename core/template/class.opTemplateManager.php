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
class opTemplateManager {
    protected $masterTemplate, $masterCollection, $masterTagCollection;

    public function __construct($masterTemplate = false) {
        $this->masterTemplate = $masterTemplate;
        $this->masterCollection = array();
        $this->masterTagCollection = array();
        $this->getTags();
    }

    public function addCollection(opTemplateCollection $templateCollection) {
        $this->masterCollection[] = $templateCollection;
    }

    public function render() {
        if (!$this->masterTemplate) {
            return 'masterTemplate not set.';
        } else {
            /*
            $data = explode(chr(10), $this->renderMasterCollection());
            # Clean out extra line breaks and spacing from sourcecode
            $finalOutput = '';
            foreach ($data as $line) {
                if (strlen(trim($line)) > 0) {
                    $finalOutput .= $line.chr(10);
                }
            }
            return $finalOutput;
            */
            return $this->renderMasterCollection();
        }
    }
    
    protected function getTags() {
        $tagCollection = explode('{', $this->masterTemplate);
        foreach ($tagCollection as $tag) {
            if (strpos($tag, '}') === false) {
                continue;
            } else {
                list($a, $b) = explode('}', $tag);
                if (strpos($a, ':') > 0) {
                    $pieces = explode(':', $a);
                    $tagName = (isset($pieces[0])) ? $pieces[0] : $a;
                } else {
                    $tagName = $a;
                }
                $this->masterTagCollection['{'.$a.'}'] = $tagName;
            }
        }
    }

    protected function findRealTag($tag) {
        foreach ($this->masterTagCollection as $realTag => $fakeTag) {
            if ($fakeTag == $tag) {
                return $realTag;
            }
        }
        return false;
    }

    protected function removeTag($tag) {
        if (isset($this->masterTagCollection[$tag])) {
            unset($this->masterTagCollection[$tag]);
        }
    }

    protected function isWrap($tag) {
        if (isset($this->masterTagCollection['{'.$tag.':wrapStart}']) &&
            isset($this->masterTagCollection['{'.$tag.':wrapContent}']) &&
            isset($this->masterTagCollection['{'.$tag.':wrapEnd}'])) {
            return true;
        }
    }

    protected function wrapData($tag, $templateData) {
        list($garbage, $data) = explode('{'.$tag.':wrapStart}', $this->masterTemplate);
        list($data, $garbage) = explode('{'.$tag.':wrapEnd}', $data);
        return str_replace('{'.$tag.':wrapContent}', $templateData, $data);
    }

    protected function cleanWrap($tag) {
        $fromPosition = strpos($this->masterTemplate, '{'.$tag.':wrapStart}');
        $toPosition = strpos($this->masterTemplate, '{'.$tag.':wrapEnd}');
        if ($fromPosition > 0 && $toPosition > 0) {
            return substr($this->masterTemplate, 0, $fromPosition).substr($this->masterTemplate, $toPosition+strlen('{'.$tag.':wrapEnd}'));
        } else {
            return $this->masterTemplate;
        }
    }

    protected function renderMasterCollection() {
        # Populate tags
        foreach ($this->masterCollection as $templateCollection) {
            $realTag = $this->findRealTag($templateCollection->getTag());
            $collectionData = '';
            foreach ($templateCollection->renderTemplates() as $templateData) {
                # Do we need to wrap the templateData?
                if ($this->isWrap($templateCollection->getTag())) {
                    $collectionData .= $this->wrapData($templateCollection->getTag(), $templateData);
                } else {
                    $collectionData .= $templateData;
                }
            }
            # Assign collectionData to tag
            $this->masterTemplate = str_replace($realTag, $collectionData, $this->masterTemplate);
            $this->removeTag($realTag);
        }
        # Remove unused tags
        foreach ($this->masterTagCollection as $realTag => $fakeTag) {
            if ($this->isWrap($fakeTag)) {
                $this->masterTemplate = $this->cleanWrap($fakeTag);
            }
            $this->masterTemplate = str_replace($realTag, '', $this->masterTemplate);
        }
        return $this->masterTemplate;
    }
}
?>