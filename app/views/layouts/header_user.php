<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillXchange - Dashboard</title>
    <link rel="stylesheet" href="<?= URLROOT; ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?= URLROOT; ?>/assets/css/profile.css">
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
</head>
<body>
    <header class="header" style="left: 250px !important; width: calc(100% - 250px) !important;">
    <nav class="nav-container">
        <div class="logo-section">
            <img src="<?= URLROOT; ?>/assets/images/logo-new.png" alt="SkillXchange Logo" class="logo-image">
        </div>
        <div class="auth-section">
            <a href="<?= URLROOT ?>/users/userprofile" class="btn btn-profile">
                <?php
                    if (isset($_SESSION['user_name'])) {
                        echo strtoupper(substr($_SESSION['user_name'], 0, 2));
                    } else {
                        echo "ME";
                    }
                ?>
            </a>
            <a href="<?= URLROOT ?>/auth/logout" class="btn btn-signout">Logout</a>
        </div>
    </nav>
</header>
