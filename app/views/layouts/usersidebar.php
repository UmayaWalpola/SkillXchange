<?php require_once "../app/views/layouts/header_user.php"; ?>

<link rel="stylesheet" href="<?= URLROOT; ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT; ?>/assets/css/profile.css">

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sidebar - SkillXchange</title>
    
</head>
<body>
    <div><!-- Sidebar Dashboard -->
    <nav class="sidebar">
        <a href="#" class="sidebar-item">
            <span class="icon"></span>
            <span>Profile</span>
        </a>
        <a href="#" class="sidebar-item">
            <span class="icon"></span>
            <span>Notifications</span>
        </a>
        <a href="#" class="sidebar-item">
            <span class="icon"></span>
            <span>Chats</span>
        </a>
        <a href="#" class="sidebar-item">
            <span class="icon"></span>
            <span>Your Matches</span>
        </a>
        <a href="#" class="sidebar-item">
            <span class="icon"></span>
            <span>Communities</span>
        </a>
        <a href="#" class="sidebar-item">
            <span class="icon"></span>
            <span>Take a Quiz</span>
        </a>
        <a href="#" class="sidebar-item active">
            <span class="icon"></span>
            <span>Projects</span>
        </a>
        <a href="<?= URLROOT ?>/wallet" class="sidebar-item">
            <span class="icon"></span>
            <span>Wallet</span>
        </a>
    </nav> </div>


    <script src="<?= URLROOT ?>/assets/js/profile.js" defer></script>

    
</body>
</html>
