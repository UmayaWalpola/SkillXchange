/**
 * Handles wallet operations with better UX and error handling
 */

// Real-time balance validation
document.getElementById('amount')?.addEventListener('input', function() {
    const amount = parseFloat(this.value) || 0;
    const currentBalanceText = document.getElementById('currentBalance')?.textContent || '0';
    const currentBalance = parseFloat(currentBalanceText.replace(/,/g, ''));
    
    if (amount > currentBalance) {
        this.setCustomValidity('Amount exceeds your current balance');
        this.classList.add('invalid');
    } else if (amount > 1000) {
        this.setCustomValidity('Maximum 1,000 BuckX per transaction');
        this.classList.add('invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('invalid');
    }
});

// Form validation before submission
document.getElementById('transferForm')?.addEventListener('submit', function(e) {
    const recipientId = document.getElementById('recipient_id')?.value;
    const amountInput = document.getElementById('amount')?.value;
    const amount = parseFloat(amountInput) || 0;
    const currentBalanceText = document.getElementById('currentBalance')?.textContent || '0';
    const currentBalance = parseFloat(currentBalanceText.replace(/,/g, ''));
    
    if (!recipientId) {
        e.preventDefault();
        alert('Please select a recipient');
        return false;
    }
    
    if (amount > currentBalance) {
        e.preventDefault();
        alert('Insufficient balance! You cannot send more than your current balance.');
        return false;
    }
    
    if (amount <= 0) {
        e.preventDefault();
        alert('Please enter a valid amount greater than 0');
        return false;
    }
});

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeWallet();
    
    function initializeWallet() {
        // Update balance every minute
        setInterval(updateBalance, 60000);
    }
    
    // Update balance display
    function updateBalance() {
        const baseUrl = getBaseUrl();
        
        fetch(baseUrl + '/wallet/getCurrentBalance')
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    const balanceElement = document.getElementById('currentBalance');
                    if (balanceElement) {
                        balanceElement.textContent = data.balance;
                    }
                    
                    // Update header balance if exists
                    const headerBalance = document.querySelector('.header-balance');
                    if (headerBalance) {
                        headerBalance.textContent = data.balance + ' BuckX';
                    }
                }
            })
            .catch(function(error) {
                console.error('Error updating balance:', error);
            });
    }
    
    // Get base URL helper
    function getBaseUrl() {
        const path = window.location.pathname;
        const pathParts = path.split('/');
        
        // Find 'public' in the path
        const publicIndex = pathParts.indexOf('public');
        
        if (publicIndex !== -1) {
            // Build URL up to 'public'
            const basePath = pathParts.slice(0, publicIndex + 1).join('/');
            return window.location.origin + basePath;
        }
        
        // Fallback
        return window.location.origin + '/SkillXchange/public';
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