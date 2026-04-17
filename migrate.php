<?php
/**
 * Migration Runner - Execute all pending migrations
 */

// Set up paths
define('APPROOT', dirname(__FILE__));

// Load configuration
require_once APPROOT . '/app/config/config.php';
require_once APPROOT . '/core/Database.php';

// Create Database instance
$db = new Database();

// Get all migration files in the database/migrations directory
$migrationDir = APPROOT . '/database/migrations';
$migrationFiles = glob($migrationDir . '/*.sql');

if (!$migrationFiles) {
    echo "No migration files found.\n";
    exit(1);
}

// Sort files to ensure they run in order
sort($migrationFiles);

$executedCount = 0;
$errorCount = 0;

foreach ($migrationFiles as $migrationFile) {
    $filename = basename($migrationFile);
    
    // Skip non-migration files
    if (strpos($filename, '.sql') === false) {
        continue;
    }

    echo "\n📄 Processing: $filename\n";
    
    $sql = file_get_contents($migrationFile);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (empty($statement)) {
            continue;
        }
        
        try {
            $db->query($statement);
            $db->execute();
            echo "  ✓ " . substr(trim(str_replace(["\n", "\r"], ' ', $statement)), 0, 60) . "...\n";
            $executedCount++;
        } catch (Exception $e) {
            // Skip "already exists" errors (idempotent migrations)
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate') !== false) {
                echo "  ⊘ Already exists: " . substr($statement, 0, 50) . "...\n";
            } else {
                echo "  ✗ Error: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✓ Migration completed!\n";
echo "  Statements executed: $executedCount\n";
echo "  Errors encountered: $errorCount\n";
echo str_repeat("=", 50) . "\n";
?>
