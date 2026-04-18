<?php
require_once 'app/bootstrap.php';

$db = new Database();
$db->query('SELECT COUNT(*) as count FROM badges');
$result = $db->single();
echo 'Badges in database: ' . ($result ? $result->count : 0) . PHP_EOL;

$db->query('SELECT id, name, icon FROM badges LIMIT 5');
$badges = $db->resultSet();
if ($badges) {
    echo 'Sample badges:' . PHP_EOL;
    foreach ($badges as $badge) {
        echo '  ' . $badge->id . ': ' . $badge->icon . ' ' . $badge->name . PHP_EOL;
    }
}
?>