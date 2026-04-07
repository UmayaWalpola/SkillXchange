<?php require_once "../app/views/layouts/header_user.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/profile_setup.css">

<main class="site-main">
    <div class="profile-setup-container">

        <div class="setup-header">
            <h1 class="setup-title">Complete Your Profile</h1>
            <p class="setup-subtitle">Let's set up your SkillXchange profile so you can start teaching and learning!</p>
        </div>

        <div class="setup-progress">
            <div class="progress-bar">
                <div class="progress-fill" style="width: 33%"></div>
            </div>
            <p class="progress-text">Step 1 of 1 — Profile Information</p>
        </div>

        <?php if (!empty($data['errors'])): ?>
            <div class="error-message">
                <strong>Please fix the following errors:</strong>
                <ul>
                    <?php foreach($data['errors'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= URLROOT ?>/users/profileSetup" method="POST" enctype="multipart/form-data" class="profile-setup-form">

            <!-- Basic Information -->
            <section class="form-section">
                <h2 class="section-title">Basic Information</h2>
                <div class="section-divider"></div>

                <div class="form-group">
                    <label for="username">Username <span class="required-star">*</span></label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Choose a unique username"
                        value="<?= htmlspecialchars($data['old']['username'] ?? $data['user']['username'] ?? '') ?>"
                        required
                        minlength="3"
                        maxlength="20"
                    >
                    <small class="field-hint">Your public display name — 3 to 20 characters</small>
                </div>

                <div class="form-group">
                    <label for="profile-picture">Profile Picture</label>
                    <div class="file-upload-wrapper">
                        <input
                            type="file"
                            id="profile-picture"
                            name="profile_picture"
                            accept="image/jpeg,image/png,image/jpg,image/gif"
                            class="file-input"
                        >
                        <div class="file-upload-display">
                            <div class="upload-icon"><i class="ph ph-camera"></i></div>
                            <p class="upload-text">Click to upload or drag and drop</p>
                            <small class="upload-hint">JPG, PNG, GIF — max 5 MB</small>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Skills to Learn -->
            <section class="form-section">
                <h2 class="section-title">
                    Skills You Want to Learn
                    <span class="badge badge-required">Min 1 required</span>
                </h2>
                <p class="section-description">Tell us what you're interested in learning. <strong>Up to 3 skills total.</strong></p>

                <div class="skills-group">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="skill-entry">
                        <div class="form-group skill-name-group">
                            <label for="learn-skill-<?= $i ?>">Skill <?= $i ?><?= $i === 1 ? ' <span class="required-star">*</span>' : '' ?></label>
                            <select id="learn-skill-<?= $i ?>" name="learn_skills[]">
                                <option value="">Select a skill</option>
                                <option value="web-development">Web Development</option>
                                <option value="frontend">Frontend Frameworks</option>
                                <option value="backend">Backend Development</option>
                                <option value="database">Database Management</option>
                                <option value="mobile">Mobile App Development</option>
                                <option value="cloud">Cloud Computing</option>
                                <option value="data-analytics">Data Analysis & Visualization</option>
                                <option value="cybersecurity">Cybersecurity</option>
                                <option value="devops">DevOps</option>
                                <option value="github">GitHub and Git</option>
                                <option value="ai">AI and ML</option>
                                <option value="marketing">Digital Marketing</option>
                                <option value="data-science">Data Science</option>
                            </select>
                        </div>
                        <div class="form-group skill-level-group">
                            <label for="learn-level-<?= $i ?>">Current Level</label>
                            <select id="learn-level-<?= $i ?>" name="learn_levels[]">
                                <option value="">Select level</option>
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </section>

            <!-- Skills to Teach -->
            <section class="form-section">
                <h2 class="section-title">
                    Skills You Can Teach
                    <span class="badge badge-optional">Optional</span>
                </h2>
                <p class="section-description">Share your expertise! Add up to 3 skills you'd like to teach others.</p>

                <div class="skills-group">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="skill-entry">
                        <div class="form-group skill-name-group">
                            <label for="teach-skill-<?= $i ?>">Skill <?= $i ?></label>
                            <select id="teach-skill-<?= $i ?>" name="teach_skills[]">
                                <option value="">Select a skill</option>
                                <option value="web-development">Web Development</option>
                                <option value="frontend">Frontend Frameworks</option>
                                <option value="backend">Backend Development</option>
                                <option value="database">Database Management</option>
                                <option value="mobile">Mobile App Development</option>
                                <option value="cloud">Cloud Computing</option>
                                <option value="data-analytics">Data Analysis & Visualization</option>
                                <option value="cybersecurity">Cybersecurity</option>
                                <option value="devops">DevOps</option>
                                <option value="github">GitHub and Git</option>
                                <option value="ai">AI and ML</option>
                                <option value="marketing">Digital Marketing</option>
                                <option value="data-science">Data Science</option>
                            </select>
                        </div>
                        <div class="form-group skill-level-group">
                            <label for="teach-level-<?= $i ?>">Proficiency</label>
                            <select id="teach-level-<?= $i ?>" name="teach_levels[]">
                                <option value="">Select level</option>
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </section>

            <!-- Submit -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    Complete Profile &amp; Start Learning
                </button>
                <p class="form-footer-note">* Required fields. You can update your profile anytime from your dashboard.</p>
            </div>

        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.profile-setup-form');
    const learnSkillSelects = document.querySelectorAll('[name="learn_skills[]"]');
    const teachSkillSelects = document.querySelectorAll('[name="teach_skills[]"]');
    const learnLevelSelects = document.querySelectorAll('[name="learn_levels[]"]');
    const teachLevelSelects = document.querySelectorAll('[name="teach_levels[]"]');

    learnSkillSelects.forEach((select, index) => {
        select.addEventListener('change', function () {
            validateLearnSkills();
            if (select.value) {
                learnLevelSelects[index].setAttribute('required', 'required');
            } else {
                learnLevelSelects[index].removeAttribute('required');
                learnLevelSelects[index].value = '';
            }
        });
    });

    teachSkillSelects.forEach((select, index) => {
        select.addEventListener('change', function () {
            if (select.value) {
                teachLevelSelects[index].setAttribute('required', 'required');
            } else {
                teachLevelSelects[index].removeAttribute('required');
                teachLevelSelects[index].value = '';
            }
        });
    });

    form.addEventListener('submit', function (e) {
        clearValidationErrors();

        if (!validateLearnSkills()) {
            e.preventDefault();
            showValidationError('learn-skill-1', 'Please select at least one skill you want to learn.');
            return false;
        }

        let hasError = false;

        learnSkillSelects.forEach((select, index) => {
            if (select.value && !learnLevelSelects[index].value) {
                e.preventDefault();
                showValidationError('learn-level-' + (index + 1), 'Please select a level for this skill.');
                hasError = true;
            }
        });

        teachSkillSelects.forEach((select, index) => {
            if (select.value && !teachLevelSelects[index].value) {
                e.preventDefault();
                showValidationError('teach-level-' + (index + 1), 'Please select a proficiency level for this skill.');
                hasError = true;
            }
        });

        if (hasError) return false;
    });

    function validateLearnSkills() {
        const hasAtLeastOne = Array.from(learnSkillSelects).some(s => s.value !== '');
        learnSkillSelects.forEach(select => {
            select.style.borderColor = hasAtLeastOne ? '' : '#e53e3e';
        });
        return hasAtLeastOne;
    }

    function showValidationError(fieldId, message) {
        const el = document.createElement('p');
        el.className = 'validation-error';
        el.textContent = message;
        const target = document.getElementById(fieldId);
        if (target) target.parentElement.appendChild(el);
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function clearValidationErrors() {
        document.querySelectorAll('.validation-error').forEach(e => e.remove());
        document.querySelectorAll('input, select').forEach(f => { f.style.borderColor = ''; });
    }

    const fileInput = document.getElementById('profile-picture');
    if (fileInput) {
        fileInput.addEventListener('change', function (e) {
            const fileName = e.target.files[0]?.name;
            const uploadText = document.querySelector('.upload-text');
            if (fileName && uploadText) {
                uploadText.textContent = fileName;
                uploadText.style.color = 'var(--primary-blue)';
                uploadText.style.fontWeight = '600';
            }
        });
    }
});
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>