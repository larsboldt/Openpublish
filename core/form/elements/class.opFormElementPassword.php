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
class opFormElementPassword extends opFormElement {
    private $maxlen, $auto, $icon;

    public function __construct($name = null, $label = null, $maxlen = false, $auto = false, $icon = false) {
        $this->setName($name);
        $this->setLabel($label);
        $this->maxlen   = $maxlen;
        $this->auto     = $auto;
        $this->icon     = $icon;
        $this->addClass('form_txt');
    }

    public function getHtml() {
        if ($this->auto) {
            $autoBtn = ($this->icon) ? '<span class="btn-refresh"><a href="#" onclick="auto_generate_password(\''. $this->elementName .'\');" class="form_btn_input" title="'.opTranslation::getTranslation('_auto_generate').'"><span><img src="'.$this->icon.'" width="16" height="16" border="0" alt="'.opTranslation::getTranslation('_auto_generate').'" /></span></a></span>' : ' <span class="btn-refresh"><a href="#" onclick="auto_generate_password(\''. $this->elementName .'\');" class="form_btn_input" title="'.opTranslation::getTranslation('_auto_generate').'"><span>'.opTranslation::getTranslation('_auto_generate').'</span></a></span>';
        } else {
            $autoBtn = '';
        }
        return '<label for="' . $this->elementName . '">' . $this->elementLabel . '</label><span class="input-shadow"><input type="text" class="'.implode(' ', $this->elementClass).'" tabindex="' . $this->elementTabIndex . '" id="' . $this->elementName . '" name="' . $this->elementName . '"'.((!$this->maxlen) ? '' : ' maxlength="'.$this->maxlen.'"').' value="' . $this->sanitizeFormData($this->elementValue) . '"/></span>'.$autoBtn;
    }
}
?>
