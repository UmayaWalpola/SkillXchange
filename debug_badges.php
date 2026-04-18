<?php
// Debug script to check badge loading
echo "Debugging badge loading...\n\n";

// Try to include the bootstrap
@include 'app/bootstrap.php';

if (class_exists('Database')) {
    echo "✓ Database class loaded successfully\n";

    try {
        $db = new Database();
        echo "✓ Database connection established\n";

        // Check if badges table exists
        $db->query("SHOW TABLES LIKE 'badges'");
        $tableExists = $db->resultSet();
        if ($tableExists) {
            echo "✓ badges table exists\n";
        } else {
            echo "✗ badges table does not exist\n";
            exit;
        }

        // Check table structure
        $db->query("DESCRIBE badges");
        $columns = $db->resultSet();
        echo "✓ badges table structure:\n";
        if ($columns) {
            foreach ($columns as $col) {
                echo "  - {$col->Field}: {$col->Type}\n";
            }
        } else {
            echo "  - Could not get table structure\n";
        }

        // Check badge count
        $db->query('SELECT COUNT(*) as count FROM badges');
        $result = $db->single();
        echo "✓ Total badges in database: " . ($result ? $result->count : 0) . "\n";

        // Check active badges
        $db->query("SELECT COUNT(*) as count FROM badges WHERE status = 'active'");
        $result = $db->single();
        echo "✓ Active badges in database: " . ($result ? $result->count : 0) . "\n";

        // Get sample badges
        $db->query('SELECT id, name, icon, status FROM badges LIMIT 5');
        $badges = $db->resultSet();
        if ($badges) {
            echo "✓ Sample badges:\n";
            foreach ($badges as $badge) {
                echo "  - ID: {$badge->id}, Name: {$badge->name}, Icon: {$badge->icon}, Status: {$badge->status}\n";
            }
        } else {
            echo "✗ No badges found\n";
        }

        // Test the Quiz model method
        if (class_exists('Quiz')) {
            echo "✓ Quiz model class exists\n";
            $quizModel = new Quiz();
            $availableBadges = $quizModel->getAvailableBadges();
            echo "✓ getAvailableBadges() returned: " . (is_array($availableBadges) ? count($availableBadges) : 'not an array') . " badges\n";

            if (is_array($availableBadges) && count($availableBadges) > 0) {
                echo "✓ First badge: ID=" . $availableBadges[0]->id . ", Name=" . $availableBadges[0]->name . "\n";
            }
        } else {
            echo "✗ Quiz model class not found\n";
        }

    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }

} else {
    echo "✗ Database class not loaded\n";
}

echo "\nDebug completed\n";
?>