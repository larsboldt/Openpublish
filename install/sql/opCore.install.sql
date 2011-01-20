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

/* OP_FILELOCATOR */
DROP TABLE IF EXISTS `op_filelocator`;
CREATE TABLE `op_filelocator` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL,
  `filepath` longtext NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_VIRTUAL_CONTROLLER */
DROP TABLE IF EXISTS `op_virtual_controller`;
CREATE TABLE `op_virtual_controller` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `controller` varchar(255) NOT NULL,
  `plugin_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_REDIRECT_CONTROLLER */
DROP TABLE IF EXISTS `op_redirect_controller`;
CREATE TABLE `op_redirect_controller` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `urlFrom` varchar(255) NOT NULL,
  `urlTo` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_LOGIN_DEFENSE_LOG */
DROP TABLE IF EXISTS `op_login_defense_log`;
CREATE TABLE `op_login_defense_log` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remote_addr` varchar(100) NOT NULL,
  `remote_port` int(11) NOT NULL,
  `referer` longtext NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `request_uri` varchar(255) NOT NULL,
  `stamp` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;


/* OP_LOGIN_HAMMER_BANLIST */
DROP TABLE IF EXISTS `op_login_hammer_banlist`;
CREATE TABLE `op_login_hammer_banlist` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `ip` varchar(100) NOT NULL,
  `minutes` int(10) unsigned NOT NULL,
  `stamp` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_PASSWORD_RESET */
DROP TABLE IF EXISTS `op_password_reset`;
CREATE TABLE `op_password_reset` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `keycode` varchar(12) NOT NULL default '',
  `stamp` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_SYSTEM_VARIABLES */
DROP TABLE IF EXISTS `op_system_variables`;
CREATE TABLE `op_system_variables` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `k` varchar(255) NOT NULL,
  `v` longtext NOT NULL,
  `c` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;