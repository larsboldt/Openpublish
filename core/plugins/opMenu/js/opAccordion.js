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
jQuery.fn.opAccordion = function(activeSelector, options) {
    var settings = $.extend({
        speed: 100
    }, options);
    var accordion = this;

    if ($(this).length) {
        $(this).children('li').find('ul').css('display', 'none').addClass('opAccordion');

        $(this).find('li').each(function() {
            if ($('ul', this).length) {
                $(this).children('dl').children('dt').children('div.opAccordionHeader').prepend('<img src="/core/plugins/opMenu/icons/toggle-small-expand.png" class="table-icon opAccordionClick opAccordionToggleClosed" style="cursor:pointer;" width="16" height="16" />');
            } else {
                $(this).children('dl').children('dt').children('div.opAccordionHeader').prepend('<div style="width:26px;float:left;">&nbsp;</div>');
            }
        });

        $('.opAccordionClick').bind('click', function() {
            $(this).parent('div').parent('dt').parent('dl').parent('li').parent('ul').find('li').each(function() {
                $(this).find('ul.opAccordion').slideUp(settings.speed);
                $(this).children('dl').children('dt').children('.opAccordionHeader').children('img.opAccordionToggleOpen').attr('src', '/core/plugins/opMenu/icons/toggle-small-expand.png').removeClass('opAccordionToggleOpen').addClass('opAccordionToggleClosed');
            });
            if ($(this).parent('div').parent('dt').parent('dl').parent('li').children('ul.opAccordion').css('display') == 'none') {
                $(this).parent('div').parent('dt').parent('dl').parent('li').children('ul.opAccordion').slideDown(settings.speed);
                $(this).attr('src', '/core/plugins/opMenu/icons/toggle-small.png').removeClass('opAccordionToggleClosed').addClass('opAccordionToggleOpen');
            }
            classSwapper(accordion);
        });

        $(activeSelector).parents().each(function() {
            if ($(this).hasClass('opAccordion')) {
                $(this).css('display', 'block');
                $(this).parent('li').children('dl').children('dt').children('div').children('img.opAccordionToggleClosed').attr('src', '/core/plugins/opMenu/icons/toggle-small.png').removeClass('opAccordionToggleClosed').addClass('opAccordionToggleOpen');
            }
        });
        
        classSwapper(accordion);
    }

    function classSwapper(obj) {
        var cssClass = 'even';
        $(obj).find('dl').each(function() {
            if ($(this).parent('li').parent('ul').css('display') != 'none') {
                $(this).removeClass('odd').removeClass('even');
                $(this).addClass(cssClass);
                cssClass = (cssClass == 'even') ? 'odd' : 'even';
            }
        });
    }
}