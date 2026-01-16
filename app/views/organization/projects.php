<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="projects-container">
        
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
        
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>My Projects</h1>
                <p>Manage and track all your organization's projects</p>
            </div>
            <button class="create-btn" onclick="window.location.href='<?= URLROOT ?>/organization/createProject'">
                + Create New Project
            </button>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <input type="text" class="search-input" placeholder="Search projects..." id="searchInput">
            
            <select class="filter-select" id="statusFilter">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="in-progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <select class="filter-select" id="categoryFilter">
                <option value="all">All Categories</option>
                <option value="web">Web Development</option>
                <option value="mobile">Mobile Development</option>
                <option value="data">Data Science</option>
                <option value="design">Design</option>
                <option value="other">Other</option>
            </select>
        </div>

        <!-- Projects Grid -->
        <div class="projects-grid" id="projectsGrid">
            <?php if(!empty($data['projects'])): ?>
                <?php foreach($data['projects'] as $project): ?>
                    <?php if (is_array($project)) $project = (object)$project; ?>

                    <div class="project-card" 
                         data-status="<?= $project->status ?>" 
                         data-category="<?= $project->category ?>">

                        <div class="project-header <?= $project->category ?>">
                            <div class="project-icon">
                                <?php 
                                    $icons = [
                                        'web' => '💻',
                                        'mobile' => '📱',
                                        'data' => '📊',
                                        'design' => '🎨',
                                        'other' => '📁'
                                    ];
                                    echo $icons[$project->category] ?? '📁';
                                ?>
                            </div>
                            <span class="status-badge <?= $project->status ?>">
                                <?= ucfirst(str_replace('-', ' ', $project->status)) ?>
                            </span>
                        </div>
                        
                        <div class="project-content">
                            <h3 class="project-title"><?= htmlspecialchars($project->name) ?></h3>
                            <p class="project-description">
                                <?= htmlspecialchars(substr($project->description, 0, 120)) ?>
                                <?= strlen($project->description) > 120 ? '...' : '' ?>
                            </p>
                            
                            <div class="project-meta">
                                <span class="meta-item">📂 <?= ucfirst($project->category) ?></span>
                                <span class="meta-item">👥 <?= $project->current_members ?? 0 ?>/<?= $project->max_members ?> Members</span>
                            </div>

                            <div class="project-skills">
                                <?php 
                                    $skills = explode(',', $project->required_skills);
                                    foreach(array_slice($skills, 0, 3) as $skill): 
                                ?>
                                    <span class="skill-tag"><?= trim(htmlspecialchars($skill)) ?></span>
                                <?php endforeach; ?>
                                <?php if(count($skills) > 3): ?>
                                    <span class="skill-tag">+<?= count($skills) - 3 ?> more</span>
                                <?php endif; ?>
                            </div>

                            <!-- Project Progress Metrics -->
                            <?php if(isset($project->metrics) && $project->metrics): 
                                $metrics = $project->metrics;
                                $totalTasks = (int)($metrics->total_tasks ?? 0);
                                $completedTasks = (int)($metrics->completed_tasks ?? 0);
                                $overdueTasks = (int)($metrics->overdue_tasks ?? 0);
                                $completionPct = $metrics->completion_percentage ?? 0;
                            ?>
                            <div class="project-progress-section">
                                <div class="progress-header">
                                    <span class="progress-label">Progress</span>
                                    <span class="progress-percentage"><?= number_format($completionPct, 1) ?>%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill" style="width: <?= $completionPct ?>%"></div>
                                </div>
                                <div class="progress-metrics-mini">
                                    <span class="metric-mini">✅ <?= $completedTasks ?>/<?= $totalTasks ?> Tasks</span>
                                    <?php if($overdueTasks > 0): ?>
                                        <span class="metric-mini overdue">⚠️ <?= $overdueTasks ?> Overdue</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="project-footer">
                                <span class="project-date">
                                    Created: <?= date('M d, Y', strtotime($project->created_at)) ?>
                                </span>
                                <div class="project-actions">
                                    <button class="action-btn members-btn" onclick="manageMembers(<?= $project->id ?>)">👥 Members</button>
                                    <button class="action-btn chat-open-btn" onclick="openChat(<?= $project->id ?>)">💬 Chat</button>
                                    <button class="action-btn edit-btn" onclick="editProject(<?= $project->id ?>)">Edit</button>
                                    <button class="action-btn delete-btn"
                                        data-project-id="<?= $project->id ?>"
                                        data-project-name="<?= htmlspecialchars($project->name, ENT_QUOTES) ?>"
                                        onclick="deleteProject(this, <?= $project->id ?>, '<?= htmlspecialchars($project->name, ENT_QUOTES) ?>')">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📁</div>
                    <h3>No Projects Found</h3>
                    <p>Create your first project to start collaborating!</p>
                    <button class="create-btn" onclick="window.location.href='<?= URLROOT ?>/organization/createProject'">
                        + Create Your First Project
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// Ensure URLROOT exists (set in header)
window.URLROOT = window.URLROOT || '<?= URLROOT ?>';

function manageMembers(id) {
    window.location.href = URLROOT + '/organization/members/' + id;
}

function editProject(id) {
    window.location.href = URLROOT + '/organization/editProject/' + id;
}

function openChat(id) {
    window.location.href = URLROOT + '/chat/index/' + id;
}

function deleteProject(btn, id, name) {
    if (!confirm(`Are you sure you want to delete "${name}"?`)) return;

    let form = new FormData();
    form.append('project_id', id);

    if (btn) btn.disabled = true;

    fetch(URLROOT + '/organization/deleteProject', {
        method: 'POST',
        body: form,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async res => {
        let text = await res.text();
        try { return JSON.parse(text); } catch (e) { throw new Error('Invalid JSON response: ' + text); }
    })
    .then(data => {
        alert(data.message || 'Done');
        if (data.success) {
            // remove project card from DOM if possible
            try {
                const card = btn.closest('.project-card');
                if (card) card.remove();
                else location.reload();
            } catch (e) { location.reload(); }
        } else {
            if (btn) btn.disabled = false;
        }
    })
    .catch(err => {
        console.error('Delete error:', err);
        alert('Error deleting project: ' + err.message);
        if (btn) btn.disabled = false;
    });
}

function filterProjects() {
    let search = document.getElementById('searchInput').value.toLowerCase();
    let status = document.getElementById('statusFilter').value;
    let category = document.getElementById('categoryFilter').value;

    document.querySelectorAll('.project-card').forEach(card => {
        let title = card.querySelector('.project-title').textContent.toLowerCase();
        let desc = card.querySelector('.project-description').textContent.toLowerCase();
        
        let matchesSearch = title.includes(search) || desc.includes(search);
        let matchesStatus = (status === 'all') || (card.dataset.status === status);
        let matchesCategory = (category === 'all') || (card.dataset.category === category);

        card.style.display = (matchesSearch && matchesStatus && matchesCategory)
            ? "block" 
            : "none";
    });
}

document.getElementById('searchInput').addEventListener('input', filterProjects);
document.getElementById('statusFilter').addEventListener('change', filterProjects);
document.getElementById('categoryFilter').addEventListener('change', filterProjects);

// Delegated delete handler (works if inline handlers fail)
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.delete-btn');
    if (!btn) return;

    // Prevent double-binding if inline handler also runs
    e.preventDefault();

    const projectId = btn.getAttribute('data-project-id');
    const projectName = btn.getAttribute('data-project-name') || 'this project';

    console.log('Delete clicked for', projectId, projectName);

    // Reuse the existing deleteProject function if available
    if (typeof deleteProject === 'function') {
        try {
            deleteProject(btn, projectId, projectName);
            return;
        } catch (err) {
            console.warn('deleteProject threw, falling back to inline fetch', err);
        }
    }

    // Fallback: perform fetch here
    if (!confirm(`Are you sure you want to delete "${projectName}"?`)) return;

    btn.disabled = true;
    const form = new FormData();
    form.append('project_id', projectId);

    fetch(URLROOT + '/organization/deleteProject', {
        method: 'POST',
        body: form,
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(async res => {
        const text = await res.text();
        try { return JSON.parse(text); } catch(e) { throw new Error('Invalid JSON: ' + text); }
    })
    .then(data => {
        console.log('Delete response:', data);
        alert(data.message || 'Done');
        if (data.success) {
            const card = btn.closest('.project-card');
            if (card) card.remove(); else location.reload();
        } else {
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error('Delete error fallback:', err);
        alert('Error deleting project: ' + err.message);
        btn.disabled = false;
    });
});
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
