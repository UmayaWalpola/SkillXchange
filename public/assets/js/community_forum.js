

// Get configuration from PHP
const currentUser = {
    id: window.currentUserId || 1,
    name: window.currentUserName || 'You'
};
const urlRoot = window.urlRoot || '';

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Communities module loaded');
    console.log('URL Root:', urlRoot);
    console.log('Current User:', currentUser);
    
    if (!urlRoot) {
        console.error('❌ ERROR: URLROOT not defined!');
        alert('Configuration error: URLROOT is not set. Please refresh the page.');
        return;
    }
    
    console.log('✅ All checks passed. Buttons should work now.');
});

// ============================================
// COMMUNITY ACTIONS
// ============================================

/**
 * Join a community
 */
async function joinCommunity(id) {
    console.log('Joining community:', id);
    
    if (!urlRoot) {
        alert('Configuration error. Please refresh the page.');
        return;
    }
    
    try {
        const response = await fetch(urlRoot + '/userdashboard/joinCommunity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `community_id=${id}`
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Join result:', result);
        
        if (result.success) {
            showNotification(result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(result.message || 'Failed to join community', 'error');
        }
    } catch (error) {
        console.error('Join error:', error);
        showNotification('Network error. Please try again.', 'error');
    }
}

/**
 * Leave a community
 */
async function leaveCommunity(id) {
    if (!confirm('Are you sure you want to leave this community?')) {
        return;
    }
    
    console.log('Leaving community:', id);
    
    try {
        const response = await fetch(urlRoot + '/userdashboard/leaveCommunity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `community_id=${id}`
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Leave result:', result);
        
        if (result.success) {
            showNotification(result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(result.message || 'Failed to leave community', 'error');
        }
    } catch (error) {
        console.error('Leave error:', error);
        showNotification('Network error. Please try again.', 'error');
    }
}

/**
 * View community details
 */
function viewCommunity(communityId) {
    console.log('Viewing community:', communityId);
    window.location.href = urlRoot + '/userdashboard/viewCommunity/' + communityId;
}

// ============================================
// COMMUNITY MESSAGING (for detail page)
// ============================================


async function createCommunityPost(communityId) {
    const titleInput = document.getElementById('postTitle');
    const contentInput = document.getElementById('postContent');
    const postTypeInput = document.getElementById('postType');
    const linkInput = document.getElementById('postLink');

    const title = titleInput?.value.trim() || '';
    const content = contentInput?.value.trim() || '';
    const postType = postTypeInput?.value || 'discussion';
    const linkUrl = linkInput?.value.trim() || '';

    if (!content) {
        showNotification('Post content is required.', 'error');
        return;
    }

    try {
        const response = await fetch(urlRoot + '/userdashboard/postToCommunity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body:
                `community_id=${encodeURIComponent(communityId)}` +
                `&title=${encodeURIComponent(title)}` +
                `&content=${encodeURIComponent(content)}` +
                `&post_type=${encodeURIComponent(postType)}` +
                `&link_url=${encodeURIComponent(linkUrl)}`
        });

        const result = await response.json();

        if (result.success) {
            showNotification(result.message || 'Post created successfully!', 'success');
            titleInput.value = '';
            contentInput.value = '';
            postTypeInput.value = 'discussion';
            linkInput.value = '';
            setTimeout(() => location.reload(), 700);
        } else {
            showNotification(result.message || 'Failed to create post.', 'error');
        }
    } catch (error) {
        console.error('Create post error:', error);
        showNotification('Network error. Please try again.', 'error');
    }
}

window.createCommunityPost = createCommunityPost;

function toggleComments(postId) {
    const el = document.getElementById('comments-' + postId);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

async function handleComment(event, communityId, postId) {
    if (event.key !== 'Enter') return;

    event.preventDefault();

    const input = event.target;
    const content = input.value.trim();

    if (!content) {
        return;
    }

    try {
        const response = await fetch(urlRoot + '/userdashboard/addCommunityComment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body:
                `community_id=${encodeURIComponent(communityId)}` +
                `&parent_id=${encodeURIComponent(postId)}` +
                `&content=${encodeURIComponent(content)}`
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Comment added!', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification(result.message || 'Failed to add comment', 'error');
        }
    } catch (error) {
        console.error('Comment error:', error);
        showNotification('Network error. Please try again.', 'error');
    }
}

function likePost(postId) {
    fetch(urlRoot + '/userdashboard/reactToPost', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `post_id=${postId}&reaction_type=like`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload(); // simple for now
        } else {
            showNotification(data.message || 'Failed to react', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showNotification('Error reacting to post', 'error');
    });
}


// ============================================
// UI HELPERS
// ============================================

/**
 * Show notification toast
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
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
        animation: slideIn 0.3s ease-out;
        max-width: 300px;
        font-weight: 500;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

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
// STYLES
// ============================================

// Add notification animations
if (!document.getElementById('notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// ============================================
// GLOBAL EXPORTS
// ============================================

window.joinCommunity = joinCommunity;
window.leaveCommunity = leaveCommunity;
window.viewCommunity = viewCommunity;
window.createCommunityPost = createCommunityPost;