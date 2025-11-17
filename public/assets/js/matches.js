// matches.js - Enhanced skill matching interactions

/**
 * Navigate to user profile
 */
function viewProfile(userId) {
    window.location.href = `${URLROOT}/userdashboard/viewProfile/${userId}`;
}

/**
 * Connect with a user
 */
async function connectWithUser(userId, userName) {
    // Prevent multiple clicks
    event.target.disabled = true;
    event.target.textContent = 'Connecting...';
    
    try {
        const response = await fetch(`${URLROOT}/userdashboard/connect`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            showNotification('success', `Connection request sent to ${userName}!`);
            
            // Update button state
            event.target.textContent = 'Request Sent';
            event.target.classList.add('btn-disabled');
            event.target.onclick = null;
            
            // Optionally redirect to chats
            setTimeout(() => {
                window.location.href = `${URLROOT}/userdashboard/chats`;
            }, 2000);
        } else {
            throw new Error(data.message || 'Failed to connect');
        }
    } catch (error) {
        console.error('Connection error:', error);
        showNotification('error', error.message || 'Failed to send connection request');
        
        // Re-enable button
        event.target.disabled = false;
        event.target.textContent = 'Connect';
    }
}

/**
 * Search matches by skill
 */
async function searchMatches(skillName, matchType = 'all') {
    if (!skillName || skillName.trim() === '') {
        return;
    }
    
    try {
        const response = await fetch(`${URLROOT}/userdashboard/searchMatches`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `skill=${encodeURIComponent(skillName)}&type=${matchType}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            displaySearchResults(data.matches, matchType);
        } else {
            showNotification('error', data.message || 'Search failed');
        }
    } catch (error) {
        console.error('Search error:', error);
        showNotification('error', 'Failed to search matches');
    }
}

/**
 * Display search results
 */
function displaySearchResults(matches, matchType) {
    const teachList = document.querySelector('.matches-column:first-child .matches-list');
    const learnList = document.querySelector('.matches-column:last-child .matches-list');
    
    if (matchType === 'teach' || matchType === 'all') {
        updateMatchList(teachList, matches.filter(m => m.type === 'teach'));
    }
    
    if (matchType === 'learn' || matchType === 'all') {
        updateMatchList(learnList, matches.filter(m => m.type === 'learn'));
    }
}

/**
 * Update match list with new data
 */
function updateMatchList(listElement, matches) {
    if (matches.length === 0) {
        listElement.innerHTML = `
            <div class="no-matches">
                <div class="empty-icon">🔍</div>
                <p>No matches found</p>
            </div>
        `;
        return;
    }
    
    listElement.innerHTML = matches.map(match => `
        <div class="match-card" onclick="viewProfile(${match.id})">
            <div class="match-avatar">${match.avatar}</div>
            <div class="match-info">
                <h3 class="match-name">${escapeHtml(match.name)}</h3>
                <p class="match-skill">${escapeHtml(match.skill)}</p>
            </div>
            <button class="btn-connect" onclick="event.stopPropagation(); connectWithUser(${match.id}, '${escapeHtml(match.name)}')">
                Connect
            </button>
        </div>
    `).join('');
}

/**
 * Show notification toast
 */
function showNotification(type, message) {
    // Remove existing notifications
    const existing = document.querySelector('.notification-toast');
    if (existing) {
        existing.remove();
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `notification-toast notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${type === 'success' ? '✓' : '✕'}</span>
            <span class="notification-message">${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => notification.classList.add('show'), 10);
    
    // Remove after 4 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 4000);
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
 * Initialize search functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add search bar if it doesn't exist
    const pageHeader = document.querySelector('.page-header');
    if (pageHeader && !document.querySelector('.matches-search')) {
        const searchBar = document.createElement('div');
        searchBar.className = 'matches-search';
        searchBar.innerHTML = `
            <input 
                type="text" 
                id="skill-search" 
                placeholder="Search by skill (e.g., web-development, python)..." 
                class="search-input"
            >
            <select id="match-type" class="search-select">
                <option value="all">All Matches</option>
                <option value="teach">People to Teach</option>
                <option value="learn">People to Learn From</option>
            </select>
            <button onclick="handleSearch()" class="btn-search">Search</button>
        `;
        pageHeader.appendChild(searchBar);
    }
});

/**
 * Handle search button click
 */
function handleSearch() {
    const skillInput = document.getElementById('skill-search');
    const matchTypeSelect = document.getElementById('match-type');
    
    if (skillInput && matchTypeSelect) {
        const skill = skillInput.value.trim();
        const type = matchTypeSelect.value;
        
        if (skill) {
            searchMatches(skill, type);
        }
    }
}

/**
 * Filter matches locally (for quick filtering without server call)
 */
function filterMatchesLocally(searchTerm) {
    const allCards = document.querySelectorAll('.match-card');
    const lowerSearch = searchTerm.toLowerCase();
    
    allCards.forEach(card => {
        const name = card.querySelector('.match-name').textContent.toLowerCase();
        const skill = card.querySelector('.match-skill').textContent.toLowerCase();
        
        if (name.includes(lowerSearch) || skill.includes(lowerSearch)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

/**
 * Real-time search as user types (with debounce)
 */
let searchTimeout;
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('skill-search');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const value = e.target.value.trim();
            
            if (value.length === 0) {
                // Reset to show all matches
                location.reload();
                return;
            }
            
            if (value.length >= 2) {
                searchTimeout = setTimeout(() => {
                    filterMatchesLocally(value);
                }, 300);
            }
        });
        
        // Search on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleSearch();
            }
        });
    }
});