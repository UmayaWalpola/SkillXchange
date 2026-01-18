// chats.js - Complete user-to-user chat functionality

let messagePollingInterval = null;
let lastMessageId = 0;

/**
 * Open chat window with a specific partner
 */
function openChatWindow(partnerId) {
    window.location.href = `${URLROOT}/chat/user/${partnerId}`;
}

/**
 * Load messages for current chat
 */
function loadMessages() {
    if (!CURRENT_CHAT_ID) return;

    fetch(`${URLROOT}/chat/fetchUserMessages?chat_id=${CURRENT_CHAT_ID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMessages(data.messages, data.current_user_id);
                
                // Update last message ID for polling
                if (data.messages.length > 0) {
                    lastMessageId = data.messages[data.messages.length - 1].id;
                }
            } else {
                console.error('Failed to load messages:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading messages:', error);
        });
}

/**
 * Display messages in the chat window
 */
function displayMessages(messages, currentUserId) {
    const container = document.getElementById('messagesContainer');
    
    if (!messages || messages.length === 0) {
        container.innerHTML = '<div class="no-messages">No messages yet. Start the conversation!</div>';
        return;
    }

    let html = '';
    messages.forEach(msg => {
        const isOwn = msg.sender_id == currentUserId;
        const avatarText = msg.sender_profile_pic || msg.sender_name.substring(0, 2).toUpperCase();
        const messageClass = isOwn ? 'message own-message' : 'message';
        
        html += `
            <div class="${messageClass}" data-message-id="${msg.id}">
                <div class="message-avatar">
                    ${avatarText.includes('uploads/') 
                        ? `<img src="${URLROOT}/${avatarText}" alt="Avatar">` 
                        : avatarText
                    }
                </div>
                <div class="message-content">
                    ${!isOwn ? `<div class="message-sender">${msg.sender_name}</div>` : ''}
                    <div class="message-text">${escapeHtml(msg.message)}</div>
                    <div class="message-time">${formatMessageTime(msg.created_at)}</div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    scrollToBottom();
}

/**
 * Send a new message
 */
function sendMessage(event) {
    event.preventDefault();

    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    const chatId = document.getElementById('chatId').value;

    if (!message || !chatId) return;

    const formData = new FormData();
    formData.append('chat_id', chatId);
    formData.append('message', message);

    fetch(`${URLROOT}/chat/sendUserMessage`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadMessages(); // Reload to show new message
        } else {
            alert('Failed to send message: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Network error. Please try again.');
    });
}

/**
 * Poll for new messages
 */
function pollForNewMessages() {
    if (!CURRENT_CHAT_ID) return;

    fetch(`${URLROOT}/chat/fetchUserMessages?chat_id=${CURRENT_CHAT_ID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages.length > 0) {
                const latestId = data.messages[data.messages.length - 1].id;
                
                // Only update if there are new messages
                if (latestId > lastMessageId) {
                    displayMessages(data.messages, data.current_user_id);
                    lastMessageId = latestId;
                }
            }
        })
        .catch(error => {
            console.error('Error polling messages:', error);
        });
}

/**
 * View partner's profile
 */
function viewPartnerProfile(partnerId) {
    window.location.href = `${URLROOT}/userdashboard/viewProfile/${partnerId}`;
}

/**
 * Search chats
 */
function searchChats() {
    const searchInput = document.getElementById('searchChats');
    const query = searchInput.value.toLowerCase();
    const chatItems = document.querySelectorAll('.chat-item');

    chatItems.forEach(item => {
        const name = item.querySelector('.chat-name').textContent.toLowerCase();
        const preview = item.querySelector('.chat-preview').textContent.toLowerCase();
        
        if (name.includes(query) || preview.includes(query)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

/**
 * Scroll to bottom of messages
 */
function scrollToBottom() {
    const container = document.getElementById('messagesContainer');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

/**
 * Format message timestamp
 */
function formatMessageTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;

    // Less than 1 minute
    if (diff < 60000) {
        return 'Just now';
    }
    
    // Less than 1 hour
    if (diff < 3600000) {
        const mins = Math.floor(diff / 60000);
        return `${mins}m ago`;
    }
    
    // Today
    if (date.toDateString() === now.toDateString()) {
        return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    }
    
    // Yesterday
    const yesterday = new Date(now);
    yesterday.setDate(yesterday.getDate() - 1);
    if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    }
    
    // Older
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
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
 * Show notification
 */
function showNotification(message, type = 'info') {
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
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('Chats page loaded');
    console.log('Current chat ID:', CURRENT_CHAT_ID);
    
    // Load messages if we're in a chat
    if (CURRENT_CHAT_ID) {
        loadMessages();
        
        // Poll for new messages every 3 seconds
        messagePollingInterval = setInterval(pollForNewMessages, 3000);
    }
    
    // Search functionality
    const searchInput = document.getElementById('searchChats');
    if (searchInput) {
        searchInput.addEventListener('input', searchChats);
    }
    
    // Focus message input
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.focus();
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
});