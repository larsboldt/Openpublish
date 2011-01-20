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
function fileBrowser(field_name, url, type, win) {
    var cmsURL = '/admin/simplebrowser/configure/' + type + '/false/false/';

    tinyMCE.activeEditor.windowManager.open({
        file : cmsURL,
        width : 800,
        height : 500,
        resizable : "yes",
        inline : "yes",
        close_previous : "no"
    }, {
        window : win,
        input : field_name
    });
    return false;
}
$(document).ready(function() {
    $('textarea').tinymce({
        script_url : '/themes/opAdmin/js/tiny_mce/tiny_mce.js',
        width: '100%',
        height: '400px',
        theme: 'advanced',
        entity_encoding: 'raw',
        auto_resize : false, // Adopt height for editor by content
        relative_urls : false,
        convert_urls : false,
        file_browser_callback: 'fileBrowser',
        plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
        theme_advanced_buttons1 : "code,|,formatselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,|,charmap,blockquote,hr,|,sub,sup,|,undo,redo",
        theme_advanced_buttons2 : "fullscreen,|,link,unlink,anchor,image,media,|,tablecontrols,|,pastetext,pasteword,search,replace,|,removeformat,cleanup",
        theme_advanced_buttons3 : "",
        theme_advanced_buttons4 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_path : true,
        theme_advanced_resize_horizontal : false,
        theme_advanced_resize_vertical : true,
        theme_advanced_resizing : true,
        theme_advanced_blockformats : "p,h1,h2,h3,h4,h5,h6,blockquote,pre,code"
    });
});