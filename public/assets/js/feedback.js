/**
 * Feedback Module JavaScript
 * Handles feedback modal, star ratings, tags, and AJAX submission
 */

// Global state
let selectedTags = [];

/**
 * Open feedback modal with user context
 */
function openFeedbackModal(userId, userName, userAvatar, contextType, contextId, contextName) {
    console.log('Opening feedback modal for:', userName, userId, contextType, contextId);
    
    // Set hidden form fields
    document.getElementById('feedbackUserId').value = userId;
    document.getElementById('feedbackContextType').value = contextType;
    document.getElementById('feedbackContextId').value = contextId;
    
    // Set visible user info
    document.getElementById('feedbackMemberName').textContent = userName;
    document.getElementById('feedbackMemberAvatar').src = userAvatar || '/public/assets/images/default-avatar.png';
    
    // Set context info
    let contextText = contextType === 'project' ? 'Project: ' : 'Session: ';
    contextText += contextName || 'N/A';
    document.getElementById('feedbackMemberContext').textContent = contextText;
    
    // Reset form
    resetFeedbackForm();
    
    // Show modal
    const modal = document.getElementById('feedbackModal');
    if (modal) {
        modal.style.display = 'flex';
        
        // Scroll modal content to top
        const modalContent = modal.querySelector('.feedback-modal-content');
        if (modalContent) {
            modalContent.scrollTop = 0;
        }
        
        console.log('Modal opened successfully');
    } else {
        console.error('Feedback modal not found!');
    }
}

/**
 * Close feedback modal
 */
function closeFeedbackModal() {
    document.getElementById('feedbackModal').style.display = 'none';
    resetFeedbackForm();
}

/**
 * Reset feedback form to initial state
 */
function resetFeedbackForm() {
    const form = document.getElementById('feedbackForm');
    if (form) {
        form.reset();
    }
    
    const ratingInput = document.getElementById('feedbackRating');
    if (ratingInput) {
        ratingInput.value = '';
    }
    
    // Reset stars
    document.querySelectorAll('.star').forEach(star => {
        star.textContent = '☆';
        star.classList.remove('active');
    });
    
    // Reset tags
    selectedTags = [];
    document.querySelectorAll('.tag-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    const tagsInput = document.getElementById('feedbackTags');
    if (tagsInput) {
        tagsInput.value = '';
    }
    
    // Reset counter and rating text
    const charCounter = document.querySelector('.char-counter');
    if (charCounter) {
        charCounter.textContent = '0 / 500 characters';
    }
    
    const ratingText = document.querySelector('.rating-text');
    if (ratingText) {
        ratingText.style.display = 'none';
    }
}

/**
 * Initialize feedback functionality on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Feedback module loaded');
    
    // Star rating interaction
    const stars = document.querySelectorAll('.star');
    const ratingText = document.querySelector('.rating-text');
    const ratingLabels = ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
    
    stars.forEach(star => {
        // Click event
        star.addEventListener('click', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            document.getElementById('feedbackRating').value = rating;
            
            // Update stars
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.textContent = '★';
                    s.classList.add('active');
                } else {
                    s.textContent = '☆';
                    s.classList.remove('active');
                }
            });
            
            // Show rating label
            ratingText.textContent = ratingLabels[rating - 1];
            ratingText.style.display = 'block';
        });
        
        // Hover effect
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.style.color = '#FFD700';
                }
            });
        });
        
        star.addEventListener('mouseleave', function() {
            stars.forEach(s => {
                if (!s.classList.contains('active')) {
                    s.style.color = '';
                }
            });
        });
    });
    
    // Tag buttons interaction
    document.querySelectorAll('.tag-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tag = this.getAttribute('data-tag');
            
            if (this.classList.contains('active')) {
                // Remove tag
                this.classList.remove('active');
                selectedTags = selectedTags.filter(t => t !== tag);
            } else {
                // Add tag
                this.classList.add('active');
                selectedTags.push(tag);
            }
            
            // Update hidden field
            document.getElementById('feedbackTags').value = selectedTags.join(',');
        });
    });
    
    // Character counter
    const commentField = document.getElementById('feedbackComment');
    if (commentField) {
        commentField.addEventListener('input', function() {
            const length = this.value.length;
            document.querySelector('.char-counter').textContent = `${length} / 1000 characters`;
        });
    }
    
    // Form submission
    const feedbackForm = document.getElementById('feedbackForm');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate rating
            const rating = document.getElementById('feedbackRating').value;
            if (!rating) {
                showFeedbackToast('Please select a rating', 'error');
                return;
            }
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('.btn-primary');
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
            
            // Submit via AJAX to the store endpoint
            fetch(window.URLROOT + '/Feedback/store', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showFeedbackToast(data.message || 'Feedback submitted successfully!', 'success');
                    closeFeedbackModal();
                    
                    // Update rating display on the page if available
                    if (data.stats) {
                        updateMemberRating(formData.get('user_id'), data.stats.avg_rating);
                    }
                    
                    // Reload page after short delay to show updated data
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showFeedbackToast(data.message || 'Failed to submit feedback', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Feedback';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showFeedbackToast('An error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Feedback';
            });
        });
    }
    
    // Attach event listeners to "Give Feedback" buttons
    const feedbackButtons = document.querySelectorAll('.give-feedback-btn');
    console.log('Found', feedbackButtons.length, 'feedback buttons');
    
    feedbackButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Feedback button clicked!', this);
            
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            const userAvatar = this.getAttribute('data-user-avatar');
            const contextType = this.getAttribute('data-context-type') || 'project';
            const contextId = this.getAttribute('data-context-id');
            const contextName = this.getAttribute('data-context-name') || '';
            
            console.log('Button data:', { userId, userName, contextType, contextId, contextName });
            
            openFeedbackModal(userId, userName, userAvatar, contextType, contextId, contextName);
        });
    });
});

/**
 * Show toast notification
 */
function showFeedbackToast(message, type) {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.feedback-toast');
    existingToasts.forEach(t => t.remove());
    
    // Create new toast
    const toast = document.createElement('div');
    toast.className = `feedback-toast ${type}`;
    
    const icon = type === 'success' ? '<i class="ph ph-check-circle"></i>' : '<i class="ph ph-warning-circle"></i>';
    toast.innerHTML = icon + '<span>' + message + '</span>';
    
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Hide and remove toast
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

/**
 * Update member rating display on the page
 */
function updateMemberRating(userId, newRating) {
    // Find the member card and update rating display
    const memberCard = document.querySelector(`[data-user-id="${userId}"]`)?.closest('.member-card');
    if (memberCard) {
        const ratingElement = memberCard.querySelector('.stat-item');
        if (ratingElement && ratingElement.textContent.includes('Rating:')) {
            ratingElement.innerHTML = `<span class="icon"><i class="ph ph-star"></i></span> Rating: ${newRating}`;
        }
    }
}

/**
 * Close modal on ESC key
 */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFeedbackModal();
    }
});

/**
 * Fetch and display feedback stats (optional - for stats page)
 */
function fetchFeedbackStats(userId, contextType, contextId) {
    const url = `${window.URLROOT}/Feedback/stats?user_id=${userId}&context_type=${contextType}&context_id=${contextId}`;
    
    fetch(url, {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.stats) {
            console.log('User Stats:', data.stats);
            // You can use this data to display stats elsewhere on the page
        }
    })
    .catch(error => console.error('Error fetching stats:', error));
}