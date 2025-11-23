<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">
            <div class="page-header">
                <h1>Notifications</h1>
                <form method="post" action="<?= URLROOT ?>/notifications/readAll">
                    <button type="submit" class="btn btn-primary">Mark all as read</button>
                </form>
            </div>

            <div class="cards-grid">
                <?php if (empty($data['notifications'])): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🔔</div>
                        <h3>No notifications yet</h3>
                        <p>You'll see important project and task updates here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($data['notifications'] as $n): ?>
                        <?php
                            $iconClass = 'info';
                            $iconSymbol = '🔧';
                            if ($n->type === 'application_accepted') { $iconClass = 'success'; $iconSymbol = '🎉'; }
                            elseif ($n->type === 'application_rejected') { $iconClass = 'danger'; $iconSymbol = '❌'; }
                            elseif ($n->type === 'project_invite') { $iconClass = 'info'; $iconSymbol = '📨'; }
                            elseif ($n->type === 'deadline_warning') { $iconClass = 'danger'; $iconSymbol = '⚠'; }
                            elseif ($n->type === 'deadline_due_today') { $iconClass = 'warning'; $iconSymbol = '📅'; }
                            elseif ($n->type === 'deadline_due_soon') { $iconClass = 'warning'; $iconSymbol = '⏳'; }
                            elseif ($n->type === 'task_assigned') { $iconClass = 'info'; $iconSymbol = '📌'; }
                            elseif ($n->type === 'task_update') { $iconClass = 'info'; $iconSymbol = '🔧'; }
                        ?>
                        <a href="<?= URLROOT ?>/notifications/read/<?= $n->id ?>" class="card notification-card <?= $n->is_read ? '' : 'unread' ?>" style="text-decoration:none;">
                            <div class="notification-icon <?= $iconClass ?>"><?= $iconSymbol ?></div>
                            <div class="notification-body">
                                <div class="notification-message"><?= htmlspecialchars($n->message) ?></div>
                                <div class="notification-meta">
                                    <?= date('M d, Y H:i', strtotime($n->created_at)) ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
.notification-card {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.notification-card.unread {
    border-left: 3px solid #2563eb;
    background: #0b1120;
}
.notification-icon {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.notification-icon.info { background: rgba(37,99,235,0.18); color: #60a5fa; }
.notification-icon.success { background: rgba(22,163,74,0.2); color: #4ade80; }
.notification-icon.danger { background: rgba(220,38,38,0.25); color: #fecaca; }
.notification-icon.warning { background: rgba(234,179,8,0.25); color: #facc15; }
.notification-body {
    flex: 1;
}
.notification-message {
    color: #e5e7eb;
    font-size: 0.9rem;
    margin-bottom: 4px;
}
.notification-meta {
    color: #6b7280;
    font-size: 0.8rem;
}
</style>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
