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
class opFormElementDateFormat extends opFormElement {
    protected $system;

    public function __construct($name = null, $label = null, $system = true) {
        $this->setName($name);
        $this->setLabel($label);
        $this->system = $system;
        $this->addClass('form_select');
    }

    public function getHtml() {
        if ($this->system) {
            $options = '<option value="0">'.opTranslation::getTranslation('_system_standard').'</option>';
        } else {
            $options = '';
        }
        $dateFormats = array('d-m-Y', 'd/m/Y', 'd.m.Y', 'd.m.y', 'm-d-Y', 'm/d/Y', 'm.d.Y', 'm.d.y', 'F j, Y', 'D, d M Y');
        $dateObj     = new DateTime();
        foreach ($dateFormats as $format) {
            $sel = ($this->elementValue == $format) ? ' selected="true"' : '';
            $options .= '<option value="'.$format.'"'.$sel.'>'.$dateObj->format($format).'</option>';
        }
        return '<label for="' . $this->elementName . '">' . $this->elementLabel . '</label><select class="'.implode(' ', $this->elementClass).'" tabindex="' . $this->elementTabIndex . '" id="' . $this->elementName . '" name="' . $this->elementName . '">'.$options.'</select>';
    }
}
?>
