<?php

// Path to the app root (important for includes/requires)
define('APPROOT', dirname(dirname(__FILE__)) . '/app');

// Base URL of your project
define('BASE_URL', 'http://localhost/SkillXchange/public');
define('URLROOT', 'http://localhost/SkillXchange/public');

// Site name (for reference in headers, titles, etc.)
define('SITENAME', 'SkillXchange');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'skillxchange');

// Start session for authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

