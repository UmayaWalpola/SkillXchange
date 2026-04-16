<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/community_forum.css">

<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">
            <div class="community-detail">
                <!-- Back Button -->
                <button class="btn back-btn" onclick="window.location.href='<?= URLROOT ?>/userdashboard/communities'">
                    ← Back to Communities
                </button>

                <!-- Community Header -->
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
                                <button class="btn leave-btn" onclick="leaveCommunity(<?= $data['community']->id ?>)">
                                    Leave Community
                                </button>
                            <?php else: ?>
                                <button class="btn btn-primary" onclick="joinCommunity(<?= $data['community']->id ?>)">
                                    Join Community
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

              <div class="detail-grid">
    <!-- Left Side: Community Feed -->
    <section class="chat-container community-feed-section">
        <h3>Community Feed</h3>

        <?php if (!$data['community']->is_member): ?>
            <div class="not-joined-msg">
                Join this community to create posts and participate in discussions.
            </div>
        <?php endif; ?>

        <?php if ($data['community']->is_member): ?>
            <div class="create-post-card">
                <h4>Create a Post</h4>

                <div class="create-post-form">
                    <input
                        type="text"
                        id="postTitle"
                        class="post-input"
                        placeholder="Post title (optional)"
                    >

                    <select id="postType">
    <option value="discussion">Discussion</option>

    <?php if (in_array($data['community']->user_role, ['admin', 'moderator'])): ?>
        <option value="announcement">Announcement</option>
    <?php endif; ?>
</select>

                    <textarea
                        id="postContent"
                        class="post-textarea"
                        placeholder="Share something with the community..."
                        rows="5"
                    ></textarea>

                    <input
                        type="url"
                        id="postLink"
                        class="post-input"
                        placeholder="Add a link (optional)"
                    >

                    <div class="post-form-actions">
                        <button
                            type="button"
                            class="send-btn"
                            onclick="createCommunityPost(<?= $data['community']->id ?>)"
                        >
                            Post
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="community-feed" id="messagesList">
            <?php if (empty($data['posts'])): ?>
                <div class="no-messages">
                    <p>No posts yet. Be the first to start the discussion!</p>
                </div>
            <?php else: ?>
                <?php foreach ($data['posts'] as $post): ?>
                    <article class="feed-post-card">
                        <div class="feed-post-header">
                            <div class="feed-post-author">
                                <div class="member-avatar">
                                    <?= strtoupper(substr($post->author_name ?? 'U', 0, 1)) ?>
                                </div>

                                <div class="feed-post-author-info">
                                    <div class="feed-post-author-name">
                                        <?= htmlspecialchars($post->author_name ?? 'Unknown User') ?>
                                    </div>
                                    <div class="feed-post-time">
                                        <?php
                                            $time = strtotime($post->created_at);
                                            $diff = time() - $time;

                                            if ($diff < 3600) {
                                                echo floor($diff / 60) . ' minutes ago';
                                            } elseif ($diff < 86400) {
                                                echo floor($diff / 3600) . ' hours ago';
                                            } else {
                                                echo date('M j, Y \a\t g:i A', $time);
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="feed-post-badges">
                                <?php if (!empty($post->post_type)): ?>
                                    <span class="post-type-badge <?= htmlspecialchars($post->post_type) ?>">
                                        <?= ucfirst(htmlspecialchars($post->post_type)) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($post->is_pinned)): ?>
                                    <span class="pinned-badge">Pinned</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($post->title)): ?>
                            <h4 class="feed-post-title">
                                <?= htmlspecialchars($post->title) ?>
                            </h4>
                        <?php endif; ?>

                        <div class="feed-post-content">
                            <?= nl2br(htmlspecialchars($post->content)) ?>
                        </div>

                        <?php if (!empty($post->link_url)): ?>
                            <div class="feed-post-link">
                                <a href="<?= htmlspecialchars($post->link_url) ?>" target="_blank" rel="noopener noreferrer">
                                    <?= htmlspecialchars($post->link_url) ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($post->image_path)): ?>
                            <div class="feed-post-image">
                                <img
                                    src="<?= URLROOT ?>/<?= htmlspecialchars($post->image_path) ?>"
                                    alt="Post image"
                                >
                            </div>
                        <?php endif; ?>

                        <div class="feed-post-footer">
    <span class="feed-post-meta">Post #<?= (int)$post->id ?></span>

    <div class="feed-post-actions">
        <!--  LIKE BUTTON -->
        <button onclick="likePost(<?= $post->id ?>)"> Like</button>

        <!-- COMMENTS BUTTON -->
        <button onclick="toggleComments(<?= $post->id ?>)"> Comments</button>

        <!-- REPORT BUTTON -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $post->user_id): ?>
            <button class="report-btn-small report-content-btn"
                data-content-type="post"
                data-content-id="<?= $post->id ?>"
                title="Report this post">
                Report
            </button>
        <?php endif; ?>
    </div>
</div>

    <input 
        type="text" 
        placeholder="Write a comment..." 
        onkeypress="handleComment(event, <?= $data['community']->id ?>, <?= $post->id ?>)"    >
</div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Right Side: Sidebar -->
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
                                    <img
                                        src="<?= URLROOT ?>/<?= htmlspecialchars($member->profile_picture) ?>"
                                        alt="<?= htmlspecialchars($member->name) ?> avatar"
                                    >
                                <?php else: ?>
                                    <?= strtoupper(substr($member->name, 0, 1)) ?>
                                <?php endif; ?>
                            </div>

                            <div class="member-info">
                                <div class="member-name">
                                    <?= htmlspecialchars($member->name) ?>
                                </div>
                                <div class="member-role">
                                    <?= ucfirst($member->role) ?>
                                </div>
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

            <p>
                <?= htmlspecialchars($data['community']->about ?? $data['community']->description) ?>
            </p>

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
// Pass data to JavaScript
window.currentUserId = <?= $_SESSION['user_id'] ?? 1 ?>;
window.currentUserName = '<?= htmlspecialchars($data['user']['name'] ?? 'You', ENT_QUOTES) ?>';
window.urlRoot = '<?= URLROOT ?>';

console.log('Community Detail Page Loaded');
console.log('Community ID:', <?= $data['community']->id ?>);
console.log('Is Member:', <?= $data['community']->is_member ? 'true' : 'false' ?>);

</script>

<script src="<?= URLROOT ?>/assets/js/community_forum.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>