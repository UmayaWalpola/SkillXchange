<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/adminsidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/dashboard.css">

<div class="dashboard-container">
<div class="dashboard-main">

    <div class="page-header">
        <div>
            <h1>User Management</h1>
            <p>View and manage all platform users</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message"><?= $_SESSION['success'] ?><?php unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message"><?= $_SESSION['error'] ?><?php unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="section-card">
        <!-- Filters -->
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
            <input type="text" id="search" class="search-input" placeholder="Search username or email…" oninput="applyFilters()" style="flex:1;min-width:200px;padding:9px 14px;border:1px solid var(--blue-bg);border-radius:8px;font-size:14px;">
            <select id="filterStatus" onchange="applyFilters()" style="padding:9px 14px;border:1px solid var(--blue-bg);border-radius:8px;font-size:14px;">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
            </select>
            <select id="filterRole" onchange="applyFilters()" style="padding:9px 14px;border:1px solid var(--blue-bg);border-radius:8px;font-size:14px;">
                <option value="">All Roles</option>
                <option value="individual">Individual</option>
                <option value="organization">Organization</option>
                <option value="quiz_manager">Quiz Manager</option>
                <option value="community_admin">Community Admin</option>
                <option value="manager">Manager</option>
            </select>
        </div>

        <div class="table-container">
            <table class="data-table" id="usersTable">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Warnings</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($data['users'])): ?>
                    <?php foreach ($data['users'] as $u): ?>
                    <tr data-username="<?= strtolower($u->username) ?>"
                        data-email="<?= strtolower($u->email) ?>"
                        data-status="<?= $u->status ?>"
                        data-role="<?= $u->role ?>">
                        <td>
                            <?= htmlspecialchars($u->username) ?>
                            <br><small style="color:#aaa"><?= htmlspecialchars($u->email) ?></small>
                        </td>
                        <td><span class="role-text"><?= htmlspecialchars($u->role) ?></span></td>
                        <td>
                            <?php if ($u->warning_count > 0): ?>
                                <span class="warning-badge"><?= $u->warning_count ?></span>
                            <?php else: ?>
                                <span style="color:#aaa">0</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="status-dot <?= $u->status ?>"><?= ucfirst($u->status) ?></span></td>
                        <td><?= date('M d, Y', strtotime($u->created_at)) ?></td>
                        <td><a href="<?= URLROOT ?>/admin/viewUser/<?= $u->id ?>" class="btn-outline">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="no-data">No users found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>

<script>
function applyFilters() {
    const q      = document.getElementById('search').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    const role   = document.getElementById('filterRole').value;
    document.querySelectorAll('#usersTable tbody tr[data-username]').forEach(row => {
        const matchQ      = !q      || row.dataset.username.includes(q) || row.dataset.email.includes(q);
        const matchStatus = !status || row.dataset.status === status;
        const matchRole   = !role   || row.dataset.role   === role;
        row.style.display = (matchQ && matchStatus && matchRole) ? '' : 'none';
    });
}
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>