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

                            <div class="project-footer">
                                <span class="project-date">
                                    Created: <?= date('M d, Y', strtotime($project->created_at)) ?>
                                </span>
                                <div class="project-actions">
                                    <button class="action-btn edit-btn" onclick="editProject(<?= $project->id ?>)">Edit</button>
                                    <button class="action-btn delete-btn"
                                        onclick="deleteProject(<?= $project->id ?>, '<?= htmlspecialchars($project->name, ENT_QUOTES) ?>')">
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
// URLROOT for JS
const URLROOT = '<?= URLROOT ?>';

function editProject(id) {
    window.location.href = URLROOT + '/organization/editProject/' + id;
}

function deleteProject(id, name) {
    if (!confirm(`Are you sure you want to delete "${name}"?`)) return;

    let form = new FormData();
    form.append('project_id', id);

    fetch(URLROOT + '/organization/deleteProject', {
        method: 'POST',
        body: form
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
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
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
