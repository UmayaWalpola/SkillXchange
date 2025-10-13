<?php require_once "../app/views/layouts/header_user.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">

<main class="site-main">

    <header class="community-header fade-in animate-delay-1">
        <div class="cover-image" style="background-image:url('<?= URLROOT ?>/assets/images/<?= $data['community']->cover_image ?>');"></div>
        <div class="community-info">
            <div class="avatar"><?= strtoupper(substr($data['community']->name, 0, 1)) ?></div>
            <div class="details">
                <h1><?= $data['community']->name ?></h1>
                <p><?= $data['community']->description ?></p>
                <div class="stats">
                    <span>👥 <?= $data['community']->members_count ?> members</span>
                    <span>💬 <?= count($data['posts']) ?> posts</span>
                    <span>📈 Active</span>
                </div>
            </div>
            <form method="POST">
                <button type="submit" name="join" class="btn btn-primary"><?= $data['joined'] ? 'Joined' : 'Join' ?></button>
            </form>
        </div>
    </header>

    <div class="create-post fade-in animate-delay-2">
        <div class="post-form">
            <div class="avatar"><?= strtoupper(substr($_SESSION['user_name'],0,1)) ?></div>
            <div class="form-content">
                <form method="POST">
                    <textarea name="content" placeholder="What's on your mind? Share with the community..." required></textarea>
                    <button type="submit" class="btn btn-primary">✈️ Post</button>
                </form>
            </div>
        </div>
    </div>

    <div id="posts-container" class="posts-feed fade-in animate-delay-3">
        <?php if(!empty($data['posts'])): ?>
            <?php foreach(array_reverse($data['posts']) as $post): ?>
                <div class="card post-card skill-card" data-skill="post">
                    <div class="card-body">
                        <p><?= htmlspecialchars($post->content) ?></p>
                        <small>by <?= $post->user_name ?> at <?= date('d M Y H:i', strtotime($post->created_at)) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No posts yet. Be the first to post!</p>
        <?php endif; ?>
    </div>

</main>

<script src="<?= URLROOT ?>/assets/js/main.js" defer></script>

<?php require_once "../app/views/layouts/footer.php"; ?>


