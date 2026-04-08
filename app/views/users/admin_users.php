<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/adminsidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/profile.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/admin.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        <div class="admin-content">
            <!-- Header -->
            <div class="profile-header">
                <div class="profile-info">
                    <div class="profile-avatar">AD</div>
                    <div class="profile-details">
                        <h1>User Management</h1>
                        <p class="profile-bio">Manage and monitor all platform users</p>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success'] ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Search and Filter Bar (no default newest-first sorting) -->
            <div class="admin-section">
                <div class="filter-bar">
                    <div class="search-box">
                        <input type="text" id="user-search" placeholder="Search by username or email..." 
                               class="search-input" onkeyup="searchUsers()">
                    </div>
                    <div class="filter-options">
                        <select id="status-filter" class="filter-select" onchange="filterUsers()">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                        <select id="role-filter" class="filter-select" onchange="filterUsers()">
                            <option value="all">All Roles</option>
                            <option value="individual">Individual</option>
                            <option value="organization">Organization</option>
                            <option value="quiz_manager">Quiz Manager</option>
                            <option value="community_admin">Community Admin</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Users Tables split by role -->
            <?php
                $grouped = [];
                if (!empty($data['users'])) {
                    foreach ($data['users'] as $u) {
                        $r = isset($u->role) ? $u->role : 'unknown';
                        if (!isset($grouped[$r])) $grouped[$r] = [];
                        $grouped[$r][] = $u;
                    }
                }
            ?>

            <?php if (!empty($grouped)): ?>
                <?php foreach ($grouped as $role => $usersByRole): ?>
                    <section class="admin-section">
                        <div class="section-header">
                            <h2 class="section-title"><?= ucfirst(htmlspecialchars($role)) ?>s (<?= count($usersByRole) ?>)</h2>
                        </div>

                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Join Date</th>
                                        <th>Warnings</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usersByRole as $user): ?>
                                        <tr data-username="<?= strtolower($user->username) ?>" data-email="<?= strtolower($user->email) ?>" data-status="<?= $user->status ?>" data-role="<?= $user->role ?>">
                                            <td>
                                                <span class="user-name"><?= htmlspecialchars($user->username) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($user->email) ?></td>
                                            <td><?= !empty($user->created_at) ? date('M d, Y', strtotime($user->created_at)) : '-' ?></td>
                                            <td>
                                                <?php if (!empty($user->warning_count) && $user->warning_count > 0): ?>
                                                    <span class="warning-badge"><?= $user->warning_count ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user->status === 'active'): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Suspended</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= URLROOT ?>/admin/viewUser/<?= $user->id ?>" class="action-btn btn-view">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php endforeach; ?>
            <?php else: ?>
                <section class="admin-section">
                    <div class="section-header">
                        <h2 class="section-title">All Users (0)</h2>
                    </div>
                    <p class="text-center text-muted">No users found</p>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>

<style>
.filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-box {
    display: flex;
    gap: 0.5rem;
    flex: 1;
    min-width: 300px;
}

.search-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 2px solid var(--primary-blue);
    border-radius: 8px;
    font-size: 0.95rem;
    background: var(--blue-bg);
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: var(--accent-blue);
    box-shadow: 0 0 0 3px rgba(156, 199, 223, 0.2);
}

.filter-options {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-select {
    padding: 0.75rem 1rem;
    border: 2px solid var(--primary-blue);
    border-radius: 8px;
    font-size: 0.95rem;
    background: var(--white-bg);
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: var(--accent-blue);
}

.warning-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background: #fef3c7;
    color: #f59e0b;
    border-radius: 50%;
    font-weight: bold;
    font-size: 0.9rem;
}

.badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-success {
    background: #dcfce7;
    color: #22c55e;
}

.badge-danger {
    background: #fee2e2;
    color: #dc2626;
}

.badge-role {
    background: var(--blue-bg);
    color: var(--primary-blue);
}

@media (max-width: 768px) {
    .filter-bar {
        flex-direction: column;
    }
    
    .search-box {
        width: 100%;
        min-width: auto;
    }
    
    .filter-options {
        width: 100%;
    }
    
    .filter-select {
        flex: 1;
        min-width: 120px;
    }
}
</style>

<script>
function searchUsers() {
    const searchTerm = document.getElementById('user-search').value.toLowerCase();
    const rows = document.querySelectorAll('table.admin-table tbody tr');
    
    rows.forEach(row => {
        const username = row.getAttribute('data-username') || '';
        const email = row.getAttribute('data-email') || '';
        
        if (username.includes(searchTerm) || email.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterUsers() {
    const status = document.getElementById('status-filter').value;
    const role = document.getElementById('role-filter').value;
    const rows = document.querySelectorAll('table.admin-table tbody tr');
    
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        const rowRole = row.getAttribute('data-role');
        
        const statusMatch = status === 'all' || rowStatus === status;
        const roleMatch = role === 'all' || rowRole === role;
        
        if (statusMatch && roleMatch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// sortUsers removed — dashboard should not default to newest-first sorting.
</script>

<script src="<?= URLROOT ?>/assets/js/admin.js" defer></script>
<?php require_once "../app/views/layouts/footer.php"; ?>