<?php require_once "../app/views/layouts/header_user.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/profile.css">

<div class="dashboard-container">
    <?php require_once "../app/views/layouts/usersidebar.php"; ?>

    <main class="dashboard-main">
        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Profile Header -->
            <div class="profile-header">
                <button class="edit-profile-btn">Edit details</button>
                <div class="profile-info">
                    <!-- FIXED: Profile Avatar with Image Support -->
<div class="profile-avatar">
    <?php if (!empty($data['user']['avatar']) && !ctype_upper($data['user']['avatar'])): ?>
        <!-- Show profile picture if exists -->
        <img src="<?= URLROOT ?>/<?= htmlspecialchars($data['user']['avatar']) ?>" 
             alt="<?= htmlspecialchars($data['user']['name']) ?>" 
             style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
    <?php else: ?>
        <!-- Show initials as fallback -->
        <?= htmlspecialchars($data['user']['avatar']) ?>
    <?php endif; ?>
</div>
                    <div class="profile-details">
                        <h1><?= htmlspecialchars($user['name']); ?></h1>
                        <p class="profile-username">@<?= htmlspecialchars($user['username']); ?></p>
                        <p class="profile-bio">
                            <?= htmlspecialchars($user['bio']); ?>
                        </p>
                    </div>
                </div>
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?= $data['user']['connections']; ?></span>
                        <span class="stat-label">Connections</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $data['user']['skills_taught']; ?></span>
                        <span class="stat-label">Skills Taught</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $data['user']['skills_learning']; ?></span>
                        <span class="stat-label">Skills Learning</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $data['user']['hours_exchanged']; ?></span>
                        <span class="stat-label">Hours Exchanged</span>
                    </div>
                </div>

                  <!-- Badges Section -->
                <section class="profile-section">
                    <div class="section-header">
                        <h2 class="section-title">Achievements & Badges</h2>
                        <a href="#" class="view-all-btn">View all badges</a>
                    </div>
                    <div class="badges-grid">
                        <?php foreach ($data['badges'] as $badge): ?>
                            <div class="badge-item">
                                <div class="badge-icon"><?= $badge['icon']; ?></div>
                                <div class="badge-name"><?= htmlspecialchars($badge['name']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>


            <!-- Profile Body -->
            <div class="profile-body">
                <!-- Skills Section -->
                <section class="profile-section">
                    <div class="section-header">
                        <h2 class="section-title">Skills & Expertise</h2>
                        <a href="#" class="view-all-btn">View all skills</a>
                    </div>
                    <div class="skills-grid">
                        <div class="skills-category">
                            <h3>Teaches:</h3>
                            <div class="skills-list">
                                <?php foreach ($data['skills']['teaches'] as $skill): ?>
                                    <span class="skill-tag"><?= htmlspecialchars($skill); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="skills-category">
                            <h3>Learns:</h3>
                            <div class="skills-list">
                                <?php foreach ($data['skills']['learns'] as $skill): ?>
                                    <span class="skill-tag learning"><?= htmlspecialchars($skill); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Projects Section -->
                <section class="profile-section">
                    <div class="section-header">
                        <h2 class="section-title">Projects</h2>
                        <a href="#" class="view-all-btn">View all projects</a>
                    </div>
                    
                    <div class="project-toggle">
                        <button class="toggle-btn active" onclick="showProjects('completed')">Completed</button>
                        <button class="toggle-btn" onclick="showProjects('progress')">In Progress</button>
                    </div>

                    <div id="completed-projects" class="projects-grid">
                        <?php foreach ($data['projects']['completed'] as $project): ?>
                            <div class="project-card">
                                <span class="project-status status-completed">Completed</span>
                                <h3 class="project-title"><?= htmlspecialchars($project['title']); ?></h3>
                                <p class="project-description">
                                    <?= htmlspecialchars($project['description']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="progress-projects" class="projects-grid" style="display: none;">
                        <?php foreach ($data['projects']['in_progress'] as $project): ?>
                            <div class="project-card">
                                <span class="project-status status-progress">In Progress</span>
                                <h3 class="project-title"><?= htmlspecialchars($project['title']); ?></h3>
                                <p class="project-description">
                                    <?= htmlspecialchars($project['description']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Feedback Section -->
                <section class="profile-section">
                    <div class="section-header">
                        <h2 class="section-title">Reviews & Feedback</h2>
                        <a href="#" class="view-all-btn">View all feedback</a>
                    </div>
                    
                    <div class="feedback-summary">
                        <div class="rating-score"><?= $data['user']['rating']; ?></div>
                        <div class="stars">★★★★★</div>
                        <div>Based on <?= $data['user']['reviews_count']; ?> reviews</div>
                    </div>

                    <div class="feedback-grid">
                        <?php foreach ($data['feedback'] as $feedback): ?>
                            <div class="feedback-item">
                                <div class="feedback-header">
                                    <span class="reviewer-name"><?= htmlspecialchars($feedback['reviewer_name']); ?></span>
                                    <span class="review-date"><?= htmlspecialchars($feedback['date']); ?></span>
                                </div>
                                <div class="stars"><?= str_repeat('★', $feedback['rating']); ?></div>
                                <p class="feedback-text">
                                    <?= htmlspecialchars($feedback['comment']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

            </div>
        </div>
    </main>
</div>

<script src="<?= URLROOT ?>/assets/js/profile.js" defer></script>

<?php require_once "../app/views/layouts/footer.php"; ?>