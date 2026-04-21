<?php
/* 
**** creta form ekata radio bytton ekka add karanna, check n=bix ekak danna  widya
 kiyala dednna, back end eken data eka aran eka org UI eke projects box eka athule
  athule eka pennann widiya completely system eke imolemetn karna widiya kiyala denna bro, 
  hriyta chages wena file eka , code kines ekath denna.php file ekk athule eka danna bro. 
  code check ekedi meka php file ekk athule dala hide karala thiyanna bro. aluthma phopo file
   ekk create karala  denna bro like febk.oho wage ekk hadla denna
🎓 APRIL 20 CODE CHECK - QUICK CHEAT SHEET 
Task: Add Radio Button (Visibility) & Checkbox (Remote Work)
Status: Ready to Copy-Paste
*/

// ============================================================
// 1. DATABASE SQL (Run this in phpMyAdmin SQL tab first)
// ============================================================
/*
ALTER TABLE `projects` 
ADD COLUMN `project_visibility` ENUM('public', 'private') DEFAULT 'public',
ADD COLUMN `allow_remote` TINYINT(1) DEFAULT 0;
*/


// ============================================================
// 2. MODEL CHANGE (app/models/Project.php)
// ============================================================
/*
GO TO: createProject() method

STEP A: Update INSERT query:
$this->db->query("INSERT INTO projects (..., project_visibility, allow_remote) VALUES (..., :p_vis, :a_rem)");

STEP B: Add Binds:
$this->db->bind(':p_vis', $data['project_visibility']);
$this->db->bind(':a_rem', $data['allow_remote']);

[Do the same for updateProject() method]
*/


// ============================================================
// 3. CONTROLLER CHANGE (app/controllers/OrganizationController.php)
// ============================================================
/*
GO TO: createProject() (or editProject) POST handling section

// A. Radio eka ganna (If not selected, default to public)
$p_visibility = $_POST['project_visibility'] ?? 'public';

// B. Checkbox eka ganna (If checked it sends '1', if not it is NOT SET)
$a_remote = isset($_POST['allow_remote']) ? 1 : 0;

// C. Add to $projectData array:
$projectData = [
    ...
    'project_visibility' => $p_visibility,
    'allow_remote'       => $a_remote,
];
*/


// ============================================================
// 4. VIEW CHANGE - FORM (app/views/organization/createProject.php)
// ============================================================
?>

<!-- COPY-PASTE THIS INTO YOUR FORM -->

<!-- RADIO BUTTONS -->
<div class="info-item" style="margin-bottom:15px;">
    <label>Project Visibility (Radio Example)</label>
    <div style="display:flex; gap:20px; padding: 10px 0;">
        <label style="font-weight:normal; cursor:pointer;">
            <input type="radio" name="project_visibility" value="public" <?= ($isEdit && ($project->project_visibility ?? '') == 'public') ? 'checked' : (!$isEdit ? 'checked' : '') ?>> 
            🌐 Public
        </label>
        <label style="font-weight:normal; cursor:pointer;">
            <input type="radio" name="project_visibility" value="private" <?= ($isEdit && ($project->project_visibility ?? '') == 'private') ? 'checked' : '' ?>> 
            🔒 Private
        </label>
    </div>
</div>

<!-- CHECKBOX -->
<div class="info-item" style="margin-bottom:15px;">
    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:normal;">
        <input type="checkbox" name="allow_remote" value="1" <?= ($isEdit && ($project->allow_remote ?? 0) == 1) ? 'checked' : '' ?>> 
        <span>🏠 Allow Remote Work (Checkbox Example)</span>
    </label>
</div>


<?php
// ============================================================
// 5. VIEW CHANGE - DISPLAY ON CARD (app/views/organization/projects.php)
// ============================================================
?>

<!-- FIND the meta section inside foreach loop and PASTE THIS -->
<div style="font-size: 11px; margin-top: 10px; color: #666; display: flex; gap: 10px;">
    <!-- Radio Result -->
    <span>
        <i class="ph ph-shield"></i> <?= ucfirst($project->project_visibility ?? 'public') ?>
    </span>

    <!-- Checkbox Result -->
    <?php if(($project->allow_remote ?? 0) == 1): ?>
        <span style="color: #22c55e; font-weight: 600;">
            <i class="ph ph-check-circle"></i> Remote
        </span>
    <?php endif; ?>
</div>
