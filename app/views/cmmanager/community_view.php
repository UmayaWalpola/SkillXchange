<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/commanagersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/dashboard.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/community_forum.css">

<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">
            <div class="community-detail">

                <!-- Back Button -->
                <button class="btn back-btn" onclick="window.location.href='<?= URLROOT ?>/communityAdmin'">
                    ← Back to Dashboard
                </button>

                <!-- Community Header -->
                <div class="detail-header">
                    <div class="detail-header-icon"><?= strtoupper(substr($data['community']->name ?? 'C', 0, 1)) ?></div>
                    <div class="detail-header-info">
                        <h1><?= htmlspecialchars($data['community']->name ?? '') ?></h1>
                        <p><?= htmlspecialchars($data['community']->description ?? '') ?></p>

                        <div class="community-stats">
                            <span><?= $data['community']->member_count ?? 0 ?> members</span>
                            <span><?= isset($data['posts']) ? count($data['posts']) : 0 ?> posts</span>
                        </div>
                    </div>
                </div>

                <div class="detail-grid">

                    <!-- Left: Feed (read-only) -->
                    <section class="chat-container community-feed-section">
                        <h3>Community Feed</h3>

                        <div class="community-feed" id="messagesList">
                            <?php if (empty($data['posts'])): ?>
                                <div class="no-messages">
                                    <p>No posts in this community yet.</p>
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
                                                        <?= date('M j, Y \a\t g:i A', strtotime($post->created_at)) ?>
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
                                                <img src="<?= URLROOT ?>/<?= htmlspecialchars($post->image_path) ?>" alt="Post image">
                                            </div>
                                        <?php endif; ?>

                                        <div class="feed-post-footer">
                                            <div class="feed-post-actions">
                                                <form method="POST" action="<?= URLROOT ?>/communityAdmin/removePost" onsubmit="return confirm('Remove this post from the community feed?');" class="feed-post-action-form">
                                                    <input type="hidden" name="community_id" value="<?= (int)$data['community']->id ?>">
                                                    <input type="hidden" name="post_id" value="<?= (int)$post->id ?>">
                                                    <button type="submit" class="btn-outline community-post-action-btn" style="border-color:#fecaca; color:#b91c1c;">Remove Post</button>
                                                </form>

                                                <?php if (!empty($post->user_id)): ?>
                                                    <button type="button"
                                                            class="btn-outline community-post-action-btn"
                                                            style="border-color:#fcd34d; color:#92400e;"
                                                            onclick="toggleWarnForm(<?= (int)$post->id ?>)">
                                                        Warn Author
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div id="warn-form-<?= (int)$post->id ?>" class="inline-form-panel" style="display:none; margin-top:12px; border:1px solid #f3d28b; background:#fffaf0;">
                                            <form method="POST" action="<?= URLROOT ?>/communityAdmin/warnPostAuthor">
                                                <input type="hidden" name="community_id" value="<?= (int)$data['community']->id ?>">
                                                <input type="hidden" name="post_id" value="<?= (int)$post->id ?>">
                                                <div style="display:grid; gap:10px;">
                                                    <label for="warn-reason-<?= (int)$post->id ?>" style="font-weight:600;">Warning reason</label>
                                                    <textarea id="warn-reason-<?= (int)$post->id ?>" name="reason" rows="3" required
                                                              placeholder="Explain why this post violates community rules..."
                                                              style="width:100%; padding:12px 14px; border:1px solid #ead7ad; border-radius:10px; resize:vertical;"></textarea>
                                                    <div style="display:flex; gap:10px; justify-content:flex-end;">
                                                        <button type="button" class="btn-cancel" onclick="toggleWarnForm(<?= (int)$post->id ?>)">Cancel</button>
                                                        <button type="submit" class="btn-primary">Send Warning</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Right: Sidebar -->
                    <aside class="detail-sidebar">
                        <div class="about-box">
                            <h3>Manager Actions</h3>
                            <p style="margin-bottom:14px;">Post official announcements for this community and keep important updates pinned in the feed.</p>
                            <form method="POST" action="<?= URLROOT ?>/communityAdmin/postAnnouncement/<?= (int)$data['community']->id ?>">
                                <div style="display:grid; gap:12px;">
                                    <div>
                                        <label for="announcement-title" style="display:block; font-weight:600; margin-bottom:6px;">Announcement Title</label>
                                        <input id="announcement-title" type="text" name="title" required placeholder="Enter announcement title"
                                               style="width:100%; padding:12px 14px; border:1px solid #d7e3eb; border-radius:10px;">
                                    </div>
                                    <div>
                                        <label for="announcement-content" style="display:block; font-weight:600; margin-bottom:6px;">Announcement Content</label>
                                        <textarea id="announcement-content" name="content" rows="4" required placeholder="Write the announcement for this community..."
                                                  style="width:100%; padding:12px 14px; border:1px solid #d7e3eb; border-radius:10px; resize:vertical;"></textarea>
                                    </div>
                                    <div style="display:flex; justify-content:flex-end;">
                                        <button type="submit" class="btn-primary">Post Announcement</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="members-box">
                            <h3>Members (<?= count($data['members'] ?? []) ?>)</h3>
                            <div class="members-list">
                                <?php if (empty($data['members'])): ?>
                                    <p class="no-members">No members yet.</p>
                                <?php else: ?>
                                    <?php foreach ($data['members'] as $member): ?>
                                        <div class="member">
                                            <div class="member-avatar">
                                                <?php if (!empty($member->profile_picture)): ?>
                                                    <img src="<?= URLROOT ?>/<?= htmlspecialchars($member->profile_picture) ?>" alt="avatar">
                                                <?php else: ?>
                                                    <?= strtoupper(substr($member->name, 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="member-info">
                                                <div class="member-name"><?= htmlspecialchars($member->name) ?></div>
                                                <div class="member-role"><?= ucfirst($member->role) ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="about-box">
                            <h3>About This Community</h3>
                            <p><?= htmlspecialchars($data['community']->description ?? '') ?></p>
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
function toggleWarnForm(postId) {
    const form = document.getElementById('warn-form-' + postId);
    if (!form) return;
    form.style.display = form.style.display === 'none' || form.style.display === '' ? 'block' : 'none';
}
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
