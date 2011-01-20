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
class opFormDataMapper {
    protected $formElementTypeToSkip;
    protected $formElementNameToSkip;
    protected $formElements;
    protected $db;
    protected $dbTable;
    protected $dbRowID;
    protected $dbFieldIDName;

    public function __construct(PDO $db) {
        $this->db            = $db;
        $this->dbTable       = false;
        $this->dbRowID       = false;
        $this->dbFieldIDName = false;
        $this->formElementTypeToSkip = array();
        $this->formElementNameToSkip = array();
        $this->formElements          = array();
    }

    public function setTable($str) {
        $this->dbTable = $str;
    }

    public function getTable() {
        return $this->dbTable;
    }

    public function setFieldIDName($str) {
        $this->dbFieldIDName = $str;
    }

    public function getFieldIDName() {
        return $this->dbFieldIDName;
    }

    public function setRowID($id) {
        $this->dbRowID = $id;
    }

    public function addElementTypeToSkip(opFormElement $element) {
        foreach ($this->formElementTypeToSkip as $type) {
            if ($element instanceof $type) {
                return false;
            }
        }
        $this->formElementTypeToSkip[] = $element;
    }

    public function addElementNameToSkip($str) {
        if (! in_array($str, $this->formElementNameToSkip, true)) {
            $this->formElementNameToSkip[] = $str;
        }
    }

    public function addElements(array $elements) {
        foreach ($elements as $element) {
            if ($element instanceof opFormElement) {
                $this->formElements[] = $element;
            } else {
                throw new Exception('Unknown element');
            }
        }
    }

    public function addElement(opFormElement $element) {
        $this->formElements[] = $element;
    }

    public function clearAllElements() {
        $this->formElements = array();
    }

    public function insert() {
        if ($this->dbTable !== false) {
            $sqlQuery = 'INSERT INTO '.$this->dbTable.' ('.str_replace(':', '', implode(',', $this->getElementNames())).') VALUES ('.implode(',', $this->getElementNames()).')';
            try {
                $rVal = $this->db->prepare($sqlQuery);
                $rVal->execute($this->getElementData());

                return $this->db->lastInsertId();
            } catch (PDOException $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            throw new Exception('Cannot call insert() before table is set');
        }
    }

    public function update() {
        if ($this->dbTable !== false) {
            if ($this->dbFieldIDName !== false) {
                if ($this->dbRowID !== false) {
                    $sqlQuery = 'UPDATE '.$this->dbTable.' SET ';
                    foreach ($this->getElementNames() as $name) {
                        $sqlQuery .= str_replace(':', '', $name).' = '.$name.',';
                    }
                    $sqlQuery = substr($sqlQuery, 0, strlen($sqlQuery)-1);
                    $sqlQuery .= ' WHERE '.$this->dbFieldIDName.' = '.$this->dbRowID;
                    try {
                        $rVal = $this->db->prepare($sqlQuery);
                        $rVal->execute($this->getElementData());
                    } catch (PDOException $e) {
                        throw new Exception($e->getMessage());
                    }
                } else {
                    return false;
                }
            } else {
                throw new Exception('Cannot call update() before id field name is set');
            }
        } else {
            throw new Exception('Cannot call update() before table is set');
        }
    }

    public function updateAllRows() {
        if ($this->dbTable !== false) {
            $sqlQuery = 'UPDATE '.$this->dbTable.' SET ';
            foreach ($this->getElementNames() as $name) {
                $sqlQuery .= str_replace(':', '', $name).' = '.$name.',';
            }
            $sqlQuery = substr($sqlQuery, 0, strlen($sqlQuery)-1);
            try {
                $rVal = $this->db->prepare($sqlQuery);
                $rVal->execute($this->getElementData());
            } catch (PDOException $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            throw new Exception('Cannot call updateAllRows() before table is set');
        }
    }

    public function delete() {
        if ($this->dbTable !== false) {
            if ($this->dbFieldIDName !== false) {
                if ($this->dbRowID !== false) {
                    $sqlQuery = 'SELECT COUNT(*) FROM '.$this->dbTable.' WHERE '.$this->dbFieldIDName.' = '.$this->dbRowID;
                    try {
                        $rVal = $this->db->query($sqlQuery);
                        $rVal->setFetchMode(PDO::FETCH_ASSOC);
                        if ($rVal->fetchColumn() > 0) {
                            $sqlQuery = 'DELETE FROM '.$this->dbTable.' WHERE '.$this->dbFieldIDName.' = '.$this->dbRowID;
                            $this->db->query($sqlQuery);
                        } else {
                            return false;
                        }
                    } catch(PDOException $e) {
                        throw new Exception($e->getMessage());
                    }
                } else {
                    return false;
                }
            } else {
                throw new Exception('Cannot call delete() before id field name is set');
            }
        } else {
            throw new Exception('Cannot call delete() before table is set');
        }
    }

    public function fetchRow() {
        if ($this->dbTable !== false) {
            if ($this->dbFieldIDName !== false) {
                if ($this->dbRowID !== false) {
                    $sqlQuery = 'SELECT COUNT(*) FROM '.$this->dbTable.' WHERE '.$this->dbFieldIDName.' = :'.$this->dbFieldIDName;
                    try {
                        $rVal = $this->db->prepare($sqlQuery);
                        $rVal->setFetchMode(PDO::FETCH_ASSOC);
                        $rVal->execute(array(':'.$this->dbFieldIDName => $this->dbRowID));
                        if ($rVal->fetchColumn() > 0) {
                            $sqlQuery = 'SELECT * FROM '.$this->dbTable.' WHERE '.$this->dbFieldIDName.' = :'.$this->dbFieldIDName;
                            $rVal = $this->db->prepare($sqlQuery);
                            $rVal->setFetchMode(PDO::FETCH_OBJ);
                            $rVal->execute(array(':'.$this->dbFieldIDName => $this->dbRowID));

                            return $rVal->fetch();
                        } else {
                            return false;
                        }
                    } catch (PDOException $e) {
                        throw new Exception($e->getMessage());
                    }
                } else {
                    return false;
                }
            } else {
                throw new Exception('Cannot call fetchRow() before id field name is set');
            }
        } else {
            throw new Exception('Cannot call fetchRow() before table is set');
        }
    }

    public function fetchAll() {
        if ($this->dbTable !== false) {
            $sqlQuery = 'SELECT COUNT(*) FROM '.$this->dbTable;
            try {
                $rVal = $this->db->query($sqlQuery);
                $rVal->setFetchMode(PDO::FETCH_OBJ);
                if ($rVal->fetchColumn() > 0) {
                    $sqlQuery = 'SELECT * FROM '.$this->dbTable;
                    $rVal = $this->db->query($sqlQuery);
                    $rVal->setFetchMode(PDO::FETCH_OBJ);
                    return $rVal->fetchAll();
                } else {
                    return false;
                }
            } catch (PDOException $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            throw new Exception('Cannot call fetchAll() before table is set');
        }
    }

    protected function getElementNames() {
        $elementNames = array();
        foreach ($this->formElements as $element) {
            foreach ($this->formElementTypeToSkip as $type) {
                if ($element instanceof $type) {
                    continue 2;
                }
            }
            if (!in_array($element->getName(), $this->formElementNameToSkip, true)) {
                $elementNames[] = ':'.$element->getName();
            }
        }
        return $elementNames;
    }

    protected function getElementData() {
        $elementData = array();
        foreach ($this->formElements as $element) {
            foreach ($this->formElementTypeToSkip as $type) {
                if ($element instanceof $type) {
                    continue 2;
                }
            }
            if (!in_array($element->getName(), $this->formElementNameToSkip, true)) {
                $elementData[':'.$element->getName()] = $element->getValue();
            }
        }

        return $elementData;
    }
}
?>
