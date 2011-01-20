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
class opCaptcha {

    public function render() {
        $captchaImage = imagecreatetruecolor(200, 60);
        //$borderColor = imagecolorallocate($captchaImage, 20, 20, 20);
        $fillColor = imagecolorallocate($captchaImage, 242, 242, 242);

        //imagefilledrectangle($captchaImage, 0, 0, 200, 60, $borderColor);
        //imagefilledrectangle($captchaImage, 2, 2, 197, 57, $fillColor);
        imagefilledrectangle($captchaImage, 0, 0, 200, 60, $fillColor);

        $captchaImage = $this->addLines($captchaImage);
        $captchaImage = $this->addText($captchaImage, $this->generateCode());
        
        header ('Content-type: image/jpeg');
        imagejpeg($captchaImage, null, 100);
        imagedestroy($captchaImage);
    }

    public function authenticate($codeToValidate) {
        if (isset($_SESSION[get_class($this)]) && strtoupper($codeToValidate) == $_SESSION[get_class($this)]) {
            return true;
        } else {
            return false;
        }
    }

    private function addLines($captchaImage) {
        for ($i = 1; $i < 20; $i++) {
            //imageline($captchaImage, $i*10, 2, $i*10, 57, imagecolorallocate($captchaImage, 120, 120, 120));
            imageline($captchaImage, $i*10, 0, $i*10, 60, imagecolorallocate($captchaImage, 221, 221, 221));
        }
        for ($i = 1; $i < 6; $i++) {
            //imageline($captchaImage, 2, $i*10, 197, $i*10, imagecolorallocate($captchaImage, 120, 120, 120));
            imageline($captchaImage, 0, $i*10, 200, $i*10, imagecolorallocate($captchaImage, 221, 221, 221));
        }
        return $captchaImage;
    }

    private function addText($captchaImage, $str) {
        for ($i = 1; $i < strlen($str)+1; $i++) {
            $txtColor = imagecolorallocate($captchaImage, rand(100, 180), rand(100, 180), rand(100, 180));
            imagestring($captchaImage, 5, 25*$i, rand(2, 6)*5, $str[$i-1], $txtColor);
        }
        return $captchaImage;
    }

    private function generateCode() {
        $codeLength = 6;
        $codeChar = 'AEIYU23456789FGHTRWSZXVNMKPJ';
        $code = '';
        for ($i = 0; $i < $codeLength; $i++) {
            $code .= $codeChar[rand(0, strlen($codeChar)-1)];
        }
        $_SESSION[get_class($this)] = $code;
        return $code;
    }
}
?>
