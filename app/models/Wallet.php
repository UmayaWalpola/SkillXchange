<?php

class Wallet extends Database {  // Match her style: extend Database
    
    private $db;
    
    public function __construct() {
        $this->db = new Database;  // Match her style: no parentheses
    }

    /**
     * Ensure wallet exists for user
     */
    public function ensureWalletExists($userId, $userRole) {
        $this->db->query("SELECT id, balance FROM wallets WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        $wallet = $this->db->single();
        
        if (!$wallet) {
            $initialAmount = ($userRole === 'organization') ? 1000.00 : 250.00;
            
            try {
                $this->db->query("INSERT INTO wallets (user_id, balance) VALUES (:user_id, :balance)");
                $this->db->bind(':user_id', $userId);
                $this->db->bind(':balance', $initialAmount);
                $this->db->execute();
                
                return $initialAmount;
            } catch (Exception $e) {
                error_log("Wallet creation error for user $userId: " . $e->getMessage());
                return 0;
            }
        }
        
        return floatval($wallet->balance);
    }

    /**
     * Get user balance
     */
    public function getBalance($userId) {
        $this->db->query("SELECT balance FROM wallets WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? floatval($result->balance) : 0;
    }

    /**
     * Get total sent amount
     */
    public function getTotalSent($userId) {
        $this->db->query("
            SELECT COALESCE(SUM(amount), 0) as total 
            FROM wallet_transactions 
            WHERE sender_id = :user_id AND status = 'completed'
        ");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return floatval($result->total);
    }

    /**
     * Get total received amount
     */
    public function getTotalReceived($userId) {
        $this->db->query("
            SELECT COALESCE(SUM(amount), 0) as total 
            FROM wallet_transactions 
            WHERE receiver_id = :user_id AND status = 'completed'
        ");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return floatval($result->total);
    }

    /**
     * Get user transactions (sent and received)
     */
    public function getTransactions($userId) {
        // Sent transactions
        $this->db->query("
            SELECT wt.*, 
                   u.username as receiver, 
                   u.role as receiver_role,
                   DATE_FORMAT(wt.created_at, '%Y-%m-%d %H:%i') as timestamp
            FROM wallet_transactions wt
            INNER JOIN users u ON wt.receiver_id = u.id
            WHERE wt.sender_id = :user_id
            ORDER BY wt.created_at DESC
            LIMIT 50
        ");
        $this->db->bind(':user_id', $userId);
        $sent = $this->db->resultSet();
        
        // Received transactions
        $this->db->query("
            SELECT wt.*, 
                   u.username as sender, 
                   u.role as sender_role,
                   DATE_FORMAT(wt.created_at, '%Y-%m-%d %H:%i') as timestamp
            FROM wallet_transactions wt
            INNER JOIN users u ON wt.sender_id = u.id
            WHERE wt.receiver_id = :user_id
            ORDER BY wt.created_at DESC
            LIMIT 50
        ");
        $this->db->bind(':user_id', $userId);
        $received = $this->db->resultSet();
        
        return ['sent' => $sent, 'received' => $received];
    }

    /**
     * Get allowed recipients for a user
     */
    public function getAllowedRecipients($userId, $userRole) {
        if ($userRole === 'organization') {
            $this->db->query("
                SELECT id, username, email, role
                FROM users
                WHERE role = 'individual'
                AND id != :user_id
                ORDER BY username ASC
            ");
        } else {
            $this->db->query("
                SELECT id, username, email, role
                FROM users
                WHERE role = 'individual'
                AND id != :user_id
                ORDER BY username ASC
            ");
        }
        
        $this->db->bind(':user_id', $userId);
        $recipients = $this->db->resultSet();
        
        return $recipients ?? [];
    }

    /**
     * Transfer money between users
     */
    public function transferMoney($senderId, $receiverId, $amount, $note = '') {
        try {
            // Get receiver details
            $receiver = $this->getUserById($receiverId);
            
            if (!$receiver) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Can't send to yourself
            if ($senderId == $receiverId) {
                return ['success' => false, 'message' => 'Cannot send BuckX to yourself'];
            }
            
            // Check balance
            $senderBalance = $this->getBalance($senderId);
            if ($senderBalance < $amount) {
                return ['success' => false, 'message' => 'Insufficient balance'];
            }
            
            // Start transaction
            $this->db->query("START TRANSACTION");
            
            // Deduct from sender
            if (!$this->updateBalance($senderId, $amount, 'subtract')) {
                throw new Exception("Failed to deduct from sender");
            }
            
            // Add to receiver
            if (!$this->updateBalance($receiverId, $amount, 'add')) {
                throw new Exception("Failed to add to receiver");
            }
            
            // Log transaction
            $this->db->query("
                INSERT INTO wallet_transactions 
                (sender_id, receiver_id, amount, note, transaction_type, status, created_at) 
                VALUES (:sender, :receiver, :amount, :note, 'transfer', 'completed', NOW())
            ");
            $this->db->bind(':sender', $senderId);
            $this->db->bind(':receiver', $receiverId);
            $this->db->bind(':amount', $amount);
            $this->db->bind(':note', $note);
            
            if (!$this->db->execute()) {
                throw new Exception("Failed to log transaction");
            }
            
            $transactionId = $this->db->lastInsertId();
            
            // Commit transaction
            $this->db->query("COMMIT");
            
            return [
                'success' => true,
                'message' => 'Transfer successful! ' . number_format($amount, 2) . ' BuckX sent to ' . $receiver->username,
                'transaction_id' => $transactionId,
                'new_balance' => $this->getBalance($senderId)
            ];
            
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            error_log("Transfer Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Transfer failed. Please try again.'];
        }
    }

    /**
     * Create BuckX purchase record
     */
    public function createPurchase($orgId, $buckxAmount, $priceLKR, $paymentMethod = 'card') {
        try {
            $this->db->query("START TRANSACTION");

            // Create purchase record
            $this->db->query("
                INSERT INTO buckx_purchases 
                (org_id, buckx_amount, price_lkr, payment_method, status, created_at, completed_at) 
                VALUES (:org_id, :amount, :price, :method, 'completed', NOW(), NOW())
            ");
            $this->db->bind(':org_id', $orgId);
            $this->db->bind(':amount', $buckxAmount);
            $this->db->bind(':price', $priceLKR);
            $this->db->bind(':method', $paymentMethod);
            $this->db->execute();

            $purchaseId = $this->db->lastInsertId();

            // Credit BuckX to wallet
            if (!$this->updateBalance($orgId, $buckxAmount, 'add')) {
                throw new Exception("Failed to credit wallet");
            }

            // Create transaction log
            $this->db->query("
                INSERT INTO wallet_transactions 
                (receiver_id, amount, note, transaction_type, status, created_at) 
                VALUES (:receiver_id, :amount, :note, 'purchase', 'completed', NOW())
            ");
            $this->db->bind(':receiver_id', $orgId);
            $this->db->bind(':amount', $buckxAmount);
            $this->db->bind(':note', "Purchased {$buckxAmount} BuckX for LKR " . number_format($priceLKR, 2));
            $this->db->execute();

            $this->db->query("COMMIT");

            return [
                'success' => true,
                'purchase_id' => $purchaseId,
                'message' => "Successfully purchased {$buckxAmount} BuckX!"
            ];

        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            error_log("Purchase Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Purchase failed. Please try again.'];
        }
    }

    /**
     * Get purchase by ID
     */
    public function getPurchaseById($purchaseId, $orgId) {
        $this->db->query("SELECT * FROM buckx_purchases WHERE id = :id AND org_id = :org_id");
        $this->db->bind(':id', $purchaseId);
        $this->db->bind(':org_id', $orgId);
        return $this->db->single();
    }

    // ============================================
    // PRIVATE HELPER METHODS
    // ============================================

    /**
     * Update balance (private helper)
     */
    private function updateBalance($userId, $amount, $operation = 'add') {
        if ($operation === 'add') {
            $this->db->query("UPDATE wallets SET balance = balance + :amount WHERE user_id = :user_id");
        } else {
            $this->db->query("UPDATE wallets SET balance = balance - :amount WHERE user_id = :user_id");
        }
        
        $this->db->bind(':amount', abs($amount));
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    /**
     * Get user by ID (private helper)
     */
    private function getUserById($userId) {
        $this->db->query("SELECT id, username, email, role FROM users WHERE id = :id");
        $this->db->bind(':id', $userId);
        return $this->db->single();
    }
}