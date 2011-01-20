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
class opFormElementCheckbox extends opFormElement {
    private $checked;

    public function __construct($name = null, $label = null, $checked = false) {
        $this->setName($name);
        $this->setLabel($label);
        $this->checked = $checked;
        $this->addClass('form_chkbox');
    }

    public function getHtml() {
        $this->checked = ($this->checked) ? ' checked="true"' : '';
        return '<div><input type="checkbox" tabindex="' . $this->elementTabIndex . '" id="' . $this->elementName . '" class="'.implode(' ', $this->elementClass).'" name="' . $this->elementName . '"'.$this->checked.' value="' . $this->elementValue . '" /> <label for="' . $this->elementName . '" class="form_chk_label">' . $this->elementLabel . '</label></div>';
    }
}
?>
