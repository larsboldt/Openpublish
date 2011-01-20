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
class opRouter {
    private $path;
    private $args = array();

    public function setPath($path) {
        $path .= DIRSEP;
        if (is_dir($path) == false) {
            throw new Exception ('Invalid controller path: `' . $path . '`');
        }
        $this->path = $path;
    }

    public function getArgs() {
        return $this->args;
    }

    public function delegate() {
        $this->getController($file, $controller, $action, $args);

        $this->args = $args;

        include ($file);

        $class = 'controller_' . $controller;
        $controller = new $class();

        if (is_callable(array($controller, $action)) == false) {
            $controller->index();
            exit();
        }

        $controller->$action();
    }

    private function getController(&$file, &$controller, &$action, &$args) {
        $route = (empty($_GET['route'])) ? array() : explode('/', trim($_GET['route'], '/\\'));
        $parts = $route;
        $args = $route;

        $cmd_path = $this->path;
        foreach ($parts as $part) {
            $fullpath = $cmd_path.'controller.'.$part;

            if (is_dir($fullpath)) {
                $cmd_path .= $part . DIRSEP;
                array_shift($parts);
                continue;
            }

            if (is_file($fullpath . '.php')) {
                $controller = $part;
                array_shift($parts);
                break;
            }
        }

        if (empty($controller)) { $controller = 'index'; };
        
        $action = array_shift($parts);
        if (empty($action)) { $action = 'index'; }

        $file = $cmd_path .'controller.'.$controller.'.php';
    }
}
?>