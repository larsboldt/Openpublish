<div id="loginBox">
    <div id="adminbar">
        <img class="opLogo" src="<?php echo $opThemePath ?>images/adminbar_logo.png" />
    </div>

    <form method="post" action="<?php echo opURL::getUrl('/admin/login'); ?>" id="adminForm" style="padding: 20px;">

        <ul id="formTable" class="adminLogin">

            <?php
            if (isset($LoginFailed)) {
                echo '<li>';
                echo '<span class="errorBox">Unknown username/password</span>';
                echo '</li>';
            } else if (isset($CaptchaFailed)) {
                echo '<li>';
                echo '<span class="errorBox">Captcha mismatch</span>';
                echo '</li>';
            }
            if ($captchaEnabled) {
                echo '<li>
                        <span class="input-shadow">
                            <img src="'.opURL::getUrl('/admin/captcha').'" alt="Captcha" title="Captcha" class="captcha-img" />
                        </span>
                      </li>
                      <li>
                        <label for="captcha">Captcha</label>
                        <span class="input-shadow"><input type="text" id="captcha" class="form_txt" name="captcha" maxlength="6" /></span>
                      </li>';
            }
            ?>
            <li>
                <label for="username">Username</label>
                <span class="input-shadow"><input type="text" id="username" name="username" class="form_txt" maxlength="50" value="<?php echo (isset($cookieData['username'])) ? $cookieData['username'] : '' ?>" /></span>
            </li>
            <li>
                <label for="password">Password</label>
                <span class="input-shadow"><input type="password" id="password" name="password" class="form_txt" maxlength="50" value="<?php echo (isset($cookieData['password'])) ? $cookieData['password'] : '' ?>" /></span>
            </li>
            <li>
                <div id="login-remember">
                    <input style="float: left;" type="checkbox" name="rememberme" id="rememberme" value="1"<?php echo (isset($cookieData) && is_array($cookieData)) ? ' checked="true"' : '' ?> /><label for="rememberme" class="">Remember me</label>
                </div>
            </li>
        </ul>

        <div id="buttons"><a href="#" onclick="$('#adminForm').submit();" class="form_btn"><span><img class="table-icon" src="/themes/opAdmin/images/icons/tick.png" alt="" width="16" height="16"/> Login</span></a> <a href="<?php echo opURL::getUrl('/admin/resetPassword'); ?>" class="form_btn_right"><span><img class="table-icon" src="/themes/opAdmin/images/icons/lock--exclamation.png" alt="" width="16" height="16"/> Reset password</span></a></div>
    </form>
</div>