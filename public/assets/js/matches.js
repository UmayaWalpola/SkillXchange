
// matches.js - Enhanced for tiered match system

/**
 * Handle connection request (accept/reject)
 */
async function handleRequest(exchangeId, action) {
    const button = event.target;
    const originalText = button.textContent;
    
    if (button.disabled) {
        console.log('Button already disabled, ignoring click');
        return;
    }
    
    button.disabled = true;
    button.textContent = action === 'accept' ? 'Accepting...' : 'Rejecting...';
    
    try {
        const response = await fetch(`${URLROOT}/userdashboard/handleRequest`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `exchange_id=${exchangeId}&action=${action}`
        });
        
        if (!response.ok) {
            throw new Error(`Server error: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message);
            
            const requestCard = button.closest('.request-card');
            if (requestCard) {
                requestCard.style.transition = 'all 0.3s ease';
                requestCard.style.opacity = '0';
                requestCard.style.transform = 'translateX(-100%)';
                
                setTimeout(() => {
                    requestCard.remove();
                    
                    const badge = document.querySelector('.badge-count');
                    if (badge) {
                        const currentCount = parseInt(badge.textContent) || 0;
                        const newCount = Math.max(0, currentCount - 1);
                        badge.textContent = newCount;
                        
                        if (newCount === 0) {
                            const section = document.querySelector('.connection-requests-section');
                            if (section) {
                                section.style.transition = 'all 0.3s ease';
                                section.style.opacity = '0';
                                setTimeout(() => section.remove(), 300);
                            }
                        }
                    }
                }, 300);
            }
            
            if (action === 'accept') {
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }
        } else {
            throw new Error(data.message || 'Failed to process request');
        }
    } catch (error) {
        console.error('Request error:', error);
        showNotification('error', error.message || 'Network error occurred');
        
        button.disabled = false;
        button.textContent = originalText;
    }
}

/**
 * Navigate to user profile
 */
function viewProfile(userId) {
    if (!userId) {
        console.error('Invalid user ID');
        return;
    }
    window.location.href = `${URLROOT}/userdashboard/viewProfile/${userId}`;
}

/**
 * Connect with a user
 */
async function connectWithUser(userId, userName) {
    const button = event.target;
    
    if (button.disabled || button.classList.contains('btn-disabled')) {
        console.log('Connection already sent or in progress');
        return;
    }
    
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Connecting...';
    
    try {
        const response = await fetch(`${URLROOT}/userdashboard/connect`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}`
        });
        
        if (!response.ok) {
            throw new Error(`Server error: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', `Connection request sent to ${userName}!`);
            
            button.textContent = 'Request Sent';
            button.classList.add('btn-disabled');
            button.onclick = null;
            
            setTimeout(() => {
                showNotification('info', 'Check notifications for updates!');
            }, 2000);
        } else {
            throw new Error(data.message || 'Failed to connect');
        }
    } catch (error) {
        console.error('Connection error:', error);
        showNotification('error', error.message || 'Failed to send connection request');
        
        button.disabled = false;
        button.textContent = originalText;
    }
}

/**
 * Filter matches by tier, skill, and other criteria
 */
function filterMatches() {
    const tierFilter = document.getElementById('match-tier-filter')?.value || 'all';
    const skillFilter = document.getElementById('skill-filter')?.value || 'all';
    
    console.log('Filtering:', { tierFilter, skillFilter });
    
    // Get all tier sections
    const tierSections = document.querySelectorAll('.match-tier-section');
    let visibleCounts = { perfect: 0, great: 0, good: 0 };
    
    tierSections.forEach(section => {
        const sectionTier = section.dataset.tier;
        const matchCards = section.querySelectorAll('.match-card');
        let sectionVisible = false;
        let cardCount = 0;
        
        // Hide/show entire section based on tier filter
        if (tierFilter !== 'all' && tierFilter !== sectionTier) {
            section.classList.add('hidden');
            return;
        } else {
            section.classList.remove('hidden');
        }
        
        // Filter individual cards by skill
        matchCards.forEach(card => {
            let showCard = true;
            
            if (skillFilter !== 'all') {
                const cardSkills = JSON.parse(card.dataset.skills || '[]');
                
                // Check if any of the card's skills match the filter
                showCard = cardSkills.some(skill => skill === skillFilter);
            }
            
            card.classList.toggle('hidden', !showCard);
            
            if (showCard) {
                sectionVisible = true;
                cardCount++;
                visibleCounts[sectionTier]++;
            }
        });
        
        // Update section count
        const countElement = section.querySelector('.tier-count');
        if (countElement) {
            countElement.textContent = `(${cardCount} found)`;
        }
        
        // Hide section if no cards visible
        if (!sectionVisible) {
            section.classList.add('hidden');
        }
    });
    
    // Update summary counts
    updateSummaryCounts(visibleCounts);
    
    // Show "no matches" message if everything is filtered out
    updateNoMatchesState(visibleCounts);
}

/**
 * Update the summary counts at the top of the page
 */
function updateSummaryCounts(counts) {
    const totalCount = counts.perfect + counts.great + counts.good;
    
    const summaryItems = document.querySelectorAll('.match-summary .summary-item');
    summaryItems.forEach(item => {
        const strong = item.querySelector('strong');
        if (!strong) return;
        
        if (item.classList.contains('perfect')) {
            strong.textContent = counts.perfect;
        } else if (item.classList.contains('great')) {
            strong.textContent = counts.great;
        } else if (item.classList.contains('good')) {
            strong.textContent = counts.good;
        } else {
            // Total count (first item)
            strong.textContent = totalCount;
        }
    });
}

/**
 * Show/hide "no matches" message when all cards are filtered out
 */
function updateNoMatchesState(counts) {
    const totalVisible = counts.perfect + counts.great + counts.good;
    
    let noMatchesEl = document.querySelector('.no-matches-filtered');
    
    if (totalVisible === 0) {
        // Check if we have ANY matches at all (not filtered)
        const allCards = document.querySelectorAll('.match-card');
        const hasMatches = allCards.length > 0;
        
        if (hasMatches && !noMatchesEl) {
            // Create filtered empty state
            noMatchesEl = document.createElement('div');
            noMatchesEl.className = 'no-matches-filtered';
            noMatchesEl.innerHTML = `
                <div class="empty-illustration">🔍</div>
                <h2>No matches found with current filters</h2>
                <p>Try adjusting your filters to see more matches</p>
                <button class="btn-clear-filters" onclick="clearFilters()" style="
                    margin-top: 1rem;
                    padding: 0.75rem 1.5rem;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-weight: 600;
                    cursor: pointer;
                ">Clear All Filters</button>
            `;
            
            const matchesPage = document.querySelector('.matches-page');
            if (matchesPage) {
                matchesPage.appendChild(noMatchesEl);
            }
        }
        
        if (noMatchesEl) {
            noMatchesEl.style.display = 'block';
        }
    } else {
        if (noMatchesEl) {
            noMatchesEl.style.display = 'none';
        }
    }
}

/**
 * Clear all active filters
 */
function clearFilters() {
    const tierFilter = document.getElementById('match-tier-filter');
    const skillFilter = document.getElementById('skill-filter');
    
    if (tierFilter) tierFilter.value = 'all';
    if (skillFilter) skillFilter.value = 'all';
    
    // Remove any filtered empty state
    const noMatchesEl = document.querySelector('.no-matches-filtered');
    if (noMatchesEl) {
        noMatchesEl.style.display = 'none';
    }
    
    // Show all sections and cards
    document.querySelectorAll('.match-tier-section').forEach(section => {
        section.classList.remove('hidden');
        
        // Reset section counts
        const cards = section.querySelectorAll('.match-card');
        cards.forEach(card => card.classList.remove('hidden'));
        
        const countElement = section.querySelector('.tier-count');
        if (countElement) {
            countElement.textContent = `(${cards.length} found)`;
        }
    });
    
    // Reset summary counts
    const perfectCount = document.querySelectorAll('[data-tier="perfect"] .match-card').length;
    const greatCount = document.querySelectorAll('[data-tier="great"] .match-card').length;
    const goodCount = document.querySelectorAll('[data-tier="good"] .match-card').length;
    
    updateSummaryCounts({
        perfect: perfectCount,
        great: greatCount,
        good: goodCount
    });
    
    showNotification('info', 'Filters cleared - showing all matches');
}

/**
 * Show notification toast
 */
function showNotification(type, message) {
    const existing = document.querySelector('.notification-toast');
    if (existing) {
        existing.remove();
    }
    
    const icons = {
        success: '✓',
        error: '✕',
        info: 'ℹ'
    };
    
    const notification = document.createElement('div');
    notification.className = `notification-toast notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${icons[type] || 'ℹ'}</span>
            <span class="notification-message">${escapeHtml(message)}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 10);
    
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
 * Initialize event listeners on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Tiered matches page loaded, initializing...');
    
    // Initialize filter listeners
    const tierFilter = document.getElementById('match-tier-filter');
    const skillFilter = document.getElementById('skill-filter');
    
    if (tierFilter) {
        tierFilter.addEventListener('change', filterMatches);
        console.log('Tier filter initialized');
    }
    
    if (skillFilter) {
        skillFilter.addEventListener('change', filterMatches);
        console.log('Skill filter initialized');
    }
    
    // Log initial state
    const perfectCards = document.querySelectorAll('[data-tier="perfect"] .match-card');
    const greatCards = document.querySelectorAll('[data-tier="great"] .match-card');
    const goodCards = document.querySelectorAll('[data-tier="good"] .match-card');
    
    console.log(`Found ${perfectCards.length} perfect, ${greatCards.length} great, and ${goodCards.length} good matches`);
    
    // Add smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add animation observer for cards
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'slideUp 0.5s ease-out forwards';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.match-card').forEach(card => {
        observer.observe(card);
    });
});

/**
 * Global error handler
 */
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    showNotification('error', 'Something went wrong. Please refresh and try again.');
});

/**
 * Handle visibility change (tab switching)
 */
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        console.log('Page visible again, checking for updates...');
        // Could add logic here to check for new matches
    }
});