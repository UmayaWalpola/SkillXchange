<?php

// Path to the app root (important for includes/requires)
define('APPROOT', dirname(dirname(__FILE__)) . '/app');

// Base URL of your project
define('BASE_URL', 'http://localhost/SkillXchange/public');
define('URLROOT', 'http://localhost/SkillXchange/public');
define('SYSTEM_REWARD_SENDER_ID', 1);
define('APP_TIMEZONE', 'Asia/Colombo');
define('DB_TIMEZONE_OFFSET', '+05:30');

// Site name (for reference in headers, titles, etc.)
define('SITENAME', 'SkillXchange');

// Database configuration
if (file_exists(__DIR__ . '/db.local.php')) {
    require __DIR__ . '/db.local.php';
} else {
    require __DIR__ . '/db.php';
}


// Start session for authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set(APP_TIMEZONE);

// Mail configuration (legacy constant style)
// NOTE: Do not commit real credentials. Put local credentials in app/config/mail.local.php (gitignored).
$mailLocalPath = __DIR__ . '/mail.local.php';
if (file_exists($mailLocalPath)) {
    require $mailLocalPath;
}

if (!defined('MAIL_HOST')) {
    define('MAIL_HOST', 'sandbox.smtp.mailtrap.io');
}
if (!defined('MAIL_PORT')) {
    define('MAIL_PORT', 2525);
}
if (!defined('MAIL_USERNAME')) {
    define('MAIL_USERNAME', '');
}
if (!defined('MAIL_PASSWORD')) {
    define('MAIL_PASSWORD', '');
}
if (!defined('MAIL_FROM')) {
    define('MAIL_FROM', 'noreply@skillxchange.com');
}
if (!defined('MAIL_NAME')) {
    define('MAIL_NAME', 'SkillXchange');
}
