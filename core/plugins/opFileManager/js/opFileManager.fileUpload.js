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
var uploadField = 2;
$(document).ready(function() {
    $('#uploadMsg').css('display', 'none');
});
function startUpload() {
    $('#uploadMsg').css('display', 'block');
    $('.form_btn').css('display', 'none');
    $('#tagArea').css('display', 'none');
    $('#tagList').find('li').each(function() {
        $('#adminForm').prepend('<input type="hidden" name="tags[]" value=" ' + $(this).children('span').children('span').html() + ' " />');
    });
    $('#adminForm').submit();
}
function addUpload() {
    $('#uploadFields').append('<div class="opAdminFormItem"><input type="file" id="file' + uploadField + '" name="file' + uploadField + '" size="30" /></div>');
    $('#file' + uploadField).uniform();
    uploadField++;
}