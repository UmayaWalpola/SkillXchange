<?php 
// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . URLROOT . '/auth/login');
    exit;
}

require_once "../app/views/layouts/header_user.php";
require_once "../app/views/layouts/adminsidebar.php";
?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/dashboard.css">

<style>

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
        background: #5472 91;
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

    /* Keep filters/actions readable on smaller widths */
    @media (max-width: 992px) {
        .reported-feedback-main {
            margin-left: 0;
            width: 100%;
            margin-top: 64px;
            padding: 20px;
        }

        .filter-tabs {
            overflow-x: auto;
            white-space: nowrap;
        }
    }
</style>

<div class="dashboard-container">
<div class="dashboard-main">

    <div class="page-header">
        <div>
            <h1>Reported Feedback</h1>
            <p>Show only user-submitted feedback reports (abusive, fake, spam, inappropriate, other).</p>
        </div>
    </div>

        <!-- Statistics Cards -->
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

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="filter-tab <?= $data['current_status'] === 'all' ? 'active' : '' ?>" onclick="filterReports('all')">
                All (<?= $data['stats']['total'] ?? 0 ?>)
            </button>
            <button class="filter-tab <?= $data['current_status'] === 'pending' ? 'active' : '' ?>" onclick="filterReports('pending')">
                Pending (<?= $data['stats']['pending'] ?? 0 ?>)
            </button>
            <button class="filter-tab <?= $data['current_status'] === 'reviewed' ? 'active' : '' ?>" onclick="filterReports('reviewed')">
                Reviewed (<?= $data['stats']['reviewed'] ?? 0 ?>)
            </button>
            <button class="filter-tab <?= $data['current_status'] === 'action_taken' ? 'active' : '' ?>" onclick="filterReports('action_taken')">
                Action Taken (<?= $data['stats']['action_taken'] ?? 0 ?>)
            </button>
            <button class="filter-tab <?= $data['current_status'] === 'dismissed' ? 'active' : '' ?>" onclick="filterReports('dismissed')">
                Dismissed (<?= $data['stats']['dismissed'] ?? 0 ?>)
            </button>
        </div>

        <!-- Reports List -->
        <div id="reportsContainer">
            <?php if (empty($data['reports'])): ?>
                <div style="text-align:center;padding:60px 20px;background:white;border:2px solid #e1eefb;border-radius:16px;">
                    <div style="font-size:64px;color:#e0e0e0;margin-bottom:15px;">
                        <i class="ph ph-check-circle"></i>
                    </div>
                    <h3 style="font-size:20px;color:#333;margin-bottom:10px;">No Reports Found</h3>
                    <p style="color:#999;font-size:14px;">All clear! No feedback reports match this filter.</p>
                </div>
            <?php else: ?>
                <?php foreach ($data['reports'] as $report): ?>
                    <?php
                        // View-level safety: render feedback-reason reports only.
                        $validReasons = ['abusive', 'fake', 'spam', 'inappropriate', 'other'];
                        $reasonValue = strtolower(trim((string)($report['reason'] ?? '')));
                        $feedbackId = (int)($report['feedback_id'] ?? 0);
                        if ($feedbackId <= 0 || !in_array($reasonValue, $validReasons, true)) {
                            continue;
                        }
                    ?>
                    <div class="report-card" data-report-id="<?= $report['id'] ?>">
                        <!-- Report Header -->
                        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:20px;">
                            <div>
                                <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                                    <h3 style="font-size:18px;font-weight:700;color:#1a1a1a;margin:0;">
                                        Report #<?= $report['id'] ?>
                                    </h3>
                                    <span class="status-badge status-<?= $report['status'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $report['status'])) ?>
                                    </span>
                                </div>
                                <div style="color:#666;font-size:13px;">
                                    Reported by <strong><?= htmlspecialchars($report['reporter_name']) ?></strong> 
                                    on <?= date('M d, Y \a\t g:i A', strtotime($report['created_at'])) ?>
                                </div>
                            </div>
                            
                            <?php if (in_array($report['status'], ['pending', 'reviewed'])): ?>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <?php if ($report['status'] === 'pending'): ?>
                                    <button class="admin-action-btn btn-dismiss" onclick="openActionModal(<?= $report['id'] ?>, 'dismissed')">
                                        <i class="ph ph-x-circle"></i> Dismiss
                                    </button>
                                    <button class="admin-action-btn btn-reviewed" onclick="openActionModal(<?= $report['id'] ?>, 'reviewed')">
                                        <i class="ph ph-check-circle"></i> Mark Reviewed
                                    </button>
                                    <?php endif; ?>
                                    <button class="admin-action-btn" style="background:#f0a500;color:white;" onclick="warnUser(<?= $report['id'] ?>, <?= (int)($report['feedback_user_id'] ?? 0) ?>)">
                                        <i class="ph ph-warning"></i> Warn User
                                    </button>
                                    <button class="admin-action-btn btn-remove" onclick="removeFeedback(<?= $report['id'] ?>, <?= $report['feedback_id'] ?>)">
                                        <i class="ph ph-trash"></i> Remove Feedback
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Report Details -->
                        <div style="background:#f8f9fa;padding:15px;border-radius:8px;margin-bottom:15px;">
                            <div style="display:grid;grid-template-columns:150px 1fr;gap:10px;font-size:14px;">
                                <div style="color:#666;font-weight:600;">Reason:</div>
                                <div style="color:#1a1a1a;">
                                    <?php
                                    $reasonIcons = [
                                        'abusive' => 'Abusive/Harassment',
                                        'fake' => 'Fake/False Information',
                                        'spam' => 'Spam',
                                        'inappropriate' => 'Inappropriate Content',
                                        'other' => 'Other'
                                    ];
                                    echo $reasonIcons[$report['reason']] ?? $report['reason'];
                                    ?>
                                </div>
                                
                                <?php if (!empty($report['details'])): ?>
                                    <div style="color:#666;font-weight:600;">Details:</div>
                                    <div style="color:#1a1a1a;"><?= htmlspecialchars($report['details']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Original Feedback -->
                        <div style="border:2px solid #e1eefb;padding:20px;border-radius:12px;background:white;">
                            <div style="font-weight:700;color:#6583aa;margin-bottom:15px;display:flex;align-items:center;gap:8px;">
                                <i class="ph ph-chat-text"></i>
                                Original Feedback
                            </div>
                            
                            <div style="display:flex;justify-content:space-between;margin-bottom:15px;">
                                <div>
                                    <div style="font-size:14px;color:#666;margin-bottom:5px;">
                                        From: <strong style="color:#1a1a1a;"><?= htmlspecialchars($report['feedback_reviewer_name']) ?></strong>
                                    </div>
                                    <div style="font-size:14px;color:#666;">
                                        To: <strong style="color:#1a1a1a;"><?= htmlspecialchars($report['feedback_user_name']) ?></strong>
                                    </div>
                                    <?php if (!empty($report['project_name'])): ?>
                                        <div style="font-size:13px;color:#6583aa;margin-top:3px;">
                                            Project: <?= htmlspecialchars($report['project_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="text-align:right;">
                                    <div style="font-size:24px;color:#FFD700;margin-bottom:5px;">
                                        <?= str_repeat('★', $report['feedback_rating']) . str_repeat('☆', 5 - $report['feedback_rating']) ?>
                                    </div>
                                    <div style="font-size:14px;font-weight:600;color:#666;"><?= $report['feedback_rating'] ?>/5</div>
                                </div>
                            </div>

                            <?php if (!empty($report['feedback_tags'])): ?>
                                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:15px;">
                                    <?php foreach (explode(',', $report['feedback_tags']) as $tag): ?>
                                        <span style="background:#e1eefb;color:#6583aa;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;">
                                            <?= htmlspecialchars(trim($tag)) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($report['feedback_comment'])): ?>
                                <div style="background:#f8f9fa;padding:12px;border-radius:8px;font-size:14px;color:#333;line-height:1.6;">
                                    <?= htmlspecialchars($report['feedback_comment']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Admin Notes (if reviewed) -->
                        <?php if (!empty($report['reviewer_name'])): ?>
                            <div style="margin-top:15px;padding:12px;background:#e1eefb;border-radius:8px;font-size:13px;">
                                <strong>Reviewed by <?= htmlspecialchars($report['reviewer_name']) ?></strong> 
                                on <?= date('M d, Y \a\t g:i A', strtotime($report['reviewed_at'])) ?>
                                <?php if (!empty($report['admin_notes'])): ?>
                                    <div style="margin-top:5px;">Note: <?= htmlspecialchars($report['admin_notes']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($data['total_pages'] > 1): ?>
            <div style="margin-top:30px;text-align:center;">
                <div style="display:inline-flex;gap:10px;align-items:center;">
                    <?php if ($data['current_page'] > 1): ?>
                        <a href="?status=<?= $data['current_status'] ?>&page=<?= $data['current_page'] - 1 ?>" 
                           style="padding:10px 20px;background:white;border:2px solid #e1eefb;border-radius:8px;font-weight:600;text-decoration:none;color:#333;">
                            Previous
                        </a>
                    <?php endif; ?>
                    
                    <span style="color:#666;font-size:14px;padding:0 15px;">
                        Page <?= $data['current_page'] ?> of <?= $data['total_pages'] ?>
                    </span>
                    
                    <?php if ($data['current_page'] < $data['total_pages']): ?>
                        <a href="?status=<?= $data['current_status'] ?>&page=<?= $data['current_page'] + 1 ?>" 
                           style="padding:10px 20px;background:white;border:2px solid #e1eefb;border-radius:8px;font-weight:600;text-decoration:none;color:#333;">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
</div>

<!-- Admin Action Modal -->
<div id="actionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;padding:30px;max-width:480px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="margin:0 0 8px;font-size:20px;color:#1a1a1a;" id="actionModalTitle">Confirm Action</h3>
        <p style="color:#666;font-size:14px;margin-bottom:20px;" id="actionModalDesc"></p>
        <div style="margin-bottom:20px;">
            <label style="display:block;font-weight:600;font-size:14px;color:#333;margin-bottom:8px;">Admin Notes <span style="color:#999;font-weight:400;">(Optional)</span></label>
            <textarea id="adminNotesInput" rows="3" placeholder="Add a note about this action..." style="width:100%;padding:12px;border:2px solid #e1eefb;border-radius:8px;font-size:14px;resize:vertical;font-family:inherit;box-sizing:border-box;"></textarea>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end;">
            <button onclick="closeActionModal()" style="padding:10px 20px;background:white;border:2px solid #e1eefb;border-radius:8px;font-weight:600;color:#666;cursor:pointer;">Cancel</button>
            <button id="actionModalConfirmBtn" style="padding:10px 20px;border:none;border-radius:8px;font-weight:600;color:white;cursor:pointer;">Confirm</button>
        </div>
    </div>
</div>

<script>
    const URLROOT = '<?= URLROOT ?>';
    let _pendingReportId = null;
    let _pendingStatus = null;

    // ── Filter tabs ───────────────────────────────────────────
    function filterReports(status) {
        window.location.href = `${URLROOT}/FeedbackReport/index?status=${status}`;
    }

    // ── Action Modal ──────────────────────────────────────────
    function openActionModal(reportId, status) {
        _pendingReportId = reportId;
        _pendingStatus   = status;

        const titles = {
            dismissed : '⚪ Dismiss Report',
            reviewed  : '✅ Mark as Reviewed',
            action_taken: '🔴 Action Taken'
        };
        const descs = {
            dismissed : 'This report will be dismissed. The feedback will remain visible.',
            reviewed  : 'Mark this report as reviewed. You can still remove the feedback after.',
            action_taken: 'Mark this report as action taken.'
        };
        const btnColors = {
            dismissed : '#6c757d',
            reviewed  : '#6583aa',
            action_taken: '#e74c3c'
        };

        document.getElementById('actionModalTitle').textContent = titles[status] || 'Confirm Action';
        document.getElementById('actionModalDesc').textContent  = descs[status]  || '';
        document.getElementById('adminNotesInput').value = '';
        const btn = document.getElementById('actionModalConfirmBtn');
        btn.textContent = 'Confirm';
        btn.style.background = btnColors[status] || '#6583aa';
        btn.onclick = submitActionModal;

        document.getElementById('actionModal').style.display = 'flex';
    }

    function closeActionModal() {
        document.getElementById('actionModal').style.display = 'none';
        _pendingReportId = null;
        _pendingStatus   = null;
    }

    async function submitActionModal() {
        if (!_pendingReportId || !_pendingStatus) return;
        const notes = document.getElementById('adminNotesInput').value.trim();
        const btn   = document.getElementById('actionModalConfirmBtn');
        btn.disabled = true;
        btn.textContent = 'Processing...';

        try {
            const formData = new FormData();
            formData.append('report_id',   _pendingReportId);
            formData.append('status',      _pendingStatus);
            formData.append('admin_notes', notes);

            const response = await fetch(`${URLROOT}/FeedbackReport/updateStatus`, {
                method: 'POST', body: formData
            });
            const result = await response.json();

            closeActionModal();
            if (result.success) {
                showToast(result.message || 'Status updated!', 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                showToast(result.message || 'Failed to update status.', 'error');
            }
        } catch (e) {
            closeActionModal();
            showToast('An error occurred.', 'error');
        }
    }

    // Close modal on backdrop click
    document.getElementById('actionModal').addEventListener('click', function(e) {
        if (e.target === this) closeActionModal();
    });

    // ── Remove Feedback ───────────────────────────────────────
    async function removeFeedback(reportId, feedbackId) {
        if (!confirm('Are you sure you want to PERMANENTLY DELETE this feedback? This action cannot be undone!')) return;

        try {
            const formData = new FormData();
            formData.append('report_id',   reportId);
            formData.append('feedback_id', feedbackId);

            const response = await fetch(`${URLROOT}/FeedbackReport/removeFeedback`, {
                method: 'POST', body: formData
            });
            const result = await response.json();

            if (result.success) {
                showToast(result.message || 'Feedback removed!', 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                showToast(result.message || 'Failed to remove feedback.', 'error');
            }
        } catch (e) {
            showToast('An error occurred.', 'error');
        }
    }

    // ── Warn User ─────────────────────────────────────────────
    async function warnUser(reportId, feedbackUserId) {
        if (!feedbackUserId) {
            showToast('Cannot identify the feedback author.', 'error');
            return;
        }
        if (!confirm('Send a warning notification to the user who wrote this feedback?')) return;

        try {
            const formData = new FormData();
            formData.append('report_id',       reportId);
            formData.append('feedback_user_id', feedbackUserId);

            const response = await fetch(`${URLROOT}/FeedbackReport/warnUser`, {
                method: 'POST', body: formData
            });
            const result = await response.json();

            if (result.success) {
                showToast('Warning sent to user!', 'success');
            } else {
                showToast(result.message || 'Failed to send warning.', 'error');
            }
        } catch (e) {
            showToast('An error occurred.', 'error');
        }
    }

    // ── Toast helper ──────────────────────────────────────────
    function showToast(message, type = 'success') {
        document.querySelectorAll('.admin-toast').forEach(t => t.remove());
        const toast = document.createElement('div');
        toast.className = 'admin-toast';
        toast.style.cssText = `
            position:fixed;top:20px;right:20px;z-index:99999;
            background:${type === 'success' ? '#10b981' : '#ef4444'};
            color:white;padding:14px 22px;border-radius:10px;
            font-weight:600;font-size:14px;box-shadow:0 4px 20px rgba(0,0,0,.2);
            display:flex;align-items:center;gap:10px;
            animation:slideInRight .3s ease;
        `;
        toast.innerHTML = `<span style="font-size:18px;">${type === 'success' ? '✓' : '✕'}</span><span>${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }

    // Slide-in animation
    const style = document.createElement('style');
    style.textContent = `@keyframes slideInRight{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}`;
    document.head.appendChild(style);
</script>

<?php require_once "../app/views/layouts/footer.php"; ?>
