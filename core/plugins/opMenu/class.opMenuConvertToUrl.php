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
class opMenuConvertToUrl {
    public static function convert($str, $toLower = false) {
        # Convert extended latin chars
        $str = self::extendedLatinToAscii($str);

        # Convert spaces to dashes
        $str = preg_replace('/[ ]/i', '-', $str);

        # Keep only a-z, 0-9, - and _
        $str = preg_replace('/[^a-z0-9-_]/i', '', $str);

        # To lowercase?
        $str = ($toLower) ? strtolower($str) : $str;

        # Return false if unable to convert to ascii/latin
        return (strlen($str) > 0) ? $str : false;
    }

    protected static function extendedLatinToAscii($str) {
        $charMap = array(
            'à' => 'a', 'ô' => 'o', 'ď' => 'd', 'ë' => 'e', 'š' => 's', 'ơ' => 'o',
            'ß' => 'ss', 'ă' => 'a', 'ř' => 'r', 'ț' => 't', 'ň' => 'n', 'ā' => 'a',
            'ķ' => 'k', 'ŝ' => 's', 'ỳ' => 'y', 'ņ' => 'n', 'ĺ' => 'l', 'ħ' => 'h',
            'ó' => 'o', 'ú' => 'u', 'ě' => 'e', 'é' => 'e', 'ç' => 'c', 'ẁ' => 'w',
            'ċ' => 'c', 'õ' => 'o', 'ø' => 'oe', 'ģ' => 'g', 'ŧ' => 't', 'ș' => 's',
            'ė' => 'e', 'ĉ' => 'c', 'ś' => 's', 'î' => 'i', 'ű' => 'u', 'ć' => 'c',
            'ę' => 'e', 'ŵ' => 'w', 'ū' => 'u', 'č' => 'c', 'ö' => 'oe', 'è' => 'e',
            'ŷ' => 'y', 'ą' => 'a', 'ł' => 'l', 'ų' => 'u', 'ů' => 'u', 'ş' => 's',
            'ğ' => 'g', 'ļ' => 'l', 'ƒ' => 'f', 'ž' => 'z', 'ẃ' => 'w', 'å' => 'aa',
            'ì' => 'i', 'ï' => 'i', 'ť' => 't', 'ŗ' => 'r', 'ä' => 'ae', 'í' => 'i',
            'ŕ' => 'r', 'ê' => 'e', 'ü' => 'ue', 'ò' => 'o', 'ē' => 'e', 'ñ' => 'n',
            'ń' => 'n', 'ĥ' => 'h', 'ĝ' => 'g', 'đ' => 'd', 'ĵ' => 'j', 'ÿ' => 'y',
            'ũ' => 'u', 'ŭ' => 'u', 'ư' => 'u', 'ţ' => 't', 'ý' => 'y', 'ő' => 'o',
            'â' => 'a', 'ľ' => 'l', 'ẅ' => 'w', 'ż' => 'z', 'ī' => 'i', 'ã' => 'a',
            'ġ' => 'g', 'ō' => 'o', 'ĩ' => 'i', 'ù' => 'u', 'į' => 'i', 'ź' => 'z',
            'á' => 'a', 'û' => 'u', 'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u',
            'ĕ' => 'e', 'À' => 'A', 'Ô' => 'O', 'Ď' => 'D', 'Ë' => 'E', 'Š' => 'S',
            'Ơ' => 'O', 'Ă' => 'A', 'Ř' => 'R', 'Ț' => 'T', 'Ň' => 'N', 'Ā' => 'A',
            'Ķ' => 'K', 'Ŝ' => 'S', 'Ỳ' => 'Y', 'Ņ' => 'N', 'Ĺ' => 'L', 'Ħ' => 'H',
            'Ó' => 'O', 'Ú' => 'U', 'Ě' => 'E', 'É' => 'E', 'Ç' => 'C', 'Ẁ' => 'W',
            'Ċ' => 'C', 'Õ' => 'O', 'Ø' => 'OE', 'Ģ' => 'G', 'Ŧ' => 'T', 'Ș' => 'S',
            'Ė' => 'E', 'Ĉ' => 'C', 'Ś' => 'S', 'Î' => 'I', 'Ű' => 'U', 'Ć' => 'C',
            'Ę' => 'E', 'Ŵ' => 'W', 'Ū' => 'U', 'Č' => 'C', 'Ö' => 'Oe', 'È' => 'E',
            'Ŷ' => 'Y', 'Ą' => 'A', 'Ł' => 'L', 'Ų' => 'U', 'Ů' => 'U', 'Ş' => 'S',
            'Ğ' => 'G', 'Ļ' => 'L', 'Ƒ' => 'F', 'Ž' => 'Z', 'Ẃ' => 'W', 'Å' => 'AA',
            'Ì' => 'I', 'Ï' => 'I', 'Ť' => 'T', 'Ŗ' => 'R', 'Ä' => 'Ae', 'Í' => 'I',
            'Ŕ' => 'R', 'Ê' => 'E', 'Ü' => 'Ue', 'Ò' => 'O', 'Ē' => 'E', 'Ñ' => 'N',
            'Ń' => 'N', 'Ĥ' => 'H', 'Ĝ' => 'G', 'Đ' => 'D', 'Ĵ' => 'J', 'Ÿ' => 'Y',
            'Ũ' => 'U', 'Ŭ' => 'U', 'Ư' => 'U', 'Ţ' => 'T', 'Ý' => 'Y', 'Ő' => 'O',
            'Â' => 'A', 'Ľ' => 'L', 'Ẅ' => 'W', 'Ż' => 'Z', 'Ī' => 'I', 'Ã' => 'A',
            'Ġ' => 'G', 'Ō' => 'O', 'Ĩ' => 'I', 'Ù' => 'U', 'Į' => 'I', 'Ź' => 'Z',
            'Á' => 'A', 'Û' => 'U', 'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'AE', 'Ĕ' => 'E'
        );
        return str_replace(array_keys($charMap), array_values($charMap), $str);
    }
}
?>