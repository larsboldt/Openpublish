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
(function($){
$.fn.jCollapse = function() {
    var uniqueID = $(this).attr('id');
    $(this).parent().find('div .wrap').each(function() {
        var div = $(this);
        var parent = $(this).prev('div');
        var toggleIcon = parent.find('img:first');
        toggleIcon.css('cursor', 'pointer');
        var srcIcon = toggleIcon.attr('src').split('/');
        srcIcon.pop();
        if (! itemActive(parent.attr('id'), uniqueID)) {
            $(this).hide();
            srcIcon.push('clear-folder-plus.png');
        } else {
            srcIcon.push('clear-folder-open-minus.png');
        }
        toggleIcon.attr('src', srcIcon.join('/'));
        toggleIcon.bind('click', function() {
            toggle(div, $(this), parent.attr('id'), uniqueID);
        });
    });

    function toggle(e, icon, eid, id) {
        var srcIcon = icon.attr('src').split('/');
        srcIcon.pop();
        var activeItems = $.cookies.get('jCollapse_' + id);
        if (activeItems) {
            activeItems = activeItems.split(',');
            var found = false;
            for (var i = 0; i < activeItems.length; i++) {
                if (activeItems[i] == eid) {
                    $(e).slideUp();
                    srcIcon.push('clear-folder-plus.png');
                    icon.attr('src', srcIcon.join('/'));
                    if (activeItems.length == 1) {
                        $.cookies.del('jCollapse_' + id);
                        return;
                    }
                    activeItems.splice(i, 1);
                    found = true;
                    break;
                }
            }
            if (!found) {
                activeItems.push(eid);
                $(e).slideDown();
                srcIcon.push('clear-folder-open-minus.png');
                icon.attr('src', srcIcon.join('/'));
            }
            $.cookies.set('jCollapse_' + id, activeItems.join(','));
        } else {
            $.cookies.set('jCollapse_' + id, eid);
            $(e).slideDown();
            srcIcon.push('clear-folder-open-minus.png');
            icon.attr('src', srcIcon.join('/'));
        }
     }

     function itemActive(eid, id) {
        var activeItems = $.cookies.get('jCollapse_' + id);
        if (activeItems) {
            activeItems = activeItems.split(',');
            var found = false;
            for (var i = 0; i < activeItems.length; i++) {
                if (activeItems[i] == eid) {
                    found = true;
                    break;
                }
            }
            return found;
        } else {
            return false;
        }
     }
}
})(jQuery);