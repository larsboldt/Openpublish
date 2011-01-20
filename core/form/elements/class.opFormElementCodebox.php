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
class opFormElementCodebox extends opFormElement {
    protected $class;
    protected $btns;

    public function __construct($name = null, $label = null) {
        $this->btns = array();
        $this->setName($name);
        $this->setLabel($label);
        $this->addClass('form_codearea');
    }

    public function addBtn(opFormElementCodeboxBtn $btn) {
        $this->btns[] = $btn;
    }

    public function getHtml() {
        return $this->getScript()."<label for=\"" . $this->elementName . "\">" . $this->elementLabel . "</label><span class=\"input-shadow\"><textarea id=\"" . $this->elementName . "_ln\" class=\"opFormElementCodeboxLN\" readonly=\"true\" wrap=\"off\"></textarea><textarea wrap=\"off\" tabindex=\"" . $this->elementTabIndex . "\" class=\"".implode(" ", $this->elementClass)."\" id=\"" . $this->elementName . "\" name=\"" . $this->elementName . "\">" . $this->sanitizeFormData($this->elementValue) . "</textarea><div id=\"opFormElementCodeboxToolbar_".$this->elementName."\" class=\"opFormElementCodeboxToolbar\">".$this->renderBtns()."</div></span>";
    }

    protected function getScript() {
        return '<script>
                function '.$this->elementName.'_insertAtCaret(code) {
                    $(\'#'.$this->elementName.'\').insertAtCaretPos(code);
                }
                $(document).ready(function() {
                    $(\'#'.$this->elementName.'\').tabby();
                    $(\'#'.$this->elementName.'\').bind(\'keyup\', function(event) {
                        var text = $(this).val();
                        var split = text.split("\n");
                        var lines = "";
                        for (var i = 0; i < split.length; i++) {
                            lines += (i+1) + "\r\n";
                        }
                        $(\'#'.$this->elementName.'_ln\').val(lines);
                        $(\'textarea[id$='.$this->elementName.'_ln]\').scrollTop($(\'textarea[id$='.$this->elementName.']\').scrollTop());
                    });
                    $(\'#'.$this->elementName.'\').bind(\'scroll\', function() {
                        $(\'textarea[id$='.$this->elementName.'_ln]\').scrollTop($(\'textarea[id$='.$this->elementName.']\').scrollTop());
                    });
                    $(\'#'.$this->elementName.'_ln\').bind(\'scroll\', function() {
                        $(\'textarea[id$='.$this->elementName.']\').scrollTop($(\'textarea[id$='.$this->elementName.'_ln]\').scrollTop());
                    });

                    var text = $(\'#'.$this->elementName.'\').val();
                    var split = text.split("\n");
                    var lines = "";
                    for (var i = 0; i < split.length; i++) {
                        lines += (i+1) + "\r\n";
                    }
                    $(\'#'.$this->elementName.'_ln\').val(lines);
                });
                </script>';
    }

    protected function renderBtns() {
        $btnData = '';
        foreach ($this->btns as $btn) {
           $btnData .= $btn->render($this->elementName);
        }
        return $btnData;
    }
}
?>