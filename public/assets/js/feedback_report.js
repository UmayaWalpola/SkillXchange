/**
 * Feedback Report Modal Handler
 * Handles reporting abusive/fake feedback
 */

/**
 * Open report modal
 * 
 * @param {number} feedbackId - ID of feedback to report
 */
function openReportModal(feedbackId) {
    const modal = document.getElementById('reportModal');
    const form = document.getElementById('reportFeedbackForm');
    
    if (!modal || !form) {
        console.error('Report modal elements not found');
        return;
    }

    // Set feedback ID
    document.getElementById('reportFeedbackId').value = feedbackId;
    
    // Reset form
    form.reset();
    
    // Show modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Focus on reason dropdown
    setTimeout(() => {
        document.getElementById('reportReason').focus();
    }, 100);
}

/**
 * Close report modal
 */
function closeReportModal() {
    const modal = document.getElementById('reportModal');
    const form = document.getElementById('reportFeedbackForm');
    
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    }
    
    if (form) {
        form.reset();
    }
}

/**
 * Show toast notification
 * 
 * @param {string} message - Message to display
 * @param {string} type - 'success' or 'error'
 */
function showReportToast(message, type = 'success') {
    // Remove existing toasts
    const existingToast = document.querySelector('.report-toast');
    if (existingToast) {
        existingToast.remove();
    }

    // Create toast
    const toast = document.createElement('div');
    toast.className = 'report-toast';
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInRight 0.3s ease;
    `;
    
    const icon = type === 'success' ? '✓' : '✕';
    toast.innerHTML = `
        <span style="font-size:18px;">${icon}</span>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    // Remove after 5 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

/**
 * Character counter for details textarea
 */
document.addEventListener('DOMContentLoaded', function() {
    const detailsTextarea = document.getElementById('reportDetails');
    
    if (detailsTextarea) {
        detailsTextarea.addEventListener('input', function() {
            const maxLength = 500;
            const currentLength = this.value.length;
            
            // Limit length
            if (currentLength > maxLength) {
                this.value = this.value.substring(0, maxLength);
            }
        });
    }
});

/**
 * Handle report form submission
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reportFeedbackForm');
    
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitReportBtn');
        const originalBtnHtml = submitBtn.innerHTML;
        
        try {
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph ph-circle-notch" style="animation:spin 1s linear infinite;"></i> Submitting...';
            
            // Get form data
            const formData = new FormData(form);
            
            // Validate
            if (!formData.get('reason')) {
                showReportToast('Please select a reason', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
                return;
            }
            
            // Submit report
            const response = await fetch(`${window.URLROOT}/FeedbackReport/submit`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showReportToast(data.message, 'success');
                closeReportModal();
                
                // Optional: Reload feedback list to update report count
                if (typeof loadFeedback === 'function') {
                    setTimeout(() => loadFeedback(), 500);
                }
            } else {
                showReportToast(data.message || 'Failed to submit report', 'error');
            }
            
        } catch (error) {
            console.error('Error submitting report:', error);
            showReportToast('An error occurred. Please try again.', 'error');
        } finally {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    });
});

/**
 * Close modal on overlay click
 */
document.addEventListener('click', function(e) {
    const modal = document.getElementById('reportModal');
    
    if (e.target === modal) {
        closeReportModal();
    }
});

/**
 * Close modal on Escape key
 */
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('reportModal');
    
    if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
        closeReportModal();
    }
});

/**
 * Add button hover effects
 */
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .report-feedback-btn:hover {
            background: #e74c3c !important;
            color: white !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(231, 76, 60, 0.2);
        }
        
        #submitReportBtn:hover:not(:disabled) {
            background: #c0392b;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
        }
        
        #submitReportBtn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    `;
    document.head.appendChild(style);
});
