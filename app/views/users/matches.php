<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/matches.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        
        <div class="matches-page">
            <div class="page-header">
                <h1>Your Matches</h1>
                <p>Connect with people who can teach you or learn from your expertise</p>
            </div>

            <div class="matches-grid">
                <!-- Teach Them Column -->
                <div class="matches-column">
                    <h2 class="column-title">
                        <span class="title-icon"></span>
                        Teach Them
                    </h2>
                    <div class="matches-list">
                        <?php if (!empty($data['teachMatches'])): ?>
                            <?php foreach ($data['teachMatches'] as $match): ?>
                                <div class="match-card" onclick="viewProfile(<?= $match['id']; ?>)">
                                    <div class="match-avatar"><?= strtoupper(substr($match['name'], 0, 2)); ?></div>
                                    <div class="match-info">
                                        <h3 class="match-name"><?= htmlspecialchars($match['name']); ?></h3>
                                        <p class="match-skill"><?= htmlspecialchars($match['skill']); ?></p>
                                    </div>
                                    <button class="btn-connect" onclick="event.stopPropagation(); connectWithUser(<?= $match['id']; ?>, '<?= htmlspecialchars($match['name']); ?>')">
                                        Connect
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-matches">
                                <div class="empty-icon">🔍</div>
                                <p>No matches found to teach at the moment.</p>
                                <small>Check back later for new matches!</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Learn From Them Column -->
                <div class="matches-column">
                    <h2 class="column-title">
                        <span class="title-icon"></span>
                        Learn From Them
                    </h2>
                    <div class="matches-list">
                        <?php if (!empty($data['learnMatches'])): ?>
                            <?php foreach ($data['learnMatches'] as $match): ?>
                                <div class="match-card" onclick="viewProfile(<?= $match['id']; ?>)">
                                    <div class="match-avatar"><?= strtoupper(substr($match['name'], 0, 2)); ?></div>
                                    <div class="match-info">
                                        <h3 class="match-name"><?= htmlspecialchars($match['name']); ?></h3>
                                        <p class="match-skill"><?= htmlspecialchars($match['skill']); ?></p>
                                    </div>
                                    <button class="btn-connect" onclick="event.stopPropagation(); connectWithUser(<?= $match['id']; ?>, '<?= htmlspecialchars($match['name']); ?>')">
                                        Connect
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-matches">
                                <div class="empty-icon"></div>
                                <p>No matches found to learn from at the moment.</p>
                                <small>Check back later for new matches!</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</main>

<script src="<?= URLROOT ?>/assets/js/matches.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>