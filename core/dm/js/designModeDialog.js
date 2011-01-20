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
$.fn.dmd = function() {
    $(this).bind('click', function(e) {
        var viewportH = parseInt($(window).height());
        var viewportW = parseInt($(window).width());
        var elementH  = parseInt($('#dmdOuterWindow').css('height'));
        var elementW  = parseInt($('#dmdOuterWindow').css('width'));
        var posX      = parseInt(e.pageX);
        var posY      = parseInt(e.pageY);
        var goLeft    = ((viewportW/2) > posX) ? false : true;
        var goDown    = ((viewportH/2) > posY) ? true : false;

        var aPosX     = (goLeft) ? posX-elementW : posX;
        var aPosY     = (goDown) ? posY : posY-elementH;

        var id        = $(this).attr('id');
        var splitArr  = id.split('-');
        var cid       = splitArr[2];
        var title     = $('#dmd-info-title-' + cid).html();
        var lid       = parseInt($('#dmd-info-lid-' + cid).html());
        var weight    = parseInt($('#dmd-info-weight-' + cid).html());
        var lock      = ($('#dmd-info-lock-' + cid).length > 0) ? true : false;

        $('.dmd-header').html('<a href="/admin/opLayout/designMode/' + lid + '" target="_parent">#' + title + '</a>');
        $('#dmd-current-weight').html(weight);
        $('#slider').slider('value', weight);
        if (lock) {
            $('#slider').slider('disable');
            $('.dmd-btn-delete').hide();
            $('.dmd-btn-save').hide();
        } else {
            $('#slider').slider('enable');
            $('.dmd-btn-delete').show();
            $('.dmd-btn-save').show();
            $('.dmd-btn-delete').attr('href', '/admindm/removeCollectionById/' + lid + '/' + cid);
            $('.dmd-btn-save').attr('href', '/admindm/updateWeightById/' + lid + '/' + cid);
        }

        $('#dmdWindow').hide();
        $('#dmdOuterWindow').hide();

        $('#dmdOuterWindow').css('left', aPosX);
        $('#dmdOuterWindow').css('top', aPosY);
        $('#dmdOuterWindow').show();
        jQuery.easing.def = 'easeOutQuart';
        if (goDown) {
            $('#dmdWindow').css('top', 0);
            $('#dmdWindow').css('bottom', null);
            $('#dmdWindow').slideDown(700);
        } else {
            $('#dmdWindow').css('top', null);
            $('#dmdWindow').css('bottom', 0);
            $('#dmdWindow').slideDown(700);
        }
    });
 
    $(this).bind('mouseover', function() {
        $(this).attr('src', '/core/dm/icons/layer-select.png');
        /*
        var id = $(this).attr('id');
        var splitArr = id.split('-');
        $('#dmd-content-' + splitArr[2]).css('background-color', '#eaeaea');
        */
    });
    $(this).bind('mouseout', function() {
        $(this).attr('src', '/core/dm/icons/layer.png');
        /*
        var id = $(this).attr('id');
        var splitArr = id.split('-');
        $('#dmd-content-' + splitArr[2]).css('background-color', null);
        */
    });
   
    $('.dmd-btn-save').bind('click', function() {
        var href = $(this).attr('href');
        $(this).attr('href', href+'/'+$('#slider').slider('value'));
    });

    $('.dmd-btn-close').bind('click', function() {
        $('#dmdOuterWindow').hide();
    });

    $('.dmd-btn-delete').bind('click', function() {
        return confirm('Are you sure you want to delete this item?');
    });
}
})(jQuery);