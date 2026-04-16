<?php 
// Check if user can moderate feedback
$allowedRoles = ['admin', 'manager', 'community_admin'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowedRoles, true)) {
    header('Location: ' . URLROOT . '/auth/login');
    exit;
}

require_once "../app/views/layouts/header_user.php";

// Preserve existing design language per role
if (isset($_SESSION['role']) && $_SESSION['role'] === 'manager') {
    require_once "../app/views/layouts/managersidebar.php";
} else {
    require_once "../app/views/layouts/adminsidebar.php";
}
?>

<style>
    .reported-feedback-container {
        max-width: 1400px;
        margin: 0 auto;
        width: 100%;
    }

    .report-card {
        background: white;
        border: 2px solid #e1eefb;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 20px;
        transition: all 0.2s;
    }

    .report-card:hover {
        border-color: #6583aa;
        box-shadow: 0 4px 12px rgba(101, 131, 170, 0.1);
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-reviewed {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-dismissed {
        background: #f8d7da;
        color: #721c24;
    }

    .status-action_taken {
        background: #d4edda;
        color: #155724;
    }

    .admin-action-btn {
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 13px;
    }

    .btn-dismiss {
        background: #f8f9fa;
        color: #666;
        border: 1px solid #dee2e6;
    }

    .btn-dismiss:hover {
        background: #e2e6ea;
    }

    .btn-remove {
        background: #e74c3c;
        color: white;
    }

    .btn-remove:hover {
        background: #c0392b;
    }

    .btn-reviewed {
        background: #6583aa;
        color: white;
    }

    .btn-reviewed:hover {
        background: #547291;
    }

    .filter-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        border-bottom: 2px solid #e1eefb;
        padding-bottom: 0;
    }

    .filter-tab {
        padding: 12px 24px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        font-weight: 600;
        cursor: pointer;
        color: #666;
        transition: all 0.2s;
    }

    .filter-tab:hover {
        color: #6583aa;
    }

    .filter-tab.active {
        color: #6583aa;
        border-bottom-color: #6583aa;
    }
</style>

<?php
$reasonLabels = [
    'abusive' => 'Abusive/Harassment',
    'fake' => 'Fake/False Information',
    'spam' => 'Spam',
    'inappropriate' => 'Inappropriate Content',
    'other' => 'Other'
];
?>

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
    <div class="container reported-feedback-container">
        <div style="margin-bottom:30px;">
            <h1 style="font-size:32px;font-weight:700;color:#1a1a1a;margin-bottom:10px;display:flex;align-items:center;gap:12px;">
                <i class="ph ph-flag" style="color:#e74c3c;"></i>
                Reported Feedback
            </h1>
            <p style="color:#666;font-size:16px;">Review and manage only user-submitted feedback reports (abusive, fake, spam, inappropriate, or other).</p>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px;">
            <div style="background:linear-gradient(135deg,#ffc107,#ff9800);padding:20px;border-radius:12px;color:white;">
                <div style="font-size:14px;font-weight:600;opacity:0.9;margin-bottom:5px;">Pending</div>
                <div style="font-size:32px;font-weight:700;"><?= $data['stats']['pending'] ?? 0 ?></div>
            </div>
            <div style="background:linear-gradient(135deg,#6583aa,#547291);padding:20px;border-radius:12px;color:white;">
                <div style="font-size:14px;font-weight:600;opacity:0.9;margin-bottom:5px;">Reviewed</div>
                <div style="font-size:32px;font-weight:700;"><?= $data['stats']['reviewed'] ?? 0 ?></div>
            </div>
            <div style="background:linear-gradient(135deg,#10b981,#059669);padding:20px;border-radius:12px;color:white;">
                <div style="font-size:14px;font-weight:600;opacity:0.9;margin-bottom:5px;">Action Taken</div>
                <div style="font-size:32px;font-weight:700;"><?= $data['stats']['action_taken'] ?? 0 ?></div>
            </div>
            <div style="background:linear-gradient(135deg,#e74c3c,#c0392b);padding:20px;border-radius:12px;color:white;">
                <div style="font-size:14px;font-weight:600;opacity:0.9;margin-bottom:5px;">Dismissed</div>
                <div style="font-size:32px;font-weight:700;"><?= $data['stats']['dismissed'] ?? 0 ?></div>
            </div>
        </div>

        <div class="filter-tabs">
            <button class="filter-tab <?= $data['current_status'] === 'all' ? 'active' : '' ?>" onclick="filterReports('all')">All (<?= $data['stats']['total'] ?? 0 ?>)</button>
            <button class="filter-tab <?= $data['current_status'] === 'pending' ? 'active' : '' ?>" onclick="filterReports('pending')">Pending (<?= $data['stats']['pending'] ?? 0 ?>)</button>
            <button class="filter-tab <?= $data['current_status'] === 'reviewed' ? 'active' : '' ?>" onclick="filterReports('reviewed')">Reviewed (<?= $data['stats']['reviewed'] ?? 0 ?>)</button>
            <button class="filter-tab <?= $data['current_status'] === 'action_taken' ? 'active' : '' ?>" onclick="filterReports('action_taken')">Action Taken (<?= $data['stats']['action_taken'] ?? 0 ?>)</button>
            <button class="filter-tab <?= $data['current_status'] === 'dismissed' ? 'active' : '' ?>" onclick="filterReports('dismissed')">Dismissed (<?= $data['stats']['dismissed'] ?? 0 ?>)</button>
        </div>

        <div id="reportsContainer">
            <?php if (empty($data['reports'])): ?>
                <div style="text-align:center;padding:60px 20px;background:white;border:2px solid #e1eefb;border-radius:16px;">
                    <div style="font-size:64px;color:#e0e0e0;margin-bottom:15px;">
                        <i class="ph ph-check-circle"></i>
                    </div>
                    <h3 style="font-size:20px;color:#333;margin-bottom:10px;">No Reports Found</h3>
                    <p style="color:#999;font-size:14px;">All clear! No reports match this filter.</p>
                </div>
            <?php else: ?>
                <?php foreach ($data['reports'] as $report): ?>
                    <?php
                    $reportKind = $report['report_kind'] ?? 'feedback';
                    $uiStatus = $report['ui_status'] ?? ($report['status'] ?? 'pending');
                    $isFeedbackReport = $reportKind === 'feedback';
                    if (!$isFeedbackReport) {
                        continue;
                    }
                    $reportTitle = $report['display_title'] ?? ($isFeedbackReport ? 'Feedback Report' : 'Report');
                    $reporterName = htmlspecialchars($report['reporter_name'] ?? 'Unknown');
                    $reportedUserName = htmlspecialchars($report['reported_user_name'] ?? '');
                    $projectName = htmlspecialchars($report['project_name'] ?? '');
                    $createdAt = !empty($report['created_at']) ? date('M d, Y \a\t g:i A', strtotime($report['created_at'])) : 'Unknown date';
                    $feedbackRating = max(0, min(5, (int)($report['feedback_rating'] ?? 0)));
                    $reasonKey = $report['reason'] ?? 'other';
                    $reasonLabel = $reasonLabels[$reasonKey] ?? ucfirst(str_replace('_', ' ', $reasonKey));
                    ?>
                    <div class="report-card" data-report-id="<?= (int)($report['id'] ?? 0) ?>">
                        <div style="display:flex;justify-content:space-between;align-items:start;gap:20px;margin-bottom:20px;flex-wrap:wrap;">
                            <div>
                                <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap;">
                                    <h3 style="font-size:18px;font-weight:700;color:#1a1a1a;margin:0;"><?= htmlspecialchars($reportTitle) ?> #<?= (int)($report['id'] ?? 0) ?></h3>
                                    <span class="status-badge status-<?= htmlspecialchars($uiStatus) ?>"><?= ucfirst(str_replace('_', ' ', $uiStatus)) ?></span>
                                </div>
                                <div style="color:#666;font-size:13px;line-height:1.6;">
                                    Reported by <strong><?= $reporterName ?></strong> on <?= $createdAt ?>
                                    <?php if (!$isFeedbackReport && $reportedUserName !== ''): ?>
                                        <br>Reported user: <strong><?= $reportedUserName ?></strong>
                                    <?php endif; ?>
                                    <?php if ($projectName !== ''): ?>
                                        <br>Project: <strong><?= $projectName ?></strong>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($uiStatus === 'pending'): ?>
                                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                    <?php if ($isFeedbackReport): ?>
                                        <button class="admin-action-btn btn-dismiss" onclick="updateFeedbackReportStatus(<?= (int)($report['id'] ?? 0) ?>, 'dismissed')">
                                            <i class="ph ph-x-circle"></i> Dismiss
                                        </button>
                                        <button class="admin-action-btn btn-reviewed" onclick="updateFeedbackReportStatus(<?= (int)($report['id'] ?? 0) ?>, 'reviewed')">
                                            <i class="ph ph-check-circle"></i> Mark Reviewed
                                        </button>
                                        <?php if (!empty($report['feedback_id'])): ?>
                                            <button class="admin-action-btn btn-remove" onclick="removeFeedback(<?= (int)($report['id'] ?? 0) ?>, <?= (int)$report['feedback_id'] ?>)">
                                                <i class="ph ph-trash"></i> Remove Feedback
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="admin-action-btn btn-dismiss" onclick="updateGeneralReportStatus(<?= (int)($report['id'] ?? 0) ?>, '<?= htmlspecialchars($report['report_type_for_action'] ?? $reportKind) ?>', 'dismissed')">
                                            <i class="ph ph-x-circle"></i> Dismiss
                                        </button>
                                        <button class="admin-action-btn btn-reviewed" onclick="updateGeneralReportStatus(<?= (int)($report['id'] ?? 0) ?>, '<?= htmlspecialchars($report['report_type_for_action'] ?? $reportKind) ?>', 'reviewed')">
                                            <i class="ph ph-check-circle"></i> Mark Reviewed
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="background:#f8f9fa;padding:15px;border-radius:8px;margin-bottom:15px;">
                            <div style="display:grid;grid-template-columns:150px 1fr;gap:10px;font-size:14px;">
                                <div style="color:#666;font-weight:600;">Reason:</div>
                                <div style="color:#1a1a1a;"><?= htmlspecialchars($reasonLabel) ?></div>

                                <?php if (!empty($report['details'])): ?>
                                    <div style="color:#666;font-weight:600;">Details:</div>
                                    <div style="color:#1a1a1a;"><?= htmlspecialchars($report['details']) ?></div>
                                <?php endif; ?>

                                <?php if (!empty($report['content_type'])): ?>
                                    <div style="color:#666;font-weight:600;">Content Type:</div>
                                    <div style="color:#1a1a1a;"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $report['content_type']))) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($isFeedbackReport): ?>
                            <div style="border:2px solid #e1eefb;padding:20px;border-radius:12px;background:white;">
                                <div style="font-weight:700;color:#6583aa;margin-bottom:15px;display:flex;align-items:center;gap:8px;">
                                    <i class="ph ph-chat-text"></i>
                                    Original Feedback
                                </div>

                                <div style="display:flex;justify-content:space-between;gap:20px;margin-bottom:15px;flex-wrap:wrap;">
                                    <div>
                                        <div style="font-size:14px;color:#666;margin-bottom:5px;">
                                            From: <strong style="color:#1a1a1a;"><?= htmlspecialchars($report['feedback_reviewer_name'] ?? 'Unknown') ?></strong>
                                        </div>
                                        <div style="font-size:14px;color:#666;">
                                            To: <strong style="color:#1a1a1a;"><?= htmlspecialchars($report['feedback_user_name'] ?? 'Unknown') ?></strong>
                                        </div>
                                        <?php if ($projectName !== ''): ?>
                                            <div style="font-size:13px;color:#6583aa;margin-top:3px;">Project: <?= $projectName ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div style="text-align:right;">
                                        <div style="font-size:24px;color:#FFD700;margin-bottom:5px;"><?= str_repeat('&#9733;', $feedbackRating) . str_repeat('&#9734;', 5 - $feedbackRating) ?></div>
                                        <div style="font-size:14px;font-weight:600;color:#666;"><?= $feedbackRating ?>/5</div>
                                    </div>
                                </div>

                                <?php if (!empty($report['feedback_tags'])): ?>
                                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:15px;">
                                        <?php foreach (explode(',', $report['feedback_tags']) as $tag): ?>
                                            <?php $trimmedTag = trim($tag); ?>
                                            <?php if ($trimmedTag !== ''): ?>
                                                <span style="background:#e1eefb;color:#6583aa;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;"><?= htmlspecialchars($trimmedTag) ?></span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($report['feedback_comment'])): ?>
                                    <div style="background:#f8f9fa;padding:12px;border-radius:8px;font-size:14px;color:#333;line-height:1.6;"><?= htmlspecialchars($report['feedback_comment']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div style="border:2px solid #e1eefb;padding:20px;border-radius:12px;background:white;">
                                <div style="font-weight:700;color:#6583aa;margin-bottom:15px;display:flex;align-items:center;gap:8px;">
                                    <i class="ph ph-info"></i>
                                    Report Context
                                </div>

                                <div style="display:grid;grid-template-columns:180px 1fr;gap:10px;font-size:14px;line-height:1.6;">
                                    <div style="color:#666;font-weight:600;">Report Type:</div>
                                    <div style="color:#1a1a1a;"><?= htmlspecialchars($reportTitle) ?></div>

                                    <?php if ($reportedUserName !== ''): ?>
                                        <div style="color:#666;font-weight:600;">Reported User:</div>
                                        <div style="color:#1a1a1a;"><?= $reportedUserName ?></div>
                                    <?php endif; ?>

                                    <?php if ($projectName !== ''): ?>
                                        <div style="color:#666;font-weight:600;">Project:</div>
                                        <div style="color:#1a1a1a;"><?= $projectName ?></div>
                                    <?php endif; ?>

                                    <?php if (isset($report['content_id']) && $report['content_id'] !== null): ?>
                                        <div style="color:#666;font-weight:600;">Content ID:</div>
                                        <div style="color:#1a1a1a;">#<?= (int)$report['content_id'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($report['reviewer_name']) && !empty($report['reviewed_at'])): ?>
                            <div style="margin-top:15px;padding:12px;background:#e1eefb;border-radius:8px;font-size:13px;">
                                <strong>Reviewed by <?= htmlspecialchars($report['reviewer_name']) ?></strong> on <?= date('M d, Y \a\t g:i A', strtotime($report['reviewed_at'])) ?>
                                <?php if (!empty($report['admin_notes'])): ?>
                                    <div style="margin-top:5px;">Note: <?= htmlspecialchars($report['admin_notes']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (($data['total_pages'] ?? 1) > 1): ?>
            <div style="margin-top:30px;text-align:center;">
                <div style="display:inline-flex;gap:10px;align-items:center;">
                    <?php if (($data['current_page'] ?? 1) > 1): ?>
                        <a href="?status=<?= $data['current_status'] ?>&page=<?= $data['current_page'] - 1 ?>" style="padding:10px 20px;background:white;border:2px solid #e1eefb;border-radius:8px;font-weight:600;text-decoration:none;color:#333;">Previous</a>
                    <?php endif; ?>

                    <span style="color:#666;font-size:14px;padding:0 15px;">Page <?= $data['current_page'] ?> of <?= $data['total_pages'] ?></span>

                    <?php if (($data['current_page'] ?? 1) < ($data['total_pages'] ?? 1)): ?>
                        <a href="?status=<?= $data['current_status'] ?>&page=<?= $data['current_page'] + 1 ?>" style="padding:10px 20px;background:white;border:2px solid #e1eefb;border-radius:8px;font-weight:600;text-decoration:none;color:#333;">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    </div>
</div>
</main>

<script>
    const URLROOT = '<?= URLROOT ?>';

    function filterReports(status) {
        window.location.href = `${URLROOT}/FeedbackReport/index?status=${status}`;
    }

    async function updateFeedbackReportStatus(reportId, status) {
        if (!confirm(`Are you sure you want to mark this report as ${status.replace('_', ' ')}?`)) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('report_id', reportId);
            formData.append('status', status);

            const response = await fetch(`${URLROOT}/FeedbackReport/updateStatus`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message || 'Report updated successfully');
                location.reload();
            } else {
                alert(data.message || 'Failed to update status');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    }

    async function updateGeneralReportStatus(reportId, reportType, status) {
        if (!confirm(`Are you sure you want to mark this report as ${status.replace('_', ' ')}?`)) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('report_id', reportId);
            formData.append('report_type', reportType);
            formData.append('status', status === 'action_taken' ? 'resolved' : status);

            const response = await fetch(`${URLROOT}/report/updateStatus`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message || 'Report updated successfully');
                location.reload();
            } else {
                alert(data.message || 'Failed to update report');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    }

    async function removeFeedback(reportId, feedbackId) {
        if (!confirm('Are you sure you want to permanently delete this feedback? This action cannot be undone!')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('report_id', reportId);
            formData.append('feedback_id', feedbackId);

            const response = await fetch(`${URLROOT}/FeedbackReport/removeFeedback`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message || 'Feedback removed successfully');
                location.reload();
            } else {
                alert(data.message || 'Failed to remove feedback');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    }
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>
