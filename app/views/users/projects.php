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
                    <button class="filter-btn" onclick="filterProjects('open')">Open</button>
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
                            
                            $statusClass = strtolower(str_replace('_', '-', $project->status));
                            $roleColor = $project->member_role === 'leader' ? '#10b981' : '#3b82f6';
                        ?>
                        <div class="project-card" data-status="<?= htmlspecialchars($statusClass) ?>">
                            <div class="project-banner" style="display: flex; align-items: center; justify-content: center;">
                                <i class="ph ph-folder-open" style="font-size: 2rem;"></i>
                            </div>
                            <span class="status-badge <?= htmlspecialchars($statusClass) ?>">
                                <?= ucfirst(str_replace('_', ' ', $project->status)) ?>
                            </span>
                            <div class="project-content">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <h3 class="project-title"><?= htmlspecialchars($project->name) ?></h3>
                                    <span style="background: <?= $roleColor ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                                        <?= ucfirst($project->member_role) ?>
                                    </span>
                                </div>
                                <p class="project-description"><?= htmlspecialchars(substr($project->description, 0, 120)) ?><?= strlen($project->description) > 120 ? '...' : '' ?></p>
                                
                                <div class="project-meta" style="margin-top: 1rem;">
                                    <span><i class="ph ph-users"></i> <?= $project->team_size ?> Members</span>
                                    <span><i class="ph ph-calendar"></i> Created: <?= date('M Y', strtotime($project->created_at)) ?></span>
                                </div>
                                
                                <div class="project-footer" style="margin-top: 1.5rem;">
                                    <span style="font-size: 0.875rem; color: #888;">
                                        Joined: <?= date('M d, Y', strtotime($project->joined_at)) ?>
                                    </span>
                                    <div style="display:flex; gap:8px;">
                                        <a href="<?= URLROOT ?>/project/detail/<?= htmlspecialchars($project->id) ?>" class="view-details-btn">
                                            View Details
                                        </a>
                                        <a href="<?= URLROOT ?>/userdashboard/chats?partnerId=<?= htmlspecialchars($project->id) ?>" class="view-details-btn" style="background:#0ea5e9;border-color:#0ea5e9;">
                                            <i class="ph ph-chat-circle-dots"></i> Chat
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 3rem 1rem; color: #666;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;"><i class="ph ph-folder-open" style="font-size: 4rem; color: #ccc;"></i></div>
                        <h3 style="color: #333; margin-bottom: 0.5rem;">No Projects Yet</h3>
                        <p>You haven't joined any projects yet. Check available projects or wait for an organization to invite you!</p>
                        <a href="<?= URLROOT ?>/project/browse" style="display: inline-block; margin-top: 1rem; padding: 0.75rem 1.5rem; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                            Browse Projects
                        </a>
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
