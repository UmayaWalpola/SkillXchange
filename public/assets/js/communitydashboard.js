// Community Admin Dashboard JavaScript - Backend Integrated

let communities = [];
let communityToDelete = null;

// Initialize dashboard
async function initDashboard() {
  await loadCommunities();
  await loadStats();
}

// Load communities from backend
async function loadCommunities() {
  try {
    const response = await fetch(`${URLROOT}/community/getAll`);
    const result = await response.json();
    
    if(result.success) {
      communities = result.data;
      renderCommunityTable();
    } else {
      showNotification('Failed to load communities', 'error');
    }
  } catch(error) {
    console.error('Error loading communities:', error);
    showNotification('Error loading communities', 'error');
  }
}

// Load statistics from backend
async function loadStats() {
  try {
    const response = await fetch(`${URLROOT}/community/getStats`);
    const result = await response.json();
    
    if(result.success) {
      updateStats(result.data);
    }
  } catch(error) {
    console.error('Error loading stats:', error);
  }
}

// Update statistics display
function updateStats(stats) {
  document.getElementById('totalCommunities').textContent = stats.total_communities || 0;
  document.getElementById('activeCommunities').textContent = stats.active_communities || 0;
  document.getElementById('totalMembers').textContent = (stats.total_members || 0).toLocaleString();
  document.getElementById('totalPosts').textContent = (stats.total_posts || 0).toLocaleString();
}

// Render community table
function renderCommunityTable(filteredCommunities = null) {
  const communitiesToShow = filteredCommunities || communities;
  const tbody = document.getElementById('communityTableBody');
  tbody.innerHTML = '';

  if(communitiesToShow.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #999;">No communities found</td></tr>';
    return;
  }

  communitiesToShow.forEach(community => {
    const row = document.createElement('tr');
    
    row.innerHTML = `
      <td>
        <div class="community-name">${escapeHtml(community.name)}</div>
        <div class="community-description">${escapeHtml(community.description.substring(0, 100))}...</div>
      </td>
      <td>
        <span class="category-badge">${escapeHtml(community.category)}</span>
      </td>
      <td>
        <span class="status-text status-${community.status}">${community.status}</span>
      </td>
      <td><div class="count-display">${parseInt(community.members || 0).toLocaleString()}</div></td>
      <td><div class="count-display">${parseInt(community.posts || 0).toLocaleString()}</div></td>
      <td><div class="date-display">${formatDate(community.created_at)}</div></td>
      <td>
        <div class="action-buttons">
          <button class="action-btn btn-view" onclick="viewCommunity(${community.id})">View</button>
          <button class="action-btn btn-edit" onclick="editCommunity(${community.id})">Edit</button>
          ${community.status === 'active' 
            ? `<button class="action-btn btn-deactivate" onclick="toggleStatus(${community.id}, 'inactive')">Deactivate</button>`
            : `<button class="action-btn btn-activate" onclick="toggleStatus(${community.id}, 'active')">Activate</button>`
          }
          <button class="action-btn btn-delete" onclick="openDeleteModal(${community.id})">Delete</button>
        </div>
      </td>
    `;
    tbody.appendChild(row);
  });
}

// Filter communities
function filterCommunities() {
  const categoryFilter = document.getElementById('categoryFilter').value;
  const statusFilter = document.getElementById('statusFilter').value;
  
  let filtered = communities;
  
  if(categoryFilter !== 'all') {
    filtered = filtered.filter(c => c.category === categoryFilter);
  }
  
  if(statusFilter !== 'all') {
    filtered = filtered.filter(c => c.status === statusFilter);
  }
  
  renderCommunityTable(filtered);
}

// View community
function viewCommunity(id) {
  window.location.href = `${URLROOT}/community/view/${id}`;
}

// Edit community
function editCommunity(id) {
  window.location.href = `${URLROOT}/community/edit/${id}`;
}

// Toggle community status (activate/deactivate)
async function toggleStatus(id, newStatus) {
  try {
    const response = await fetch(`${URLROOT}/community/toggleStatus`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id: id })
    });

    const result = await response.json();
    
    if(result.success) {
      showNotification(result.message, 'success');
      await loadCommunities();
      await loadStats();
    } else {
      showNotification(result.message, 'error');
    }
  } catch(error) {
    console.error('Error toggling status:', error);
    showNotification('Error updating community status', 'error');
  }
}

// Open delete modal
function openDeleteModal(id) {
  communityToDelete = id;
  document.getElementById('deleteModal').style.display = 'block';
}

// Close delete modal
function closeDeleteModal() {
  communityToDelete = null;
  document.getElementById('deleteModal').style.display = 'none';
}

// Confirm delete
async function confirmDelete() {
  if(communityToDelete === null) return;
  
  try {
    const response = await fetch(`${URLROOT}/community/delete`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id: communityToDelete })
    });

    const result = await response.json();
    
    if(result.success) {
      showNotification(result.message, 'success');
      closeDeleteModal();
      await loadCommunities();
      await loadStats();
    } else {
      showNotification(result.message, 'error');
    }
  } catch(error) {
    console.error('Error deleting community:', error);
    showNotification('Error deleting community', 'error');
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

// Utility functions
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
}

// Close modal when clicking outside
window.onclick = function(event) {
  const modal = document.getElementById('deleteModal');
  if(event.target == modal) {
    closeDeleteModal();
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
document.addEventListener('DOMContentLoaded', initDashboard);