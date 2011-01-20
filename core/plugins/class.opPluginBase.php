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
class opPluginBase {
    private $modifyObserver;
    protected $theme, $args, $db;

    final public function __construct(opTheme &$theme, $args) {
        $this->db        = opSystem::getDatabaseInstance();
        $this->theme     = $theme;
        $this->args      = $args;
        $this->observers = array();

        $this->modifyObserver = new opPluginModifyObserver();
        
        $this->initialize();
    }

    public function adminIndex() {
        return new opHtmlTemplate(get_class($this).' says &quot;Hello World!&quot;');
    }

    public function getOutput($requestID, $renderMode) {
        return false;
    }

    public static function getConfig() {
        return simplexml_load_file(DOCUMENT_ROOT.'/core/plugins/opPluginBase.xml');
    }

    public static function getIcon() {
        return false;
    }

    public static function install() {
        return true;
    }

    public static function uninstall() {
        return true;
    }

    public static function getContentList() {
        return false;
    }

    public static function getContentNameById($id) {
        return false;
    }

    public static function getContentEditPath() {
        return false;
    }

    public static function getBreadcrumb() {
        return false;
    }

    public static function getSitemap() {
        return false;
    }

    public static function controller($url) {
        return false;
    }

    public static function getPageTitle($url) {
        return false;
    }

    public static function isLayoutAssigned($layoutID) {
        return false;
    }

    protected static function getRelativePath($className) {
        $fileLocator = opSystem::getFileLocatorInstance();
        return $fileLocator->getRelativePath('class.'.$className.'.php');
    }

    protected static function getFullPath($className) {
        $fileLocator = opSystem::getFileLocatorInstance();
        return $fileLocator->getFullPath('class.'.$className.'.php');
    }

    final protected function updateLastModified($pluginID, $contentID) {
        if ($this->modifyObserver) {
            $this->modifyObserver->update($pluginID, $contentID);
        }
    }

    protected function initialize() {
        return false;
    }
}
?>