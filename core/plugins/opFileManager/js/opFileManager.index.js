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
    $('#folderList').jCollapse();

    $('.draggable').draggable({
        revert: 'invalid',
        zIndex: 9999
    });

    $('.droppable').droppable({
        hoverClass: 'droppableHover',
        drop: function(event, ui) {
            window.location = '/admin/opFileManager/fileMove/'+$(this).attr('id')+'/'+$(ui.draggable).attr('id');
        }
    });

    $("#opFileManagerImagePreviewDialog").dialog({
        modal: true,
        autoOpen: false,
        width: 482,
        resizable: false
    });
});

function imagePreview(src) {
    $('#opFileManagerImagePreviewDialog').html('<div align="center" style="margin: 100px 0"><img src="/core/plugins/opFileManager/icons/ajax.gif" /></div>');
    $('#opFileManagerImagePreviewDialog').dialog('open');
    $('<img />').attr('src', src).load(function() {
        $('#opFileManagerImagePreviewDialog').html(this);
    });
    $('.ui-widget-overlay').bind('click', function() {
        $('#opFileManagerImagePreviewDialog').dialog('close');
    });
    $('.ui-dialog').css('position', 'fixed');
    $('.ui-dialog').css('top', 80);
}