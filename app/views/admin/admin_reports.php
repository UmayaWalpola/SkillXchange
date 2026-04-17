<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/adminsidebar.php"; ?>
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/dashboard.css">

<main class="site-main">
<div class="dashboard-container">
<div class="dashboard-main">

    <div class="page-header">
        <div>
            <h1>Project Member Reports</h1>
            <p>Reports submitted by organizations against project members</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message"><?= $_SESSION['success'] ?><?php unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message"><?= $_SESSION['error'] ?><?php unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="section-card">
        <?php if (empty($data['reports'])): ?>
            <p class="no-data">No reports found</p>
        <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Reported User</th>
                        <th>Reported By (Org)</th>
                        <th>Project</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($data['reports'] as $r): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($r->reported_name) ?>
                        <br><small style="color:#aaa"><?= htmlspecialchars($r->reported_email) ?></small>
                    </td>
                    <td><?= htmlspecialchars($r->reporter_name) ?></td>
                    <td><?= htmlspecialchars($r->project_name) ?></td>
                    <td><?= htmlspecialchars($r->reason) ?>
                        <?php if (!empty($r->description)): ?>
                            <br><small style="color:#aaa"><?= htmlspecialchars($r->description) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-dot <?= $r->status ?>">
                            <?= ucfirst($r->status) ?>
                        </span>
                    </td>
                    <td><?= date('M d, Y', strtotime($r->created_at)) ?></td>
                    <td>
                        <?php if ($r->status === 'pending'): ?>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <!-- Warn button triggers inline form -->
                            <button class="btn-outline" style="color:#d97706;border-color:#d97706;font-size:13px;"
                                onclick="document.getElementById('warn-<?= $r->id ?>').classList.toggle('open')">
                                 Warn
                            </button>
                            <form method="POST" action="<?= URLROOT ?>/admin/dismissReport">
                                <input type="hidden" name="report_id" value="<?= $r->id ?>">
                                <button type="submit" class="btn-outline" style="font-size:13px;">Dismiss</button>
                            </form>
                        </div>

                        <!-- Inline warn form -->
                        <div id="warn-<?= $r->id ?>" class="inline-form-panel">
                            <form method="POST" action="<?= URLROOT ?>/admin/warnUser">
                                <input type="hidden" name="report_id" value="<?= $r->id ?>">
                                <input type="hidden" name="user_id"   value="<?= $r->reported_user_id ?>">
                                <div class="form-group">
                                    <label>Warning reason</label>
                                    <input type="text" name="reason" required
                                        placeholder="e.g. Inappropriate behaviour in project"
                                        value="<?= htmlspecialchars($r->reason) ?>">
                                </div>
                                <div class="form-footer">
                                    <button type="submit" class="btn-primary">Send Warning</button>
                                    <button type="button" class="btn-cancel"
                                        onclick="document.getElementById('warn-<?= $r->id ?>').classList.remove('open')">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                        <?php else: ?>
                            <span style="color:#aaa;font-size:13px;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>