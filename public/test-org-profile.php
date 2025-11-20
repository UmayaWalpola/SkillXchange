<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';

// Simulate organization session
$_SESSION['user_id'] = 37;
$_SESSION['role'] = 'organization';
$_SESSION['username'] = 'Pretty Software';

echo "=== TESTING ORGANIZATION PROFILE CRUD ===\n\n";

$db = new Database();

// 1. Check current organization data
echo "1. CURRENT ORGANIZATION DATA (User ID 37):\n";
$db->query("SELECT id, username, email, bio FROM users WHERE id = 37");
$org = $db->single();

if ($org) {
    echo "   ID: {$org->id}\n";
    echo "   Name: {$org->username}\n";
    echo "   Email: {$org->email}\n";
    echo "   Bio: {$org->bio}\n";
} else {
    echo "   Organization not found!\n";
}

// 2. Test UPDATE (simulate form submission)
echo "\n2. TESTING PROFILE UPDATE:\n";
echo "   Simulating: updateProfile() with new data...\n";

$db->query("UPDATE users SET 
            username = :username,
            email = :email,
            bio = :bio
            WHERE id = :id");
$db->bind(':id', 37);
$db->bind(':username', 'Pretty Software Updated');
$db->bind(':email', 'updated@prettysoftware.com');
$db->bind(':bio', 'We build amazing software solutions');

if ($db->execute()) {
    echo "   ✓ Update successful!\n";
} else {
    echo "   ❌ Update failed!\n";
}

// 3. Verify update
echo "\n3. VERIFY UPDATE:\n";
$db->query("SELECT id, username, email, bio FROM users WHERE id = 37");
$org = $db->single();

if ($org) {
    echo "   ID: {$org->id}\n";
    echo "   Name: {$org->username}\n";
    echo "   Email: {$org->email}\n";
    echo "   Bio: {$org->bio}\n";
}

// 4. Test getStats
echo "\n4. TESTING getStats CALCULATION:\n";

$db->query("SELECT COUNT(*) as total FROM projects WHERE organization_id = 37");
$totalProjects = $db->single()->total;

$db->query("SELECT COUNT(*) as total FROM projects WHERE organization_id = 37 AND status = 'active'");
$activeProjects = $db->single()->total;

$db->query("SELECT COUNT(*) as total FROM project_applications WHERE project_id IN (SELECT id FROM projects WHERE organization_id = 37)");
$totalApplications = $db->single()->total;

$db->query("SELECT COUNT(DISTINCT user_id) as total FROM project_members WHERE project_id IN (SELECT id FROM projects WHERE organization_id = 37)");
$totalMembers = $db->single()->total;

echo "   Total Projects: {$totalProjects}\n";
echo "   Active Projects: {$activeProjects}\n";
echo "   Total Applications: {$totalApplications}\n";
echo "   Total Members: {$totalMembers}\n";

// 5. Restore original data
echo "\n5. RESTORING ORIGINAL DATA:\n";
$db->query("UPDATE users SET 
            username = :username,
            email = :email,
            bio = :bio
            WHERE id = :id");
$db->bind(':id', 37);
$db->bind(':username', 'Pretty Software');
$db->bind(':email', 'ps@gmail.com');
$db->bind(':bio', null);

if ($db->execute()) {
    echo "   ✓ Original data restored!\n";
} else {
    echo "   ❌ Restore failed!\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
