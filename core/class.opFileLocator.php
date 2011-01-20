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
class opFileLocator {
    private $db, $cachedFilePaths, $basepath, $dirsToScan, $dirsToSkip;
    
    public function __construct($basepath) {
        $this->db       = opSystem::getDatabaseInstance();
        $this->basepath = $basepath;
        
        $this->updateCache();

        $this->dirsToScan = array($this->basepath.'core'.DIRECTORY_SEPARATOR,
                                  $this->basepath.'plugins'.DIRECTORY_SEPARATOR,
                                  $this->basepath.'themes'.DIRECTORY_SEPARATOR);
        $this->dirsToSkip = array('.', '..', 'js', 'css', 'images', 'templates', 'icons', 'sql');

        foreach ($this->cachedFilePaths as $v) {
            if (!is_file($v['filepath'].$v['filename'])) {
                $this->removeCachedFileLocation($v['id']);
            }
        }
    }

    public function findAndLoad($fileName) {
        $fileName = 'class.'.$fileName.'.php';
        foreach ($this->cachedFilePaths as $v) {
            if ($v['filename'] == $fileName) {
                if (file_exists($v['filepath'].$fileName)) {
                    require_once($v['filepath'].$fileName);
                    return true;
                } else {
                    $this->removeCachedFileLocation($v['id']);
                }
            }
        }
        # not cached, scan file system
        foreach ($this->dirsToScan as $v) {
            if ($this->findClass($v, $fileName)) {
                return true;
            }
        }
        return false;
    }

    public function getFullPath($fileName) {
        foreach ($this->cachedFilePaths as $v) {
            if ($v['filename'] == $fileName) {
                return $v['filepath'];
            }
        }
        return false;
    }

    public function getRelativePath($fileName) {
        foreach ($this->cachedFilePaths as $v) {
            if ($v['filename'] == $fileName) {
                return str_replace($this->basepath, '/', $v['filepath']);
            }
        }
        return false;
    }

    private function updateCache() {
        try {
            $rVal = $this->db->query('SELECT * FROM op_filelocator');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $this->cachedFilePaths = $rVal->fetchAll();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function cacheFileLocation($fileName, $filePath) {
        try {
            $rVal = $this->db->prepare('INSERT INTO op_filelocator (filename, filepath) VALUES (:filename, :filepath)');
            $rVal->execute(array('filename' => $fileName, 'filepath' => $filePath));

            $this->updateCache();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function removeCachedFileLocation($id) {
        try {
            $rVal = $this->db->prepare('DELETE FROM op_filelocator WHERE id = :id');
            $rVal->execute(array('id' => $id));

            $this->updateCache();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function findClass($dir, $class){
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file == $class) {
                    require($dir.$file);
                    $this->cacheFileLocation($file, $dir);
                    return true;
                } else if (! in_array($file, $this->dirsToSkip, true)){
                    if (is_dir($dir.$file)) {
                        if ($this->findClass($dir.$file.'/', $class)) {
                            return true;
                        }
                    }
                }
            }
            closedir($handle);
        }
        return false;
    }
}
?>