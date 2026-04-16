<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<?php
// -----------------------------------------------------------------------------
// ORGANIZATION APPLICATIONS PAGE - ASSET VERSIONING
// Adds filemtime cache-busting so latest CSS/JS always loads after updates.
// -----------------------------------------------------------------------------
$orgCssPath = dirname(__DIR__, 3) . '/public/assets/css/organizations.css';
$orgJsPath = dirname(__DIR__, 3) . '/public/js/project_applications.js';
$orgCssVersion = file_exists($orgCssPath) ? (string)filemtime($orgCssPath) : '1';
$orgJsVersion = file_exists($orgJsPath) ? (string)filemtime($orgJsPath) : '1';
?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css?v=<?= htmlspecialchars($orgCssVersion) ?>">

<?php
// -----------------------------------------------------------------------------
// ORGANIZATION APPLICATIONS PAGE - DATA PREP
// These helpers keep the compact card markup easier to read and maintain.
// -----------------------------------------------------------------------------
$allApplications = isset($applications) && is_array($applications) ? $applications : [];

$applicationsByStatus = [
    'pending' => array_values(array_filter($allApplications, function ($app) {
        return ($app->status ?? '') === 'pending';
    })),
    'accepted' => array_values(array_filter($allApplications, function ($app) {
        return ($app->status ?? '') === 'accepted';
    })),
    'rejected' => array_values(array_filter($allApplications, function ($app) {
        return ($app->status ?? '') === 'rejected';
    })),
];

$applicationSectionMeta = [
    'pending' => [
        'title' => 'Pending Applications',
        'empty' => 'No pending applications.',
        'chip_label' => 'Pending',
        'chip_class' => 'is-pending',
    ],
    'accepted' => [
        'title' => 'Accepted Applications',
        'empty' => 'No accepted applications yet.',
        'chip_label' => 'Accepted',
        'chip_class' => 'is-accepted',
    ],
    'rejected' => [
        'title' => 'Rejected Applications',
        'empty' => 'No rejected applications.',
        'chip_label' => 'Rejected',
        'chip_class' => 'is-rejected',
    ],
];

$formatRating = static function ($rating) {
    $numeric = is_numeric($rating) ? (float)$rating : 0;
    return number_format($numeric, 2);
};

$formatAppliedAt = static function ($appliedAt) {
    if (empty($appliedAt)) {
        return 'N/A';
    }

    $timestamp = strtotime((string)$appliedAt);
    return $timestamp ? date('M d, Y H:i', $timestamp) : 'N/A';
};
?>

<main class="site-main">
    <div class="container org-dashboard">
        <div class="page-header">
            <h1>Project Applications</h1>
            <p>Review applicants quickly from compact cards, then open full details only when needed.</p>
        </div>

        <!-- ORGANIZATION APPLICATIONS - TOP STATS -->
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

        <?php foreach ($applicationSectionMeta as $statusKey => $section): ?>
            <?php $sectionApplications = $applicationsByStatus[$statusKey] ?? []; ?>

            <!-- ORGANIZATION APPLICATIONS - STATUS SECTION -->
            <section class="applications-section">
                <h2 class="section-title"><?= htmlspecialchars($section['title']) ?></h2>

                <?php if (empty($sectionApplications)): ?>
                    <div class="card empty-card">
                        <div class="card-body"><?= htmlspecialchars($section['empty']) ?></div>
                    </div>
                <?php else: ?>
                    <!-- ORGANIZATION APPLICATIONS - MINI CARD GRID -->
                    <div class="org-app-card-grid">
                        <?php foreach ($sectionApplications as $app): ?>
                            <?php
                                $applicationId = (int)($app->id ?? 0);
                                $detailsId = 'org-app-details-' . $applicationId;
                                $applicantName = trim((string)($app->user_name ?? 'Applicant'));
                                $projectName = trim((string)($app->project_name ?? 'Project'));
                                $applicantEmail = trim((string)($app->user_email ?? ''));
                                $ratingText = $formatRating($app->user_rating ?? 0);
                                $appliedAtText = $formatAppliedAt($app->applied_at ?? null);
                                $matchedSkills = isset($app->matched_skills_with_level) && is_array($app->matched_skills_with_level)
                                    ? array_values(array_filter(array_map('trim', $app->matched_skills_with_level)))
                                    : [];
                                $avatarInitial = strtoupper(substr($applicantName !== '' ? $applicantName : 'A', 0, 1));
                            ?>

                            <!-- ORGANIZATION APPLICATIONS - COMPACT CARD -->
                            <article class="org-app-card" data-app-status="<?= htmlspecialchars($statusKey) ?>">
                                <div class="org-app-card__header">
                                    <div class="org-app-card__profile">
                                        <?php if (!empty($app->profile_picture)): ?>
                                            <img src="<?= URLROOT . '/' . ltrim($app->profile_picture, '/') ?>" alt="<?= htmlspecialchars($applicantName) ?>" class="org-app-card__avatar">
                                        <?php else: ?>
                                            <div class="org-app-card__avatar org-app-card__avatar--initial"><?= htmlspecialchars($avatarInitial) ?></div>
                                        <?php endif; ?>

                                        <div class="org-app-card__identity">
                                            <h3 class="org-app-card__name"><?= htmlspecialchars($applicantName) ?></h3>
                                            <div class="org-app-card__project">
                                                <i class="ph ph-briefcase"></i>
                                                <span><?= htmlspecialchars($projectName) ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <span class="org-app-card__status <?= htmlspecialchars($section['chip_class']) ?>">
                                        <?= htmlspecialchars($section['chip_label']) ?>
                                    </span>
                                </div>

                                <!-- ORGANIZATION APPLICATIONS - SUMMARY METRICS -->
                                <div class="org-app-card__summary">
                                    <div class="org-app-card__metric">
                                        <i class="ph ph-star"></i>
                                        <span>Rating: <?= htmlspecialchars($ratingText) ?></span>
                                    </div>
                                </div>

                                <!-- ORGANIZATION APPLICATIONS - PRIMARY ACTIONS -->
                                <div class="org-app-card__actions">
                                    <?php if ($statusKey === 'pending'): ?>
                                        <a href="<?= URLROOT . '/organization/handleApplication/' . $applicationId . '/accept' ?>"
                                           class="org-app-btn org-app-btn--accept confirm-action"
                                           data-confirm="Accept this applicant?">
                                            Accept
                                        </a>
                                        <a href="<?= URLROOT . '/organization/handleApplication/' . $applicationId . '/reject' ?>"
                                           class="org-app-btn org-app-btn--reject confirm-action"
                                           data-confirm="Reject this applicant?">
                                            Reject
                                        </a>
                                    <?php endif; ?>

                                    <button type="button"
                                            class="org-app-btn org-app-btn--details"
                                            data-app-toggle
                                            data-target="<?= htmlspecialchars($detailsId) ?>"
                                            data-open-label="More Details"
                                            data-close-label="Hide Details"
                                            aria-expanded="false">
                                        <span data-toggle-label>More Details</span>
                                        <i class="ph ph-caret-down"></i>
                                    </button>
                                </div>

                                <!-- ORGANIZATION APPLICATIONS - EXPANDABLE DETAILS -->
                                <div id="<?= htmlspecialchars($detailsId) ?>" class="org-app-card__details" hidden>
                                    <div class="org-app-detail-grid">
                                        <div class="org-app-detail-block">
                                            <h4>Applicant Email</h4>
                                            <p><?= $applicantEmail !== '' ? htmlspecialchars($applicantEmail) : 'Not provided' ?></p>
                                        </div>

                                        <div class="org-app-detail-block">
                                            <h4>Completed Projects</h4>
                                            <p><?= (int)($app->completed_projects ?? 0) ?></p>
                                        </div>

                                        <div class="org-app-detail-block">
                                            <h4>Applied At</h4>
                                            <p><?= htmlspecialchars($appliedAtText) ?></p>
                                        </div>

                                        <div class="org-app-detail-block org-app-detail-block--full">
                                            <h4>Matched Skills</h4>
                                            <?php if (!empty($matchedSkills)): ?>
                                                <div class="org-app-detail-tags">
                                                    <?php foreach ($matchedSkills as $skill): ?>
                                                        <span class="org-app-detail-tag"><?= htmlspecialchars($skill) ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <p>No matched required skills found.</p>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($app->experience)): ?>
                                            <div class="org-app-detail-block org-app-detail-block--full">
                                                <h4>Relevant Experience</h4>
                                                <p><?= nl2br(htmlspecialchars($app->experience)) ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($app->skills)): ?>
                                            <div class="org-app-detail-block org-app-detail-block--full">
                                                <h4>Skills Match</h4>
                                                <p><?= nl2br(htmlspecialchars($app->skills)) ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($app->contribution)): ?>
                                            <div class="org-app-detail-block org-app-detail-block--full">
                                                <h4>Contribution Plan</h4>
                                                <p><?= nl2br(htmlspecialchars($app->contribution)) ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($app->motivation)): ?>
                                            <div class="org-app-detail-block org-app-detail-block--full">
                                                <h4>Motivation</h4>
                                                <p><?= nl2br(htmlspecialchars($app->motivation)) ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($app->commitment)): ?>
                                            <div class="org-app-detail-block">
                                                <h4>Time Commitment</h4>
                                                <p><?= htmlspecialchars($app->commitment) ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($app->duration)): ?>
                                            <div class="org-app-detail-block">
                                                <h4>Duration</h4>
                                                <p><?= htmlspecialchars($app->duration) ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($app->portfolio)): ?>
                                            <div class="org-app-detail-block org-app-detail-block--full">
                                                <h4>Portfolio</h4>
                                                <p>
                                                    <a href="<?= htmlspecialchars($app->portfolio) ?>" target="_blank" rel="noopener noreferrer" class="org-app-detail-link">
                                                        <?= htmlspecialchars($app->portfolio) ?>
                                                    </a>
                                                </p>
                                            </div>
                                        <?php endif; ?>

                                        <div class="org-app-detail-block org-app-detail-block--full">
                                            <h4>Additional Notes</h4>
                                            <p><?= nl2br(htmlspecialchars($app->message ?? 'No additional notes provided.')) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>
</main>

<!-- ORGANIZATION APPLICATIONS - CARD INTERACTIONS -->
<script src="<?= URLROOT ?>/js/project_applications.js?v=<?= htmlspecialchars($orgJsVersion) ?>"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
