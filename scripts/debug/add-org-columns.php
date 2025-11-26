<?php
require_once "../app/config/config.php";
require_once "../core/Database.php";

$db = new Database();

// Check which columns already exist
$db->query("DESCRIBE users");
$columns = $db->resultSet();
$existingColumns = [];
foreach($columns as $col) {
    $existingColumns[] = $col->Field;
}

$columnsToAdd = [
    'phone' => 'VARCHAR(20)',
    'website' => 'VARCHAR(255)',
    'address' => 'VARCHAR(255)',
    'city' => 'VARCHAR(100)',
    'country' => 'VARCHAR(100)',
    'postal_code' => 'VARCHAR(20)',
    'linkedin' => 'VARCHAR(255)',
    'twitter' => 'VARCHAR(255)',
    'github' => 'VARCHAR(255)'
];

foreach($columnsToAdd as $colName => $colType) {
    if(!in_array($colName, $existingColumns)) {
        $db->query("ALTER TABLE users ADD COLUMN $colName $colType");
        $db->execute();
        echo "✓ Added column: $colName\n";
    } else {
        echo "• Column already exists: $colName\n";
    }
}

echo "\nVerifying new structure:\n";
$db->query("DESCRIBE users");
$newColumns = $db->resultSet();
foreach($newColumns as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}
?>
