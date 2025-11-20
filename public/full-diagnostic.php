<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();

echo "<h2 style='color: #658396;'>Complete Application System Diagnostic</h2>";

// 1. Check session
echo "<h3>1️⃣ SESSION INFO:</h3>";
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ NOT LOGGED IN - Please login first</p>";
    exit;
}
echo "<p>User ID: <strong>{$_SESSION['user_id']}</strong></p>";
echo "<p>User Role: <strong>" . ($_SESSION['role'] ?? 'Not set') . "</strong></p>";

// 2. Get user details
echo "<h3>2️⃣ LOGGED IN USER DETAILS:</h3>";
$db->query("SELECT id, username, email, role FROM users WHERE id = :id");
$db->bind(':id', $_SESSION['user_id']);
$user = $db->single();
echo "<pre>";
print_r($user);
echo "</pre>";

// 3. Check database structure
echo "<h3>3️⃣ DATABASE STRUCTURE:</h3>";
$db->query("DESCRIBE project_applications");
$columns = $db->resultSet();
$newColumns = [];
foreach ($columns as $col) {
    if (in_array($col->Field, ['experience', 'skills', 'contribution', 'commitment', 'duration', 'motivation', 'portfolio'])) {
        $newColumns[] = $col->Field;
    }
}
echo "<p>New columns present: " . (empty($newColumns) ? "<span style='color:red;'>❌ NONE</span>" : "<span style='color:green;'>✅ " . count($newColumns) . " columns</span>") . "</p>";
if (!empty($newColumns)) {
    echo "<p>Columns: " . implode(", ", $newColumns) . "</p>";
}

// 4. List all projects
echo "<h3>4️⃣ ALL PROJECTS IN DATABASE:</h3>";
$db->query("SELECT id, organization_id, name, created_at FROM projects ORDER BY created_at DESC LIMIT 10");
$allProjects = $db->resultSet();
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Org ID</th><th>Name</th><th>Created</th></tr>";
foreach ($allProjects as $p) {
    $highlight = ($p->name === 'Kithsara Project') ? "style='background-color: #FFFF99;'" : "";
    echo "<tr {$highlight}><td>{$p->id}</td><td>{$p->organization_id}</td><td>{$p->name}</td><td>{$p->created_at}</td></tr>";
}
echo "</table>";

// 5. List all applications
echo "<h3>5️⃣ ALL APPLICATIONS IN DATABASE:</h3>";
$db->query("SELECT pa.id, pa.project_id, pa.user_id, pa.status, pa.applied_at, p.name as project_name, u.username FROM project_applications pa JOIN projects p ON pa.project_id = p.id JOIN users u ON pa.user_id = u.id ORDER BY pa.applied_at DESC");
$allApps = $db->resultSet();

if (empty($allApps)) {
    echo "<p style='color: orange;'>⚠️ NO APPLICATIONS in database</p>";
} else {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Project</th><th>User</th><th>Status</th><th>Applied</th></tr>";
    foreach ($allApps as $app) {
        echo "<tr><td>{$app->id}</td><td>{$app->project_name}</td><td>{$app->username}</td><td>{$app->status}</td><td>{$app->applied_at}</td></tr>";
    }
    echo "</table>";
}

// 6. If logged in as organization, show their projects
if ($user->role === 'organization') {
    echo "<h3>6️⃣ YOUR ORGANIZATION'S PROJECTS:</h3>";
    $db->query("SELECT id, name FROM projects WHERE organization_id = :org_id");
    $db->bind(':org_id', $user->id);
    $myProjects = $db->resultSet();
    
    if (empty($myProjects)) {
        echo "<p style='color: orange;'>⚠️ No projects found for your organization</p>";
    } else {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Project ID</th><th>Project Name</th></tr>";
        foreach ($myProjects as $p) {
            echo "<tr><td>{$p->id}</td><td>{$p->name}</td></tr>";
        }
        echo "</table>";
    }
    
    // 7. Applications for your projects
    echo "<h3>7️⃣ APPLICATIONS FOR YOUR PROJECTS:</h3>";
    $db->query("
        SELECT pa.id, pa.project_id, pa.user_id, pa.status, pa.applied_at, p.name as project_name, u.username
        FROM project_applications pa
        JOIN projects p ON pa.project_id = p.id
        JOIN users u ON pa.user_id = u.id
        WHERE p.organization_id = :org_id
        ORDER BY pa.applied_at DESC
    ");
    $db->bind(':org_id', $user->id);
    $myApps = $db->resultSet();
    
    if (empty($myApps)) {
        echo "<p style='color: orange;'>⚠️ No applications for your projects yet</p>";
    } else {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>App ID</th><th>Project</th><th>User</th><th>Status</th><th>Applied</th></tr>";
        foreach ($myApps as $app) {
            echo "<tr><td>{$app->id}</td><td>{$app->project_name}</td><td>{$app->username}</td><td>{$app->status}</td><td>{$app->applied_at}</td></tr>";
        }
        echo "</table>";
    }
}

echo "<hr>";
echo "<p style='font-size: 0.9em; color: #666;'>Last check: " . date('Y-m-d H:i:s') . "</p>";
?>
