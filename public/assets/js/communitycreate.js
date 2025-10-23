// Community Create JavaScript - Backend Integrated

let rules = [];
let tags = [];
const MAX_RULES = 10;
const MAX_TAGS = 10;

// Initialize
function init() {
  setupEventListeners();
  updateRuleCount();
}

// Setup event listeners
function setupEventListeners() {
  // Character counter for description
  const descTextarea = document.getElementById('communityDescription');
  descTextarea.addEventListener('input', function() {
    document.getElementById('descCharCount').textContent = this.value.length;
  });
}

// Add rule
function addRule() {
  if(rules.length >= MAX_RULES) {
    showNotification('Maximum 10 rules allowed', 'error');
    return;
  }
  
  document.getElementById('ruleModalTitle').textContent = 'Add Rule';
  document.getElementById('editingRuleIndex').value = '-1';
  document.getElementById('ruleForm').reset();
  document.getElementById('ruleModal').style.display = 'block';
}

// Edit rule
function editRule(index) {
  const rule = rules[index];
  document.getElementById('ruleModalTitle').textContent = 'Edit Rule';
  document.getElementById('editingRuleIndex').value = index;
  document.getElementById('ruleTitle').value = rule.title;
  document.getElementById('ruleDescription').value = rule.description;
  document.getElementById('ruleModal').style.display = 'block';
}

// Save rule
function saveRule(event) {
  event.preventDefault();
  
  const title = document.getElementById('ruleTitle').value.trim();
  const description = document.getElementById('ruleDescription').value.trim();
  const editingIndex = parseInt(document.getElementById('editingRuleIndex').value);
  
  if(!title || !description) {
    showNotification('Please fill in all rule fields', 'error');
    return;
  }
  
  const rule = { title, description };
  
  if(editingIndex === -1) {
    rules.push(rule);
  } else {
    rules[editingIndex] = rule;
  }
  
  renderRules();
  closeRuleModal();
  showNotification('Rule saved successfully', 'success');
}

// Delete rule
function deleteRule(index) {
  if(confirm('Are you sure you want to delete this rule?')) {
    rules.splice(index, 1);
    renderRules();
    showNotification('Rule deleted successfully', 'success');
  }
}

// Render rules
function renderRules() {
  const container = document.getElementById('rulesContainer');
  const noRulesMsg = document.getElementById('noRulesMessage');
  
  if(rules.length === 0) {
    container.innerHTML = '';
    noRulesMsg.style.display = 'block';
  } else {
    noRulesMsg.style.display = 'none';
    container.innerHTML = rules.map((rule, index) => `
      <div class="rule-item">
        <div class="rule-header">
          <div class="rule-number">${index + 1}</div>
          <div class="rule-content">
            <div class="rule-title">${escapeHtml(rule.title)}</div>
            <div class="rule-description">${escapeHtml(rule.description)}</div>
          </div>
          <div class="rule-actions">
            <button class="btn-icon" onclick="editRule(${index})" title="Edit">✏️</button>
            <button class="btn-icon" onclick="deleteRule(${index})" title="Delete">🗑️</button>
          </div>
        </div>
      </div>
    `).join('');
  }
  
  updateRuleCount();
  
  // Enable/disable add button based on rule count
  document.getElementById('addRuleBtn').disabled = rules.length >= MAX_RULES;
}

// Update rule count
function updateRuleCount() {
  document.getElementById('ruleCount').textContent = rules.length;
}

// Close rule modal
function closeRuleModal() {
  document.getElementById('ruleModal').style.display = 'none';
  document.getElementById('ruleForm').reset();
}

// Handle tag input
function handleTagInput(event) {
  if(event.key === 'Enter') {
    event.preventDefault();
    addTag();
  }
}

// Add tag
function addTag() {
  const input = document.getElementById('tagInput');
  const tagValue = input.value.trim().toLowerCase();
  
  if(!tagValue) return;
  
  if(tags.length >= MAX_TAGS) {
    showNotification('Maximum 10 tags allowed', 'error');
    return;
  }
  
  if(tags.includes(tagValue)) {
    showNotification('Tag already added', 'error');
    return;
  }
  
  tags.push(tagValue);
  input.value = '';
  renderTags();
}

// Remove tag
function removeTag(index) {
  tags.splice(index, 1);
  renderTags();
}

// Render tags
function renderTags() {
  const container = document.getElementById('tagsContainer');
  
  if(tags.length === 0) {
    container.innerHTML = '<div style="color: #999; font-size: 14px;">No tags added yet</div>';
  } else {
    container.innerHTML = tags.map((tag, index) => `
      <span class="tag-chip">
        ${escapeHtml(tag)}
        <button class="tag-remove" onclick="removeTag(${index})">×</button>
      </span>
    `).join('');
  }
}

// Validate form
function validateForm() {
  let isValid = true;
  let errors = {};
  
  // Clear previous errors
  document.querySelectorAll('.error-text').forEach(el => el.textContent = '');
  document.querySelectorAll('.form-group').forEach(el => el.classList.remove('has-error'));
  
  const name = document.getElementById('communityName').value.trim();
  const category = document.getElementById('communityCategory').value;
  const description = document.getElementById('communityDescription').value.trim();
  
  if(!name) {
    errors.name = 'Community name is required';
    isValid = false;
  } else if(name.length > 100) {
    errors.name = 'Community name cannot exceed 100 characters';
    isValid = false;
  }
  
  if(!category) {
    errors.category = 'Category is required';
    isValid = false;
  }
  
  if(!description) {
    errors.description = 'Description is required';
    isValid = false;
  } else if(description.length > 1000) {
    errors.description = 'Description cannot exceed 1000 characters';
    isValid = false;
  }
  
  // Display errors
  if(errors.name) {
    document.getElementById('nameError').textContent = errors.name;
    document.getElementById('communityName').closest('.form-group').classList.add('has-error');
  }
  if(errors.category) {
    document.getElementById('categoryError').textContent = errors.category;
    document.getElementById('communityCategory').closest('.form-group').classList.add('has-error');
  }
  if(errors.description) {
    document.getElementById('descriptionError').textContent = errors.description;
    document.getElementById('communityDescription').closest('.form-group').classList.add('has-error');
  }
  
  return isValid;
}

// Get form data
function getFormData() {
  return {
    name: document.getElementById('communityName').value.trim(),
    category: document.getElementById('communityCategory').value,
    description: document.getElementById('communityDescription').value.trim(),
    privacy: document.getElementById('communityPrivacy').value,
    rules: rules,
    tags: tags,
    status: 'active'
  };
}

// Save draft
async function saveDraft() {
  if(!validateForm()) {
    showNotification('Please fix the errors before saving', 'error');
    return;
  }
  
  const formData = getFormData();
  formData.status = 'draft';
  
  await saveCommunity(formData, 'Draft saved successfully!');
}

// Preview community
function previewCommunity() {
  if(!validateForm()) {
    showNotification('Please fix the errors before previewing', 'error');
    return;
  }
  
  const formData = getFormData();
  
  // Show preview in modal or new tab
  showNotification('Preview feature coming soon!', 'success');
}

// Publish community
async function publishCommunity() {
  if(!validateForm()) {
    showNotification('Please fix the errors before publishing', 'error');
    return;
  }
  
  const formData = getFormData();
  formData.status = 'active';
  
  await saveCommunity(formData, 'Community created successfully!');
}

// Save community to backend
async function saveCommunity(data, successMessage) {
  try {
    // Show loading
    const loadingNotification = showNotification('Saving community...', 'info');
    
    const response = await fetch(`${URLROOT}/community/store`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    });
    
    const result = await response.json();
    
    if(result.success) {
      showNotification(successMessage, 'success');
      
      // Redirect to dashboard after 1.5 seconds
      setTimeout(() => {
        window.location.href = result.redirect || `${URLROOT}/community`;
      }, 1500);
    } else {
      // Display errors
      if(result.errors && result.errors.length > 0) {
        result.errors.forEach(error => {
          showNotification(error, 'error');
        });
      } else {
        showNotification('Failed to save community', 'error');
      }
    }
  } catch(error) {
    console.error('Error saving community:', error);
    showNotification('Error saving community. Please try again.', 'error');
  }
}

// Notification system
function showNotification(message, type = 'success') {
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.textContent = message;
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    background: ${type === 'success' ? '#22c55e' : type === 'error' ? '#ef4444' : '#3b82f6'};
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
  
  return notification;
}

// Utility function
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Close modal when clicking outside
window.onclick = function(event) {
  const ruleModal = document.getElementById('ruleModal');
  if(event.target == ruleModal) {
    closeRuleModal();
  }
}

// Add CSS for animations and styles
const style = document.createElement('style');
style.textContent = `
  .has-error input,
  .has-error select,
  .has-error textarea {
    border-color: #ef4444;
  }
  
  .error-text {
    color: #ef4444;
    font-size: 12px;
    margin-top: 5px;
    display: block;
  }
  
  .rule-item {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
  }
  
  .rule-header {
    display: flex;
    gap: 15px;
    align-items: start;
  }
  
  .rule-number {
    background: #3b82f6;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
  }
  
  .rule-content {
    flex: 1;
  }
  
  .rule-title {
    font-weight: 600;
    margin-bottom: 5px;
    color: #1f2937;
  }
  
  .rule-description {
    color: #6b7280;
    font-size: 14px;
  }
  
  .rule-actions {
    display: flex;
    gap: 8px;
  }
  
  .btn-icon {
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 18px;
    padding: 5px;
    transition: transform 0.2s;
  }
  
  .btn-icon:hover {
    transform: scale(1.2);
  }
  
  .tag-chip {
    display: inline-flex;
    align-items: center;
    background: #eff6ff;
    color: #1e40af;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 14px;
    margin: 5px;
  }
  
  .tag-remove {
    background: transparent;
    border: none;
    color: #1e40af;
    cursor: pointer;
    font-size: 20px;
    margin-left: 8px;
    padding: 0;
    line-height: 1;
  }
  
  .tag-remove:hover {
    color: #1e3a8a;
  }
  
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

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', init);