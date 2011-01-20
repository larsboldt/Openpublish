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

/* OP_SYSTEM_VARIABLES */
INSERT INTO `op_system_variables` (`k`, `v`, `c`) VALUES ('thumb_size', '50', 'opGallery');
INSERT INTO `op_system_variables` (`k`, `v`, `c`) VALUES ('image_size', '260', 'opGallery');
INSERT INTO `op_system_variables` (`k`, `v`, `c`) VALUES ('url_var', 'a', 'opGallery');
INSERT INTO `op_system_variables` (`k`, `v`, `c`) VALUES ('image_template', '<p><img src="{resizedImage}" alt="{imageTitle}" title="{imageTitle}" /></p>\n<p>{imageDescription}</p>', 'opGallery');
INSERT INTO `op_system_variables` (`k`, `v`, `c`) VALUES ('thumb_template', '<li><a href="{imageLink}" title="{imageTitle}"><img src="{thumbImage}" alt="{imageTitle}" /></a></li>', 'opGallery');
INSERT INTO `op_system_variables` (`k`, `v`, `c`) VALUES ('album_template', '<div align="center">Image {currentImageNumber} of {totalImagesNumber}</div>\n{image}\n{if:prev}\n<div style="float:left;" align="left">\n\t<a href="{prevImageLink}" title="{prevImageTitle}">Previous</a>\n</div>\n{/if:prev}\n{if:next}\n<div style="float:right;" align="right">\n\t<a href="{nextImageLink}" title="{nextImageTitle}">Next</a>\n</div>\n{/if:next}', 'opGallery');