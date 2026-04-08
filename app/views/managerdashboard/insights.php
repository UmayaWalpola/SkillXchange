<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/managersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/manager_dashboard.css">


<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">

            <div class="insights-page">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>User Insights</h1>
                    <p>Platform analytics and statistics overview</p>
                </div>
            </div>

            <!-- USERS -->
            <div class="insights-section insights-section--users">
                <div class="insights-section-title"> Users</div>
                <dl class="insights-rows">
                    <div class="insight-row">
                        <dt class="insight-label">Individual Users</dt>
                        <dd class="insight-value"><?= $data['insights']['users']['total'] ?></dd>
                    </div>
                    <div class="insight-row">
                        <dt class="insight-label">Organizations</dt>
                        <dd class="insight-value"><?= $data['insights']['users']['organizations'] ?></dd>
                    </div>
                    <div class="insight-row">
                        <dt class="insight-label">New Users This Month</dt>
                        <dd class="insight-value"><?= $data['insights']['users']['new_this_month'] ?></dd>
                    </div>
                    <div class="insight-row">
                        <dt class="insight-label">Suspended Users</dt>
                        <dd class="insight-value"><?= $data['insights']['users']['suspended'] ?></dd>
                    </div>
                </dl>
            </div>

            <!-- BUCKX -->
            <div class="insights-section insights-section--buckx">
                <div class="insights-section-title"> BuckX & Revenue</div>
                <dl class="insights-rows">
                    <div class="insight-row">
                        <dt class="insight-label">Completed Purchases</dt>
                        <dd class="insight-value"><?= $data['insights']['buckx']['total_purchases'] ?></dd>
                    </div>
                    <div class="insight-row">
                        <dt class="insight-label">Total BuckX Purchased</dt>
                        <dd class="insight-value"><?= number_format($data['insights']['buckx']['total_buckx']) ?></dd>
                    </div>
                    <div class="insight-row">
                        <dt class="insight-label">Total Revenue</dt>
                        <dd class="insight-value">LKR <?= number_format($data['insights']['buckx']['total_revenue']) ?></dd>
                    </div>
                </dl>
            </div>

            <!-- QUIZZES -->
            <div class="insights-section insights-section--quizzes">
                <div class="insights-section-title"> Quizzes</div>
                <dl class="insights-rows">
                    <div class="insight-row">
                        <dt class="insight-label">Total Quiz Attempts</dt>
                        <dd class="insight-value"><?= $data['insights']['quizzes']['total_attempts'] ?></dd>
                    </div>
                    <div class="insight-row">
                        <dt class="insight-label">Completed Attempts</dt>
                        <dd class="insight-value"><?= $data['insights']['quizzes']['completed_attempts'] ?></dd>
                    </div>
                    <div class="insight-row">
                        <dt class="insight-label">Most Attempted Quiz</dt>
                        <dd class="insight-value">
                            <span class="insight-value-main"><?= htmlspecialchars($data['insights']['quizzes']['most_attempted']) ?></span>
                            <span class="insight-value-meta">&bull; <?= $data['insights']['quizzes']['most_attempted_count'] ?> attempts</span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- EXCHANGES -->
            <div class="insights-section insights-section--exchanges">
                <div class="insights-section-title"> Skill Exchanges</div>
                <dl class="insights-rows">
                    <div class="insight-row">
                        <dt class="insight-label">Total Exchanges</dt>
                        <dd class="insight-value"><?= $data['insights']['exchanges']['total'] ?></dd>
                    </div>
                    <div class="insight-row">
                        <dt class="insight-label">Accepted</dt>
                        <dd class="insight-value"><?= $data['insights']['exchanges']['accepted'] ?></dd>
                    </div>
                    <div class="insight-row">
                        <dt class="insight-label">Pending</dt>
                        <dd class="insight-value"><?= $data['insights']['exchanges']['pending'] ?></dd>
                    </div>
                </dl>
            </div>

            <!-- COMMUNITIES -->
            <div class="insights-section insights-section--communities">
                <div class="insights-section-title"> Communities</div>
                <dl class="insights-rows">
                    <div class="insight-row">
                        <dt class="insight-label">Total Communities</dt>
                        <dd class="insight-value"><?= $data['insights']['communities']['total'] ?></dd>
                    </div>
                    <div class="insight-row">
                        <dt class="insight-label">Total Community Members</dt>
                        <dd class="insight-value"><?= $data['insights']['communities']['members'] ?></dd>
                    </div>
                </dl>
            </div>

            </div>

        </div>
    </div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>