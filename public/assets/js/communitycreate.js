// Community Create JavaScript

let rules = [];
let tags = [];
const MAX_RULES = 10;
const MAX_TAGS = 10;

// Initialize
function initCreate() {
  updateRuleCount();
  setupCharacterCount();
}

// Character count for description
function setupCharacterCount() {
  const descriptionField = document.getElementById('communityDescription');
  const charCount = document.getElementById('descCharCount');
  
  descriptionField.addEventListener('input', function() {
    charCount.textContent = this.value.length;
  });
}

// Rule Management
function addRule() {
  if (rules.length >= MAX_RULES) {
    showNotification('Maximum of 10 rules allowed', 'error');
    return;
  }
  
  // Reset form
  document.getElementById('ruleForm').reset();
  document.getElementById('editingRuleIndex').value = '-1';
  document.getElementById('ruleModalTitle').textContent = 'Add Rule';
  
  // Show modal
  document.getElementById('ruleModal').style.display = 'block';
}

function saveRule(event) {
  event.preventDefault();
  
  const title = document.getElementById('ruleTitle').value.trim();
  const description = document.getElementById('ruleDescription').value.trim();
  const editingIndex = parseInt(document.getElementById('editingRuleIndex').value);
  
  if (!title || !description) {
    showNotification('Please fill in all rule fields', 'error');
    return;
  }
  
  if (editingIndex === -1) {
    // Add new rule
    rules.push({ title, description });
    showNotification('Rule added successfully!', 'success');
  } else {
    // Update existing rule
    rules[editingIndex] = { title, description };
    showNotification('Rule updated successfully!', 'success');
  }
  
  renderRules();
  closeRuleModal();
  updateRuleCount();
}

function renderRules() {
  const container = document.getElementById('rulesContainer');
  const noRulesMsg = document.getElementById('noRulesMessage');
  
  if (rules.length === 0) {
    container.innerHTML = '';
    noRulesMsg.style.display = 'block';
    return;
  }
  
  noRulesMsg.style.display = 'none';
  container.innerHTML = '';
  
  rules.forEach((rule, index) => {
    const ruleDiv = document.createElement('div');
    ruleDiv.className = 'rule-item';
    ruleDiv.innerHTML = `
      <div class="rule-content">
        <div class="rule-number">Rule ${index + 1}</div>
        <div class="rule-title">${rule.title}</div>
        <div class="rule-description">${rule.description}</div>
      </div>
      <div class="rule-actions">
        <button class="btn-icon" onclick="editRule(${index})" title="Edit">✏️</button>
        <button class="btn-icon" onclick="deleteRule(${index})" title="Delete">🗑️</button>
      </div>
    `;
    container.appendChild(ruleDiv);
  });
}

function editRule(index) {
  const rule = rules[index];
  
  document.getElementById('ruleTitle').value = rule.title;
  document.getElementById('ruleDescription').value = rule.description;
  document.getElementById('editingRuleIndex').value = index;
  document.getElementById('ruleModalTitle').textContent = 'Edit Rule';
  
  document.getElementById('ruleModal').style.display = 'block';
}

function deleteRule(index) {
  if (confirm('Are you sure you want to delete this rule?')) {
    rules.splice(index, 1);
    renderRules();
    updateRuleCount();
    showNotification('Rule deleted successfully!', 'success');
  }
}

function updateRuleCount() {
  document.getElementById('ruleCount').textContent = rules.length;
  
  const addBtn = document.getElementById('addRuleBtn');
  if (rules.length >= MAX_RULES) {
    addBtn.disabled = true;
    addBtn.style.opacity = '0.5';
    addBtn.style.cursor = 'not-allowed';
  } else {
    addBtn.disabled = false;
    addBtn.style.opacity = '1';
    addBtn.style.cursor = 'pointer';
  }
}

function closeRuleModal() {
  document.getElementById('ruleModal').style.display = 'none';
}

// Tag Management
function handleTagInput(event) {
  if (event.key === 'Enter') {
    event.preventDefault();
    addTag();
  }
}

function addTag() {
  const input = document.getElementById('tagInput');
  const tag = input.value.trim().toLowerCase();
  
  if (!tag) return;
  
  if (tags.length >= MAX_TAGS) {
    showNotification('Maximum of 10 tags allowed', 'error');
    return;
  }
  
  if (tags.includes(tag)) {
    showNotification('Tag already exists', 'error');
    return;
  }
  
  tags.push(tag);
  input.value = '';
  renderTags();
}

function renderTags() {
  const container = document.getElementById('tagsContainer');
  container.innerHTML = '';
  
  tags.forEach((tag, index) => {
    const tagDiv = document.createElement('div');
    tagDiv.className = 'tag-item';
    tagDiv.innerHTML = `
      ${tag}
      <button class="tag-remove" onclick="removeTag(${index})">×</button>
    `;
    container.appendChild(tagDiv);
  });
}

function removeTag(index) {
  tags.splice(index, 1);
  renderTags();
}

// Validation
function validateForm() {
  let isValid = true;
  
  // Clear previous errors
  document.querySelectorAll('.error-text').forEach(el => el.textContent = '');
  
  // Validate name
  const name = document.getElementById('communityName').value.trim();
  if (!name) {
    document.getElementById('nameError').textContent = 'Community name is required';
    isValid = false;
  }
  
  // Validate category
  const category = document.getElementById('communityCategory').value;
  if (!category) {
    document.getElementById('categoryError').textContent = 'Category is required';
    isValid = false;
  }
  
  // Validate description
  const description = document.getElementById('communityDescription').value.trim();
  if (!description) {
    document.getElementById('descriptionError').textContent = 'Description is required';
    isValid = false;
  } else if (description.length < 50) {
    document.getElementById('descriptionError').textContent = 'Description must be at least 50 characters';
    isValid = false;
  }
  
  return isValid;
}

// Save functions
function saveDraft() {
  if (!validateForm()) {
    showNotification('Please fix the errors before saving', 'error');
    return;
  }
  
  const communityData = getCommunityData();
  communityData.status = 'draft';
  
  saveCommunity(communityData);
}

function publishCommunity() {
  if (!validateForm()) {
    showNotification('Please fix the errors before publishing', 'error');
    return;
  }
  
  if (rules.length === 0) {
    if (!confirm('No rules have been added. Do you want to publish without rules?')) {
      return;
    }
  }
  
  const communityData = getCommunityData();
  communityData.status = 'active';
  
  saveCommunity(communityData);
}

function getCommunityData() {
  return {
    name: document.getElementById('communityName').value.trim(),
    category: document.getElementById('communityCategory').value,
    privacy: document.getElementById('communityPrivacy').value,
    description: document.getElementById('communityDescription').value.trim(),
    rules: rules,
    tags: tags
  };
}

function saveCommunity(communityData) {
  // Show loading state
  const buttons = document.querySelectorAll('.action-bar button');
  buttons.forEach(btn => btn.disabled = true);
  
  // Send to backend
  fetch(`${URLROOT}/community/save`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(communityData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification(data.message, 'success');
      setTimeout(() => {
        window.location.href = `${URLROOT}/community`;
      }, 1500);
    } else {
      showNotification(data.errors.join(', '), 'error');
      buttons.forEach(btn => btn.disabled = false);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('An error occurred. Please try again.', 'error');
    buttons.forEach(btn => btn.disabled = false);
  });
}

function previewCommunity() {
  if (!validateForm()) {
    showNotification('Please fix the errors before previewing', 'error');
    return;
  }
  
  const communityData = getCommunityData();
  console.log('Preview:', communityData);
  showNotification('Preview functionality coming soon!', 'success');
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

// Modal close on outside click
window.onclick = function(event) {
  const ruleModal = document.getElementById('ruleModal');
  if (event.target == ruleModal) {
    closeRuleModal();
  }
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
`;
document.head.appendChild(style);

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initCreate);