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
                    <?php
                    // Convert array to object if needed
                    if (is_array($project)) {
                        $project = (object)$project;
                    }
                    ?>
                    <div class="project-card" data-status="<?= $project->status ?>" data-category="<?= $project->category ?>">
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
                                <span class="meta-item">
                                    <span class="meta-icon">📂</span>
                                    <?= ucfirst($project->category) ?>
                                </span>
                                <span class="meta-item">
                                    <span class="meta-icon">👥</span>
                                    <?= $project->current_members ?? 0 ?>/<?= $project->max_members ?> Members
                                </span>
                            </div>

                            <div class="project-skills">
                                <?php 
                                $skills = explode(',', $project->required_skills);
                                $displaySkills = array_slice($skills, 0, 3);
                                foreach($displaySkills as $skill): 
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
                                    <!--<button class="action-btn view-btn" onclick="viewProject(<?= $project->id ?>)">
                                        View
                                    </button> -->
                                    <button class="action-btn edit-btn" onclick="editProject(<?= $project->id ?>)">
                                         Edit
                                    </button>
                                    <button class="action-btn delete-btn" onclick="deleteProject(<?= $project->id ?>, '<?= htmlspecialchars($project->name, ENT_QUOTES) ?>')">
                                         Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty State -->
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
// Define URLROOT for JavaScript
const URLROOT = '<?= URLROOT ?>';

// View project
function viewProject(projectId) {
    window.location.href = URLROOT + '/organization/viewProject/' + projectId;
}

// Edit project
function editProject(projectId) {
    window.location.href = URLROOT + '/organization/editProject/' + projectId;
}

// Delete project
function deleteProject(projectId, projectName) {
    if (confirm(`Are you sure you want to delete "${projectName}"? This action cannot be undone.`)) {
        const formData = new FormData();
        formData.append('project_id', projectId);
        
        fetch(URLROOT + '/organization/deleteProject', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the project');
        });
    }
}

// Filter projects
function filterProjects() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const categoryFilter = document.getElementById('categoryFilter').value;
    
    const projectCards = document.querySelectorAll('.project-card');
    let visibleCount = 0;
    
    projectCards.forEach(card => {
        const title = card.querySelector('.project-title').textContent.toLowerCase();
        const description = card.querySelector('.project-description').textContent.toLowerCase();
        const status = card.dataset.status;
        const category = card.dataset.category;
        
        const matchesSearch = title.includes(searchInput) || description.includes(searchInput);
        const matchesStatus = statusFilter === 'all' || status === statusFilter;
        const matchesCategory = categoryFilter === 'all' || category === categoryFilter;
        
        if (matchesSearch && matchesStatus && matchesCategory) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show/hide empty state
    const emptyState = document.querySelector('.empty-state');
    const projectsGrid = document.getElementById('projectsGrid');
    
    if (visibleCount === 0 && projectCards.length > 0) {
        if (!document.querySelector('.no-results')) {
            const noResults = document.createElement('div');
            noResults.className = 'empty-state no-results';
            noResults.innerHTML = `
                <div class="empty-icon">🔍</div>
                <h3>No Projects Match Your Filters</h3>
                <p>Try adjusting your search criteria</p>
            `;
            projectsGrid.appendChild(noResults);
        }
    } else {
        const noResults = document.querySelector('.no-results');
        if (noResults) noResults.remove();
    }
}

// Add event listeners
document.getElementById('searchInput').addEventListener('input', filterProjects);
document.getElementById('statusFilter').addEventListener('change', filterProjects);
document.getElementById('categoryFilter').addEventListener('change', filterProjects);

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
</script>

<style>
.alert {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-weight: 500;
    transition: opacity 0.3s ease;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    grid-column: 1 / -1;
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #333;
    margin-bottom: 10px;
}

.empty-state p {
    color: #666;
    margin-bottom: 20px;
}

.delete-btn {
    background-color: #dc3545;
    color: white;
}

.delete-btn:hover {
    background-color: #c82333;
}
</style>

<?php require_once "../app/views/layouts/footer_user.php"; ?>

<h1>My Projects</h1>

<a href="/SkillXchange/public/ProjectController/create">Create New Project</a>

<?php foreach ($data['projects'] as $p): ?>
    <div style="border:1px solid #ccc;padding:10px;margin:10px 0">
        <h3><?= $p->name ?></h3>
        <p><?= $p->description ?></p>

        <a href="/SkillXchange/public/ProjectController/edit/<?= $p->id ?>">Edit</a>
        <a href="/SkillXchange/public/ProjectController/delete/<?= $p->id ?>">Delete</a>
    </div>
<?php endforeach; ?>
