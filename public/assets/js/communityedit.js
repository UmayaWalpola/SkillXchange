// Community Edit Form JavaScript

// Add new rule input
function addRule() {
    const container = document.getElementById('rulesContainer');
    const ruleItem = document.createElement('div');
    ruleItem.className = 'rule-item';
    ruleItem.innerHTML = `
        <input type="text" class="form-input rule-input" placeholder="Enter a rule">
        <button type="button" class="btn-remove" onclick="removeRule(this)">×</button>
    `;
    container.appendChild(ruleItem);
}

// Remove rule input
function removeRule(button) {
    button.parentElement.remove();
}

// Handle form submission
document.getElementById('editCommunityForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
    document.getElementById('formErrors').style.display = 'none';
    
    // Get form data
    const name = document.getElementById('communityName').value.trim();
    const category = document.getElementById('category').value;
    const description = document.getElementById('description').value.trim();
    const privacy = document.querySelector('input[name="privacy"]:checked').value;
    const status = document.querySelector('input[name="status"]:checked').value;
    const tagsInput = document.getElementById('tags').value.trim();
    
    // Get rules
    const rules = [];
    document.querySelectorAll('.rule-input').forEach(input => {
        if(input.value.trim()) {
            rules.push(input.value.trim());
        }
    });
    
    // Process tags
    const tags = tagsInput ? tagsInput.split(',').map(tag => tag.trim()).filter(tag => tag) : [];
    
    // Validation
    let isValid = true;
    
    if(!name) {
        document.getElementById('nameError').textContent = 'Community name is required';
        isValid = false;
    }
    
    if(!category) {
        document.getElementById('categoryError').textContent = 'Category is required';
        isValid = false;
    }
    
    if(!description) {
        document.getElementById('descriptionError').textContent = 'Description is required';
        isValid = false;
    }
    
    if(!isValid) return;
    
    // Prepare data
    const communityData = {
        name,
        category,
        description,
        privacy,
        status,
        rules,
        tags
    };
    
    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Updating...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch(`${URLROOT}/community/update/${communityId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(communityData)
        });
        
        const result = await response.json();
        
        if(result.success) {
            // Show success message
            showNotification('Community updated successfully!', 'success');
            
            // Redirect to dashboard after 1 second
            setTimeout(() => {
                window.location.href = `${URLROOT}/community`;
            }, 1000);
        } else {
            // Show errors
            const errorDiv = document.getElementById('formErrors');
            errorDiv.innerHTML = result.errors.join('<br>');
            errorDiv.style.display = 'block';
            
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    } catch(error) {
        console.error('Error:', error);
        const errorDiv = document.getElementById('formErrors');
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
        
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
});

// Notification function
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#22c55e' : '#ef4444'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add animations
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
    
    .rule-item {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }
    
    .rule-input {
        flex: 1;
    }
    
    .btn-remove {
        background: #ef4444;
        color: white;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 20px;
        line-height: 1;
    }
    
    .btn-remove:hover {
        background: #dc2626;
    }
`;
document.head.appendChild(style);