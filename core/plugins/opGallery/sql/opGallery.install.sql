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

/* OP_GALLERY */
DROP TABLE IF EXISTS `op_gallery`;
CREATE TABLE `op_gallery` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `parent` bigint(20) unsigned NOT NULL,
  `thumb_size` varchar(3),
  `image_size` varchar(3),
  `image_template` longtext,
  `thumb_template` longtext,
  `album_template` longtext,
  `stamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_GALLERY_PICTURES */
DROP TABLE IF EXISTS `op_gallery_pictures`;
CREATE TABLE `op_gallery_pictures` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `image_id` bigint(20) unsigned NOT NULL,
  `parent` bigint(20) unsigned NOT NULL,
  `title` varchar(255),
  `description` longtext,
  `position` int(11) unsigned NOT NULL,
  `stamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_GALLERY_CATEGORIES */
DROP TABLE IF EXISTS `op_gallery_categories`;
CREATE TABLE `op_gallery_categories` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(40) NOT NULL,
  `parent` bigint(20) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;