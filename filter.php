<?php
/* 
🎓 APRIL 20 CODE CHECK - PROJECT FILTERING GUIDE
Task: Filter User's Joined Projects by Status
*/

// ============================================================
// 1. DATABASE (No Change Needed)
// Status column already exists in 'projects' table.
// ============================================================


// ============================================================
// 2. MODEL (app/models/Project.php)
// Update your fetching method to support filtering
// ============================================================
/*
GO TO: getMemberProjects() or similar method

REPLACE / UPDATE with this:
public function getUserProjectsByStatus($userId, $status = 'all') {
    $sql = "SELECT p.*, pm.joined_at, pm.role 
            FROM projects p 
            JOIN project_members pm ON p.id = pm.project_id 
            WHERE pm.user_id = :user_id";

    // Add filter if status is not 'all'
    if ($status !== 'all') {
        $sql .= " AND p.status = :status";
    }

    $sql .= " ORDER BY pm.joined_at DESC";

    $this->db->query($sql);
    $this->db->bind(':user_id', $userId);
    
    if ($status !== 'all') {
        $this->db->bind(':status', $status);
    }

    return $this->db->resultSet();
}
*/


// ============================================================
// 3. CONTROLLER (app/controllers/ProjectController.php)
// Capture the status from the URL
// ============================================================
/*
GO TO: browse() method

UPDATE Logic:
public function browse() {
    $userId = $_SESSION['user_id'];
    
    // Get status from GET request (e.g., browse?member_status=active)
    $statusFilter = $_GET['member_status'] ?? 'all';

    // Call updated model method
    $memberProjects = $this->projectModel->getUserProjectsByStatus($userId, $statusFilter);
    
    $data = [
        'memberProjects' => $memberProjects,
        'selectedFilter' => $statusFilter,
        // ... rest of your data
    ];
    $this->view('projects/browse', $data);
}
*/


// ============================================================
// 4. VIEW - UI (app/views/projects/browse.php)
// Add the Filter Dropdown
// ============================================================
?>

<!-- ADD this dropdown above your 'You're a Member' grid -->
<div class="member-filter-wrap" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
    <label><i class="ph ph-funnel"></i> Filter My Projects:</label>
    <select id="memberStatusFilter" class="disc-pill" style="border:1px solid #ccc; padding:5px 15px; border-radius:20px;">
        <option value="all">All Projects</option>
        <option value="active">Active</option>
        <option value="in-progress">In Progress</option>
        <option value="completed">Completed</option>
    </select>
</div>

<!-- Ensure each card has a data-status attribute for JS filtering -->
<!-- Inside the foreach loop ($memberProjectsList as $proj): -->
<a href="..." class="disc-member-card" data-status="<?= strtolower($proj->status) ?>"> 
    <!-- card content -->
</a>


<?php
// ============================================================
// 5. JAVASCRIPT (Client-Side for Instant Filtering)
// ============================================================
?>
<script>
// Add this to the bottom script block in browse.php
document.getElementById('memberStatusFilter').addEventListener('change', function() {
    const selectedStatus = this.value;
    const memberCards = document.querySelectorAll('.disc-member-card');
    let visibleCount = 0;

    memberCards.forEach(card => {
        const cardStatus = card.getAttribute('data-status');
        
        if (selectedStatus === 'all' || cardStatus === selectedStatus) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // Optional: Show empty state if count is 0
    const emptyState = document.getElementById('memberEmptyState');
    if (visibleCount === 0) {
        // Show an empty message if you have one
    }
});
</script>

<?php
/*
🎯 EXAM TIP:
If they ask for MODEL/CONTROLLER changes, use the Server-side method (Step 2 & 3).
If they just want it to "work quickly" during code check, just add the JS method (Step 4 & 5).
*/
?>
