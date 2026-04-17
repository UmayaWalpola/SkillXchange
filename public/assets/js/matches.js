

// matches.js - Fixed version with proper UI updates


function connectWithUser(userId, userName) {
   if (!confirm(`Send connection request to ${userName}?`)) {
       return;
   }


   const formData = new FormData();
   formData.append('user_id', userId);


   fetch(`${URLROOT}/userdashboard/connect`, {
       method: 'POST',
       body: formData
   })
   .then(response => response.json())
   .then(data => {
       if (data.success) {
           showNotification(data.message || 'Connection request sent!', 'success');
          
           // Update the button to show pending state
           updateButtonToPending(userId);
       } else {
           showNotification(data.message || 'Failed to send request', 'error');
       }
   })
   .catch(error => {
       console.error('Error:', error);
       showNotification('Network error. Please try again.', 'error');
   });
}


function updateButtonToPending(userId) {
   // Find all match cards for this user
   const matchCards = document.querySelectorAll(`.match-card`);
  
   matchCards.forEach(card => {
       const connectBtn = card.querySelector('.btn-connect');
       if (connectBtn && connectBtn.onclick.toString().includes(`connectWithUser(${userId}`)) {
           // Replace button with pending badge
           const footer = connectBtn.closest('.match-footer');
           connectBtn.remove();
          
           const pendingBadge = document.createElement('span');
           pendingBadge.className = 'badge badge-warning';
           pendingBadge.innerHTML = '⏳ Request Pending';
           footer.appendChild(pendingBadge);
       }
   });
}


function handleRequest(exchangeId, action) {
   const actionText = action === 'accept' ? 'accept' : 'reject';
  
   if (!confirm(`Are you sure you want to ${actionText} this request?`)) {
       return;
   }


   const formData = new FormData();
   formData.append('exchange_id', exchangeId);
   formData.append('action', action);


   fetch(`${URLROOT}/userdashboard/handleRequest`, {
       method: 'POST',
       body: formData
   })
   .then(response => response.json())
   .then(data => {
       if (data.success) {
           showNotification(data.message, 'success');
          
           // Remove the request card with animation
           const requestCard = document.querySelector(`[onclick*="handleRequest(${exchangeId}"]`).closest('.request-card');
           if (requestCard) {
               requestCard.style.opacity = '0';
               requestCard.style.transform = 'translateX(-20px)';
               setTimeout(() => {
                   requestCard.remove();
                   updateRequestCount();
                  
                   // If accepted, update the match card status
                   if (action === 'accept') {
                       // Reload page to show updated connection status
                       setTimeout(() => {
                           location.reload();
                       }, 1000);
                   }
               }, 300);
           }
       } else {
           showNotification(data.message || 'Action failed', 'error');
       }
   })
   .catch(error => {
       console.error('Error:', error);
       showNotification('Network error. Please try again.', 'error');
   });
}


function updateRequestCount() {
   const requestsSection = document.querySelector('.connection-requests-section');
   if (requestsSection) {
       const requestCards = requestsSection.querySelectorAll('.request-card');
       const badgeCount = requestsSection.querySelector('.badge-count');
      
       if (badgeCount) {
           badgeCount.textContent = requestCards.length;
       }
      
       // Hide section if no requests
       if (requestCards.length === 0) {
           requestsSection.style.display = 'none';
       }
   }
}


function viewProfile(userId) {
   window.location.href = `${URLROOT}/userdashboard/viewProfile/${userId}`;
}


function clearFilters() {
   document.getElementById('match-tier-filter').value = 'all';
   document.getElementById('skill-filter').value = 'all';
   applyFilters();
}


function applyFilters() {
   const tierFilter = document.getElementById('match-tier-filter')?.value || 'all';
   const skillFilter = document.getElementById('skill-filter')?.value || 'all';

   const cards = document.querySelectorAll('.match-card');
   let visibleCount = 0;

   cards.forEach(card => {
       let cardSkills = [];

       try {
           cardSkills = JSON.parse(card.dataset.skills || '[]');
       } catch (e) {
           cardSkills = [];
       }

       const matchesTier = tierFilter === 'all' || card.dataset.tier === tierFilter;
       const matchesSkill = skillFilter === 'all' || cardSkills.includes(skillFilter);

       if (matchesTier && matchesSkill) {
           card.style.display = 'block';
           visibleCount++;
       } else {
           card.style.display = 'none';
       }
   });

   // optional: update count text
   const tierCount = document.querySelector('.tier-count');
   if (tierCount) {
       tierCount.textContent = `(${visibleCount} found)`;
   }
}


function showNotification(message, type = 'info') {
   // Remove existing notifications
   const existing = document.querySelector('.notification-banner');
   if (existing) {
       existing.remove();
   }
  
   const notification = document.createElement('div');
   notification.className = `notification-banner notification-${type}`;
   notification.innerHTML = `
       <span class="notification-message">${message}</span>
       <button class="notification-close" onclick="this.parentElement.remove()">×</button>
   `;
  
   document.body.appendChild(notification);
  
   // Auto-remove after 5 seconds
   setTimeout(() => {
       if (notification.parentElement) {
           notification.style.opacity = '0';
           setTimeout(() => notification.remove(), 300);
       }
   }, 5000);
}


function openSkillChat(userId) {
   window.location.href = `${URLROOT}/chat/user/${userId}`;
}


// Search functionality (if needed)
function searchMatches() {
   const searchInput = document.querySelector('.matches-search-input');
   if (!searchInput) return;
  
   const query = searchInput.value.toLowerCase();
   const cards = document.querySelectorAll('.match-card');
  
   cards.forEach(card => {
       const name = card.querySelector('.match-name')?.textContent.toLowerCase() || '';
       const skills = card.querySelectorAll('.skill-name');
       let skillText = '';
       skills.forEach(skill => skillText += skill.textContent.toLowerCase() + ' ');
      
       if (name.includes(query) || skillText.includes(query)) {
           card.style.display = 'block';
       } else {
           card.style.display = 'none';
       }
   });
}


function openSkillChat(userId, skillName = null, direction = null) {
   window.location.href = `${URLROOT}/chat/user/${userId}`;
}


// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
   // Filter event listeners
   const tierFilter = document.getElementById('match-tier-filter');
   const skillFilter = document.getElementById('skill-filter');
  
   if (tierFilter) {
       tierFilter.addEventListener('change', applyFilters);
   }
  
   if (skillFilter) {
       skillFilter.addEventListener('change', applyFilters);
   }
  
   // Search functionality
   const searchInput = document.querySelector('.matches-search-input');
   if (searchInput) {
       searchInput.addEventListener('input', searchMatches);
   }
  
   // Log page load for debugging
   console.log('Matches page loaded successfully');
   console.log('URLROOT:', URLROOT);
});
