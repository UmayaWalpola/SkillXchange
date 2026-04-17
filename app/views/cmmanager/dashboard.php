<?php require_once '../app/views/layouts/header_user.php'; ?>
<?php require_once '../app/views/layouts/commanagersidebar.php'; ?>

<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/global.css">
<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/dashboard.css">
<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/communityadmin.css">

<div class="community-dashboard">
    <div class="community-main">

        <?php if(!empty($_SESSION['success'])): ?>
            <div class="success-message">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if(!empty($_SESSION['error'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h1>Community Dashboard</h1>
                <p>Manage all communities and control their visibility</p>
            </div>
            <a href="<?php echo URLROOT; ?>/communityAdmin/create" class="btn-primary">
                + Create Community
            </a>
        </div>

        <div class="admin-section">
            <div class="section-header">
                <h2 class="section-title">Communities</h2>
                <select class="filter-select" id="statusFilter" onchange="filterCommunities()">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="table-container">
                <table class="data-table" id="communityTable">
                    <thead>
                        <tr>
                            <th>Community Name</th>
                            <th>Created Date</th>
                            <th>Total Members</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="communityTableBody">
                        <?php if(isset($data['communities']) && !empty($data['communities'])): ?>
                            <?php foreach($data['communities'] as $community): ?>
                                <tr class="community-row" data-status="<?= $community->status ?>">
                                    <td style="font-weight:600; color:var(--dark-bg);">
                                        <?= htmlspecialchars($community->name) ?>
                                    </td>
                                    <td style="font-size:13px; color:#9ca3af;">
                                        <?= date('M d, Y', strtotime($community->created_at)) ?>
                                    </td>
                                    <td>
                                        <span class="member-badge">
                                            <?= $community->member_count ?? 0 ?> members
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions-cell">
                                            <a href="<?php echo URLROOT; ?>/communityAdmin/viewCommunity/<?= $community->id ?>" class="btn-view-community">
                                                View Feed
                                            </a>
                                            <?php if($community->status === 'active'): ?>
                                                <button class="btn-deactivate-community" onclick="deactivateCommunity(<?= $community->id ?>)">
                                                    Deactivate
                                                </button>
                                            <?php else: ?>
                                                <button class="btn-activate-community" onclick="activateCommunity(<?= $community->id ?>)">
                                                    Activate
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <h3>No communities yet</h3>
                                        <p>Create your first community to get started</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
const URLROOT = '<?php echo URLROOT; ?>';

function activateCommunity(communityId) {
    if (!confirm('Activate this community? It will become visible to users.')) return;
    fetch(URLROOT + '/communityAdmin/activate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: communityId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification('Community activated successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Failed to activate', 'error');
        }
    })
    .catch(() => showNotification('An error occurred', 'error'));
}

function deactivateCommunity(communityId) {
    if (!confirm('Deactivate this community? Users will not be able to see it.')) return;
    fetch(URLROOT + '/communityAdmin/deactivate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: communityId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification('Community deactivated successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Failed to deactivate', 'error');
        }
    })
    .catch(() => showNotification('An error occurred', 'error'));
}

function filterCommunities() {
    const val = document.getElementById('statusFilter').value;
    document.querySelectorAll('.community-row').forEach(row => {
        row.style.display = (val === 'all' || row.dataset.status === val) ? '' : 'none';
    });
}

function showNotification(message, type) {
    const n = document.createElement('div');
    n.style.cssText = `
        position:fixed; top:20px; right:20px; z-index:10000;
        padding:14px 20px; border-radius:10px; font-size:13.5px; font-weight:600;
        color:white; box-shadow:0 4px 20px rgba(0,0,0,0.15);
        background:${type === 'success' ? '#22c55e' : '#ef4444'};
        animation:slideIn 0.3s ease;
    `;
    n.textContent = message;
    document.body.appendChild(n);
    setTimeout(() => n.remove(), 3000);
}
</script>

<?php require_once '../app/views/layouts/footer_user.php'; ?>