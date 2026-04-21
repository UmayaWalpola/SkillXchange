<?php
/* 
🎓 APRIL 20 CODE CHECK - PROJECT APPLICATIONS RATING FILTER
Task: Filter Applications by Rating Ranges (0-2, 2-4, 4-5)
Status: Complete Guide with View, Model, and Controller logic.
*/

// ==========================================
// 1. MODEL (app/models/Project.php)
// GET Applications with Average Rating filter
// ==========================================
/*
LOCATE: getPendingApplications() method or add this New Method:

public function getApplicationsByRating($orgId, $range = 'all') {
    $sql = "SELECT pa.*, u.name as user_name, u.profile_image, p.name as project_name,
            (SELECT AVG(rating) FROM user_feedback WHERE user_id = pa.user_id) as avg_rating
            FROM project_applications pa
            JOIN users u ON pa.user_id = u.id
            JOIN projects p ON pa.project_id = p.id
            WHERE p.organization_id = :org_id AND pa.status = 'pending'";

    // Handle Ranges using HAVING (because avg_rating is a calculated field)
    if ($range == '0-2') {
        $sql .= " HAVING avg_rating >= 0 AND avg_rating < 2";
    } elseif ($range == '2-4') {
        $sql .= " HAVING avg_rating >= 2 AND avg_rating < 4";
    } elseif ($range == '4-5') {
        $sql .= " HAVING avg_rating >= 4 AND avg_rating <= 5";
    }

    $this->db->query($sql);
    $this->db->bind(':org_id', $orgId);
    return $this->db->resultSet();
}
*/


// ==========================================
// 2. CONTROLLER (app/controllers/OrganizationController.php)
// Handle the Filter Request
// ==========================================
/*
GO TO: applications() method

UPDATE Logic:
public function applications() {
    $orgId = $_SESSION['user_id'];
    
    // Get range from URL (e.g., ?rating=2-4)
    $ratingRange = $_GET['rating'] ?? 'all';

    // Call the model with filter
    $pendingApps = $this->projectModel->getApplicationsByRating($orgId, $ratingRange);
    
    $data = [
        'pending' => $pendingApps,
        'current_rating' => $ratingRange
    ];
    
    $this->view('organization/applications', $data);
}
*/


// ==========================================
// 3. VIEW - UI (app/views/organization/applications.php)
// Add Filter Dropdown
// ==========================================
?>

<!-- PLACE THIS ABOVE YOUR PENDING APPLICATIONS GRID -->
<div class="rating-filter" style="margin: 20px 0; display: flex; align-items: center; gap: 15px;">
    <label><i class="ph ph-star-half"></i> Filter by User Rating:</label>
    <select id="ratingDropdown" onchange="filterByRating(this.value)" style="padding: 8px 15px; border-radius: 8px; border: 1px solid #ddd;">
        <option value="all" <?= ($data['current_rating'] == 'all') ? 'selected' : '' ?>>All Ratings</option>
        <option value="0-2" <?= ($data['current_rating'] == '0-2') ? 'selected' : '' ?>>⭐ 0 - 2 (Low)</option>
        <option value="2-4" <?= ($data['current_rating'] == '2-4') ? 'selected' : '' ?>>⭐⭐ 2 - 4 (Medium)</option>
        <option value="4-5" <?= ($data['current_rating'] == '4-5') ? 'selected' : '' ?>>⭐⭐⭐ 4 - 5 (High)</option>
    </select>
</div>

<!-- Each Card Example (Make sure you display the rating) -->
<div class="app-card" data-rating="<?= $app->avg_rating ?>">
    <div class="rating-box">
        <i class="ph ph-star"></i> <?= number_format($app->avg_rating, 2) ?>
    </div>
    <!-- rest of info -->
</div>


<?php
// ==========================================
// 4. JAVASCRIPT (Client-Side Filter for speed)
// ==========================================
?>
<script>
function filterByRating(range) {
    // Standard approach: Reload page with GET parameter
    window.location.href = '<?= URLROOT ?>/organization/applications?rating=' + range;
}

/* 
OR if you want instant JS filtering (No reload):
*/
function jsFilterRating(range) {
    const cards = document.querySelectorAll('.app-card');

    cards.forEach(card => {
        const rating = parseFloat(card.getAttribute('data-rating') || 0);
        let show = false;

        if (range === 'all') show = true;
        else if (range === '0-2' && rating >= 0 && rating < 2) show = true;
        else if (range === '2-4' && rating >= 2 && rating < 4) show = true;
        else if (range === '4-5' && rating >= 4 && rating <= 5) show = true;

        card.style.display = show ? 'block' : 'none';
    });
}
</script>

<?php
/*
🎯 TEACHER'S TIP:
Explain the SQL 'HAVING' clause:
"Since 'avg_rating' is a calculated column (not a real table column), we cannot use it in a WHERE clause. 
We must use HAVING instead to filter the results after aggregation."
*/
?>
