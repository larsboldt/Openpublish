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
class opCookie {
    private $cookieName;
    private $cookieEncrypt;
    private $secretKey;

    /**
     * @param string $cookieName
     */
    public function __construct($cookieName = '', $cookieEncrypt = false, $secretKey = false) {
        $this->cookieName = $cookieName;
        $this->cookieEncrypt = $cookieEncrypt;
        $this->secretKey = $secretKey;
    }
    
    /**
     * Creates a cookie with the username and password for a remember-me function.
     * @param string $domain
     * @return bool Cookie may still not be created even if true is returned, false means cookie is not created. Check for cookie manually after page reload to verify.
     */
    public function set($cookieData, $expireDays = 30, $domain = '') {
        // expires in 30days
        $expires = time()+60*60*24*intval($expireDays);
        // store username/password/expiry date in cookie
        $cookieData = serialize(array('data' => $cookieData, 'expires' => $expires));

        if ($this->cookieEncrypt) {
            if ($this->secretKey) {
                // encrypt $cookieData using RIJNDAEL_256
                $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
                $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
                $cookieData = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->secretKey, $cookieData, MCRYPT_MODE_ECB, $iv);
            } else {
                return false;
            }
        }

        return setcookie($this->cookieName, $this->urlsafe_b64encode($cookieData), $expires, '/', $domain, false, true);
    }

    /**
     * Decrypt, validate and retrieve the cookie data.
     * @return bool|array Returns false if cookie is not set or fails expiry check. Returns array if cookie is set and validates.
     */
    public function get() {
        if (isset($_COOKIE[$this->cookieName])) {
            $cookieData = $this->urlsafe_b64decode($_COOKIE[$this->cookieName]);
            
            if ($this->cookieEncrypt) {
                if ($this->secretKey) {
                    // Decrypt cookie data
                    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
                    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
                    $cookieData = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->secretKey, $cookieData, MCRYPT_MODE_ECB, $iv),"\0");
                } else {
                    return false;
                }
            }

            $cookieData = unserialize($cookieData);
            if ($this->verifyCookieExpires($cookieData)) {
                return $cookieData['data'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Destroys the cookie.
     * @param string $domain
     * @return bool Cookie may still be present even if true is returned, false means cookie is still present. Check for cookie manually after page reload to verify.
     */
    public function remove($domain = '') {
        if (isset($_COOKIE[$this->cookieName])) {
            return setcookie($this->cookieName, '', time() - 3600, '/', $domain);
        } else {
            return false;
        }
    }

    /**
     * Verify expiry date of cookie.
     * @return bool - see get().
     */
    private function verifyCookieExpires($cookieData) {
        if (isset($cookieData['expires']) && time() <= $cookieData['expires']) {
            return true;
        } else {
            return false;
        }
    }

    private function urlsafe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_','.'),$data);
        return $data;
    }

    private function urlsafe_b64decode($string) {
        $data = str_replace(array('-','_','.'),array('+','/','='),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }
}
?>
