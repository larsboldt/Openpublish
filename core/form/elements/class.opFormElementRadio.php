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
class opFormElementRadio extends opFormElement {
    private $options;

    public function __construct($name = null, $label = null) {
        $this->setName($name);
        $this->setLabel($label);
    }

    public function addOption($id, $label, $value, $checked) {
        $this->options[] = array($id, $label, $value, $checked);
    }

    public function getHtml() {
        $html = '<label>' . $this->elementLabel . '</label><ul class="radioList">';
        foreach ($this->options as $v) {
            if (isset($_POST[$this->elementName])) {
                $checked = ($_POST[$this->elementName] == $v[2]) ? ' checked="true"' : '';
            } else {
                $checked = ($v[3]) ? ' checked="true"' : '';
            }
            $html .= '<li><input type="radio" tabindex="' . $this->elementTabIndex . '" class="form_radio" id="' . $v[0] . '" name="' . $this->elementName . '"'.$checked.' value="' . $v[2] . '"/> <label class="form_radio_label" for="' . $v[0] . '">' . $v[1] . '</label></li>';
        }
        $html .= '</ul>';
        return $html;
    }
}
?>
