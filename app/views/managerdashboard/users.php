<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/managersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/manager_dashboard.css">

<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">
            
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>User Management</h1>
                    <p>Add and manage admin users</p>
                </div>
                <button class="btn-primary" onclick="openAddUserModal()">+ Add New User</button>
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
                        <tbody id="usersTableBody">
                            <?php if(!empty($data['users'])): ?>
                                <?php foreach($data['users'] as $user): ?>
                                    <tr data-user-id="<?= $user['id'] ?>">
                                        <td><strong><?= htmlspecialchars($user['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                       <?php 
$badgeClass = 'badge-info';
if($user['role'] == 'admin') $badgeClass = 'badge-danger';
elseif($user['role'] == 'quiz_manager') $badgeClass = 'badge-warning';
elseif($user['role'] == 'manager') $badgeClass = 'badge-primary';
elseif($user['role'] == 'community_admin') $badgeClass = 'badge-success';
?>
                                        <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars(str_replace('', ' ', ucwords($user['role'], ''))) ?>
                                        </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-icon btn-edit" title="Edit User" onclick="openEditUserModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>', '<?= htmlspecialchars($user['role'], ENT_QUOTES) ?>')">
                                                    Edit
                                                </button>
                                                <button class="btn-icon btn-delete" title="Remove User" onclick="removeUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name'], ENT_QUOTES) ?>')">
                                                    Remove
                                                </button>
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
</main>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddUserModal()">&times;</span>
        <h2>Add New User</h2>
        <form id="addUserForm" data-urlroot="<?= URLROOT ?>" style="margin-top: 20px;">
            <div style="margin-bottom: 15px;">
                <label for="userName">Full Name</label>
                <input type="text" id="userName" name="name" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="userEmail">Email Address</label>
                <input type="email" id="userEmail" name="email" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="userRole">Role</label>
                <select id="userRole" name="role" required style="width: 100%; padding: 15px 20px; border-radius: 10px; border: 2px solid var(--primary-blue); font-size: 1rem; background-color: var(--blue-bg);">
                    <option value="">Select Role</option>
                    <option value="Admin">Admin</option>
                    <option value="Quiz Manager">Quiz Manager</option>
                    <option value="Community Admin">Community Admin</option>
                </select>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="userPassword">Password</label>
                <input type="password" id="userPassword" name="password" required>
            </div>
            
            <button type="submit" class="btn-primary" style="width: 100%;">Add User</button>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditUserModal()">&times;</span>
        <h2>Edit User</h2>
        <form id="editUserForm" data-urlroot="<?= URLROOT ?>" style="margin-top: 20px;">
            <input type="hidden" id="editUserId" name="user_id">
            
            <div style="margin-bottom: 15px;">
                <label for="editUserName">Full Name</label>
                <input type="text" id="editUserName" name="name" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="editUserEmail">Email Address</label>
                <input type="email" id="editUserEmail" name="email" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="editUserRole">Role</label>
                <select id="editUserRole" name="role" required style="width: 100%; padding: 15px 20px; border-radius: 10px; border: 2px solid var(--primary-blue); font-size: 1rem; background-color: var(--blue-bg);">
                    <option value="">Select Role</option>
                    <option value="Admin">Admin</option>
                    <option value="Quiz Manager">Quiz Manager</option>
                    <option value="Community Admin">Community Admin</option>
                </select>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="editUserPassword">New Password (leave blank to keep current)</label>
                <input type="password" id="editUserPassword" name="password" placeholder="Leave blank to keep current password">
            </div>
            
            <button type="submit" class="btn-primary" style="width: 100%;">Update User</button>
        </form>
    </div>
</div>

<script src="<?= URLROOT ?>/assets/js/manager_users.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>