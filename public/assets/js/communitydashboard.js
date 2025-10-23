// Community Admin Dashboard JavaScript

// Sample community data
let communities = [
  {
    id: 1,
    name: "Web Developers Hub",
    description: "A community for web developers to share knowledge and collaborate",
    category: "technology",
    status: "active",
    members: 1247,
    posts: 3456,
    privacy: "public",
    createdDate: "2024-01-15"
  },
  {
    id: 2,
    name: "Fitness & Wellness",
    description: "Share your fitness journey and wellness tips",
    category: "health",
    status: "active",
    members: 892,
    posts: 2134,
    privacy: "public",
    createdDate: "2024-02-10"
  },
  {
    id: 3,
    name: "Digital Marketing Pros",
    description: "Learn and discuss digital marketing strategies",
    category: "business",
    status: "active",
    members: 654,
    posts: 1523,
    privacy: "private",
    createdDate: "2024-02-20"
  },
  {
    id: 4,
    name: "Creative Writers Circle",
    description: "A space for writers to share and improve their craft",
    category: "lifestyle",
    status: "inactive",
    members: 423,
    posts: 891,
    privacy: "public",
    createdDate: "2024-03-05"
  },
  {
    id: 5,
    name: "Online Learning Community",
    description: "Discuss online courses and educational resources",
    category: "education",
    status: "active",
    members: 1105,
    posts: 2876,
    privacy: "public",
    createdDate: "2024-01-25"
  }
];

let communityToDelete = null;

// Initialize dashboard
function initDashboard() {
  updateStats();
  renderCommunityTable();
}

// Update statistics
function updateStats() {
  const totalCommunities = communities.length;
  const activeCommunities = communities.filter(c => c.status === 'active').length;
  const totalMembers = communities.reduce((sum, c) => sum + c.members, 0);
  const totalPosts = communities.reduce((sum, c) => sum + c.posts, 0);

  document.getElementById('totalCommunities').textContent = totalCommunities;
  document.getElementById('activeCommunities').textContent = activeCommunities;
  document.getElementById('totalMembers').textContent = totalMembers.toLocaleString();
  document.getElementById('totalPosts').textContent = totalPosts.toLocaleString();
}

// Render community table
function renderCommunityTable(filteredCommunities = null) {
  const communitiesToShow = filteredCommunities || communities;
  const tbody = document.getElementById('communityTableBody');
  tbody.innerHTML = '';

  if (communitiesToShow.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #999;">No communities found</td></tr>';
    return;
  }

  communitiesToShow.forEach(community => {
    const row = document.createElement('tr');
    
    row.innerHTML = `
      <td>
        <div class="community-name">${community.name}</div>
        <div class="community-description">${community.description}</div>
      </td>
      <td>
        <span class="category-badge">${community.category}</span>
      </td>
      <td>
        <span class="status-text status-${community.status}">${community.status}</span>
      </td>
      <td><div class="count-display">${community.members.toLocaleString()}</div></td>
      <td><div class="count-display">${community.posts.toLocaleString()}</div></td>
      <td><div class="date-display">${new Date(community.createdDate).toLocaleDateString()}</div></td>
      <td>
        <div class="action-buttons">
          <button class="action-btn btn-view" onclick="viewCommunity(${community.id})">View</button>
          <button class="action-btn btn-edit" onclick="editCommunity(${community.id})">Edit</button>
          ${community.status === 'active' 
            ? `<button class="action-btn btn-deactivate" onclick="deactivateCommunity(${community.id})">Deactivate</button>`
            : `<button class="action-btn btn-activate" onclick="activateCommunity(${community.id})">Activate</button>`
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
  
  if (categoryFilter !== 'all') {
    filtered = filtered.filter(c => c.category === categoryFilter);
  }
  
  if (statusFilter !== 'all') {
    filtered = filtered.filter(c => c.status === statusFilter);
  }
  
  renderCommunityTable(filtered);
}

// Community management functions
function viewCommunity(id) {
  // Future: Redirect to community view page
  window.location.href = `${URLROOT}/community/view/${id}`;
}

function editCommunity(id) {
  // Future: Redirect to community edit page
  window.location.href = `${URLROOT}/community/edit/${id}`;
}

function activateCommunity(id) {
  const community = communities.find(c => c.id === id);
  if (community) {
    community.status = 'active';
    updateStats();
    renderCommunityTable();
    showNotification('Community activated successfully!', 'success');
    // Future: Make AJAX call to backend
  }
}

function deactivateCommunity(id) {
  const community = communities.find(c => c.id === id);
  if (community) {
    community.status = 'inactive';
    updateStats();
    renderCommunityTable();
    showNotification('Community deactivated successfully!', 'success');
    // Future: Make AJAX call to backend
  }
}

function openDeleteModal(id) {
  communityToDelete = id;
  document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
  communityToDelete = null;
  document.getElementById('deleteModal').style.display = 'none';
}

function confirmDelete() {
  if (communityToDelete !== null) {
    communities = communities.filter(c => c.id !== communityToDelete);
    updateStats();
    renderCommunityTable();
    closeDeleteModal();
    showNotification('Community deleted successfully!', 'success');
    // Future: Make AJAX call to backend
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

// Close modal when clicking outside
window.onclick = function(event) {
  const modal = document.getElementById('deleteModal');
  if (event.target == modal) {
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