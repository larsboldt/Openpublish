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

/* Plugins */
INSERT INTO `op_plugins` VALUES (1, 'opCreate', 0, 0, -1, 1, NOW());
INSERT INTO `op_plugins` VALUES (2, 'opDocuments', 0, 0, 0, 0, NOW());
INSERT INTO `op_plugins` VALUES (3, 'opFileManager', 0, 0, -1, 1, NOW());
INSERT INTO `op_plugins` VALUES (4, 'opLayout', 0, 0, -1, 1, NOW());
INSERT INTO `op_plugins` VALUES (5, 'opMenu', 0, 0, -1, 1, NOW());
INSERT INTO `op_plugins` VALUES (6, 'opPlugin', 0, 0, -1, 1, NOW());
INSERT INTO `op_plugins` VALUES (7, 'opSiteConfig', 0, 0, -1, 1, NOW());
INSERT INTO `op_plugins` VALUES (8, 'opThemes', 0, 0, -1, 1, NOW());
INSERT INTO `op_plugins` VALUES (9, 'opUsers', 0, 0, -1, 1, NOW());
INSERT INTO `op_plugins` VALUES (10, 'opTranslation', 0, 0, -1, 1, NOW());
INSERT INTO `op_plugins` VALUES (11, 'opGallery', 0, 0, 0, 0, NOW());
INSERT INTO `op_plugins` VALUES (12, 'opSocialSharer', 0, 0, 0, 0, NOW());
INSERT INTO `op_plugins` VALUES (13, 'opSocialSharerTitle', 2, 0, 0, 0, NOW());
INSERT INTO `op_plugins` VALUES (14, 'opGoogleAnalytics', 2, 0, 0, 0, NOW());

INSERT INTO `op_plugin_config` VALUES (10);

/* Theme */
INSERT INTO `op_themes` VALUES (1, 'themes/opDefault/', 1);

/* Theme templates */
INSERT INTO `op_theme_templates` VALUES ('1', 'Frontpage', 'templates/frontpage.tpl', 1);

/* Documents */
INSERT INTO `op_document_categories` VALUES (1, 'Demo', 0, 0);
INSERT INTO `op_documents` VALUES ('1', 'Extend', '<h1><img src="/themes/opDefault/images/demo/puzzle.png" alt="" width="16" height="16" /> Extend</h1><p>Learn how you can deliver fun and creative content to your website by installing plugins. Extend your website with news, search, contact forms, galleries and much more...</p><p><a href="http://www.openpublish.org/download" target="_blank" title="Extend Openpublish CMS"><img src="/themes/opDefault/images/demo/btn_slide_readmore.jpg" alt="" width="106" height="28" /></a></p>', '1');
INSERT INTO `op_documents` VALUES ('2', 'Design', '<h1><img src="/themes/opDefault/images/demo/map.png" alt="" width="16" height="16" /> Design</h1><p>Want a more personal touch to your site? Openpublish CMS makes it easy for you to style your content in infinite ways and is one of the first CMS to offer unlimited use of themes within the same website. Each layout can have its own theme or style.</p><p><a href="http://www.openpublish.org/develop/themes" target="_blank" title="Style Openpublish CMS"><img src="/themes/opDefault/images/demo/btn_slide_readmore.jpg" alt="" width="106" height="28" /></a></p>', '1');
INSERT INTO `op_documents` VALUES ('3', 'Community', '<h1><img src="/themes/opDefault/images/demo/users.png" alt="" width="16" height="16" /> Community</h1><p>Get involved! Openpublish CMS is open source after all. Check out our forums and developer section to find out more about how you can help make Openpublish CMS better.</p><p><a href="http://www.openpublish.org/community" target="_blank" title="Get involved in Openpublish CMS!"><img src="/themes/opDefault/images/demo/btn_slide_readmore.jpg" alt="" width="106" height="28" /></a></p>', '1');
INSERT INTO `op_documents` VALUES ('4', 'Congratulations', '<table width="700" border="0" cellspacing="0" cellpadding="0"><tbody><tr><td><p><img style="float: left" title="Getting started - Openpublish CMS" src="/themes/opDefault/images/demo/opbox.png" alt="Getting started - Openpublish CMS" width="370" height="308" /></p></td><td valign="top"><h1>Congratulations</h1><p>You have successfully installed Openpublish Content Management System!</p><p>If this is your first time using Openpublish CMS please take the time to read the manual so you can get the most out of it.</p><p>Have fun!</p><table border="0" cellspacing="0" cellpadding="0"><tbody><tr><td><p><img title="Lars Boldt" src="/themes/opDefault/images/demo/signature_lars_boldt.jpg" alt="Lars Boldt" width="116" height="30" /><br /><strong>Lars Boldt</strong><br />Author</p></td><td width="20"></td><td><p><img title="Christian Philippsen" src="/themes/opDefault/images/demo/signatur_christian.jpg" alt="Philippsen" width="120" height="22" /><br /><strong>Christian Philippsen</strong><br />Designer</p></td></tr><tr><td></td><td></td><td></td></tr></tbody></table></td></tr></tbody></table>', '1');
INSERT INTO `op_documents` VALUES ('5', 'Getting started', '<table width="700" border="0" cellspacing="0" cellpadding="0"><tbody><tr><td><p><img style="float: left" title="Getting started - Openpublish CMS" src="/themes/opDefault/images/demo/opbooks.png" alt="Getting started - Openpublish CMS" width="330" height="314" /></p></td><td valign="top"><h1>Getting started</h1><p>Openpublish CMS offers comprehensive documentation online where you can learn everything you need to know about the system. <a href="http://www.openpublish.org/learn" target="_blank">www.openpublish.org/learn</a></p><p>If you cannot find the answers you need in the online documentation you can always try asking other users of Openpublish CMS. Check out the community portal for options: <a href="http://www.openpublish.org/community" target="_blank">www.openpublish.org/community</a></p><p>Lastly, remember that this default setup only includes the basic tools you need to build your website. Check out <a href="http://www.openpublish.org/download" target="_blank">www.openpublish.org/download</a> to find out how you can add more content to your website.</p></td></tr></tbody></table>', '1');

/* Layouts */
INSERT INTO `op_layouts` VALUES ('1', 'Home', 0, 1, 0, 0, NOW(), 0, 0, 0);
INSERT INTO `op_layout_collections` VALUES ('1', 1, 1, 0, 5, 1);
INSERT INTO `op_layout_collections` VALUES ('2', 3, 1, 0, 2, 1);
INSERT INTO `op_layout_collections` VALUES ('3', 4, 1, 0, 2, 2);
INSERT INTO `op_layout_collections` VALUES ('4', 2, 1, 0, 2, 4);
INSERT INTO `op_layout_collections` VALUES ('5', 2, 1, 1, 2, 5);
INSERT INTO `op_layout_collections` VALUES ('6', 5, 1, 0, 2, 3);

/* Menu */
INSERT INTO `op_menus` VALUES ('1', 'Top', 'navigation', '', '', 0);
INSERT INTO `op_menu_items` VALUES ('1', 'Home', 'Home', 0, 'home', 0, 0, 1, 1, 1, 1, '', 1, 1, 0, NOW());
INSERT INTO `op_menu_items` VALUES ('2', 'Administration', 'Administration', 0, 'admin', 1, 0, 1, 2, 0, 2, '_blank', 0, 1, 0, NOW());
INSERT INTO `op_menu_external_url_manager` VALUES (1, '/admin');