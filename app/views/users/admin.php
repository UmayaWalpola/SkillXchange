<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/adminsidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/profile.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/admin.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        <div class="admin-content">
            <!-- Dashboard Header -->
            <div class="profile-header">
                <div class="profile-info">
                    <div class="profile-avatar">AD</div>
                    <div class="profile-details">
                        <h1>Admin Dashboard</h1>
                        <p class="profile-bio">Manage your Skillxchange platform</p>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-number"><?= number_format($data['stats']['total_users']) ?></span>
                        <span class="stat-label">Total Users</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-number"><?= number_format($data['stats']['active_users']) ?></span>
                        <span class="stat-label">Active Users</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-number"><?= number_format($data['stats']['pending_reports']) ?></span>
                        <span class="stat-label">Pending Reports</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-number"><?= number_format($data['stats']['suspended_users']) ?></span>
                        <span class="stat-label">Suspended Users</span>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success'] ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="admin-body">
                <section class="admin-section" style="text-align: center;">
                    <div class="section-header" style="justify-content: center; gap: 1.5rem;">
                        <h2 class="section-title">Quick Links</h2>
                    </div>

                    <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; margin-top:1rem;">
                        <a href="<?= URLROOT ?>/admin/users" class="btn-primary" style="min-width:180px;">User Management</a>
                        <a href="<?= URLROOT ?>/admin/reports" class="btn-primary" style="min-width:180px;">Reports</a>
                        <a href="<?= URLROOT ?>/admin/activityLogs" class="btn-primary" style="min-width:180px;">Activity Logs</a>
                    </div>

                    <p class="text-muted" style="margin-top:1rem;">Use the links above to manage users, review reports and view activity logs.</p>
                </section>
            </div>

                <!-- (Popular Skills removed as requested) -->
            </div>
        </div>
    </div>
</div>
</main>

<style>
.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.alert-success {
    background: #dcfce7;
    color: #166534;
    border: 2px solid #22c55e;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 2px solid #ef4444;
}

.reason-badge {
    display: inline-block;
    padding: 0.4rem 0.9rem;
    background: var(--blue-bg);
    color: var(--primary-blue);
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.warning-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background: #fef3c7;
    color: #f59e0b;
    border-radius: 50%;
    font-weight: bold;
    font-size: 0.9rem;
}

.badge-danger {
    background: #fee2e2;
    color: #dc2626;
}

.text-muted {
    color: #9ca3af;
}

.text-center {
    text-align: center;
}

.activity-log-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-log-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: var(--white-bg);
    border: 2px solid var(--blue-bg);
    border-radius: 10px;
    transition: all 0.3s ease;
}

.activity-log-item:hover {
    border-color: var(--accent-blue);
    box-shadow: 0 4px 12px rgba(101, 131, 150, 0.1);
}

.activity-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    background: var(--blue-bg);
    border-radius: 10px;
    flex-shrink: 0;
}

.activity-details {
    flex: 1;
}

.activity-title {
    font-size: 1rem;
    margin-bottom: 0.3rem;
    color: var(--dark-bg);
}

.activity-description {
    font-size: 0.9rem;
    color: #6b7280;
    margin-bottom: 0.3rem;
}

.activity-time {
    font-size: 0.85rem;
    color: #9ca3af;
}
</style>

<script src="<?= URLROOT ?>/assets/js/admin.js" defer></script>
<?php require_once "../app/views/layouts/footer_user.php"; ?>