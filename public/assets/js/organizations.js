// ===== ORGANIZATION JAVASCRIPT =====
// Combined JS file for all organization pages

// ===== PROFILE PAGE =====
let originalProfileData = {};

function enableEdit() {
    // Enable all inputs
    const inputs = document.querySelectorAll('.info-input, .info-textarea');
    inputs.forEach(input => {
        input.disabled = false;
    });
    
    // Show action buttons
    document.getElementById('actionButtons').style.display = 'flex';
    
    // Hide edit button
    document.querySelector('.edit-btn').style.display = 'none';
    
    // Store original data
    inputs.forEach(input => {
        originalProfileData[input.id] = input.value;
    });
}

function cancelEdit() {
    // Disable all inputs
    const inputs = document.querySelectorAll('.info-input, .info-textarea');
    inputs.forEach(input => {
        input.disabled = true;
        // Restore original values
        if (originalProfileData[input.id]) {
            input.value = originalProfileData[input.id];
        }
    });
    
    // Hide action buttons
    document.getElementById('actionButtons').style.display = 'none';
    
    // Show edit button
    document.querySelector('.edit-btn').style.display = 'block';
}

function saveProfile() {
    // Gather form data
    const formData = new FormData();
    formData.append('org_name', document.getElementById('inputOrgName').value);
    formData.append('email', document.getElementById('inputEmail').value);
    formData.append('phone', document.getElementById('inputPhone').value);
    formData.append('website', document.getElementById('inputWebsite').value);
    formData.append('description', document.getElementById('inputDescription').value);
    formData.append('address', document.getElementById('inputAddress').value);
    formData.append('city', document.getElementById('inputCity').value);
    formData.append('country', document.getElementById('inputCountry').value);
    formData.append('postal_code', document.getElementById('inputPostal').value);
    formData.append('linkedin', document.getElementById('inputLinkedin').value);
    formData.append('twitter', document.getElementById('inputTwitter').value);
    formData.append('github', document.getElementById('inputGithub').value);
    
    // Send AJAX request
    fetch(URLROOT + '/organization/updateProfile', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Profile updated successfully!');
            cancelEdit();
        } else {
            alert('Error updating profile: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating profile');
    });
}

// ===== PROJECTS PAGE =====
function viewProject(projectId) {
    window.location.href = URLROOT + '/organization/project/' + projectId;
}

function editProject(projectId) {
    window.location.href = URLROOT + '/organization/editProject/' + projectId;
}

function filterProjects() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const categoryFilter = document.getElementById('categoryFilter').value;
    
    const projectCards = document.querySelectorAll('.project-card');
    let visibleCount = 0;
    
    projectCards.forEach(card => {
        const title = card.querySelector('.project-title').textContent.toLowerCase();
        const status = card.querySelector('.status-badge').textContent.toLowerCase();
        const category = card.querySelector('.project-header').classList[1];
        
        const matchesSearch = title.includes(searchInput);
        const matchesStatus = statusFilter === 'all' || status.includes(statusFilter);
        const matchesCategory = categoryFilter === 'all' || category === categoryFilter;
        
        if (matchesSearch && matchesStatus && matchesCategory) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show/hide empty state
    const emptyState = document.querySelector('.empty-state');
    if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}

// Add event listeners for project filters
if (document.getElementById('searchInput')) {
    document.getElementById('searchInput').addEventListener('input', filterProjects);
}
if (document.getElementById('statusFilter')) {
    document.getElementById('statusFilter').addEventListener('change', filterProjects);
}
if (document.getElementById('categoryFilter')) {
    document.getElementById('categoryFilter').addEventListener('change', filterProjects);
}

// ===== APPLICATIONS PAGE =====
function viewProfile(userId) {
    window.location.href = URLROOT + '/profile/' + userId;
}

function handleApplication(applicationId, action) {
    const message = action === 'accept' ? 
        'Are you sure you want to accept this application?' : 
        'Are you sure you want to reject this application?';
    
    if (confirm(message)) {
        const formData = new FormData();
        formData.append('application_id', applicationId);
        formData.append('action', action);
        
        fetch(URLROOT + '/organization/handleApplication', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error processing application');
        });
    }
}

function filterApplications() {
    const statusFilter = document.getElementById('statusFilter').value;
    const projectFilter = document.getElementById('projectFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    
    const applicationCards = document.querySelectorAll('.application-card');
    let visibleCount = 0;
    
    applicationCards.forEach(card => {
        const status = card.querySelector('.status-badge').textContent.toLowerCase();
        const projectName = card.querySelector('.project-name').textContent;
        
        const matchesStatus = statusFilter === 'all' || status.includes(statusFilter);
        const matchesProject = projectFilter === 'all' || projectName === projectFilter;
        // TODO: Add date filtering logic
        
        if (matchesStatus && matchesProject) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Update stats
    updateApplicationStats();
    
    // Show/hide empty state
    const emptyState = document.getElementById('emptyState');
    if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}

function updateApplicationStats() {
    const cards = document.querySelectorAll('.application-card');
    let total = 0, pending = 0, accepted = 0, rejected = 0;
    
    cards.forEach(card => {
        if (card.style.display !== 'none') {
            total++;
            const status = card.dataset.status;
            if (status === 'pending') pending++;
            if (status === 'accepted') accepted++;
            if (status === 'rejected') rejected++;
        }
    });
    
    if (document.getElementById('totalApplications')) {
        document.getElementById('totalApplications').textContent = total;
        document.getElementById('pendingApplications').textContent = pending;
        document.getElementById('acceptedApplications').textContent = accepted;
        document.getElementById('rejectedApplications').textContent = rejected;
    }
}

// Add event listeners for application filters
if (document.getElementById('statusFilter')) {
    document.getElementById('statusFilter').addEventListener('change', filterApplications);
}
if (document.getElementById('projectFilter')) {
    document.getElementById('projectFilter').addEventListener('change', filterApplications);
}
if (document.getElementById('dateFilter')) {
    document.getElementById('dateFilter').addEventListener('change', filterApplications);
}

// ===== CHATS PAGE =====
let currentProjectId = null;

function selectProject(projectId) {
    currentProjectId = projectId;
    
    // Update active state
    document.querySelectorAll('.project-item').forEach(item => {
        item.classList.remove('active');
    });
    event.target.closest('.project-item').classList.add('active');
    
    // Load messages for this project
    loadMessages(projectId);
    
    // Update chat header
    const projectName = event.target.closest('.project-item').querySelector('.project-name').textContent;
    document.getElementById('chatProjectName').textContent = projectName;
}

function loadMessages(projectId) {
    fetch(URLROOT + '/organization/getMessages/' + projectId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMessages(data.data);
            }
        })
        .catch(error => {
            console.error('Error loading messages:', error);
        });
}

function displayMessages(messages) {
    const messagesArea = document.getElementById('messagesArea');
    messagesArea.innerHTML = '';
    
    messages.forEach(message => {
        const isOwn = message.user_id === currentUserId; // You need to set currentUserId globally
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message ' + (isOwn ? 'own' : 'other');
        
        messageDiv.innerHTML = `
            <div class="message-avatar">${message.user_initial}</div>
            <div class="message-content">
                <div class="message-header">
                    <span class="message-sender">${message.user_name}</span>
                    <span class="message-time">${message.time}</span>
                </div>
                <p class="message-text">${message.message}</p>
            </div>
        `;
        
        messagesArea.appendChild(messageDiv);
    });
    
    // Scroll to bottom
    messagesArea.scrollTop = messagesArea.scrollHeight;
}

function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message || !currentProjectId) {
        return;
    }
    
    const formData = new FormData();
    formData.append('project_id', currentProjectId);
    formData.append('message', message);
    
    fetch(URLROOT + '/organization/sendMessage', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            loadMessages(currentProjectId);
        } else {
            alert('Error sending message: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending message');
    });
}

// Send message on Enter key
if (document.getElementById('messageInput')) {
    document.getElementById('messageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
}

function toggleMembers() {
    const sidebar = document.getElementById('membersSidebar');
    if (sidebar.style.display === 'none' || !sidebar.style.display) {
        sidebar.style.display = 'block';
        sidebar.classList.add('active');
    } else {
        sidebar.style.display = 'none';
        sidebar.classList.remove('active');
    }
}

function showProjectDetails() {
    // TODO: Implement project details modal
    alert('Project details coming soon!');
}

// Search projects in chat sidebar
if (document.getElementById('projectSearch')) {
    document.getElementById('projectSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const projectItems = document.querySelectorAll('.project-item');
        
        projectItems.forEach(item => {
            const projectName = item.querySelector('.project-name').textContent.toLowerCase();
            if (projectName.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });
}

// ===== GLOBAL FUNCTIONS =====

// Load notification badges
function loadNotificationBadges() {
    fetch(URLROOT + '/organization/getStats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.data;
                
                // Update sidebar badges
                if (document.getElementById('applicationsBadge')) {
                    const pendingApps = stats.pending_applications || 0;
                    document.getElementById('applicationsBadge').textContent = pendingApps;
                    document.getElementById('applicationsBadge').style.display = pendingApps > 0 ? 'block' : 'none';
                }
                
                // Update chats badge (unread messages - TODO)
                // document.getElementById('chatsBadge').textContent = stats.unread_messages || 0;
            }
        })
        .catch(error => {
            console.error('Error loading stats:', error);
        });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load notification badges
    loadNotificationBadges();
    
    // Refresh badges every 30 seconds
    setInterval(loadNotificationBadges, 30000);
    
    // Initialize current page specific functions
    const currentPage = window.location.pathname.split('/').pop();
    
    if (currentPage === 'applications') {
        updateApplicationStats();
    }
});