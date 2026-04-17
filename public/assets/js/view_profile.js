/* View Profile Page JavaScript */

console.log('View Profile JavaScript loaded!');

// Send connection request
function sendConnectionRequest(userId, userName, clickEvent) {
    console.log('Sending connection request to:', userName);

    if (!userId) {
        showToast('Unable to send request: invalid user.');
        return;
    }

    if (!confirm('Send connection request to ' + userName + '?')) {
        return;
    }

    const button = clickEvent?.currentTarget;
    if (button) {
        button.disabled = true;
        button.textContent = 'Sending...';
    }

    const formData = new FormData();
    formData.append('user_id', userId);

    fetch(`${window.URLROOT}/userdashboard/connect`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (button) {
                button.textContent = 'Request Sent';
                button.style.opacity = '0.6';
            }
            showToast(data.message || ('Connection request sent to ' + userName + '!'));
        } else {
            if (button) {
                button.disabled = false;
                button.textContent = 'Connect';
            }
            showToast(data.message || 'Failed to send connection request.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (button) {
            button.disabled = false;
            button.textContent = 'Connect';
        }
        showToast('An error occurred. Please try again.');
    });
}

// Send message - redirect to chats
function sendMessage(userId) {
    console.log('Redirecting to chat...');

    if (!userId) {
        showToast('Unable to open chat: invalid user.');
        return;
    }

    const context = window.MATCH_CONTEXT || {};
    const params = new URLSearchParams();

    if (context.type) params.set('match_type', context.type);
    if (context.skill) params.set('skill', context.skill);
    if (context.dir) params.set('dir', context.dir);

    let url = `${window.URLROOT}/chat/user/${userId}`;
    const query = params.toString();
    if (query) {
        url += `?${query}`;
    }

    window.location.href = url;
}

// Show toast notification
function showToast(message) {
    // Remove any existing toasts
    const existingToast = document.querySelector('.profile-toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    toast.className = 'profile-toast';
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: var(--primary-blue);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 9999;
        animation: slideUp 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideDown 0.3s ease';
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Make functions globally accessible
window.sendConnectionRequest = sendConnectionRequest;
window.sendMessage = sendMessage;
