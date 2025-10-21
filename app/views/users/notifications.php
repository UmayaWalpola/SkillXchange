<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/notifications.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        
        <div class="notifications-page">
            <div class="page-header">
                <h1>Notifications</h1>
                <p>Stay updated with your latest activities</p>
            </div>

            <div class="notifications-actions">
                <button class="btn btn-primary" onclick="markAllAsRead()">Mark all as read</button>
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterNotifications('all', this)">All</button>
                    <button class="filter-btn" onclick="filterNotifications('unread', this)">Unread</button>
                    <button class="filter-btn" onclick="filterNotifications('read', this)">Read</button>
                </div>
            </div>

            <div class="notifications-list">
                <?php if (!empty($data['notifications'])): ?>
                    <?php foreach ($data['notifications'] as $notification): ?>
                        <div class="notification-item <?= $notification['read'] ? 'read' : 'unread'; ?>" data-status="<?= $notification['read'] ? 'read' : 'unread'; ?>">
                            <div class="notification-icon <?= $notification['type']; ?>">
                                <?= $notification['icon']; ?>
                            </div>
                            <div class="notification-content">
                                <h3><?= htmlspecialchars($notification['title']); ?></h3>
                                <p><?= htmlspecialchars($notification['message']); ?></p>
                                <span class="notification-time"><?= htmlspecialchars($notification['time']); ?></span>
                            </div>
                            <?php if (!$notification['read']): ?>
                                <div class="unread-dot"></div>
                            <?php endif; ?>
                            <button class="delete-btn" onclick="deleteNotification(this)" title="Delete">
                                ✕
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-notifications">
                        <div class="empty-icon">🔔</div>
                        <h2>No notifications yet</h2>
                        <p>When you get notifications, they'll show up here</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
</main>

<script src="<?= URLROOT ?>/assets/js/notifications.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>