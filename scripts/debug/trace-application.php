<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();

// Check last 10 POST requests to understand the flow
echo "LAST APPLICATIONS SUBMITTED\n";
echo str_repeat("=", 60) . "\n\n";

// Check applications in last 24 hours
$db->query("SELECT pa.id, p.name, p.id as project_id, p.organization_id, u.username, u.id as user_id, pa.status, pa.applied_at, pa.experience, pa.skills FROM project_applications pa JOIN projects p ON pa.project_id = p.id JOIN users u ON pa.user_id = u.id ORDER BY pa.applied_at DESC LIMIT 10");
$apps = $db->resultSet();

foreach ($apps as $app) {
    echo "ID: {$app->id}\n";
    echo "  User: {$app->username} (ID: {$app->user_id})\n";
    echo "  Project: {$app->name} (ID: {$app->project_id}, Org: {$app->organization_id})\n";
    echo "  Status: {$app->status}\n";
    echo "  Applied: {$app->applied_at}\n";
    echo "  Has experience: " . (!empty($app->experience) ? "Yes" : "No") . "\n";
    echo "  Has skills: " . (!empty($app->skills) ? "Yes" : "No") . "\n";
    echo "\n";
}

echo "TOTAL APPLICATIONS: ";
$db->query("SELECT COUNT(*) as cnt FROM project_applications");
$cnt = $db->single();
echo $cnt->cnt . "\n";

echo "\nKITHSARA PROJECT DETAILS:\n";
$db->query("SELECT * FROM projects WHERE name LIKE '%Kithsara%'");
$kith = $db->single();
if ($kith) {
    echo "  ID: {$kith->id}\n";
    echo "  Name: {$kith->name}\n";
    echo "  Organization ID: {$kith->organization_id}\n";
    echo "  Status: {$kith->status}\n";
    echo "  Current Members: {$kith->current_members}\n";
    
    echo "\n  Applications for this project:\n";
    $db->query("SELECT u.username, pa.status, pa.applied_at FROM project_applications pa JOIN users u ON pa.user_id = u.id WHERE pa.project_id = :pid ORDER BY pa.applied_at DESC");
    $db->bind(':pid', $kith->id);
    $apps_for_kith = $db->resultSet();
    if ($apps_for_kith) {
        foreach ($apps_for_kith as $a) {
            echo "    - {$a->username}: {$a->status} ({$a->applied_at})\n";
        }
    } else {
        echo "    (none)\n";
    }
}

echo "\nLOG: Check application_audit table if it exists\n";
$db->query("SHOW TABLES LIKE 'application%'");
$tables = $db->resultSet();
if ($tables) {
    foreach ($tables as $t) {
        echo "Found table: " . implode(', ', (array)$t) . "\n";
    }
}
?>
