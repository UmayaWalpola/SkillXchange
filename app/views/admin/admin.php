<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/adminsidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/dashboard.css">

<main class="site-main">
<div class="dashboard-container">
<div class="dashboard-main">
<div class="admin-content">

    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <p class="admin-subtitle">Platform overview and quick access</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message"><?= $_SESSION['success'] ?><?php unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message"><?= $_SESSION['error'] ?><?php unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="ph ph-users"></i></div>
            <div class="stat-info">
                <span class="stat-number"><?= number_format($data['stats']['total_users']) ?></span>
                <span class="stat-label">Total Users</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="ph ph-check-circle"></i></div>
            <div class="stat-info">
                <span class="stat-number"><?= number_format($data['stats']['active_users']) ?></span>
                <span class="stat-label">Active Users</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="ph ph-prohibit"></i></div>
            <div class="stat-info">
                <span class="stat-number"><?= number_format($data['stats']['suspended_users']) ?></span>
                <span class="stat-label">Suspended</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="ph ph-flag"></i></div>
            <div class="stat-info">
                <span class="stat-number"><?= number_format($data['stats']['pending_reports']) ?></span>
                <span class="stat-label">Pending Reports</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="ph ph-warning"></i></div>
            <div class="stat-info">
                <span class="stat-number"><?= number_format($data['stats']['total_warnings']) ?></span>
                <span class="stat-label">Warnings Issued</span>
            </div>
        </div>
    </div>

    <section class="admin-section" style="margin-bottom:24px;">
        <div class="section-header">
            <h2 class="section-title">Shortcuts</h2>
        </div>
        <div class="quick-actions-grid">
            <a href="<?= URLROOT ?>/admin/users" class="action-card"><span class="action-text">User Management</span></a>
            <a href="<?= URLROOT ?>/admin/activityLogs" class="action-card"><span class="action-text">Activity Logs</span></a>
            <a href="<?= URLROOT ?>/admin/reports" class="action-card"><span class="action-text">Reports</span></a>
            <a href="<?= URLROOT ?>/FeedbackReport/index" class="action-card"><span class="action-text">Feedback Reports</span></a>
        </div>
    </section>

</div>
</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>