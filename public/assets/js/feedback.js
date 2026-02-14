/**
 * Feedback Module - AJAX Handler
 * Handles feedback form submission without page reload
 */

/**
 * Open feedback modal
 * @param {number} userId - User ID to give feedback to
 * @param {string} userName - User's name
 * @param {number|null} projectId - Optional project ID
 */
function openFeedbackModal(userId, userName, projectId = null) {
    const modal = document.getElementById('feedbackModal');
    if (!modal) return;

    // Set hidden field values
    document.getElementById('feedback_user_id').value = userId;
    document.getElementById('feedback_project_id').value = projectId || '';

    // Set member info display
    const memberNameEl = document.getElementById('feedback_member_name');
    if (memberNameEl) {
        memberNameEl.textContent = userName;
    }

    // Reset form
    const form = document.getElementById('feedbackForm');
    if (form) {
        form.reset();
    }

    // Reset star rating display
    clearStarSelection();

    // Reset comment counter
    const commentCount = document.getElementById('commentCount');
    if (commentCount) {
        commentCount.textContent = '0';
    }

    // Show modal with animation
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
}

/**
 * Close feedback modal
 */
function closeFeedbackModal() {
    const modal = document.getElementById('feedbackModal');
    if (!modal) return;

    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

/**
 * Set star rating
 * @param {number} rating - Rating value (1-5)
 */
function setRating(rating) {
    const radioBtn = document.getElementById('star' + rating);
    if (radioBtn) {
        radioBtn.checked = true;
        updateStarDisplay(rating);
    }
}

/**
 * Update star display
 * @param {number} rating - Rating value
 */
function updateStarDisplay(rating) {
    const ratingValueEl = document.getElementById('ratingValue');
    if (!ratingValueEl) return;

    const ratingTexts = {
        '1': 'Poor (1/5)',
        '2': 'Fair (2/5)',
        '3': 'Average (3/5)',
        '4': 'Good (4/5)',
        '5': 'Excellent (5/5)'
    };

    ratingValueEl.textContent = ratingTexts[rating.toString()] || '';
    ratingValueEl.classList.add('show');
}

/**
 * Clear star selection
 */
function clearStarSelection() {
    const ratingValueEl = document.getElementById('ratingValue');
    if (ratingValueEl) {
        ratingValueEl.classList.remove('show');
        ratingValueEl.textContent = '';
    }

    // Uncheck all radio buttons
    document.querySelectorAll('input[name="rating"]').forEach(input => {
        input.checked = false;
    });
}

/**
 * Show feedback toast notification
 * @param {string} message - Toast message
 * @param {boolean} isSuccess - Whether it's a success or error message
 */
function showFeedbackToast(message, isSuccess = true) {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.feedback-toast');
    existingToasts.forEach(toast => toast.remove());

    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'feedback-toast ' + (isSuccess ? 'success' : 'error');

    // Text prefix based on type
    const prefix = isSuccess ? 'Success:' : 'Error:';

    toast.innerHTML = `
        <span>${prefix}</span>
        <span style="font-weight:500;">${message}</span>
    `;

    document.body.appendChild(toast);

    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}

/**
 * Submit feedback via AJAX
 * @param {Event} event - Form submit event
 */
function submitFeedback(event) {
    event.preventDefault();
    
    // Get form data
    const form = document.getElementById('feedbackForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('.btn-submit-feedback');
    const rating = document.getElementById('feedback_rating').value;
    
    // Validate rating
    if (!rating || rating < 1 || rating > 5) {
        document.getElementById('ratingError').style.display = 'block';
        document.getElementById('ratingError').textContent = 'Please select a rating';
        return false;
    }
    
    // Disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.classList.add('loading');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Submitting...';
    
    // Make AJAX request
    fetch(window.URLROOT + '/feedback/submit', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
        }
        return response.json();
    })
    .then(data => {
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.classList.remove('loading');
        submitBtn.textContent = originalText;
        
        if (data.success) {
            // Show success toast
            showFeedbackToast(data.message || 'Feedback submitted successfully!', true);
            
            // Close modal
            closeFeedbackModal();
            
            // Optional: Reload page after 2 seconds to show updated rating
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            // Show error toast
            showFeedbackToast(data.message || 'Failed to submit feedback', false);
        }
    })
    .catch(error => {
        console.error('Feedback submission error:', error);
        
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.classList.remove('loading');
        submitBtn.textContent = originalText;
        
        // Show error toast
        showFeedbackToast('An error occurred. Please try again.', false);
    });
    
    return false;
}

/**
 * Initialize feedback button click handlers
 * Call this function when the page loads if buttons are dynamically created
 */
function initializeFeedbackButtons() {
    document.querySelectorAll('.give-feedback-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            const projectId = this.dataset.projectId || null;
            
            openFeedbackModal(userId, userName, projectId);
        });
    });
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFeedbackButtons);
} else {
    initializeFeedbackButtons();
}

/**
 * Fetch and display user feedback statistics
 * @param {number} userId - User ID
 * @param {number|null} projectId - Optional project ID
 * @param {string} targetElementId - ID of element to display stats
 */
function loadFeedbackStats(userId, projectId = null, targetElementId = 'feedbackStats') {
    let url = window.URLROOT + '/feedback/stats/' + userId;
    if (projectId) {
        url += '/' + projectId;
    }
    
    fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const targetEl = document.getElementById(targetElementId);
            if (targetEl) {
                targetEl.innerHTML = `
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:14px;color:#666;">Rating</span>
                        <span style="font-weight:600;color:#333;">${data.avgRating.toFixed(1)}</span>
                        <span style="color:#666;font-size:14px;">(${data.count} reviews)</span>
                    </div>
                `;
            }
        }
    })
    .catch(error => {
        console.error('Error loading feedback stats:', error);
    });
}

/**
 * Handle keyboard shortcuts for modal
 */
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('feedbackModal');
    if (!modal || modal.style.display !== 'flex') return;
    
    // Number keys 1-5 for quick rating
    if (e.key >= '1' && e.key <= '5') {
        const rating = parseInt(e.key);
        setRating(rating);
    }
    
    // Ctrl/Cmd + Enter to submit
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('feedbackForm').dispatchEvent(new Event('submit'));
    }
});

/**
 * Validate feedback form before submission
 * @returns {boolean}
 */
function validateFeedbackForm() {
    const rating = document.getElementById('feedback_rating').value;
    const userId = document.getElementById('feedback_user_id').value;
    
    // Check rating
    if (!rating || rating < 1 || rating > 5) {
        showFeedbackToast('Please select a rating between 1 and 5 stars', false);
        document.getElementById('ratingError').style.display = 'block';
        return false;
    }
    
    // Check user ID
    if (!userId) {
        showFeedbackToast('Invalid user selection', false);
        return false;
    }
    
    return true;
}

// Export functions for use in other scripts if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        submitFeedback,
        openFeedbackModal,
        closeFeedbackModal,
        setRating,
        loadFeedbackStats,
        initializeFeedbackButtons
    };
}
