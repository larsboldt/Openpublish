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
class opPluginFullTextSearch {
    private $queryFilter;
    private $queryKeywords;
    private $queryTotalCount = 0;
    private $resultPerPage = 20;
    private $queryExecuteArr = array();
    private $fullResult;
    private $limitedResult;

    public function __construct($activePage, $queryFilter, $queryKeywords, $queryOrder, $db, $pageListingLimit = 20) {
        $this->resultPerPage = $pageListingLimit;
        $qStr = 'SELECT * FROM op_plugin_repo';
        $qStr .= $this->queryFilterBuilder($queryFilter, $queryKeywords);
        $rVal = $db->prepare($qStr);
        $rVal->execute($this->queryExecuteArr);
        $this->fullResult = $rVal->fetchAll();
        $totalPages = ceil(count($this->fullResult)/$this->resultPerPage);
        $activePage = ($activePage > $totalPages-1) ? $totalPages-1 : $activePage;
        $qStr .= $this->queryOrderBuilder($queryOrder);
        $qStr .= $this->queryLimitBuilder($activePage);
        $rVal = $db->prepare($qStr);
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $rVal->execute($this->queryExecuteArr);
        $this->limitedResult = $rVal->fetchAll();
    }

    public function getFullResult() {
        return $this->fullResult;
    }

    public function getLimitedResult() {
        return $this->limitedResult;
    }

    private function queryFilterBuilder($queryFilter, $queryKeywords) {
        if (strlen($queryFilter) > 1 || strlen($queryKeywords) > 2) {
            $filterStr = ' WHERE ';
            $qF = false;
            if (strlen($queryFilter) > 1) {
                $filterStr .= 'category = :catFilter';
                $this->queryExecuteArr['catFilter'] = $queryFilter;
                $qF = true;
            }
            if (strlen($queryKeywords) > 2) {
                $filterStr .= ($qF) ? ' AND ' : '';
                $filterStr .= ' MATCH(name, description, author) AGAINST(:keywords IN BOOLEAN MODE)';
                $this->queryExecuteArr['keywords'] = $queryKeywords;
            }
            return $filterStr;
        } else {
            return '';
        }
    }

    private function queryOrderBuilder($queryOrder) {
        if (is_array($queryOrder)) {
            $orderStr = ' ORDER BY ';
            foreach ($queryOrder as $k => $v) {
                $orderStr .= $k.' '.$v.',';
            }
            return substr($orderStr, 0, strlen($orderStr)-1);
        } else {
            return ' ORDER BY '.$queryOrder;
        }
    }

    private function queryLimitBuilder($activePage) {
        if (is_numeric($activePage) && $activePage >= 0) {
            return ' LIMIT '.($activePage*$this->resultPerPage).','.$this->resultPerPage;
        } else {
            return '';
        }
    }
}
?>