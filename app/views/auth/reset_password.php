<?php
// app/views/auth/reset_password.php
?>
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/auth.css">

<div class="login-container">
    <div class="login-card">
        <div class="login-left">
            <img src="<?= URLROOT ?>/assets/images/logo-new.png" alt="Skill Exchange Logo">
        </div>

        <div class="login-right">
            <h2>RESET PASSWORD</h2>
            <p>Enter the OTP sent to your email</p>

            <?php if (!empty($data['error'])): ?>
                <div style="background:#fee;color:#c33;padding:10px;border-radius:8px;margin-bottom:15px;">
                    <?= htmlspecialchars($data['error']) ?>
                </div>
            <?php endif; ?>

            <form action="<?= URLROOT ?>/auth/resetPassword" method="POST">
                <input type="hidden" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>">
                <input type="text" name="otp" placeholder="6-digit OTP" maxlength="6" required
                       value="<?= htmlspecialchars($data['otp'] ?? '') ?>">
                <input type="password" name="password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                <button type="submit">Reset Password</button>
            </form>

            <p><a href="<?= URLROOT ?>/auth/forgotPassword">Resend OTP</a> &nbsp;|&nbsp; <a href="<?= URLROOT ?>/auth/signin">Back to Login</a></p>
        </div>
    </div>
</div>