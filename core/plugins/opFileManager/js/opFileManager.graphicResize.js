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

    $('#imageHeight').bind('change', function() {
        var oH = $('#originalHeight').attr('value');
        var oW = $('#originalWidth').attr('value');
        var nH = $(this).attr('value');
        var nW = oW;
        if ($('#aspectRatio').attr('checked')) {
            var nP = nH/oH;
            nW = Math.floor(oW*nP);
            $('#imageWidth').attr('value', nW);
        }
        $('#newWidth').attr('value', nW);
        $('#newHeight').attr('value', nH);
    });

    $('#imageWidth').bind('change', function() {
        var oH = $('#originalHeight').attr('value');
        var oW = $('#originalWidth').attr('value');
        var nW = $(this).attr('value');
        var nH = oH;
        if ($('#aspectRatio').attr('checked')) {    
            var nP = nW/oW;
            nH = Math.floor(oH*nP);
            $('#imageHeight').attr('value', nH);
        }
        $('#newWidth').attr('value', nW);
        $('#newHeight').attr('value', nH);
    });
});