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
$(document).ready(function() {
    $('.folderParent').bind('click', function() {
        if ($(this).attr('checked')) {
            $(this).parent().parent().find('ul').find('input:checkbox').each(function() {
                $(this).attr('checked', true).attr('disabled', true);
            });
        } else {
            $(this).parent().parent().find('ul').find('input:checkbox').each(function() {
                $(this).attr('checked', false).attr('disabled', false);
            });
        }
    });
});