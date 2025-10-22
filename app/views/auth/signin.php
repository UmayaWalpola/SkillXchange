

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/auth.css">

<div class="login-container">
    <div class="login-card">
        <div class="login-left">
            <img src="<?= URLROOT ?>/assets/images/logo.png" alt="Skill Exchange Logo">
        </div>

        <div class="login-right">
            <h2>WELCOME BACK!</h2>
            <p>LinkUp, SkillUp</p>

            <?php if (!empty($data['error'])): ?>
                <div style="background:#fee;color:#c33;padding:10px;border-radius:8px;margin-bottom:15px;">
                    <?= htmlspecialchars($data['error']) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($data['success'])): ?>
                <div style="background:#efe;color:#363;padding:10px;border-radius:8px;margin-bottom:15px;">
                    <?= htmlspecialchars($data['success']) ?>
                </div>
            <?php endif; ?>

            <form action="<?= URLROOT ?>/auth/signin" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <p><a href="<?= URLROOT ?>/auth/forgot">Forgot password?</a></p>
                <button type="submit">Log In</button>
            </form>

            <p>Don't have an account? <a href="<?= URLROOT ?>/auth/register">Sign Up</a></p>
        </div>
    </div>
</div>

