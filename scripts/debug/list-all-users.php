<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();

echo "ALL USERS IN SYSTEM\n";
echo str_repeat("=", 70) . "\n\n";

$db->query("SELECT id, username, email, role, created_at FROM users ORDER BY id DESC");
$users = $db->resultSet();

echo "Total Users: " . count($users) . "\n\n";

foreach ($users as $u) {
    echo "ID: {$u->id} | {$u->username} | {$u->email} | Role: {$u->role} | Created: {$u->created_at}\n";
}

echo "\n\nAPPLICATIONS BY USER:\n";
$db->query("SELECT u.id, u.username, COUNT(pa.id) as app_count FROM users u LEFT JOIN project_applications pa ON u.id = pa.user_id GROUP BY u.id, u.username ORDER BY app_count DESC");
$users_with_apps = $db->resultSet();

foreach ($users_with_apps as $u) {
    if ($u->app_count > 0) {
        echo "  {$u->username}: {$u->app_count} application(s)\n";
    }
}
?>
