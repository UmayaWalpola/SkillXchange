<?php require_once "../app/views/layouts/header_user.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT; ?>/assets/css/homepage.css">

<div class="site-main">
    <section class="hero fade-in">
        <div class="container hero-inner">
            <h1>SkillXchange Community</h1>
            <p>Discover communities tailored to your interests and join discussions on Web Development, UI Design, and more.</p>
        </div>
    </section>

    <section class="container section fade-in animate-delay-1">
        <div class="section-head">
            <h2>Featured Communities</h2>
        </div>
        <div class="grid">
            <?php foreach($communities as $community): ?>
            <article class="card skill-card" data-skill="<?= $community['name'] ?>">
                <div class="media">
                    <img src="<?= URLROOT ?>/<?= $community['image'] ?>" alt="<?= $community['name'] ?>">
                </div>
                <div class="card-body">
                    <h3><?= $community['name'] ?></h3>
                    <p class="muted"><?= $community['description'] ?></p>
                    <div class="card-foot">
                        <p class="members muted"><?= $community['members'] ?> Members</p>
                        <button class="btn btn-primary">Join</button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script src="<?= URLROOT ?>/assets/js/main.js" defer></script>

<?php require_once "../app/views/layouts/footer.php"; ?>
