/* Communities Page JavaScript - Forum Style with Comments */

// Get data from PHP
const communitiesData = window.communitiesData || [];
const currentUser = {
    id: window.currentUserId || 1,
    name: window.currentUserName || 'You'
};

// Store joined communities in memory
let joinedCommunities = {};

// Store community posts in memory
let allPosts = {};

// Initialize posts from PHP data
communitiesData.forEach(community => {
    if (community.posts && community.posts.length > 0) {
        allPosts[community.id] = community.posts;
    } else {
        allPosts[community.id] = [];
    }
});

// Currently viewing post (for comments)
let currentViewingPost = null;
let currentCommunityId = null;

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
    renderCommunityCards();
}

// Leave a community
function leaveCommunity(id) {
    delete joinedCommunities[id];
    renderCommunityCards();
}

// View community details
function viewCommunity(community) {
    currentCommunityId = community.id;
    const isJoined = joinedCommunities[community.id];
    const members = community.membersList || [];
    const posts = allPosts[community.id] || [];

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
                    <span>💬 ${posts.length} posts</span>
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
            <div class="forum-container">
                <div class="forum-header">
                    <h3>Community Forum</h3>
                    ${isJoined ? `<button class="btn btn-primary" onclick="showCreatePostModal()">Create Post</button>` : ''}
                </div>

                ${!isJoined ? '<div class="not-joined-msg">Join this community to create posts and participate in discussions</div>' : ''}
                
                <div class="posts-list" id="postsList">
                    ${posts.length === 0 ? '<div class="no-posts">No posts yet. Be the first to post!</div>' : 
                        posts.map(post => `
                            <div class="forum-post">
                                <div class="post-header">
                                    <div class="post-author-info">
                                        <div class="post-avatar">${post.author.charAt(0).toUpperCase()}</div>
                                        <div>
                                            <div class="post-author">${escapeHtml(post.author)}</div>
                                            <div class="post-time">${escapeHtml(post.time)}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="post-content">
                                    <h4 class="post-title">${escapeHtml(post.title)}</h4>
                                    <p class="post-text">${escapeHtml(post.content)}</p>
                                </div>
                                <div class="post-footer">
                                    <button class="post-action" onclick="likePost(${post.id})">
                                        👍 ${post.likes} Likes
                                    </button>
                                    <button class="post-action" onclick="viewComments(${post.id})">
                                        💬 ${(post.replies || []).length} Comments
                                    </button>
                                </div>
                                
                                <!-- Comments Section -->
                                <div id="comments-${post.id}" class="comments-section hide-element">
                                    <div class="comments-list">
                                        ${(post.replies || []).map(reply => `
                                            <div class="comment">
                                                <div class="comment-avatar">${reply.author.charAt(0).toUpperCase()}</div>
                                                <div class="comment-content">
                                                    <div class="comment-author">${escapeHtml(reply.author)}</div>
                                                    <div class="comment-text">${escapeHtml(reply.content)}</div>
                                                    <div class="comment-time">${escapeHtml(reply.time)}</div>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                    ${isJoined ? `
                                        <div class="comment-input-section">
                                            <input type="text" class="comment-input" id="comment-input-${post.id}" placeholder="Write a comment...">
                                            <button class="btn btn-primary btn-sm" onclick="addComment(${post.id})">Comment</button>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        `).join('')
                    }
                </div>
            </div>

            <div class="detail-sidebar">
                <div class="members-box">
                    <h3>Members (${members.length + (isJoined ? 1 : 0)})</h3>
                    <div class="members-list">
                        ${isJoined ? `
                            <div class="member">
                                <div class="member-avatar">${currentUser.name.charAt(0).toUpperCase()}</div>
                                <div>
                                    <div class="member-name">${escapeHtml(currentUser.name)} (You)</div>
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

        <!-- Create Post Modal -->
        <div id="createPostModal" class="modal hide-element">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Create New Post</h3>
                    <button class="modal-close" onclick="hideCreatePostModal()">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Post Title</label>
                        <input type="text" id="postTitle" class="form-input" placeholder="What's on your mind?">
                    </div>
                    <div class="form-group">
                        <label>Post Content</label>
                        <textarea id="postContent" class="form-textarea" rows="6" placeholder="Share your thoughts, questions, or insights..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" onclick="hideCreatePostModal()">Cancel</button>
                    <button class="btn btn-primary" onclick="createPost()">Post</button>
                </div>
            </div>
        </div>
    `;

    document.getElementById('communityDetailContent').innerHTML = detailHTML;
}

// Show create post modal
function showCreatePostModal() {
    const modal = document.getElementById('createPostModal');
    if (modal) {
        modal.classList.remove('hide-element');
    }
}

// Hide create post modal
function hideCreatePostModal() {
    const modal = document.getElementById('createPostModal');
    if (modal) {
        modal.classList.add('hide-element');
        document.getElementById('postTitle').value = '';
        document.getElementById('postContent').value = '';
    }
}

// Create a new post
function createPost() {
    const title = document.getElementById('postTitle').value.trim();
    const content = document.getElementById('postContent').value.trim();
    
    if (!title || !content) {
        alert('Please fill in both title and content');
        return;
    }
    
    // Initialize posts array if it doesn't exist
    if (!allPosts[currentCommunityId]) {
        allPosts[currentCommunityId] = [];
    }
    
    // Add new post to the beginning of the array
    const newPost = {
        id: Date.now(),
        author: currentUser.name,
        authorId: currentUser.id,
        title: title,
        content: content,
        time: 'Just now',
        likes: 0,
        replies: []
    };
    
    allPosts[currentCommunityId].unshift(newPost);
    
    // Refresh the view
    const community = communitiesData.find(c => c.id === currentCommunityId);
    if (community) {
        hideCreatePostModal();
        viewCommunity(community);
    }
}

// Like a post
function likePost(postId) {
    const posts = allPosts[currentCommunityId];
    const post = posts.find(p => p.id === postId);
    
    if (post) {
        post.likes++;
        
        // Refresh the view
        const community = communitiesData.find(c => c.id === currentCommunityId);
        if (community) {
            viewCommunity(community);
        }
    }
}

// View comments for a post
function viewComments(postId) {
    const commentsSection = document.getElementById(`comments-${postId}`);
    if (commentsSection) {
        commentsSection.classList.toggle('hide-element');
    }
}

// Add a comment to a post
function addComment(postId) {
    const commentInput = document.getElementById(`comment-input-${postId}`);
    const commentText = commentInput.value.trim();
    
    if (!commentText) {
        alert('Please enter a comment');
        return;
    }
    
    const posts = allPosts[currentCommunityId];
    const post = posts.find(p => p.id === postId);
    
    if (post) {
        if (!post.replies) {
            post.replies = [];
        }
        
        const newComment = {
            id: Date.now(),
            author: currentUser.name,
            authorId: currentUser.id,
            content: commentText,
            time: 'Just now'
        };
        
        post.replies.push(newComment);
        
        // Refresh the view
        const community = communitiesData.find(c => c.id === currentCommunityId);
        if (community) {
            viewCommunity(community);
            // Re-open the comments section
            setTimeout(() => {
                const commentsSection = document.getElementById(`comments-${postId}`);
                if (commentsSection) {
                    commentsSection.classList.remove('hide-element');
                }
            }, 100);
        }
    }
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