<?php
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
<div id="container">

    <h3>Installation wizard | 2 of 3
        <span class="heading-icon"><img src="/themes/opAdmin/images/icons/box.png" width="16" height="16" alt="" title="" class="table-icon" /></span>
    </h3>

    <div id="content-plugin">

        <form id="adminForm" name="adminForm" action="/install/index.php?step=2" method="post">
            <h5>Site & Server setup</h5>
            <div class="opAdminFormItem">
                <label>Sitename:</label>
                <span class="input-shadow"><input type="text" class="form_txt" name="sitename" value="<?php echo ($installData) ? $installData['sitename'] : '' ?>" /></span>
            </div>
            <div class="opAdminFormItem">
                <label>Domain (example.com):</label>
                <span class="input-shadow"><input type="text" class="form_txt" name="domain" value="<?php echo ($installData) ? $installData['domain'] : '' ?>" /></span>
            </div>
            <div class="opAdminFormItem">
                <label>Administrator firstname:</label>
                <span class="input-shadow"><input type="text" class="form_txt" name="adminfn" value="<?php echo ($installData) ? $installData['adminfn'] : '' ?>" /></span>
            </div>
            <div class="opAdminFormItem">
                <label>Administrator lastname:</label>
                <span class="input-shadow"><input type="text" class="form_txt" name="adminln"  value="<?php echo ($installData) ? $installData['adminln'] : '' ?>" /></span>
            </div>
            <div class="opAdminFormItem">
                <label>Administrator e-mail:</label>
                <span class="input-shadow"><input type="text" class="form_txt" name="adminemail" value="<?php echo ($installData) ? $installData['adminemail'] : '' ?>" /></span>
            </div>
            <div class="opAdminFormItem">
                <label>Administrator password:</label>
                <span class="input-shadow"><input type="password" class="form_txt" name="adminpwd" value="<?php echo ($installData) ? $installData['adminpwd'] : '' ?>" /></span>
            </div>
            <div class="opAdminFormItem">
                <label>Server timezone:</label>
                <span class="input-shadow">
                    <select name="timezone">
                        <?php
                        $timezones = DateTimeZone::listAbbreviations();

                        $cities = array();
                        foreach($timezones as $key => $zones) {
                            foreach($zones as $id => $zone) {
                                if (preg_match('/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $zone['timezone_id'])) {
                                    $cities[$zone['timezone_id']][] = $key;
                                }
                            }
                        }

                        ksort($cities);
                        foreach($cities as $key => $value) {
                            echo '<option value="'.$key.'">'.$key.'</option>';
                        }
                        ?>
                    </select>
                </span>
            </div>
            <div class="opAdminFormItem">
                <label>Database driver:</label>
                <span class="input-shadow">
                    <select name="database">
                        <option value="mysql">MySQL</option>
                        <!--<option value="postgre">PostgreSQL</option>-->
                    </select>
                </span>
            </div>
            <div class="opAdminFormItem">
                <label>Database host:</label>
                <span class="input-shadow"><input type="text" class="form_txt" name="dbhost" value="<?php echo ($installData) ? $installData['dbhost'] : 'localhost' ?>" /></span>
            </div>
            <div class="opAdminFormItem">
                <label>Database name:</label>
                <span class="input-shadow"><input type="text" class="form_txt" name="dbname" value="<?php echo ($installData) ? $installData['dbname'] : '' ?>" /></span>
            </div>
            <div class="opAdminFormItem">
                <label>Database username:</label>
                <span class="input-shadow"><input type="text" class="form_txt" name="dbuser"  value="<?php echo ($installData) ? $installData['dbuser'] : '' ?>" /></span>
            </div>
            <div class="opAdminFormItem">
                <label>Database password:</label>
                <span class="input-shadow"><input type="password" class="form_txt" name="dbpass"  value="<?php echo ($installData) ? $installData['dbpass'] : '' ?>" /></span>
            </div>
        </form>

        <div id="page-btn-back">
            <a class="form_btn" href="/install/index.php?step=1"><span><img src="/themes/opAdmin/images/icons/arrow-180-medium.png" width="16" height="16" border="0" alt="" class="table-icon" /> Back</span></a>
        </div>

        <div id="page-btn-next">
            <a class="form_btn" href="#" onclick="document.adminForm.submit();"><span>Next <img src="/themes/opAdmin/images/icons/arrow-000-medium.png" width="16" height="16" border="0" alt="" class="table-icon" /></span></a>
        </div>

    </div><!--END content-plugin-->

</div><!--END container-->