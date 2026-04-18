<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/managersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/dashboard.css">


    <div class="dashboard-container">
        <div class="dashboard-main">

            <div class="admin-content">
                <!-- Dashboard Header (blue rectangle) -->
                <div class="admin-header">
                    <h1>Manager Dashboard</h1>
                    <p class="admin-subtitle">Quick access to platform management</p>
                </div>

                <!-- Stats Overview -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ph ph-buildings"></i></div>
                        <div class="stat-info">
                            <span class="stat-number"><?= number_format($data['stats']['total_organizations'] ?? 0) ?></span>
                            <span class="stat-label">Organizations</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ph ph-users"></i></div>
                        <div class="stat-info">
                            <span class="stat-number"><?= number_format($data['stats']['total_users'] ?? 0) ?></span>
                            <span class="stat-label">Individual Users</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ph ph-user-gear"></i></div>
                        <div class="stat-info">
                            <span class="stat-number"><?= number_format($data['stats']['total_admins'] ?? 0) ?></span>
                            <span class="stat-label">Staff Accounts</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ph ph-megaphone"></i></div>
                        <div class="stat-info">
                            <span class="stat-number"><?= number_format($data['stats']['total_announcements'] ?? 0) ?></span>
                            <span class="stat-label">Announcements</span>
                        </div>
                    </div>
                </div>

                <!-- Shortcuts (no data tables) -->
                <section class="admin-section">
                    <div class="section-header">
                        <h2 class="section-title">Shortcuts</h2>
                    </div>

                    <div class="quick-actions-grid">
                        <a href="<?= URLROOT ?>/manager/organizations" class="action-card">
                            <span class="action-text">Organizations</span>
                        </a>
                        <a href="<?= URLROOT ?>/manager/users" class="action-card">
                            <span class="action-text">Users</span>
                        </a>
                        <a href="<?= URLROOT ?>/manager/announcements" class="action-card">
                            <span class="action-text">Announcements</span>
                        </a>
                        <a href="<?= URLROOT ?>/manager/feedback" class="action-card">
                            <span class="action-text">Platform Feedback</span>
                        </a>
                        <a href="<?= URLROOT ?>/manager/insights" class="action-card">
                            <span class="action-text">User Insights</span>
                        </a>
                    </div>
                </section>
            </div>

        </div>
    </div>


<?php require_once "../app/views/layouts/footer_user.php"; ?>