// matches.js - Fixed: passes match_type, skill, direction to chat URL

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
    const matchCards = document.querySelectorAll(`.match-card`);
    matchCards.forEach(card => {
        const connectBtn = card.querySelector('.btn-connect');
        if (connectBtn && connectBtn.onclick.toString().includes(`connectWithUser(${userId}`)) {
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
    if (!confirm(`Are you sure you want to ${actionText} this request?`)) return;

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
            const requestCard = document.querySelector(`[onclick*="handleRequest(${exchangeId}"]`).closest('.request-card');
            if (requestCard) {
                requestCard.style.opacity = '0';
                requestCard.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    requestCard.remove();
                    updateRequestCount();
                    if (action === 'accept') {
                        setTimeout(() => location.reload(), 1000);
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
        if (badgeCount) badgeCount.textContent = requestCards.length;
        if (requestCards.length === 0) requestsSection.style.display = 'none';
    }
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

    document.querySelectorAll('.match-tier-section').forEach(section => {
        const tier = section.dataset.tier;
        section.style.display = (tierFilter === 'all' || tierFilter === tier) ? 'block' : 'none';
    });

    document.querySelectorAll('.match-card').forEach(card => {
        const cardSkills = JSON.parse(card.dataset.skills || '[]');
        const matchesSkill = skillFilter === 'all' || cardSkills.includes(skillFilter);
        const matchesTier = tierFilter === 'all' || card.dataset.tier === tierFilter;
        card.style.display = (matchesSkill && matchesTier) ? 'block' : 'none';
    });
}

/**
 * Navigate to chat, passing match context as URL params so ChatController
 * can determine whether to show role selector in the transaction modal.
 *
 * @param {number} userId       - Partner's user ID
 * @param {string} matchType    - 'mutual' | 'multi' | 'single'
 * @param {string} skillOrTeach - For mutual: the skill current user teaches.
 *                                For single/multi: the matched skill name.
 * @param {string} dirOrLearn  - For mutual: the skill current user learns.
 *                                For single/multi: 'teacher' or 'learner' (current user's fixed role).
 */
function openSkillChat(userId, matchType = null, skillOrTeach = null, dirOrLearn = null) {
    let url = `${URLROOT}/chat/user/${userId}`;
    const params = new URLSearchParams();

    if (matchType)    params.set('match_type', matchType);
    if (skillOrTeach) params.set('skill', skillOrTeach);
    if (dirOrLearn)   params.set('dir', dirOrLearn);

    const qs = params.toString();
    if (qs) url += '?' + qs;

    window.location.href = url;
}

function showNotification(message, type = 'info') {
    const existing = document.querySelector('.notification-banner');
    if (existing) existing.remove();

    const notification = document.createElement('div');
    notification.className = `notification-banner notification-${type}`;
    notification.innerHTML = `
        <span class="notification-message">${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">×</button>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

function searchMatches() {
    const searchInput = document.querySelector('.matches-search-input');
    if (!searchInput) return;
    const query = searchInput.value.toLowerCase();
    document.querySelectorAll('.match-card').forEach(card => {
        const name = card.querySelector('.match-name')?.textContent.toLowerCase() || '';
        let skillText = '';
        card.querySelectorAll('.skill-name').forEach(s => skillText += s.textContent.toLowerCase() + ' ');
        card.style.display = (name.includes(query) || skillText.includes(query)) ? 'block' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const tierFilter = document.getElementById('match-tier-filter');
    const skillFilter = document.getElementById('skill-filter');
    if (tierFilter)  tierFilter.addEventListener('change', applyFilters);
    if (skillFilter) skillFilter.addEventListener('change', applyFilters);

    const searchInput = document.querySelector('.matches-search-input');
    if (searchInput) searchInput.addEventListener('input', searchMatches);

    console.log('Matches page loaded. URLROOT:', URLROOT);
});