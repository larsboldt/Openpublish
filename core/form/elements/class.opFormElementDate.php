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
class opFormElementDate extends opFormElement {
    public function __construct($name = null, $label = null) {
        $this->setName($name);
        $this->setLabel($label);
        $this->addClass('form_txt');
    }
  
    public function getHtml() {
        $script = '<script>
                    $(document).ready(function() {
                        $(\'#'.$this->elementName.'\').datepicker({minDate: new Date(), dateFormat: \'yy-mm-dd\', constrainInput: true, onSelect: function(dateText, inst) {
                            $(this).attr(\'value\', dateText);
                            $(this).datepicker(\'hide\');
                            }
                        });
                    });
                    </script>';
        return $script.'<label for="' . $this->elementName . '">' . $this->elementLabel . '</label><span class="input-shadow"><input type="text" class="'.implode(' ', $this->elementClass).'" id="' . $this->elementName . '" name="' . $this->elementName . '" value="' . $this->elementValue . '" maxlength="10" /></span><span class="btn-refresh"><a href="javascript:;" onclick="javascript:$(\'#'.$this->elementName.'\').datepicker(\'show\');" id="datePickerBtn_'.$this->elementName.'" class="form_btn_input" title="'.opTranslation::getTranslation('_select_date').'"><span><img src="/themes/opAdmin/images/icons/calendar-day.png" width="16" height="16" border="0" alt="'.opTranslation::getTranslation('_select_date').'" /></span></a></span>';
    }
}
?>