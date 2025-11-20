<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();

echo "Updating application ID 7 status from 'accepted' to 'pending'...\n";

$db->query("UPDATE project_applications SET status = 'pending' WHERE id = 7");
if ($db->execute()) {
    echo "✓ Successfully updated!\n\n";
} else {
    echo "❌ Failed to update\n\n";
}

// Verify
$db->query("SELECT id, project_id, user_id, status FROM project_applications WHERE id = 7");
$app = $db->single();
echo "Verification:\n";
echo "  ID: {$app->id}\n";
echo "  Project: {$app->project_id}\n";
echo "  User: {$app->user_id}\n";
echo "  Status: {$app->status}\n";

// Check all stats
$db->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending, SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted FROM project_applications WHERE project_id IN (SELECT id FROM projects WHERE organization_id = 37)");
$stats = $db->single();
echo "\nOrg 37 stats:\n";
echo "  Total: {$stats->total}\n";
echo "  Pending: {$stats->pending}\n";
echo "  Accepted: {$stats->accepted}\n";
?>
