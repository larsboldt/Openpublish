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
var opLayout, opLayoutInner, oColor;

$(document).ready(function() {
    /* Initialize colorpicker */
    oColor = $('#opDesignModeColor').attr('value');
    $('.opDesignModeWrapperTitle').css('color', '#' + oColor);
    $('.opDesignModeWrapper').css('border', '1px dotted #'+ oColor);
    $('#colorPickerBtn').ColorPicker({
        color: oColor,
        onChange: function(hsb, hex, rgb) {
            $('.opDesignModeWrapperTitle').css('color', '#' + hex);
            $('.opDesignModeWrapper').css('border', '1px dotted #'+ hex);
        },
        onSubmit: function(hsb, hex, rgb, element) {
            window.location = '/admindm/setOutlineColor/'+hex;
        },
        onHide: function(element) {
            $('.opDesignModeWrapperTitle').css('color', '#'+ oColor);
            $('.opDesignModeWrapper').css('border', '1px dotted #'+ oColor);
            $(element).fadeOut(500);
            return false;
        }
    });

    /* Initialize droppable targets */
    $('.opDesignModeWrapper').droppable({
        activeClass: 'opDesignModeWrapperActive',
        hoverClass: 'opDesignModeWrapperHover',
        accept: '.opDesignModeDraggable',
        drop: function(event, ui) {
            var elementData = $(ui.draggable).attr('id').split(':');
            window.location = '/admindm/assignContentTo/' + $(this).attr('id') + '/' + elementData[0] + '/' + elementData[1];
        }
    });

    /* Initialize draggable elements */
    $('.opDesignModeDraggable').draggable({
        helper: function () { 
            return $(this).clone().appendTo('body').css('zIndex',9999).show();
        },
        cursor: 'move',
        cursorAt: {
            left: 0,
            top: 0
        }
    });

    /* Disable all anchor links except those with designModeLink class */
    $('a').each(function() {
        if (! $(this).hasClass('designModeLink')) {
            $(this).attr('href', '#');
            $(this).attr('target', '_self');
            $(this).bind('click', function() {
                //$(this).parent('.opDesignModeContentItem:first').click();
            });
        }
    });

    /* Replace flash/video content with a placeholder to avoid issues with zindex / topmost */
    $('object,embed').each(function() {
        $(this).replaceWith('<div align="center" style="padding:10px;background-color:#000;color:#fff;width:' + ($(this).attr('width')-20) + 'px;height:' + $(this).attr('height') + 'px;">Flash/Video disabled in designMode<br />Dimensions: ' + $(this).attr('width') + 'x' + $(this).attr('height') + 'px</div>');
    });

    /* Initialize palette */
    $('.opDesignModePalette').dmAccordion('.dmOpen');

    /* Initialize ui.layout */
    opLayout = $('body').layout({
        north: {
            resizable: false,
            onhide: 'opLayoutInner.resizeAll',
            onshow: 'opLayoutInner.resizeAll',
            onclose: 'opLayoutInner.resizeAll',
            onopen: 'opLayoutInner.resizeAll',
            onresize: 'opLayoutInner.resizeAll'
        },
        east: {
            size: 250,
            slideTrigger_open: 'mouseover',
            onhide: 'opLayoutInner.resizeAll',
            onshow: 'opLayoutInner.resizeAll',
            onclose: 'opLayoutInner.resizeAll',
            onopen: 'opLayoutInner.resizeAll',
            onresize: 'opLayoutInner.resizeAll'
        },
        west: {
            size: 36,
            resizable: false,
            slideTrigger_open: 'mouseover',
            onhide: 'opLayoutInner.resizeAll',
            onshow: 'opLayoutInner.resizeAll',
            onclose: 'opLayoutInner.resizeAll',
            onopen: 'opLayoutInner.resizeAll'
        }
    });
    opLayout.addPinBtn("#opPinButtonWest", "west");
    opLayout.addPinBtn("#opPinButtonEast", "east");

    opLayoutInner = $('.ui-layout-center').layout ({
        center: {
            paneSelector: '.opLayoutInnerCenter'
        },
        south: {
            paneSelector: '.opLayoutInnerSouth',
            size: 184,
            resizable: false,
            slidable: false,
            initHidden: true
        }
    });
    
    /* opDesignModeContentItem */
    $('.opDesignModeContentItem').bind('mouseover', function() {
        $(this).css('cursor', 'pointer');
    });
    $('.opDesignModeContentItem').bind('mouseout', function() {
        $(this).css('cursor', '');
    });

    /* Content properties */
    $('.opDesignModeContentItem').bind('click', function() {
        var paneData = $('.opLayoutInnerSouth').html();
        $('.opLayoutInnerSouth').html('<div align="center" style="margin-top:40px;"><img src="/core/dm/images/ajax.gif" /></div>');
        opLayoutInner.open('south');
        $.getJSON('/admindm/getProperties/' +$(this).attr('id'), function(data) {
            $('.opLayoutInnerSouth').html(paneData);
            $('#opLayoutProperties_layoutOwner').html(data['layoutName']);
            $('#opLayoutProperties_layoutOwnerLink').attr('href', '/admindm/' + data['parent']);
            
            $('#opLayoutProperties_contentPlugin').html(data['realPluginName']);
            $('#opLayoutProperties_contentPluginLink').attr('href', '/admin/' + data['pluginName']);

            if (data['contentEditPath'] == false) {
                $('#opLayoutProperties_contentData').html('<span id="opLayoutProperties_contentName">' + data['contentName'] + '</span>');
            } else {
                $('#opLayoutProperties_contentData').html('<a href="' + data['contentEditPath'] + data['plugin_child_id'] + '" id="opLayoutProperties_contentNameLink" class="designModeLink"><span id="opLayoutProperties_contentName">' + data['contentName'] + '</span></a>');
            }

            $('#opLayoutProperties_contentRemove').attr('href', '/admindm/removeCollectionById/' + data['id']);

            /* opContentWeightSlider */
            $('#opContentWeightSliderIndicator').html(data['position']);
            $('#opContentWeightSliderSaveBtn').attr('href', '/admindm/updateWeightById/' + data['parent'] + '/' + data['id'] + '/' + data['position']);
            $('#opContentWeightSlider').slider({
                max: 50,
                min: -50,
                value: data['position'],
                slide: function(event, ui) {
                    $("#opContentWeightSliderIndicator").html(ui.value);
                    $('#opContentWeightSliderSaveBtn').attr('href', '/admindm/updateWeightById/' + data['parent'] + '/' + data['id'] + '/' + ui.value);
                }

            });
            if (data['lockProperties'] == 1) {
                $('#opContentWeightSlider').slider('disable');
                $('#opContentWeightSliderSaveBtn').attr('href', '#').removeClass('designModeLinkSave').addClass('designModeLinkLock');
                $('#opLayoutProperties_contentRemove').attr('href', '#').removeClass('designModeLinkRemove').addClass('designModeLinkLock');
            } else {
                if (! $('#opContentWeightSliderSaveBtn').hasClass('designModeLinkSave')) {
                    $('#opContentWeightSliderSaveBtn').removeClass('designModeLinkLock').addClass('designModeLinkSave');
                }
                if (! $('#opLayoutProperties_contentRemove').hasClass('designModeLinkSave')) {
                    $('#opLayoutProperties_contentRemove').removeClass('designModeLinkLock').addClass('designModeLinkRemove');
                }
            }
        });
    });

    /* Toggle layout */
    var layoutVisible = true;
    $('#opDesignModeLayoutToggleBtn').bind('click', function() {
        if (layoutVisible) {
            opLayout.close('east');
            opLayout.close('west');
            opLayout.close('north');
            opLayoutInner.hide('south');
            layoutVisible = false;
        } else {
            opLayout.open('east');
            opLayout.open('west');
            opLayout.open('north');
            layoutVisible = true;
        }
    });
});