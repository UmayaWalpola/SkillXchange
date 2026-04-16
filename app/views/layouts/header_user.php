<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillXchange - Dashboard</title>
    <link rel="stylesheet" href="<?= URLROOT; ?>/assets/css/global.css">
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
<script>
    // Make URLROOT available to all client-side scripts
    window.URLROOT = '<?= URLROOT ?>';
</script>

<?php
if (!class_exists('Notification')) {
    require_once dirname(__DIR__, 2) . '/models/Notification.php';
}

$user = null;
if (isset($_SESSION['user_id'])) {
    // Minimal inline user fetch to avoid changing controllers
    try {
        $db = new Database();
        $db->query("SELECT id, username, email, profile_picture, role FROM users WHERE id = :id LIMIT 1");
        $db->bind(':id', $_SESSION['user_id']);
        $user = $db->single();
    } catch (Exception $e) {
        $user = null;
    }
}

$notifModel = null;
$notifUnreadCount = 0;
$notifLatest = [];
if ($user) {
    try {
        $notifModel = new Notification();
        $notifUnreadCount = $notifModel->getUnreadCount($user->id);
        $notifLatest = $notifModel->getUserNotifications($user->id, 10);
    } catch (Exception $e) {
        $notifUnreadCount = 0;
        $notifLatest = [];
    }
}

function sx_get_display_name($user) {
    if (!$user) return 'Guest';
    // Username is already a good display name for both org and individual
    return $user->username ?? 'User';
}

function sx_get_role_label($user) {
    if (!$user || empty($user->role)) return '';
    return str_replace('_', ' ', strtolower($user->role));
}
?>

<?php
$profileUrl = URLROOT . '/users/userprofile';
$settingsUrl = $profileUrl;

if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'organization':
            $profileUrl = URLROOT . '/organization/profile';
            $settingsUrl = URLROOT . '/organization/profile';
            break;

        case 'manager':
            $profileUrl = URLROOT . '/manager/dashboard';
            $settingsUrl = URLROOT . '/manager/dashboard';
            break;

        case 'quizmanager':
            $profileUrl = URLROOT . '/quizmanager/dashboard';
            $settingsUrl = URLROOT . '/quizmanager/dashboard';
            break;

        case 'communitymanager':
            $profileUrl = URLROOT . '/communitymanager/dashboard';
            $settingsUrl = URLROOT . '/communitymanager/dashboard';
            break;

        case 'admin':
            $profileUrl = URLROOT . '/admin/dashboard';
            $settingsUrl = URLROOT . '/admin/dashboard';
            break;

        default:
            $profileUrl = URLROOT . '/users/userprofile';
            $settingsUrl = URLROOT . '/users/userprofile';
            break;
    }
}
?>

<header class="header">
    <nav class="nav-container">
        <div class="logo-section">
            <img src="<?= URLROOT; ?>/assets/images/logo-new.png" alt="SkillXchange Logo" class="logo-image">
        </div>

        <div class="auth-section">
            <?php if ($user): ?>
                <div class="notif-bell" id="sxNotifBell">
                    <button type="button" class="notif-bell-button" id="sxNotifTrigger"><i class="ph ph-bell"></i></button>
                    <?php if ($notifUnreadCount > 0): ?>
                        <div class="notif-badge"><?= $notifUnreadCount > 9 ? '9+' : $notifUnreadCount; ?></div>
                    <?php endif; ?>
                    <div class="notif-dropdown" id="sxNotifDropdown">
                        <div class="notif-header">
                            <span class="notif-header-title">Notifications</span>
                            <a href="<?= URLROOT ?>/notifications" class="notif-header-link">View all</a>
                        </div>
                        <div class="notif-list">
                            <?php if (!empty($notifLatest)): ?>
                                <?php foreach ($notifLatest as $n): ?>
                                    <?php
                                        $iconClass = 'info';
                                        $iconSymbol = '<i class="ph ph-wrench"></i>';
                                        if ($n->type === 'application_accepted') { $iconClass = 'success'; $iconSymbol = '<i class="ph ph-confetti"></i>'; }
                                        elseif ($n->type === 'application_rejected') { $iconClass = 'danger'; $iconSymbol = '<i class="ph ph-x-circle"></i>'; }
                                        elseif ($n->type === 'project_invite') { $iconClass = 'info'; $iconSymbol = '<i class="ph ph-envelope"></i>'; }
                                        elseif ($n->type === 'deadline_warning') { $iconClass = 'danger'; $iconSymbol = '<i class="ph ph-warning"></i>'; }
                                        elseif ($n->type === 'deadline_due_today') { $iconClass = 'warning'; $iconSymbol = '<i class="ph ph-calendar"></i>'; }
                                        elseif ($n->type === 'deadline_due_soon') { $iconClass = 'warning'; $iconSymbol = '<i class="ph ph-hourglass"></i>'; }
                                        elseif ($n->type === 'task_assigned') { $iconClass = 'info'; $iconSymbol = '<i class="ph ph-push-pin"></i>'; }
                                        elseif ($n->type === 'task_update') { $iconClass = 'info'; $iconSymbol = '<i class="ph ph-wrench"></i>'; }
                                        elseif ($n->type === 'system_warning') { $iconClass = 'warning'; $iconSymbol = '<i class="ph ph-warning"></i>'; }
                                        elseif ($n->type === 'account_ban') { $iconClass = 'danger'; $iconSymbol = '<i class="ph ph-x-circle"></i>'; }
                                    ?>
                                    <a href="<?= URLROOT ?>/notifications/read/<?= $n->id ?>" class="notif-item <?= $n->is_read ? '' : 'unread' ?>" style="text-decoration:none;">
                                        <div class="notif-icon <?= $iconClass ?>"><?= $iconSymbol ?></div>
                                        <div class="notif-body">
                                            <div class="notif-text"><?= htmlspecialchars($n->message) ?></div>
                                            <div class="notif-meta"><?= date('M d, Y H:i', strtotime($n->created_at)) ?></div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="notif-empty">No notifications yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="user-dropdown" id="sxUserDropdown">
                    <button type="button" class="user-trigger" id="sxUserTrigger">
                        <div class="user-avatar">
                            <?php if (!empty($user->profile_picture)): ?>
                                <img src="<?= URLROOT . '/' . ltrim($user->profile_picture, '/') ?>" alt="Avatar">
                            <?php else: ?>
                                <?= strtoupper(substr(sx_get_display_name($user), 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars(sx_get_display_name($user)) ?></span>
                            <?php if (sx_get_role_label($user)): ?>
                                <span class="user-role"><?= htmlspecialchars(sx_get_role_label($user)) ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="user-caret">▾</span>
                    </button>
                    <div class="user-menu" id="sxUserMenu">
                       <a href="<?= $profileUrl ?>">Profile</a>
                       <a href="<?= $settingsUrl ?>">Settings</a>
                       <a href="<?= URLROOT ?>/auth/logout" class="logout">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="guest-actions">
                    <a href="<?= URLROOT ?>/auth/signin" class="btn-auth">Login</a>
                    <a href="<?= URLROOT ?>/auth/signup" class="btn-auth btn-auth-primary">Sign Up</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('sxUserTrigger');
    var menu = document.getElementById('sxUserMenu');
    var notifTrigger = document.getElementById('sxNotifTrigger');
    var notifDropdown = document.getElementById('sxNotifDropdown');

    if (trigger && menu) {
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            menu.classList.toggle('show');
            if (notifDropdown) notifDropdown.classList.remove('show');
        });
    }

    if (notifTrigger && notifDropdown) {
        notifTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
            if (menu) menu.classList.remove('show');
        });
    }

    document.addEventListener('click', function () {
        if (menu) menu.classList.remove('show');
        if (notifDropdown) notifDropdown.classList.remove('show');
    });
});
</script>