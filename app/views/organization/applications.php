<?php
// -----------------------------------------------------------------------------
// ORGANIZATION APPLICATIONS ENTRY
// Use compact-card UI with "More Details" toggle from the dedicated partial.
// -----------------------------------------------------------------------------
require __DIR__ . '/_applications_compact.php';
return;

// Legacy layout kept below for fallback/reference.
require_once "../app/views/layouts/header_user.php";
require_once "../app/views/layouts/organization_sidebar.php";
?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<?php
// app/views/organization/applications.php
// Expects: $applications (array), $stats (object)
?>

<main class="site-main">
    <div class="container org-dashboard">
        <div class="page-header">
            <h1>Project Applications</h1>
            <p>Manage and review all project applications</p>
        </div>

        <!-- Stats Boxes -->
        <div class="stats-grid">
            <div class="stat-box total">
                <div class="stat-number"><?= $stats->total ?? 0 ?></div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-box pending">
                <div class="stat-number"><?= $stats->pending ?? 0 ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-box accepted">
                <div class="stat-number"><?= $stats->accepted ?? 0 ?></div>
                <div class="stat-label">Accepted</div>
            </div>
            <div class="stat-box rejected">
                <div class="stat-number"><?= $stats->rejected ?? 0 ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>

        <!-- Pending Applications -->
        <div class="applications-section">
            <h2 class="section-title">Pending Applications</h2>
            <div class="applications-list">
                <?php 
                    $pendingApps = array_filter($applications, function($app) { 
                        return $app->status === 'pending'; 
                    });
                    if (empty($pendingApps)): 
                ?>
                    <div class="card empty-card">
                        <div class="card-body">No pending applications.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingApps as $app): ?>
                        <div class="card application-card">
                            <div class="card-left">
                                <?php if (!empty($app->profile_picture)): ?>
                                    <img src="<?= URLROOT . '/' . $app->profile_picture ?>" alt="avatar" class="avatar" />
                                <?php else: ?>
                                    <div class="avatar-initial"><?= strtoupper(substr($app->user_name ?? 'U',0,1)) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <div class="card-top">
                                    <div class="app-user">
                                        <strong><?= htmlspecialchars($app->user_name) ?></strong>
                                        <small class="muted">(<?= htmlspecialchars($app->user_email) ?>)</small>
                                    </div>
                                    <div class="app-meta">
                                        <span class="icon"><i class="ph ph-star"></i></span> Rating: <?= $app->user_rating ?? '0' ?>
                                        &nbsp;•&nbsp; <span class="icon"><i class="ph ph-users"></i></span> Completed: <?= $app->completed_projects ?? 0 ?>
                                        &nbsp;•&nbsp; <small class="muted"><?= date('M d, Y H:i', strtotime($app->applied_at)) ?></small>
                                    </div>
                                </div>

                                <?php $matchedSkills = (isset($app->matched_skills_with_level) && is_array($app->matched_skills_with_level)) ? $app->matched_skills_with_level : []; ?>
                                <div class="app-skills">
                                    <?php if (!empty($matchedSkills)): ?>
                                        <?php foreach ($matchedSkills as $sk): ?>
                                            <span class="skill-tag"><?= htmlspecialchars(trim($sk)) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="skill-tag" style="opacity:0.8;">No matched required skills</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Detailed Application Info -->
                                <div class="app-details">
                                    <?php if (!empty($app->experience)): ?>
                                    <div class="detail-section">
                                        <strong>Relevant Experience:</strong>
                                        <p><?= nl2br(htmlspecialchars($app->experience)) ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($app->skills)): ?>
                                    <div class="detail-section">
                                        <strong>Skills Match:</strong>
                                        <p><?= nl2br(htmlspecialchars($app->skills)) ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($app->contribution)): ?>
                                    <div class="detail-section">
                                        <strong>Contribution Plan:</strong>
                                        <p><?= nl2br(htmlspecialchars($app->contribution)) ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($app->motivation)): ?>
                                    <div class="detail-section">
                                        <strong>Motivation:</strong>
                                        <p><?= nl2br(htmlspecialchars($app->motivation)) ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($app->commitment) || !empty($app->duration)): ?>
                                    <div class="detail-section inline">
                                        <?php if (!empty($app->commitment)): ?>
                                        <div class="inline-item">
                                            <strong>Time Commitment:</strong>
                                            <p><?= htmlspecialchars($app->commitment) ?></p>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($app->duration)): ?>
                                        <div class="inline-item">
                                            <strong>Duration:</strong>
                                            <p><?= htmlspecialchars($app->duration) ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($app->portfolio)): ?>
                                    <div class="detail-section">
                                        <strong>Portfolio:</strong>
                                        <p><a href="<?= htmlspecialchars($app->portfolio) ?>" target="_blank" class="portfolio-link"><?= htmlspecialchars($app->portfolio) ?></a></p>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="app-message" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                                    <strong>Additional Notes:</strong>
                                    <p><?= nl2br(htmlspecialchars($app->message ?? 'No additional notes')) ?></p>
                                </div>

                                <div class="app-footer">
                                    <div class="project-name">Project: <strong><?= htmlspecialchars($app->project_name) ?></strong></div>
                                    <div class="status-actions">
                                        <a href="<?= URLROOT . '/organization/handleApplication/' . $app->id . '/accept' ?>" class="btn btn-success confirm-action" data-confirm="Accept applicant?">Accept</a>
                                        <a href="<?= URLROOT . '/organization/handleApplication/' . $app->id . '/reject' ?>" class="btn btn-danger confirm-action" data-confirm="Reject applicant?" style="margin-left:6px;">Reject</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Accepted Applications -->
        <div class="applications-section">
            <h2 class="section-title">Accepted Applications</h2>
            <div class="applications-list">
                <?php 
                    $acceptedApps = array_filter($applications, function($app) { 
                        return $app->status === 'accepted'; 
                    });
                    if (empty($acceptedApps)): 
                ?>
                    <div class="card empty-card">
                        <div class="card-body">No accepted applications yet.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($acceptedApps as $app): ?>
                        <div class="card application-card">
                            <div class="card-left">
                                <?php if (!empty($app->profile_picture)): ?>
                                    <img src="<?= URLROOT . '/' . $app->profile_picture ?>" alt="avatar" class="avatar" />
                                <?php else: ?>
                                    <div class="avatar-initial"><?= strtoupper(substr($app->user_name ?? 'U',0,1)) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <div class="card-top">
                                    <div class="app-user">
                                        <strong><?= htmlspecialchars($app->user_name) ?></strong>
                                        <small class="muted">(<?= htmlspecialchars($app->user_email) ?>)</small>
                                    </div>
                                    <div class="app-meta">
                                        <span class="icon"><i class="ph ph-star"></i></span> Rating: <?= $app->user_rating ?? '0' ?>
                                        &nbsp;•&nbsp; <span class="icon"><i class="ph ph-users"></i></span> Completed: <?= $app->completed_projects ?? 0 ?>
                                        &nbsp;•&nbsp; <small class="muted"><?= date('M d, Y H:i', strtotime($app->applied_at)) ?></small>
                                    </div>
                                </div>

                                <?php $matchedSkills = (isset($app->matched_skills_with_level) && is_array($app->matched_skills_with_level)) ? $app->matched_skills_with_level : []; ?>
                                <div class="app-skills">
                                    <?php if (!empty($matchedSkills)): ?>
                                        <?php foreach ($matchedSkills as $sk): ?>
                                            <span class="skill-tag"><?= htmlspecialchars(trim($sk)) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="skill-tag" style="opacity:0.8;">No matched required skills</span>
                                    <?php endif; ?>
                                </div>

                                <div class="app-message">
                                    <strong>Message:</strong>
                                    <p><?= nl2br(htmlspecialchars($app->message ?? '')) ?></p>
                                </div>

                                <div class="app-footer">
                                    <div class="project-name">Project: <strong><?= htmlspecialchars($app->project_name) ?></strong></div>
                                    <span class="status-badge accepted">Accepted</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rejected Applications -->
        <div class="applications-section">
            <h2 class="section-title">Rejected Applications</h2>
            <div class="applications-list">
                <?php 
                    $rejectedApps = array_filter($applications, function($app) { 
                        return $app->status === 'rejected'; 
                    });
                    if (empty($rejectedApps)): 
                ?>
                    <div class="card empty-card">
                        <div class="card-body">No rejected applications.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($rejectedApps as $app): ?>
                        <div class="card application-card">
                            <div class="card-left">
                                <?php if (!empty($app->profile_picture)): ?>
                                    <img src="<?= URLROOT . '/' . $app->profile_picture ?>" alt="avatar" class="avatar" />
                                <?php else: ?>
                                    <div class="avatar-initial"><?= strtoupper(substr($app->user_name ?? 'U',0,1)) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <div class="card-top">
                                    <div class="app-user">
                                        <strong><?= htmlspecialchars($app->user_name) ?></strong>
                                        <small class="muted">(<?= htmlspecialchars($app->user_email) ?>)</small>
                                    </div>
                                    <div class="app-meta">
                                        <span class="icon"><i class="ph ph-star"></i></span> Rating: <?= $app->user_rating ?? '0' ?>
                                        &nbsp;•&nbsp; <span class="icon"><i class="ph ph-users"></i></span> Completed: <?= $app->completed_projects ?? 0 ?>
                                        &nbsp;•&nbsp; <small class="muted"><?= date('M d, Y H:i', strtotime($app->applied_at)) ?></small>
                                    </div>
                                </div>

                                <?php $matchedSkills = (isset($app->matched_skills_with_level) && is_array($app->matched_skills_with_level)) ? $app->matched_skills_with_level : []; ?>
                                <div class="app-skills">
                                    <?php if (!empty($matchedSkills)): ?>
                                        <?php foreach ($matchedSkills as $sk): ?>
                                            <span class="skill-tag"><?= htmlspecialchars(trim($sk)) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="skill-tag" style="opacity:0.8;">No matched required skills</span>
                                    <?php endif; ?>
                                </div>

                                <div class="app-message">
                                    <strong>Message:</strong>
                                    <p><?= nl2br(htmlspecialchars($app->message ?? '')) ?></p>
                                </div>

                                <div class="app-footer">
                                    <div class="project-name">Project: <strong><?= htmlspecialchars($app->project_name) ?></strong></div>
                                    <span class="status-badge rejected">Rejected</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script src="<?= URLROOT ?>/js/project_applications.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
