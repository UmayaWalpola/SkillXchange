<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();

echo "ORGANIZATION ID 37 DETAILS\n";
echo str_repeat("=", 60) . "\n\n";

// Get org details
$db->query("SELECT * FROM users WHERE id = 37");
$org = $db->single();
if ($org) {
    echo "Organization User: {$org->username}\n";
    echo "Email: {$org->email}\n";
    echo "Role: {$org->role}\n\n";
}

// Get projects for org 37
echo "PROJECTS FOR ORG 37:\n";
$db->query("SELECT id, name, organization_id FROM projects WHERE organization_id = 37");
$projects = $db->resultSet();
foreach ($projects as $p) {
    echo "  {$p->id}: {$p->name}\n";
}

echo "\nAPPLICATIONS FOR ORG 37'S PROJECTS:\n";
$db->query("SELECT pa.id, pa.project_id, pa.user_id, u.username, pa.status, pa.applied_at FROM project_applications pa JOIN projects p ON pa.project_id = p.id JOIN users u ON pa.user_id = u.id WHERE p.organization_id = 37 ORDER BY pa.applied_at DESC");
$apps = $db->resultSet();
echo "Total: " . count($apps) . "\n";
foreach ($apps as $a) {
    echo "  {$a->username} → Project {$a->project_id}: {$a->status} ({$a->applied_at})\n";
}

echo "\nALL USERS:\n";
$db->query("SELECT id, username, email, role FROM users LIMIT 20");
$users = $db->resultSet();
foreach ($users as $u) {
    echo "  {$u->id}: {$u->username} ({$u->role})\n";
}
?>
