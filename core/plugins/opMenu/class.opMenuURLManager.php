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
class opMenuURLManager {
    protected $urlMap, $db, $vc, $rc;
    
    public function __construct() {
        $this->db     = opSystem::getDatabaseInstance();
        $this->vc     = opSystem::getVirtualControllerInstance();
        $this->rc     = opSystem::getRedirectControllerInstance();
        $this->urlMap = array();

        $this->updateMap();
    }

    /**
     * Returns database id if $url is not registered and successfully registered, false otherwise
     * Automatically removes registered redirect on url
     * @param string $url
     * @return boolean|int
     */
    public function registerURL($url) {
        if (! $this->isRegistered($url)) {
            # Register first level as virtual controller
            $urlPieces = explode(' ', trim(str_replace('/', ' ', $url)));
            $pluginID = opPlugin::getIdByName('opMenu');
            if ($this->vc->isRegistered($urlPieces[0])) {
                if ($this->vc->getPluginID($urlPieces[0]) != $pluginID) {
                    # Unable to register controller, another plugin owns this controller
                    return false;
                }
            } else {
                $this->vc->registerController($urlPieces[0], $pluginID);
            }

            if ($this->rc->isRedirectRegistered($url)) {
                $this->rc->unregisterRedirectURL($url);
            }
            
            $rVal = $this->db->prepare('INSERT INTO op_menu_url_manager (url) VALUES (:u)');
            $rVal->execute(array('u' => $url));
            $insertID = $this->db->lastInsertId();

            $this->updateMap();
            return $insertID;
        }
        return false;
    }

    /**
     * Deletes entry and redirects identified by $id
     * @param int $id
     * @return boolean
     */
    public function unregisterURL($id) {
        try {
            $rVal = $this->db->prepare('SELECT * FROM op_menu_url_manager WHERE id = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $id));
            $urlData = $rVal->fetch();

            $rVal = $this->db->prepare('DELETE FROM op_menu_url_manager WHERE id = :id');
            $rVal->execute(array('id' => $id));

            $this->updateMap();

            $urlPieces = explode(' ', trim(str_replace('/', ' ', $urlData['url'])));
            # Unregister controller
            if (!$this->isControllerInUse($urlPieces[0])) {
                if ($this->vc->isRegistered($urlPieces[0])) {
                    $this->vc->unregisterController($urlPieces[0]);
                }
            }

            if ($this->rc->isRedirectToRegistered($urlData['url'])) {
                $this->rc->unregisterRedirectToURL($urlData['url']);
            }
           
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
     * On true returns id (int) of matching $url, false otherwise
     * @param string $url
     * @return boolean|int
     */
    public function getID($url) {
        if ($this->isRegistered($url)) {
            return $this->urlMap[$url];
        }
        return false;
    }

    /**
     * On true returns url (string) of matching $id, false otherwise
     * @param int $id
     * @return boolean|string
     */
    public function getURL($id) {
        foreach ($this->urlMap as $url => $dbid) {
            if ($id == $dbid) {
                return $url;
            }
        }
        return false;
    }

    /**
     * Checks if a controller is still needed based on registered URLs
     * @param string $controller
     * @return boolean
     */
    protected function isControllerInUse($controller) {
        foreach ($this->urlMap as $url => $id) {
            $urlPieces = explode(' ', trim(str_replace('/', ' ', $url)));
            if ($urlPieces[0] == $controller) {
                return true;
            }
        }
        return false;
    }

    /**
     * Populates $urlMap
     */
    protected function updateMap() {
        $this->urlMap = array();
        $rVal = $this->db->query('SELECT * FROM op_menu_url_manager');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($rVal->fetchAll() as $item) {
            $this->urlMap[$item['url']] = $item['id'];
        }
    }
}
?>