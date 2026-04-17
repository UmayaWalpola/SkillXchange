<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/community_forum.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/reporting.css">

<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">
            <div class="community-detail">

                <button class="btn back-btn" onclick="window.location.href='<?= URLROOT ?>/userdashboard/communities'">
                    ← Back to Communities
                </button>

                <div class="detail-header">
                    <div class="detail-header-icon"><?= strtoupper(substr($data['community']->name ?? 'C', 0, 1)) ?></div>
                    <div class="detail-header-info">
                        <h1><?= htmlspecialchars($data['community']->name ?? '') ?></h1>
                        <p><?= htmlspecialchars($data['community']->description ?? '') ?></p>
                        <div class="community-stats">
                            <span><i class="ph ph-users"></i> <?= $data['community']->members ?> members</span>
                            <span><i class="ph ph-chat-circle-dots"></i> <?= $data['community']->posts ?> posts</span>
                        </div>
                        <div class="header-actions">
                            <?php if ($data['community']->is_member): ?>
                                <button class="btn leave-btn" onclick="leaveCommunity(<?= $data['community']->id ?>)">Leave Community</button>
                            <?php else: ?>
                                <button class="btn btn-primary" onclick="joinCommunity(<?= $data['community']->id ?>)">Join Community</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="detail-grid">
                    <!-- Feed -->
                    <section class="chat-container community-feed-section">
                        <h3>Community Feed</h3>

                        <?php if (!$data['community']->is_member): ?>
                            <div class="not-joined-msg">Join this community to create posts and participate in discussions.</div>
                        <?php endif; ?>

                        <?php if ($data['community']->is_member): ?>
                            <div class="create-post-card">
                                <h4>Create a Post</h4>
                                <!-- NOTE: must be a real <form> with enctype for file upload -->
                                <form id="createPostForm" enctype="multipart/form-data">
                                    <div class="create-post-form">
                                        <input type="text" id="postTitle" class="post-input" placeholder="Post title (optional)">

                                        <select id="postType" class="post-input">
                                            <option value="discussion">Discussion</option>
                                            <?php if (in_array($data['community']->user_role, ['admin', 'moderator'])): ?>
                                                <option value="announcement">Announcement</option>
                                            <?php endif; ?>
                                        </select>

                                        <textarea id="postContent" class="post-textarea" placeholder="Share something with the community..." rows="5"></textarea>

                                        <input type="url" id="postLink" class="post-input" placeholder="Add a link (optional)">

                                        <!-- Image upload -->
                                        <div class="image-upload-wrap">
                                            <label for="postImage" class="image-upload-label">
                                                <i class="ph ph-image"></i> Attach an image (optional)
                                            </label>
                                            <input type="file" id="postImage" name="image" accept="image/*" class="image-upload-input">
                                            <div id="imagePreviewWrap" class="image-preview-wrap" style="display:none;">
                                                <img id="imagePreview" src="" alt="Preview">
                                                <button type="button" class="remove-image-btn" onclick="removeImagePreview()">✕</button>
                                            </div>
                                        </div>

                                        <div class="post-form-actions">
                                            <button type="button" class="send-btn" id="postSubmitBtn" onclick="createCommunityPost(<?= $data['community']->id ?>)">
                                                Post
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>

                        <div class="community-feed" id="messagesList">
                            <?php if (empty($data['posts'])): ?>
                                <div class="no-messages"><p>No posts yet. Be the first to start the discussion!</p></div>
                            <?php else: ?>
                                <?php
                                // Separate top-level posts from comments
                                $topPosts = [];
                                $commentsByParent = [];
                                foreach ($data['posts'] as $post) {
                                    if (empty($post->parent_id)) {
                                        $topPosts[] = $post;
                                    } else {
                                        $commentsByParent[$post->parent_id][] = $post;
                                    }
                                }
                                ?>
                                <?php foreach ($topPosts as $post): ?>
                                    <?php
                                    $timeStr = date('M j, Y \a\t g:i A', strtotime($post->created_at));
                                    $likeCount = $post->like_count ?? 0;
                                    $userLiked = $post->user_reacted ?? false;
                                    ?>
                                    <article class="feed-post-card" id="post-card-<?= $post->id ?>">
                                        <div class="feed-post-header">
                                            <div class="feed-post-author">
                                                <div class="member-avatar"><?= strtoupper(substr($post->author_name ?? 'U', 0, 1)) ?></div>
                                                <div class="feed-post-author-info">
                                                    <div class="feed-post-author-name"><?= htmlspecialchars($post->author_name ?? 'Unknown User') ?></div>
                                                    <div class="feed-post-time"><?= $timeStr ?></div>
                                                </div>
                                            </div>
                                            <div class="feed-post-badges">
                                                <?php if (!empty($post->post_type)): ?>
                                                    <span class="post-type-badge <?= htmlspecialchars($post->post_type) ?>"><?= ucfirst(htmlspecialchars($post->post_type)) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($post->is_pinned)): ?>
                                                    <span class="pinned-badge">Pinned</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if (!empty($post->title)): ?>
                                            <h4 class="feed-post-title"><?= htmlspecialchars($post->title) ?></h4>
                                        <?php endif; ?>

                                        <div class="feed-post-content"><?= nl2br(htmlspecialchars($post->content)) ?></div>

                                        <?php if (!empty($post->link_url)): ?>
                                            <div class="feed-post-link">
                                                <a href="<?= htmlspecialchars($post->link_url) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($post->link_url) ?></a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($post->image_path)): ?>
                                            <div class="feed-post-image">
                                                <img src="<?= URLROOT ?>/<?= htmlspecialchars($post->image_path) ?>" alt="Post image">
                                            </div>
                                        <?php endif; ?>

                                        <div class="feed-post-footer">
                                            <span class="feed-post-meta">Post #<?= (int)$post->id ?></span>
                                            <div class="feed-post-actions">
                                                <!-- Like button -->
                                                <button
                                                    class="like-btn <?= $userLiked ? 'liked' : '' ?>"
                                                    id="like-btn-<?= $post->id ?>"
                                                    onclick="likePost(<?= $post->id ?>)"
                                                    title="<?= $userLiked ? 'Unlike' : 'Like' ?>"
                                                >
                                                    <svg class="like-icon" viewBox="0 0 24 24" fill="<?= $userLiked ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" width="16" height="16">
                                                        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/>
                                                        <path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                                                    </svg>
                                                    <span id="like-count-<?= $post->id ?>"><?= $likeCount ?></span>
                                                </button>

                                                <!-- Comments toggle -->
                                                <button class="comments-toggle-btn" onclick="toggleComments(<?= $post->id ?>)">
                                                    <i class="ph ph-chat-circle"></i>
                                                    <?= count($commentsByParent[$post->id] ?? []) ?> Comments
                                                </button>

                                                <!-- Report -->
                                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $post->user_id): ?>
                                                    <button class="report-btn-small report-content-btn"
                                                        data-content-type="post"
                                                        data-content-id="<?= $post->id ?>"
                                                        title="Report this post">Report
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Comments section -->
                                        <div class="comments-section" id="comments-<?= $post->id ?>" style="display:none;">
                                            <div class="comments-list">
                                                <?php if (!empty($commentsByParent[$post->id])): ?>
                                                    <?php foreach ($commentsByParent[$post->id] as $comment): ?>
                                                        <div class="comment-item">
                                                            <div class="member-avatar comment-avatar"><?= strtoupper(substr($comment->author_name ?? 'U', 0, 1)) ?></div>
                                                            <div class="comment-body">
                                                                <span class="comment-author"><?= htmlspecialchars($comment->author_name ?? 'Unknown') ?></span>
                                                                <span class="comment-text"><?= htmlspecialchars($comment->content) ?></span>
                                                                <span class="comment-time"><?= date('M j, Y g:i A', strtotime($comment->created_at)) ?></span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <p class="no-comments-yet">No comments yet.</p>
                                                <?php endif; ?>
                                            </div>

                                            <?php if ($data['community']->is_member): ?>
                                                <div class="add-comment-row">
                                                    <div class="member-avatar"><?= strtoupper(substr($data['user']['name'] ?? 'U', 0, 1)) ?></div>
                                                    <input
                                                        type="text"
                                                        class="comment-input"
                                                        placeholder="Write a comment… (Enter to send)"
                                                        onkeypress="handleComment(event, <?= $data['community']->id ?>, <?= $post->id ?>)"
                                                    >
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Sidebar -->
                    <aside class="detail-sidebar">
                        <div class="members-box">
                            <h3>Members (<?= count($data['members']) ?>)</h3>
                            <div class="members-list">
                                <?php if (empty($data['members'])): ?>
                                    <p class="no-members">No members yet.</p>
                                <?php else: ?>
                                    <?php foreach ($data['members'] as $member): ?>
                                        <div class="member">
                                            <div class="member-avatar">
                                                <?php if (!empty($member->profile_picture)): ?>
                                                    <img src="<?= URLROOT ?>/<?= htmlspecialchars($member->profile_picture) ?>" alt="<?= htmlspecialchars($member->name) ?>">
                                                <?php else: ?>
                                                    <?= strtoupper(substr($member->name, 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="member-info">
                                                <div class="member-name"><?= htmlspecialchars($member->name) ?></div>
                                                <div class="member-role"><?= ucfirst($member->role) ?></div>
                                            </div>
                                            <?php if ($member->user_id == $_SESSION['user_id']): ?>
                                                <span class="you-badge">You</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="about-box">
                            <h3>About This Community</h3>
                            <p><?= htmlspecialchars($data['community']->about ?? $data['community']->description) ?></p>
                            <?php if (!empty($data['community']->created_at)): ?>
                                <div class="community-meta">
                                    <small>Created <?= date('M j, Y', strtotime($data['community']->created_at)) ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </aside>
                </div>

            </div>
        </div>
    </div>
</main>

<script>
window.currentUserId = <?= $_SESSION['user_id'] ?? 1 ?>;
window.currentUserName = '<?= htmlspecialchars($data['user']['name'] ?? 'You', ENT_QUOTES) ?>';
window.urlRoot = '<?= URLROOT ?>';
</script>

<script src="<?= URLROOT ?>/assets/js/community_forum.js"></script>
<script src="<?= URLROOT ?>/assets/js/reporting.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>