<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();

echo "<h2>Debugging Application Submission</h2>";

// Check if new columns exist
echo "<h3>1. Checking Database Structure:</h3>";
$db->query("DESCRIBE project_applications");
$columns = $db->resultSet();

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th></tr>";
foreach ($columns as $col) {
    $hasNew = in_array($col->Field, ['experience', 'skills', 'contribution', 'commitment', 'duration', 'motivation', 'portfolio']);
    $color = $hasNew ? '#90EE90' : '#FFB6C6';
    echo "<tr style='background-color: {$color};'><td>{$col->Field}</td><td>{$col->Type}</td><td>{$col->Null}</td></tr>";
}
echo "</table>";

// Check all applications
echo "<h3>2. All Applications in Database:</h3>";
$db->query("SELECT pa.id, pa.project_id, pa.user_id, pa.status, pa.applied_at, p.name as project_name, u.username FROM project_applications pa JOIN projects p ON pa.project_id = p.id JOIN users u ON pa.user_id = u.id ORDER BY pa.applied_at DESC");
$apps = $db->resultSet();

if (empty($apps)) {
    echo "<p style='color: red;'><strong>❌ NO APPLICATIONS FOUND IN DATABASE</strong></p>";
} else {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Project</th><th>User</th><th>Status</th><th>Applied At</th></tr>";
    foreach ($apps as $app) {
        echo "<tr><td>{$app->id}</td><td>{$app->project_name}</td><td>{$app->username}</td><td>{$app->status}</td><td>{$app->applied_at}</td></tr>";
    }
    echo "</table>";
}

// Check if Kithsara project exists
echo "<h3>3. Looking for 'Kithsara Project':</h3>";
$db->query("SELECT id, name, organization_id FROM projects WHERE name LIKE '%Kithsara%'");
$projects = $db->resultSet();

if (empty($projects)) {
    echo "<p style='color: red;'>❌ No project with 'Kithsara' in name found</p>";
} else {
    foreach ($projects as $p) {
        echo "<p>Found: <strong>{$p->name}</strong> (ID: {$p->id}, Org ID: {$p->organization_id})</p>";
    }
}

// Check if organization 37 exists
echo "<h3>4. Organization 37 (Pretty Software):</h3>";
$db->query("SELECT id, username, email FROM users WHERE id = 37");
$org = $db->single();
if ($org) {
    echo "<p>Organization: <strong>{$org->username}</strong> ({$org->email})</p>";
} else {
    echo "<p style='color: red;'>❌ Organization 37 not found</p>";
}

// Try to get applications for org 37
echo "<h3>5. Applications for Organization 37:</h3>";
$db->query("
    SELECT pa.id, pa.project_id, pa.user_id, pa.status, p.name as project_name, u.username
    FROM project_applications pa
    JOIN projects p ON pa.project_id = p.id
    JOIN users u ON pa.user_id = u.id
    WHERE p.organization_id = 37
    ORDER BY pa.applied_at DESC
");
$orgApps = $db->resultSet();

if (empty($orgApps)) {
    echo "<p style='color: orange;'>⚠️ No applications found for org 37</p>";
} else {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Project</th><th>User</th><th>Status</th></tr>";
    foreach ($orgApps as $app) {
        echo "<tr><td>{$app->id}</td><td>{$app->project_name}</td><td>{$app->username}</td><td>{$app->status}</td></tr>";
    }
    echo "</table>";
}

// Check current user session
echo "<h3>6. Current Session Info:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
