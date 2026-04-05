// chats.js - Complete user-to-user chat functionality with transactions

let messagePollingInterval = null;
let lastMessageId = 0;

/**
 * Open chat window with a specific partner
 */
function openChatWindow(partnerId) {
    // Use the same route as the rest of the app for consistency
    window.location.href = `${URLROOT}/userdashboard/chats?partnerId=${partnerId}`;
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

                // Update transaction banner if status changed
                if (data.active_transaction) {
                    updateTransactionBanner(data.active_transaction);
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
            showNotification('Failed to send message: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        showNotification('Network error. Please try again.', 'error');
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

                // Update transaction if it changed
                if (data.active_transaction) {
                    updateTransactionBanner(data.active_transaction);
                }
            }
        })
        .catch(error => {
            console.error('Error polling messages:', error);
        });
}

// ============================================
// TRANSACTION FUNCTIONS
// ============================================

/**
 * Open transaction offer modal
 */
function openTransactionModal() {
    const modal = document.getElementById('transactionModal');
    modal.style.display = 'flex';
    
    // Reset form
    document.getElementById('transactionForm').reset();
    document.getElementById('buckxAmountGroup').style.display = 'none';
    document.getElementById('skillxGroup').style.display = 'none';
}

/**
 * Close transaction offer modal
 */
function closeTransactionModal() {
    const modal = document.getElementById('transactionModal');
    modal.style.display = 'none';
}

/**
 * Handle payment type change in modal
 */
document.addEventListener('DOMContentLoaded', function() {
    const paymentType = document.getElementById('paymentType');
    if (paymentType) {
        paymentType.addEventListener('change', function() {
            const buckxGroup = document.getElementById('buckxAmountGroup');
            const skillxGroup = document.getElementById('skillxGroup');
            
            if (this.value === 'buckx') {
                buckxGroup.style.display = 'block';
                skillxGroup.style.display = 'none';
                document.getElementById('buckxAmount').required = true;
                document.getElementById('skillName').required = false;
                document.getElementById('skillDebtHours').required = false;
            } else if (this.value === 'skillx') {
                buckxGroup.style.display = 'none';
                skillxGroup.style.display = 'block';
                document.getElementById('buckxAmount').required = false;
                document.getElementById('skillName').required = true;
                document.getElementById('skillDebtHours').required = true;
            } else {
                buckxGroup.style.display = 'none';
                skillxGroup.style.display = 'none';
            }
        });
    }

    // Handle transaction form submission
    const transactionForm = document.getElementById('transactionForm');
    if (transactionForm) {
        transactionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            createTransactionOffer();
        });
    }

    // Handle report form submission
    const reportForm = document.getElementById('reportForm');
    if (reportForm) {
        reportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitReport();
        });
    }
});

/**
 * Create transaction offer
 */
function createTransactionOffer() {
    const formData = new FormData(document.getElementById('transactionForm'));
    formData.append('chat_id', CURRENT_CHAT_ID);

    fetch(`${URLROOT}/transaction/createOffer`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Offer sent successfully!', 'success');
            closeTransactionModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Failed to create offer', 'error');
        }
    })
    .catch(error => {
        console.error('Error creating offer:', error);
        showNotification('Network error. Please try again.', 'error');
    });
}

/**
 * Respond to transaction offer (accept/reject)
 */
function respondToOffer(eventId, action) {
    const confirmMsg = action === 'accept' 
        ? 'Accept this transaction offer?' 
        : 'Reject this offer?';
    
    if (!confirm(confirmMsg)) return;

    const formData = new FormData();
    formData.append('event_id', eventId);
    formData.append('action', action);

    fetch(`${URLROOT}/transaction/respondToOffer`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Action failed', 'error');
        }
    })
    .catch(error => {
        console.error('Error responding to offer:', error);
        showNotification('Network error. Please try again.', 'error');
    });
}

/**
 * Leave lesson (terminate transaction)
 */
function leaveLesson(eventId) {
    if (!confirm('Are you sure you want to leave this lesson? This will terminate the transaction and return any frozen funds.')) {
        return;
    }

    const formData = new FormData();
    formData.append('event_id', eventId);

    fetch(`${URLROOT}/transaction/leaveLesson`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Failed to leave lesson', 'error');
        }
    })
    .catch(error => {
        console.error('Error leaving lesson:', error);
        showNotification('Network error. Please try again.', 'error');
    });
}

/**
 * Teacher marks session as completed
 */
function markCompleted(eventId) {
    if (!confirm('Mark this session as completed? The learner will then verify and payment will be processed.')) {
        return;
    }

    const formData = new FormData();
    formData.append('event_id', eventId);

    fetch(`${URLROOT}/transaction/markCompleted`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Failed to mark completed', 'error');
        }
    })
    .catch(error => {
        console.error('Error marking completed:', error);
        showNotification('Network error. Please try again.', 'error');
    });
}

/**
 * Learner verifies completion (agree)
 */
function verifyCompletion(eventId, action) {
    if (action === 'agreed') {
        if (!confirm('Confirm that the session was completed successfully? Payment will be transferred.')) {
            return;
        }

        const formData = new FormData();
        formData.append('event_id', eventId);
        formData.append('action', 'agreed');

        fetch(`${URLROOT}/transaction/verifyCompletion`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.message || 'Failed to verify completion', 'error');
            }
        })
        .catch(error => {
            console.error('Error verifying completion:', error);
            showNotification('Network error. Please try again.', 'error');
        });
    }
}

/**
 * Open report issue modal
 */
function openReportModal(eventId) {
    document.getElementById('reportEventId').value = eventId;
    document.getElementById('reportModal').style.display = 'flex';
}

/**
 * Close report modal
 */
function closeReportModal() {
    document.getElementById('reportModal').style.display = 'none';
    document.getElementById('reportForm').reset();
}

/**
 * Submit report/dispute
 */
function submitReport() {
    const eventId = document.getElementById('reportEventId').value;
    const disputeReason = document.getElementById('disputeReason').value.trim();

    if (!disputeReason) {
        showNotification('Please describe the issue', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('event_id', eventId);
    formData.append('action', 'report');
    formData.append('dispute_reason', disputeReason);

    fetch(`${URLROOT}/transaction/verifyCompletion`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeReportModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Failed to submit report', 'error');
        }
    })
    .catch(error => {
        console.error('Error submitting report:', error);
        showNotification('Network error. Please try again.', 'error');
    });
}

/**
 * Update transaction banner with new status
 */
function updateTransactionBanner(transaction) {
    const banner = document.getElementById('transactionBanner');
    if (!banner) return;

    // Store transaction data
    banner.dataset.transaction = JSON.stringify(transaction);

    // Check if status changed - if so, reload page for full UI update
    const currentStatus = banner.querySelector('[data-status]');
    if (currentStatus && currentStatus.dataset.status !== transaction.status) {
        location.reload();
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

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
 * Show notification toast
 */
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelectorAll('.notification-banner');
    existing.forEach(n => n.remove());
    
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

// ============================================
// INITIALIZATION
// ============================================

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

    // Close modals on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
});