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
class opCSSFile {
    private $cssFile, $cssMedia, $cssConditional, $dropInDesignMode;

    public function __construct($file = 'Unset') {
        $this->cssMedia         = false;
        $this->cssConditional   = false;
        $this->dropInDesignMode = false;
        
        $this->cssFile          = $file;
    }

    public function getFile() {
        return $this->cssFile;
    }

    public function setMedia($media) {
        $this->cssMedia = $media;
    }

    public function getMedia() {
        return $this->cssMedia;
    }

    public function setConditional($conditional) {
        $this->cssConditional = $conditional;
    }

    public function getConditional() {
        return $this->cssConditional;
    }

    public function setDropInDesignMode($boolean) {
        $this->dropInDesignMode = ($boolean && $boolean != 'false') ? true : false;
    }

    public function getDropInDesignMode() {
        return $this->dropInDesignMode;
    }

    public function render() {
        $cssConditionalStart = (! $this->cssConditional) ? '' : '<!-- [if '.$this->cssConditional.']>'.chr(13);
        $cssConditionalEnd   = (! $this->cssConditional) ? '' : chr(13).'<![endif]-->';
        $cssMedia            = (! $this->cssMedia) ? '' : ' media="'.$this->cssMedia.'"';
        return $cssConditionalStart.'<link href="'.$this->cssFile.'" rel="stylesheet" type="text/css"'.$cssMedia.' />'.$cssConditionalEnd;
    }
}
?>