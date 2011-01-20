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

/* OP_MENU_BRIDGE */
DROP TABLE IF EXISTS `op_menu_bridge`;
CREATE TABLE `op_menu_bridge` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `menu_from` bigint(20) unsigned NOT NULL,
  `menu_to` bigint(20) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_MENU_URL_MANAGER */
DROP TABLE IF EXISTS `op_menu_url_manager`;
CREATE TABLE `op_menu_url_manager` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_MENU_EXTERNAL_URL_MANAGER */
DROP TABLE IF EXISTS `op_menu_external_url_manager`;
CREATE TABLE `op_menu_external_url_manager` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_MENU_ITEMS */
DROP TABLE IF EXISTS `op_menu_items`;
CREATE TABLE `op_menu_items` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `hint` varchar(100) NOT NULL,
  `alias_override` tinyint(1) unsigned NOT NULL,
  `alias` varchar(100) NOT NULL,
  `url` bigint(20) unsigned NOT NULL,
  `parent` int(11) unsigned NOT NULL,
  `menu_parent` int(11) NOT NULL,
  `position` int(11) unsigned NOT NULL,
  `layout_id` int(11) NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  `target` varchar(10) NOT NULL,
  `home` tinyint(1) unsigned NOT NULL,
  `enabled` tinyint(1) unsigned NOT NULL default '0',
  `hide` tinyint(1) unsigned NOT NULL default '0',
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_MENUS */
DROP TABLE IF EXISTS `op_menus`;
CREATE TABLE `op_menus` (
  `id` bigint(11) unsigned NOT NULL auto_increment,
  `name` varchar(40) NOT NULL,
  `menu_id` varchar(30) NOT NULL,
  `menu_class` varchar(30) NOT NULL,
  `menu_active_class` varchar(30) NOT NULL,
  `menu_active_class_parents` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;