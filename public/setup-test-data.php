<?php
require 'app/config/config.php';
require 'core/Database.php';

$db = new Database();

echo "=== COMPLETE TEST DATA SETUP ===\n\n";

// Step 1: Find or get org and user IDs
echo "[Step 1] Finding test data...\n";

// Get any project
$db->query("SELECT id, organization_id FROM projects LIMIT 1");
$project = $db->single();

if (!$project) {
    echo "ERROR: No projects in database. Please create projects first.\n";
    exit;
}

$projectId = $project->id;
$orgId = $project->organization_id;

echo "  Using Project: {$projectId}\n";
echo "  Organization: {$orgId}\n";

// Get any user
$db->query("SELECT id, username FROM users WHERE id != :id AND role='individual' LIMIT 1");
$db->bind(':id', $orgId); // Make sure we don't use the org
$user = $db->single();

if (!$user) {
    echo "ERROR: No individual users found\n";
    exit;
}

$userId = $user->id;
echo "  Test User: {$userId} ({$user->username})\n";

// Step 2: Clear any existing test applications
echo "\n[Step 2] Clearing existing test applications...\n";
$db->query("DELETE FROM project_applications WHERE project_id = :pid AND user_id = :uid");
$db->bind(':pid', $projectId);
$db->bind(':uid', $userId);
$db->execute();
echo "  ✓ Cleared\n";

// Step 3: Create 3 test applications
echo "\n[Step 3] Creating test applications...\n";

$testMessages = [
    "I have 5 years of experience in this field and I'm very interested in contributing!",
    "This project aligns perfectly with my skills. I'd love to join the team.",
    "I'm passionate about this type of work and believe I can make a great contribution."
];

for ($i = 0; $i < 3; $i++) {
    $msg = $testMessages[$i];
    
    // Use different users for variety
    $testUserId = $userId + $i;
    
    // Check if user exists
    $db->query("SELECT id FROM users WHERE id = :id");
    $db->bind(':id', $testUserId);
    $userExists = $db->single();
    
    if (!$userExists) {
        continue; // Skip if user doesn't exist
    }
    
    $db->query("INSERT INTO project_applications (project_id, user_id, message, status, applied_at) VALUES (:pid, :uid, :msg, 'pending', CURRENT_TIMESTAMP)");
    $db->bind(':pid', $projectId);
    $db->bind(':uid', $testUserId);
    $db->bind(':msg', $msg);
    
    if ($db->execute()) {
        echo "  ✓ Created application {$i+1} (User: {$testUserId})\n";
    }
}

// Step 4: Verify all applications
echo "\n[Step 4] Verifying applications...\n";
$db->query("SELECT COUNT(*) as count FROM project_applications WHERE project_id = :pid");
$db->bind(':pid', $projectId);
$result = $db->single();
echo "  Total applications for project {$projectId}: {$result->count}\n";

// Step 5: Test the exact query the controller uses
echo "\n[Step 5] Testing organization applications query...\n";
$db->query("SELECT pa.id, pa.project_id, pa.user_id, pa.message, pa.status, pa.applied_at, 
                  p.name AS project_name, u.username AS user_name, u.email AS user_email, 
                  u.profile_picture, u.bio AS user_title, 
                  GROUP_CONCAT(DISTINCT us.skill_name SEPARATOR ', ') AS user_skills,
                  (SELECT COUNT(*) FROM user_projects WHERE user_id = u.id AND status = 'completed') AS completed_projects,
                  (SELECT IFNULL(ROUND(AVG(rating),2),0) FROM user_feedback WHERE user_id = u.id) AS user_rating 
           FROM project_applications pa
           JOIN projects p ON pa.project_id = p.id
           JOIN users u ON pa.user_id = u.id
           LEFT JOIN user_skills us ON us.user_id = u.id
           WHERE p.organization_id = :org_id
           GROUP BY pa.id
           ORDER BY pa.applied_at DESC");
$db->bind(':org_id', $orgId);
$apps = $db->resultSet();

echo "  Result count: " . count($apps) . "\n";
if (!empty($apps)) {
    foreach ($apps as $app) {
        echo "    - ID {$app->id}: {$app->user_name} applied for '{$app->project_name}' (Status: {$app->status})\n";
    }
} else {
    echo "  NO RESULTS - Check that organization_id matches!\n";
}

// Step 6: Test stats query
echo "\n[Step 6] Testing stats query...\n";
$db->query("SELECT COUNT(*) AS total, 
                  SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                  SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) AS accepted,
                  SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected
           FROM project_applications
           WHERE project_id IN (SELECT id FROM projects WHERE organization_id = :org_id)");
$db->bind(':org_id', $orgId);
$stats = $db->single();

echo "  Total: {$stats->total}\n";
echo "  Pending: {$stats->pending}\n";
echo "  Accepted: {$stats->accepted}\n";
echo "  Rejected: {$stats->rejected}\n";

echo "\n=== SETUP COMPLETE ===\n";
echo "\nNEXT STEPS:\n";
echo "1. Open http://localhost/SkillXchange/public (or your site URL)\n";
echo "2. Make sure you're logged in as organization ID: {$orgId}\n";
echo "3. Go to: /organization/applications\n";
echo "4. You should see pending applications with stats and full details\n";
echo "\nIf you still see 0/0/0/0:\n";
echo "- Check that you're logged in as the correct organization\n";
echo "- Check that organization owns the projects with applications\n";
echo "- Run this script again to verify data was created\n";
?>
