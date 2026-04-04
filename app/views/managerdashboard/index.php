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

            <!-- Quick Actions -->
            <div class="section-card">
                <h2 class="section-title">Quick Actions</h2>
                <div class="quick-actions-grid">
                    <a href="<?= URLROOT ?>/manager/organizations" class="action-card">
                        <span class="action-text">View Organizations</span>
                    </a>
                    
                    <a href="<?= URLROOT ?>/manager/users" class="action-card">
                        <span class="action-text">Manage Users</span>
                    </a>
                    
                    <a href="<?= URLROOT ?>/manager/announcements" class="action-card">
                        <span class="action-text">Announcements</span>
                    </a>
                    
                    <a href="<?= URLROOT ?>/manager/feedback" class="action-card">
                        <span class="action-text">View Feedback</span>
                    </a>

                    <a href="<?= URLROOT ?>/manager/insights" class="action-card">
                        <span class="action-text">User Insights</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>