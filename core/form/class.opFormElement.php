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
abstract class opFormElement {
    protected $elementSanitize = true;
    protected $elementTabIndex = 0;
    protected $elementName = null;
    protected $elementLabel = null;
    protected $elementValidators = array();
    protected $elementValue = null;
    protected $elementErrors = array();
    protected $elementClass = array();

    public function __construct($name = null, $label = null) {
        $this->setName($name);
        $this->setLabel($label);
    }

    public function setName($name) {
        $this->elementName = $name;
    }

    public function getName() {
        return $this->elementName;
    }

    public function setLabel($label) {
        $this->elementLabel = $label;
    }

    public function getLabel() {
        return $this->elementLabel;
    }

    public function setValue($string) {
        $this->elementValue = $string;
    }

    public function getValue() {
        return $this->elementValue;
    }

    public function setSanitize($boolean) {
        $this->elementSanitize = ($boolean) ? true : false;
    }

    public function getSanitize() {
        return $this->elementSanitize;
    }

    public function setTabIndex($int) {
        if (is_numeric($int)) {
            $this->elementTabIndex = $int;
        }
    }

    public function getTabIndex() {
        return $this->elementTabIndex;
    }

    public function addValidator(opFormValidate $validator) {
        $this->elementValidators[] = $validator;
    }

    public function isValid($string) {
        $valid = true;
        foreach ($this->getValidators() as $validator) {
            if (! $validator->isValid($string)) {
                $valid = false;
                $this->elementErrors[] = $validator->getError();
            }
        }
        return $valid;
    }

    public function getErrors() {
        return $this->elementErrors;
    }

    public function getValidators() {
        return $this->elementValidators;
    }

    protected function sanitizeFormData($value) {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8', false);
    }

    public function addClass($className) {
        $this->elementClass[] = $className;
    }

    abstract public function getHtml();
}
?>
