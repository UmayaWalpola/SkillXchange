<?php
// Simple test script to check badge functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing badge functionality...\n";

require 'app/config/config.php';
require 'app/config/db.php';
require 'core/Database.php';
require 'app/models/Quiz.php';

$quiz = new Quiz();
$badges = $quiz->getAvailableBadges();

if (is_array($badges)) {
    echo "Badges loaded: " . count($badges) . "\n";
    if (count($badges) > 0) {
        echo "First badge: " . $badges[0]->name . "\n";
    }
} else {
    echo "Badges did not load as array\n";
}

echo "Test completed\n";
?>