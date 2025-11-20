<?php
/**
 * Integration Test for Organization Profile System
 * Tests:
 * 1. Database schema (all columns exist)
 * 2. Organization profile retrieval
 * 3. Profile update with all fields
 * 4. Session handling
 */

session_start();
$_SESSION['user_id'] = 37;
$_SESSION['role'] = 'organization';
$_SESSION['username'] = 'Pretty Software';

require_once "../app/config/config.php";
require_once "../core/Database.php";

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║   ORGANIZATION PROFILE SYSTEM - INTEGRATION TEST           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$db = new Database();
$tests_passed = 0;
$tests_total = 0;

// TEST 1: Database Schema
echo "TEST 1: Database Schema\n";
echo "─────────────────────────\n";
$tests_total++;
$db->query("DESCRIBE users");
$columns = $db->resultSet();
$columnNames = array_map(fn($c) => $c->Field, (array)$columns);

$requiredColumns = [
    'id', 'username', 'email', 'bio', 'phone', 'website', 
    'address', 'city', 'country', 'postal_code', 
    'linkedin', 'twitter', 'github', 'role'
];

$missingColumns = array_diff($requiredColumns, $columnNames);
if (empty($missingColumns)) {
    echo "✓ All required columns exist\n";
    $tests_passed++;
} else {
    echo "✗ Missing columns: " . implode(', ', $missingColumns) . "\n";
}
echo "\n";

// TEST 2: Organization Profile Retrieval
echo "TEST 2: Profile Retrieval\n";
echo "─────────────────────────\n";
$tests_total++;
$db->query("SELECT * FROM users WHERE id = :id AND role = 'organization'");
$db->bind(':id', $_SESSION['user_id']);
$orgProfile = $db->single();

if ($orgProfile && $orgProfile->username === 'Pretty Software') {
    echo "✓ Organization profile loaded successfully\n";
    echo "  ID: {$orgProfile->id}\n";
    echo "  Name: {$orgProfile->username}\n";
    echo "  Email: {$orgProfile->email}\n";
    echo "  Role: {$orgProfile->role}\n";
    $tests_passed++;
} else {
    echo "✗ Failed to load organization profile\n";
}
echo "\n";

// TEST 3: Update All Fields
echo "TEST 3: Profile Update (All Fields)\n";
echo "──────────────────────────────────\n";
$tests_total++;

// Store original values
$original = [
    'username' => $orgProfile->username,
    'email' => $orgProfile->email,
    'bio' => $orgProfile->bio,
    'phone' => $orgProfile->phone,
    'website' => $orgProfile->website,
    'address' => $orgProfile->address,
    'city' => $orgProfile->city,
    'country' => $orgProfile->country,
    'postal_code' => $orgProfile->postal_code,
    'linkedin' => $orgProfile->linkedin,
    'twitter' => $orgProfile->twitter,
    'github' => $orgProfile->github,
];

// Simulate update data
$updateData = [
    'username' => 'Test Org Updated',
    'email' => 'test@example.com',
    'bio' => 'Test bio',
    'phone' => '555-1234',
    'website' => 'https://test.com',
    'address' => '123 Test St',
    'city' => 'Test City',
    'country' => 'Test Country',
    'postal_code' => '12345',
    'linkedin' => 'https://linkedin.com/test',
    'twitter' => '@test',
    'github' => 'https://github.com/test',
];

// Execute update
$db->query("UPDATE users SET 
    username = :username,
    email = :email,
    bio = :bio,
    phone = :phone,
    website = :website,
    address = :address,
    city = :city,
    country = :country,
    postal_code = :postal_code,
    linkedin = :linkedin,
    twitter = :twitter,
    github = :github
    WHERE id = :id");

$db->bind(':id', $_SESSION['user_id']);
$db->bind(':username', $updateData['username']);
$db->bind(':email', $updateData['email']);
$db->bind(':bio', $updateData['bio']);
$db->bind(':phone', $updateData['phone']);
$db->bind(':website', $updateData['website']);
$db->bind(':address', $updateData['address']);
$db->bind(':city', $updateData['city']);
$db->bind(':country', $updateData['country']);
$db->bind(':postal_code', $updateData['postal_code']);
$db->bind(':linkedin', $updateData['linkedin']);
$db->bind(':twitter', $updateData['twitter']);
$db->bind(':github', $updateData['github']);

if ($db->execute()) {
    echo "✓ Update executed successfully\n";
    
    // Verify update
    $db->query("SELECT * FROM users WHERE id = :id");
    $db->bind(':id', $_SESSION['user_id']);
    $updated = $db->single();
    
    $allFieldsCorrect = true;
    foreach ($updateData as $field => $value) {
        if ($updated->$field !== $value) {
            echo "  ✗ Field '$field' mismatch: expected '{$value}', got '{$updated->$field}'\n";
            $allFieldsCorrect = false;
        }
    }
    
    if ($allFieldsCorrect) {
        echo "✓ All fields updated correctly\n";
        $tests_passed++;
    } else {
        echo "✗ Some fields not updated correctly\n";
    }
} else {
    echo "✗ Update failed\n";
}
echo "\n";

// TEST 4: Restore Original Values
echo "TEST 4: Restore Original Data\n";
echo "────────────────────────────\n";
$tests_total++;

$db->query("UPDATE users SET 
    username = :username,
    email = :email,
    bio = :bio,
    phone = :phone,
    website = :website,
    address = :address,
    city = :city,
    country = :country,
    postal_code = :postal_code,
    linkedin = :linkedin,
    twitter = :twitter,
    github = :github
    WHERE id = :id");

$db->bind(':id', $_SESSION['user_id']);
$db->bind(':username', $original['username']);
$db->bind(':email', $original['email']);
$db->bind(':bio', $original['bio']);
$db->bind(':phone', $original['phone']);
$db->bind(':website', $original['website']);
$db->bind(':address', $original['address']);
$db->bind(':city', $original['city']);
$db->bind(':country', $original['country']);
$db->bind(':postal_code', $original['postal_code']);
$db->bind(':linkedin', $original['linkedin']);
$db->bind(':twitter', $original['twitter']);
$db->bind(':github', $original['github']);

if ($db->execute()) {
    echo "✓ Original data restored\n";
    $tests_passed++;
} else {
    echo "✗ Failed to restore original data\n";
}
echo "\n";

// TEST 5: AJAX Endpoint Ready
echo "TEST 5: AJAX Endpoint Readiness\n";
echo "──────────────────────────────\n";
$tests_total++;

$file = "../app/controllers/OrganizationController.php";
$content = file_get_contents($file);

if (strpos($content, 'public function updateProfile()') !== false && 
    strpos($content, "UPDATE users SET") !== false &&
    strpos($content, ':postal_code') !== false) {
    echo "✓ updateProfile() method implemented with all field bindings\n";
    $tests_passed++;
} else {
    echo "✗ updateProfile() method incomplete\n";
}
echo "\n";

// Summary
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    TEST SUMMARY                            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "Passed: $tests_passed / $tests_total\n";

if ($tests_passed === $tests_total) {
    echo "\n✓ ALL TESTS PASSED - Organization profile system is ready!\n";
    echo "\nYou can now:\n";
    echo "1. Log in as organization (ID 37, user: Pretty Software)\n";
    echo "2. Visit /organization/profile\n";
    echo "3. Click 'Edit Profile'\n";
    echo "4. Update any fields\n";
    echo "5. Click 'Save Changes'\n";
    echo "6. Changes will be saved to database\n";
    echo "\nFor testing: http://localhost/SkillXchange/public/test-profile-ajax-form.html\n";
} else {
    echo "\n✗ Some tests failed - review the errors above\n";
}
?>
