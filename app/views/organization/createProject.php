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

            <!-- Card-style header with category icon and inline status -->
            <div id="projectCardHeader" class="project-card-header <?= $isEdit ? ($project->category ?? 'web') : 'web'?>">
                <div class="header-left">
                    <div id="projIcon" class="proj-icon web"><i class="ph ph-code"></i></div>
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

                    <div class="info-item full-width">
                        <label>Skills Needed</label>
                        <p class="skills-note">Choose the skills that this project needs. Click a suggestion to add it to the required skills list.</p>
                        <input id="requiredSkillsInput" type="text" name="required_skills" value="<?= $isEdit ? htmlspecialchars($project->required_skills) : '' ?>" placeholder="Select a category to load suggested skills" autocomplete="off" spellcheck="false" required>
                        <div id="skillsSuggestionList" class="skill-suggestion-list"></div>
                        <div id="selectedSkillsPreview" class="selected-skills-preview"></div>
                        <small id="skillsHint" class="skills-hint"></small>
                    </div>

                    <div class="info-item">
                        <label>Category</label>
                        <select id="categorySelect" name="category" required>
                            <?php 
                                $categories = ['web','mobile','data','design','other'];
                                foreach ($categories as $cat):
                            ?>
                                <option value="<?= $cat ?>" <?= $isEdit && $project->category == $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <h2 class="section-title">Project Settings</h2>
                <div class="section-content settings-grid">
                    <div class="small-card">
                        <label>Max Members</label>
                        <input type="number" name="max_members" min="1" value="<?= $isEdit ? $project->max_members : 5 ?>" required>
                    </div>

                    <div class="small-card">
                        <label>Start Date</label>
                        <input class="info-input" type="date" name="start_date" value="<?= $isEdit ? $project->start_date : '' ?>">
                    </div>

                    <div class="small-card">
                        <label>End Date</label>
                        <input class="info-input" type="date" name="end_date" value="<?= $isEdit ? $project->end_date : '' ?>">
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
// UI polish for Create Project form: update icon and skill hints based on category
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('categorySelect');
    const requiredSkillsInput = document.getElementById('requiredSkillsInput');
    const skillsHint = document.getElementById('skillsHint');
    const skillsSuggestionList = document.getElementById('skillsSuggestionList');
    const selectedSkillsPreview = document.getElementById('selectedSkillsPreview');
    const icon = document.getElementById('projIcon');
    const categorySkillMap = <?= json_encode($categorySkillMap ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

      //create a map of category to icon and css class for easy reference
    const map = {
        'web': {class: 'web', icon: '<i class="ph ph-code"></i>'},
        'mobile': {class: 'mobile', icon: '<i class="ph ph-device-mobile"></i>'},
        'data': {class: 'data', icon: '<i class="ph ph-chart-bar"></i>'},
        'design': {class: 'design', icon: '<i class="ph ph-paint-brush"></i>'},
        'other': {class: 'other', icon: '<i class="ph ph-sparkle"></i>'}
    };

    function updateSkillsHint() {
        if (!categorySelect) return;

        const category = categorySelect.value || 'web';
        const suggestions = categorySkillMap[category] || [];

        if (requiredSkillsInput) {
            if (suggestions.length > 0) {
                requiredSkillsInput.placeholder = 'Example: ' + suggestions.join(', ');
            } else {
                requiredSkillsInput.placeholder = 'Enter required skills separated by commas';
            }
        }

        if (skillsHint) {
            if (suggestions.length > 0) {
                skillsHint.textContent = 'Allowed ' + category + ' skills: ' + suggestions.join(' | ');
            } else {
                skillsHint.textContent = '';
            }
        }

        if (skillsSuggestionList) {
            skillsSuggestionList.innerHTML = '';
            suggestions.forEach(function(skill) {
                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'skill-chip';
                chip.textContent = skill;
                chip.addEventListener('click', function() {
                    const currentSkills = parseSkills(requiredSkillsInput ? requiredSkillsInput.value : '');
                    const normalizedSkill = normalizeSkill(skill);

                    if (!currentSkills.some(function(item) { return normalizeSkill(item) === normalizedSkill; })) {
                        currentSkills.push(skill);
                        if (requiredSkillsInput) {
                            requiredSkillsInput.value = currentSkills.join(', ');
                        }
                        renderSelectedSkills();
                    }
                });
                skillsSuggestionList.appendChild(chip);
            });
        }

        renderSelectedSkills();
    }

    function normalizeSkill(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/[\s_-]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function parseSkills(value) {
        return String(value || '')
            .split(',')
            .map(function(item) { return item.trim(); })
            .filter(Boolean);
    }

    function renderSelectedSkills() {
        if (!selectedSkillsPreview || !requiredSkillsInput) {
            return;
        }

        const currentSkills = parseSkills(requiredSkillsInput.value);
        selectedSkillsPreview.innerHTML = '';

        if (currentSkills.length === 0) {
            const emptyState = document.createElement('span');
            emptyState.className = 'skills-note';
            emptyState.textContent = 'Selected skills will appear here.';
            selectedSkillsPreview.appendChild(emptyState);
            return;
        }

        currentSkills.forEach(function(skill) {
            const chip = document.createElement('span');
            chip.className = 'skill-chip is-selected';

            const label = document.createElement('span');
            label.textContent = skill;
            chip.appendChild(label);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'skill-remove';
            remove.innerHTML = '&times;';
            remove.addEventListener('click', function() {
                const nextSkills = currentSkills.filter(function(item) {
                    return normalizeSkill(item) !== normalizeSkill(skill);
                });
                requiredSkillsInput.value = nextSkills.join(', ');
                renderSelectedSkills();
            });

            chip.appendChild(remove);
            selectedSkillsPreview.appendChild(chip);
        });
    }

    if (requiredSkillsInput) {
        requiredSkillsInput.addEventListener('input', renderSelectedSkills);
    }

    function updateHeader() {
        const val = categorySelect ? categorySelect.value : 'web';
        icon.innerHTML = map[val].icon;
        updateSkillsHint();
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', updateHeader);
        updateHeader();
    }
});
</script>
