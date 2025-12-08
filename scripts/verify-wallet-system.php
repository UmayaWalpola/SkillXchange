<?php
/**
 * Verify wallet system tables
 */

require_once __DIR__ . '/../app/config/config.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Wallet System Verification ===\n\n";
    
    // Check wallets table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM wallets");
    $walletCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "✓ Wallets table exists: $walletCount wallets found\n";
    
    // Check wallet_transactions table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM wallet_transactions");
    $transCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "✓ Wallet transactions table exists: $transCount transactions found\n";
    
    // Check wallet_notifications table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM wallet_notifications");
    $notifCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "✓ Wallet notifications table exists: $notifCount notifications found\n";
    
    // Show sample wallet data
    echo "\n=== Sample Wallet Data ===\n";
    $stmt = $pdo->query("
        SELECT w.id, w.user_id, w.balance, u.username, u.role 
        FROM wallets w 
        JOIN users u ON w.user_id = u.id 
        LIMIT 5
    ");
    $wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($wallets as $wallet) {
        echo sprintf(
            "User: %-20s | Role: %-15s | Balance: $%.2f\n",
            $wallet['username'],
            $wallet['role'],
            $wallet['balance']
        );
    }
    
    echo "\n✓ Wallet system is working correctly!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
