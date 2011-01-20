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

/* OP_ADMIN_USERS */
DROP TABLE IF EXISTS `op_admin_users`;
CREATE TABLE `op_admin_users` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `username` varchar(100) NOT NULL,
  `password` varchar(64) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `superadmin` tinyint(1) unsigned NOT NULL,
  `dm_color` varchar(6) NOT NULL default 'f00000',
  `dm_last_insert_id` varchar(10),
  `locale` varchar(2) NOT NULL default 'gb',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;