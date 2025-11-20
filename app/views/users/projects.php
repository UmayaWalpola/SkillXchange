<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/projects.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        
        <!-- Projects List Page -->
        <div id="projectsListPage" class="projects-section">
            <div class="projects-header">
                <div class="page-header">
                    <h1>My Projects</h1>
                    <p>Collaborate with others on real-world projects and build experience</p>
                </div>
            </div>
            
            <div class="projects-filters">
                <input type="text" id="searchProjects" placeholder="Search projects..." class="search-box" />
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterProjects('all')">All Projects</button>
                    <button class="filter-btn" onclick="filterProjects('active')">Active</button>
                    <button class="filter-btn" onclick="filterProjects('in-progress')">In Progress</button>
                    <button class="filter-btn" onclick="filterProjects('completed')">Completed</button>
                </div>
            </div>
            
            <div class="projects-container" id="projectsContainer">
                <?php 
                    $projectsList = isset($projects) ? $projects : [];
                    if(!empty($projectsList)): 
                ?>
                    <?php foreach ($projectsList as $project): ?>
                        <?php 
                            if (is_array($project)) $project = (object)$project;
                            
                            $icons = [
                                'web' => '💻',
                                'mobile' => '📱',
                                'data' => '📊',
                                'design' => '🎨',
                                'other' => '📁'
                            ];
                            $icon = $icons[$project->category] ?? '📁';
                            $categoryClass = $project->category ?? 'other';
                        ?>
                        <div class="project-card" data-status="<?= htmlspecialchars($project->status) ?>">
                            <div class="project-banner <?= htmlspecialchars($categoryClass) ?>">
                                <?= $icon ?>
                            </div>
                            <span class="status-badge <?= htmlspecialchars($project->status) ?>">
                                <?= ucfirst(str_replace('-', ' ', $project->status)) ?>
                            </span>
                            <div class="project-content">
                                <h3 class="project-title"><?= htmlspecialchars($project->name) ?></h3>
                                <p class="project-description"><?= htmlspecialchars(substr($project->description, 0, 120)) ?><?= strlen($project->description) > 120 ? '...' : '' ?></p>
                                <div class="project-meta">
                                    <span class="project-category"><?= ucfirst(htmlspecialchars($project->category)) ?></span>
                                    <span><?= intval($project->current_members ?? 0) ?>/<?= htmlspecialchars($project->max_members) ?> Members</span>
                                </div>
                                <div class="project-skills">
                                    <?php 
                                        $skills = array_slice(explode(',', $project->required_skills), 0, 3);
                                        foreach ($skills as $skill): 
                                    ?>
                                        <span class="skill-tag"><?= htmlspecialchars(trim($skill)) ?></span>
                                    <?php endforeach; ?>
                                    <?php if(count(explode(',', $project->required_skills)) > 3): ?>
                                        <span class="skill-tag">+<?= count(explode(',', $project->required_skills)) - 3 ?> more</span>
                                    <?php endif; ?>
                                </div>
                                <div class="project-footer">
                                    <span class="members-count"><?= intval($project->current_members ?? 0) ?> Members</span>
                                    <a href="<?= URLROOT ?>/project/detail/<?= htmlspecialchars($project->id) ?>" class="view-details-btn">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 3rem 1rem; color: #666;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📁</div>
                        <h3 style="color: #333; margin-bottom: 0.5rem;">No Projects Yet</h3>
                        <p>You haven't joined any projects yet. Browse available projects or ask your organization to invite you!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
</main>

<script>
function filterProjects(status) {
    const cards = document.querySelectorAll('.project-card');
    const buttons = document.querySelectorAll('.filter-btn');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    cards.forEach(card => {
        if (status === 'all' || card.dataset.status === status) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

document.getElementById('searchProjects').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const cards = document.querySelectorAll('.project-card');
    
    cards.forEach(card => {
        const title = card.querySelector('.project-title').textContent.toLowerCase();
        const desc = card.querySelector('.project-description').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || desc.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
