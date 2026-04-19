<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/profile_setup.css">

<?php
$availableSkills = $data['availableSkills'] ?? [];
$existingTeachSkills = $data['skills']['teaches'] ?? [];
$existingLearnSkills = $data['skills']['learns'] ?? [];
$old = $data['old'] ?? [];

// If validation failed, rebuild rows from old submitted values
if (!empty($old)) {
    $existingTeachSkills = [];
    $oldTeachSkills = $old['teach_skills'] ?? [];
    $oldTeachLevels = $old['teach_levels'] ?? [];

    foreach ($oldTeachSkills as $index => $skillName) {
        $existingTeachSkills[] = [
            'name' => $skillName,
            'level' => $oldTeachLevels[$index] ?? ''
        ];
    }

    $existingLearnSkills = [];
    $oldLearnSkills = $old['learn_skills'] ?? [];
    $oldLearnLevels = $old['learn_levels'] ?? [];

    foreach ($oldLearnSkills as $index => $skillName) {
        $existingLearnSkills[] = [
            'name' => $skillName,
            'level' => $oldLearnLevels[$index] ?? ''
        ];
    }
}

// Ensure at least one row exists
if (empty($existingTeachSkills)) {
    $existingTeachSkills = [['name' => '', 'level' => '']];
}

if (empty($existingLearnSkills)) {
    $existingLearnSkills = [['name' => '', 'level' => '']];
}

// Build reusable HTML for JS-added rows
$skillOptionsHtml = '<option value="">Select a skill</option>';
foreach ($availableSkills as $skillOption) {
    $value = htmlspecialchars($skillOption['skill_name'], ENT_QUOTES, 'UTF-8');
    $label = htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $skillOption['skill_name'])), ENT_QUOTES, 'UTF-8');
    $skillOptionsHtml .= "<option value=\"{$value}\">{$label}</option>";
}
?>

<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">
            <div class="profile-setup-container edit-profile-page">
                <div class="setup-card">
                    <div class="setup-header setup-header-left">
                        <span class="setup-eyebrow">Profile Settings</span>
                        <h1 class="setup-title">Edit Your Profile</h1>
                        <p class="setup-subtitle">Update your details, refresh your bio, and keep your teaching and learning skills in sync.</p>
                    </div>

                    <?php if (!empty($data['errors'])): ?>
                        <div class="error-messages">
                            <?php foreach ($data['errors'] as $error): ?>
                                <p class="error"><?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= URLROOT ?>/users/editProfile" enctype="multipart/form-data" class="profile-setup-form">

                        <!-- Profile Picture -->
                        <section class="form-section">
                            <h2 class="section-title">Basic Information</h2>
                            <div class="section-divider"></div>

                            <div class="form-group">
                                <label>Profile Picture</label>

                                <?php if (!empty($data['user']['profile_picture'])): ?>
                                    <div class="current-picture">
                                        <img
                                            src="<?= URLROOT ?>/<?= htmlspecialchars($data['user']['profile_picture']) ?>"
                                            alt="Current profile picture"
                                            class="current-picture-image"
                                        >
                                        <p class="current-picture-label">Current picture</p>
                                    </div>
                                <?php endif; ?>

                                <input type="hidden" name="existing_profile_picture" value="<?= htmlspecialchars($data['user']['profile_picture'] ?? '') ?>">
                                <input type="file" name="profile_picture" accept="image/*">
                                <small class="field-hint">Leave empty to keep your current picture. Max 5MB (JPG, PNG, GIF)</small>
                            </div>

                            <div class="form-group">
                                <label for="username">Username *</label>
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    value="<?= htmlspecialchars($old['username'] ?? $data['user']['username'] ?? '') ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="bio">Bio</label>
                                <textarea
                                    id="bio"
                                    name="bio"
                                    rows="4"
                                    placeholder="Tell us about yourself..."
                                ><?= htmlspecialchars($old['bio'] ?? $data['user']['bio'] ?? '') ?></textarea>
                            </div>
                        </section>

                        <!-- Skills I Teach -->
                        <section class="form-section">
                            <h2 class="section-title">Skills I Can Teach</h2>
                            <p class="section-description">Keep your teaching skills current so matching stays relevant.</p>
                            <div id="teach-skills-container" class="skills-group dynamic-skills-group">
                                <?php foreach ($existingTeachSkills as $skill): ?>
                                    <div class="skill-row">
                                        <select name="teach_skills[]" class="skill-select">
                                            <option value="">Select a skill</option>
                                            <?php foreach ($availableSkills as $skillOption): ?>
                                                <option
                                                    value="<?= htmlspecialchars($skillOption['skill_name']) ?>"
                                                    <?= (($skill['name'] ?? '') === $skillOption['skill_name']) ? 'selected' : '' ?>
                                                >
                                                    <?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $skillOption['skill_name']))) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <select name="teach_levels[]" class="level-select">
                                            <option value="">Level</option>
                                            <option value="beginner" <?= (($skill['level'] ?? '') === 'beginner') ? 'selected' : '' ?>>Beginner</option>
                                            <option value="intermediate" <?= (($skill['level'] ?? '') === 'intermediate') ? 'selected' : '' ?>>Intermediate</option>
                                            <option value="advanced" <?= (($skill['level'] ?? '') === 'advanced') ? 'selected' : '' ?>>Advanced</option>
                                        </select>

                                        <button type="button" class="remove-skill-btn" onclick="removeSkillRow(this)">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <button type="button" class="add-skill-btn" onclick="addTeachSkill()">+ Add Teaching Skill</button>
                        </section>

                        <!-- Skills I Want to Learn -->
                        <section class="form-section">
                            <h2 class="section-title">Skills I Want to Learn</h2>
                            <p class="section-description">Adjust your learning goals to keep recommendations and matches aligned.</p>
                            <div id="learn-skills-container" class="skills-group dynamic-skills-group">
                                <?php foreach ($existingLearnSkills as $skill): ?>
                                    <div class="skill-row">
                                        <select name="learn_skills[]" class="skill-select">
                                            <option value="">Select a skill</option>
                                            <?php foreach ($availableSkills as $skillOption): ?>
                                                <option
                                                    value="<?= htmlspecialchars($skillOption['skill_name']) ?>"
                                                    <?= (($skill['name'] ?? '') === $skillOption['skill_name']) ? 'selected' : '' ?>
                                                >
                                                    <?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $skillOption['skill_name']))) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <select name="learn_levels[]" class="level-select">
                                            <option value="">Level</option>
                                            <option value="beginner" <?= (($skill['level'] ?? '') === 'beginner') ? 'selected' : '' ?>>Beginner</option>
                                            <option value="intermediate" <?= (($skill['level'] ?? '') === 'intermediate') ? 'selected' : '' ?>>Intermediate</option>
                                            <option value="advanced" <?= (($skill['level'] ?? '') === 'advanced') ? 'selected' : '' ?>>Advanced</option>
                                        </select>

                                        <button type="button" class="remove-skill-btn" onclick="removeSkillRow(this)">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <button type="button" class="add-skill-btn" onclick="addLearnSkill()">+ Add Learning Skill</button>
                        </section>

                        <div class="form-actions">
                            <a href="<?= URLROOT ?>/users/userprofile" class="cancel-btn">Cancel</a>
                            <button type="submit" class="save-btn">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
const skillOptionsHtml = `<?= $skillOptionsHtml ?>`;

function addTeachSkill() {
    const container = document.getElementById('teach-skills-container');
    const skillRow = document.createElement('div');
    skillRow.className = 'skill-row';

    skillRow.innerHTML = `
        <select name="teach_skills[]" class="skill-select">
            ${skillOptionsHtml}
        </select>
        <select name="teach_levels[]" class="level-select">
            <option value="">Level</option>
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
        </select>
        <button type="button" class="remove-skill-btn" onclick="removeSkillRow(this)">Remove</button>
    `;

    container.appendChild(skillRow);
}

function addLearnSkill() {
    const container = document.getElementById('learn-skills-container');
    const skillRow = document.createElement('div');
    skillRow.className = 'skill-row';

    skillRow.innerHTML = `
        <select name="learn_skills[]" class="skill-select">
            ${skillOptionsHtml}
        </select>
        <select name="learn_levels[]" class="level-select">
            <option value="">Level</option>
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
        </select>
        <button type="button" class="remove-skill-btn" onclick="removeSkillRow(this)">Remove</button>
    `;

    container.appendChild(skillRow);
}

function removeSkillRow(button) {
    const row = button.parentElement;
    const teachContainer = document.getElementById('teach-skills-container');
    const learnContainer = document.getElementById('learn-skills-container');

    if (row.parentElement === teachContainer && teachContainer.children.length === 1) {
        row.querySelector('select[name="teach_skills[]"]').value = '';
        row.querySelector('select[name="teach_levels[]"]').value = '';
        return;
    }

    if (row.parentElement === learnContainer && learnContainer.children.length === 1) {
        row.querySelector('select[name="learn_skills[]"]').value = '';
        row.querySelector('select[name="learn_levels[]"]').value = '';
        return;
    }

    row.remove();
}
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>
