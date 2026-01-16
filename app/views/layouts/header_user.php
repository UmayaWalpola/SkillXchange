<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillXchange - Dashboard</title>
    <link rel="stylesheet" href="<?= URLROOT; ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?= URLROOT; ?>/assets/css/profile.css">
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    <style>
        .header {
            position: fixed;
            top: 0;
            left: 250px !important;
            width: calc(100% - 250px) !important;
            height: 64px;
            background: #111827;
            display: flex;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }

        .nav-container {
            width: 100%;
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-image {
            height: 32px;
        }

        .auth-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .notif-bell {
            position: relative;
            margin-right: 8px;
        }

        .notif-bell-button {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            border: 1px solid rgba(156,163,175,0.7);
            background: #020617;
            color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(15,23,42,0.7);
            transition: background 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease;
        }

        .notif-bell-button:hover {
            background: #111827;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(15,23,42,0.9);
        }

        .notif-badge {
            position: absolute;
            top: -4px;
            right: -2px;
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            border-radius: 999px;
            background: #2563eb;
            color: #e5e7eb;
            font-size: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 0 2px #020617;
        }

        .notif-dropdown {
            position: absolute;
            top: 115%;
            right: 0;
            width: 320px;
            max-height: 420px;
            overflow: hidden;
            background: #020617;
            border-radius: 14px;
            border: 1px solid rgba(31,41,55,0.95);
            box-shadow: 0 16px 40px rgba(0,0,0,0.85);
            display: none;
            z-index: 1500;
        }

        .notif-dropdown.show {
            display: block;
        }

        .notif-header {
            padding: 10px 14px;
            border-bottom: 1px solid rgba(31,41,55,0.9);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .notif-header-title {
            color: #e5e7eb;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .notif-header-link {
            color: #60a5fa;
            font-size: 0.8rem;
            text-decoration: none;
        }

        .notif-list {
            max-height: 340px;
            overflow-y: auto;
        }

        .notif-item {
            padding: 10px 14px;
            display: flex;
            gap: 10px;
            border-bottom: 1px solid rgba(31,41,55,0.7);
            background: #020617;
        }

        .notif-item.unread {
            background: #0b1120;
        }

        .notif-icon {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .notif-icon.info { background: rgba(37,99,235,0.18); color: #60a5fa; }
        .notif-icon.success { background: rgba(22,163,74,0.2); color: #4ade80; }
        .notif-icon.danger { background: rgba(220,38,38,0.25); color: #fecaca; }
        .notif-icon.warning { background: rgba(234,179,8,0.25); color: #facc15; }

        .notif-body {
            flex: 1;
        }

        .notif-text {
            color: #e5e7eb;
            font-size: 0.83rem;
            margin-bottom: 4px;
        }

        .notif-meta {
            color: #6b7280;
            font-size: 0.75rem;
        }

        .notif-empty {
            padding: 16px;
            text-align: center;
            color: #6b7280;
            font-size: 0.85rem;
        }

        .user-dropdown {
            position: relative;
            display: flex;
            align-items: center;
        }

        .user-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 10px;
            background: #111827;
            border-radius: 999px;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 4px 10px rgba(0,0,0,0.35);
            transition: background 0.2s ease, box-shadow 0.2s ease, transform 0.1s ease;
        }

        .user-trigger:hover {
            background: #1f2937;
            box-shadow: 0 6px 16px rgba(0,0,0,0.45);
            transform: translateY(-1px);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            overflow: hidden;
            background: linear-gradient(135deg, #34d399, #10b981);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 700;
            font-size: 16px;
            flex-shrink: 0;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .user-name {
            color: #f9fafb;
            font-weight: 600;
            font-size: 0.95rem;
            max-width: 160px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            color: #9ca3af;
            font-size: 0.75rem;
            text-transform: capitalize;
        }

        .user-caret {
            color: #9ca3af;
            font-size: 0.8rem;
        }

        .user-menu {
            position: absolute;
            top: 110%;
            right: 0;
            min-width: 190px;
            background: #111827;
            border-radius: 10px;
            padding: 8px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.55);
            border: 1px solid rgba(55,65,81,0.9);
            display: none;
        }

        .user-menu.show {
            display: block;
        }

        .user-menu a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            text-decoration: none;
            color: #e5e7eb;
            font-size: 0.9rem;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .user-menu a:hover {
            background: #1f2937;
            color: #ffffff;
        }

        .user-menu a.logout {
            color: #fca5a5;
        }

        .user-menu a.logout:hover {
            background: #7f1d1d;
            color: #ffffff;
        }

        .guest-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-auth {
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid #4b5563;
            background: transparent;
            color: #e5e7eb;
            font-size: 0.9rem;
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        .btn-auth:hover {
            background: #e5e7eb;
            color: #111827;
            border-color: #e5e7eb;
        }

        .btn-auth-primary {
            background: #3b82f6;
            border-color: #3b82f6;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(59,130,246,0.45);
        }

        .btn-auth-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }

        @media (max-width: 768px) {
            .header {
                left: 0 !important;
                width: 100% !important;
            }
            .nav-container {
                padding: 0 12px;
            }
            .user-name {
                max-width: 110px;
            }
        }
    </style>
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

<header class="header">
    <nav class="nav-container">
        <div class="logo-section">
            <img src="<?= URLROOT; ?>/assets/images/logo-new.png" alt="SkillXchange Logo" class="logo-image">
        </div>

        <div class="auth-section">
            <?php if ($user): ?>
                <div class="notif-bell" id="sxNotifBell">
                    <button type="button" class="notif-bell-button" id="sxNotifTrigger">🔔</button>
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
                        <a href="<?= URLROOT ?>/users/userprofile">
                            Profile
                        </a>
                        <a href="<?= URLROOT ?>/users/userprofile">
                            Settings
                        </a>
                        <a href="<?= URLROOT ?>/auth/logout" class="logout">
                            Logout
                        </a>
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