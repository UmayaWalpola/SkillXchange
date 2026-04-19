// Manager User Management - Form Validation

// Email validation regex pattern
const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

// Validate email format
function validateEmailFormat(email) {
    return EMAIL_REGEX.test(email);
}

// Show validation error for email
function showEmailError(inputId, message) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    input.classList.add('input-error');
    let errorElement = input.parentElement.querySelector('.error-text');
    
    if (!errorElement) {
        errorElement = document.createElement('small');
        errorElement.className = 'error-text';
        input.parentElement.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
    errorElement.style.display = 'block';
}

// Clear validation error for email
function clearEmailError(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    input.classList.remove('input-error');
    const errorElement = input.parentElement.querySelector('.error-text');
    
    if (errorElement) {
        errorElement.style.display = 'none';
    }
}

// Real-time email validation on input
function setupEmailValidation(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    input.addEventListener('blur', function() {
        const email = this.value.trim();
        
        if (!email) {
            clearEmailError(inputId);
            return;
        }
        
        if (!validateEmailFormat(email)) {
            showEmailError(inputId, 'Please enter a valid email address (e.g., user@example.com)');
        } else {
            clearEmailError(inputId);
        }
    });
    
    // Clear error on typing
    input.addEventListener('focus', function() {
        clearEmailError(inputId);
    });
}

// Validate Add User form
function validateAddUserForm() {
    const name = document.getElementById('add-name').value.trim();
    const email = document.getElementById('add-email').value.trim();
    const role = document.getElementById('add-role').value.trim();
    const password = document.getElementById('add-password').value.trim();
    
    // Check required fields
    if (!name) {
        alert('Please enter a full name');
        return false;
    }
    
    if (!email) {
        alert('Please enter an email address');
        return false;
    }
    
    if (!role) {
        alert('Please select a role');
        return false;
    }
    
    if (!password) {
        alert('Please enter a password');
        return false;
    }
    
    // Validate email format
    if (!validateEmailFormat(email)) {
        showEmailError('add-email', 'Please enter a valid email address');
        alert('Please enter a valid email address (e.g., user@example.com)');
        return false;
    }
    
    // Validate password strength (minimum 6 characters)
    if (password.length < 6) {
        alert('Password must be at least 6 characters long');
        return false;
    }
    
    return true;
}

// Validate Edit User form
function validateEditUserForm(userId) {
    const name = document.getElementById(`edit-name-${userId}`).value.trim();
    const email = document.getElementById(`edit-email-${userId}`).value.trim();
    const role = document.getElementById(`edit-role-${userId}`).value.trim();
    const password = document.getElementById(`edit-password-${userId}`).value.trim();
    
    // Check required fields
    if (!name) {
        alert('Please enter a full name');
        return false;
    }
    
    if (!email) {
        alert('Please enter an email address');
        return false;
    }
    
    if (!role) {
        alert('Please select a role');
        return false;
    }
    
    // Validate email format
    if (!validateEmailFormat(email)) {
        showEmailError(`edit-email-${userId}`, 'Please enter a valid email address');
        alert('Please enter a valid email address (e.g., user@example.com)');
        return false;
    }
    
    // Validate password strength if provided
    if (password && password.length < 6) {
        alert('Password must be at least 6 characters long');
        return false;
    }
    
    return true;
}

// Toggle Add User form visibility
function toggleAddForm() {
    const form = document.getElementById('addUserForm');
    if (form) {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
}

// Toggle Edit User form visibility
function toggleEditForm(userId) {
    const form = document.getElementById(`edit-${userId}`);
    if (form) {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
}

// Initialize event listeners when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Setup real-time validation for Add form email
    setupEmailValidation('add-email');
    
    // Setup real-time validation for all Edit form emails
    const editForms = document.querySelectorAll('[id^="edit-email-"]');
    editForms.forEach(input => {
        setupEmailValidation(input.id);
    });
    
    // Attach form submission validation
    const addUserForm = document.querySelector('#addUserForm form');
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(e) {
            if (!validateAddUserForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Attach validation to all edit forms
    const editForms2 = document.querySelectorAll('#addUserForm + * form, .inline-form-panel form');
    editForms2.forEach((form, index) => {
        // Only for edit forms (those with user_id input)
        const userIdInput = form.querySelector('input[name="user_id"]');
        if (userIdInput) {
            form.addEventListener('submit', function(e) {
                if (!validateEditUserForm(userIdInput.value)) {
                    e.preventDefault();
                }
            });
        }
    });
});
