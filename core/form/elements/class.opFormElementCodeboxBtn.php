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
class opFormElementCodeboxBtn {
    protected $title, $code, $icon;

    public function __construct($title) {
        if (!empty($title)) {
            $this->title = $title;
        } else {
            throw new Exception('CodeboxBtn must have a title');
        }
    }

    public function setIcon($path) {
        $this->icon = $path;
    }

    public function getIcon() {
        return '<img src="'.$this->icon.'" alt="'.$this->getTitle().'" />';
    }

    public function getTitle() {
        return $this->title;
    }

    public function setCode($str) {
        $this->code = $str;
    }

    public function getCode() {
        return $this->code;
    }

    public function render($elementName) {
        return '<a href="javascript:'.$elementName.'_insertAtCaret(\''.$this->getCode().'\');" title="'.$this->getTitle().'">'.$this->getIcon().'</a>';
    }
}
?>