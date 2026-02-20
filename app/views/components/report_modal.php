<!-- Report Modal Component -->
<div id="reportModal" class="report-modal" style="display: none;">
    <div class="report-modal-overlay" onclick="closeReportModal()"></div>
    <div class="report-modal-content">
        <div class="report-modal-header">
            <h3><i class="ph ph-warning-octagon"></i> Report Content</h3>
            <button class="close-modal-btn" onclick="closeReportModal()">&times;</button>
        </div>
        <form id="reportForm" onsubmit="submitReport(event)">
            <input type="hidden" id="reportContentType" name="content_type">
            <input type="hidden" id="reportContentId" name="content_id">
            <input type="hidden" id="reportUserId" name="reported_user_id">
            <input type="hidden" id="reportProjectId" name="project_id">
            <input type="hidden" id="reportType" value="">
            
            <div class="form-group">
                <label for="reportReason">Reason for reporting *</label>
                <select id="reportReason" name="reason" required>
                    <option value="">Select a reason...</option>
                    <option value="Spam">Spam</option>
                    <option value="Harassment">Harassment or Bullying</option>
                    <option value="Inappropriate Content">Inappropriate Content</option>
                    <option value="Hate Speech">Hate Speech</option>
                    <option value="Violence">Violence or Threats</option>
                    <option value="False Information">False Information</option>
                    <option value="Copyright Violation">Copyright Violation</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="reportDescription">Additional details (optional)</label>
                <textarea id="reportDescription" name="description" rows="4" placeholder="Please provide more context about why you're reporting this..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeReportModal()">Cancel</button>
                <button type="submit" class="btn-submit-report">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<style>
.report-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.report-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.report-modal-content {
    position: relative;
    background: var(--white-bg);
    border: 2px solid var(--primary-blue);
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.report-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 2px solid var(--blue-bg);
    background: var(--blue-bg);
}

.report-modal-header h3 {
    font-size: 1.25rem;
    color: var(--dark-bg);
    margin: 0;
}

.close-modal-btn {
    background: none;
    border: none;
    font-size: 2rem;
    color: var(--dark-bg);
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.2s ease;
}

.close-modal-btn:hover {
    background: rgba(0, 0, 0, 0.1);
}

#reportForm {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--dark-bg);
    margin-bottom: 0.5rem;
}

.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid var(--blue-bg);
    border-radius: 8px;
    font-size: 0.95rem;
    font-family: inherit;
    background: var(--white-bg);
    color: var(--dark-bg);
}

.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-blue);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1rem;
    border-top: 1px solid var(--blue-bg);
}

.btn-cancel,
.btn-submit-report {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.btn-cancel {
    background: var(--blue-bg);
    color: var(--dark-bg);
}

.btn-cancel:hover {
    background: var(--accent-blue);
    color: var(--white-bg);
}

.btn-submit-report {
    background: var(--primary-blue);
    color: var(--white-bg);
}

.btn-submit-report:hover {
    background: var(--accent-blue);
}

.btn-submit-report:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Report Button Styles */
.btn-report {
    padding: 0.5rem 1rem;
    background: transparent;
    color: #ef4444;
    border: 2px solid #ef4444;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-report:hover {
    background: #ef4444;
    color: white;
}

.btn-report-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}
</style>

<script>
function openReportModal(type, id, projectId = null) {
    const modal = document.getElementById('reportModal');
    const form = document.getElementById('reportForm');
    const reportType = document.getElementById('reportType');
    
    // Reset form
    form.reset();
    
    // Set report type
    reportType.value = type;
    
    // Set appropriate hidden fields based on type
    if (type === 'content') {
        document.getElementById('reportContentType').value = id.type;
        document.getElementById('reportContentId').value = id.id;
        document.getElementById('reportUserId').value = '';
        document.getElementById('reportProjectId').value = '';
    } else if (type === 'user') {
        document.getElementById('reportUserId').value = id;
        document.getElementById('reportContentType').value = '';
        document.getElementById('reportContentId').value = '';
        document.getElementById('reportProjectId').value = '';
    } else if (type === 'projectUser') {
        document.getElementById('reportUserId').value = id;
        document.getElementById('reportProjectId').value = projectId;
        document.getElementById('reportContentType').value = '';
        document.getElementById('reportContentId').value = '';
    }
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeReportModal() {
    const modal = document.getElementById('reportModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

function submitReport(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = form.querySelector('.btn-submit-report');
    const reportType = document.getElementById('reportType').value;
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    // Determine endpoint
    let endpoint = '';
    if (reportType === 'content') {
        endpoint = '<?= URLROOT ?>/report/reportContent';
    } else if (reportType === 'user') {
        endpoint = '<?= URLROOT ?>/report/reportUser';
    } else if (reportType === 'projectUser') {
        endpoint = '<?= URLROOT ?>/report/reportProjectUser';
    }
    
    // Submit form
    fetch(endpoint, {
        method: 'POST',
        body: new FormData(form)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('[SUCCESS] ' + data.message);
            closeReportModal();
            form.reset();
        } else {
            alert('[ERROR] ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting your report. Please try again.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Report';
    });
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeReportModal();
    }
});
</script>
