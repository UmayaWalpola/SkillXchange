<?php
/**
 * Setup Wallet System
 * Run this script to create the necessary wallet tables in the database
 */

// Include config
require_once __DIR__ . '/../app/config/config.php';

try {
    // Create database connection
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Read the migration file
    $sql = file_get_contents(__DIR__ . '/../database/migrations/create_wallet_system.sql');
    
    if ($sql === false) {
        throw new Exception("Could not read migration file");
    }
    
    echo "Executing wallet system migration...\n";
    
    // Execute the SQL statements
    $pdo->exec($sql);
    
    echo "✓ Wallet system tables created successfully!\n";
    echo "✓ Initial wallets created for existing users!\n";
    echo "\nWallet system is now ready to use.\n";
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
