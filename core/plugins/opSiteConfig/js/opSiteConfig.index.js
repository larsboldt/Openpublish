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
    $('input:radio[name="login_protection"]').bind('click', function() {
        if ($(this).val() == 2) {
            alert("Caution!\nWhitelist protection requires you to specify the IP's that will have access to the administration panel. If you leave the field blank or don't include your own you will lock yourself out of the administration panel. Only use whitelist with static IP's!");
        }
    });
});