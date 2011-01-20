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
class opSQLImport {
    protected $db, $debug;

    public function __construct(PDO $db, $debug = false) {
        $this->db       = $db;
        $this->debug    = $debug;
    }

    public function import($sqlFile) {
        if (is_file($sqlFile)) {
            try {
                $sqlQuery = $this->cleanAndSplitSQLFile(file($sqlFile));
                foreach ($sqlQuery as $query) {
                    $this->db->query($query);
                }
                return true;
            } catch (PDOException $e) {
                if ($this->debug) {
                    echo $e->getMessage();
                }
                return false;
            }
        } else {
            return false;
        }
    }

    protected function cleanAndSplitSQLFile($lines) {
        $queries = array();
        $cleaned = array();
        # Clean empty lines and comments
        foreach ($lines as $key => $line) {
            $line = trim($line);
            if (strlen($line) > 0 &&
                substr($line, 0, 1) != '*' &&
                substr($line, 0, 2) != '/*' &&
                substr($line, 0, 2) != '*/') {
                $cleaned[] = trim($line);
            }
        }

        for ($i = 0; $i < count($cleaned); $i++) {
            $line = $cleaned[$i];
            $line = str_replace(array(chr(10), chr(13)), '', $line);
            if (strlen($line) > 0) {
                $query = $line;
                while (substr($line, -1) != ';') {
                    $i++;
                    if ($i < count($cleaned)) {
                        $line = $cleaned[$i];
                        $line = str_replace(array(chr(10), chr(13)), '', $line);
                        $query .= $line;
                    } else {
                        break;
                    }
                }
                $queries[] = $query;
            }
        }
        return $queries;
    }
}
?>