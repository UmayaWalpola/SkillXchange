// Filter notifications
function filterNotifications(filter, button) {
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');

    // Filter items
    const items = document.querySelectorAll('.notification-item');
    items.forEach(item => {
        const status = item.getAttribute('data-status');
        
        if (filter === 'all') {
            item.style.display = 'flex';
        } else if (filter === status) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// Mark all as read
function markAllAsRead() {
    const items = document.querySelectorAll('.notification-item');
    
    items.forEach(item => {
        item.classList.remove('unread');
        item.classList.add('read');
        item.setAttribute('data-status', 'read');
        
        // Remove unread dot
        const unreadDot = item.querySelector('.unread-dot');
        if (unreadDot) {
            unreadDot.remove();
        }
    });

    // Show success message
    showNotification('All notifications marked as read');
}

// Delete notification
function deleteNotification(button) {
    const item = button.closest('.notification-item');
    
    // Fade out animation
    item.style.transition = 'all 0.3s ease';
    item.style.opacity = '0';
    item.style.transform = 'translateX(100px)';
    
    // Remove after animation
    setTimeout(() => {
        item.remove();
        
        // Check if no notifications left
        const remainingItems = document.querySelectorAll('.notification-item');
        if (remainingItems.length === 0) {
            showEmptyState();
        }
    }, 300);
}

// Show empty state
function showEmptyState() {
    const list = document.querySelector('.notifications-list');
    list.innerHTML = `
        <div class="no-notifications">
            <div class="empty-icon">🔔</div>
            <h2>No notifications yet</h2>
            <p>When you get notifications, they'll show up here</p>
        </div>
    `;
}

// Show notification toast
function showNotification(message) {
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--primary-blue);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
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