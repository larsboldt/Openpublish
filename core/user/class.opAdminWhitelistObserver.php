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
class opAdminWhitelistObserver implements SplObserver {
    private $systemConfiguration;

    public function __construct() {
        $this->systemConfiguration = opSystem::getSystemConfiguration();
    }

    public function update(SplSubject $subject) {
        if ($this->systemConfiguration->login_protection == 2) {
            $whiteList = explode(',',$this->systemConfiguration->whitelist);
            foreach ($whiteList as $k => $ip) {
                $whiteList[$k] = trim($ip);
            }
            if (! in_array($_SERVER['REMOTE_ADDR'], $whiteList, true)) {
                die('<h1 align="center">Access denied</h1>');
            }
        }
    }
}
?>
