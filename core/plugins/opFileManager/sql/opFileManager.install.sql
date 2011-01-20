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

/* OP_FILEMANAGER_FILEMAP */
DROP TABLE IF EXISTS `op_filemanager_filemap`;
CREATE TABLE `op_filemanager_filemap` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL,
  `filepath` longtext NOT NULL,
  `parent` bigint(20) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_FILEMANAGER_FOLDERS */
DROP TABLE IF EXISTS `op_filemanager_folders`;
CREATE TABLE `op_filemanager_folders` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(40) NOT NULL,
  `parent` bigint(20) unsigned NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_FILEMANAGER_FILEMAP */
DROP TABLE IF EXISTS `op_filemanager_tags`;
CREATE TABLE `op_filemanager_tags` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `tag` varchar(30) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

/* OP_FILEMANAGER_FILEMAP */
DROP TABLE IF EXISTS `op_filemanager_tags_to_file`;
CREATE TABLE `op_filemanager_tags_to_file` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `tag_id` bigint(20) unsigned NOT NULL,
  `file_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;