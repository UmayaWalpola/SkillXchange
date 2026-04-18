<?php
$uri = $_SERVER['REQUEST_URI'];
function sidebarActive($path) {
    global $uri;
    return strpos($uri, $path) !== false ? 'active' : '';
}
?>
<nav class="sidebar">
    <a href="<?= URLROOT ?>/admin/dashboard" class="sidebar-item <?= sidebarActive('/admin/dashboard') ?>">
        <span class="icon"><i class="ph ph-gauge"></i></span>
        <span>Dashboard</span>
    </a>
    <a href="<?= URLROOT ?>/admin/users" class="sidebar-item <?= sidebarActive('/admin/users') ?>">
        <span class="icon"><i class="ph ph-users"></i></span>
        <span>User Management</span>
    </a>
    <a href="<?= URLROOT ?>/admin/activityLogs" class="sidebar-item <?= sidebarActive('/admin/activityLogs') ?>">
        <span class="icon"><i class="ph ph-list-checks"></i></span>
        <span>Activity Logs</span>
    </a>
    <a href="<?= URLROOT ?>/admin/reports" class="sidebar-item <?= sidebarActive('/admin/reports') ?>">
        <span class="icon"><i class="ph ph-flag"></i></span>
        <span>Reports Management</span>
    </a>
    <a href="<?= URLROOT ?>/FeedbackReport/index" class="sidebar-item <?= sidebarActive('/FeedbackReport') ?>">
        <span class="icon"><i class="ph ph-chat-circle-dots"></i></span>
        <span>Feedback Reports</span>
    </a>
</nav>