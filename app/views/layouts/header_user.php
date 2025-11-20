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
    if (!trigger || !menu) return;

    trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.classList.toggle('show');
    });

    document.addEventListener('click', function () {
        menu.classList.remove('show');
    });
});
</script>