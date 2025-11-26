<?php
require_once "../app/config/config.php";
require_once "../core/Database.php";

$db = new Database();
$db->query("SHOW TABLES");
$tables = $db->resultSet();

echo "Tables in database:\n";
foreach($tables as $t) {
    $key = key((array)$t);
    echo $t->$key . "\n";
}
?>
