<div id="loginBox">
    <div id="adminbar">
        <img class="opLogo" src="<?php echo $opThemePath ?>images/adminbar_logo.png" />
    </div>
    <form method="post" action="<?php echo opURL::getUrl('/admin/resetPassword'); ?>" id="adminForm" style="padding: 20px;">

        <ul id="formTable" class="adminLogin">

            <?php
            if (isset($ResetFailed)) {
                echo '<li><span class="errorBox">Unknown username</span></li>';
            } else if (isset($CaptchaFailed)) {
                echo '<li><span class="infoBox">Captcha mismatch</span></li>';
            } else if (isset($ResetSuccess)) {
                echo '<li><span class="successBox">Instructions on how to reset your password has been mailed to your e-mail address.</span></li>';
            }
            ?>
            <li>
                <span class="input-shadow">
                    <img src="<?php echo opURL::getUrl('/admin/captcha'); ?>" alt="Captcha" title="Captcha" class="captcha-img" />
                </span>
            </li>
            <li>
                <label for="captcha">Captcha</label>
                <span class="input-shadow"><input type="text" id="captcha" class="form_txt" name="captcha" maxlength="6" /></span>
            </li>
            <li>
                <label for="username">Username</label>
                <span class="input-shadow"><input type="text" id="username" name="username" class="form_txt" maxlength="50" value="<?php echo (isset($cookieData['username'])) ? $cookieData['username'] : '' ?>" /></span>
            </li>
        </ul>

        <div id="buttons"><a href="#" onclick="$('#adminForm').submit();" class="form_btn"><span><img class="table-icon" src="/themes/opAdmin/images/icons/tick.png" alt="" width="16" height="16"/> Reset</span></a> <a href="<?php echo opURL::getUrl('/admin/login'); ?>" class="form_btn_right"><span><img class="table-icon" src="/themes/opAdmin/images/icons/arrow-180-medium.png" alt="" width="16" height="16"/> Back</span></a></a></div>
    </form>
</div>