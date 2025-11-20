<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';

// Simulate organization session
$_SESSION['user_id'] = 37;
$_SESSION['role'] = 'organization';
$_SESSION['username'] = 'Pretty Software';

echo "=== TESTING UPDATEPROFILE ENDPOINT ===\n\n";

// Simulate POST request data
$_POST['org_name'] = 'Pretty Software Test';
$_POST['email'] = 'test@prettysoftware.com';
$_POST['phone'] = '1234567890';
$_POST['website'] = 'https://prettysoftware.com';
$_POST['description'] = 'Test description';
$_POST['address'] = '123 Main St';
$_POST['city'] = 'New York';
$_POST['country'] = 'USA';
$_POST['postal_code'] = '10001';
$_POST['linkedin'] = 'https://linkedin.com/company/prettysoftware';
$_POST['twitter'] = 'https://twitter.com/prettysoftware';
$_POST['github'] = 'https://github.com/prettysoftware';

echo "1. SIMULATED POST DATA:\n";
foreach ($_POST as $key => $value) {
    echo "   {$key}: {$value}\n";
}

echo "\n2. ATTEMPTING UPDATE:\n";

$db = new Database();

// Collect form data (same as controller)
$data = [
    'id' => $_SESSION['user_id'],
    'org_name' => isset($_POST['org_name']) ? trim($_POST['org_name']) : '',
    'email' => isset($_POST['email']) ? trim($_POST['email']) : '',
    'phone' => isset($_POST['phone']) ? trim($_POST['phone']) : '',
    'website' => isset($_POST['website']) ? trim($_POST['website']) : '',
    'description' => isset($_POST['description']) ? trim($_POST['description']) : '',
    'address' => isset($_POST['address']) ? trim($_POST['address']) : '',
    'city' => isset($_POST['city']) ? trim($_POST['city']) : '',
    'country' => isset($_POST['country']) ? trim($_POST['country']) : '',
    'postal_code' => isset($_POST['postal_code']) ? trim($_POST['postal_code']) : '',
    'linkedin' => isset($_POST['linkedin']) ? trim($_POST['linkedin']) : '',
    'twitter' => isset($_POST['twitter']) ? trim($_POST['twitter']) : '',
    'github' => isset($_POST['github']) ? trim($_POST['github']) : ''
];

echo "   Data collected: " . count($data) . " fields\n";

// Update user profile
$db->query("UPDATE users SET 
            username = :username,
            email = :email,
            bio = :bio
            WHERE id = :id");
$db->bind(':id', $data['id']);
$db->bind(':username', $data['org_name']);
$db->bind(':email', $data['email']);
$db->bind(':bio', $data['description']);

echo "   Executing query...\n";
if ($db->execute()) {
    echo "   ✓ Update successful!\n";
    $_SESSION['username'] = $data['org_name'];
    
    echo "\n3. VERIFY UPDATE:\n";
    $db->query("SELECT id, username, email, bio FROM users WHERE id = 37");
    $org = $db->single();
    echo "   Name: {$org->username}\n";
    echo "   Email: {$org->email}\n";
    echo "   Bio: {$org->bio}\n";
    
    // Restore original
    echo "\n4. RESTORING ORIGINAL:\n";
    $db->query("UPDATE users SET username = 'Pretty Software', email = 'ps@gmail.com', bio = NULL WHERE id = 37");
    $db->execute();
    echo "   ✓ Original restored\n";
} else {
    echo "   ❌ Update failed!\n";
    echo "   Error: " . $db->getLastError() ?? "Unknown error\n";
}

echo "\n=== END TEST ===\n";
?>
