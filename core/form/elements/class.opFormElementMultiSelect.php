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
class opFormElementMultiSelect extends opFormElement {
    private $options = array();
    private $elementSize;

    public function __construct($name = null, $label = null, $size = 5) {
        $this->setName($name);
        $this->setLabel($label);
        $this->elementSize = $size;
        $this->addClass('form_select');
    }

    public function addOption($value, $text) {
        $this->options[$value] = $text;
    }

    public function getHtml() {
        $options = '';
        foreach ($this->options as $k => $v) {
            $sel = ($this->elementValue == $k) ? ' selected="true"' : '';
            $options .= '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
        }
        return '<label for="' . $this->elementName . '">' . $this->elementLabel . '</label><select class="'.implode(' ', $this->elementClass).'" tabindex="' . $this->elementTabIndex . '" multiple="true" size="' . $this->elementSize . '" id="' . $this->elementName . '" name="' . $this->elementName . '">'.$options.'</select>';
    }
}
?>