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
class opFileCache {
    protected $cacheBasePath = 'files/cache/';
    protected $file;
    protected $systemConfiguration;
    
    public function __construct(opFileType $file) {
        $this->file = $file;
        $this->systemConfiguration = opSystem::getSystemConfiguration();
        $this->generateCachePathForFile();
    }

    public function isCached() {
        return is_file(DOCUMENT_ROOT.$this->cacheBasePath.$this->getConvertedCachePath().$this->file->getFilename());
    }

    public function getCachedBasePath() {
        return $this->cacheBasePath.$this->getConvertedCachePath();
    }

    public function getCachedFilePath() {
        return $this->cacheBasePath.$this->getConvertedCachePath().$this->file->getFilename();
    }

    public function deleteCachedFile() {
        if ($this->isCached()) {
            @unlink(DOCUMENT_ROOT.$this->cacheBasePath.$this->getConvertedCachePath().$this->file->getFilename());
        }
    }

    protected function getConvertedCachePath() {
        return str_replace(DOCUMENT_ROOT.'files/store/', '', $this->file->getBasePath());
    }

    protected function generateCachePathForFile() {
        $folders = explode('/', $this->getConvertedCachePath());
        $folderPath = DOCUMENT_ROOT.$this->cacheBasePath;
        foreach ($folders as $k) {
            if (!is_dir($folderPath.$k)) {
                mkdir($folderPath.$k);
                chmod($folderPath.$k, octdec($this->systemConfiguration->dir_permission));
            }
            $folderPath = $folderPath.$k.'/';
        }
    }
}
?>