<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/community_forum.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        
        <!-- Communities List Page -->
        <div id="communitiesListPage" class="communities-page">
            <div class="page-header">
                <h1>Join Our Communities</h1>
                <p>Connect with like-minded people and share your skills</p>
            </div>
            
            <div class="communities-grid">
                <?php foreach ($data['communities'] as $community): ?>
                    <div class="community-card" data-community-id="<?= $community['id']; ?>">
                        <div class="community-icon"><?= $community['icon']; ?></div>
                        <span class="joined-badge badge badge-success hide-element">✓ Joined</span>
                        <h3><?= htmlspecialchars($community['name']); ?></h3>
                        <p><?= htmlspecialchars($community['description']); ?></p>
                        <div class="community-stats">
                            <span>👥 <?= $community['members']; ?> members</span>
                            <span>💬 <?= $community['totalPosts']; ?> posts</span>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-primary join-btn" onclick="joinCommunity(<?= $community['id']; ?>)">
                                Join
                            </button>
                            <button class="btn leave-btn hide-element" onclick="leaveCommunity(<?= $community['id']; ?>)">
                                Leave
                            </button>
                            <button class="btn view-btn" onclick='viewCommunity(<?= json_encode($community); ?>)'>
                                View
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Community Detail Page -->
        <div id="communityDetailPage" class="community-detail hide-element">
            <button class="btn back-btn" onclick="goBack()">← Back to Communities</button>
            <div id="communityDetailContent"></div>
        </div>

    </div>
</div>
</main>

<script>
// Pass PHP data to JavaScript
window.communitiesData = <?= json_encode($data['communities']); ?>;
window.currentUserId = <?= $_SESSION['user_id'] ?? 1; ?>;
window.currentUserName = '<?= $data['user']['name'] ?? 'You'; ?>';
</script>

<script src="<?= URLROOT ?>/assets/js/community_forum.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>