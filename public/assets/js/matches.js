/* Matches Page JavaScript - Fixed with proper URL */

console.log('Matches JavaScript loaded!');

// View user profile - MUST be window.viewProfile to work with onclick
window.viewProfile = function(userId) {
    console.log('=== viewProfile called ===');
    console.log('Viewing profile for user ID:', userId);
    
    // Get the base URL dynamically
    const baseUrl = window.location.origin + window.location.pathname.split('/public/')[0] + '/public';
    
    // Redirect to user profile page
    window.location.href = baseUrl + '/userdashboard/viewProfile/' + userId;
}

// Connect with a user - MUST be window.connectWithUser
window.connectWithUser = function(userId, userName) {
    console.log('=== connectWithUser called ===');
    console.log('Connecting with user:', userId, userName);
    
    // Show confirmation
    if (!confirm('Send connection request to ' + userName + '?')) {
        return;
    }
    
    // Find the button that was clicked
    const buttons = document.querySelectorAll('.btn-connect');
    let clickedButton = null;
    
    buttons.forEach(btn => {
        const card = btn.closest('.match-card');
        if (card) {
            const cardName = card.querySelector('.match-name').textContent;
            if (cardName === userName) {
                clickedButton = btn;
            }
        }
    });
    
    if (!clickedButton) {
        console.error('Could not find button for user:', userName);
        return;
    }
    
    // Disable button and show loading
    clickedButton.disabled = true;
    clickedButton.textContent = 'Sending...';
    
    // Simulate API call
    setTimeout(() => {
        clickedButton.textContent = 'Requested';
        clickedButton.style.opacity = '0.6';
        showToast('Connection request sent to ' + userName + '!');
    }, 1000);
}

// Show toast notification
function showToast(message) {
    console.log('Showing toast:', message);
    
    // Remove any existing toasts
    const existingToast = document.querySelector('.match-toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    toast.className = 'match-toast';
    toast.textContent = message;
    
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

console.log('✓ Matches JavaScript functions registered globally');