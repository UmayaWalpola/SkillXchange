<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../app/models/Project.php';

// Simulate logged in user
$_SESSION['user_id'] = 41;
$_SESSION['role'] = 'individual';

// Test application to Kithsara project
$db = new Database();
$projectModel = new Project();

echo "TEST: Applying to Kithsara Project\n";
echo str_repeat("=", 60) . "\n\n";

// First, check if application already exists
$db->query("SELECT id FROM project_applications WHERE project_id = 13 AND user_id = 41");
$existing = $db->single();

if ($existing) {
    echo "❌ Application already exists (ID: {$existing->id})\n";
    echo "Cleaning up...\n";
    $db->query("DELETE FROM project_applications WHERE id = :id");
    $db->bind(':id', $existing->id);
    $db->execute();
    echo "✓ Old application deleted\n\n";
}

// Now try to create new application
echo "Attempting to create new application...\n";
$result = $projectModel->applyToProjectAdvanced(
    13, // project ID
    41, // user ID
    'I have strong PHP and MySQL experience',
    'PHP, JavaScript, MySQL, HTML/CSS',
    'Backend development and database design',
    '20-30',
    '3-6',
    'Interested in real-world project experience',
    'https://github.com/devinda'
);

echo "Result: " . ($result ? "✓ SUCCESS" : "❌ FAILED") . "\n\n";

// Verify application exists now
$db->query("SELECT * FROM project_applications WHERE project_id = 13 AND user_id = 41");
$app = $db->single();

if ($app) {
    echo "✓ Application found in database:\n";
    echo "  ID: {$app->id}\n";
    echo "  Project: 13\n";
    echo "  User: 41\n";
    echo "  Status: {$app->status}\n";
    echo "  Applied: {$app->applied_at}\n";
    echo "  Experience: " . (strlen($app->experience) > 20 ? substr($app->experience, 0, 20) . "..." : $app->experience) . "\n";
    echo "  Skills: " . (strlen($app->skills) > 20 ? substr($app->skills, 0, 20) . "..." : $app->skills) . "\n";
} else {
    echo "❌ Application NOT found in database!\n";
}

// Check all current applications
echo "\nAll applications for org 37:\n";
$db->query("SELECT pa.id, pa.project_id, u.username, pa.status FROM project_applications pa JOIN projects p ON pa.project_id = p.id JOIN users u ON pa.user_id = u.id WHERE p.organization_id = 37");
$allApps = $db->resultSet();
foreach ($allApps as $a) {
    echo "  - {$a->username} → Project {$a->project_id}: {$a->status}\n";
}
?>
