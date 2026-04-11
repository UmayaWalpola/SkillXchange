<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/adminsidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/dashboard.css">

<?php $u = $data['user']; ?>

<main class="site-main">
<div class="dashboard-container">
<div class="dashboard-main">

    <div class="page-header">
        <div>
            <h1><?= htmlspecialchars($u->username) ?></h1>
            <p><?= htmlspecialchars($u->email) ?> &middot; <span class="role-text"><?= htmlspecialchars($u->role) ?></span></p>
        </div>
        <a href="<?= URLROOT ?>/admin/users" class="btn-outline">← Back</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message"><?= $_SESSION['success'] ?><?php unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message"><?= $_SESSION['error'] ?><?php unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Account status -->
    <div class="section-card" style="margin-bottom:24px;">
        <h2 class="section-title">Account Status</h2>

        <div style="display:flex;gap:32px;flex-wrap:wrap;margin-bottom:20px;">
            <div>
                <div style="font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Status</div>
                <span class="status-dot <?= $u->status ?>"><?= ucfirst($u->status) ?></span>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Warnings</div>
                <?php if ($u->warning_count > 0): ?>
                    <span class="warning-badge"><?= $u->warning_count ?></span>
                <?php else: ?>
                    <span style="color:#aaa;font-size:14px;">None</span>
                <?php endif; ?>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Joined</div>
                <span style="font-size:14px;"><?= date('M d, Y', strtotime($u->created_at)) ?></span>
            </div>
            <?php if ($u->status === 'suspended'): ?>
            <div>
                <div style="font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Suspension Reason</div>
                <span style="font-size:14px;"><?= htmlspecialchars($u->suspension_reason ?? '—') ?></span>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Expires</div>
                <span style="font-size:14px;"><?= $u->suspension_expires_at ? date('M d, Y', strtotime($u->suspension_expires_at)) : 'Permanent' ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($u->status === 'active'): ?>
            <button class="btn-outline" onclick="document.getElementById('suspendPanel').classList.toggle('open')" style="color:#dc2626;border-color:#dc2626;">
                Suspend User
            </button>
            <div id="suspendPanel" class="inline-form-panel">
                <form method="POST" action="<?= URLROOT ?>/admin/suspendUser">
                    <input type="hidden" name="user_id" value="<?= $u->id ?>">
                    <div class="form-group">
                        <label>Reason</label>
                        <input type="text" name="reason" required placeholder="Reason for suspension">
                    </div>
                    <div class="form-group">
                        <label>Duration</label>
                        <select name="duration">
                            <option value="permanent">Permanent</option>
                            <option value="7">7 days</option>
                            <option value="14">14 days</option>
                            <option value="30">30 days</option>
                        </select>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn-primary">Confirm Suspend</button>
                        <button type="button" class="btn-cancel" onclick="document.getElementById('suspendPanel').classList.remove('open')">Cancel</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <form method="POST" action="<?= URLROOT ?>/admin/reactivateUser" style="display:inline;">
                <input type="hidden" name="user_id" value="<?= $u->id ?>">
                <button type="submit" class="btn-outline" style="color:#16a34a;border-color:#16a34a;">Reactivate User</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Skills -->
    <?php if (!empty($data['skills'])): ?>
    <div class="section-card" style="margin-bottom:24px;">
        <h2 class="section-title">Skills</h2>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php foreach ($data['skills'] as $s): ?>
                <span class="badge <?= $s->skill_type === 'teach' ? 'badge-info' : 'badge-warning' ?>">
                    <?= htmlspecialchars($s->skill_name) ?> (<?= $s->skill_type ?>)
                </span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Warnings -->
    <div class="section-card" style="margin-bottom:24px;">
        <h2 class="section-title">Warnings (<?= count($data['warnings']) ?>)</h2>
        <?php if (!empty($data['warnings'])): ?>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>Type</th><th>Reason</th><th>By Admin</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($data['warnings'] as $w): ?>
                <tr>
                    <td><span class="badge badge-warning"><?= htmlspecialchars($w->warning_type) ?></span></td>
                    <td><?= htmlspecialchars($w->reason) ?></td>
                    <td><?= htmlspecialchars($w->admin_username) ?></td>
                    <td><?= date('M d, Y', strtotime($w->created_at)) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="no-data">No warnings issued</p>
        <?php endif; ?>
    </div>

    <!-- Reports against user -->
    <div class="section-card" style="margin-bottom:24px;">
        <h2 class="section-title">Reports Against User (<?= count($data['reports']) ?>)</h2>
        <?php if (!empty($data['reports'])): ?>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>Reason</th><th>Reported By</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($data['reports'] as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r->reason) ?></td>
                    <td><?= htmlspecialchars($r->reporter_username) ?></td>
                    <td>
                        <span class="badge <?= $r->status === 'pending' ? 'badge-warning' : ($r->status === 'resolved' ? 'badge-success' : 'badge-info') ?>">
                            <?= ucfirst($r->status) ?>
                        </span>
                    </td>
                    <td><?= date('M d, Y', strtotime($r->created_at)) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="no-data">No reports against this user</p>
        <?php endif; ?>
    </div>

    <!-- Activity -->
    <div class="section-card">
        <h2 class="section-title">Recent Activity</h2>
        <?php if (!empty($data['activities'])): ?>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>Type</th><th>Description</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($data['activities'] as $act): ?>
                <tr>
                    <td><?= htmlspecialchars($act->activity_type) ?></td>
                    <td><?= htmlspecialchars($act->description ?? '—') ?></td>
                    <td><?= date('M d, Y H:i', strtotime($act->created_at)) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="no-data">No activity recorded</p>
        <?php endif; ?>
    </div>

</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>