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
class opFormElementTagList extends opFormElement {
    protected $sourceData;
    protected $tagBtnTitle, $tagMsg;
    protected $tags = array();

    public function setSourceData($data) {

    }

    public function addTag($tag) {
        $this->tags[] = '<li><span class="tag"><span>' . htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') . '</span><img src="/themes/opAdmin/images/icons/cross-small-gray.png" width="16" height="16" border="0" class="tagIcon" onclick="$(this).parent().parent().remove();" /></span></li>';
    }

    public function setTagBtnTitle($title) {
        $this->tagBtnTitle = $title;
    }

    public function setTagMsg($msg) {
        $this->tagMsg = $msg;
    }

    public function getValue() {
        if (is_array($this->elementValue)) {
            foreach ($this->elementValue as $key => $tag) {
                if (strlen(trim($tag)) > 0) {
                    $this->elementValue[$key] = trim($tag);
                } else {
                    unset($this->elementValue[$key]);
                }
            }
        }

        return $this->elementValue;
    }

    public function getHtml() {
        if (is_array($this->getValue())) {
            $this->tags = array();
            foreach ($this->getValue() as $tag) {
                $this->addTag($tag);
            }
        }
        return '<div id="tagArea">
                    <h6>'.$this->elementLabel.'</h6>
                    <ul id="tagList">'.implode("\n", $this->tags).'</ul>
                    <div id="tagControl">
                        <span class="tagShadow"><input type="text" id="tagText" name="'.$this->elementName.'[]" maxlength="30" /></span><a class="tagBtn" href="javascript:addTag()" title="'.$this->tagBtnTitle.'"><span><img src="/themes/opAdmin/images/icons/tag.png" width="16" height="16" border="0" alt="'.$this->tagBtnTitle.'" class="btn-icon" /> </span></a>
                    </div>
                    <div id="tagControlMsg">'.$this->tagMsg.'</div>
                </div>';
    }
}
?>
