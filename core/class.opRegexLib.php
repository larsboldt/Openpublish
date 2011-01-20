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
class opRegexLib {
    /**
     * Rewrites string to a valid OS filename
     * @param string $fileName
     * @return string
     */
    public static function rewriteFileName($fileName) {
        return preg_replace('/[^-_.\w]/', '-', $fileName);
    }

    /**
     * Checks string against email pattern, does not check top level domain
     * @param string $str
     * @return bool
     */
    public static function isEmail($str) {
        return preg_match('/^[\w!#$%&\'*+\/=?`{|}~^-]+(?:\.[!#$%&\'*+\/=?`{|}~^-]+)*@[A-Z0-9-]+(?:\.[A-Z0-9-]+)*$/i', $str);
    }

    /**
     * Checks string aginst email pattern with a top level domain from 2-6 characters
     * @param string $str
     * @return bool
     */
    public static function isEmailWithTLD($str) {
        return preg_match('/^[\w!#$%&\'*+\/=?`{|}~^-]+(?:\.[!#$%&\'*+\/=?`{|}~^-]+)*@(?:[A-Z0-9-]+\.)+[A-Z]{2,6}$/i', $str);
    }

    /**
     * Strips string of space and hyphens and then verifies creditcard number against VISA, Mastercard, Discover, AMEX, Diners Club and JCB
     * @param string $str
     * @return bool
     */
    public static function isCC($str) {
        return preg_match('/^(?:
                          (4[0-9]{12}(?:[0-9]{3})?) |
                          (5[1-5][0-9]{14}) |
                          (6(?:011|5[0-9][0-9])[0-9]{12}) |
                          (3[47][0-9]{13}) |
                          (3(?:0[0-5]|[68][0-9])[0-9]{11}) |
                          ((?:2131|1800|35\d{3})\d{11})
                          )$/', preg_replace('/\D/', '', $str));
    }
}
?>