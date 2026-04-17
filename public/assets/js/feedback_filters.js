/**
 * Feedback Filters and List Management
 * Handles filtering, sorting, and pagination of feedback items
 */

// Current state
let currentFilters = {
    user_id: window.CURRENT_USER_ID,
    sort: 'newest',
    page: 1,
    limit: 10
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Read filters from URL params
    loadFiltersFromURL();
    
    // Attach event listeners
    attachEventListeners();
    
    // Load initial feedback list
    loadFeedback();
});

/**
 * Load filters from URL query parameters
 */
function loadFiltersFromURL() {
    const params = new URLSearchParams(window.location.search);
    
    if (params.get('sort')) {
        currentFilters.sort = params.get('sort');
        document.getElementById('sortSelect').value = currentFilters.sort;
    }
    
    if (params.get('rating')) {
        currentFilters.rating = params.get('rating');
        document.getElementById('ratingFilter').value = currentFilters.rating;
    }
    
    if (params.get('tag')) {
        currentFilters.tag = params.get('tag');
        document.getElementById('tagFilter').value = currentFilters.tag;
    }
    
    if (params.get('page')) {
        currentFilters.page = parseInt(params.get('page'));
    }
}

/**
 * Attach event listeners to filter controls
 */
function attachEventListeners() {
    // Sort change
    document.getElementById('sortSelect').addEventListener('change', function() {
        currentFilters.sort = this.value;
        currentFilters.page = 1;
        loadFeedback();
    });
    
    // Rating filter change
    //Listening to changes 
    document.getElementById('ratingFilter').addEventListener('change', function() {
        if (this.value) {
            currentFilters.rating = this.value;
        } else {
            delete currentFilters.rating;
        }
        currentFilters.page = 1;
        loadFeedback();
    });
    
    // Tag filter change
    document.getElementById('tagFilter').addEventListener('change', function() {
        if (this.value) {
            currentFilters.tag = this.value;
        } else {
            delete currentFilters.tag;
        }
        currentFilters.page = 1;          // Reset to first page when changing filters
        loadFeedback();
    });
    
    // Clear filters button
    document.getElementById('clearFilters').addEventListener('click', function() {
        currentFilters = {
            user_id: window.CURRENT_USER_ID,
            sort: 'newest',
            page: 1,
            limit: 10
        };
        
        document.getElementById('sortSelect').value = 'newest'; // Reset sort to default
        document.getElementById('ratingFilter').value = '';    // Clear rating filter
        document.getElementById('tagFilter').value = '';          // Clear tag filter
        
        loadFeedback();            // Load feedback with default filters
    });
    
    // Pagination buttons
    document.getElementById('prevPage').addEventListener('click', function() {
        if (currentFilters.page > 1) {
            currentFilters.page--;
            loadFeedback();
        }
    });
    
    document.getElementById('nextPage').addEventListener('click', function() {
        currentFilters.page++;
        loadFeedback();
    });
}

/**
 * Load feedback from server with current filters
 */
function loadFeedback() {
    // Build query string
    const queryString = new URLSearchParams(currentFilters).toString();
    
    // Update URL without reload
    const newURL = window.location.pathname + '?' + queryString;
    history.replaceState(null, '', newURL);
    
    // Show loading state
    const container = document.getElementById('feedbackContainer');
    container.innerHTML = `
        <div style="text-align:center;padding:60px 20px;">
            <div style="font-size:48px;color:#e0e0e0;margin-bottom:15px;">
                <i class="ph ph-spinner"></i>
            </div>
            <p style="color:#999;font-size:16px;">Loading feedback...</p>
        </div>
    `;
    
    // Fetch feedback
    fetch(`${window.URLROOT}/Feedback/list?${queryString}`, {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderFeedback(data.items, data.pagination);
        } else {
            showError(data.message || 'Failed to load feedback');
        }
    })
    .catch(error => {
        console.error('Error loading feedback:', error);
        showError('An error occurred while loading feedback');
    });
}

/**
 * Render feedback items in the container
 */
function renderFeedback(items, pagination) {
    const container = document.getElementById('feedbackContainer');
    
    if (items.length === 0) {
        container.innerHTML = `
            <div style="text-align:center;padding:60px 20px;background:white;border:2px solid #e1eefb;border-radius:16px;">
                <div style="font-size:64px;color:#e0e0e0;margin-bottom:15px;">
                    <i class="ph ph-star"></i>
                </div>
                <h3 style="font-size:20px;color:#333;margin-bottom:10px;">No Feedback Found</h3>
                <p style="color:#999;font-size:14px;">Try adjusting your filters</p>
            </div>
        `;
        document.getElementById('paginationContainer').style.display = 'none';
        return;
    }
    
    // Render feedback cards
    let html = '<div style="display:grid;gap:20px;">';
    
    items.forEach(feedback => {
        const stars = generateStars(feedback.rating);
        const tags = feedback.tags ? feedback.tags.split(',').map(t => t.trim()).filter(Boolean) : [];
        const date = new Date(feedback.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        
        html += `
            <div class="feedback-card" style="background:white;border:2px solid #e1eefb;border-radius:16px;padding:25px;transition:all 0.2s;">
                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:15px;gap:20px;">
                    <div style="display:flex;gap:15px;align-items:start;flex:1;">
                        ${feedback.reviewer_picture ? 
                            `<img src="${escapeHtml(window.URLROOT + '/' + feedback.reviewer_picture)}" alt="Avatar" style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:2px solid #e1eefb;">` :
                            `<div style="width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,var(--primary-blue),var(--accent-blue));color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:20px;">${escapeHtml(feedback.reviewer_name ? feedback.reviewer_name.charAt(0).toUpperCase() : '?')}</div>`
                        }
                        <div>
                            <div style="font-weight:700;font-size:16px;color:#1a1a1a;margin-bottom:5px;">
                                ${escapeHtml(feedback.reviewer_name || 'Unknown')}
                            </div>
                            <div style="color:#666;font-size:13px;">${date}</div>
                            ${feedback.context_name ? `<div style="color:#6583aa;font-size:13px;margin-top:3px;">Project: ${escapeHtml(feedback.context_name)}</div>` : ''}
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:24px;color:#FFD700;margin-bottom:5px;">${stars}</div>
                        <div style="font-size:14px;font-weight:600;color:#666;">${feedback.rating}/5</div>
                    </div>
                </div>
                
                ${tags.length > 0 ? `
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:15px;">
                    ${tags.map(tag => `
                        <span style="background:#e1eefb;color:#6583aa;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:600;">
                            ${escapeHtml(tag)}
                        </span>
                    `).join('')}
                </div>
                ` : ''}
                
                ${feedback.comment ? `
                <div style="color:#333;font-size:14px;line-height:1.6;padding:15px;background:#f8f9fa;border-radius:8px;margin-bottom:12px;">
                    ${escapeHtml(feedback.comment)}
                </div>
                ` : ''}
                
                <div style="border-top:1px solid #e1eefb;padding-top:12px;margin-top:12px;display:flex;justify-content:flex-end;">
                    ${
                        // Only show Report button for feedback the USER RECEIVED (not feedback they gave)
                        (feedback.user_id == window.CURRENT_USER_ID)
                        ? `<button class="report-feedback-btn" data-feedback-id="${feedback.id}" style="background:transparent;border:1px solid #e74c3c;color:#e74c3c;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;gap:6px;">
                            <i class="ph ph-flag" style="font-size:16px;"></i>
                            Report
                           </button>`
                        : ''
                    }
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
    
    // Update pagination
    updatePagination(pagination);
}

/**
 * Generate star rating HTML
 */
function generateStars(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        stars += i <= rating ? '★' : '☆';
    }
    return stars;
}

/**
 * Update pagination controls
 */
function updatePagination(pagination) {
    const container = document.getElementById('paginationContainer');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const pageInfo = document.getElementById('pageInfo');
    
    if (pagination.total_pages <= 1) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    pageInfo.textContent = `Page ${pagination.page} of ${pagination.total_pages}`;
    
    prevBtn.disabled = pagination.page <= 1;
    nextBtn.disabled = pagination.page >= pagination.total_pages;
    
    prevBtn.style.opacity = prevBtn.disabled ? '0.5' : '1';
    prevBtn.style.cursor = prevBtn.disabled ? 'not-allowed' : 'pointer';
    nextBtn.style.opacity = nextBtn.disabled ? '0.5' : '1';
    nextBtn.style.cursor = nextBtn.disabled ? 'not-allowed' : 'pointer';
}

/**
 * Show error message
 */
function showError(message) {
    const container = document.getElementById('feedbackContainer');
    container.innerHTML = `
        <div style="text-align:center;padding:60px 20px;background:#fee2e2;border:2px solid #ef4444;border-radius:16px;">
            <div style="font-size:64px;color:#ef4444;margin-bottom:15px;">
                <i class="ph ph-warning-circle"></i>
            </div>
            <h3 style="font-size:20px;color:#991b1b;margin-bottom:10px;">Error</h3>
            <p style="color:#666;font-size:14px;">${escapeHtml(message)}</p>
        </div>
    `;
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Event delegation for report buttons
 */
document.addEventListener('click', function(e) {
    if (e.target.closest('.report-feedback-btn')) {
        const btn = e.target.closest('.report-feedback-btn');
        const feedbackId = btn.dataset.feedbackId;
        
        // Open feedback-specific report modal (defined in feedback_report.js)
        if (typeof openFeedbackReportModal === 'function') {
            openFeedbackReportModal(feedbackId);
        } else {
            console.error('Feedback report modal function not found. Make sure feedback_report.js is loaded.');
        }
    }
});
