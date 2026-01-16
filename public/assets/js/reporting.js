/**
 * SkillXchange Reporting System
 * Handles all report modal interactions and AJAX submissions
 */

(function() {
    'use strict';

    // Report Modal Elements
    let reportModal = null;
    let reportForm = null;
    let reportOverlay = null;

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeReportModal();
        attachReportButtonListeners();
    });

    /**
     * Initialize the report modal structure
     */
    function initializeReportModal() {
        // Check if modal already exists
        reportModal = document.getElementById('reportModal');
        if (!reportModal) {
            createReportModal();
        }

        reportForm = document.getElementById('reportForm');
        reportOverlay = document.getElementById('reportOverlay');

        // Close modal on overlay click
        if (reportOverlay) {
            reportOverlay.addEventListener('click', closeReportModal);
        }

        // Close modal on cancel button
        const cancelBtn = document.getElementById('reportCancelBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeReportModal);
        }

        // Handle form submission
        if (reportForm) {
            reportForm.addEventListener('submit', handleReportSubmit);
        }
    }

    /**
     * Create the report modal HTML
     */
    function createReportModal() {
        const modalHTML = `
            <div id="reportOverlay" class="modal-overlay" style="display:none;">
                <div id="reportModal" class="modal-container">
                    <div class="modal-header">
                        <h3 class="modal-title">Report Content</h3>
                        <button type="button" class="modal-close" onclick="closeReportModal()">×</button>
                    </div>
                    <form id="reportForm" class="modal-body">
                        <input type="hidden" name="report_type" id="reportType">
                        <input type="hidden" name="reported_user_id" id="reportedUserId">
                        <input type="hidden" name="content_type" id="reportContentType">
                        <input type="hidden" name="content_id" id="reportContentId">
                        <input type="hidden" name="project_id" id="reportProjectId">

                        <div class="form-group">
                            <label for="reportReason">Reason for report *</label>
                            <select name="reason" id="reportReason" required class="form-control">
                                <option value="">-- Select a reason --</option>
                                <option value="spam">Spam</option>
                                <option value="harassment">Harassment</option>
                                <option value="hate_speech">Hate speech</option>
                                <option value="fake_profile">Fake profile</option>
                                <option value="inappropriate_content">Inappropriate content</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="reportDescription">Additional details (optional)</label>
                            <textarea name="description" id="reportDescription" rows="4" class="form-control" placeholder="Please provide any additional information that might help us review this report..."></textarea>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-secondary" id="reportCancelBtn">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="reportSubmitBtn">Submit Report</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        reportModal = document.getElementById('reportModal');
        reportOverlay = document.getElementById('reportOverlay');
        reportForm = document.getElementById('reportForm');
    }

    /**
     * Attach click listeners to all report buttons
     * Uses event delegation for dynamically added buttons
     */
    function attachReportButtonListeners() {
        // Use event delegation on document body for dynamic content
        document.body.addEventListener('click', function(e) {
            // Check if clicked element or its parent is a report button
            const target = e.target.closest('.report-user-btn, .report-project-member-btn, .report-content-btn');
            if (!target) return;

            e.preventDefault();

            if (target.classList.contains('report-user-btn')) {
                const userId = target.dataset.userId;
                openReportModal('user', { userId: userId });
            } else if (target.classList.contains('report-project-member-btn')) {
                const userId = target.dataset.userId;
                const projectId = target.dataset.projectId;
                openReportModal('projectUser', { userId: userId, projectId: projectId });
            } else if (target.classList.contains('report-content-btn')) {
                const contentType = target.dataset.contentType;
                const contentId = target.dataset.contentId;
                openReportModal('content', { contentType: contentType, contentId: contentId });
            }
        });
    }

    /**
     * Open the report modal with specific data
     */
    window.openReportModal = function(type, data) {
        if (!reportOverlay || !reportModal) {
            initializeReportModal();
        }

        // Reset form
        reportForm.reset();

        // Set hidden fields based on report type
        document.getElementById('reportType').value = type;

        if (type === 'user') {
            document.getElementById('reportedUserId').value = data.userId || '';
            document.querySelector('.modal-title').textContent = 'Report User Profile';
        } else if (type === 'projectUser') {
            document.getElementById('reportedUserId').value = data.userId || '';
            document.getElementById('reportProjectId').value = data.projectId || '';
            document.querySelector('.modal-title').textContent = 'Report Project Member';
        } else if (type === 'content') {
            document.getElementById('reportContentType').value = data.contentType || '';
            document.getElementById('reportContentId').value = data.contentId || '';
            const title = data.contentType === 'post' ? 'Report Post' : 'Report Message';
            document.querySelector('.modal-title').textContent = title;
        }

        // Show modal
        reportOverlay.style.display = 'flex';
        setTimeout(() => {
            reportOverlay.classList.add('show');
        }, 10);
    };

    /**
     * Close the report modal
     */
    window.closeReportModal = function() {
        if (reportOverlay) {
            reportOverlay.classList.remove('show');
            setTimeout(() => {
                reportOverlay.style.display = 'none';
            }, 300);
        }
    };

    /**
     * Handle report form submission
     */
    function handleReportSubmit(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('reportSubmitBtn');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        const formData = new FormData(reportForm);
        const reportType = formData.get('report_type');

        // Determine endpoint
        let endpoint = '';
        if (reportType === 'user') {
            endpoint = window.URLROOT + '/report/reportUser';
        } else if (reportType === 'projectUser') {
            endpoint = window.URLROOT + '/report/reportProjectUser';
        } else if (reportType === 'content') {
            endpoint = window.URLROOT + '/report/reportContent';
        }

        // Submit via AJAX
        fetch(endpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;

            if (data.success) {
                showToast(data.message, 'success');
                closeReportModal();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            console.error('Report submission error:', error);
            showToast('An error occurred. Please try again.', 'error');
        });
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'info') {
        // Remove existing toasts
        const existing = document.querySelectorAll('.toast-notification');
        existing.forEach(t => t.remove());

        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            z-index: 10000;
            font-size: 15px;
            font-weight: 500;
            animation: slideInRight 0.3s ease;
            max-width: 400px;
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Add animation styles
    if (!document.getElementById('reportingStyles')) {
        const style = document.createElement('style');
        style.id = 'reportingStyles';
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(400px); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }

})();
