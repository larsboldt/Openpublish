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
class opMenuExternalURLManager {
    protected $db, $urlMap;

    /**
     * Returns instance of opMenuExternalURLManager
     * @param PDO $db
     */
    public function __construct(PDO $db) {
        $this->db     = $db;
        $this->urlMap = array();

        $this->updateMap();
    }

    /**
     * Returns database id of registered $url
     * @param string $url
     * @return int
     */
    public function registerURL($url) {
        $rVal = $this->db->prepare('INSERT INTO op_menu_external_url_manager (url) VALUES (:u)');
        $rVal->execute(array('u' => $url));
        return $this->db->lastInsertId();
    }

    /**
     * Deletes database entry identified by $id
     * @param int $id
     * @return boolean
     */
    public function unregisterURL($id) {
        try {
            $rVal = $this->db->prepare('DELETE FROM op_menu_external_url_manager WHERE id = :id');
            $rVal->execute(array('id' => $id));
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Returns true if $url is registered, false otherwise
     * @param string $url
     * @return boolean
     */
    public function isRegistered($url) {
        return array_key_exists($url, $this->urlMap);
    }

    /**
     * Returns the registered url identified by $id, false if not found or it otherwise fails
     * @param int $id
     * @return boolean|string
     */
    public function getURL($id) {
        if (in_array($id, $this->urlMap, true)) {
            foreach ($this->urlMap as $url => $dbid) {
                if ($dbid == $id) {
                    return $url;
                }
            }
            return false;
        } else {
            return false;
        }
    }

    public function getID($url) {
        if (array_key_exists($url, $this->urlMap)) {
            return $this->urlMap[$url];
        } else {
            return false;
        }
    }

    /**
     * Populates $urlMap
     */
    protected function updateMap() {
        $this->urlMap = array();
        $rVal = $this->db->query('SELECT * FROM op_menu_external_url_manager');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($rVal->fetchAll() as $item) {
            $this->urlMap[$item['url']] = $item['id'];
        }
    }
}
?>