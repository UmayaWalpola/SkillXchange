<?php require_once "../app/views/layouts/header_user.php"; ?>
 <?php require_once "../app/views/layouts/adminsidebar.php"; ?>


<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/admin.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        <div class="admin-content">
            <!-- Dashboard Header -->
            <div class="admin-header">
                <h1>Admin Dashboard</h1>
                <p class="admin-subtitle">Manage your Skillxchange platform</p>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <span class="stat-number"><?= number_format($data['stats']['total_users']) ?></span>
                        <span class="stat-label">Total Users</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🔄</div>
                    <div class="stat-info">
                        <span class="stat-number"><?= number_format($data['stats']['active_exchanges']) ?></span>
                        <span class="stat-label">Active Exchanges</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <span class="stat-number"><?= number_format($data['stats']['completed_exchanges']) ?></span>
                        <span class="stat-label">Completed</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💡</div>
                    <div class="stat-info">
                        <span class="stat-number"><?= number_format($data['stats']['total_skills']) ?></span>
                        <span class="stat-label">Total Skills</span>
                    </div>
                </div>
            </div>

            <div class="admin-body">
                <!-- Reports Section -->
                <section class="admin-section">
                    <div class="section-header">
                        <h2 class="section-title">Recent Reports</h2>
                        <a href="<?= URLROOT ?>/admin/reports" class="btn-primary">See All Reports</a>
                    </div>
                    
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Reported User</th>
                                    <th>Reported By</th>
                                    <th>Reason</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data['recent_reports'])): ?>
                                    <?php foreach (array_slice($data['recent_reports'], 0, 5) as $report): ?>
                                        <tr>
                                            <td>
                                                <div class="user-cell">
                                                    <div class="user-avatar">
                                                        <?= strtoupper(substr($report->reported_username ?? 'U', 0, 2)) ?>
                                                    </div>
                                                    <span><?= htmlspecialchars($report->reported_username ?? 'Unknown') ?></span>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($report->reporter_username ?? 'Anonymous') ?></td>
                                            <td><?= htmlspecialchars($report->reason) ?></td>
                                            <td><?= date('M d, Y', strtotime($report->created_at)) ?></td>
                                            <td>
                                                <a href="<?= URLROOT ?>/admin/viewReport/<?= $report->id ?>" class="action-btn btn-view">View</a>
                                                <button class="action-btn btn-suspend" onclick="sendWarning(<?= $report->reported_user_id ?>, <?= $report->id ?>)">Warn</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center;">No reports found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- User Management Section -->
                <section class="admin-section">
                    <div class="section-header">
                        <h2 class="section-title">Recent Users</h2>
                        <a href="<?= URLROOT ?>/admin/users" class="btn-primary">View All Users</a>
                    </div>
                    
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Join Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data['recent_users'])): ?>
                                    <?php foreach ($data['recent_users'] as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="user-cell">
                                                    <div class="user-avatar">
                                                        <?= strtoupper(substr($user->username ?? 'U', 0, 2)) ?>
                                                    </div>
                                                    <span><?= htmlspecialchars($user->username ?? 'Unknown') ?></span>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($user->email) ?></td>
                                            <td><?= date('M d, Y', strtotime($user->created_at)) ?></td>
                                            <td>
                                                <span class="badge badge-success">Active</span>
                                            </td>
                                            <td>
                                                <a href="<?= URLROOT ?>/admin/viewUser/<?= $user->id ?>" class="action-btn btn-view">View</a>
                                                <button class="action-btn btn-suspend" onclick="suspendUser(<?= $user->id ?>)">Suspend</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center;">No users found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Popular Skills Section -->
                <section class="admin-section">
                    <div class="section-header">
                        <h2 class="section-title">Popular Skills</h2>
                        <a href="<?= URLROOT ?>/admin/skills" class="btn-primary">Manage Skills</a>
                    </div>
                    
                    <div class="skills-stats-grid">
                        <?php if (!empty($data['popular_skills'])): ?>
                            <?php foreach (array_slice($data['popular_skills'], 0, 3) as $skill): ?>
                                <div class="skill-stat-card">
                                    <div class="skill-stat-header">
                                        <h3><?= htmlspecialchars($skill->skill_name) ?></h3>
                                    </div>
                                    <div class="skill-stat-numbers">
                                        <div class="skill-stat-item">
                                            <span class="stat-num"><?= $skill->teachers ?></span>
                                            <span class="stat-text">Teachers</span>
                                        </div>
                                        <div class="skill-stat-item">
                                            <span class="stat-num"><?= $skill->learners ?></span>
                                            <span class="stat-text">Learners</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No skills data available</p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
</main>

<script src="<?= URLROOT ?>/assets/js/admin.js" defer></script>
<?php require_once "../app/views/layouts/footer.php"; ?>