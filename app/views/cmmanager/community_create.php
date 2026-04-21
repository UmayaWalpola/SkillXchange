<?php require_once '../app/views/layouts/header_user.php'; ?>
<?php require_once '../app/views/layouts/commanagersidebar.php'; ?>

<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/global.css">
<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/dashboard.css">
<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/communityadmin.css">

<div class="community-form-wrapper">
    <div class="community-form-container">

        <a href="<?php echo URLROOT; ?>/communityAdmin" class="btn-back">← Back to Dashboard</a>

        <div class="form-header">
            <h1>Create a Community</h1>
            <p>Set up a new community for your skill</p>
        </div>

        <form id="communityForm">
            <div class="form-group">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <label for="communityName">Select Skill *</label>
                    <button type="button" class="btn-add-skill" onclick="openAddSkillModal()">
                        + Add New Skill
                    </button>
                </div>
                <select id="communityName" required>
                    <option value="">-- Choose a skill --</option>
                    <?php if(isset($data['skills']) && !empty($data['skills'])): ?>
                        <?php foreach($data['skills'] as $skill): ?>
                            <option value="<?= htmlspecialchars($skill->id) ?>">
                                <?= htmlspecialchars($skill->skill_name) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No available skills without communities</option>
                    <?php endif; ?>
                </select>
                <?php if(empty($data['skills'])): ?>
                    <small class="help-text">Every skill already has a community. Add a new skill first if you want to create another community.</small>
                <?php endif; ?>
                <span class="error-text" id="nameError"></span>
            </div>

            <div class="form-group">
                <label for="communityDescription">Description *</label>
                <textarea
                    id="communityDescription"
                    placeholder="Describe your community..."
                    maxlength="1000"
                    required
                ></textarea>
                <span class="char-count"><span id="descCharCount">0</span> / 1000 characters</span>
                <span class="error-text" id="descriptionError"></span>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="previewCommunity()">Preview</button>
                <button type="button" class="btn-primary" onclick="publishCommunity()">Create Community</button>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="modal-overlay" onclick="if(event.target===this) closePreviewModal()">
    <div class="modal-box">
        <button class="modal-close" onclick="closePreviewModal()">&times;</button>
        <h2>Preview Your Community</h2>
        <div id="previewContent"></div>
        <div class="form-footer" style="margin-top:20px;">
            <button class="btn-secondary" onclick="closePreviewModal()">Edit</button>
            <button class="btn-primary" onclick="publishCommunity()">Create Community</button>
        </div>
    </div>
</div>

<!-- Add Skill Modal -->
<div id="addSkillModal" class="modal-overlay" onclick="if(event.target===this) closeAddSkillModal()">
    <div class="modal-box">
        <button class="modal-close" onclick="closeAddSkillModal()">&times;</button>
        <h2>Add New Skill</h2>
        <form id="addSkillForm" onsubmit="saveNewSkill(event)">
            <div class="skill-form-group">
                <label for="newSkillName">Skill Name *</label>
                <input type="text" id="newSkillName" placeholder="e.g., Advanced Python" required maxlength="100">
                <div class="skill-error-msg" id="skillNameError"></div>
                <div class="skill-success-msg" id="skillNameSuccess"></div>
            </div>
            <div class="skill-form-group">
                <label for="newSkillDescription">Description (optional)</label>
                <input type="text" id="newSkillDescription" placeholder="Brief description..." maxlength="200">
            </div>
            <div class="form-footer">
                <button type="button" class="btn-secondary" onclick="closeAddSkillModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="saveSkillBtn">Add Skill</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('communityDescription')?.addEventListener('input', function() {
    document.getElementById('descCharCount').textContent = this.value.length;
});

function previewCommunity() {
    const skillId = document.getElementById('communityName').value;
    const skillName = document.getElementById('communityName').options[document.getElementById('communityName').selectedIndex].text;
    const description = document.getElementById('communityDescription').value;

    document.getElementById('nameError').textContent = '';
    document.getElementById('descriptionError').textContent = '';

    let hasError = false;
    if (!skillId) { document.getElementById('nameError').textContent = 'Please select a skill'; hasError = true; }
    if (!description.trim()) { document.getElementById('descriptionError').textContent = 'Please enter a description'; hasError = true; }
    if (hasError) return;

    document.getElementById('previewContent').innerHTML = `
        <div class="preview-community">
            <h3>${skillName}</h3>
            <p>${description}</p>
        </div>`;
    document.getElementById('previewModal').classList.add('open');
}

function closePreviewModal() {
    document.getElementById('previewModal').classList.remove('open');
}

function openAddSkillModal() {
    document.getElementById('addSkillModal').classList.add('open');
    document.getElementById('addSkillForm').reset();
    document.getElementById('skillNameError').style.display = 'none';
    document.getElementById('skillNameSuccess').style.display = 'none';
}

function closeAddSkillModal() {
    document.getElementById('addSkillModal').classList.remove('open');
}

function saveNewSkill(event) {
    event.preventDefault();
    const skillName = document.getElementById('newSkillName').value.trim();
    const skillDescription = document.getElementById('newSkillDescription').value.trim();

    document.getElementById('skillNameError').style.display = 'none';
    document.getElementById('skillNameSuccess').style.display = 'none';

    if (!skillName) {
        document.getElementById('skillNameError').textContent = 'Please enter a skill name';
        document.getElementById('skillNameError').style.display = 'block';
        return;
    }

    const saveBtn = document.getElementById('saveSkillBtn');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Adding...';

    fetch('<?= URLROOT ?>/communityAdmin/addSkill', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ skill_name: skillName, description: skillDescription })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('skillNameSuccess').textContent = 'Skill added successfully!';
            document.getElementById('skillNameSuccess').style.display = 'block';
            const select = document.getElementById('communityName');
            const opt = document.createElement('option');
            opt.value = data.skill_id;
            opt.text = skillName;
            select.appendChild(opt);
            select.value = data.skill_id;
            setTimeout(() => closeAddSkillModal(), 1500);
        } else {
            document.getElementById('skillNameError').textContent = data.message || 'Failed to add skill';
            document.getElementById('skillNameError').style.display = 'block';
        }
        saveBtn.disabled = false;
        saveBtn.textContent = 'Add Skill';
    })
    .catch(() => {
        document.getElementById('skillNameError').textContent = 'An error occurred.';
        document.getElementById('skillNameError').style.display = 'block';
        saveBtn.disabled = false;
        saveBtn.textContent = 'Add Skill';
    });
}

function publishCommunity() {
    const skillId = document.getElementById('communityName').value;
    const description = document.getElementById('communityDescription').value;

    document.getElementById('nameError').textContent = '';
    document.getElementById('descriptionError').textContent = '';

    let hasError = false;
    if (!skillId) { document.getElementById('nameError').textContent = 'Please select a skill'; hasError = true; }
    if (!description.trim()) { document.getElementById('descriptionError').textContent = 'Please enter a description'; hasError = true; }
    if (hasError) return;

    closePreviewModal();

    const btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Creating...';

    fetch('<?= URLROOT ?>/communityAdmin/store', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ skill_id: skillId, description: description.trim() })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert('Error: ' + (data.errors?.[0] || 'Failed to create community'));
            btn.disabled = false;
            btn.textContent = 'Create Community';
        }
    })
    .catch(() => {
        alert('An error occurred. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Create Community';
    });
}
</script>

<?php require_once '../app/views/layouts/footer_user.php'; ?>
