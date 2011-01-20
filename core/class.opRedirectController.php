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
class opRedirectController {
    protected $db, $urlRedirectMap;

    /**
     * Returns instance of opRedirectController
     * @param PDO $db
     */
    public function __construct(PDO $db) {
        $this->db             = $db;
        $this->urlRedirectMap = array();

        $this->updateRedirectMap();
    }

    /**
     * Returns true if $urlTo is not registered and successfully registered, false otherwise
     * @param string $urlFrom
     * @param string $urlTo
     * @return boolean
     */
    public function registerRedirectURL($urlFrom, $urlTo) {
        if (! $this->isRedirectToRegistered($urlTo)) {
            try {
                $this->unregisterRedirectURL($urlFrom);
                $rVal = $this->db->prepare('INSERT INTO op_redirect_controller (urlFrom, urlTo) VALUES (:urlFrom, :urlTo)');
                $rVal->execute(array('urlFrom' => $urlFrom, 'urlTo' => $urlTo));

                $this->updateRedirectMap();
                return true;
            } catch (PDOException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Returns true if $urlTo is registered and successfully unregistered, false otherwise
     * @param string $urlTo
     * @return boolean
     */
    public function unregisterRedirectToURL($urlTo) {
        if ($this->isRedirectToRegistered($urlTo)) {
            try {
                $rVal = $this->db->prepare('DELETE FROM op_redirect_controller WHERE urlTo = :urlTo');
                $rVal->execute(array('urlTo' => $urlTo));

                $this->updateRedirectMap();
                return true;
            } catch (PDOException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Returns true if $urlFrom is registered and successfully unregistered, false otherwise
     * @param string $urlTo
     * @return boolean
     */
    public function unregisterRedirectURL($urlFrom) {
        if ($this->isRedirectRegistered($urlFrom)) {
            try {
                $rVal = $this->db->prepare('DELETE FROM op_redirect_controller WHERE urlFrom = :urlFrom');
                $rVal->execute(array('urlFrom' => $urlFrom));

                $this->updateRedirectMap();
                return true;
            } catch (PDOException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Returns true if $urlFrom is registered, false otherwise
     * @param string $urlFrom
     * @return boolean
     */
    public function isRedirectRegistered($urlFrom) {
        return array_key_exists($urlFrom, $this->urlRedirectMap);
    }

    /**
     * Returns true if $urlTo is registered, false otherwise
     * @param string $urlTo
     * @return boolean
     */
    public function isRedirectToRegistered($urlTo) {
        return in_array($urlTo, $this->urlRedirectMap, true);
    }

    /**
     * Redirects with a 301 header from the $urlFrom to the registered $urlTo
     * @param string $urlFrom
     * @return boolean|void
     */
    public function redirect($urlFrom) {
        if ($this->isRedirectRegistered($urlFrom)) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$this->urlRedirectMap[$urlFrom]);
            exit();
        }
        return false;
    }

    /**
     * Populates $this->urlRedirectMap
     * @return boolean
     */
    protected function updateRedirectMap() {
        try {
            $rVal = $this->db->query('SELECT * FROM op_redirect_controller');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($rVal->fetchAll() as $item) {
                $this->urlRedirectMap[$item['urlFrom']] = $item['urlTo'];
            }
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
