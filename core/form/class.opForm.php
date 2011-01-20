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
class opForm {
    protected $errorCollection      = array();
    protected $elementCollection    = array();
    protected $formAction           = null;
    protected $formMethod           = 'post';

    public function addElement(opFormElement $element) {
        if (null === $element->getName()) {
            throw new Exception('Element must have a name');
        }

        $this->elementCollection[$element->getName()] = $element;

        return $this;
    }

    public function setAction($action) {
        $this->formAction = $action;
        return $this;
    }

    public function setMethod($method) {
        $this->formMethod = $method;
        return $this;
    }

    public function render() {
        $output = '<form action="' . $this->formAction . '" method="' . $this->formMethod . '">';
        foreach ($this->elementCollection as $element) {
            if (count($element->getErrors()) > 0) {
                $errorArr = array();
                foreach ($element->getErrors() as $e) {
                    $errorArr[] = $e;
                }
                $this->errorCollection[$element->getLabel()] = $errorArr;
            }
            $output .= '<div>' . $element->getHtml() . '</div>';
        }
        $output .= '</form>';
        return $output;
    }

    public function isValid(array $data) {
        $valid = true;
        foreach ($this->getElements() as $key => $element) {
            if (isset($data[$key])) {
                $valid = $element->isValid($data[$key]) && $valid;
                $value = ($element->getSanitize()) ? $this->sanitizeFormData($data[$key]) : $data[$key];
                $element->setValue($value);
            } else {
                $valid = $element->isValid(null) && $valid;
                $element->setValue('0');
            }
        }
        return $valid;
    }

    public function getElements() {
        return $this->elementCollection;
    }

    protected function sanitizeFormData($value) {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = htmlspecialchars(trim($val), ENT_NOQUOTES, 'UTF-8', false);
            }
            return $value;
        } else {
            return htmlspecialchars(trim($value), ENT_NOQUOTES, 'UTF-8', false);
        }
    }
}
?>