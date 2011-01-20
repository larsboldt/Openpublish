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
    $('#tagText').bind('focus', function() {
        $('#tagControlMsg').show();
    });
    $('#tagText').bind('blur', function() {
        $('#tagControlMsg').hide();
    });
});

/**
$(document).keydown(function(event) {
    switch (event.keyCode) {
        case 13:
            addTag();
            return false;
    }
});
 */

function addTag() {
    if ($('#tagText').attr('value').length > 0) {
        var tag = $('#tagText').attr('value');
        $('#tagText').attr('value', '');
        $.post('/admin/opFileManager/ajax/sanitizeTag', {tagData: tag}, function(data) {
            if (data.length > 0) {
                $('#tagList').append('<li><span class="tag"><span>' + data + '</span><img src="/themes/opAdmin/images/icons/cross-small-gray.png" width="16" height="16" border="0" class="tagIcon" onclick="$(this).parent().parent().remove();" /></span></li>');
            }
        });
    }
}