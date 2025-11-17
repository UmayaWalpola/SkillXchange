<?php

class Exchange extends Database {
    
    private $db;
    
    public function __construct() {
        $this->db = new Database;
    }
    
    /**
     * Create an exchange/connection request
     */
    public function createExchangeRequest($senderId, $receiverId, $skillOffered = null, $skillWanted = null) {
        // Check if exchange already exists
        $this->db->query("
            SELECT id FROM exchanges 
            WHERE (sender_id = :sender_id AND receiver_id = :receiver_id)
               OR (sender_id = :receiver_id AND receiver_id = :sender_id)
            LIMIT 1
        ");
        
        $this->db->bind(':sender_id', $senderId);
        $this->db->bind(':receiver_id', $receiverId);
        
        if ($this->db->single()) {
            return false; // Exchange already exists
        }
        
        // Get matching skills automatically if not provided
        if (!$skillOffered || !$skillWanted) {
            $matchingSkills = $this->getMatchingSkills($senderId, $receiverId);
            $skillOffered = $matchingSkills['offered'] ?? 'general';
            $skillWanted = $matchingSkills['wanted'] ?? 'general';
        }
        
        // Create new exchange request
        $this->db->query("
            INSERT INTO exchanges (
                sender_id, 
                receiver_id, 
                skill_offered, 
                skill_wanted, 
                status, 
                created_at
            ) VALUES (
                :sender_id, 
                :receiver_id, 
                :skill_offered, 
                :skill_wanted, 
                'pending', 
                NOW()
            )
        ");
        
        $this->db->bind(':sender_id', $senderId);
        $this->db->bind(':receiver_id', $receiverId);
        $this->db->bind(':skill_offered', $skillOffered);
        $this->db->bind(':skill_wanted', $skillWanted);
        
        if ($this->db->execute()) {
            // Optionally create a notification
            $this->createExchangeNotification($senderId, $receiverId);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get matching skills between two users
     */
    private function getMatchingSkills($userId1, $userId2) {
        // What user1 teaches that user2 wants to learn
        $this->db->query("
            SELECT us1.skill_name as offered
            FROM user_skills us1
            INNER JOIN user_skills us2 
                ON us1.skill_name = us2.skill_name
            WHERE us1.user_id = :user_id1 
                AND us1.skill_type = 'teach'
                AND us2.user_id = :user_id2
                AND us2.skill_type = 'learn'
            LIMIT 1
        ");
        
        $this->db->bind(':user_id1', $userId1);
        $this->db->bind(':user_id2', $userId2);
        $offered = $this->db->single()->offered ?? null;
        
        // What user1 wants to learn that user2 teaches
        $this->db->query("
            SELECT us1.skill_name as wanted
            FROM user_skills us1
            INNER JOIN user_skills us2 
                ON us1.skill_name = us2.skill_name
            WHERE us1.user_id = :user_id1 
                AND us1.skill_type = 'learn'
                AND us2.user_id = :user_id2
                AND us2.skill_type = 'teach'
            LIMIT 1
        ");
        
        $this->db->bind(':user_id1', $userId1);
        $this->db->bind(':user_id2', $userId2);
        $wanted = $this->db->single()->wanted ?? null;
        
        return [
            'offered' => $offered,
            'wanted' => $wanted
        ];
    }
    
    /**
     * Create a notification for the exchange request
     */
    private function createExchangeNotification($senderId, $receiverId) {
        // Get sender's name
        $this->db->query("SELECT name FROM users WHERE id = :id");
        $this->db->bind(':id', $senderId);
        $sender = $this->db->single();
        
        if (!$sender) return false;
        
        $this->db->query("
            INSERT INTO notifications (
                user_id,
                type,
                title,
                message,
                related_user_id,
                created_at
            ) VALUES (
                :user_id,
                'exchange',
                'New Connection Request',
                :message,
                :related_user_id,
                NOW()
            )
        ");
        
        $this->db->bind(':user_id', $receiverId);
        $this->db->bind(':message', $sender->name . ' wants to connect with you');
        $this->db->bind(':related_user_id', $senderId);
        
        return $this->db->execute();
    }
    
    /**
     * Get all exchange requests for a user
     */
    public function getExchangeRequests($userId) {
        $this->db->query("
            SELECT 
                e.*,
                sender.name as sender_name,
                sender.email as sender_email,
                sender.avatar as sender_avatar,
                receiver.name as receiver_name,
                receiver.email as receiver_email,
                receiver.avatar as receiver_avatar
            FROM exchanges e
            INNER JOIN users sender ON e.sender_id = sender.id
            INNER JOIN users receiver ON e.receiver_id = receiver.id
            WHERE e.receiver_id = :user_id AND e.status = 'pending'
            ORDER BY e.created_at DESC
        ");
        
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }
    
    /**
     * Get all active exchanges (accepted connections)
     */
    public function getActiveExchanges($userId) {
        $this->db->query("
            SELECT 
                e.*,
                CASE 
                    WHEN e.sender_id = :user_id THEN receiver.id
                    ELSE sender.id
                END as partner_id,
                CASE 
                    WHEN e.sender_id = :user_id THEN receiver.name
                    ELSE sender.name
                END as partner_name,
                CASE 
                    WHEN e.sender_id = :user_id THEN receiver.email
                    ELSE sender.email
                END as partner_email,
                CASE 
                    WHEN e.sender_id = :user_id THEN receiver.avatar
                    ELSE sender.avatar
                END as partner_avatar
            FROM exchanges e
            INNER JOIN users sender ON e.sender_id = sender.id
            INNER JOIN users receiver ON e.receiver_id = receiver.id
            WHERE (e.sender_id = :user_id OR e.receiver_id = :user_id)
                AND e.status = 'accepted'
            ORDER BY e.updated_at DESC
        ");
        
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }
    
    /**
     * Accept an exchange request
     */
    public function acceptExchange($exchangeId, $userId) {
        // Verify user is the receiver
        $this->db->query("
            UPDATE exchanges 
            SET status = 'accepted', updated_at = NOW()
            WHERE id = :exchange_id AND receiver_id = :user_id
        ");
        
        $this->db->bind(':exchange_id', $exchangeId);
        $this->db->bind(':user_id', $userId);
        
        if ($this->db->execute()) {
            // Create notification for sender
            $this->createAcceptanceNotification($exchangeId);
            return true;
        }
        
        return false;
    }
    
    /**
     * Reject an exchange request
     */
    public function rejectExchange($exchangeId, $userId) {
        $this->db->query("
            UPDATE exchanges 
            SET status = 'rejected', updated_at = NOW()
            WHERE id = :exchange_id AND receiver_id = :user_id
        ");
        
        $this->db->bind(':exchange_id', $exchangeId);
        $this->db->bind(':user_id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Cancel an exchange request (by sender)
     */
    public function cancelExchange($exchangeId, $userId) {
        $this->db->query("
            DELETE FROM exchanges 
            WHERE id = :exchange_id AND sender_id = :user_id AND status = 'pending'
        ");
        
        $this->db->bind(':exchange_id', $exchangeId);
        $this->db->bind(':user_id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Create notification when exchange is accepted
     */
    private function createAcceptanceNotification($exchangeId) {
        // Get exchange details
        $this->db->query("
            SELECT e.sender_id, u.name as receiver_name
            FROM exchanges e
            INNER JOIN users u ON e.receiver_id = u.id
            WHERE e.id = :exchange_id
        ");
        
        $this->db->bind(':exchange_id', $exchangeId);
        $exchange = $this->db->single();
        
        if (!$exchange) return false;
        
        $this->db->query("
            INSERT INTO notifications (
                user_id,
                type,
                title,
                message,
                related_exchange_id,
                created_at
            ) VALUES (
                :user_id,
                'exchange_accepted',
                'Connection Accepted!',
                :message,
                :exchange_id,
                NOW()
            )
        ");
        
        $this->db->bind(':user_id', $exchange->sender_id);
        $this->db->bind(':message', $exchange->receiver_name . ' accepted your connection request');
        $this->db->bind(':exchange_id', $exchangeId);
        
        return $this->db->execute();
    }
    
    /**
     * Check if connection exists between two users
     */
    public function connectionExists($userId1, $userId2) {
        $this->db->query("
            SELECT id FROM exchanges 
            WHERE ((sender_id = :user_id1 AND receiver_id = :user_id2)
                OR (sender_id = :user_id2 AND receiver_id = :user_id1))
            AND status IN ('pending', 'accepted')
            LIMIT 1
        ");
        
        $this->db->bind(':user_id1', $userId1);
        $this->db->bind(':user_id2', $userId2);
        
        return $this->db->single() !== false;
    }
}