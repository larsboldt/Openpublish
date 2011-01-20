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
jQuery.fn.dmAccordion = function(activeSelector, options) {
    var settings = $.extend({
        speed: 100
    }, options);

    $(this).children('li').find('ul').css('display', 'none').addClass('dmAccordion');
    $(this).children('li').children('div').addClass('dmAccordionHeader');
    $('.dmAccordionHeader').css('cursor', 'pointer');

    $('.dmAccordionHeader').bind('click', function() {
        $(this).parent('li').parent('ul').find('li').each(function() {
            $(this).find('ul.dmAccordion').slideUp(settings.speed);
            $(this).children('div.dmAccordionHeader').children('img.dmAccordionToggleOpen').replaceWith('<img src="/core/dm/icons/clear-folder-plus.png" class="dmAccordionToggleClosed opAdminToolbarIcon" />');
        });
        if ($(this).parent('li').children('ul.dmAccordion').css('display') == 'none') {
            $(this).parent('li').children('ul.dmAccordion').slideDown(settings.speed);
            $(this).parent('li').children('div.dmAccordionHeader').children('img.dmAccordionToggleClosed').replaceWith('<img src="/core/dm/icons/clear-folder-open-minus.png" class="dmAccordionToggleOpen opAdminToolbarIcon" />');
        }
    });

    $(activeSelector).parents().each(function() {
        if ($(this).hasClass('dmAccordion')) {
            $(this).css('display', 'block');
            $(this).parent('li').children('div.dmAccordionHeader').children('img.dmAccordionToggleClosed').replaceWith('<img src="/core/dm/icons/clear-folder-open-minus.png" class="dmAccordionToggleOpen opAdminToolbarIcon" />');
        }
    });
}