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
        // Check if exchange already exists (any status)
        $this->db->query("
            SELECT id, status FROM exchanges 
            WHERE (requester_id = :requester_id AND receiver_id = :receiver_id)
               OR (requester_id = :receiver_id AND receiver_id = :requester_id)
            LIMIT 1
        ");
        
        $this->db->bind(':requester_id', $senderId);
        $this->db->bind(':receiver_id', $receiverId);
        
        $existing = $this->db->single();
        if ($existing) {
            error_log("Exchange already exists with status: " . $existing->status);
            // If cancelled, allow re-request
            if ($existing->status === 'cancelled') {
                $this->db->query("DELETE FROM exchanges WHERE id = :id");
                $this->db->bind(':id', $existing->id);
                $this->db->execute();
            } else {
                return false; // Exchange already exists
            }
        }
        
        // Get matching skills automatically if not provided
        if (!$skillOffered || !$skillWanted) {
            $matchingSkills = $this->getMatchingSkills($senderId, $receiverId);
            $skillOffered = $matchingSkills['offered'] ?? 'general';
            $skillWanted = $matchingSkills['wanted'] ?? 'general';
        }
        
        error_log("Creating exchange: sender=$senderId, receiver=$receiverId, offered=$skillOffered, wanted=$skillWanted");
        
        // Create new exchange request
        $this->db->query("
            INSERT INTO exchanges (
                requester_id, 
                receiver_id, 
                skill_offered, 
                skill_wanted, 
                status, 
                created_at
            ) VALUES (
                :requester_id, 
                :receiver_id, 
                :skill_offered, 
                :skill_wanted, 
                'pending', 
                NOW()
            )
        ");
        
        $this->db->bind(':requester_id', $senderId);
        $this->db->bind(':receiver_id', $receiverId);
        $this->db->bind(':skill_offered', $skillOffered);
        $this->db->bind(':skill_wanted', $skillWanted);
        
        if ($this->db->execute()) {
            error_log("Exchange created successfully!");
            // Create a notification
            $this->createExchangeNotification($senderId, $receiverId);
            return true;
        }
        
        error_log("Failed to create exchange!");
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
        $result = $this->db->single();
        $offered = $result ? $result->offered : null;
        
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
        $result = $this->db->single();
        $wanted = $result ? $result->wanted : null;
        
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
        $this->db->query("SELECT username FROM users WHERE id = :id");
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
        $this->db->bind(':message', $sender->username . ' wants to connect with you');
        $this->db->bind(':related_user_id', $senderId);
        
        return $this->db->execute();
    }
    
    /**
     * Get all exchange requests for a user (FIXED)
     */
    public function getExchangeRequests($userId) {
        $this->db->query("
            SELECT 
                e.id,
                e.requester_id,
                e.receiver_id,
                e.skill_offered,
                e.skill_wanted,
                e.status,
                e.created_at,
                requester.username as sender_name,
                requester.email as sender_email,
                requester.profile_picture as sender_avatar
            FROM exchanges e
            INNER JOIN users requester ON e.requester_id = requester.id
            WHERE e.receiver_id = :user_id AND e.status = 'pending'
            ORDER BY e.created_at DESC
        ");
        
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }
    
    /**
     * Get all active exchanges (accepted connections)
     * CHANGED: 'accepted' → 'active'
     */
    public function getActiveExchanges($userId) {
        $this->db->query("
            SELECT 
                e.*,
                CASE 
                    WHEN e.requester_id = :user_id THEN e.receiver_id
                    ELSE e.requester_id
                END as partner_id,
                CASE 
                    WHEN e.requester_id = :user_id THEN receiver.username
                    ELSE requester.username
                END as partner_name,
                CASE 
                    WHEN e.requester_id = :user_id THEN receiver.email
                    ELSE requester.email
                END as partner_email,
                CASE 
                    WHEN e.requester_id = :user_id THEN receiver.profile_picture
                    ELSE requester.profile_picture
                END as partner_avatar
            FROM exchanges e
            INNER JOIN users requester ON e.requester_id = requester.id
            INNER JOIN users receiver ON e.receiver_id = receiver.id
            WHERE (e.requester_id = :user_id OR e.receiver_id = :user_id)
                AND e.status = 'active'
            ORDER BY e.created_at DESC
        ");
        
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }
    
    /**
     * Accept an exchange request
     * CHANGED: status 'accepted' → 'active'
     */
    public function acceptExchange($exchangeId, $userId) {
        // Verify user is the receiver
        $this->db->query("
            SELECT * FROM exchanges 
            WHERE id = :exchange_id AND receiver_id = :user_id AND status = 'pending'
        ");
        $this->db->bind(':exchange_id', $exchangeId);
        $this->db->bind(':user_id', $userId);
        $exchange = $this->db->single();
        
        if (!$exchange) {
            error_log("Accept Exchange Failed: Exchange not found or not pending");
            return false;
        }
        
        // Update status to active (not 'accepted')
        $this->db->query("
            UPDATE exchanges 
            SET status = 'active', created_at = NOW()
            WHERE id = :exchange_id
        ");
        $this->db->bind(':exchange_id', $exchangeId);
        
        if ($this->db->execute()) {
            // Create chat between the two users
            $chatCreated = $this->createChatForExchange($exchange->requester_id, $exchange->receiver_id);
            
            // Create notification for sender
            $this->createAcceptanceNotification($exchangeId, $exchange->requester_id, $userId);
            
            error_log("Exchange accepted successfully. Chat created: " . ($chatCreated ? 'yes' : 'no'));
            return true;
        }
        
        error_log("Failed to update exchange status");
        return false;
    }

    /**
     * Create a chat when exchange is accepted
     */
    private function createChatForExchange($userId1, $userId2) {
        // Check if chat already exists
        $this->db->query("
            SELECT id FROM chats 
            WHERE (user1_id = :user1 AND user2_id = :user2)
               OR (user1_id = :user2 AND user2_id = :user1)
            LIMIT 1
        ");
        $this->db->bind(':user1', $userId1);
        $this->db->bind(':user2', $userId2);
        
        if ($this->db->single()) {
            error_log("Chat already exists between users $userId1 and $userId2");
            return true; // Chat already exists, which is fine
        }
        
        // Create new chat
        $this->db->query("
            INSERT INTO chats (user1_id, user2_id, created_at)
            VALUES (:user1, :user2, NOW())
        ");
        $this->db->bind(':user1', $userId1);
        $this->db->bind(':user2', $userId2);
        
        $result = $this->db->execute();
        error_log("Creating new chat between $userId1 and $userId2: " . ($result ? 'success' : 'failed'));
        
        return $result;
    }
    
    /**
     * Reject an exchange request
     * CHANGED: Uses 'cancelled' instead of 'rejected'
     */
    public function rejectExchange($exchangeId, $userId) {
        $this->db->query("
            UPDATE exchanges 
            SET status = 'cancelled', created_at = NOW()
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
            WHERE id = :exchange_id AND requester_id = :user_id AND status = 'pending'
        ");
        
        $this->db->bind(':exchange_id', $exchangeId);
        $this->db->bind(':user_id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Create notification when exchange is accepted
     */
    private function createAcceptanceNotification($exchangeId, $requesterId, $receiverId) {
        // Get receiver's name
        $this->db->query("SELECT username FROM users WHERE id = :id");
        $this->db->bind(':id', $receiverId);
        $receiver = $this->db->single();
        
        if (!$receiver) return false;
        
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
                'exchange_accepted',
                'Connection Accepted!',
                :message,
                :related_user_id,
                NOW()
            )
        ");
        
        $this->db->bind(':user_id', $requesterId);
        $this->db->bind(':message', $receiver->username . ' accepted your connection request');
        $this->db->bind(':related_user_id', $receiverId);
        
        return $this->db->execute();
    }
    
    /**
     * Check if connection exists between two users
     * CHANGED: Checks for 'pending' and 'active' (not 'accepted')
     */
    public function connectionExists($userId1, $userId2) {
        $this->db->query("
            SELECT id FROM exchanges 
            WHERE ((requester_id = :user_id1 AND receiver_id = :user_id2)
                OR (requester_id = :user_id2 AND receiver_id = :user_id1))
            AND status IN ('pending', 'active')
            LIMIT 1
        ");
        
        $this->db->bind(':user_id1', $userId1);
        $this->db->bind(':user_id2', $userId2);
        
        return $this->db->single() !== false;
    }
}
