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
                        <h1>Reports Management</h1>
                        <p class="profile-bio">Review and manage user reports</p>
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

            <!-- Filter Section -->
            <div class="admin-section">
                <div class="filter-bar">
                    <div class="filter-options">
                        <select id="status-filter" class="filter-select" onchange="filterReports()">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="resolved">Resolved</option>
                            <option value="dismissed">Dismissed</option>
                        </select>
                        <select id="sort-filter" class="filter-select" onchange="sortReports()">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Reports Table -->
            <section class="admin-section">
                <div class="section-header">
                    <h2 class="section-title">
                        All Reports 
                        <?php 
                        $pendingCount = count(array_filter($data['reports'], fn($r) => $r->status === 'pending'));
                        if ($pendingCount > 0): 
                        ?>
                            <span class="count-badge"><?= $pendingCount ?> Pending</span>
                        <?php endif; ?>
                    </h2>
                </div>
                
                <div class="table-container">
                    <table class="admin-table" id="reports-table">
                        <thead>
                            <tr>
                                <th>Report ID</th>
                                <th>Reported User</th>
                                <th>Reported By</th>
                                <th>Reason</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['reports'])): ?>
                                <?php foreach ($data['reports'] as $report): ?>
                                    <tr data-status="<?= $report->status ?>" 
                                        data-date="<?= strtotime($report->created_at) ?>"
                                        class="report-row priority-<?= $report->status === 'pending' ? 'high' : 'normal' ?>">
                                        <td><strong>#<?= $report->id ?></strong></td>
                                        <td><?= htmlspecialchars($report->reported_username) ?></td>
                                        <td><?= htmlspecialchars($report->reporter_username) ?></td>
                                        <td>
                                            <span class="reason-badge"><?= htmlspecialchars($report->reason) ?></span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($report->created_at)) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $report->status ?>">
                                                <?= ucfirst($report->status) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= URLROOT ?>/admin/viewReport/<?= $report->id ?>" 
                                               class="action-btn btn-view">Review</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No reports found</td>
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

<style>
.report-row.priority-high {
    border-left: 4px solid #f59e0b;
}

.count-badge {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    background: #fef3c7;
    color: #d97706;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

.status-badge {
    display: inline-block;
    padding: 0.4rem 0.9rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-pending {
    background: #fef3c7;
    color: #d97706;
}

.status-reviewed {
    background: #dbeafe;
    color: #1e40af;
}

.status-resolved {
    background: #dcfce7;
    color: #16a34a;
}

.status-dismissed {
    background: #f3f4f6;
    color: #6b7280;
}

.reason-badge {
    display: inline-block;
    padding: 0.4rem 0.9rem;
    background: var(--blue-bg);
    color: var(--primary-blue);
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.user-avatar-sm {
    width: 35px;
    height: 35px;
    background: linear-gradient(135deg, var(--primary-blue), var(--accent-blue));
    color: var(--white-bg);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    font-weight: bold;
}

.filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.filter-options {
    display: flex;
    gap: 0.5rem;
}
</style>

<script>
function filterReports() {
    const status = document.getElementById('status-filter').value;
    const rows = document.querySelectorAll('#reports-table tbody tr');
    
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        if (status === 'all' || rowStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function sortReports() {
    const sortOrder = document.getElementById('sort-filter').value;
    const tbody = document.querySelector('#reports-table tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const dateA = parseInt(a.getAttribute('data-date'));
        const dateB = parseInt(b.getAttribute('data-date'));
        return sortOrder === 'newest' ? dateB - dateA : dateA - dateB;
    });
    
    rows.forEach(row => tbody.appendChild(row));
}
</script>

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