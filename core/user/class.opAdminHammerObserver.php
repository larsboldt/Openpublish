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
class opAdminHammerObserver implements SplObserver {
    private $systemConfiguration;
    private $db;

    public function __construct() {
        $this->systemConfiguration = opSystem::getSystemConfiguration();
        $this->db = opSystem::getDatabaseInstance();
    }

    public function update(SplSubject $subject) {
        if ($this->systemConfiguration->hammer_protection == 1) {
            $userIP = $_SERVER['REMOTE_ADDR'];
            
            # Check and issue bans
            foreach ($this->parseHammerIntervals($this->systemConfiguration->hammer_intervals) as $interval) {
                $timesBeforeBan = $interval[0]; // int
                $withinInterval = $interval[1]; // seconds
                $equalsBanFor   = $interval[2]; // minutes

                $rVal = $this->db->prepare('SELECT * FROM op_login_defense_log WHERE remote_addr = :ip AND stamp >= DATE_SUB(NOW(), INTERVAL :interval SECOND)');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('ip' => $userIP, 'interval' => $withinInterval));

                if ($rVal->fetchColumn() >= $timesBeforeBan) {
                    $this->issueBan($userIP, $equalsBanFor);
                }
            }

            # Clean issued bans
            $this->cleanBans();

            # Check issued bans
            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_login_hammer_banlist WHERE ip = :ip AND NOW() <= DATE_ADD(stamp, INTERVAL minutes MINUTE)');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('ip' => $userIP));
            if ($rVal->fetchColumn() > 0) {
                # This user has been banned
                die('<h1 align="center">Access denied</h1>');
            }
        }
    }

    protected function parseHammerIntervals($intervals) {
        $intervals = explode(',', $intervals);
        $parsedIntervals = array();
        if (count($intervals) > 0) {
            foreach ($intervals as $interval) {
                $interval = trim($interval);
                if (strpos($interval, ':') > 0) {
                    $tib = explode(':', $interval);
                    if (count($tib) == 3) {
                        if (is_numeric($tib[0]) && is_numeric($tib[1]) && is_numeric($tib[2])) {
                            $parsedIntervals[] = $tib;
                        }
                    }
                }
            }
        }
        return $parsedIntervals;
    }

    protected function issueBan($ip, $minutes) {
        $rVal = $this->db->prepare('INSERT INTO op_login_hammer_banlist (ip, minutes, stamp) VALUES (:ip, :minutes, NOW())');
        $rVal->execute(array('ip' => $ip, 'minutes' => $minutes));
    }

    protected function cleanBans() {
        $rVal = $this->db->query('SELECT * FROM op_login_hammer_banlist WHERE NOW() > DATE_ADD(stamp, INTERVAL minutes MINUTE)');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($rVal->fetchAll() as $row) {
            $qDel = $this->db->prepare('DELETE FROM op_login_hammer_banlist WHERE id = :id');
            $qDel->execute(array('id' => $row['id']));
        }
    }
}
?>