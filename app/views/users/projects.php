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
                    <h1>Active Projects</h1>
                    <p>Collaborate with others on real-world projects and build experience</p>
                </div>
                <button class="create-project-btn">+ Create Project</button>
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
                <?php foreach ($data['projects'] as $project): ?>
                    <div class="project-card" data-status="<?= $project['status']; ?>">
                        <div class="project-banner <?= $project['categoryClass']; ?>">
                            <?= $project['icon']; ?>
                        </div>
                        <span class="status-badge <?= $project['status']; ?>">
                            <?= strtoupper($project['status']); ?>
                        </span>
                        <div class="project-content">
                            <h3 class="project-title"><?= htmlspecialchars($project['title']); ?></h3>
                            <p class="project-description"><?= htmlspecialchars($project['description']); ?></p>
                            <div class="project-meta">
                                <span class="project-category"><?= htmlspecialchars($project['category']); ?></span>
                                <span>by <?= htmlspecialchars($project['creator']); ?></span>
                            </div>
                            <div class="project-skills">
                                <?php foreach (array_slice($project['skills'], 0, 3) as $skill): ?>
                                    <span class="skill-tag"><?= htmlspecialchars($skill); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="project-footer">
                                <span class="members-count"><?= $project['totalMembers']; ?> Members</span>
                                <button class="view-details-btn" onclick='showDetail(<?= json_encode($project); ?>)'>
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Project Detail Page -->
        <div id="projectDetailPage" class="project-detail">
            <button class="back-btn" onclick="goBack()">← Back to Projects</button>
            
            <div class="project-header" id="projectHeader"></div>
            
            <div class="detail-content">
                <div class="detail-main">
                    <section>
                        <h2>Project Overview</h2>
                        <p id="projectOverview"></p>
                    </section>
                    
                    <section>
                        <h2>Goals & Objectives</h2>
                        <ul class="goals-list" id="goalsList"></ul>
                    </section>
                    
                    <section>
                        <h2>Skills Required</h2>
                        <div id="skillsList" style="display: flex; gap: 0.8rem; flex-wrap: wrap;"></div>
                    </section>
                    
                    <section>
                        <h2>Project Progress</h2>
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <p id="progressText"></p>
                    </section>
                </div>
                
                <div class="detail-sidebar">
                    <div class="card">
                        <h3>Project Lead</h3>
                        <div class="lead-info" id="leadInfo"></div>
                    </div>
                    
                    <div class="card">
                        <h3>Team Members</h3>
                        <div id="teamList"></div>
                        <button class="join-btn" onclick="joinProject()">+ Join Project</button>
                    </div>
                    
                    <div class="card">
                        <h3>Resources</h3>
                        <ul class="resources-list" id="resourcesList"></ul>
                    </div>
                    
                    <div class="card">
                        <div style="display: flex; justify-content: space-between;">
                            <div>
                                <div style="color: #999; font-size: 0.85rem; margin-bottom: 0.5rem;">Created</div>
                                <div id="createdDate" style="font-size: 1.1rem; font-weight: 600; color: #1a1a1a;"></div>
                            </div>
                            <div>
                                <div style="color: #999; font-size: 0.85rem; margin-bottom: 0.5rem;">Deadline</div>
                                <div id="deadlineDate" style="font-size: 1.1rem; font-weight: 600; color: #1a1a1a;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</main>

<script src="<?= URLROOT ?>/assets/js/projects.js" defer></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>