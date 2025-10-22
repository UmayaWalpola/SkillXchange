<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/profile.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/view_profile.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        
        <!-- Back Button -->
        <div class="back-button-container">
            <a href="<?= URLROOT ?>/userdashboard/matches" class="btn-back">
                ← Back to Matches
            </a>
        </div>

        <div class="profile-content">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-info">
                    <div class="profile-avatar"><?= strtoupper(substr($data['user']['name'], 0, 2)); ?></div>
                    <div class="profile-details">
                        <h1><?= htmlspecialchars($data['user']['name']); ?></h1>
                        <p class="profile-username">@<?= htmlspecialchars($data['user']['username']); ?></p>
                        <p class="profile-bio">
                            <?= htmlspecialchars($data['user']['bio']); ?>
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
                </div>
                
                <!-- Action Buttons -->
                <div class="profile-actions">
                    <button class="btn-action btn-primary" onclick="sendConnectionRequest('<?= htmlspecialchars($data['user']['name']); ?>')">
                        Connect
                    </button>
                    <button class="btn-action btn-secondary" onclick="sendMessage()">
                        Send Message
                    </button>
                </div>
            </div>

            <!-- Profile Body -->
            <div class="profile-body">
                <!-- Skills Section -->
                <section class="profile-section">
                    <div class="section-header">
                        <h2 class="section-title">Skills & Expertise</h2>
                    </div>
                    
                    <!-- Teaching Skills -->
                    <?php if (!empty($data['skills']['teaches'])): ?>
                    <div class="skills-category-section">
                        <h3 class="skills-category-title">Teaching Skills</h3>
                        <div class="skills-container">
                            <?php foreach ($data['skills']['teaches'] as $skill): ?>
                                <div class="skill-item">
                                    <div class="skill-name"><?= htmlspecialchars($skill['name']); ?></div>
                                    <span class="badge badge-<?= strtolower($skill['level']); ?>">
                                        <?= htmlspecialchars($skill['level']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Learning Skills -->
                    <?php if (!empty($data['skills']['learns'])): ?>
                    <div class="skills-category-section">
                        <h3 class="skills-category-title">Learning Skills</h3>
                        <div class="skills-container">
                            <?php foreach ($data['skills']['learns'] as $skill): ?>
                                <div class="skill-item">
                                    <div class="skill-name"><?= htmlspecialchars($skill['name']); ?></div>
                                    <span class="badge badge-<?= strtolower($skill['level']); ?>">
                                        <?= htmlspecialchars($skill['level']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </section>

                <!-- Projects Section -->
                <section class="profile-section">
                    <div class="section-header">
                        <h2 class="section-title">Projects</h2>
                    </div>
                    
                    <div class="projects-grid">
                        <?php if (!empty($data['projects']['completed'])): ?>
                            <?php foreach ($data['projects']['completed'] as $project): ?>
                                <div class="project-card">
                                    <span class="project-status status-completed">Completed</span>
                                    <h3 class="project-title"><?= htmlspecialchars($project['title']); ?></h3>
                                    <p class="project-description">
                                        <?= htmlspecialchars($project['description']); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($data['projects']['in_progress'])): ?>
                            <?php foreach ($data['projects']['in_progress'] as $project): ?>
                                <div class="project-card">
                                    <span class="project-status status-progress">In Progress</span>
                                    <h3 class="project-title"><?= htmlspecialchars($project['title']); ?></h3>
                                    <p class="project-description">
                                        <?= htmlspecialchars($project['description']); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Feedback Section -->
                <section class="profile-section">
                    <div class="section-header">
                        <h2 class="section-title">Reviews & Feedback</h2>
                    </div>
                    
                    <div class="feedback-summary">
                        <div class="rating-score"><?= $data['user']['rating']; ?></div>
                        <div class="stars">★★★★★</div>
                        <div>Based on <?= $data['user']['reviews_count']; ?> reviews</div>
                    </div>

                    <div class="feedback-grid">
                        <?php if (!empty($data['feedback'])): ?>
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
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>

    </div>
</div>
</main>

<script src="<?= URLROOT ?>/assets/js/view_profile.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>