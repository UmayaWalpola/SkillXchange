<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>


<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/profile_setup.css">

<div class="profile-setup-container">
    <div class="setup-card">
        <h1>Edit Your Profile</h1>
        <p class="subtitle">Update your information and skills</p>

        <?php if (!empty($data['errors'])): ?>
            <div class="error-messages">
                <?php foreach($data['errors'] as $error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= URLROOT ?>/users/editProfile" enctype="multipart/form-data">
            
            <!-- Profile Picture -->
            <div class="form-group">
                <label>Profile Picture</label>
                <?php if (!empty($data['user']['profile_picture'])): ?>
                    <div class="current-picture">
                        <img src="<?= URLROOT ?>/<?= htmlspecialchars($data['user']['profile_picture']) ?>" 
                             alt="Current profile picture" 
                             style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 10px;">
                        <p style="font-size: 0.9rem; color: #666;">Current picture</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="profile_picture" accept="image/*">
                <small>Leave empty to keep current picture. Max 5MB (JPG, PNG, GIF)</small>
            </div>

            <!-- Username -->
            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="<?= htmlspecialchars($data['old']['username'] ?? $data['user']['username']) ?>" 
                       required>
            </div>

            <!-- Bio -->
            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" 
                          name="bio" 
                          rows="4" 
                          placeholder="Tell us about yourself..."><?= htmlspecialchars($data['old']['bio'] ?? $data['user']['bio'] ?? '') ?></textarea>
            </div>

            <!-- Skills I Teach -->
            <div class="form-section">
                <h3>Skills I Can Teach</h3>
                <div id="teach-skills-container">
                    <?php 
                    $existingTeachSkills = $data['skills']['teaches'] ?? [];
                    if (empty($existingTeachSkills)) {
                        $existingTeachSkills = [['name' => '', 'level' => '']];
                    }
                    foreach ($existingTeachSkills as $index => $skill): 
                    ?>
                        <div class="skill-row">
                            <select name="teach_skills[]" class="skill-select">
                                <option value="">Select a skill</option>
                                
<option value="web-development" <?= ($skill['name'] === 'web-development') ? 'selected' : '' ?>>Web Development</option>
<option value="frontend" <?= ($skill['name'] === 'frontend') ? 'selected' : '' ?>>Frontend Frameworks</option>
<option value="backend" <?= ($skill['name'] === 'backend') ? 'selected' : '' ?>>Backend Development</option>
<option value="database" <?= ($skill['name'] === 'database') ? 'selected' : '' ?>>Database Management</option>
<option value="mobile" <?= ($skill['name'] === 'mobile') ? 'selected' : '' ?>>Mobile App Development</option>
<option value="cloud" <?= ($skill['name'] === 'cloud') ? 'selected' : '' ?>>Cloud Computing</option>
<option value="data-analytics" <?= ($skill['name'] === 'data-analytics') ? 'selected' : '' ?>>Data Analysis & Visualization</option>
<option value="cybersecurity" <?= ($skill['name'] === 'cybersecurity') ? 'selected' : '' ?>>Cybersecurity</option>
<option value="devops" <?= ($skill['name'] === 'devops') ? 'selected' : '' ?>>DevOps</option>
<option value="github" <?= ($skill['name'] === 'github') ? 'selected' : '' ?>>GitHub and Git</option>
<option value="ai" <?= ($skill['name'] === 'ai') ? 'selected' : '' ?>>AI and ML</option>
<option value="marketing" <?= ($skill['name'] === 'marketing') ? 'selected' : '' ?>>Digital Marketing</option>
<option value="data-science" <?= ($skill['name'] === 'data-science') ? 'selected' : '' ?>>Data Science</option>

                            </select>
                            <select name="teach_levels[]" class="level-select">
                                <option value="">Level</option>
                                <option value="beginner" <?= ($skill['level'] === 'beginner') ? 'selected' : '' ?>>Beginner</option>
                                <option value="intermediate" <?= ($skill['level'] === 'intermediate') ? 'selected' : '' ?>>Intermediate</option>
                                <option value="advanced" <?= ($skill['level'] === 'advanced') ? 'selected' : '' ?>>Advanced</option>
                            </select>
                            <button type="button" class="remove-skill-btn" onclick="removeSkillRow(this)">Remove</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="add-skill-btn" onclick="addTeachSkill()">+ Add Teaching Skill</button>
            </div>

            <!-- Skills I Want to Learn -->
            <div class="form-section">
                <h3>Skills I Want to Learn *</h3>
                <div id="learn-skills-container">
                    <?php 
                    $existingLearnSkills = $data['skills']['learns'] ?? [];
                    if (empty($existingLearnSkills)) {
                        $existingLearnSkills = [['name' => '', 'level' => '']];
                    }
                    foreach ($existingLearnSkills as $index => $skill): 
                    ?>
                        <div class="skill-row">
                            <select name="learn_skills[]" class="skill-select">
                                <option value="">Select a skill</option>
<option value="web-development" <?= ($skill['name'] === 'web-development') ? 'selected' : '' ?>>Web Development</option>
<option value="frontend" <?= ($skill['name'] === 'frontend') ? 'selected' : '' ?>>Frontend Frameworks</option>
<option value="backend" <?= ($skill['name'] === 'backend') ? 'selected' : '' ?>>Backend Development</option>
<option value="database" <?= ($skill['name'] === 'database') ? 'selected' : '' ?>>Database Management</option>
<option value="mobile" <?= ($skill['name'] === 'mobile') ? 'selected' : '' ?>>Mobile App Development</option>
<option value="cloud" <?= ($skill['name'] === 'cloud') ? 'selected' : '' ?>>Cloud Computing</option>
<option value="data-analytics" <?= ($skill['name'] === 'data-analytics') ? 'selected' : '' ?>>Data Analysis & Visualization</option>
<option value="cybersecurity" <?= ($skill['name'] === 'cybersecurity') ? 'selected' : '' ?>>Cybersecurity</option>
<option value="devops" <?= ($skill['name'] === 'devops') ? 'selected' : '' ?>>DevOps</option>
<option value="github" <?= ($skill['name'] === 'github') ? 'selected' : '' ?>>GitHub and Git</option>
<option value="ai" <?= ($skill['name'] === 'ai') ? 'selected' : '' ?>>AI and ML</option>
<option value="marketing" <?= ($skill['name'] === 'marketing') ? 'selected' : '' ?>>Digital Marketing</option>
<option value="data-science" <?= ($skill['name'] === 'data-science') ? 'selected' : '' ?>>Data Science</option>

                            </select>
                            <select name="learn_levels[]" class="level-select">
                                <option value="">Level</option>
                                <option value="beginner" <?= ($skill['level'] === 'beginner') ? 'selected' : '' ?>>Beginner</option>
                                <option value="intermediate" <?= ($skill['level'] === 'intermediate') ? 'selected' : '' ?>>Intermediate</option>
                                <option value="advanced" <?= ($skill['level'] === 'advanced') ? 'selected' : '' ?>>Advanced</option>
                            </select>
                            <button type="button" class="remove-skill-btn" onclick="removeSkillRow(this)">Remove</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="add-skill-btn" onclick="addLearnSkill()">+ Add Learning Skill</button>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Save Changes</button>
                <a href="<?= URLROOT ?>/users/userprofile" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Add teaching skill row
function addTeachSkill() {
    const container = document.getElementById('teach-skills-container');
    const skillRow = document.createElement('div');
    skillRow.className = 'skill-row';
    skillRow.innerHTML = `
        <select name="teach_skills[]" class="skill-select">
            <option value="">Select a skill</option>
                                <option value="web-development">Web Development</option>
                                <option value="frontend">Frontend Frameworks</option>      
                                <option value="backend">Backend Development</option>
                                <option value="database">Database Management</option>                                      
                                <option value="mobile">Mobile App Development</option> 
                                <option value="cloud">Cloud Computing</option>
                                <option value="data-analytics">Data Analysis & Visualization</option>
                                <option value="cybersecurity">Cybersecurity</option>
                                <option value="devops">Devops</option>
                                <option value="github">GitHub and Git</option>
                                <option value="ai">AI and ML</option>
                                <option value="marketing">Digital Marketing</option>
                                <option value="data-science">Data Science</option>
                           
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

// Add learning skill row
function addLearnSkill() {
    const container = document.getElementById('learn-skills-container');
    const skillRow = document.createElement('div');
    skillRow.className = 'skill-row';
    skillRow.innerHTML = `
        <select name="learn_skills[]" class="skill-select">
            <option value="">Select a skill</option>
                                <option value="web-development">Web Development</option>
                                <option value="frontend">Frontend Frameworks</option>      
                                <option value="backend">Backend Development</option>
                                <option value="database">Database Management</option>                                      
                                <option value="mobile">Mobile App Development</option> 
                                <option value="cloud">Cloud Computing</option>
                                <option value="data-analytics">Data Analysis & Visualization</option>
                                <option value="cybersecurity">Cybersecurity</option>
                                <option value="devops">Devops</option>
                                <option value="github">GitHub and Git</option>
                                <option value="ai">AI and ML</option>
                                <option value="marketing">Digital Marketing</option>
                                <option value="data-science">Data Science</option>
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

// Remove skill row
function removeSkillRow(button) {
    const row = button.parentElement;
    row.remove();
}
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>