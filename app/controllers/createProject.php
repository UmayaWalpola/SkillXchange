<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="projects-container">

        <!-- Detect Create or Edit -->
        <?php
            $isEdit = isset($project) && !empty($project);
            $title = $isEdit ? "Edit Project" : "Create Project";
            $action = $isEdit 
                ? URLROOT . "/organization/editProject/" . $project->id 
                : URLROOT . "/organization/createProject";
            $availableSkills = $availableSkills ?? [];
            $projectCategory = $isEdit ? ($project->category ?? 'other') : 'other';
            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $existingStartDate = $isEdit ? (string)($project->start_date ?? '') : '';
            $existingEndDate   = $isEdit ? (string)($project->end_date ?? '') : '';
            // For new projects require future dates (from tomorrow). For edits, preserve existing earlier dates.
            $startDateMin = ($existingStartDate !== '' && $existingStartDate < $tomorrow) ? $existingStartDate : $tomorrow;
            $endDateMin   = ($existingEndDate !== '' && $existingEndDate < $tomorrow) ? $existingEndDate : $tomorrow;
            $selectedSkills = $isEdit
                ? array_values(array_filter(array_map('trim', explode(',', (string)($project->required_skills ?? '')))))
                : [];

            // Project type
            $projectType = $isEdit ? (string)($project->project_type ?? 'commercial') : 'commercial';
            if (!in_array($projectType, ['commercial', 'freelance'], true)) {
                $projectType = 'commercial';
            }

            // Project priority (UI can be toggled via CSS; backend still supports it with a default)
            $priorityValue = $isEdit ? strtolower((string)($project->priority ?? 'medium')) : 'medium';
            if (!in_array($priorityValue, ['low', 'medium', 'high'], true)) {
                $priorityValue = 'medium';
            }
        ?>

        <h1><?= $title ?></h1>

        <!-- Error messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $msg): ?>
                        <li><?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= $action ?>" method="POST" class="project-form">
            <input type="hidden" name="category" value="<?= htmlspecialchars($projectCategory) ?>">

            <!-- Card-style header with category icon and inline status -->
            <div id="projectCardHeader" class="project-card-header <?= htmlspecialchars($projectCategory) ?>">
                <div class="header-left">
                    <div id="projIcon" class="proj-icon <?= htmlspecialchars($projectCategory) ?>"><i class="ph ph-sparkle"></i></div>
                    <div>
                        <div class="proj-title"><?= $title ?></div>
                        <div class="proj-sub">Fill in the details below to create your project</div>
                    </div>
                </div>
                <div class="header-right">
                    <?php if ($isEdit): ?>
                        <div class="inline-status">
                            <label style="font-size:12px; display:block; margin-bottom:4px;">Status</label>
                            <select name="status">
                                <?php 
                                    $statuses = ['in-progress' => 'In Progress','active' => 'Active','completed' => 'Completed','cancelled' => 'Cancelled'];
                                    foreach ($statuses as $val => $label):
                                ?>
                                    <option value="<?= $val ?>" <?= $project->status == $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="status" value="active">
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-section">
                <h2 class="section-title">Project Details</h2>
                <div class="section-content">
                    <div class="info-item full-width">
                        <label>Project Name</label>
                        <input type="text" name="name" value="<?= $isEdit ? htmlspecialchars($project->name) : '' ?>" required>
                    </div>

                    <div class="info-item full-width">
                        <label>Project Description</label>
                        <textarea name="description" rows="5" required><?= $isEdit ? htmlspecialchars($project->description) : '' ?></textarea>
                    </div>

                    <div class="info-item">
                        <label>Skills Needed</label>
                        <select id="requiredSkillsSelect" class="form-select">
                            <option value="">Select a skill</option>
                            <?php foreach ($availableSkills as $skill): ?>
                                <option value="<?= htmlspecialchars($skill->skill_name ?? '') ?>">
                                    <?= htmlspecialchars($skill->skill_name ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input id="requiredSkillsInput" type="hidden" name="required_skills" value="<?= htmlspecialchars(implode(', ', $selectedSkills)) ?>" required>
                        <div id="selectedSkillsList" class="selected-skills-list">
                            <?php foreach ($selectedSkills as $skillName): ?>
                                <span class="selected-skill-chip" data-skill="<?= htmlspecialchars($skillName) ?>">
                                    <span><?= htmlspecialchars($skillName) ?></span>
                                    <button type="button" class="selected-skill-remove" aria-label="Remove skill">&times;</button>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <small id="skillsHint" style="display:block;margin-top:6px;color:#355a72;font-size:13px;line-height:1.45;">
                            Pick from the platform skill list. Selected skills will be added below.                       </small>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <h2 class="section-title">Project Settings</h2>
                <div class="section-content settings-grid">
                    <div class="small-card">
                        <label>Project Type</label>
                        <select name="project_type" class="form-select" required>
                            <option value="commercial" <?= $projectType === 'commercial' ? 'selected' : '' ?>>Commercial</option>
                            <option value="freelance" <?= $projectType === 'freelance' ? 'selected' : '' ?>>Freelance</option>
                        </select>
                    </div>

                    <!--<div class="small-card">
                        <label>Max Members</label>
                        <input type="number" name="max_members" min="1" value="<?= $isEdit ? $project->max_members : 5 ?>" required>
                    </div>   -->

                    <!-- Priority (hidden by default via CSS: .project-priority-field) -->
                    <div class="small-card project-priority-field">
                        <label>Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low" <?= $priorityValue === 'low' ? 'selected' : '' ?>>Low</option>
                            <option value="medium" <?= $priorityValue === 'medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="high" <?= $priorityValue === 'high' ? 'selected' : '' ?>>High</option>
                        </select>
                    </div>

                    <div class="small-card">
                        <label>Start Date</label>
                        <input class="info-input" type="date" name="start_date" value="<?= $isEdit ? $project->start_date : '' ?>" min="<?= htmlspecialchars($startDateMin) ?>">
                    </div>

                    <div class="small-card">
                        <label>End Date</label>
                        <input class="info-input" type="date" name="end_date" value="<?= $isEdit ? $project->end_date : '' ?>" min="<?= htmlspecialchars($endDateMin) ?>">
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:20px;">
                <button type="submit" class="create-btn"><?= $isEdit ? "Update Project" : "Create Project" ?></button>
            </div>

        </form>
    </div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const skillSelect = document.getElementById('requiredSkillsSelect');
    const hiddenInput = document.getElementById('requiredSkillsInput');
    const selectedSkillsList = document.getElementById('selectedSkillsList');
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    const selectedSkills = new Set(
        (hiddenInput.value || '')
            .split(',')
            .map(skill => skill.trim())
            .filter(Boolean)
    );

    function syncHiddenInput() {
        hiddenInput.value = Array.from(selectedSkills).join(', ');
    }

    function renderSelectedSkills() {
        selectedSkillsList.innerHTML = '';

        selectedSkills.forEach(function(skill) {
            const chip = document.createElement('span');
            chip.className = 'selected-skill-chip';
            chip.dataset.skill = skill;
            chip.innerHTML = '<span>' + skill + '</span><button type="button" class="selected-skill-remove" aria-label="Remove skill">&times;</button>';
            selectedSkillsList.appendChild(chip);
        });

        syncHiddenInput();
    }

    if (skillSelect) {
        skillSelect.addEventListener('change', function() {
            const skill = (skillSelect.value || '').trim();
            if (!skill) {
                return;
            }

            selectedSkills.add(skill);
            renderSelectedSkills();
            skillSelect.value = '';
        });
    }

    if (selectedSkillsList) {
        selectedSkillsList.addEventListener('click', function(event) {
            if (!event.target.classList.contains('selected-skill-remove')) {
                return;
            }

            const chip = event.target.closest('.selected-skill-chip');
            if (!chip) {
                return;
            }

            selectedSkills.delete(chip.dataset.skill || '');
            renderSelectedSkills();
        });
    }

    if (startDateInput && startDateInput.min && startDateInput.value && startDateInput.value < startDateInput.min) {
        startDateInput.value = startDateInput.min;
    }

    if (endDateInput && endDateInput.min && endDateInput.value && endDateInput.value < endDateInput.min) {
        endDateInput.value = endDateInput.min;
    }

    // Set minimum date for start_date to tomorrow if not already set
    if (startDateInput) {
        const t = new Date();
        t.setDate(t.getDate() + 1);
        const tomorrowStr = t.toISOString().split('T')[0];
        if (!startDateInput.min || startDateInput.min < tomorrowStr) {
            startDateInput.min = tomorrowStr;
        }
    }

    // Set up end_date minimum based on start_date (default to tomorrow)
    if (startDateInput && endDateInput) {
        const t2 = new Date();
        t2.setDate(t2.getDate() + 1);
        const tomorrowStr = t2.toISOString().split('T')[0];

        // If start_date is already set, initialize end_date min from start_date
        if (startDateInput.value) {
            endDateInput.min = startDateInput.value;
        } else {
            endDateInput.min = tomorrowStr;
        }

        // When start_date changes, update end_date minimum
        startDateInput.addEventListener('change', function() {
            if (this.value) {
                endDateInput.min = this.value;
                // If end_date is set and is now less than the new start_date, clear it
                if (endDateInput.value && endDateInput.value < this.value) {
                    endDateInput.value = '';
                }
            }
        });
    }

    renderSelectedSkills();
});
</script>
