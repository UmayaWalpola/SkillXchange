
<?php
require_once '../app/config/config.php';
require_once '../core/Database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    echo "✅ Database connected successfully!<br>";
    echo "Connected to: " . DB_NAME;
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
}