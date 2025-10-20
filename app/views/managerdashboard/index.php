<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/managersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/manager_dashboard.css">

<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">
            
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>Manager Dashboard</h1>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card-no-icon">
                    <div class="stat-info">
                        <h3><?= $data['stats']['total_organizations'] ?? 0 ?></h3>
                        <p>Total Organizations</p>
                    </div>
                </div>

                <div class="stat-card-no-icon">
                    <div class="stat-info">
                        <h3><?= $data['stats']['total_users'] ?? 0 ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>

                <div class="stat-card-no-icon">
                    <div class="stat-info">
                        <h3><?= $data['stats']['total_admins'] ?? 0 ?></h3>
                        <p>Admin Users</p>
                    </div>
                </div>

                <div class="stat-card-no-icon">
                    <div class="stat-info">
                        <h3><?= $data['stats']['total_announcements'] ?? 0 ?></h3>
                        <p>Announcements</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="section-card">
                <h2 class="section-title">Quick Actions</h2>
                <div class="quick-actions-grid">
                    <a href="<?= URLROOT ?>/managerdashboard/organizations" class="action-card">
                        <span class="action-icon">🏢</span>
                        <span class="action-text">View Organizations</span>
                    </a>
                    
                    <a href="<?= URLROOT ?>/managerdashboard/users" class="action-card">
                        <span class="action-icon">👥</span>
                        <span class="action-text">Manage Users</span>
                    </a>
                    
                    <a href="<?= URLROOT ?>/managerdashboard/announcements" class="action-card">
                        <span class="action-icon">📢</span>
                        <span class="action-text">Post Announcement</span>
                    </a>
                    
                    <a href="<?= URLROOT ?>/managerdashboard/feedback" class="action-card">
                        <span class="action-icon">💬</span>
                        <span class="action-text">View Feedback</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>