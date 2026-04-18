<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/managersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/dashboard.css">


    <div class="dashboard-container">
        <div class="dashboard-main">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>User Management</h1>
                    <p>Assign and manage admin roles</p>
                </div>
                <button class="btn-primary" onclick="toggleAddForm()">+ Add New User</button>
            </div>

            <!-- Success / Error Messages -->
            <?php if (!empty($data['success'])): ?>
                <div class="success-message"><?= htmlspecialchars($data['success']) ?></div>
            <?php endif; ?>
            <?php if (!empty($data['error'])): ?>
                <div class="error-message"><?= htmlspecialchars($data['error']) ?></div>
            <?php endif; ?>

            <!-- Add New User Form (hidden by default) -->
            <div id="addUserForm" class="section-card inline-form-panel">
                <h2 class="section-title">Add New User</h2>
                <form method="POST" action="<?= URLROOT ?>/manager/addUser">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="add-name">Full Name</label>
                            <input id="add-name" type="text" name="name" required placeholder="Enter full name">
                        </div>
                        <div class="form-group">
                            <label for="add-email">Email Address</label>
                            <input id="add-email" type="email" name="email" required placeholder="Enter email">
                        </div>
                        <div class="form-group">
                            <label for="add-role">Role</label>
                            <select id="add-role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="quiz_manager">Quiz Manager</option>
                                <option value="community_admin">Community Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="add-password">Password</label>
                            <input id="add-password" type="password" name="password" required placeholder="Enter password">
                        </div>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn-primary">Add User</button>
                        <button type="button" onclick="toggleAddForm()" class="btn-cancel">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="section-card">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['users'])): ?>
                                <?php foreach ($data['users'] as $user): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($user->name) ?></strong></td>
                                        <td><?= htmlspecialchars($user->email) ?></td>
                                        <td>
                                            <span class="role-text"><?= htmlspecialchars(str_replace('_', ' ', $user->role)) ?></span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($user->created_at)) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-outline"
                                                    onclick="toggleEditForm(<?= $user->id ?>)">
                                                    Edit
                                                </button>
                                                <?php if ((string)($user->status ?? 'active') === 'active'): ?>
                                                    <form method="POST" action="<?= URLROOT ?>/manager/suspendUser"
                                                        onsubmit="return confirm('Suspend <?= htmlspecialchars($user->name) ?>?');">
                                                        <input type="hidden" name="user_id" value="<?= $user->id ?>">
                                                        <input type="hidden" name="reason" value="Suspended by manager">
                                                        <button type="submit" class="btn-outline btn-danger">Suspend</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" action="<?= URLROOT ?>/manager/reactivateUser"
                                                        onsubmit="return confirm('Reactivate <?= htmlspecialchars($user->name) ?>?');">
                                                        <input type="hidden" name="user_id" value="<?= $user->id ?>">
                                                        <button type="submit" class="btn-outline btn-success">Reactivate</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Inline Edit Form -->
                                            <div id="edit-<?= $user->id ?>" class="inline-form-panel">
                                                <form method="POST" action="<?= URLROOT ?>/manager/updateUser">
                                                    <input type="hidden" name="user_id" value="<?= $user->id ?>">
                                                    <div class="form-grid-2">
                                                        <div class="form-group">
                                                            <label for="edit-name-<?= $user->id ?>">Full Name</label>
                                                            <input id="edit-name-<?= $user->id ?>" type="text" name="name" required
                                                                value="<?= htmlspecialchars($user->name) ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="edit-email-<?= $user->id ?>">Email</label>
                                                            <input id="edit-email-<?= $user->id ?>" type="email" name="email" required
                                                                value="<?= htmlspecialchars($user->email) ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="edit-role-<?= $user->id ?>">Role</label>
                                                            <select id="edit-role-<?= $user->id ?>" name="role" required>
                                                                <option value="admin"           <?= $user->role == 'admin'           ? 'selected' : '' ?>>Admin</option>
                                                                <option value="quiz_manager"    <?= $user->role == 'quiz_manager'    ? 'selected' : '' ?>>Quiz Manager</option>
                                                                <option value="community_admin" <?= $user->role == 'community_admin' ? 'selected' : '' ?>>Community Admin</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="edit-password-<?= $user->id ?>">New Password (leave blank to keep current)</label>
                                                            <input id="edit-password-<?= $user->id ?>" type="password" name="password"
                                                                placeholder="Leave blank to keep current">
                                                        </div>
                                                    </div>
                                                    <div class="form-footer">
                                                        <button type="submit" class="btn-primary">
                                                            Save Changes
                                                        </button>
                                                        <button type="button" onclick="toggleEditForm(<?= $user->id ?>)"
                                                            class="btn-cancel">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="no-data">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>


<script>
function toggleAddForm() {
    const form = document.getElementById('addUserForm');
    form.classList.toggle('open');
}

function toggleEditForm(userId) {
    const form = document.getElementById('edit-' + userId);
    form.classList.toggle('open');
}
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>