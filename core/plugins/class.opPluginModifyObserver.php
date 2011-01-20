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
class opPluginModifyObserver {
    private $db;
    
    public function __construct() {
        $this->db = opSystem::getDatabaseInstance();
    }

    public function update($pluginID, $contentID) {
        $lastModified = gmdate('D, d M Y H:i:s \G\M\T', time());
        foreach (opLayout::getAssignedLayouts($pluginID, $contentID) as $layout) {
            $rVal = $this->db->prepare('UPDATE op_layouts SET last_modified = :lm, etag = :etag WHERE id = :id');
            $rVal->execute(array('lm' => $lastModified, 'etag' => opLayout::generateETag($layout['layoutID']), 'id' => $layout['layoutID']));
        }
        $opCache = new opCache(opPlugin::getNameById($pluginID).'_'.$contentID);
        $opCache->clearCache();
    }
}
?>