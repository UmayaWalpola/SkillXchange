<?php
// Simple test script to check badge functionality
echo "Testing badge functionality...\n";

// Try to include the bootstrap
@include 'app/bootstrap.php';

if (class_exists('Database')) {
    echo "Database class loaded successfully\n";

    $db = new Database();
    $db->query('SELECT COUNT(*) as count FROM badges WHERE status = "active"');
    $result = $db->single();

    if ($result) {
        echo "Found " . $result->count . " active badges in database\n";

        if ($result->count > 0) {
            $db->query('SELECT id, name, icon FROM badges WHERE status = "active" LIMIT 3');
            $badges = $db->resultSet();
            echo "Sample badges:\n";
            foreach ($badges as $badge) {
                echo "  ID: {$badge->id}, Name: {$badge->name}, Icon: {$badge->icon}\n";
            }
        }
    } else {
        echo "Could not query badges\n";
    }
} else {
    echo "Database class not loaded\n";
}

echo "Test completed\n";
?>