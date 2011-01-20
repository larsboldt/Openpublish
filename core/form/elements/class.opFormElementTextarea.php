<?php
defined("_OP") or die("Access denied");
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
class opFormElementTextarea extends opFormElement {
    protected $class;

    public function __construct($name = null, $label = null, $class = true) {
        $this->setName($name);
        $this->setLabel($label);
        $this->class = $class;
        if ($this->class) {
            $this->addClass("form_txtarea");
        }
    }

    public function getHtml() {
        $span = ($this->class) ? "<span class=\"input-shadow\">" : "";
        $spanEnd = ($this->class) ? "</span>" : "";
        return "<label for=\"" . $this->elementName . "\">" . $this->elementLabel . "</label>".$span."<textarea  tabindex=\"" . $this->elementTabIndex . "\" class=\"".implode(" ", $this->elementClass)."\" id=\"" . $this->elementName . "\" name=\"" . $this->elementName . "\">" . $this->sanitizeFormData($this->elementValue) . "</textarea>".$spanEnd;
    }
}
?>
