<?php
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
class opCreateFile {
    protected $document_root, $filename, $content;

    public function __construct($document_root, $filename, $content) {
        $this->document_root = $document_root;
        $this->filename = $filename;
        $this->content = $content;
    }

    public function write() {
        if (is_writable($this->document_root)) {
            if ($handle = fopen($this->document_root.'/'.$this->filename, 'w')) {
                if (fwrite($handle, $this->content) === false) {
                    fclose($handle);
                    return false;
                }
                fclose($handle);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
?>
