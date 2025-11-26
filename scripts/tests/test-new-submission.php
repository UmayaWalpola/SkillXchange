<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../app/models/Project.php';

// Simulate being logged in as Devinda (individual user 41)
$_SESSION['user_id'] = 41;
$_SESSION['role'] = 'individual';

echo "=== TESTING NEW APPLICATION SUBMISSION ===\n\n";

// First, let's use the second new project (ID: 14 - Devinda Web Project) that has no applications yet
$projectId = 14;

$db = new Database();
$db->query("SELECT name FROM projects WHERE id = :id");
$db->bind(':id', $projectId);
$project = $db->single();

echo "Test Setup:\n";
echo "  User: Devinda (ID 41)\n";
echo "  Project: {$project->name} (ID {$projectId})\n";
echo "  Role: individual\n\n";

// Check if application already exists
$db->query("SELECT id FROM project_applications WHERE project_id = :pid AND user_id = 41");
$db->bind(':pid', $projectId);
$existing = $db->single();

if ($existing) {
    echo "⚠️  Application already exists (ID: {$existing->id}). Deleting...\n";
    $db->query("DELETE FROM project_applications WHERE id = :id");
    $db->bind(':id', $existing->id);
    $db->execute();
    echo "✓ Deleted\n\n";
}

// Now submit a new application
echo "Submitting new application...\n";
$projectModel = new Project();

$result = $projectModel->applyToProjectAdvanced(
    $projectId,
    41,
    'I have 3 years of web development experience with modern frameworks',
    'JavaScript, React, PHP, MySQL, HTML/CSS, REST APIs',
    'I can lead frontend development and help with API integration',
    '20-30',
    '3-6',
    'This project aligns perfectly with my career goals in full-stack development',
    'https://github.com/devinda'
);

echo "Result: " . ($result ? "✓ SUCCESS" : "❌ FAILED") . "\n\n";

// Verify the application was inserted
echo "Verification:\n";
$db->query("SELECT * FROM project_applications WHERE project_id = :pid AND user_id = 41 ORDER BY applied_at DESC");
$db->bind(':pid', $projectId);
$apps = $db->resultSet();

if ($apps) {
    $app = $apps[0];
    echo "  ✓ Application found!\n";
    echo "    ID: {$app->id}\n";
    echo "    Project: {$projectId}\n";
    echo "    User: 41 (Devinda)\n";
    echo "    Status: {$app->status}\n";
    echo "    Applied: {$app->applied_at}\n";
    echo "    Experience: " . (strlen($app->experience) > 40 ? substr($app->experience, 0, 40) . "..." : $app->experience) . "\n";
    echo "    Skills: " . (strlen($app->skills) > 40 ? substr($app->skills, 0, 40) . "..." : $app->skills) . "\n";
} else {
    echo "  ❌ No application found!\n";
}

// Check org stats
echo "\n\nOrganization 37 Updated Stats:\n";
$projectModel = new Project();
$stats = $projectModel->getApplicationStats(37);
echo "  Total: {$stats->total}\n";
echo "  Pending: {$stats->pending}\n";
echo "  Accepted: {$stats->accepted}\n";
echo "  Rejected: {$stats->rejected}\n";

echo "\n=== NEXT STEPS ===\n";
echo "1. Log in as Pretty Software (org 37)\n";
echo "2. Go to Organization → Applications\n";
echo "3. You should now see:\n";
echo "   - Pending Applications: 2 (kithsara project + Devinda Web Project)\n";
echo "   - Accepted Applications: 2 (Zcode + CodeCollab Hub)\n";
?>
