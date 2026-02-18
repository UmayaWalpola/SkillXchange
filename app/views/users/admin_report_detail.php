<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/adminsidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/profile.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        <div class="admin-content">
            <!-- Header -->
            <div class="profile-header">
                <div class="profile-info">
                    <div class="profile-avatar">R#<?= $data['report']->id ?></div>
                    <div class="profile-details">
                        <h1>Report #<?= $data['report']->id ?></h1>
                        <p class="profile-bio">Review and take action on user report</p>
                    </div>
                </div>
                <div style="margin-left:auto; align-self:center">
                    <a href="<?= URLROOT ?>/admin/reports" class="btn-secondary">← Back to Reports</a>
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

            <!-- Report Details Card -->
            <div class="report-detail-card">
                <div class="report-status-badge">
                    <span class="badge badge-lg badge-<?= $data['report']->status ?>">
                        <?= ucfirst($data['report']->status) ?>
                    </span>
                </div>

                <div class="report-grid">
                    <!-- Reported User -->
                    <div class="report-section">
                        <h3>Reported User</h3>
                        <div class="user-info-box">
                            <div class="user-avatar-med">
                                <?= strtoupper(substr($data['report']->reported_username, 0, 2)) ?>
                            </div>
                            <div>
                                <div class="user-name"><?= htmlspecialchars($data['report']->reported_username) ?></div>
                                <div class="user-email"><?= htmlspecialchars($data['report']->reported_email) ?></div>
                                <a href="<?= URLROOT ?>/admin/viewUser/<?= $data['report']->reported_user_id ?>" 
                                   class="view-profile-link">View Full Profile →</a>
                            </div>
                        </div>
                    </div>

                    <!-- Reporter -->
                    <div class="report-section">
                        <h3>Reported By</h3>
                        <div class="user-info-box">
                            <div class="user-avatar-med">
                                <?= strtoupper(substr($data['report']->reporter_username, 0, 2)) ?>
                            </div>
                            <div>
                                <div class="user-name"><?= htmlspecialchars($data['report']->reporter_username) ?></div>
                                <div class="user-email"><?= htmlspecialchars($data['report']->reporter_email) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Information -->
                <div class="report-info-section">
                    <div class="info-row">
                        <strong>Submitted:</strong>
                        <span><?= date('F d, Y \a\t g:i A', strtotime($data['report']->created_at)) ?></span>
                    </div>
                    <div class="info-row">
                        <strong>Reason:</strong>
                        <span class="reason-tag"><?= htmlspecialchars($data['report']->reason) ?></span>
                    </div>
                    <?php if ($data['report']->description): ?>
                        <div class="info-row full-width">
                            <strong>Description:</strong>
                            <p class="report-description"><?= nl2br(htmlspecialchars($data['report']->description)) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($data['report']->status === 'resolved' || $data['report']->status === 'dismissed'): ?>
                        <div class="info-row">
                            <strong>Resolved By:</strong>
                            <span><?= htmlspecialchars($data['report']->resolved_by_username ?? 'Unknown') ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Resolved At:</strong>
                            <span><?= date('F d, Y \a\t g:i A', strtotime($data['report']->resolved_at)) ?></span>
                        </div>
                        <?php if ($data['report']->action_taken && $data['report']->action_taken !== 'none'): ?>
                            <div class="info-row">
                                <strong>Action Taken:</strong>
                                <span class="action-tag"><?= ucfirst(str_replace('_', ' ', $data['report']->action_taken)) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($data['report']->admin_notes): ?>
                            <div class="info-row full-width">
                                <strong>Admin Notes:</strong>
                                <p class="admin-notes"><?= nl2br(htmlspecialchars($data['report']->admin_notes)) ?></p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons (only if pending/reviewed) -->
                <?php if ($data['report']->status === 'pending' || $data['report']->status === 'reviewed'): ?>
                    <div class="report-actions">
                        <button class="btn-warning" onclick="openWarningModal(<?= $data['report']->reported_user_id ?>, <?= $data['report']->id ?>)">
                            Send Warning
                        </button>
                        <button class="btn-danger" onclick="openSuspendModal(<?= $data['report']->reported_user_id ?>, <?= $data['report']->id ?>)">
                            Suspend User
                        </button>
                        <button class="btn-success" onclick="openResolveModal(<?= $data['report']->id ?>)">
                            Resolve Report
                        </button>
                        <button class="btn-secondary" onclick="openDismissModal(<?= $data['report']->id ?>)">
                            Dismiss Report
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Previous Warnings -->
            <section class="admin-section">
                <h3 class="section-title">Previous Warnings to This User</h3>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Reason</th>
                                <th>Issued By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['user_warnings'])): ?>
                                <?php foreach ($data['user_warnings'] as $warning): ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($warning->created_at)) ?></td>
                                        <td><span class="badge badge-<?= $warning->warning_type ?>"><?= ucfirst($warning->warning_type) ?></span></td>
                                        <td><?= htmlspecialchars($warning->reason) ?></td>
                                        <td><?= htmlspecialchars($warning->admin_username) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No previous warnings</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Other Reports -->
            <section class="admin-section">
                <h3 class="section-title">Other Reports About This User</h3>
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
                            <?php if (!empty($data['other_reports'])): ?>
                                <?php foreach ($data['other_reports'] as $report): ?>
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
                                    <td colspan="5" class="text-center">No other reports</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
</main>

<!-- Warning Modal -->
<div id="warningModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeWarningModal()">&times;</span>
        <h2>Send Warning & Resolve Report</h2>
        <form method="POST" action="<?= URLROOT ?>/admin/sendWarning">
            <input type="hidden" name="user_id" id="warning_user_id">
            <input type="hidden" name="report_id" id="warning_report_id">
            
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
                       placeholder="Brief reason" value="<?= htmlspecialchars($data['report']->reason) ?>">
            </div>
            
            <div class="form-group">
                <label for="warning_message">Message to User *</label>
                <textarea name="message" id="warning_message" rows="5" required 
                          placeholder="Explain the violation and expected behavior"></textarea>
            </div>
            
            <p class="info-text">This will send a warning to the user and mark the report as resolved.</p>
            
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeWarningModal()">Cancel</button>
                <button type="submit" class="btn-primary">Send Warning & Resolve</button>
            </div>
        </form>
    </div>
</div>

<!-- Suspend Modal -->
<div id="suspendModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeSuspendModal()">&times;</span>
        <h2>Suspend User</h2>
        <form method="POST" action="<?= URLROOT ?>/admin/suspendUser">
            <input type="hidden" name="user_id" id="suspend_user_id">
            
            <div class="form-group">
                <label for="suspension_reason">Reason for Suspension *</label>
                <textarea name="reason" id="suspension_reason" rows="4" required><?= htmlspecialchars($data['report']->reason) ?></textarea>
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
            
            <p class="info-text">Note: You'll need to manually resolve the report after suspension.</p>
            
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeSuspendModal()">Cancel</button>
                <button type="submit" class="btn-danger">Suspend User</button>
            </div>
        </form>
    </div>
</div>

<!-- Resolve Modal -->
<div id="resolveModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeResolveModal()">&times;</span>
        <h2>Resolve Report</h2>
        <form method="POST" action="<?= URLROOT ?>/admin/resolveReport">
            <input type="hidden" name="report_id" id="resolve_report_id">
            
            <div class="form-group">
                <label for="action_taken">Action Taken *</label>
                <select name="action_taken" id="action_taken" required>
                    <option value="none">No action needed</option>
                    <option value="warning">Warning sent separately</option>
                    <option value="suspension">User suspended</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="admin_notes">Admin Notes *</label>
                <textarea name="admin_notes" id="admin_notes" rows="4" required 
                          placeholder="Enter your resolution notes"></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeResolveModal()">Cancel</button>
                <button type="submit" class="btn-success">Resolve Report</button>
            </div>
        </form>
    </div>
</div>

<!-- Dismiss Modal -->
<div id="dismissModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeDismissModal()">&times;</span>
        <h2>Dismiss Report</h2>
        <p>This report will be marked as dismissed (no action taken).</p>
        <form method="POST" action="<?= URLROOT ?>/admin/dismissReport">
            <input type="hidden" name="report_id" id="dismiss_report_id">
            
            <div class="form-group">
                <label for="dismiss_notes">Reason for Dismissal *</label>
                <textarea name="admin_notes" id="dismiss_notes" rows="4" required 
                          placeholder="Why is this report being dismissed?"></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeDismissModal()">Cancel</button>
                <button type="submit" class="btn-warning">Dismiss Report</button>
            </div>
        </form>
    </div>
</div>

<style>
.report-detail-card {
    background: var(--white-bg);
    border: 2px solid var(--blue-bg);
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    position: relative;
}

.report-status-badge {
    position: absolute;
    top: 2rem;
    right: 2rem;
}

.badge-lg {
    padding: 0.6rem 1.2rem;
    font-size: 1rem;
}

.report-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.report-section h3 {
    margin: 0 0 1rem 0;
    color: var(--dark-bg);
    font-size: 1.1rem;
}

.user-info-box {
    display: flex;
    gap: 1rem;
    align-items: center;
    padding: 1rem;
    background: var(--blue-bg);
    border-radius: 10px;
}

.user-avatar-med {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-blue), var(--accent-blue));
    color: var(--white-bg);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    flex-shrink: 0;
}

.user-name {
    font-weight: 600;
    color: var(--dark-bg);
    margin-bottom: 0.3rem;
}

.user-email {
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.view-profile-link {
    color: var(--primary-blue);
    font-size: 0.9rem;
    text-decoration: none;
    font-weight: 600;
}

.view-profile-link:hover {
    color: var(--accent-blue);
}

.report-info-section {
    background: var(--blue-bg);
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.info-row {
    display: flex;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(101, 131, 150, 0.2);
}

.info-row:last-child {
    border-bottom: none;
}

.info-row.full-width {
    flex-direction: column;
}

.info-row strong {
    min-width: 150px;
    color: var(--dark-bg);
}

.reason-tag {
    display: inline-block;
    padding: 0.4rem 1rem;
    background: #fef3c7;
    color: #d97706;
    border-radius: 20px;
    font-weight: 600;
}

.action-tag {
    display: inline-block;
    padding: 0.4rem 1rem;
    background: #dcfce7;
    color: #16a34a;
    border-radius: 20px;
    font-weight: 600;
}

.report-description,
.admin-notes {
    margin: 0.5rem 0 0 0;
    color: #4b5563;
    line-height: 1.6;
}

.report-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    padding-top: 1rem;
    border-top: 2px solid var(--blue-bg);
}

.info-text {
    background: #dbeafe;
    color: #1e40af;
    padding: 0.75rem;
    border-radius: 6px;
    font-size: 0.9rem;
    margin: 1rem 0;
}
</style>

<script>
function openWarningModal(userId, reportId) {
    document.getElementById('warning_user_id').value = userId;
    document.getElementById('warning_report_id').value = reportId;
    document.getElementById('warningModal').style.display = 'block';
}

function closeWarningModal() {
    document.getElementById('warningModal').style.display = 'none';
}

function openSuspendModal(userId, reportId) {
    document.getElementById('suspend_user_id').value = userId;
    document.getElementById('suspendModal').style.display = 'block';
}

function closeSuspendModal() {
    document.getElementById('suspendModal').style.display = 'none';
}

function openResolveModal(reportId) {
    document.getElementById('resolve_report_id').value = reportId;
    document.getElementById('resolveModal').style.display = 'block';
}

function closeResolveModal() {
    document.getElementById('resolveModal').style.display = 'none';
}

function openDismissModal(reportId) {
    document.getElementById('dismiss_report_id').value = reportId;
    document.getElementById('dismissModal').style.display = 'block';
}

function closeDismissModal() {
    document.getElementById('dismissModal').style.display = 'none';
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<script src="<?= URLROOT ?>/assets/js/admin.js" defer></script>
<?php require_once "../app/views/layouts/footer_user.php"; ?>