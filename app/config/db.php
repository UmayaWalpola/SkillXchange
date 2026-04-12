<?php
if (file_exists(__DIR__ . '/db.local.php')) {
    require_once __DIR__ . '/db.local.php';
} else {
    define('DB_HOST', 'sql200.infinityfree.com');
    define('DB_USER', 'if0_41634304');
    define('DB_PASS', 'skillxchange123');
    define('DB_NAME', 'if0_41634304_skillxchange');
    define('DB_PORT', 3306);
}