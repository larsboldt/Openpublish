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
class opFormValidateStringLength extends opFormValidate {
    private $min = false;
    private $max = false;

    /**
     * @param int $min
     * @param int $max
     */
    public function __construct($min, $max) {
        $this->min = intval($min);
        $this->max = intval($max);
    }

    public function isValid($string) {
        $string = trim($string);
        if (mb_strlen($string) >= $this->min && mb_strlen($string) <= $this->max) {
            return true;
        }
        $this->setError(sprintf(opTranslation::getTranslation('_validate_strlen'), $this->min, $this->max));
        return false;
    }
}
?>
