<?php
session_start();
$_SESSION['user_id'] = 37;
$_SESSION['role'] = 'organization';
$_SESSION['username'] = 'Pretty Software';

require_once "../app/config/config.php";
require_once "../core/Database.php";

echo "=== TESTING PROFILE DATA RETRIEVAL ===\n";

$db = new Database();
$db->query("SELECT * FROM users WHERE id = :id AND role = 'organization'");
$db->bind(':id', $_SESSION['user_id']);
$orgUser = $db->single();

if ($orgUser) {
    echo "✓ Organization profile loaded:\n";
    echo json_encode((array)$orgUser, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "✗ Failed to load organization profile\n";
}
?>
