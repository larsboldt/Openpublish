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

/* OP_PLUGIN_CONFIG */
DROP TABLE IF EXISTS `op_plugin_config`;
CREATE TABLE `op_plugin_config` (
  `page_listing_limit` int(10) unsigned NOT NULL
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_PLUGIN_REPO */
DROP TABLE IF EXISTS `op_plugin_repo`;
CREATE TABLE `op_plugin_repo` (
  `pid` int(11) unsigned NOT NULL,
  `description` longtext NOT NULL,
  `name` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `version` int(11) unsigned NOT NULL,
  `rating` int(11) unsigned NOT NULL,
  `downloads` int(11) unsigned NOT NULL,
  `category` varchar(255) NOT NULL,
  `dependency` longtext NOT NULL,
  `upgrade` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`pid`),
  FULLTEXT KEY `ft` (`description`,`name`,`author`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_PLUGINS */
DROP TABLE IF EXISTS `op_plugins`;
CREATE TABLE `op_plugins` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `plugin_name` varchar(255) NOT NULL,
  `processing_position` tinyint(1) unsigned NOT NULL default '1',
  `position` int(11) unsigned NOT NULL,
  `cat_id` bigint(20) NOT NULL,
  `core` tinyint(3) unsigned NOT NULL,
  `stamp` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;