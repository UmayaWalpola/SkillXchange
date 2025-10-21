/* Communities Page JavaScript */

// Get data from PHP
const communitiesData = window.communitiesData || [];
const currentUser = {
    id: window.currentUserId || 1,
    name: window.currentUserName || 'You'
};

// Store joined communities in localStorage
let joinedCommunities = JSON.parse(localStorage.getItem('joinedCommunities')) || {};

// Store community messages in localStorage
let allMessages = JSON.parse(localStorage.getItem('communityMessages')) || {};

// Initialize messages from PHP data if not in localStorage
communitiesData.forEach(community => {
    if (!allMessages[community.id]) {
        allMessages[community.id] = community.messages || [];
    }
});

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    renderCommunityCards();
});

// Render community cards with join/leave status
function renderCommunityCards() {
    const cards = document.querySelectorAll('.community-card');
    
    cards.forEach(card => {
        const communityId = parseInt(card.getAttribute('data-community-id'));
        const isJoined = joinedCommunities[communityId];
        
        const joinBtn = card.querySelector('.join-btn');
        const leaveBtn = card.querySelector('.leave-btn');
        const joinedBadge = card.querySelector('.joined-badge');
        
        if (isJoined) {
            joinBtn.classList.add('hide-element');
            leaveBtn.classList.remove('hide-element');
            joinedBadge.classList.remove('hide-element');
        } else {
            joinBtn.classList.remove('hide-element');
            leaveBtn.classList.add('hide-element');
            joinedBadge.classList.add('hide-element');
        }
    });
}

// Join a community
function joinCommunity(id) {
    joinedCommunities[id] = true;
    localStorage.setItem('joinedCommunities', JSON.stringify(joinedCommunities));
    renderCommunityCards();
}

// Leave a community
function leaveCommunity(id) {
    delete joinedCommunities[id];
    localStorage.setItem('joinedCommunities', JSON.stringify(joinedCommunities));
    renderCommunityCards();
}

// View community details
function viewCommunity(community) {
    const isJoined = joinedCommunities[community.id];
    const members = community.membersList || [];
    const messages = allMessages[community.id] || [];

    // Hide list page, show detail page
    document.getElementById('communitiesListPage').classList.add('hide-element');
    document.getElementById('communityDetailPage').classList.remove('hide-element');

    // Build the detail page HTML
    const detailHTML = `
        <div class="detail-header">
            <div class="detail-header-icon">${community.icon}</div>
            <div class="detail-header-info">
                <h1>${escapeHtml(community.name)}</h1>
                <p>${escapeHtml(community.description)}</p>
                <div class="community-stats">
                    <span>👥 ${community.members} members</span>
                    <span>💬 ${community.posts} posts</span>
                </div>
                <div class="header-actions">
                    ${isJoined 
                        ? `<button class="btn leave-btn" onclick="leaveCommunity(${community.id}); viewCommunity(${JSON.stringify(community).replace(/"/g, '&quot;')})">Leave Community</button>`
                        : `<button class="btn btn-primary" onclick="joinCommunity(${community.id}); viewCommunity(${JSON.stringify(community).replace(/"/g, '&quot;')})">Join Community</button>`
                    }
                </div>
            </div>
        </div>

        <div class="detail-grid">
            <div class="chat-container">
                <h3>Community Chat</h3>
                ${!isJoined ? '<div class="not-joined-msg">Join this community to participate in conversations</div>' : ''}
                <div class="messages" id="messagesList">
                    ${messages.map(msg => `
                        <div class="msg ${msg.authorId === currentUser.id ? 'own' : ''}">
                            ${msg.authorId !== currentUser.id ? `<div class="msg-author">${escapeHtml(msg.author)}</div>` : ''}
                            <div class="msg-text">${escapeHtml(msg.text)}</div>
                            <div class="msg-time">${escapeHtml(msg.time)}</div>
                        </div>
                    `).join('')}
                </div>
                ${isJoined ? `
                    <div class="input-row">
                        <input type="text" class="msg-input" id="messageInput" placeholder="Type your message..." onkeypress="handleKeyPress(event, ${community.id})">
                        <button class="btn send-btn" onclick="sendMessage(${community.id})">Send</button>
                    </div>
                ` : ''}
            </div>

            <div class="detail-sidebar">
                <div class="members-box">
                    <h3>Members (${members.length + (isJoined ? 1 : 0)})</h3>
                    <div class="members-list">
                        ${isJoined ? `
                            <div class="member">
                                <div class="member-avatar">${currentUser.name.charAt(0).toUpperCase()}</div>
                                <div>
                                    <div class="member-name">${escapeHtml(currentUser.name)}</div>
                                    <div class="member-role">Member</div>
                                </div>
                            </div>
                        ` : ''}
                        ${members.map(member => `
                            <div class="member">
                                <div class="member-avatar">${member.name.charAt(0).toUpperCase()}</div>
                                <div>
                                    <div class="member-name">${escapeHtml(member.name)}</div>
                                    <div class="member-role">${escapeHtml(member.role)}</div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <div class="about-box">
                    <h3>About</h3>
                    <p>${escapeHtml(community.about)}</p>
                </div>
            </div>
        </div>
    `;

    document.getElementById('communityDetailContent').innerHTML = detailHTML;
    scrollToBottom();
}

// Send a message in the community chat
function sendMessage(communityId) {
    const input = document.getElementById('messageInput');
    const text = input.value.trim();
    
    if (text) {
        const now = new Date();
        const time = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        
        // Initialize messages array if it doesn't exist
        if (!allMessages[communityId]) {
            allMessages[communityId] = [];
        }
        
        // Add new message
        allMessages[communityId].push({
            id: Date.now(),
            author: currentUser.name,
            authorId: currentUser.id,
            text: text,
            time: time
        });

        // Save to localStorage
        localStorage.setItem('communityMessages', JSON.stringify(allMessages));

        // Clear input
        input.value = '';
        
        // Refresh the view
        const community = communitiesData.find(c => c.id === communityId);
        if (community) {
            viewCommunity(community);
        }
    }
}

// Handle Enter key press in message input
function handleKeyPress(event, communityId) {
    if (event.key === 'Enter') {
        sendMessage(communityId);
    }
}

// Scroll chat to bottom
function scrollToBottom() {
    setTimeout(() => {
        const messagesList = document.getElementById('messagesList');
        if (messagesList) {
            messagesList.scrollTop = messagesList.scrollHeight;
        }
    }, 100);
}

// Go back to communities list
function goBack() {
    document.getElementById('communityDetailPage').classList.add('hide-element');
    document.getElementById('communitiesListPage').classList.remove('hide-element');
    renderCommunityCards();
}

// Helper function to escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}