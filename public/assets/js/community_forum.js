/* ============================================
   COMMUNITIES - DATABASE VERSION
   ============================================ */

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

/**
 * Send a message in community chat
 */
async function sendMessage(communityId) {
    const input = document.getElementById('messageInput');
    const content = input?.value.trim();
    
    if (!content) {
        return;
    }
    
    // Disable input while sending
    input.disabled = true;
    const sendBtn = document.querySelector('.send-btn');
    if (sendBtn) sendBtn.disabled = true;
    
    try {
        const response = await fetch(urlRoot + '/userdashboard/postToCommunity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `community_id=${communityId}&content=${encodeURIComponent(content)}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            input.value = '';
            loadMessages(communityId);
            showNotification('Message sent!', 'success');
        } else {
            showNotification(result.message || 'Failed to send message', 'error');
        }
    } catch (error) {
        console.error('Send message error:', error);
        showNotification('Failed to send message', 'error');
    } finally {
        input.disabled = false;
        if (sendBtn) sendBtn.disabled = false;
        input.focus();
    }
}

/**
 * Load messages for a community
 */
async function loadMessages(communityId) {
    try {
        const response = await fetch(urlRoot + '/userdashboard/getCommunityMessages?community_id=' + communityId);
        const result = await response.json();
        
        if (result.success) {
            renderMessages(result.posts);
            scrollToBottom();
        }
    } catch (error) {
        console.error('Load messages error:', error);
    }
}

/**
 * Render messages in the chat
 */
function renderMessages(posts) {
    const messagesList = document.getElementById('messagesList');
    if (!messagesList) return;
    
    messagesList.innerHTML = posts.map(post => {
        const isOwn = post.user_id == currentUser.id;
        const time = formatTime(post.created_at);
        
        return `
            <div class="msg ${isOwn ? 'own' : ''}">
                ${!isOwn ? `<div class="msg-author">${escapeHtml(post.author_name)}</div>` : ''}
                <div class="msg-text">${escapeHtml(post.content)}</div>
                <div class="msg-time">${time}</div>
            </div>
        `;
    }).join('');
}

/**
 * Format timestamp for display
 */
function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    // If today, show time
    if (diff < 86400000 && date.getDate() === now.getDate()) {
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }
    
    // If yesterday
    if (diff < 172800000 && date.getDate() === now.getDate() - 1) {
        return 'Yesterday ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }
    
    // Otherwise show date
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

<<<<<<< HEAD
                ${!isJoined ? '<div class="not-joined-msg">Join this community to create posts and participate in discussions</div>' : ''}
                
                <div class="posts-list" id="postsList">
                    ${posts.length === 0 ? '<div class="no-posts">No posts yet. Be the first to post!</div>' : 
                        posts.map(post => `
                            <div class="forum-post">
                                <div class="post-header">
                                    <div class="post-author-info">
                                        <div class="post-avatar">${post.author.charAt(0).toUpperCase()}</div>
                                        <div>
                                            <div class="post-author">${escapeHtml(post.author)}</div>
                                            <div class="post-time">${escapeHtml(post.time)}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="post-content">
                                    <h4 class="post-title">${escapeHtml(post.title)}</h4>
                                    <p class="post-text">${escapeHtml(post.content)}</p>
                                </div>
                                <div class="post-footer">
                                    <button class="post-action" onclick="likePost(${post.id})">
                                        👍 ${post.likes} Likes
                                    </button>
                                    <button class="post-action" onclick="viewComments(${post.id})">
                                        💬 ${(post.replies || []).length} Comments
                                    </button>
                                    ${post.authorId !== currentUser.id ? `<button class="btn-report" onclick="openReportModal('content', {contentType: 'post', contentId: ${post.id}})" title="Report this post">🚩</button>` : ''}
                                </div>
                                
                                <!-- Comments Section -->
                                <div id="comments-${post.id}" class="comments-section hide-element">
                                    <div class="comments-list">
                                        ${(post.replies || []).map(reply => `
                                            <div class="comment">
                                                <div class="comment-avatar">${reply.author.charAt(0).toUpperCase()}</div>
                                                <div class="comment-content">
                                                    <div class="comment-author">${escapeHtml(reply.author)}</div>
                                                    <div class="comment-text">${escapeHtml(reply.content)}</div>
                                                    <div class="comment-time">${escapeHtml(reply.time)}</div>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                    ${isJoined ? `
                                        <div class="comment-input-section">
                                            <input type="text" class="comment-input" id="comment-input-${post.id}" placeholder="Write a comment...">
                                            <button class="btn btn-primary btn-sm" onclick="addComment(${post.id})">Comment</button>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        `).join('')
                    }
                </div>
            </div>
=======
/**
 * Handle Enter key in message input
 */
function handleKeyPress(event, communityId) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage(communityId);
    }
}
>>>>>>> 4cc97da (All quiz and Community features)

/**
 * Scroll chat to bottom
 */
function scrollToBottom() {
    setTimeout(() => {
        const messagesList = document.getElementById('messagesList');
        if (messagesList) {
            messagesList.scrollTop = messagesList.scrollHeight;
        }
    }, 100);
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
// AUTO-REFRESH (for community detail page)
// ============================================

if (window.location.pathname.includes('viewCommunity')) {
    const communityId = window.location.pathname.split('/').pop();
    if (communityId && !isNaN(communityId)) {
        console.log('Auto-refresh enabled for community:', communityId);
        setInterval(() => {
            loadMessages(communityId);
        }, 10000); // Refresh every 10 seconds
    }
}

// ============================================
// GLOBAL EXPORTS
// ============================================

window.joinCommunity = joinCommunity;
window.leaveCommunity = leaveCommunity;
window.viewCommunity = viewCommunity;
window.sendMessage = sendMessage;
window.handleKeyPress = handleKeyPress;