/**
 * Handles wallet operations with better UX and error handling
 */
// Real-time balance validation
document.getElementById('amount').addEventListener('input', function() {
    const amount = parseFloat(this.value) || 0;
    const currentBalance = parseFloat(document.getElementById('currentBalance').textContent.replace(',', ''));
    
    if (amount > currentBalance) {
        this.setCustomValidity('Amount exceeds your current balance');
        this.classList.add('invalid');
    } else if (amount > 10000) {
        this.setCustomValidity('Maximum 10,000 BuckX per transaction');
        this.classList.add('invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('invalid');
    }
});

// Form validation before submission
document.getElementById('transferForm')?.addEventListener('submit', function(e) {
    const recipientId = document.getElementById('recipient_id').value;
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const currentBalance = parseFloat(document.getElementById('currentBalance').textContent.replace(',', ''));
    
    if (!recipientId) {
        e.preventDefault();
        alert('❌ Please select a recipient');
        return false;
    }
    
    if (amount > currentBalance) {
        e.preventDefault();
        alert('❌ Insufficient balance! You cannot send more than your current balance.');
        return false;
    }
    
    if (amount <= 0) {
        e.preventDefault();
        alert('❌ Please enter a valid amount greater than 0');
        return false;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    

    // Initialize
    initializeWallet();
    
    function initializeWallet() {
        // Check for notifications periodically
        checkNotifications();
        setInterval(checkNotifications, 30000); // Every 30 seconds
        
        // Update balance periodically
        setInterval(updateBalance, 60000); // Every minute
    }
    

    // Check for new notifications
    function checkNotifications() {
        fetch(getBaseUrl() + '/wallet/getNotifications')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.notifications.length > 0) {
                    const unread = data.notifications.filter(n => !n.is_read);
                    if (unread.length > 0) {
                        showNotificationBadge(unread.length);
                    }
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }
    

    // Update balance display
    function updateBalance() {
        fetch(getBaseUrl() + '/wallet/getCurrentBalance')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const balanceElement = document.getElementById('currentBalance');
                    if (balanceElement) {
                        balanceElement.textContent = data.balance;
                        
                        // Update header balance if exists
                        const headerBalance = document.querySelector('.header-balance');
                        if (headerBalance) {
                            headerBalance.textContent = data.balance + ' BuckX';
                        }
                    }
                }
            })
            .catch(error => console.error('Error updating balance:', error));
    }
    

    // Show notification badge
    function showNotificationBadge(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = 'block';
        }
    }
    

    // Get base URL helper
    function getBaseUrl() {
        return window.location.origin;
    }
    

    // Format number with commas
    function formatNumber(num) {
        return parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
    

    // Show toast notification
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${getToastIcon(type)}</span>
            <span class="toast-message">${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Remove after 5 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
    
    function getToastIcon(type) {
        const icons = {
            success: '\u2705',
            error: '\u274C',
            warning: '\u26A0\uFE0F',
            info: '\u2139\uFE0F'
        };
        return icons[type] || icons.info;
    }
});


// Toast Styles (injected dynamically)
const toastStyles = document.createElement('style');
toastStyles.textContent = `
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 10000;
        transform: translateX(400px);
        opacity: 0;
        transition: all 0.3s ease;
        max-width: 400px;
        border-left: 4px solid #3b82f6;
    }
    
    .toast.show {
        transform: translateX(0);
        opacity: 1;
    }
    
    .toast-success {
        border-left-color: #22c55e;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    }
    
    .toast-error {
        border-left-color: #dc2626;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    }
    
    .toast-warning {
        border-left-color: #f59e0b;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }
    
    .toast-icon {
        font-size: 1.5rem;
    }
    
    .toast-message {
        font-size: 0.95rem;
        font-weight: 500;
        color: #1f2937;
    }
`;
document.head.appendChild(toastStyles);