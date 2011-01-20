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
class opGraphics {
    protected $file, $tempDir;

    public function __construct(opFileType $file, $tempDir) {
        $this->file     = $file;
        $this->tempDir  = $tempDir;
    }

    public function crop($x1, $x2, $y1, $y2, $q = 75) {
        $src    = $this->getResource();
        $dest   = imagecreatetruecolor($x2-$x1, $y2-$y1);
        imagecopyresampled($dest, $src, 0, 0, $x1, $y1, $x2-$x1, $y2-$y1, $x2-$x1, $y2-$y1);

        $tmpName = $this->save($dest, $q);

        imagedestroy($src);
        imagedestroy($dest);
        return $tmpName;
    }

    public function resize($nW, $nH, $q) {
        $src  = $this->getResource();
        $dest = imagecreatetruecolor($nW, $nH);
        imagecopyresampled($dest, $src, 0, 0, 0, 0, $nW, $nH, imagesx($src), imagesy($src));

        $tmpName = $this->save($dest, $q);

        imagedestroy($src);
        imagedestroy($dest);
        return $tmpName;
    }

    protected function save(&$resource, $q) {
        if (is_writable($this->tempDir)) {
            $tmpName = md5(rand(0,5000).$this->file->getBasePath().$this->file->getFilename().microtime()).'.'.$this->file->getExtension();
            switch ($this->file->getExtension()) {
                case 'jpg':
                    imagejpeg($resource, $this->tempDir.$tmpName, $q);
                    break;
                case 'gif':
                    imagegif($resource, $this->tempDir.$tmpName);
                    break;
                case 'png':
                    imagepng($resource, $this->tempDir.$tmpName);
                    break;

            }
            return $tmpName;
        }
        return false;
    }

    protected function getResource() {
        switch ($this->file->getExtension()) {
            case 'jpg':
                $src = imagecreatefromjpeg($this->file->getBasePath().$this->file->getFilename());
                break;
            case 'gif':
                $src = imagecreatefromgif($this->file->getBasePath().$this->file->getFilename());
                break;
            case 'png':
                $src = imagecreatefrompng($this->file->getBasePath().$this->file->getFilename());
                break;
            default:
                $src = false;
        }
        return $src;
    }
}
?>