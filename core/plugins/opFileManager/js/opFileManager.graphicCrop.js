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
    $('#cropImage').imgAreaSelect({handles: true, fadeSpeed: 200, keys: true, selectionOpacity: 0, outerOpacity: 0.6, onSelectChange: updateDimensions, parent: '#imageWrap'});

    $('#lockWidthIcon').bind('click', function() {
        var icon = $('#lockWidthIcon').attr('src');
        if ($('#lockWidth').attr('disabled') == false) {
            $('#lockWidth').attr('disabled', true);
            icon = icon.replace(/lock-unlock.png/, 'lock.png');
            $('#lockWidthIcon').attr('src', icon);
            $('#cropImage').imgAreaSelect({minWidth: $('#lockWidth').attr('value'), maxWidth: $('#lockWidth').attr('value')});
        } else {
            $('#lockWidth').attr('disabled', false);
            icon = icon.replace(/lock.png/, 'lock-unlock.png');
            $('#lockWidthIcon').attr('src', icon);
            $('#cropImage').imgAreaSelect({minWidth: '0', maxWidth: '10000'});
        }
    }).css('cursor', 'pointer');

    $('#lockHeightIcon').bind('click', function() {
        var icon = $('#lockHeightIcon').attr('src');
        if ($('#lockHeight').attr('disabled') == false) {
            $('#lockHeight').attr('disabled', true);
            icon = icon.replace(/lock-unlock.png/, 'lock.png');
            $('#lockHeightIcon').attr('src', icon);
            $('#cropImage').imgAreaSelect({minHeight: $('#lockHeight').attr('value'), maxHeight: $('#lockHeight').attr('value')});
        } else {
            $('#lockHeight').attr('disabled', false);
            icon = icon.replace(/lock.png/, 'lock-unlock.png');
            $('#lockHeightIcon').attr('src', icon);
            $('#cropImage').imgAreaSelect({minHeight: '0', maxHeight: '10000'});
        }
    }).css('cursor', 'pointer');
    
    $('#form_sbmt').bind('click', function() {
        $('#adminForm').submit();
    });

    $('#jpg_quality_slider').slider({
        value:75,
        min: 0,
        max: 100,
        slide: function(event, ui) {
            $('#jpg_quality').html(ui.value);
            $('#quality').attr('value', ui.value);
        }
	});
});
function updateDimensions(img, selection) {
    $('#lockWidth').attr('value', selection.width);
    $('#lockHeight').attr('value', selection.height);

    $('#cropX1').attr('value', selection.x1);
    $('#cropX2').attr('value', selection.x2);
    $('#cropY1').attr('value', selection.y1);
    $('#cropY2').attr('value', selection.y2);
}