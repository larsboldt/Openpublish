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
class opFileFactory {
    /**
     * @return bool|opFileType
     */
    public static function identify($file) {
        # small path fix
        $tmp = str_replace(DOCUMENT_ROOT,'',$file);
        if (substr($tmp, 0, 1) == '/') {
            $file = DOCUMENT_ROOT.substr($tmp, 1);
        }

        if (is_file($file)) {
            $fileLocator = opSystem::getFileLocatorInstance();
            $pathinfo = pathinfo($file);
            $extClass = 'op'.strtoupper($pathinfo['extension']).'File';
            if ($fileLocator->findAndLoad($extClass)) {
                $fileClass = new $extClass($file);
                if ($fileClass instanceof opFileType) {
                    return $fileClass;
                } else {
                    die(get_class($fileClass).' does not extend the class opFileType.');
                }
            } else {
                return new opFileType($file);
            }
        } else {
            return false;
        }
    }
}
?>
