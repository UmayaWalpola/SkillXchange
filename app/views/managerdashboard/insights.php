<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/managersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/dashboard.css">

<style>
    .insights-table-container {
        background: var(--white-bg);
        border: 1px solid rgba(59, 130, 246, 0.1);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .insights-table-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--dark-bg);
        margin: 0 0 18px 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .insights-table-container .data-table {
        border: none;
        table-layout: fixed;
    }

    .insights-table-container .data-table th:first-child,
    .insights-table-container .data-table td:first-child {
        width: 60%;
    }

    .insights-table-container .data-table th:last-child,
    .insights-table-container .data-table td:last-child {
        width: 40%;
        text-align: right;
    }
</style>

    <div class="dashboard-container">
        <div class="dashboard-main">

            <div class="page-header">
                <div>
                    <h1>User Insights</h1>
                    <p>Platform analytics and statistics overview</p>
                </div>
            </div>

            <!-- USERS -->
            <div class="insights-table-container">
                <div class="insights-table-title">Users</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Individual users</td>
                            <td><strong><?= $data['insights']['users']['total'] ?></strong></td>
                        </tr>
                        <tr>
                            <td>Organizations</td>
                            <td><strong><?= $data['insights']['users']['organizations'] ?></strong></td>
                        </tr>
                        <tr>
                            <td>New this month</td>
                            <td><strong><?= $data['insights']['users']['new_this_month'] ?></strong></td>
                        </tr>
                        <tr>
                            <td>Suspended</td>
                            <td><strong><?= $data['insights']['users']['suspended'] ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- BUCKX -->
            <div class="insights-table-container">
                <div class="insights-table-title">BuckX &amp; Revenue</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Completed purchases</td>
                            <td><strong><?= $data['insights']['buckx']['total_purchases'] ?></strong></td>
                        </tr>
                        <tr>
                            <td>Total BuckX purchased</td>
                            <td><strong><?= number_format($data['insights']['buckx']['total_buckx']) ?></strong></td>
                        </tr>
                        <tr>
                            <td>Total revenue</td>
                            <td><strong>LKR <?= number_format($data['insights']['buckx']['total_revenue']) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- QUIZZES -->
            <div class="insights-table-container">
                <div class="insights-table-title">Quizzes</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total attempts</td>
                            <td><strong><?= $data['insights']['quizzes']['total_attempts'] ?></strong></td>
                        </tr>
                        <tr>
                            <td>Completed attempts</td>
                            <td><strong><?= $data['insights']['quizzes']['completed_attempts'] ?></strong></td>
                        </tr>
                        <tr>
                            <td>Most attempted quiz</td>
                            <td>
                                <strong><?= htmlspecialchars($data['insights']['quizzes']['most_attempted']) ?></strong><br>
                                <span style="font-size: 0.9em; color: var(--text-muted);"><?= $data['insights']['quizzes']['most_attempted_count'] ?> attempts</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- EXCHANGES -->
            <div class="insights-table-container">
                <div class="insights-table-title">Skill Exchanges</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total exchanges</td>
                            <td><strong><?= $data['insights']['exchanges']['total'] ?></strong></td>
                        </tr>
                        <tr>
                            <td>Accepted</td>
                            <td><strong><?= $data['insights']['exchanges']['accepted'] ?></strong></td>
                        </tr>
                        <tr>
                            <td>Pending</td>
                            <td><strong><?= $data['insights']['exchanges']['pending'] ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- COMMUNITIES -->
            <div class="insights-table-container">
                <div class="insights-table-title">Communities</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total communities</td>
                            <td><strong><?= $data['insights']['communities']['total'] ?></strong></td>
                        </tr>
                        <tr>
                            <td>Total members</td>
                            <td><strong><?= $data['insights']['communities']['members'] ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>


<?php require_once "../app/views/layouts/footer_user.php"; ?>