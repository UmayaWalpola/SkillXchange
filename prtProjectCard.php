<?php
/* 
🎓 APRIL 20 CODE CHECK - CUSTOM FIELD IMPLEMENTATION GUIDE
Task: Add Priority Dropdown OR Checkbox & Display on Organization Project Card
Status: 100% COMPLETE - READY FOR EXAM
*/

// ============================================================
// 1. DATABASE (Run in phpMyAdmin)
// ============================================================
/*
ALTER TABLE `projects` 
ADD COLUMN `priority` ENUM('low', 'medium', 'high') DEFAULT 'medium',
ADD COLUMN `is_featured` TINYINT(1) DEFAULT 0;
*/


// ============================================================
// 2. MODEL (app/models/Project.php)
// ============================================================
/*
GO TO: createProject() AND updateProject() methods

A. Update SQL query string:
"... category, status, priority, is_featured, required_skills ..."
"... :category, :status, :priority, :is_featured, :required_skills ..."

B. Add Bindings:
$this->db->bind(':priority', $data['priority']);
$this->db->bind(':is_featured', $data['is_featured']);
*/


// ============================================================
// 3. CONTROLLER (app/controllers/OrganizationController.php)
// ============================================================
/*
GO TO: createProject() or editProject() -> Inside if($_SERVER['REQUEST_METHOD'] === 'POST')

// Catch Values:
$priority = $_POST['priority'] ?? 'medium';
$is_featured = isset($_POST['is_featured']) ? 1 : 0;

// Add to $projectData array (around Line 190):
'priority'    => $priority,
'is_featured' => $is_featured,
*/


// ============================================================
// 4. VIEW - FORM (app/views/organization/createProject.php)
// ============================================================
/*
LOCATE: Line 109 (End of skills section </div>)
PASTE THIS:
*/
?>
<div class="info-item" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
    <label>Project Priority</label>
    <select name="priority" class="form-select">
        <option value="low" <?= ($isEdit && ($project->priority ?? '') == 'low') ? 'selected' : '' ?>>🟢 Low Priority</option>
        <option value="medium" <?= ($isEdit && ($project->priority ?? '') == 'medium') ? 'selected' : (!$isEdit ? 'selected' : '') ?>>🟡 Medium Priority</option>
        <option value="high" <?= ($isEdit && ($project->priority ?? '') == 'high') ? 'selected' : '' ?>>🔴 High Priority</option>
    </select>
</div>

<div class="info-item">
    <label style="display:flex; align-items:center; gap:10px; font-weight:normal; cursor:pointer;">
        <input type="checkbox" name="is_featured" value="1" <?= ($isEdit && ($project->is_featured ?? 0) == 1) ? 'checked' : '' ?>>
        <span>⭐ Mark as Featured Project</span>
    </label>
</div>


<?php
// ============================================================
// 5. VIEW - ORG CARD (app/views/organization/projects.php)
// ============================================================
/*
LOCATE: Line 79 (Inside <div class="simple-project-meta">)
PASTE THIS:
*/
?>
<div class="card-badges" style="margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap;">
    <!-- Priority Badge -->
    <?php
        $p = $project->priority ?? 'medium';
        $pMap = [
            'low'    => ['c' => '#2e7d32', 'b' => '#e8f5e9', 'l' => 'Low'],
            'medium' => ['c' => '#ef6c00', 'b' => '#fff3e0', 'l' => 'Medium'],
            'high'   => ['c' => '#c62828', 'b' => '#ffebee', 'l' => 'High']
        ];
        $style = $pMap[$p];
    ?>
    <span style="background:<?= $style['b'] ?>; color:<?= $style['c'] ?>; padding:2px 10px; border-radius:12px; font-size:10px; font-weight:bold; border:1px solid <?= $style['c'] ?>44;">
        <?= $style['l'] ?> Priority
    </span>

    <!-- Featured Checkbox Result -->
    <?php if(($project->is_featured ?? 0) == 1): ?>
        <span style="background:#fff9c4; color:#fbc02d; padding:2px 10px; border-radius:12px; font-size:10px; font-weight:bold; border:1px solid #fbc02d44;">
            ⭐ Featured
        </span>
    <?php endif; ?>
</div>
