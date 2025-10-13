

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/auth.css">

<div class="login-container">
    <div class="login-card">
        <div class="login-left">
            <img src="<?= URLROOT ?>/images/logo.png" alt="Skill Exchange Logo">
        </div>

        <div class="login-right">
            <h2> WELCOME BACK ! </h2>
            <p> LinkUp, SkillUp </p>

            <form action="<?= URLROOT ?>/auth/login" method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <p><a href="<?= URLROOT ?>/auth/forgot"> Forgot password? </a></p>
                <button type="submit">Log In</button>
            </form>

            <p>Don't have an account? <a href="<?= URLROOT ?>/auth/register">Sign Up</a></p>
        </div>
    </div>
</div>


