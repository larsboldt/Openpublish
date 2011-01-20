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
$(document).ready(function(){
    $("#system_message_error").show();
    $("#system_message_error").click(function() {
        $(this).fadeOut(1000);
    });
    $("#system_message_inform").show();
    $("#system_message_inform").click(function() {
        $(this).fadeOut(1000);
    });
    var showMessage = ($("#system_message_success_wrapper").length > 0) ? true : false;
    if (showMessage) {
        var noticeData = $('#system_message_success_wrapper').html();
        $('#system_message_success_wrapper').remove();
        $('#statusbar').html(noticeData);

        $('#statusbar').show();
        $('#system_message_success').show();
        $('#statusbar').fadeTo(3000, 1).fadeOut(800, function() {
            $('#system_message_success').remove();
            $('#statusbar').html('&nbsp;');
        });
    }
    $("table.scheme tbody tr:last").find("td").css({'border-bottom': 'none'});
    $("ul#languageList").find("li:last a").css({
    	'border-top': '1px solid #333',
    	'background-position': '10px 10px',
		'padding-top': '10px',
		'margin-top': '10px'
    });
    $("ul#topbar-right").find("li:first").css({
    	'background': 'none',
    	'padding': '0 8px 0 0'
    });

    $("input:file").addClass("fileHeight");
    $("select.form_select, input:radio, input:checkbox, input:file").uniform();
    $('#file1').uniform();
});