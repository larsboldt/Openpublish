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

/* OP_LAYOUT_COLLECTIONS */
DROP TABLE IF EXISTS `op_layout_collections`;
CREATE TABLE `op_layout_collections` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `tagID` int(10) NOT NULL,
  `parent` bigint(20) unsigned NOT NULL,
  `position` int(10) NOT NULL,
  `plugin_id` bigint(20) unsigned NOT NULL,
  `plugin_child_id` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_LAYOUTS */
DROP TABLE IF EXISTS `op_layouts`;
CREATE TABLE `op_layouts` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `parent` bigint(20) unsigned NOT NULL,
  `theme_template` bigint(20) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL default '0',
  `quickpublish` tinyint(3) unsigned NOT NULL default '0',
  `last_modified` varchar(255) NOT NULL,
  `etag` varchar(255) NOT NULL,
  `disable_local_cache` tinyint(1) unsigned NOT NULL default '0',
  `disable_meta_inheritance` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_LAYOUT_METATAGS */
DROP TABLE IF EXISTS `op_layout_metatags`;
CREATE TABLE `op_layout_metatags` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `title` varchar(255),
  `description` longtext,
  `keywords` longtext,
  `owner` varchar(255),
  `author` varchar(255),
  `copyright` varchar(255),
  `robots` tinyint(1) unsigned default '1',
  `parent` bigint(20) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;