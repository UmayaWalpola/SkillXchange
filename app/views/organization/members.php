<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="container org-dashboard">
        <div class="page-header">
            <div>
                <h1>Manage Team Members</h1>
                <p>Project: <strong><?= htmlspecialchars($data['project']->name) ?></strong></p>
            </div>
            <a href="<?= URLROOT ?>/organization/projects" class="btn btn-secondary">← Back to Projects</a>
        </div>

        <!-- Success/Error Messages -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Members Count -->
        <div class="members-header">
            <div class="members-count">
                <span class="count-number"><?= count($data['members']) ?></span>
                <span class="count-label">Active Members</span>
            </div>
        </div>

        <!-- Members List -->
        <div class="members-list">
            <?php if(empty($data['members'])): ?>
                <div class="card empty-card">
                    <div class="card-body">
                        <div class="empty-icon">👥</div>
                        <h3>No Members Yet</h3>
                        <p>Once you accept project applications, members will appear here for role assignment.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach($data['members'] as $member): ?>
                    <div class="card member-card">
                        <div class="card-left">
                            <?php if (!empty($member->profile_picture)): ?>
                                <img src="<?= URLROOT . '/' . $member->profile_picture ?>" alt="avatar" class="avatar" />
                            <?php else: ?>
                                <div class="avatar-initial"><?= strtoupper(substr($member->username ?? 'U', 0, 1)) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <div class="member-header">
                                <div class="member-info">
                                    <h3 class="member-name"><?= htmlspecialchars($member->username) ?></h3>
                                    <p class="member-email"><?= htmlspecialchars($member->email) ?></p>
                                </div>
                                <div class="member-stats">
                                    <span class="stat-item">
                                        <span class="icon">⭐</span> Rating: <?= $member->user_rating ?? '0' ?>
                                    </span>
                                    <span class="stat-item">
                                        <span class="icon">✓</span> Completed: <?= $member->completed_projects ?? 0 ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Skills -->
                            <?php if (!empty($member->user_skills)): ?>
                                <div class="member-skills">
                                    <strong>Skills:</strong>
                                    <div class="skills-list">
                                        <?php foreach (explode(',', $member->user_skills) as $skill): ?>
                                            <span class="skill-tag"><?= htmlspecialchars(trim($skill)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Role Assignment (form POST to updateMemberRole) -->
                            <div class="role-assignment">
                                <form method="POST" action="<?= URLROOT ?>/organization/updateMemberRole" class="role-form" style="display:flex;align-items:center;gap:10px;">
                                    <input type="hidden" name="member_id" value="<?= $member->id ?>" />
                                    <input type="hidden" name="project_id" value="<?= $data['projectId'] ?>" />

                                    <label for="role_<?= $member->id ?>" style="margin:0 6px 0 0;"><strong>Assign Role:</strong></label>

                                    <?php
                                        $presetRoles = ['Member','Designer','Developer','UI/UX','Backend Engineer','Frontend Engineer','Data Analyst','QA Tester','Project Lead'];
                                        $currentRole = $member->role ?? 'Member';
                                        $isCustom = !in_array($currentRole, $presetRoles);
                                    ?>

                                    <select name="role" class="role-select" id="role_<?= $member->id ?>">
                                        <?php foreach ($presetRoles as $r): ?>
                                            <option value="<?= htmlspecialchars($r) ?>" <?= ($currentRole === $r ? 'selected' : '') ?>><?= htmlspecialchars($r) ?></option>
                                        <?php endforeach; ?>
                                        <option value="custom" <?= ($isCustom ? 'selected' : '') ?>>Custom Role</option>
                                    </select>

                                    <input type="text" name="custom_role" class="custom-role-input" id="custom_<?= $member->id ?>" placeholder="Enter custom role..." value="<?= ($isCustom ? htmlspecialchars($currentRole) : '') ?>" style="display:<?= ($isCustom ? 'inline-block' : 'none') ?>;padding:6px;border-radius:6px;border:1px solid #e1eefb;" />

                                    <button type="submit" class="btn btn-primary">Save Role</button>
                                </form>
                            </div>

                            <!-- Owner actions: Remove / Report -->
                            <div class="member-actions" style="margin-top:10px;">
                                <button class="btn btn-danger remove-member-btn" data-member-id="<?= $member->id ?>" data-user-id="<?= $member->user_id ?>" data-project-id="<?= $data['projectId'] ?>">Remove</button>
                                <button class="btn btn-warning report-member-btn" data-member-id="<?= $member->id ?>" data-user-id="<?= $member->user_id ?>" data-project-id="<?= $data['projectId'] ?>">Report</button>
                            </div>

                            <!-- Member Metadata -->
                            <div class="member-meta">
                                <small class="muted">Joined: <?= date('M d, Y', strtotime($member->joined_at)) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    window.URLROOT = window.URLROOT || '<?= URLROOT ?>';

// Handle custom role input visibility
document.querySelectorAll('.role-select').forEach(select => {
    select.addEventListener('change', function() {
        const memberId = this.id.replace('role_', '');
        const customInput = document.getElementById('custom_' + memberId);
        
        if (this.value === 'custom') {
            customInput.style.display = 'block';
            customInput.focus();
        } else {
            customInput.style.display = 'none';
            customInput.value = '';
        }
    });
});

// Handle role update
// Note: role updates now use a POST form to /organization/updateMemberRole. The legacy AJAX handler was removed.

// Handle remove member
document.querySelectorAll('.remove-member-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const memberId = this.dataset.memberId;
        const projectId = this.dataset.projectId;
        const userId = this.dataset.userId;

        if (!confirm('Are you sure you want to remove this member from the project? This cannot be undone.')) return;

        const formData = new FormData();
        formData.append('member_id', memberId);
        formData.append('project_id', projectId);

        // Disable button while processing
        btn.disabled = true;

        fetch(URLROOT + '/organization/removeMember', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Remove the member card from DOM
                const card = btn.closest('.member-card');
                if (card) card.remove();
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to remove member');
            btn.disabled = false;
        });
    });
});

// Handle report member
document.querySelectorAll('.report-member-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const memberId = this.dataset.memberId;
        const projectId = this.dataset.projectId;
        const userId = this.dataset.userId;

        const reason = prompt('Enter a short reason for reporting this user (required):');
        if (!reason || !reason.trim()) {
            alert('Report reason is required');
            return;
        }

        const details = prompt('Optional: add additional details (leave blank if none):') || '';

        const formData = new FormData();
        formData.append('project_id', projectId);
        formData.append('reported_user_id', userId);
        formData.append('reason', reason.trim());
        formData.append('details', details.trim());

        btn.disabled = true;

        fetch(URLROOT + '/organization/reportUser', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Report submitted');
            } else {
                alert('Error: ' + data.message);
            }
            btn.disabled = false;
        })
        .catch(err => {
            console.error(err);
            alert('Failed to submit report');
            btn.disabled = false;
        });
    });
});

// Utility function to escape HTML
function htmlEscape(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
