<?php require_once "../app/views/layouts/header_user.php"; ?>

<link rel="stylesheet" href="<?= URLROOT; ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT; ?>/assets/css/admin.css">

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SkillXchange</title>
</head>
<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            
            <div class="sidebar-menu">
                <a href="<?= URLROOT ?>/admin/dashboard" class="sidebar-item active">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
        </nav>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Your page content goes here -->
        </main>
    </div>

    <script src="<?= URLROOT ?>/assets/js/admin.js" defer></script>
</body>
</html>