<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/managersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/manager_dashboard.css">

<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>Organizations Management</h1>
                    <p>View and manage registered organizations</p>
                </div>
            </div>

            <!-- Success / Error Messages -->
            <?php if (!empty($data['success'])): ?>
                <div class="success-message"><?= htmlspecialchars($data['success']) ?></div>
            <?php endif; ?>
            <?php if (!empty($data['error'])): ?>
                <div class="error-message"><?= htmlspecialchars($data['error']) ?></div>
            <?php endif; ?>

            <!-- Organizations Table -->
            <div class="section-card">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Organization Name</th>
                                <th>Email</th>
                                <th>Wallet Balance</th>
                                <th>Projects</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['organizations'])): ?>
                                <?php foreach ($data['organizations'] as $org): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($org->name) ?></strong></td>
                                        <td><?= htmlspecialchars($org->email) ?></td>
                                        <td><?= number_format($org->wallet_balance, 0) ?> BuckX</td>
                                        <td><?= (int) $org->project_count ?></td>
                                        <td><?= date('M d, Y', strtotime($org->created_at)) ?></td>
                                        <td>
                                            <div class="action-buttons">

                                                <?php if (!empty($org->org_cert)): ?>
                                                    <a class="btn-outline" href="<?= URLROOT ?>/<?= htmlspecialchars($org->org_cert) ?>" target="_blank" rel="noopener">
                                                        View Certificate
                                                    </a>
                                                <?php else: ?>
                                                    <span>No certificate</span>
                                                <?php endif; ?>

                                                <!-- Suspend / Reactivate -->
                                                <?php if ($org->status === 'active'): ?>
                                                   <button class="btn-outline" onclick="toggleSuspendForm(<?= $org->id ?>)">
                                                        Suspend
                                                    </button>
                                                <?php else: ?>
                                                    <form method="POST" action="<?= URLROOT ?>/manager/reactivateOrganization">
                                                        <input type="hidden" name="org_id" value="<?= $org->id ?>">
                                                        <button type="submit" class="btn-outline">
                                                            Reactivate
                                                        </button>

                                                    </form>
                                                <?php endif; ?>

                                            </div>

                                            <?php if ($org->status === 'suspended'): ?>
                                                <div class="details-panel open">
                                                    <p><strong>Suspended On:</strong> <?= $org->suspended_at ?? '—' ?></p>
                                                    <p><strong>Reason:</strong> <?= htmlspecialchars($org->suspension_reason ?? '—') ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Inline Suspend Form -->
                                            <?php if ($org->status === 'active'): ?>
                                                <div id="suspend-<?= $org->id ?>" class="inline-form-panel">
                                                    <form method="POST" action="<?= URLROOT ?>/manager/suspendOrganization">
                                                        <input type="hidden" name="org_id" value="<?= $org->id ?>">
                                                        <div class="form-group">
                                                            <label for="reason-<?= $org->id ?>">Reason</label>
                                                            <textarea id="reason-<?= $org->id ?>" name="reason" rows="2" required
                                                                placeholder="Reason for suspension..."></textarea>
                                                        </div>
                                                        <div class="form-footer">
                                                            <button type="submit" class="btn-primary">
                                                                Confirm Suspend
                                                            </button>
                                                            <button type="button" onclick="toggleSuspendForm(<?= $org->id ?>)"
                                                                class="btn-cancel">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            <?php endif; ?>

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="no-data">No organizations found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
function toggleSuspendForm(orgId) {
    const form = document.getElementById('suspend-' + orgId);
    form.classList.toggle('open');
}
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>