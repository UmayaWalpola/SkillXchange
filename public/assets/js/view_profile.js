/* View Profile Page JavaScript */

console.log('View Profile JavaScript loaded!');

// Send connection request
function sendConnectionRequest(userName) {
    console.log('Sending connection request to:', userName);
    
    if (confirm('Send connection request to ' + userName + '?')) {
        // Get the button
        const button = event.target;
        
        // Disable button and show loading
        button.disabled = true;
        button.textContent = 'Sending...';
        
        // Simulate API call
        setTimeout(() => {
            button.textContent = 'Request Sent';
            button.style.opacity = '0.6';
            showToast('Connection request sent to ' + userName + '!');
        }, 1000);
        
        /* 
        // REAL IMPLEMENTATION - Uncomment when backend is ready
        fetch('/api/connections/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                receiver_name: userName
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.textContent = 'Request Sent';
                button.style.opacity = '0.6';
                showToast('Connection request sent to ' + userName + '!');
            } else {
                alert('Failed to send connection request: ' + data.message);
                button.disabled = false;
                button.textContent = 'Connect';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            button.disabled = false;
            button.textContent = 'Connect';
        });
        */
    }
}

// Send message - redirect to chats
function sendMessage() {
    console.log('Redirecting to chats...');
    
    // Redirect to chats page
    // You can pass user ID as query parameter if needed
    window.location.href = '/userdashboard/chats';
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