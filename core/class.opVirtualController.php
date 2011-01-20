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
class opVirtualController {
    protected $db, $controllerMap;

    /**
     * @param PDO $db
     */
    public function __construct(PDO $db) {
        $this->db   = $db;
        $this->updateControllerMap();
    }

    /**
     * Returns true if controller is registered
     * @param string $controller
     * @param int $pluginID
     * @return boolean
     */
    public function registerController($controller, $pluginID) {
        if (! $this->isRegistered($controller)) {
            $rVal = $this->db->prepare('INSERT INTO op_virtual_controller (controller, plugin_id) VALUES (:c, :p)');
            $rVal->execute(array('c' => $controller, 'p' => $pluginID));

            $this->updateControllerMap();
            return true;
        }
        return false;
    }

    /**
     * Returns true if controller is unregistered
     * @param string $controller
     * @return boolean
     */
    public function unregisterController($controller) {
        if ($this->isRegistered($controller)) {
            $rVal = $this->db->prepare('DELETE FROM op_virtual_controller WHERE controller = :c');
            $rVal->execute(array('c' => $controller));

            $this->updateControllerMap();
            return true;
        }
        return false;
    }

    /**
     * Returns true if controller is already registered
     * @param string $controller
     * @return boolean
     */
    public function isRegistered($controller) {
        return array_key_exists($controller, $this->controllerMap);
    }

    /**
     * On true returns id (int) of matching $controller, false otherwise
     * @param string $controller
     * @return boolean|int
     */
    public function getID($controller) {
        if ($this->isRegistered($controller)) {
            return $this->controllerMap[$controller][0];
        }
        return false;
    }

    /**
     * On true returns pluginID (int) of matching $controller, false otherwise
     * @param string $controller
     * @return boolean|int
     */
    public function getPluginID($controller) {
        if ($this->isRegistered($controller)) {
            return $this->controllerMap[$controller][1];
        }
        return false;
    }

    /**
     * Populate controllerMap array
     */
    protected function updateControllerMap() {
        $this->controllerMap = array();
        $rVal = $this->db->query('SELECT * FROM op_virtual_controller');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($rVal->fetchAll() as $item) {
            $this->controllerMap[$item['controller']] = array($item['id'], $item['plugin_id']);
        }
    }
}
?>