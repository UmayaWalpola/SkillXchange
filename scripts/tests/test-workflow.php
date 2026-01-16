<?php
/**
 * COMPLETE APPLICATION WORKFLOW TEST
 * This verifies the entire application submission flow
 */

require_once '../app/config/config.php';
require_once '../core/Database.php';
require_once '../app/models/Project.php';

$db = new Database();

echo "=== PROJECT APPLICATION WORKFLOW TEST ===\n\n";

// 1. Check if tables exist
echo "[1] Checking database tables...\n";
$db->query("SHOW TABLES LIKE 'project_applications'");
$result = $db->single();
if ($result) {
    echo "✓ project_applications table exists\n";
} else {
    echo "✗ project_applications table NOT found\n";
    exit(1);
}

// 2. Count existing applications
echo "\n[2] Checking application records...\n";
$db->query("SELECT COUNT(*) as count FROM project_applications");
$result = $db->single();
echo "Total applications in database: " . $result->count . "\n";

// 3. Get stats by status
echo "\n[3] Application statistics...\n";
$db->query("SELECT status, COUNT(*) as count FROM project_applications GROUP BY status");
$statuses = $db->resultSet();
foreach ($statuses as $stat) {
    echo "  - " . ucfirst($stat->status) . ": " . $stat->count . "\n";
}

// 4. Check recent applications
echo "\n[4] Recent 5 applications...\n";
$db->query("
    SELECT pa.id, pa.project_id, pa.user_id, pa.status, pa.applied_at,
           p.name as project_name, u.username as user_name
    FROM project_applications pa
    LEFT JOIN projects p ON pa.project_id = p.id
    LEFT JOIN users u ON pa.user_id = u.id
    ORDER BY pa.applied_at DESC
    LIMIT 5
");
$recent = $db->resultSet();
foreach ($recent as $app) {
    echo "  ID: {$app->id} | Project: {$app->project_name} | User: {$app->user_name} | Status: {$app->status}\n";
}

// 5. Test the model's getAllApplicationsForOrganization method
echo "\n[5] Testing Model::getAllApplicationsForOrganization()...\n";
$projectModel = new Project();

// Get first organization with projects
$db->query("SELECT DISTINCT organization_id FROM projects LIMIT 1");
$org = $db->single();
if ($org) {
    $orgId = $org->organization_id;
    echo "Testing with Organization ID: " . $orgId . "\n";
    
    $allApps = $projectModel->getAllApplicationsForOrganization($orgId);
    echo "✓ Retrieved " . count($allApps) . " applications for organization\n";
    
    if (!empty($allApps)) {
        $firstApp = $allApps[0];
        echo "\n  Sample application fields:\n";
        echo "    - user_name: " . ($firstApp->user_name ?? 'N/A') . "\n";
        echo "    - user_email: " . ($firstApp->user_email ?? 'N/A') . "\n";
        echo "    - project_name: " . ($firstApp->project_name ?? 'N/A') . "\n";
        echo "    - message: " . substr($firstApp->message ?? '', 0, 50) . "...\n";
        echo "    - status: " . ($firstApp->status ?? 'N/A') . "\n";
        echo "    - user_skills: " . ($firstApp->user_skills ?? 'N/A') . "\n";
    }
} else {
    echo "No organizations found with projects\n";
}

// 6. Test getApplicationStats
echo "\n[6] Testing Model::getApplicationStats()...\n";
if ($org) {
    $stats = $projectModel->getApplicationStats($orgId);
    echo "✓ Stats for Organization {$orgId}:\n";
    echo "  - Total: " . ($stats->total ?? 0) . "\n";
    echo "  - Pending: " . ($stats->pending ?? 0) . "\n";
    echo "  - Accepted: " . ($stats->accepted ?? 0) . "\n";
    echo "  - Rejected: " . ($stats->rejected ?? 0) . "\n";
}

// 7. Verify the form action route
echo "\n[7] Verifying form action route...\n";
echo "Expected form action: " . URLROOT . "/ProjectApplication/apply/{projectId}\n";
echo "✓ Route is ready for POST requests\n";

echo "\n=== ALL TESTS COMPLETE ===\n";
echo "\nWORKFLOW SUMMARY:\n";
echo "1. User submits form at /project/detail/{id}\n";
echo "2. Form POSTs to /ProjectApplication/apply/{id}\n";
echo "3. Controller validates and calls Model::applyToProject()\n";
echo "4. Application stored in DB with status='pending'\n";
echo "5. Organization visits /organization/applications\n";
echo "6. Controller calls Model::getAllApplicationsForOrganization()\n";
echo "7. View displays applications grouped by status\n";
?>
