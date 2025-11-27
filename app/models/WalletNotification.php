<?php

class WalletNotification {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Create notification
     */
    public function create($userId, $transactionId, $type, $title, $message) {
        $this->db->query("INSERT INTO wallet_notifications 
                         (user_id, transaction_id, type, title, message, created_at) 
                         VALUES (:user_id, :transaction_id, :type, :title, :message, NOW())");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':transaction_id', $transactionId);
        $this->db->bind(':type', $type);
        $this->db->bind(':title', $title);
        $this->db->bind(':message', $message);
        return $this->db->execute();
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 20) {
        $this->db->query("SELECT * FROM wallet_notifications 
                         WHERE user_id = :user_id 
                         ORDER BY created_at DESC 
                         LIMIT :limit");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    /**
     * Get unread count
     */
    public function getUnreadCount($userId) {
        $this->db->query("SELECT COUNT(*) as count FROM wallet_notifications 
                         WHERE user_id = :user_id AND is_read = 0");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? intval($result->count) : 0;
    }

    /**
     * Mark as read
     */
    public function markAsRead($notificationId, $userId) {
        $this->db->query("UPDATE wallet_notifications 
                         SET is_read = 1 
                         WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':id', $notificationId);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    /**
     * Check for low balance and create notification if needed
     */
    public function checkLowBalance($userId, $balance, $threshold = 20.00) {
        if ($balance <= $threshold && $balance > 0) {
            // Check if we already sent a notification recently
            $this->db->query("SELECT id FROM wallet_notifications 
                             WHERE user_id = :user_id 
                             AND type = 'low_balance' 
                             AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                             LIMIT 1");
            $this->db->bind(':user_id', $userId);
            
            if (!$this->db->single()) {
                // No recent notification, send one
                $this->create(
                    $userId,
                    null,
                    'low_balance',
                    'Low Balance Alert ⚠️',
                    "Your BuckX balance is running low: " . number_format($balance, 2) . " BuckX remaining"
                );
            }
        }
    }
}