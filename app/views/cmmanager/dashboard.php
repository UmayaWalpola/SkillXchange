<?php require_once '../app/views/layouts/header_user.php'; ?>
<?php require_once '../app/views/layouts/adminsidebar.php'; ?>

<!-- Link CSS -->
<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/communitydashboard.css">

<div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1 class="dashboard-title">Community Admin Dashboard</h1>
        <a href="<?php echo URLROOT; ?>/community/create" class="btn btn-primary create-btn">
            + Create New Community
        </a>
    </div>

    <!-- Stats Overview -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon">🏘️</div>
            <div class="stat-details">
                <div class="stat-value" id="totalCommunities">0</div>
                <div class="stat-label">Total Communities</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-details">
                <div class="stat-value" id="activeCommunities">0</div>
                <div class="stat-label">Active</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-details">
                <div class="stat-value" id="totalMembers">0</div>
                <div class="stat-label">Total Members</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-details">
                <div class="stat-value" id="totalPosts">0</div>
                <div class="stat-label">Total Posts</div>
            </div>
        </div>
    </div>

    <!-- Community Table -->
    <div class="community-table-container">
        <div class="table-header">
            <h2 class="table-title">Community Management</h2>
            <div class="table-filters">
                <select class="filter-select" id="categoryFilter" onchange="filterCommunities()">
                    <option value="all">All Categories</option>
                    <option value="technology">Technology</option>
                    <option value="education">Education</option>
                    <option value="health">Health</option>
                    <option value="lifestyle">Lifestyle</option>
                    <option value="business">Business</option>
                </select>
                
                <select class="filter-select" id="statusFilter" onchange="filterCommunities()">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <table class="community-table">
            <thead>
                <tr>
                    <th>Community Details</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Members</th>
                    <th>Posts</th>
                    <th>Created Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="communityTableBody"></tbody>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeDeleteModal()">&times;</span>
        <h2>Delete Community</h2>
        <p>Are you sure you want to delete this community? This action cannot be undone.</p>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn btn-delete" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<!-- Link JavaScript -->
<script>
    // Pass URLROOT to JavaScript
    const URLROOT = '<?php echo URLROOT; ?>';
</script>
<script src="<?php echo URLROOT; ?>/assets/js/communitydashboard.js"></script>

<?php require_once '../app/views/layouts/footer_user.php'; ?>