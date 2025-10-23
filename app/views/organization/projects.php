<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="projects-container">
        
        <!-- Page Header -->
        <div class="page-header">
                <h1>My Projects</h1>
                <p>Manage and track all your organization's projects</p>
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
            </select>

            <select class="filter-select" id="categoryFilter">
                <option value="all">All Categories</option>
                <option value="web">Web Development</option>
                <option value="mobile">Mobile Development</option>
                <option value="data">Data Science</option>
                <option value="design">Design</option>
            </select>
        </div>

        <!-- Projects Grid -->
        <div class="projects-grid" id="projectsGrid">
            <!-- Projects will be loaded here dynamically -->
            
            <!-- Sample Project Card (for demo - will be replaced by PHP loop) -->
            <?php if(!empty($data['projects'])): ?>
                <?php foreach($data['projects'] as $project): ?>
                    <div class="project-card">
                        <div class="project-header <?= $project['category'] ?>">
                            <div class="project-icon">
                                <?php 
                                    $icons = [
                                        'web' => '💻',
                                        'mobile' => '📱',
                                        'data' => '📊',
                                        'design' => '🎨'
                                    ];
                                    echo $icons[$project['category']] ?? '💻';
                                ?>
                            </div>
                            <span class="status-badge <?= $project['status'] ?>">
                                <?= ucfirst($project['status']) ?>
                            </span>
                        </div>
                        
                        <div class="project-content">
                            <h3 class="project-title"><?= htmlspecialchars($project['name']) ?></h3>
                            <p class="project-description"><?= htmlspecialchars($project['description']) ?></p>
                            
                            <div class="project-meta">
                                <span class="meta-item">
                                    <span class="meta-icon">📂</span>
                                    <?= ucfirst($project['category']) ?>
                                </span>
                                <span class="meta-item">
                                    <span class="meta-icon">👥</span>
                                    <?= $project['current_members'] ?>/<?= $project['max_members'] ?> Members
                                </span>
                            </div>

                            <div class="project-skills">
                                <?php 
                                $skills = explode(',', $project['required_skills']);
                                foreach($skills as $skill): 
                                ?>
                                    <span class="skill-tag"><?= trim($skill) ?></span>
                                <?php endforeach; ?>
                            </div>

                            <div class="project-footer">
                                <span class="project-date">
                                    Created: <?= date('M d, Y', strtotime($project['created_at'])) ?>
                                </span>
                                <div class="project-actions">
                                    <button class="action-btn view-btn" onclick="viewProject(<?= $project['id'] ?>)">View</button>
                                    <button class="action-btn edit-btn" onclick="editProject(<?= $project['id'] ?>)">Edit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <h3>No Projects Found</h3>
                    <p>Create your first project to start collaborating!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="<?= URLROOT ?>/assets/js/organizations.js" defer></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>