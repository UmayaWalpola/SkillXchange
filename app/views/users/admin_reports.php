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
                <h1>Reports Management</h1>
                <p class="admin-subtitle">Review and manage user reports and violations</p>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-info">
                        <span class="stat-number">24</span>
                        <span class="stat-label">Pending Reports</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <span class="stat-number">156</span>
                        <span class="stat-label">Resolved</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🚫</div>
                    <div class="stat-info">
                        <span class="stat-number">8</span>
                        <span class="stat-label">Suspended Users</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <span class="stat-number">87%</span>
                        <span class="stat-label">Resolution Rate</span>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="admin-section">
                <div class="filter-bar">
                    <div class="search-box">
                        <input type="text" placeholder="Search reports..." class="search-input">
                        <button class="btn-primary">Search</button>
                    </div>
                    <div class="filter-options">
                        <select class="filter-select">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="reviewing">Under Review</option>
                            <option value="resolved">Resolved</option>
                        </select>
                        <select class="filter-select">
                            <option value="">All Types</option>
                            <option value="harassment">Harassment</option>
                            <option value="spam">Spam</option>
                            <option value="inappropriate">Inappropriate Content</option>
                            <option value="other">Other</option>
                        </select>
                        <select class="filter-select">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="priority">Priority</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Reports Table -->
            <section class="admin-section">
                <div class="section-header">
                    <h2 class="section-title">All Reports (24 Pending)</h2>
                    <button class="btn-primary">Export Reports</button>
                </div>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Report ID</th>
                                <th>Reported User</th>
                                <th>Reported By</th>
                                <th>Reason</th>
                                <th>Date</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tbody>
    <?php if (empty($data['reports'])): ?>
        <tr><td colspan="8" style="text-align:center;">No reports found.</td></tr>
    <?php else: ?>
      <?php foreach ($data['reports'] as $report): ?>
            <tr class="report-row priority-medium">
                <td><strong>#<?= $report->id ?></strong> <small>(<?= ucfirst($report->type) ?>)</small></td>
                <td>
                    <div class="user-cell">
                        <span><?= htmlspecialchars($report->reported_name) ?></span>
                    </div>
                </td>
                <td>
                    <div class="user-cell">
                        <span><?= htmlspecialchars($report->reporter_name) ?></span>
                    </div>
                </td>
                <td>
                    <span class="reason-badge other"><?= htmlspecialchars($report->reason) ?></span>
                    <?php if(!empty($report->content_preview)): ?>
                        <br><small style="color:#666;">"<?= substr(htmlspecialchars($report->content_preview), 0, 50) ?>..."</small>
                    <?php endif; ?>
                </td>
                <td><?= date('M d, Y', strtotime($report->created_at)) ?></td>
                <td><span class="priority-badge medium">Medium</span></td>
                <td>
                    <span class="status-badge <?= strtolower($report->status) ?>">
                        <?= ucfirst($report->status) ?>
                    </span>
                </td>
                <td>
                    <button class="action-btn btn-resolve" 
                            onclick="updateReportStatus(<?= $report->id ?>, '<?= $report->type ?>', 'resolved')">
                        Resolve
                    </button>

                    <?php if ($report->type === 'user'): ?>
                        <button class="action-btn btn-view" 
                                style="background-color: #f59e0b; color: white;"
                                onclick="updateReportStatus(<?= $report->id ?>, 'user', 'warned')">
                            Warn
                        </button>
                        <button class="action-btn" 
                                style="background-color: #ef4444; color: white;"
                                onclick="if(confirm('Ban this user?')) updateReportStatus(<?= $report->id ?>, 'user', 'banned')">
                            Ban
                        </button>
                    <?php elseif ($report->type === 'content'): ?>
                        <button class="action-btn" 
                                style="background-color: #dc2626; color: white;"
                                onclick="if(confirm('Delete this content permanently?')) updateReportStatus(<?= $report->id ?>, 'content', 'content_removed')">
                            Delete Content
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="pagination">
                    <button class="pagination-btn" disabled>Previous</button>
                    <div class="pagination-numbers">
                        <button class="pagination-number active">1</button>
                        <button class="pagination-number">2</button>
                        <button class="pagination-number">3</button>
                    </div>
                    <button class="pagination-btn">Next</button>
                </div>
            </section>

            <!-- Quick Actions Panel -->
            <section class="admin-section">
                <div class="section-header">
                    <h2 class="section-title">Quick Actions</h2>
                </div>
                
                <div class="quick-actions-grid">
                    <div class="quick-action-card">
                        <div class="quick-action-icon">📋</div>
                        <h3>Bulk Review</h3>
                        <p>Review multiple pending reports at once</p>
                        <button class="btn-primary">Start Review</button>
                    </div>
                    
                    <div class="quick-action-card">
                        <div class="quick-action-icon">📊</div>
                        <h3>Generate Report</h3>
                        <p>Create detailed analytics report</p>
                        <button class="btn-primary">Generate</button>
                    </div>
                    
                    <div class="quick-action-card">
                        <div class="quick-action-icon">⚙️</div>
                        <h3>Report Settings</h3>
                        <p>Configure report rules and auto-actions</p>
                        <button class="btn-primary">Configure</button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
</main>

<style>
/* Report specific styles */
.report-row {
    transition: all 0.3s ease;
}

.report-row.priority-high {
    border-left: 4px solid #ef4444;
}

.report-row.priority-medium {
    border-left: 4px solid #f59e0b;
}

.report-row.priority-low {
    border-left: 4px solid #3b82f6;
}

.reason-badge {
    display: inline-block;
    padding: 0.4rem 0.9rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.reason-badge.harassment {
    background: #fee2e2;
    color: #dc2626;
}

.reason-badge.spam {
    background: #fef3c7;
    color: #d97706;
}

.reason-badge.inappropriate {
    background: #fce7f3;
    color: #be123c;
}

.reason-badge.fake {
    background: #dbeafe;
    color: #1e40af;
}

.reason-badge.other {
    background: #e0e7ff;
    color: #4338ca;
}

.priority-badge {
    display: inline-block;
    padding: 0.3rem 0.7rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.priority-badge.high {
    background: #fee2e2;
    color: #dc2626;
}

.priority-badge.medium {
    background: #fef3c7;
    color: #d97706;
}

.priority-badge.low {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge {
    display: inline-block;
    padding: 0.4rem 0.9rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-badge.pending {
    background: #fef3c7;
    color: #d97706;
}

.status-badge.reviewing {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge.resolved {
    background: #dcfce7;
    color: #16a34a;
}

/* Quick Actions */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.quick-action-card {
    background: var(--blue-bg);
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.quick-action-card:hover {
    transform: translateY(-5px);
    border-color: var(--accent-blue);
    box-shadow: 0 10px 25px rgba(101, 131, 150, 0.15);
}

.quick-action-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.quick-action-card h3 {
    font-size: 1.3rem;
    color: var(--dark-bg);
    margin-bottom: 0.5rem;
}

.quick-action-card p {
    color: var(--dark-bg);
    opacity: 0.7;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

.quick-action-card .btn-primary {
    width: 100%;
}

/* Filter Bar (reuse from users list) */
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

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 2px solid var(--blue-bg);
}

.pagination-btn {
    padding: 0.5rem 1rem;
    background: var(--primary-blue);
    color: var(--white-bg);
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.pagination-btn:hover:not(:disabled) {
    background: var(--accent-blue);
    color: var(--dark-bg);
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-numbers {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.pagination-number {
    width: 40px;
    height: 40px;
    border: 2px solid var(--primary-blue);
    background: var(--white-bg);
    color: var(--dark-bg);
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.pagination-number:hover {
    background: var(--blue-bg);
}

.pagination-number.active {
    background: var(--primary-blue);
    color: var(--white-bg);
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
    
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="<?= URLROOT ?>/assets/js/admin.js" defer></script>


<script>
function updateReportStatus(id, type, status) {
    // 1. Create the data to send
    const formData = new FormData();
    formData.append('report_id', id);
    formData.append('report_type', type); // 'user', 'content', etc.
    formData.append('status', status);    // 'banned', 'warned', 'resolved'

    // 2. Send it to the Controller using AJAX (fetch)
    fetch('<?= URLROOT ?>/ReportController/updateStatus', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // 3. If successful, reload the page to see the changes
            alert(data.message);
            location.reload(); 
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>