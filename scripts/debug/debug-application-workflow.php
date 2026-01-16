<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../app/models/Project.php';

echo "=== APPLICATION WORKFLOW DEBUG ===\n\n";

// 1. Check all applications
echo "1. ALL APPLICATIONS IN DATABASE:\n";
$db = new Database();
$db->query("SELECT id, project_id, user_id, status, applied_at FROM project_applications ORDER BY applied_at DESC");
$allApps = $db->resultSet();
echo "Total: " . count($allApps) . "\n";
foreach ($allApps as $app) {
    echo "  ID {$app->id}: Project {$app->project_id}, User {$app->user_id}, Status: {$app->status}, Applied: {$app->applied_at}\n";
}

// 2. Check pending applications
echo "\n2. PENDING APPLICATIONS ONLY:\n";
$db->query("SELECT id, project_id, user_id, status FROM project_applications WHERE status = 'pending'");
$pending = $db->resultSet();
echo "Total Pending: " . count($pending) . "\n";
foreach ($pending as $app) {
    echo "  ID {$app->id}: Project {$app->project_id}, User {$app->user_id}\n";
}

// 3. Check organization's projects and applications
echo "\n3. ORG 37 (Pretty Software) - PROJECTS & APPLICATIONS:\n";
$db->query("SELECT id, name FROM projects WHERE organization_id = 37");
$orgProjects = $db->resultSet();
echo "Projects: " . count($orgProjects) . "\n";
foreach ($orgProjects as $p) {
    echo "  Project {$p->id}: {$p->name}\n";
    
    $db->query("SELECT pa.id, pa.status, u.username FROM project_applications pa JOIN users u ON pa.user_id = u.id WHERE pa.project_id = :pid ORDER BY pa.applied_at DESC");
    $db->bind(':pid', $p->id);
    $apps = $db->resultSet();
    if ($apps) {
        foreach ($apps as $a) {
            echo "    - ID {$a->id}: {$a->username} ({$a->status})\n";
        }
    } else {
        echo "    (no applications)\n";
    }
}

// 4. Test the getAllApplicationsForOrganization method
echo "\n4. USING getAllApplicationsForOrganization(37):\n";
$projectModel = new Project();
$orgApps = $projectModel->getAllApplicationsForOrganization(37);
echo "Result: " . count($orgApps) . " applications\n";
foreach ($orgApps as $app) {
    echo "  ID {$app->id}: {$app->user_name} → {$app->project_name} ({$app->status})\n";
}

// 5. Test the getApplicationStats method
echo "\n5. USING getApplicationStats(37):\n";
$stats = $projectModel->getApplicationStats(37);
echo "Total: {$stats->total}, Pending: {$stats->pending}, Accepted: {$stats->accepted}, Rejected: {$stats->rejected}\n";

echo "\n=== END DEBUG ===\n";
?>
