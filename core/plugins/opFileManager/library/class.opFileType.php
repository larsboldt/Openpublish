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
class opFileType {
    protected $fileInfo;

    public function __construct($file) {
        $this->fileInfo = pathinfo($file);
    }

    public function getSize() {
        return filesize($this->getBasePath().$this->getFilename());
    }

    public function getSizeAsString() {
        return (round($this->getSize()/1024,0) >= 1000) ? round(($this->getSize()/1024)/1024,2).' MB' : round($this->getSize()/1024,0).' kB';
    }

    public function getExtension() {
        return strtolower($this->fileInfo['extension']);
    }

    public function getBasePath() {
        return $this->fileInfo['dirname'].'/';
    }

    public function getRelativePath() {
        return '/'.str_replace(DOCUMENT_ROOT, '', $this->getBasePath());
    }

    public function getMTime() {
        return filemtime($this->getBasePath().$this->getFilename());
    }

    public function getFilename() {
        return $this->fileInfo['basename'];
    }

    public function getFilenameNoExt() {
        return $this->fileInfo['filename'];
    }
}
?>
