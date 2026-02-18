/**
 * Community Forum JavaScript - Complete Fixed Version
 * Handles all community interactions for user dashboard
 */

console.log('🔄 Loading community_forum.js...');

// Get URLROOT from window (set in view files)
const URLROOT = window.URLROOT || '';

if (!URLROOT) {
    console.error('❌ ERROR: URLROOT not defined!');
}

console.log('✅ Community Forum JS loaded');
console.log('URLROOT:', URLROOT);

// ============================================
// COMMUNITY ACTIONS
// ============================================

/**
 * Join a community
 */
async function joinCommunity(communityId) {
    console.log('📥 Joining community:', communityId);
    
    if (!URLROOT) {
        alert('Configuration error. Please refresh the page.');
        return;
    }
    
    const button = event?.target;
    const originalText = button?.textContent || 'Join';
    
    if (button) {
        button.disabled = true;
        button.textContent = 'Joining...';
    }
    
    try {
        const response = await fetch(URLROOT + '/userdashboard/joinCommunity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'community_id=' + communityId
        });
        
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        
        const result = await response.json();
        console.log('✅ Join result:', result);
        
        if (result.success) {
            showNotification(result.message || 'Successfully joined community!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(result.message || 'Failed to join community', 'error');
            if (button) {
                button.disabled = false;
                button.textContent = originalText;
            }
        }
    } catch (error) {
        console.error('❌ Join error:', error);
        showNotification('Network error. Please try again.', 'error');
        if (button) {
            button.disabled = false;
            button.textContent = originalText;
        }
    }
}

/**
 * Leave a community
 */
async function leaveCommunity(communityId) {
    console.log('📤 Leaving community:', communityId);
    
    if (!confirm('Are you sure you want to leave this community?')) {
        return;
    }
    
    if (!URLROOT) {
        alert('Configuration error. Please refresh the page.');
        return;
    }
    
    const button = event?.target;
    const originalText = button?.textContent || 'Leave';
    
    if (button) {
        button.disabled = true;
        button.textContent = 'Leaving...';
    }
    
    try {
        const response = await fetch(URLROOT + '/userdashboard/leaveCommunity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'community_id=' + communityId
        });
        
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        
        const result = await response.json();
        console.log('✅ Leave result:', result);
        
        if (result.success) {
            showNotification(result.message || 'Successfully left community', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(result.message || 'Failed to leave community', 'error');
            if (button) {
                button.disabled = false;
                button.textContent = originalText;
            }
        }
    } catch (error) {
        console.error('❌ Leave error:', error);
        showNotification('Network error. Please try again.', 'error');
        if (button) {
            button.disabled = false;
            button.textContent = originalText;
        }
    }
}

/**
 * View community details
 */
function viewCommunity(communityId) {
    console.log('👁️ Viewing community:', communityId);
    
    if (!URLROOT) {
        alert('Configuration error. Please refresh the page.');
        return;
    }
    
    window.location.href = URLROOT + '/userdashboard/viewCommunity/' + communityId;
}

// ============================================
// NOTIFICATION SYSTEM
// ============================================

/**
 * Show notification toast
 */
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelectorAll('.custom-notification');
    existing.forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = 'custom-notification notification-' + type;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        animation: slideInFromRight 0.3s ease-out;
        max-width: 300px;
        font-weight: 500;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutToRight 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================
// CSS ANIMATIONS
// ============================================

if (!document.getElementById('community-notification-styles')) {
    const style = document.createElement('style');
    style.id = 'community-notification-styles';
    style.textContent = `
        @keyframes slideInFromRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutToRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        .custom-notification {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
    `;
    document.head.appendChild(style);
}

// ============================================
// MAKE FUNCTIONS GLOBALLY ACCESSIBLE
// ============================================

window.joinCommunity = joinCommunity;
window.leaveCommunity = leaveCommunity;
window.viewCommunity = viewCommunity;
window.showNotification = showNotification;
window.escapeHtml = escapeHtml;

console.log('✅ All community functions registered globally');
console.log('Available functions:', Object.keys(window).filter(k => ['joinCommunity', 'leaveCommunity', 'viewCommunity'].includes(k)));