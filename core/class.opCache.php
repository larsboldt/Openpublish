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
class opCache {
    public $cacheLocation = false;
    protected $cacheFile = false;
    protected $cacheLifetime = '3600';
    protected $useCacheExtension = true;
    protected $cacheExtension = '.cache';
    
    public function __construct($cacheFile, $ttl = 3600, $useCacheExtension = true) {
        $this->cacheFile = $cacheFile;
        $this->cacheLifetime = $ttl;
        $this->cacheLocation = DOCUMENT_ROOT.'/files/cache/';
        $this->cacheExtension = ($useCacheExtension) ? '.cache' : '';
    }

    public function isCache() {
        if (is_file($this->cacheLocation.$this->cacheFile.$this->cacheExtension) &&
            gmdate('YmdHis', (filemtime($this->cacheLocation.$this->cacheFile.$this->cacheExtension)+intval($this->cacheLifetime))) >= gmdate('YmdHis')) {
            return true;
        } else {
            return false;
        }
    }

    public function getCache() {
        if (is_file($this->cacheLocation.$this->cacheFile.'.lock')) {
            return false;
        } else {
            return file_get_contents($this->cacheLocation.$this->cacheFile.$this->cacheExtension);
        }
    }

    public function writeCache($cache) {
        if (is_writeable($this->cacheLocation)) {
            file_put_contents($this->cacheLocation.$this->cacheFile.'.lock', 'lock');
            file_put_contents($this->cacheLocation.$this->cacheFile.$this->cacheExtension, $cache, LOCK_EX);
            unlink($this->cacheLocation.$this->cacheFile.'.lock');
        }
        return $cache;
    }

    public function clearCache() {
        if (is_file($this->cacheLocation.$this->cacheFile.$this->cacheExtension)) {
            unlink($this->cacheLocation.$this->cacheFile.$this->cacheExtension);
        }
    }
}
?>