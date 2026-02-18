<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/adminsidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/admin.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        <div class="admin-content">
            <!-- Header -->
            <div class="admin-header">
                <div>
                    <h1>User Details: <?= htmlspecialchars($data['user']->username) ?></h1>
                    <p class="admin-subtitle">View and manage user account</p>
                </div>
                <a href="<?= URLROOT ?>/admin/users" class="btn-secondary">← Back to Users</a>
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

            <!-- User Info Card -->
            <div class="user-detail-card">
                <div class="user-detail-header">
                    <div class="user-avatar-large">
                        <?= strtoupper(substr($data['user']->username, 0, 2)) ?>
                    </div>
                    <div class="user-detail-info">
                        <h2><?= htmlspecialchars($data['user']->username) ?></h2>
                        <p><?= htmlspecialchars($data['user']->email) ?></p>
                        <div class="user-badges-row">
                            <span class="badge <?= $data['user']->status === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                <?= ucfirst($data['user']->status) ?>
                            </span>
                            <span class="badge badge-info"><?= ucfirst($data['user']->role) ?></span>
                            <?php if ($data['user']->warning_count > 0): ?>
                                <span class="badge badge-warning"><?= $data['user']->warning_count ?> Warning(s)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="user-detail-grid">
                    <div class="detail-item">
                        <strong>User ID:</strong>
                        <span><?= $data['user']->id ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Join Date:</strong>
                        <span><?= date('F d, Y', strtotime($data['user']->created_at)) ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Profile Completed:</strong>
                        <span><?= $data['user']->profile_completed ? 'Yes' : 'No' ?></span>
                    </div>
                    <?php if ($data['user']->bio): ?>
                        <div class="detail-item full-width">
                            <strong>Bio:</strong>
                            <span><?= htmlspecialchars($data['user']->bio) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($data['user']->status === 'suspended'): ?>
                    <div class="suspension-info">
                        <h4>⚠️ Suspension Details</h4>
                        <p><strong>Suspended on:</strong> <?= date('F d, Y g:i A', strtotime($data['user']->suspended_at)) ?></p>
                        <p><strong>Reason:</strong> <?= htmlspecialchars($data['user']->suspension_reason) ?></p>
                        <?php if ($data['user']->suspension_expires_at): ?>
                            <p><strong>Expires:</strong> <?= date('F d, Y g:i A', strtotime($data['user']->suspension_expires_at)) ?></p>
                        <?php else: ?>
                            <p><strong>Duration:</strong> Permanent</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="user-actions">
                    <?php if ($data['user']->status === 'active'): ?>
                        <button class="btn-warning" onclick="openSuspendModal(<?= $data['user']->id ?>)">
                            Suspend User
                        </button>
                    <?php else: ?>
                        <form method="POST" action="<?= URLROOT ?>/admin/activateUser" style="display: inline;">
                            <input type="hidden" name="user_id" value="<?= $data['user']->id ?>">
                            <button type="submit" class="btn-success" onclick="return confirm('Activate this user?')">
                                Activate User
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <button class="btn-primary" onclick="openWarningModal(<?= $data['user']->id ?>)">
                        Send Warning
                    </button>
                    
                    <button class="btn-danger" onclick="openDeleteModal(<?= $data['user']->id ?>, '<?= htmlspecialchars($data['user']->username) ?>')">
                        Ban Permanently
                    </button>
                </div>
            </div>

            <!-- User Skills -->
            <section class="admin-section">
                <h3 class="section-title">Skills</h3>
                <div class="skills-display">
                    <?php if (!empty($data['skills'])): ?>
                        <?php foreach ($data['skills'] as $skill): ?>
                            <div class="skill-item">
                                <span class="skill-name"><?= htmlspecialchars($skill->skill_name) ?></span>
                                <span class="skill-type <?= $skill->skill_type ?>"><?= ucfirst($skill->skill_type) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No skills listed</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- User Warnings -->
            <section class="admin-section">
                <h3 class="section-title">Warning History</h3>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Reason</th>
                                <th>Message</th>
                                <th>Issued By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['warnings'])): ?>
                                <?php foreach ($data['warnings'] as $warning): ?>
                                    <tr>
                                        <td><?= date('M d, Y g:i A', strtotime($warning->created_at)) ?></td>
                                        <td><span class="badge badge-<?= $warning->warning_type ?>"><?= ucfirst($warning->warning_type) ?></span></td>
                                        <td><?= htmlspecialchars($warning->reason) ?></td>
                                        <td><?= htmlspecialchars($warning->message) ?></td>
                                        <td><?= htmlspecialchars($warning->admin_username) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No warnings issued</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Reports Against User -->
            <section class="admin-section">
                <h3 class="section-title">Reports Against This User</h3>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reported By</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['reports'])): ?>
                                <?php foreach ($data['reports'] as $report): ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($report->created_at)) ?></td>
                                        <td><?= htmlspecialchars($report->reporter_username) ?></td>
                                        <td><?= htmlspecialchars($report->reason) ?></td>
                                        <td><span class="badge badge-<?= $report->status ?>"><?= ucfirst($report->status) ?></span></td>
                                        <td>
                                            <a href="<?= URLROOT ?>/admin/viewReport/<?= $report->id ?>" class="action-btn btn-view">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No reports</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Recent Activity -->
            <section class="admin-section">
                <h3 class="section-title">Recent Activity</h3>
                <div class="activity-list">
                    <?php if (!empty($data['activities'])): ?>
                        <?php foreach ($data['activities'] as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon">📌</div>
                                <div class="activity-content">
                                    <div class="activity-type"><?= ucfirst(str_replace('_', ' ', $activity->activity_type)) ?></div>
                                    <?php if ($activity->description): ?>
                                        <div class="activity-desc"><?= htmlspecialchars($activity->description) ?></div>
                                    <?php endif; ?>
                                    <div class="activity-time"><?= date('M d, Y g:i A', strtotime($activity->created_at)) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No recent activity</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>
</main>

<!-- Suspend User Modal -->
<div id="suspendModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeSuspendModal()">&times;</span>
        <h2>Suspend User</h2>
        <form method="POST" action="<?= URLROOT ?>/admin/suspendUser">
            <input type="hidden" name="user_id" id="suspend_user_id">
            
            <div class="form-group">
                <label for="suspension_reason">Reason for Suspension *</label>
                <textarea name="reason" id="suspension_reason" rows="4" required 
                          placeholder="Enter detailed reason for suspension"></textarea>
            </div>
            
            <div class="form-group">
                <label for="suspension_duration">Duration *</label>
                <select name="duration" id="suspension_duration" required>
                    <option value="7days">7 Days</option>
                    <option value="30days">30 Days</option>
                    <option value="90days">90 Days</option>
                    <option value="permanent">Permanent</option>
                </select>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeSuspendModal()">Cancel</button>
                <button type="submit" class="btn-warning">Suspend User</button>
            </div>
        </form>
    </div>
</div>

<!-- Warning Modal -->
<div id="warningModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeWarningModal()">&times;</span>
        <h2>Send Warning</h2>
        <form method="POST" action="<?= URLROOT ?>/admin/sendWarning">
            <input type="hidden" name="user_id" id="warning_user_id">
            
            <div class="form-group">
                <label for="warning_type">Warning Type *</label>
                <select name="warning_type" id="warning_type" required>
                    <option value="minor">Minor - First offense</option>
                    <option value="moderate">Moderate - Repeated offense</option>
                    <option value="severe">Severe - Serious violation</option>
                    <option value="final">Final - Last warning before ban</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="warning_reason">Reason *</label>
                <input type="text" name="reason" id="warning_reason" required 
                       placeholder="Brief reason (e.g., Inappropriate behavior)">
            </div>
            
            <div class="form-group">
                <label for="warning_message">Message to User *</label>
                <textarea name="message" id="warning_message" rows="5" required 
                          placeholder="Detailed message explaining the warning"></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeWarningModal()">Cancel</button>
                <button type="submit" class="btn-primary">Send Warning</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete User Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeDeleteModal()">&times;</span>
        <h2>⚠️ Permanently Ban User</h2>
        <p class="warning-text">This action cannot be undone. The user and all their data will be permanently removed.</p>
        <form method="POST" action="<?= URLROOT ?>/admin/deleteUser">
            <input type="hidden" name="user_id" id="delete_user_id">
            
            <div class="form-group">
                <label>Confirm username: <strong id="delete_username"></strong></label>
            </div>
            
            <div class="form-group">
                <label for="delete_reason">Reason for Permanent Ban *</label>
                <textarea name="reason" id="delete_reason" rows="4" required 
                          placeholder="Enter detailed reason for permanent ban"></textarea>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="confirm_delete" required>
                    I understand this action is permanent and cannot be undone
                </label>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-danger">Permanently Ban User</button>
            </div>
        </form>
    </div>
</div>

<style>
.user-detail-card {
    background: var(--white-bg);
    border: 2px solid var(--blue-bg);
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.user-detail-header {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid var(--blue-bg);
}

.user-avatar-large {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--primary-blue), var(--accent-blue));
    color: var(--white-bg);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: bold;
    flex-shrink: 0;
}

.user-detail-info h2 {
    margin: 0 0 0.5rem 0;
    color: var(--dark-bg);
}

.user-detail-info p {
    margin: 0 0 1rem 0;
    color: #6b7280;
}

.user-badges-row {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.user-detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-item strong {
    color: var(--dark-bg);
    font-size: 0.9rem;
}

.detail-item span {
    color: #6b7280;
}

.suspension-info {
    background: #fef3c7;
    border: 2px solid #f59e0b;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.suspension-info h4 {
    margin: 0 0 1rem 0;
    color: #92400e;
}

.suspension-info p {
    margin: 0.5rem 0;
    color: #78350f;
}

.user-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.skills-display {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.skill-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--blue-bg);
    border-radius: 25px;
}

.skill-name {
    font-weight: 600;
    color: var(--dark-bg);
}

.skill-type {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.skill-type.teach {
    background: #dcfce7;
    color: #166534;
}

.skill-type.learn {
    background: #dbeafe;
    color: #1e40af;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: var(--blue-bg);
    border-radius: 10px;
}

.activity-icon {
    font-size: 1.5rem;
}

.activity-content {
    flex: 1;
}

.activity-type {
    font-weight: 600;
    color: var(--dark-bg);
    margin-bottom: 0.3rem;
}

.activity-desc {
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 0.3rem;
}

.activity-time {
    font-size: 0.85rem;
    color: #9ca3af;
}

.badge-minor { background: #dbeafe; color: #1e40af; }
.badge-moderate { background: #fef3c7; color: #d97706; }
.badge-severe { background: #fee2e2; color: #dc2626; }
.badge-final { background: #1f2937; color: #ffffff; }
.badge-pending { background: #fef3c7; color: #d97706; }
.badge-reviewed { background: #dbeafe; color: #1e40af; }
.badge-resolved { background: #dcfce7; color: #16a34a; }
.badge-dismissed { background: #f3f4f6; color: #6b7280; }

.warning-text {
    background: #fee2e2;
    color: #991b1b;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}
</style>

<script>
function openSuspendModal(userId) {
    document.getElementById('suspend_user_id').value = userId;
    document.getElementById('suspendModal').style.display = 'block';
}

function closeSuspendModal() {
    document.getElementById('suspendModal').style.display = 'none';
}

function openWarningModal(userId) {
    document.getElementById('warning_user_id').value = userId;
    document.getElementById('warningModal').style.display = 'block';
}

function closeWarningModal() {
    document.getElementById('warningModal').style.display = 'none';
}

function openDeleteModal(userId, username) {
    document.getElementById('delete_user_id').value = userId;
    document.getElementById('delete_username').textContent = username;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<script src="<?= URLROOT ?>/assets/js/admin.js" defer></script>
<?php require_once "../app/views/layouts/footer.php"; ?>