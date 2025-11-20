<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="container org-dashboard">
        <div class="page-header">
            <div>
                <h1>Task History</h1>
                <p>Task: <strong><?= htmlspecialchars($task->title) ?></strong></p>
            </div>
            <a href="<?= URLROOT ?>/tasks/project/<?= $task->project_id ?>" class="btn btn-secondary">← Back to Tasks</a>
        </div>

        <?php if (empty($history)): ?>
            <p class="empty-state">No history recorded for this task yet.</p>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($history as $entry): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-time"><?= date('M d, Y H:i', strtotime($entry->timestamp)) ?></div>
                            <div class="timeline-title"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $entry->action))) ?></div>
                            <div class="timeline-meta">
                                <?php if ($entry->username): ?>
                                    by <strong><?= htmlspecialchars($entry->username) ?></strong>
                                <?php else: ?>
                                    by system
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
