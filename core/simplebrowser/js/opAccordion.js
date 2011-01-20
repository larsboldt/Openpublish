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
jQuery.fn.opAccordion = function(options) {
    var settings = $.extend({
        speed: 100
    }, options);

    if ($(this).length) {

        $(this).children('li').find('ul').css('display', 'none').addClass('opAccordion');

        $(this).find('li').each(function() {
            if ($('ul', this).length) {
                $(this).prepend('<img src="/core/simplebrowser/icons/toggle-small-expand.png" class="toggleImg accordionTrigger opAccordionToggleClosed" width="16" height="16" />');
            }
        });

        $('.accordionTrigger').bind('click', function() {
            $(this).parent('li').parent('ul').find('li').each(function() {
                $(this).find('ul.opAccordion').slideUp(settings.speed);
                $(this).children('img.opAccordionToggleOpen').attr('src', '/core/simplebrowser/icons/toggle-small-expand.png').removeClass('opAccordionToggleOpen').addClass('opAccordionToggleClosed');
            });
            if ($(this).parent('li').children('ul.opAccordion').css('display') == 'none') {
                $(this).parent('li').children('ul.opAccordion').slideDown(settings.speed);
                $(this).parent('li').children('img.opAccordionToggleClosed').attr('src', '/core/simplebrowser/icons/toggle-small.png').removeClass('opAccordionToggleClosed').addClass('opAccordionToggleOpen');
            }
        });

    }
}