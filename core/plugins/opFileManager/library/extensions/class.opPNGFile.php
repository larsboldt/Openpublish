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
class opPNGFile extends opGraphicsFile {
    private $width;
    private $height;

    public function __construct($file) {
        $this->fileInfo = pathinfo($file);
        list($this->width, $this->height) = getimagesize($this->getBasePath().$this->getFilename());
    }

    public function getWidth() {
        return $this->width;
    }

    public function getHeight() {
        return $this->height;
    }

    protected function generateThumbnail($maxsize, $destPath) {
        return $this->resizeImage($this->getWidth(), $this->getHeight(), $maxsize, $this->getBasePath().$this->getFilename(), $destPath);
    }

    private function resizeImage($originalWidth, $originalHeight, $newMaxSize, $sourcePath, $destPath) {
        if (is_writable($destPath)) {
            $imRS = imagecreatefrompng($sourcePath);
            imageAlphaBlending($imRS, false);
            if ($imRS) {
                if ($newMaxSize >= $originalHeight && $newMaxSize >= $originalWidth) {
                    copy($sourcePath, $destPath.$newMaxSize.'_'.$this->getFilename());
                    imagedestroy($imRS);
                    return true;
                } else if ($originalWidth > $originalHeight) {
                    $newWidth = $newMaxSize;
                    $newHeigth = ceil($originalHeight*($newMaxSize/$originalWidth));
                } else if ($originalWidth == $originalHeight) {
                    $newWidth = $newMaxSize;
                    $newHeigth = $newMaxSize;
                } else {
                    $newWidth = ceil($originalWidth*($newMaxSize/$originalHeight));
                    $newHeigth = $newMaxSize;
                }
                $imRS_resized = imagecreatetruecolor($newWidth, $newHeigth);
                imageAlphaBlending($imRS_resized, false);
                imagecopyresampled($imRS_resized, $imRS, 0, 0, 0, 0, $newWidth, $newHeigth, $originalWidth, $originalHeight);
                imageSaveAlpha($imRS_resized, true);
                imagepng($imRS_resized, $destPath.$newMaxSize.'_'.$this->getFilename());
                imagedestroy($imRS_resized);
                imagedestroy($imRS);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
?>