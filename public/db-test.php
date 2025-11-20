
<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();

echo "DATABASE CHECK - Applications System\n";
echo str_repeat("=", 60) . "\n\n";

// 1. Check applications table structure
echo "1. PROJECT_APPLICATIONS TABLE COLUMNS:\n";
$db->query("DESCRIBE project_applications");
$cols = $db->resultSet();
foreach ($cols as $col) {
    echo "   {$col->Field}\n";
}

// 2. Check all applications
echo "\n2. ALL APPLICATIONS:\n";
$db->query("SELECT COUNT(*) as count FROM project_applications");
$cnt = $db->single();
echo "   Total: {$cnt->count}\n";

$db->query("SELECT pa.id, p.name as project, u.username, pa.status, pa.applied_at FROM project_applications pa JOIN projects p ON pa.project_id = p.id JOIN users u ON pa.user_id = u.id ORDER BY pa.applied_at DESC");
$allApps = $db->resultSet();
foreach ($allApps as $app) {
    echo "   - {$app->username} applied to {$app->project} ({$app->status})\n";
}

// 3. Find Kithsara project
echo "\n3. KITHSARA PROJECT:\n";
$db->query("SELECT id, name, organization_id FROM projects WHERE name LIKE '%Kithsara%'");
$kith = $db->single();
if ($kith) {
    echo "   ✓ ID: {$kith->id}, Org: {$kith->organization_id}\n";
} else {
    echo "   ❌ Not found\n";
}
?>