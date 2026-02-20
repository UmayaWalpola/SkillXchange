<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/community_forum.css">

<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">
            <div class="communities-page">
                <div class="page-header">
                    <h1>Join Our Communities</h1>
                    <p>Connect with like-minded people and share your skills</p>
                </div>
                
                <div class="communities-grid">
                    <?php if (empty($data['communities'])): ?>
                        <div class="no-communities">
                            <p>No communities found.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($data['communities'] as $community): ?>
                            <div class="community-card">
                                <div class="community-icon"><?= strtoupper(substr($community->name, 0, 1)) ?></div>
                                <h3><?= htmlspecialchars($community->name ?? '') ?></h3>
                                <p><?= htmlspecialchars($community->description ?? '') ?></p>
                                
                                <div class="community-stats">
                                    <span><i class="ph ph-users"></i> <?= $community->members ?? 0 ?> members</span>
                                    <span><i class="ph ph-chat-circle-dots"></i> <?= $community->posts ?? 0 ?> posts</span>
                                </div>
                                
                                <?php if ($community->is_member): ?>
                                    <span class="joined-badge">✓ Joined</span>
                                <?php endif; ?>
                                
                                <div class="btn-group">
                                    <?php if ($community->is_member): ?>
                                        <button class="btn leave-btn" onclick="leaveCommunity(<?= $community->id ?>)">
                                            Leave
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-primary" onclick="joinCommunity(<?= $community->id ?>)">
                                            Join
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="btn view-btn" onclick="viewCommunity(<?= $community->id ?>)">
                                        View
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Pass PHP data to JavaScript
window.communitiesData = <?= json_encode($data['communities']) ?>;
window.currentUserId = <?= $_SESSION['user_id'] ?? 1 ?>;
window.currentUserName = '<?= htmlspecialchars($data['user']['name'] ?? 'You', ENT_QUOTES) ?>';
window.urlRoot = '<?= URLROOT ?>'; // CRITICAL!

console.log('Communities loaded:', window.communitiesData?.length || 0);
console.log('URL Root:', window.urlRoot);
</script>

<script src="<?= URLROOT ?>/assets/js/community_forum.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>