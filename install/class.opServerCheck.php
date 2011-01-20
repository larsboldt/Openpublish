<?php
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
class opServerCheck {
    public function passAll() {
        if (! $this->passPHPVersion()) { return false; }
        if (! $this->passGDVersion()) { return false; }
        if (! $this->isPDO()) { return false; }
        if (! $this->isSPL()) { return false; }
        //if (! $this->isZIP()) { return false; }
        if (! $this->isFilesWritable()) { return false; }
        if (! $this->isPluginsWritable()) { return false; }
        if (! $this->isTranslationsWritable()) { return false; }
        //if (! $this->isThemesWritable()) { return false; }
        if (! $this->isCURL()) { return false; }
        if (! $this->isHASH()) { return false; }
        if (! $this->isJSON()) { return false; }
        if (! $this->isMCRYPT()) { return false; }
        if (! $this->isSimpleXML()) { return false; }
        if (! $this->isMbString()) { return false; }

        return true;
    }

    public function passPHPVersion() {
        return version_compare("5.2", $this->phpVersion(), "<=");
    }

    public function passGDVersion() {
        return (intval($this->gdVersion()) >= 2);
    }

    public function phpVersion() {
        return phpversion();
    }

    public function isFilesWritable() {
        if (is_dir(DOCUMENT_ROOT.'/files')) {
            return is_writable(DOCUMENT_ROOT.'/files');
        } else {
            return false;
        }
    }

    public function isPluginsWritable() {
        if (is_dir(DOCUMENT_ROOT.'/plugins')) {
            return is_writable(DOCUMENT_ROOT.'/plugins');
        } else {
            return false;
        }
    }

    public function isTranslationsWritable() {
        if (is_dir(DOCUMENT_ROOT.'/translations')) {
            return is_writable(DOCUMENT_ROOT.'/translations');
        } else {
            return false;
        }
    }
    
    /*
    public function isThemesWritable() {
        if (is_dir(DOCUMENT_ROOT.'/themes')) {
            return is_writable(DOCUMENT_ROOT.'/themes');
        } else {
            return false;
        }
    }
    */
    public function gdVersion() {
        if (function_exists('gd_info')) {
            $gdV = gd_info();
            return preg_replace ('/[^\d.\s]/', '', $gdV['GD Version']);
        } else {
            return false;
        }
    }

    public function isSPL() {
        return class_exists('ArrayIterator', false);
    }

    public function isPDO() {
        return class_exists('PDO', false);
    }

    public function isCURL() {
        return function_exists('curl_init');
    }

    public function isHASH() {
        return function_exists('hash_hmac');
    }

    public function isJSON() {
        return function_exists('json_encode');
    }

    public function isMCRYPT() {
        return function_exists('mcrypt_encrypt');
    }

    public function isSimpleXML() {
        return function_exists('simplexml_load_file');
    }

    public function isMbString() {
        return function_exists('mb_strlen');
    }

    /*
    public function isZIP() {
        return class_exists('ZipArchive', false);
    }
    */
}
?>