
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/auth.css">

<div class="login-container">
    <div class="login-card">
        <div class="login-left">
            <img src="<?= URLROOT ?>/assets/images/logo-new.png" alt="Skill Exchange Logo">
        </div>

        <div class="login-right">
            <h2>FORGOT PASSWORD</h2>
            <p>Enter your email to receive a reset OTP</p>

            <?php if (!empty($data['error'])): ?>
                <div style="background:#fee;color:#c33;padding:10px;border-radius:8px;margin-bottom:15px;">
                    <?= htmlspecialchars($data['error']) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($data['success'])): ?>
                <div style="background:#efe;color:#363;padding:10px;border-radius:8px;margin-bottom:15px;">
                    <?= htmlspecialchars($data['success']) ?>
                </div>
                <a href="<?= URLROOT ?>/auth/resetPassword?email=<?= urlencode($data['email']) ?>">
                    <button type="button" style="width:100%;margin-bottom:10px;">Enter OTP & Reset Password</button>
                </a>
            <?php else: ?>
                <form action="<?= URLROOT ?>/auth/forgotPassword" method="POST">
                    <input type="email" name="email" placeholder="Enter your email" required
                           value="<?= htmlspecialchars($data['email'] ?? '') ?>">
                    <button type="submit">Send OTP</button>
                </form>
            <?php endif; ?>

            <p><a href="<?= URLROOT ?>/auth/signin">Back to Login</a></p>
        </div>
    </div>
</div>