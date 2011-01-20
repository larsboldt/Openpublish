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
class opAdminForm extends opForm {
    private $SACValue       = false;
    private $SACStatus      = false;
    private $formCancelLink = false;
    private $icon           = false;
    private $title          = false;

    public function __construct($icon = false, $title = '') {
        $this->icon = $icon;
        $this->title = $title;

        $this->SACValue = (isset($_POST['frmSaveAndClose']) && $_POST['frmSaveAndClose'] == 1) ? true : false;
    }

    public function setCancelLink($link) {
        $this->formCancelLink = $link;
    }

    public function setSaveAndClose($bool) {
        $this->SACStatus = ($bool) ? true : false;
    }

    public function saveAndClose() {
        return $this->SACValue;
    }

    public function getErrors() {
        return $this->errorCollection;
    }

    public function getErrorsAsUl() {
        $html = '<ul>';
        foreach ($this->errorCollection as $k => $v) {
            $html .= '<li>'.$k.'<ul>';
            foreach ($v as $a => $b) {
                $html .= '<li>'.$b.'</li>';
            }
            $html .= '</ul></li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public function render() {
        $output = "<form id=\"adminForm\" action=\"" . $this->formAction . "\" method=\"" . $this->formMethod . "\">";
        if ($this->icon != false) {
            $this->icon = "<span class=\"heading-icon\"><img src=\"".$this->icon."\" class=\"table-icon\" width=\"16\" height=\"16\" border=\"0\" alt=\"\"/></span>";
        }
        $output .= "<h3>".$this->icon.$this->title."</h3>";
        $output .= "<div id=\"content-plugin\">";
        $i = 0;
        foreach ($this->elementCollection as $element) {
            $element->setTabIndex($i);
            if (! $element instanceof opFormElementTextheader && ! $element instanceof opFormElementTabGroup && ! $element instanceof opFormElementTabContent && ! $element instanceof opFormElementTabContentEnd) {
                $output .= "<div class=\"opAdminFormItem\">";
            }
            if (count($element->getErrors()) > 0) {
                $errorArr = array();
                foreach ($element->getErrors() as $e) {
                    $errorArr[] = $e;
                }
                $this->errorCollection[$element->getLabel()] = $errorArr;
                $element->addClass("elementError");
                $output .= $element->getHtml()."<div class=\"elementErrors\">".$this->getElementErrorsAsUl($errorArr)."</div>";
            } else {
                $output .= $element->getHtml();
            }
            if (! $element instanceof opFormElementTextheader && ! $element instanceof opFormElementTabGroup && ! $element instanceof opFormElementTabContent && ! $element instanceof opFormElementTabContentEnd) {
                $output .= "</div>";
            }
            $i++;
        }
        $cancel = ($this->formCancelLink) ? "<a class=\"form_btn\" href=\"".$this->formCancelLink."\" title=\"".opTranslation::getTranslation("_back")."\"><span><img src=\"/themes/opAdmin/images/icons/arrow-180-medium.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"".opTranslation::getTranslation("_back")."\" class=\"table-icon\" /> ".opTranslation::getTranslation("_back")."</span></a>" : "";
        $saveAndClose = ($this->SACStatus) ? "<input type=\"hidden\" id=\"frmSaveAndClose\" name=\"frmSaveAndClose\" value=\"0\" /><a class=\"form_btn\" href=\"javascript:$('#frmSaveAndClose').attr('value', 1);adminFormSubmit();\" title=\"".opTranslation::getTranslation("_save_and_close")."\"><span><img src=\"/themes/opAdmin/images/icons/tick-circle.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"".opTranslation::getTranslation("_save_and_close")."\" class=\"table-icon\" /> ".opTranslation::getTranslation("_save_and_close")."</span></a>" : "";
        $output .= "<div id=\"btn\"><a class=\"form_btn\" href=\"javascript:adminFormSubmit();\" title=\"".opTranslation::getTranslation("_save")."\"><span><img src=\"/themes/opAdmin/images/icons/tick.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"".opTranslation::getTranslation("_save")."\" class=\"table-icon\" /> ".opTranslation::getTranslation("_save")."</span></a>".$saveAndClose.$cancel."</div>";
        $output .= "</div></form>";
        
        return $output;
    }

    protected function getElementErrorsAsUl($errors) {
        $html = '<ul>';
        foreach ($errors as $error) {
            $html .= '<li>'.$error.'</li>';
        }
        $html .= '</ul>';
        return $html;
    }
}
?>