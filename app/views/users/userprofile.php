<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/profile.css">

<main class="site-main">
<div class="dashboard-container">

    <div class="dashboard-main">
        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Profile Header -->
            <div class="profile-header">
                <?php if ($data['is_own_profile']): ?>
                    <a href="<?= URLROOT ?>/users/editProfile" class="edit-profile-btn">Edit details</a>
                <?php endif; ?>
                
                <?php
                    $profileImagePath = ltrim((string) ($data['user']['profile_picture'] ?? $data['user']['avatar'] ?? ''), '/');
                    $hasProfileImage = $profileImagePath !== '' && strpos($profileImagePath, 'uploads/') === 0;
                ?>
                <div class="profile-info">
                    <div class="profile-avatar">
                        <?php if ($hasProfileImage): ?>
                            <img
                                src="<?= URLROOT ?>/<?= htmlspecialchars($profileImagePath) ?>"
                                alt="<?= htmlspecialchars($data['user']['name']); ?> profile picture"
                                class="profile-avatar-image"
                            >
                        <?php else: ?>
                            <?= htmlspecialchars($data['user']['avatar']); ?>
                        <?php endif; ?>
                    </div>
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
            </div>

            <!-- Success Message -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <?= $_SESSION['success']; ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Body -->
            <div class="profile-body">
                <!-- Skills Section - SPLIT INTO TWO -->
                <section class="profile-section">
                    <div class="section-header">
                        <h2 class="section-title">Skills & Expertise</h2>
                        <a href="#" class="view-all-btn">View all skills</a>
                    </div>
                    
                    <!-- Teaching Skills -->
                    <div class="skills-category-section">
                        <h3 class="skills-category-title">Teaching Skills</h3>
                        <div class="skills-container">
                            <?php if (!empty($data['skills']['teaches'])): ?>
                                <?php foreach ($data['skills']['teaches'] as $skill): ?>
                                    <div class="skill-item">
                                        <div class="skill-name"><?= htmlspecialchars($skill); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No teaching skills added yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Learning Skills -->
                    <div class="skills-category-section">
                        <h3 class="skills-category-title">Learning Skills</h3>
                        <div class="skills-container">
                            <?php if (!empty($data['skills']['learns'])): ?>
                                <?php foreach ($data['skills']['learns'] as $skill): ?>
                                    <div class="skill-item">
                                        <div class="skill-name"><?= htmlspecialchars($skill); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No learning skills added yet.</p>
                            <?php endif; ?>
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
                        <?php else: ?>
                            <p>No completed projects yet.</p>
                        <?php endif; ?>
                    </div>

                    <div id="progress-projects" class="projects-grid" style="display: none;">
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
                        <?php else: ?>
                            <p>No projects in progress.</p>
                        <?php endif; ?>
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
                        <?php else: ?>
                            <p>No feedback yet.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Recent Activity -->
                <section class="profile-section">
                    <div class="section-header">
                        <h2 class="section-title">Recent Activity</h2>
                    </div>
                    <div class="activity-timeline">
                        <?php if (!empty($data['activity'])): ?>
                            <?php foreach ($data['activity'] as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-date"><?= htmlspecialchars($activity['date']); ?></div>
                                    <div class="activity-description"><?= htmlspecialchars($activity['description']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No recent activity.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- System Feedback Box - Only for own profile -->
                <?php if ($data['is_own_profile']): ?>
                <section class="system-feedback-section">
                    <div class="system-feedback-box">
                        <h3 class="system-feedback-title">Send Feedback to Management</h3>
                        <p class="system-feedback-description">Help us improve! Share your suggestions or report issues.</p>
                        
                        <form action="<?= URLROOT ?>/feedback/submit" method="POST" class="system-feedback-form">
                            <textarea 
                                name="feedback_message" 
                                class="system-feedback-textarea" 
                                placeholder="Share your thoughts, suggestions, or report any issues..."
                                rows="4"
                                required
                            ></textarea>
                            
                            <div class="system-feedback-actions">
                                <select name="feedback_type" class="feedback-type-select">
                                    <option value="suggestion">Suggestion</option>
                                    <option value="bug">Bug Report</option>
                                    <option value="feature">Feature Request</option>
                                    <option value="other">Other</option>
                                </select>
                                <button type="submit" class="system-feedback-btn">Send Feedback</button>
                            </div>
                        </form>
                    </div>
                </section>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</main>

<script src="<?= URLROOT ?>/assets/js/profile.js" defer></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
