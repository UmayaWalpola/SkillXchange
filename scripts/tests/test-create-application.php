<?php
require 'app/config/config.php';
require 'core/Database.php';

$db = new Database();

echo "=== CREATING TEST APPLICATION ===\n\n";

// 1. Get first project and organization
echo "[1] Finding test project and organization...\n";
$db->query("SELECT p.id, p.organization_id FROM projects p LIMIT 1");
$project = $db->single();

if (!$project) {
    echo "ERROR: No projects found in database\n";
    exit;
}

$projectId = $project->id;
$orgId = $project->organization_id;
echo "  Project ID: {$projectId}\n";
echo "  Organization ID: {$orgId}\n";

// 2. Get a user (individual)
echo "\n[2] Finding test user...\n";
$db->query("SELECT id, username FROM users WHERE role='individual' LIMIT 1");
$user = $db->single();

if (!$user) {
    echo "ERROR: No individual users found\n";
    exit;
}

$userId = $user->id;
echo "  User ID: {$userId}\n";
echo "  Username: {$user->username}\n";

// 3. Check if already applied
echo "\n[3] Checking for duplicate application...\n";
$db->query("SELECT id FROM project_applications WHERE project_id = :pid AND user_id = :uid");
$db->bind(':pid', $projectId);
$db->bind(':uid', $userId);
$existing = $db->single();

if ($existing) {
    echo "  Application already exists (ID: {$existing->id}). Deleting it...\n";
    $db->query("DELETE FROM project_applications WHERE project_id = :pid AND user_id = :uid");
    $db->bind(':pid', $projectId);
    $db->bind(':uid', $userId);
    $db->execute();
}

// 4. Insert new application
echo "\n[4] Creating application...\n";
$message = "This is a test application to verify the workflow is working.";
$db->query("INSERT INTO project_applications (project_id, user_id, message, status, applied_at) VALUES (:pid, :uid, :msg, 'pending', CURRENT_TIMESTAMP)");
$db->bind(':pid', $projectId);
$db->bind(':uid', $userId);
$db->bind(':msg', $message);

if ($db->execute()) {
    echo "  ✓ Application created successfully\n";
    $appId = $db->lastInsertId();
    echo "  Application ID: {$appId}\n";
} else {
    echo "  ✗ Failed to create application\n";
    exit;
}

// 5. Verify application can be retrieved
echo "\n[5] Verifying application retrieval...\n";
$db->query("SELECT * FROM project_applications WHERE id = :id");
$db->bind(':id', $appId);
$app = $db->single();

if ($app) {
    echo "  ✓ Application found\n";
    echo "    - Project: {$app->project_id}\n";
    echo "    - User: {$app->user_id}\n";
    echo "    - Status: {$app->status}\n";
    echo "    - Applied: {$app->applied_at}\n";
} else {
    echo "  ✗ Could not find application\n";
}

// 6. Test the getAllApplicationsForOrganization query
echo "\n[6] Testing getAllApplicationsForOrganization query...\n";
echo "  Querying for org_id = {$orgId}...\n";

$query = "SELECT pa.id, pa.project_id, pa.user_id, pa.message, pa.status, pa.applied_at,
                 p.name AS project_name, u.username AS user_name, u.email AS user_email
          FROM project_applications pa
          JOIN projects p ON pa.project_id = p.id
          JOIN users u ON pa.user_id = u.id
          WHERE p.organization_id = :org_id
          ORDER BY pa.applied_at DESC";

$db->query($query);
$db->bind(':org_id', $orgId);
$results = $db->resultSet();

echo "  ✓ Query executed. Results: " . count($results) . "\n";

foreach ($results as $r) {
    echo "    - {$r->user_name} applied for '{$r->project_name}' (Status: {$r->status})\n";
}

// 7. Stats query
echo "\n[7] Testing getApplicationStats query...\n";
$statsQuery = "SELECT COUNT(*) AS total,
                      SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                      SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) AS accepted,
                      SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected
               FROM project_applications
               WHERE project_id IN (SELECT id FROM projects WHERE organization_id = :org_id)";

$db->query($statsQuery);
$db->bind(':org_id', $orgId);
$stats = $db->single();

echo "  ✓ Stats retrieved:\n";
echo "    - Total: {$stats->total}\n";
echo "    - Pending: {$stats->pending}\n";
echo "    - Accepted: {$stats->accepted}\n";
echo "    - Rejected: {$stats->rejected}\n";

echo "\n=== TEST COMPLETE ===\n";
echo "\nNow:\n";
echo "1. Login as organization (ID: {$orgId})\n";
echo "2. Go to /organization/applications\n";
echo "3. You should see the test application in 'Pending Applications'\n";
?>
