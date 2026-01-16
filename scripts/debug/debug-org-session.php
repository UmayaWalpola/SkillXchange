<?php
require 'app/config/config.php';
require 'core/Database.php';
session_start();

echo "=== SESSION & ORGANIZATION DEBUG ===\n\n";

// Show current session
echo "[1] Current Session:\n";
echo "  user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "  role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";

if (!isset($_SESSION['user_id'])) {
    echo "\n  ERROR: Not logged in! Session not set.\n";
    echo "  Cannot determine which organization to query.\n";
    exit;
}

$orgId = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'unknown';

echo "\n[2] Using for query:\n";
echo "  org_id: {$orgId}\n";
echo "  role: {$role}\n";

// Check if this user/org exists
$db = new Database();
echo "\n[3] Checking user record:\n";
$db->query("SELECT id, username, role FROM users WHERE id = :id");
$db->bind(':id', $orgId);
$userRecord = $db->single();

if ($userRecord) {
    echo "  ✓ User found\n";
    echo "    - ID: {$userRecord->id}\n";
    echo "    - Username: {$userRecord->username}\n";
    echo "    - Role: {$userRecord->role}\n";
} else {
    echo "  ✗ User NOT found in database\n";
    exit;
}

// Check projects owned by this organization
echo "\n[4] Projects owned by this organization:\n";
$db->query("SELECT id, name FROM projects WHERE organization_id = :org_id");
$db->bind(':org_id', $orgId);
$projects = $db->resultSet();

if (empty($projects)) {
    echo "  ✗ NO PROJECTS FOUND - this might be why no applications show\n";
} else {
    echo "  ✓ Found " . count($projects) . " projects:\n";
    foreach ($projects as $p) {
        echo "    - {$p->id}: {$p->name}\n";
    }
}

// Check applications for projects owned by this org
echo "\n[5] Applications for this organization's projects:\n";
$db->query("SELECT pa.id, pa.project_id, pa.user_id, pa.status, 
                  p.name AS project_name, u.username AS user_name
           FROM project_applications pa
           JOIN projects p ON pa.project_id = p.id
           JOIN users u ON pa.user_id = u.id
           WHERE p.organization_id = :org_id");
$db->bind(':org_id', $orgId);
$applications = $db->resultSet();

if (empty($applications)) {
    echo "  ✗ NO APPLICATIONS FOUND\n";
    echo "    (This could be because no one has applied, or org has no projects)\n";
} else {
    echo "  ✓ Found " . count($applications) . " applications:\n";
    foreach ($applications as $app) {
        echo "    - {$app->user_name} applied for '{$app->project_name}' (Status: {$app->status})\n";
    }
}

echo "\n=== END DEBUG ===\n";
?>
