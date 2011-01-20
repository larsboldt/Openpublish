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

/* OP_SITE_CONFIG */
DROP TABLE IF EXISTS `op_site_config`;
CREATE TABLE `op_site_config` (
  `site_name` varchar(150) NOT NULL,
  `site_url` varchar(100) NOT NULL,
  `date_format` varchar(20) NOT NULL,
  `time_format` varchar(20) NOT NULL,
  `file_permission` varchar(4) NOT NULL,
  `dir_permission` varchar(4) NOT NULL,
  `title_separator` varchar(10) NOT NULL,
  `title_breadcrumb` tinyint(1) unsigned NOT NULL,
  `title_breadcrumb_separator` varchar(10) NOT NULL,
  `caching` tinyint(1) unsigned NOT NULL,
  `cache_ttl` int(10) unsigned NOT NULL,
  `local_caching` tinyint(1) unsigned NOT NULL,
  `local_cache_ttl` int(10) unsigned NOT NULL,
  `site_status` tinyint(1) unsigned NOT NULL,
  `force_url_lowercase` tinyint(1) NOT NULL,
  `disable_captcha` tinyint(1) NOT NULL,
  `compress_css` tinyint(1) NOT NULL,
  `compress_js` tinyint(1) NOT NULL,
  `login_protection` tinyint(1) unsigned NOT NULL,
  `blacklist` longtext NOT NULL,
  `whitelist` longtext NOT NULL,
  `hammer_protection` tinyint(1) unsigned NOT NULL,
  `hammer_intervals` longtext NOT NULL
) ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;