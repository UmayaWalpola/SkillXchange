<?php
// Path to the app root (important for includes/requires)
define('APPROOT', dirname(dirname(__FILE__)));

// Check if running on localhost (XAMPP) or Live Server
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
$host = strtolower(trim(explode(':', $host)[0]));
$isLocalhost = in_array($host, ['localhost', '127.0.0.1'], true);

if ($isLocalhost) {
    // Localhost URLs
    define('BASE_URL', 'http://localhost/SkillXchange/public');
    define('URLROOT', 'http://localhost/SkillXchange/public');
} else {
    // Live Server URLs (InfinityFree)
    define('BASE_URL', 'http://skillxchange.great-site.net/public');
    define('URLROOT', 'http://skillxchange.great-site.net/public');
}

// Site name (for reference in headers, titles, etc.)
define('SITENAME', 'SkillXchange');

// Database configuration
// Localhost එකේදී db.local.php run වෙයි, Live එකේදී db.php run වෙයි
$localDbConfig = __DIR__ . '/db.local.php';
$liveDbConfig = __DIR__ . '/db.php';

if ($isLocalhost && file_exists($localDbConfig)) {
    require $localDbConfig;
} elseif (file_exists($liveDbConfig)) {
    require $liveDbConfig;
} elseif (file_exists($localDbConfig)) {
    require $localDbConfig;
} else {
    die('Database config file not found.');
}

// Start session for authentication
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
