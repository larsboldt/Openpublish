<?php
defined('_OP') or die('Access denied');
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
?>
<h3><?php echo opTranslation::getTranslation('_users', $opPluginName) ?>
<span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/users.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_users', $opPluginName) ?>" class="table-icon" /></span>
    <span class="action-right-btns">
        <a href="/admin/opUsers/userNew" title="<?php echo opTranslation::getTranslation('_new_user', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/user--plus.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_new_user', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_new_user', $opPluginName) ?></span></a>
        <!--<a href="/admin/opUsers/userAccess" title="User access"><span><img src="<?php echo $opPluginPath ?>icons/key.png" width="16" height="16" alt="" title="" class="table-icon" /> Access</span></a>-->
        <a href="javascript:$('#adminForm').submit();" onclick="return confirm('<?php echo opTranslation::getTranslation('_delete_users_msg', $opPluginName) ?>')" title="<?php echo opTranslation::getTranslation('_delete_users', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/users--minus.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_delete_users', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_delete_users', $opPluginName) ?></span></a>
    </span>
</h3>
<div id="content-plugin">
    <form method="post" id="adminForm" action="/admin/opUsers/userDelete">
        <h5 class="list"><?php echo opTranslation::getTranslation('_manage_users', $opPluginName) ?></h5>
        <ul id="sortList">
            <?php
            foreach ($opUsers as $user) {
                $superAdmin = ($user['superadmin'] == 1) ? ' disabled="true"' : '';
                echo '<li>
                        <span class="sortChk"><input type="checkbox" name="'.$user['id'].'" value="1"'.$superAdmin.' /></span>
                        <span class="sortTitle"><a href="/admin/opUsers/userEdit/'.$user['id'].'">'.$user['firstname'].' '.$user['lastname'].'</a></span>
                      </li>';
            }
            ?>
        </ul>
    </form>
</div>