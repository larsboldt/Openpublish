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
abstract class opGraphicsFile extends opFileType {
    abstract function getWidth();
    abstract function getHeight(); 

    public function getThumbnail($maxsize) {
        $cache = new opFileGraphicsCache($this, $maxsize);
        if ($cache->isCached()) {
            return $cache->getCachedFilePath();
        } else {
            if ($this->generateThumbnail($maxsize, DOCUMENT_ROOT.$cache->getCachedBasePath())) {
                if ($cache->isCached()) {
                    return $cache->getCachedFilePath();
                } else {
                    return false;
                }
            }
        }
    }

    public function removeThumbnail() {
        $file = new opFileCache($this);
        $file->deleteCachedFile();
    }

    protected function generateThumbnail($maxsize, $destPath) {
        return false;
    }
}
?>