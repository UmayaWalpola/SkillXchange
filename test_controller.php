<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test the quiz manager controller directly
echo "Testing QuizmanagerController::create()...\n";

// Start session and set fake user
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'quiz_manager';

echo "Session set\n";

// Include necessary files
require_once 'core/Database.php';
require_once 'core/Controller.php';
require_once 'app/models/Quiz.php';
require_once 'app/controllers/QuizmanagerController.php';

echo "Files included\n";

// Create controller instance
try {
    $controller = new QuizmanagerController();
    echo "Controller created successfully\n";
} catch (Exception $e) {
    echo "Error creating controller: " . $e->getMessage() . "\n";
    exit(1);
}

// Try to call the create method
try {
    echo "Calling create method...\n";
    ob_start(); // Capture output
    $controller->create();
    $output = ob_get_clean();
    echo "Method called successfully\n";
    echo "Output length: " . strlen($output) . " characters\n";
    if (strlen($output) > 0) {
        echo "First 500 chars of output:\n" . substr($output, 0, 500) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>