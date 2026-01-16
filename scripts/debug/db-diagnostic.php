<?php
require 'app/config/config.php';
require 'core/Database.php';

$db = new Database();

echo "=== DATABASE DIAGNOSTIC ===\n\n";

// 1. Check projects
echo "[1] Projects in database:\n";
$db->query("SELECT id, organization_id, name FROM projects LIMIT 10");
$projects = $db->resultSet();
foreach ($projects as $p) {
    echo "  ID: {$p->id}, Org: {$p->organization_id}, Name: {$p->name}\n";
}

// 2. Check applications
echo "\n[2] Applications in database:\n";
$db->query("SELECT * FROM project_applications LIMIT 10");
$apps = $db->resultSet();
if (empty($apps)) {
    echo "  NO APPLICATIONS FOUND\n";
} else {
    foreach ($apps as $app) {
        echo "  ID: {$app->id}, Project: {$app->project_id}, User: {$app->user_id}, Status: {$app->status}\n";
    }
}

// 3. Check organizations
echo "\n[3] Organizations in database:\n";
$db->query("SELECT id, name FROM users WHERE role='organization' LIMIT 5");
$orgs = $db->resultSet();
foreach ($orgs as $org) {
    echo "  ID: {$org->id}, Name: {$org->name}\n";
}

// 4. Check users
echo "\n[4] Individual users in database:\n";
$db->query("SELECT id, username FROM users WHERE role='individual' LIMIT 5");
$users = $db->resultSet();
foreach ($users as $user) {
    echo "  ID: {$user->id}, Username: {$user->username}\n";
}

// 5. Test the getAllApplicationsForOrganization query for org_id=1
echo "\n[5] Testing getAllApplicationsForOrganization for org_id=1:\n";
$query = "SELECT pa.id, pa.project_id, pa.user_id, pa.message, pa.status, pa.applied_at, 
                 p.name AS project_name, u.username AS user_name, u.email AS user_email, 
                 u.profile_picture, u.bio AS user_title, 
                 GROUP_CONCAT(DISTINCT us.skill_name SEPARATOR ', ') AS user_skills,
                 (SELECT COUNT(*) FROM user_projects WHERE user_id = u.id AND status = 'completed') AS completed_projects,
                 (SELECT IFNULL(ROUND(AVG(rating),2),0) FROM user_feedback WHERE user_id = u.id) AS user_rating 
          FROM project_applications pa
          JOIN projects p ON pa.project_id = p.id
          JOIN users u ON pa.user_id = u.id
          LEFT JOIN user_skills us ON us.user_id = u.id
          WHERE p.organization_id = 1
          GROUP BY pa.id
          ORDER BY pa.applied_at DESC";

$db->query($query);
$result = $db->resultSet();
echo "  Result count: " . count($result) . "\n";
if (!empty($result)) {
    foreach ($result as $r) {
        echo "    - {$r->user_name} applied for {$r->project_name}\n";
    }
}

// 6. Debug: check if projects table has organization_id column
echo "\n[6] Checking projects table structure:\n";
$db->query("DESCRIBE projects");
$columns = $db->resultSet();
$has_org_id = false;
foreach ($columns as $col) {
    if ($col->Field === 'organization_id') {
        $has_org_id = true;
        echo "  ✓ organization_id column exists\n";
    }
}
if (!$has_org_id) {
    echo "  ✗ organization_id column NOT FOUND - this is the problem!\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
?>
