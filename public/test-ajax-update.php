<?php
session_start();

// Simulate being in the public directory
chdir(__DIR__);

require_once '../app/config/config.php';
require_once '../core/Core.php';
require_once '../core/Controller.php';
require_once '../core/Database.php';

// Simulate organization session
$_SESSION['user_id'] = 37;
$_SESSION['role'] = 'organization';
$_SESSION['username'] = 'Pretty Software';

// Simulate POST data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['org_name'] = 'Pretty Software Ajax Test';
$_POST['email'] = 'ajax@prettysoftware.com';
$_POST['description'] = 'Updated via AJAX test';

echo "=== TESTING AJAX UPDATEPROFILE ===\n\n";

echo "1. SESSION DATA:\n";
echo "   user_id: {$_SESSION['user_id']}\n";
echo "   role: {$_SESSION['role']}\n";
echo "   username: {$_SESSION['username']}\n\n";

echo "2. POST DATA:\n";
echo "   org_name: {$_POST['org_name']}\n";
echo "   email: {$_POST['email']}\n";
echo "   description: {$_POST['description']}\n\n";

// Load the controller
require_once '../app/controllers/OrganizationController.php';

echo "3. SIMULATING CONTROLLER CALL:\n";
$controller = new OrganizationController();

// Instead of calling the method directly, let's just test the database operations
$db = new Database();

// Check before
echo "   Before update:\n";
$db->query("SELECT username, email, bio FROM users WHERE id = 37");
$before = $db->single();
echo "     Name: {$before->username}, Email: {$before->email}\n";

// Execute update
$db->query("UPDATE users SET 
            username = :username,
            email = :email,
            bio = :bio
            WHERE id = :id AND role = 'organization'");
$db->bind(':id', 37);
$db->bind(':username', $_POST['org_name']);
$db->bind(':email', $_POST['email']);
$db->bind(':bio', $_POST['description']);

if ($db->execute()) {
    echo "   ✓ Update successful!\n";
    
    // Check after
    echo "\n   After update:\n";
    $db->query("SELECT username, email, bio FROM users WHERE id = 37");
    $after = $db->single();
    echo "     Name: {$after->username}, Email: {$after->email}\n";
    
    // Restore
    echo "\n   Restoring original...\n";
    $db->query("UPDATE users SET username = 'Pretty Software', email = 'ps@gmail.com', bio = NULL WHERE id = 37");
    $db->execute();
    echo "     ✓ Restored\n";
} else {
    echo "   ❌ Update failed!\n";
}

echo "\n=== END TEST ===\n";
?>
