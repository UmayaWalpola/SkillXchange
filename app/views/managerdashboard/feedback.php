<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/managersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/dashboard.css">

<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>Platform Feedback</h1>
                    <p>View feedback submitted by users</p>
                </div>
            </div>

            <!-- Feedback Cards -->
            <div class="feedback-container">
                <?php if (!empty($data['feedbacks'])): ?>
                    <?php foreach ($data['feedbacks'] as $feedback): ?>
                        <div class="feedback-card">

                            <!-- User Info -->
                            <div class="feedback-header">
                                <div class="feedback-user-info">
                                    <div class="feedback-avatar">
                                        <?= strtoupper(substr($feedback->user_name, 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h4 class="feedback-user-name"><?= htmlspecialchars($feedback->user_name) ?></h4>
                                        <p class="feedback-user-email"><?= htmlspecialchars($feedback->user_email) ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Subject and Message -->
                            <h3 class="feedback-subject"><?= htmlspecialchars($feedback->subject) ?></h3>
                            <p class="feedback-message"><?= nl2br(htmlspecialchars($feedback->message)) ?></p>

                            <!-- Date -->
                            <div class="feedback-footer">
                                <span class="feedback-date">
                                    <?= date('M d, Y', strtotime($feedback->created_at)) ?>
                                </span>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="section-card">
                        <p class="no-data">No feedback received yet</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>