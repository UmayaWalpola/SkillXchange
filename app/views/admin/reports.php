<?php require_once "../app/views/layouts/header_user.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/manager_dashboard.css">

<style>
.reports-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.reports-header {
    margin-bottom: 2rem;
}

.reports-header h1 {
    font-size: 2rem;
    color: var(--dark-bg);
    margin-bottom: 0.5rem;
}

.reports-filters {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    background: var(--white-bg);
    border: 2px solid var(--blue-bg);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--dark-bg);
    margin-bottom: 0.5rem;
}

.filter-group select {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid var(--blue-bg);
    border-radius: 8px;
    font-size: 0.95rem;
    background: var(--white-bg);
    color: var(--dark-bg);
    cursor: pointer;
}

.filter-group select:focus {
    outline: none;
    border-color: var(--primary-blue);
}

.reports-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--white-bg);
    border: 2px solid var(--blue-bg);
    border-radius: 12px;
    padding: 1.5rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(101, 131, 150, 0.15);
}

.stat-card h3 {
    font-size: 2rem;
    color: var(--primary-blue);
    margin: 0 0 0.5rem 0;
}

.stat-card p {
    font-size: 0.9rem;
    color: var(--dark-bg);
    opacity: 0.7;
    margin: 0;
}

.reports-table {
    background: var(--white-bg);
    border: 2px solid var(--blue-bg);
    border-radius: 12px;
    overflow: hidden;
}

.table-header {
    background: var(--blue-bg);
    padding: 1rem 1.5rem;
    border-bottom: 2px solid var(--primary-blue);
}

.table-header h2 {
    font-size: 1.25rem;
    color: var(--dark-bg);
    margin: 0;
}

.reports-list {
    max-height: 600px;
    overflow-y: auto;
}

.report-item {
    padding: 1.5rem;
    border-bottom: 1px solid var(--blue-bg);
    transition: background-color 0.2s ease;
}

.report-item:hover {
    background: var(--blue-bg);
}

.report-item:last-child {
    border-bottom: none;
}

.report-header-row {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.report-meta {
    flex: 1;
}

.report-type-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-right: 0.5rem;
}

.badge-content {
    background: #dbeafe;
    color: #1e40af;
}

.badge-user {
    background: #fce7f3;
    color: #9f1239;
}

.badge-project {
    background: #fef3c7;
    color: #92400e;
}

.report-meta strong {
    color: var(--dark-bg);
    font-weight: 600;
}

.report-meta span {
    color: var(--dark-bg);
    opacity: 0.7;
    font-size: 0.9rem;
}

.report-actions {
    display: flex;
    gap: 0.5rem;
}

.status-select {
    padding: 0.5rem 1rem;
    border: 2px solid var(--blue-bg);
    border-radius: 6px;
    font-size: 0.85rem;
    background: var(--white-bg);
    color: var(--dark-bg);
    cursor: pointer;
}

.status-select:focus {
    outline: none;
    border-color: var(--primary-blue);
}

.btn-update-status {
    padding: 0.5rem 1rem;
    background: var(--primary-blue);
    color: var(--white-bg);
    border: none;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-update-status:hover {
    background: var(--accent-blue);
}

.report-content {
    margin-top: 1rem;
}

.report-reason {
    padding: 0.75rem;
    background: var(--blue-bg);
    border-left: 3px solid var(--primary-blue);
    border-radius: 6px;
    margin-bottom: 0.75rem;
}

.report-reason strong {
    color: var(--primary-blue);
    display: block;
    margin-bottom: 0.25rem;
}

.report-description {
    color: var(--dark-bg);
    opacity: 0.8;
    line-height: 1.6;
}

.no-reports {
    text-align: center;
    padding: 3rem;
    color: var(--dark-bg);
    opacity: 0.5;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-reviewed {
    background: #dbeafe;
    color: #1e40af;
}

.status-resolved {
    background: #d1fae5;
    color: #065f46;
}

.status-dismissed {
    background: #fee2e2;
    color: #991b1b;
}
</style>

<main class="site-main">
    <div class="reports-container">
        <div class="reports-header">
            <h1>📋 Manage Reports</h1>
            <p>Review and manage all reported content and users</p>
        </div>

        <!-- Stats -->
        <div class="reports-stats">
            <div class="stat-card">
                <h3><?= count(array_filter($data['reports'], fn($r) => $r->status === 'pending')) ?></h3>
                <p>Pending Reports</p>
            </div>
            <div class="stat-card">
                <h3><?= count(array_filter($data['reports'], fn($r) => $r->status === 'reviewed')) ?></h3>
                <p>Reviewed Reports</p>
            </div>
            <div class="stat-card">
                <h3><?= count(array_filter($data['reports'], fn($r) => $r->status === 'resolved')) ?></h3>
                <p>Resolved Reports</p>
            </div>
            <div class="stat-card">
                <h3><?= count($data['reports']) ?></h3>
                <p>Total Reports</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="reports-filters">
            <div class="filter-group">
                <label>Report Type</label>
                <select id="filterType" onchange="applyFilters()">
                    <option value="all" <?= $data['currentType'] === 'all' ? 'selected' : '' ?>>All Types</option>
                    <option value="content" <?= $data['currentType'] === 'content' ? 'selected' : '' ?>>Content Reports</option>
                    <option value="user" <?= $data['currentType'] === 'user' ? 'selected' : '' ?>>User Reports</option>
                    <option value="project_member" <?= $data['currentType'] === 'project_member' ? 'selected' : '' ?>>Project Member Reports</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select id="filterStatus" onchange="applyFilters()">
                    <option value="all" <?= $data['currentStatus'] === 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="pending" <?= $data['currentStatus'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="reviewed" <?= $data['currentStatus'] === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                    <option value="resolved" <?= $data['currentStatus'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    <option value="dismissed" <?= $data['currentStatus'] === 'dismissed' ? 'selected' : '' ?>>Dismissed</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Date Range</label>
                <select id="filterDate" onchange="applyFilters()">
                    <option value="all" <?= $data['currentDate'] === 'all' ? 'selected' : '' ?>>All Time</option>
                    <option value="today" <?= $data['currentDate'] === 'today' ? 'selected' : '' ?>>Today</option>
                    <option value="week" <?= $data['currentDate'] === 'week' ? 'selected' : '' ?>>Past Week</option>
                    <option value="month" <?= $data['currentDate'] === 'month' ? 'selected' : '' ?>>Past Month</option>
                </select>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="reports-table">
            <div class="table-header">
                <h2>Reports List (<?= count($data['reports']) ?>)</h2>
            </div>
            <div class="reports-list">
                <?php if (empty($data['reports'])): ?>
                    <div class="no-reports">
                        <p>No reports found matching your filters.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($data['reports'] as $report): ?>
                        <div class="report-item">
                            <div class="report-header-row">
                                <div class="report-meta">
                                    <?php
                                    $badgeClass = '';
                                    $typeLabel = '';
                                    switch ($report->report_type) {
                                        case 'content':
                                            $badgeClass = 'badge-content';
                                            $typeLabel = ucfirst($report->content_type ?? 'Content');
                                            break;
                                        case 'user':
                                            $badgeClass = 'badge-user';
                                            $typeLabel = 'User Profile';
                                            break;
                                        case 'project_member':
                                            $badgeClass = 'badge-project';
                                            $typeLabel = 'Project Member';
                                            break;
                                    }
                                    ?>
                                    <span class="report-type-badge <?= $badgeClass ?>"><?= $typeLabel ?></span>
                                    <span class="status-badge status-<?= $report->status ?>"><?= ucfirst($report->status) ?></span>
                                    <br>
                                    <strong>Reported by:</strong> <span><?= htmlspecialchars($report->reporter_name) ?></span>
                                    <?php if (isset($report->reported_user_name)): ?>
                                        | <strong>Reported:</strong> <span><?= htmlspecialchars($report->reported_user_name) ?></span>
                                    <?php endif; ?>
                                    <?php if (isset($report->project_name)): ?>
                                        | <strong>Project:</strong> <span><?= htmlspecialchars($report->project_name) ?></span>
                                    <?php endif; ?>
                                    <br>
                                    <small>📅 <?= date('M d, Y h:i A', strtotime($report->created_at)) ?></small>
                                </div>
                                <div class="report-actions">
                                    <select class="status-select" data-report-id="<?= $report->id ?>" data-report-type="<?= $report->report_type ?>">
                                        <option value="pending" <?= $report->status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="reviewed" <?= $report->status === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                                        <option value="resolved" <?= $report->status === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                        <option value="dismissed" <?= $report->status === 'dismissed' ? 'selected' : '' ?>>Dismissed</option>
                                    </select>
                                    <button class="btn-update-status" onclick="updateReportStatus(<?= $report->id ?>, '<?= $report->report_type ?>')">Update</button>
                                </div>
                            </div>
                            <div class="report-content">
                                <div class="report-reason">
                                    <strong>Reason:</strong> <?= htmlspecialchars($report->reason) ?>
                                </div>
                                <?php if (!empty($report->description)): ?>
                                    <div class="report-description">
                                        <strong>Description:</strong><br>
                                        <?= nl2br(htmlspecialchars($report->description)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
function applyFilters() {
    const type = document.getElementById('filterType').value;
    const status = document.getElementById('filterStatus').value;
    const date = document.getElementById('filterDate').value;
    
    window.location.href = `<?= URLROOT ?>/report/manage?type=${type}&status=${status}&date=${date}`;
}

function updateReportStatus(reportId, reportType) {
    const selectElement = document.querySelector(`select[data-report-id="${reportId}"]`);
    const newStatus = selectElement.value;
    
    if (!confirm('Are you sure you want to update this report status?')) {
        return;
    }
    
    fetch('<?= URLROOT ?>/report/updateStatus', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `report_id=${reportId}&report_type=${reportType}&status=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the report.');
    });
}
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>
