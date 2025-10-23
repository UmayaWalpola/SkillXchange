<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="applications-container">
        
        <!-- Page Header -->
        <div class="page-header">
            <h1>Project Applications</h1>
            <p>Review and manage applications from users wanting to join your projects</p>
        </div>

        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-number" id="totalApplications">0</span>
                <span class="stat-label">Total</span>
            </div>
            <div class="stat-item">
                <span class="stat-number pending" id="pendingApplications">0</span>
                <span class="stat-label">Pending</span>
            </div>
            <div class="stat-item">
                <span class="stat-number accepted" id="acceptedApplications">0</span>
                <span class="stat-label">Accepted</span>
            </div>
            <div class="stat-item">
                <span class="stat-number rejected" id="rejectedApplications">0</span>
                <span class="stat-label">Rejected</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <select class="filter-select" id="statusFilter">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="accepted">Accepted</option>
                <option value="rejected">Rejected</option>
            </select>

            <select class="filter-select" id="projectFilter">
                <option value="all">All Projects</option>
                <!-- Projects will be loaded dynamically -->
            </select>

            <input type="date" class="filter-select" id="dateFilter">
        </div>

        <!-- Applications List -->
        <div class="applications-list" id="applicationsList">
            <?php if(!empty($data['applications'])): ?>
                <?php foreach($data['applications'] as $application): ?>
                    <div class="application-card" data-status="<?= $application['status'] ?>">
                        <div class="application-header">
                            <div class="applicant-info">
                                <div class="applicant-avatar">
                                    <?= strtoupper(substr($application['user_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h3 class="applicant-name"><?= htmlspecialchars($application['user_name']) ?></h3>
                                    <p class="applicant-title"><?= htmlspecialchars($application['user_title']) ?></p>
                                </div>
                            </div>
                            <span class="status-badge <?= $application['status'] ?>">
                                <?= ucfirst($application['status']) ?>
                            </span>
                        </div>

                        <div class="application-body">
                            <div class="project-info">
                                <span class="project-label">Applied for:</span>
                                <span class="project-name"><?= htmlspecialchars($application['project_name']) ?></span>
                            </div>

                            <div class="application-message">
                                <h4>Application Message</h4>
                                <p><?= nl2br(htmlspecialchars($application['message'])) ?></p>
                            </div>

                            <div class="applicant-skills">
                                <?php 
                                $skills = explode(',', $application['user_skills']);
                                foreach($skills as $skill): 
                                ?>
                                    <span class="skill-tag"><?= trim($skill) ?></span>
                                <?php endforeach; ?>
                            </div>

                            <div class="application-meta">
                                <span class="meta-item">
                                    📅 Applied: <?= date('M d, Y', strtotime($application['applied_at'])) ?>
                                </span>
                                <span class="meta-item">⭐ Rating: <?= $application['user_rating'] ?>/5</span>
                                <span class="meta-item">📊 Completed Projects: <?= $application['completed_projects'] ?></span>
                            </div>
                        </div>

                        <div class="application-actions">
                            <button class="action-btn view-profile-btn" onclick="viewProfile(<?= $application['user_id'] ?>)">
                                View Profile
                            </button>
                            <?php if($application['status'] == 'pending'): ?>
                                <button class="action-btn reject-btn" onclick="handleApplication(<?= $application['id'] ?>, 'reject')">
                                    Reject
                                </button>
                                <button class="action-btn accept-btn" onclick="handleApplication(<?= $application['id'] ?>, 'accept')">
                                    Accept
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <h3>No Applications Yet</h3>
                    <p>When users apply to your projects, they'll appear here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="<?= URLROOT ?>/assets/js/organizations.js" defer></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>