<div id="loginBox">
    <div id="adminbar">
        <img class="opLogo" src="<?php echo $opThemePath ?>images/adminbar_logo.png" />
    </div>
    <form method="post" action="<?php echo opURL::getUrl('/admin/resetPassword'); ?>" id="adminForm" style="padding: 20px;">
        <h5><?php echo (isset($ResetFailed)) ? 'Failed' : 'Success'; ?></h5>
        <?php
        if (isset($ResetFailed)) {
            echo '<p style="font-size: 12px; line-height: 22px; margin: 0 0 20px 0;">Unknown or expired key.<br />A reset request expires after 24 hours.</p>';
        } else if (isset($ResetSuccess)) {
            echo '<p style="font-size: 12px; line-height: 22px; margin: 0 0 20px 0;">A new password has been sent to your registered e-mail address.</p>';
        }
        ?>
        <div id="buttons"><a href="<?php echo opURL::getUrl('/admin/login'); ?>" class="form_btn"><span><img class="table-icon" src="/themes/opAdmin/images/icons/tick.png" alt="" width="16" height="16"/> OK</span></a></div>
    </form>
</div>