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
    <div>
        <!-- Admin Sidebar -->
        <nav class="sidebar">
            <a href="<?= URLROOT ?>/admin/dashboard" class="sidebar-item active">
                <span class="icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="<?= URLROOT ?>/admin/users" class="sidebar-item">
                <span class="icon">👥</span>
                <span>User Management</span>
            </a>
            <a href="<?= URLROOT ?>/admin/skills" class="sidebar-item">
                <span class="icon">💡</span>
                <span>Skills Management</span>
            </a>
            <a href="<?= URLROOT ?>/admin/reports" class="sidebar-item">
                <span class="icon">⚠️</span>
                <span>Reports</span>
            </a>
        </nav>
    </div>

    <script src="<?= URLROOT ?>/assets/js/admin.js" defer></script>
    
</body>
</html>