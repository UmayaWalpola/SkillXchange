<!-- Admin Sidebar -->
<nav class="sidebar" style="z-index: 99;">
    <a href="<?= URLROOT ?>/admin/dashboard" class="sidebar-item <?= (strpos($_SERVER['REQUEST_URI'], 'admin/dashboard') !== false) ? 'active' : '' ?>">
        <span>Dashboard</span>
    </a>

    <a href="<?= URLROOT ?>/admin/users" class="sidebar-item <?= (strpos($_SERVER['REQUEST_URI'], 'admin/users') !== false) ? 'active' : '' ?>">
        <span>User Management</span>
    </a>

    <a href="<?= URLROOT ?>/admin/reports" class="sidebar-item <?= (strpos($_SERVER['REQUEST_URI'], 'admin/reports') !== false) ? 'active' : '' ?>">
        <span>Reports</span>
    </a>

    <a href="<?= URLROOT ?>/admin/activityLogs" class="sidebar-item <?= (strpos($_SERVER['REQUEST_URI'], 'admin/activityLogs') !== false) ? 'active' : '' ?>">
        <span>Activity Logs</span>
    </a>

    <a href="<?= URLROOT ?>/FeedbackReport/index" class="sidebar-item <?= (strpos($_SERVER['REQUEST_URI'], '/FeedbackReport') !== false) ? 'active' : '' ?>">
        <span>Reported Feedback</span>
    </a>
</nav>
