<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/adminsidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/dashboard.css">

<div class="dashboard-container">
<div class="dashboard-main">

    <div class="page-header">
        <div>
            <h1>Activity Logs</h1>
            <p>Monitor user activity and admin actions</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message"><?= $_SESSION['success'] ?><?php unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message"><?= $_SESSION['error'] ?><?php unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- User activity -->
    <div class="section-card" style="margin-bottom:24px;">
        <h2 class="section-title">User Activity</h2>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>User</th><th>Type</th><th>Description</th><th>Date</th></tr></thead>
                <tbody>
                <?php if (!empty($data['user_activities'])): ?>
                    <?php foreach ($data['user_activities'] as $a): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($a->username ?? 'Unknown') ?>
                            <br><small style="color:#aaa"><?= htmlspecialchars($a->email ?? '') ?></small>
                        </td>
                        <td><?= htmlspecialchars($a->activity_type) ?></td>
                        <td><?= htmlspecialchars($a->description ?? '—') ?></td>
                        <td><?= date('M d, Y H:i', strtotime($a->created_at)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="no-data">No user activity found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Admin actions -->
    <div class="section-card">
        <h2 class="section-title">Admin Actions</h2>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>Admin</th><th>Action</th><th>Target User</th><th>Description</th><th>Date</th></tr></thead>
                <tbody>
                <?php if (!empty($data['admin_actions'])): ?>
                    <?php foreach ($data['admin_actions'] as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a->admin_username) ?></td>
                        <td><?= htmlspecialchars(str_replace('_', ' ', $a->action_type)) ?></td>
                        <td><?= htmlspecialchars($a->target_username ?? '—') ?></td>
                        <td><?= htmlspecialchars($a->description) ?></td>
                        <td><?= date('M d, Y H:i', strtotime($a->created_at)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">No admin actions found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>

<?php require_once "../app/views/layouts/footer_user.php"; ?>