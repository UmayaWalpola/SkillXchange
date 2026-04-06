<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/managersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/manager_dashboard.css">


<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>User Insights</h1>
                    <p>Platform analytics and statistics overview</p>
                </div>
            </div>

            <!-- USERS -->
            <div class="insights-section">
                <div class="insights-section-title"> Users</div>
                <div class="insights-grid">
                    <div class="insight-card accent-users">
                        <span class="card-value"><?= $data['insights']['users']['total'] ?></span>
                        <span class="card-label">Individual Users</span>
                    </div>
                    <div class="insight-card accent-users">
                        <span class="card-value"><?= $data['insights']['users']['organizations'] ?></span>
                        <span class="card-label">Organizations</span>
                    </div>
                    <div class="insight-card accent-users">
                        <span class="card-value"><?= $data['insights']['users']['new_this_month'] ?></span>
                        <span class="card-label">New Users This Month</span>
                    </div>
                    <div class="insight-card accent-users">
                        <span class="card-value"><?= $data['insights']['users']['suspended'] ?></span>
                        <span class="card-label">Suspended Users</span>
                    </div>
                </div>
            </div>

            <!-- BUCKX -->
            <div class="insights-section">
                <div class="insights-section-title"> BuckX & Revenue</div>
                <div class="insights-grid-3">
                    <div class="insight-card accent-buckx">
                        <span class="card-value"><?= $data['insights']['buckx']['total_purchases'] ?></span>
                        <span class="card-label">Completed Purchases</span>
                    </div>
                    <div class="insight-card accent-buckx">
                        <span class="card-value"><?= number_format($data['insights']['buckx']['total_buckx']) ?></span>
                        <span class="card-label">Total BuckX Purchased</span>
                    </div>
                    <div class="insight-card accent-buckx">
                        <span class="card-value small">LKR <?= number_format($data['insights']['buckx']['total_revenue']) ?></span>
                        <span class="card-label">Total Revenue</span>
                    </div>
                </div>
            </div>

            <!-- QUIZZES -->
            <div class="insights-section">
                <div class="insights-section-title"> Quizzes</div>
                <div class="insights-grid-3">
                    <div class="insight-card accent-quizzes">
                        <span class="card-value"><?= $data['insights']['quizzes']['total_attempts'] ?></span>
                        <span class="card-label">Total Quiz Attempts</span>
                    </div>
                    <div class="insight-card accent-quizzes">
                        <span class="card-value"><?= $data['insights']['quizzes']['completed_attempts'] ?></span>
                        <span class="card-label">Completed Attempts</span>
                    </div>
                    <div class="insight-card accent-quizzes">
                        <span class="card-value small"><?= htmlspecialchars($data['insights']['quizzes']['most_attempted']) ?></span>
                        <span class="card-label">Most Attempted Quiz &bull; <?= $data['insights']['quizzes']['most_attempted_count'] ?> attempts</span>
                    </div>
                </div>
            </div>

            <!-- EXCHANGES -->
            <div class="insights-section">
                <div class="insights-section-title"> Skill Exchanges</div>
                <div class="insights-grid-3">
                    <div class="insight-card accent-exchanges">
                        <span class="card-value"><?= $data['insights']['exchanges']['total'] ?></span>
                        <span class="card-label">Total Exchanges</span>
                    </div>
                    <div class="insight-card accent-exchanges">
                        <span class="card-value"><?= $data['insights']['exchanges']['accepted'] ?></span>
                        <span class="card-label">Accepted</span>
                    </div>
                    <div class="insight-card accent-exchanges">
                        <span class="card-value"><?= $data['insights']['exchanges']['pending'] ?></span>
                        <span class="card-label">Pending</span>
                    </div>
                </div>
            </div>

            <!-- COMMUNITIES -->
            <div class="insights-section">
                <div class="insights-section-title"> Communities</div>
                <div class="insights-grid-2">
                    <div class="insight-card accent-community">
                        <span class="card-value"><?= $data['insights']['communities']['total'] ?></span>
                        <span class="card-label">Total Communities</span>
                    </div>
                    <div class="insight-card accent-community">
                        <span class="card-value"><?= $data['insights']['communities']['members'] ?></span>
                        <span class="card-label">Total Community Members</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>