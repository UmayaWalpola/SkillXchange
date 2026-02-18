
// matches.js - Complete version with chat functionality

function connectWithUser(userId, userName) {
    if (!confirm(`Send connection request to ${userName}?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('user_id', userId);

    fetch(`${URLROOT}/userdashboard/connect`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Connection request sent!', 'success');
            
            // Update the button to show pending state
            updateButtonToPending(userId);
        } else {
            showNotification(data.message || 'Failed to send request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Network error. Please try again.', 'error');
    });
}

function updateButtonToPending(userId) {
    // Find all match cards for this user
    const matchCards = document.querySelectorAll(`.match-card`);
    
    matchCards.forEach(card => {
        const connectBtn = card.querySelector('.btn-connect');
        if (connectBtn && connectBtn.onclick.toString().includes(`connectWithUser(${userId}`)) {
            // Replace button with pending badge
            const footer = connectBtn.closest('.match-footer');
            connectBtn.remove();
            
            const pendingBadge = document.createElement('span');
            pendingBadge.className = 'badge badge-warning';
            pendingBadge.innerHTML = '⏳ Request Pending';
            footer.appendChild(pendingBadge);
        }
    });
}

function handleRequest(exchangeId, action) {
    const actionText = action === 'accept' ? 'accept' : 'reject';
    
    if (!confirm(`Are you sure you want to ${actionText} this request?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('exchange_id', exchangeId);
    formData.append('action', action);

    fetch(`${URLROOT}/userdashboard/handleRequest`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Remove the request card with animation
            const requestCard = document.querySelector(`[onclick*="handleRequest(${exchangeId}"]`).closest('.request-card');
            if (requestCard) {
                requestCard.style.opacity = '0';
                requestCard.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    requestCard.remove();
                    updateRequestCount();
                    
                    // If accepted, update the match card status
                    if (action === 'accept') {
                        // Reload page to show updated connection status
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                }, 300);
            }
        } else {
            showNotification(data.message || 'Action failed', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Network error. Please try again.', 'error');
    });
}

function updateRequestCount() {
    const requestsSection = document.querySelector('.connection-requests-section');
    if (requestsSection) {
        const requestCards = requestsSection.querySelectorAll('.request-card');
        const badgeCount = requestsSection.querySelector('.badge-count');
        
        if (badgeCount) {
            badgeCount.textContent = requestCards.length;
        }
        
        // Hide section if no requests
        if (requestCards.length === 0) {
            requestsSection.style.display = 'none';
        }
    }
}

/**
 * NEW: Open chat with a connected user
 */
function openChat(userId) {
    console.log('Opening chat with user:', userId);
    // Use the same route as the sidebar: /userdashboard/chats?partnerId={id}
    const target = `${URLROOT}/userdashboard/chats?partnerId=${userId}`;
    console.log('Redirecting to:', target);
    window.location.href = target;
}

function viewProfile(userId) {
    window.location.href = `${URLROOT}/userdashboard/viewProfile/${userId}`;
}

function clearFilters() {
    document.getElementById('match-tier-filter').value = 'all';
    document.getElementById('skill-filter').value = 'all';
    applyFilters();
}

function applyFilters() {
    const tierFilter = document.getElementById('match-tier-filter').value;
    const skillFilter = document.getElementById('skill-filter').value;
    
    const sections = document.querySelectorAll('.match-tier-section');
    const cards = document.querySelectorAll('.match-card');
    
    // Show/hide sections based on tier filter
    sections.forEach(section => {
        const tier = section.dataset.tier;
        if (tierFilter === 'all' || tierFilter === tier) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
    
    // Filter cards by skill
    cards.forEach(card => {
        const cardSkills = JSON.parse(card.dataset.skills || '[]');
        const matchesSkill = skillFilter === 'all' || cardSkills.includes(skillFilter);
        const matchesTier = tierFilter === 'all' || card.dataset.tier === tierFilter;
        
        if (matchesSkill && matchesTier) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelector('.notification-banner');
    if (existing) {
        existing.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification-banner notification-${type}`;
    notification.innerHTML = `
        <span class="notification-message">${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Search functionality (if needed)
function searchMatches() {
    const searchInput = document.querySelector('.matches-search-input');
    if (!searchInput) return;
    
    const query = searchInput.value.toLowerCase();
    const cards = document.querySelectorAll('.match-card');
    
    cards.forEach(card => {
        const name = card.querySelector('.match-name')?.textContent.toLowerCase() || '';
        const skills = card.querySelectorAll('.skill-name');
        let skillText = '';
        skills.forEach(skill => skillText += skill.textContent.toLowerCase() + ' ');
        
        if (name.includes(query) || skillText.includes(query)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Filter event listeners
    const tierFilter = document.getElementById('match-tier-filter');
    const skillFilter = document.getElementById('skill-filter');
    
    if (tierFilter) {
        tierFilter.addEventListener('change', applyFilters);
    }
    
    if (skillFilter) {
        skillFilter.addEventListener('change', applyFilters);
    }
    
    // Search functionality
    const searchInput = document.querySelector('.matches-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', searchMatches);
    }
    
    // Log page load for debugging
    console.log('Matches page loaded successfully');
    console.log('URLROOT:', URLROOT);
});