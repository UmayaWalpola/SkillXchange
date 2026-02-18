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
                    <div class="detail-header-icon"><?= htmlspecialchars($data['community']->icon) ?></div>
                    <div class="detail-header-info">
                        <h1><?= htmlspecialchars($data['community']->name) ?></h1>
                        <p><?= htmlspecialchars($data['community']->description) ?></p>
                        
                        <div class="community-stats">
                            <span>👥 <?= $data['community']->members ?> members</span>
                            <span>💬 <?= $data['community']->posts ?> posts</span>
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

                <!-- Main Content Grid -->
                <div class="detail-grid">
                    <!-- Chat/Messages Section -->
                    <div class="chat-container">
                        <h3>Community Chat</h3>
                        
                        <?php if (!$data['community']->is_member): ?>
                            <div class="not-joined-msg">
                                Join this community to participate in conversations
                            </div>
                        <?php endif; ?>
                        
                        <div class="messages" id="messagesList">
                            <?php if (empty($data['posts'])): ?>
                                <div class="no-messages">
                                    <p>No messages yet. Be the first to start the conversation!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($data['posts'] as $post): ?>
                                    <div class="msg <?= $post->user_id == $_SESSION['user_id'] ? 'own' : '' ?>">
                                        <?php if ($post->user_id != $_SESSION['user_id']): ?>
                                            <div class="msg-author"><?= htmlspecialchars($post->author_name) ?></div>
                                        <?php endif; ?>
                                        <div class="msg-text"><?= htmlspecialchars($post->content) ?></div>
                                        <div class="msg-time">
                                            <?php
                                                $time = strtotime($post->created_at);
                                                $diff = time() - $time;
                                                if ($diff < 3600) {
                                                    echo floor($diff / 60) . ' minutes ago';
                                                } elseif ($diff < 86400) {
                                                    echo floor($diff / 3600) . ' hours ago';
                                                } else {
                                                    echo date('M j, g:i A', $time);
                                                }
                                            ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($data['community']->is_member): ?>
                            <div class="input-row">
                                <input 
                                    type="text" 
                                    class="msg-input" 
                                    id="messageInput" 
                                    placeholder="Type your message..." 
                                    onkeypress="handleKeyPress(event, <?= $data['community']->id ?>)"
                                >
                                <button class="btn send-btn" onclick="sendMessage(<?= $data['community']->id ?>)">
                                    Send
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sidebar -->
                    <div class="detail-sidebar">
                        <!-- Members List -->
                        <div class="members-box">
                            <h3>Members (<?= count($data['members']) ?>)</h3>
                            <div class="members-list">
                                <?php if (empty($data['members'])): ?>
                                    <p class="no-members">No members yet</p>
                                <?php else: ?>
                                    <?php foreach ($data['members'] as $member): ?>
                                        <div class="member">
                                            <div class="member-avatar">
                                                <?php if (!empty($member->profile_picture)): ?>
                                                    <img src="<?= URLROOT ?>/<?= htmlspecialchars($member->profile_picture) ?>" alt="Avatar">
                                                <?php else: ?>
                                                    <?= strtoupper(substr($member->name, 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="member-name">
                                                    <?= htmlspecialchars($member->name) ?>
                                                    <?php if ($member->user_id == $_SESSION['user_id']): ?>
                                                        <span class="you-badge">(You)</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="member-role">
                                                    <?= ucfirst($member->role) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- About Section -->
                        <div class="about-box">
                            <h3>About</h3>
                            <p>
                                <?= htmlspecialchars($data['community']->about ?? $data['community']->description) ?>
                            </p>
                            
                            <?php if (!empty($data['community']->created_at)): ?>
                                <div class="community-meta">
                                    <small>Created <?= date('M j, Y', strtotime($data['community']->created_at)) ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
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

// Auto-scroll to bottom on load
document.addEventListener('DOMContentLoaded', function() {
    const messagesList = document.getElementById('messagesList');
    if (messagesList) {
        messagesList.scrollTop = messagesList.scrollHeight;
    }
});
</script>

<script src="<?= URLROOT ?>/assets/js/community_forum.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>