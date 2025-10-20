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
                                            if($user['role'] == 'Admin') $badgeClass = 'badge-danger';
                                            elseif($user['role'] == 'Quiz Manager') $badgeClass = 'badge-warning';
                                            elseif($user['role'] == 'Community Admin') $badgeClass = 'badge-success';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($user['role']) ?></span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-icon btn-delete" title="Remove User" onclick="removeUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')">
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
        <form id="addUserForm" style="margin-top: 20px;">
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

<script>
function openAddUserModal() {
    document.getElementById('addUserModal').style.display = 'block';
}

function closeAddUserModal() {
    document.getElementById('addUserModal').style.display = 'none';
    document.getElementById('addUserForm').reset();
}

document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= URLROOT ?>/managerdashboard/addUser', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeAddUserModal();
            location.reload(); // Reload to show new user
        } else {
            alert('Error adding user');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding user');
    });
});

function removeUser(userId, userName) {
    if (confirm(`Are you sure you want to remove "${userName}"? This action cannot be undone.`)) {
        fetch('<?= URLROOT ?>/managerdashboard/removeUser', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                document.querySelector(`tr[data-user-id="${userId}"]`).remove();
            } else {
                alert('Error removing user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error removing user');
        });
    }
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const addModal = document.getElementById('addUserModal');
    if (event.target == addModal) {
        addModal.style.display = 'none';
    }
}
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>