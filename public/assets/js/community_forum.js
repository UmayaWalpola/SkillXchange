// CODECHECK GUIDE: PHP sets these globals in the community views before this script loads.
// If an AJAX route breaks, first confirm urlRoot is set correctly.
const currentUser = {
    id: window.currentUserId || 1,
    name: window.currentUserName || 'You'
};
const urlRoot = window.urlRoot || window.URLROOT || '';

document.addEventListener('DOMContentLoaded', function() {
    if (!urlRoot) {
        console.error('❌ URLROOT not defined!');
        return;
    }
    console.log('✅ Communities module loaded');
});

// ============================================
// COMMUNITY ACTIONS
// ============================================

async function joinCommunity(id) {
    try {
        const res = await fetch(urlRoot + '/userdashboard/joinCommunity', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `community_id=${id}`
        });
        const result = await res.json();
        showNotification(result.message, result.success ? 'success' : 'error');
        if (result.success) setTimeout(() => location.reload(), 1000);
    } catch (e) {
        showNotification('Network error. Please try again.', 'error');
    }
}

async function leaveCommunity(id) {
    if (!confirm('Are you sure you want to leave this community?')) return;
    try {
        const res = await fetch(urlRoot + '/userdashboard/leaveCommunity', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `community_id=${id}`
        });
        const result = await res.json();
        showNotification(result.message, result.success ? 'success' : 'error');
        if (result.success) setTimeout(() => location.reload(), 1000);
    } catch (e) {
        showNotification('Network error. Please try again.', 'error');
    }
}

function viewCommunity(communityId) {
    window.location.href = urlRoot + '/userdashboard/viewCommunity/' + communityId;
}

// ============================================
// CREATE POST (with image upload via FormData)
// ============================================

async function createCommunityPost(communityId) {
    // CODECHECK GUIDE: These IDs must match the form fields in app/views/users/community_detail.php.
    const title     = document.getElementById('postTitle')?.value.trim() || '';
    const content   = document.getElementById('postContent')?.value.trim() || '';
    const postType  = document.getElementById('postType')?.value || 'discussion';
    const linkUrl   = document.getElementById('postLink')?.value.trim() || '';
    const imageFile = document.getElementById('postImage')?.files[0] || null;

    if (!content && !imageFile) {
        showNotification('Post content or an image is required.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('community_id', communityId);
    formData.append('title', title);
    formData.append('content', content);
    formData.append('post_type', postType);
    formData.append('link_url', linkUrl);

    // File uploads must stay in FormData; do not set Content-Type manually.
    if (imageFile) formData.append('image', imageFile);

    const btn = document.getElementById('postSubmitBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'Posting…'; }

    try {
        const res = await fetch(urlRoot + '/userdashboard/postToCommunity', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();

        if (result.success) {
            showNotification(result.message || 'Post created!', 'success');
            setTimeout(() => location.reload(), 700);
        } else {
            showNotification(result.message || 'Failed to create post.', 'error');
            if (btn) { btn.disabled = false; btn.textContent = 'Post'; }
        }
    } catch (e) {
        console.error(e);
        showNotification('Network error. Please try again.', 'error');
        if (btn) { btn.disabled = false; btn.textContent = 'Post'; }
    }
}

// Image preview helper
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('postImage');
    if (!input) return;
    input.addEventListener('change', function () {
        const file = this.files[0];
        const wrap = document.getElementById('imagePreviewWrap');
        const preview = document.getElementById('imagePreview');
        if (file && wrap && preview) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                wrap.style.display = 'flex';
            };
            reader.readAsDataURL(file);
        }
    });
});

function removeImagePreview() {
    const input = document.getElementById('postImage');
    const wrap  = document.getElementById('imagePreviewWrap');
    const preview = document.getElementById('imagePreview');
    if (input)   input.value = '';
    if (preview) preview.src = '';
    if (wrap)    wrap.style.display = 'none';
}

// ============================================
// LIKES — update in-place, no page reload
// ============================================

async function likePost(postId) {
    const btn       = document.getElementById('like-btn-' + postId);
    const countEl   = document.getElementById('like-count-' + postId);
    if (!btn) return;

    try {
        const res = await fetch(urlRoot + '/userdashboard/reactToPost', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${postId}&reaction_type=like`
        });
        const data = await res.json();

        if (data.success) {
            // Toggle liked class
            btn.classList.toggle('liked', data.reacted);
            btn.title = data.reacted ? 'Unlike' : 'Like';

            // Update the SVG fill
            const icon = btn.querySelector('.like-icon');
            if (icon) icon.setAttribute('fill', data.reacted ? 'currentColor' : 'none');

            // Update count
            if (countEl) countEl.textContent = data.like_count;
        } else {
            showNotification(data.message || 'Could not react', 'error');
        }
    } catch (e) {
        console.error(e);
        showNotification('Network error.', 'error');
    }
}

// ============================================
// COMMENTS
// ============================================

function toggleComments(postId) {
    const el = document.getElementById('comments-' + postId);
    if (!el) return;
    const isHidden = el.style.display === 'none' || el.style.display === '';
    el.style.display = isHidden ? 'block' : 'none';

    // Focus comment input when opening
    if (isHidden) {
        const input = el.querySelector('.comment-input');
        if (input) setTimeout(() => input.focus(), 50);
    }
}

async function handleComment(event, communityId, postId) {
    if (event.key !== 'Enter') return;
    event.preventDefault();

    const input   = event.target;
    const content = input.value.trim();
    if (!content) return;

    input.disabled = true;

    try {
        const res = await fetch(urlRoot + '/userdashboard/addCommunityComment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `community_id=${encodeURIComponent(communityId)}&parent_id=${encodeURIComponent(postId)}&content=${encodeURIComponent(content)}`
        });
        const result = await res.json();

        if (result.success) {
            // Append the new comment to the DOM without a reload
            appendComment(postId, currentUser.name, content);
            input.value = '';
        } else {
            showNotification(result.message || 'Failed to add comment', 'error');
        }
    } catch (e) {
        console.error(e);
        showNotification('Network error.', 'error');
    } finally {
        input.disabled = false;
        input.focus();
    }
}

function appendComment(postId, authorName, content) {
    const section = document.getElementById('comments-' + postId);
    if (!section) return;

    let list = section.querySelector('.comments-list');
    if (!list) {
        list = document.createElement('div');
        list.className = 'comments-list';
        section.insertBefore(list, section.querySelector('.add-comment-row'));
    }

    // Remove "no comments yet" placeholder if present
    const placeholder = list.querySelector('.no-comments-yet');
    if (placeholder) placeholder.remove();

    const item = document.createElement('div');
    item.className = 'comment-item';
    item.innerHTML = `
        <div class="member-avatar comment-avatar">${escapeHtml(authorName.charAt(0).toUpperCase())}</div>
        <div class="comment-body">
            <span class="comment-author">${escapeHtml(authorName)}</span>
            <span class="comment-text">${escapeHtml(content)}</span>
            <span class="comment-time">just now</span>
        </div>
    `;
    list.appendChild(item);

    // Update the button count
    const toggleBtn = document.querySelector(`[onclick="toggleComments(${postId})"]`);
    if (toggleBtn) {
        const current = parseInt(toggleBtn.textContent.match(/\d+/)?.[0] || '0');
        toggleBtn.innerHTML = `<i class="ph ph-chat-circle"></i> ${current + 1} Comments`;
    }
}

// ============================================
// UI HELPERS
// ============================================

function showNotification(message, type = 'info') {
    const n = document.createElement('div');
    n.className = `notification notification-${type}`;
    n.textContent = message;
    n.style.cssText = `
        position:fixed;top:80px;right:20px;
        padding:15px 20px;
        background:${type==='success'?'#10b981':type==='error'?'#ef4444':'#3b82f6'};
        color:#fff;border-radius:8px;
        box-shadow:0 4px 12px rgba(0,0,0,.15);
        z-index:10000;animation:slideIn .3s ease-out;
        max-width:300px;font-weight:500;
    `;
    document.body.appendChild(n);
    setTimeout(() => {
        n.style.animation = 'slideOut .3s ease-out';
        setTimeout(() => n.remove(), 300);
    }, 3000);
}

function escapeHtml(text) {
    if (!text) return '';
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}

if (!document.getElementById('notification-styles')) {
    const s = document.createElement('style');
    s.id = 'notification-styles';
    s.textContent = `
        @keyframes slideIn { from { transform:translateX(400px);opacity:0 } to { transform:translateX(0);opacity:1 } }
        @keyframes slideOut { from { transform:translateX(0);opacity:1 } to { transform:translateX(400px);opacity:0 } }

        /* Like button */
        .like-btn {
            display:inline-flex;align-items:center;gap:6px;
            padding:0.55rem 1rem;border-radius:8px;border:none;
            background:#4a90e2;color:#fff;cursor:pointer;font-size:.85rem;font-weight:600;
            transition:all .2s;
        }
        .like-btn:hover { background:#2563eb;transform:translateY(-2px); }
        .like-btn.liked { background:#2563eb; }
        .like-btn.liked .like-icon { fill:currentColor; }

        /* Comments toggle */
        .comments-toggle-btn {
            display:inline-flex;align-items:center;gap:6px;
            padding:0.55rem 1rem;border-radius:8px;border:none;
            background:#4a90e2;color:#fff;cursor:pointer;font-size:.85rem;font-weight:600;
            transition:all .2s;
        }
        .comments-toggle-btn:hover { background:#2563eb;transform:translateY(-2px); }

        /* Feed post actions container */
        .feed-post-actions {
            display:flex;align-items:center;gap:0.75rem;
        }

        /* Comments section */
        .comments-section {
            margin-top:1rem;padding-top:1rem;
            border-top:1px solid var(--border,#e8e8f0);
        }
        .comments-list { display:flex;flex-direction:column;gap:.65rem;margin-bottom:.85rem; }
        .comment-item { display:flex;align-items:flex-start;gap:.6rem; }
        .comment-avatar { width:28px;height:28px;font-size:.75rem;flex-shrink:0; }
        .comment-body { display:flex;flex-wrap:wrap;align-items:baseline;gap:.35rem;font-size:.88rem; }
        .comment-author { font-weight:600;color:var(--text-1,#1a1a2e); }
        .comment-text { color:var(--text-2,#4a4a6a); }
        .comment-time { font-size:.75rem;color:var(--text-3,#8888aa); }
        .no-comments-yet { font-size:.85rem;color:var(--text-3,#8888aa);padding:.25rem 0; }

        /* Add comment row */
        .add-comment-row { display:flex;align-items:center;gap:.6rem;margin-top:.5rem; }
        .comment-input {
            flex:1;padding:.55rem .85rem;
            border:1.5px solid var(--border,#e8e8f0);border-radius:20px;
            font-size:.88rem;outline:none;
            background:var(--surface-2,#f8f8fc);
            transition:border-color .2s;
        }
        .comment-input:focus { border-color:#2563eb; }

        /* Image upload */
        .image-upload-wrap { display:flex;flex-direction:column;gap:.5rem; }
        .image-upload-label {
            display:inline-flex;align-items:center;gap:.4rem;
            padding:.55rem 1rem;border-radius:8px;cursor:pointer;
            border:1.5px dashed var(--border,#e8e8f0);
            font-size:.88rem;color:var(--text-2,#4a4a6a);
            transition:border-color .2s;width:fit-content;
        }
        .image-upload-label:hover { border-color:#2563eb;color:#2563eb; }
        .image-upload-input { display:none; }
        .image-preview-wrap {
            position:relative;display:flex;width:fit-content;
            border-radius:10px;overflow:hidden;border:1px solid var(--border,#e8e8f0);
        }
        .image-preview-wrap img { max-height:180px;max-width:100%;object-fit:cover;display:block; }
        .remove-image-btn {
            position:absolute;top:6px;right:6px;
            background:rgba(0,0,0,.55);color:#fff;
            border:none;border-radius:50%;width:24px;height:24px;
            cursor:pointer;font-size:.8rem;line-height:1;
        }
    `;
    document.head.appendChild(s);
}

// ============================================
// GLOBAL EXPORTS
// ============================================

window.joinCommunity       = joinCommunity;
window.leaveCommunity      = leaveCommunity;
window.viewCommunity       = viewCommunity;
window.createCommunityPost = createCommunityPost;
window.likePost            = likePost;
window.toggleComments      = toggleComments;
window.handleComment       = handleComment;
window.removeImagePreview  = removeImagePreview;
