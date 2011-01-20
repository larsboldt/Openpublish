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
$.fn.categoryCollapse = function() {
    var activeItem = 'albumCategory_' + parseInt($(this).attr('class'));//$.cookies.get('categoryCollapse');

    $('.albumCategory').each(function() {
        var eid = $(this).attr('id');
        var e = $(this).find('.albumList:first');
        var toggleIcon = $(this).find('img:first');
        toggleIcon.css('cursor', 'pointer');
        var srcIcon = toggleIcon.attr('src').split('/');
        srcIcon.pop();
        if (activeItem != eid) {
            var close = true;
            $('#' + activeItem).parents().each(function() {
                if ($(this).attr('id') == eid) {
                    close = false;
                }
            });
            if (close) {
                e.css('display', 'none');
                srcIcon.push('clear-folder-plus.png');
            } else {
                srcIcon.push('clear-folder-open-minus.png');
            }
        } else {
            srcIcon.push('clear-folder-open-minus.png');
        }
        toggleIcon.attr('src', srcIcon.join('/'));
        toggleIcon.bind('click', function() {
            toggle(eid);
        });
    });
  
    function toggle(eid) {
        $('.albumList').each(function() {
            var close = true;
            $(this).find('.albumCategory').each(function() {
                if ($(this).attr('id') == eid) {
                    close = false;
                }
            });
            if (close) {
                var eIcon = $(this).parent('li').find('img:first');
                var iconSplit = eIcon.attr('src').split('/');
                iconSplit.pop();
                iconSplit.push('clear-folder-plus.png');
                eIcon.attr('src', iconSplit.join('/'));
                $(this).slideUp();
            }
        });

        openActive(eid);
     }

    function openActive(eid, force) {
        if (typeof(eid) != "undefined") {
            var eIcon = $('#' + eid).find('img:first');
            var iconSplit = eIcon.attr('src').split('/');
            iconSplit.pop();
            if ($('#' + eid).children('.albumList').css('display') == 'none' || force) {
                iconSplit.push('clear-folder-open-minus.png');
                eIcon.attr('src', iconSplit.join('/'));
                $('#' + eid).find('.albumList:first').slideDown();
                //$.cookies.set('categoryCollapse', eid);
            } else {
                iconSplit.push('clear-folder-plus.png');
                eIcon.attr('src', iconSplit.join('/'));
                $('#' + eid).find('.albumList:first').slideUp();
                //$.cookies.del('categoryCollapse');
            }

            $('#' + eid).parents().each(function() {
                if ($(this).hasClass('.albumList')) {
                    $(this).slideDown();
                } else if ($(this).hasClass('.albumCategory')) {
                    eIcon = $(this).find('img:first');
                    iconSplit = eIcon.attr('src').split('/');
                    iconSplit.pop();
                    iconSplit.push('clear-folder-open-minus.png');
                    eIcon.attr('src', iconSplit.join('/'));
                }
            });

            $('#' + eid).children().each(function() {
                if ($(this).hasClass('.albumList')) {
                    $(this).hide();
                } else if ($(this).hasClass('.albumCategory')) {
                    eIcon = $(this).find('img:first');
                    iconSplit = eIcon.attr('src').split('/');
                    iconSplit.pop();
                    iconSplit.push('clear-folder-plus.png');
                    eIcon.attr('src', iconSplit.join('/'));
                }
            });
        }
     }
}
})(jQuery);