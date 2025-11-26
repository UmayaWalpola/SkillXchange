<?php
require_once "../app/config/config.php";
require_once "../core/Database.php";

$db = new Database();
$db->query("DESCRIBE users");
$columns = $db->resultSet();

echo "Users table columns:\n";
foreach($columns as $col) {
    echo $col->Field . " (" . $col->Type . ") - " . ($col->Null === "YES" ? "NULLABLE" : "NOT NULL") . "\n";
}
?>
