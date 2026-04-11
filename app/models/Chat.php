<?php
// app/models/Chat.php


class Chat extends Database {
  
   private $db;
  
   public function __construct() {
       $this->db = new Database();
   }
  
   /**
    * Get or create a chat between two users (with optional skill context)
    */
   public function getOrCreateChat($userId1, $userId2, $skillName = null, $direction = null) {
       // Check if chat exists
       $query = "
           SELECT id FROM chats
           WHERE ((user1_id = :user1 AND user2_id = :user2)
              OR (user1_id = :user2 AND user2_id = :user1))
       ";
      
       // Add skill context filters if provided
       if ($skillName && $direction) {
           $query .= " AND skill_context = :skill AND exchange_direction = :direction";
       } else {
           $query .= " AND skill_context IS NULL";
       }
      
       $query .= " LIMIT 1";
      
       $this->db->query($query);
       $this->db->bind(':user1', $userId1);
       $this->db->bind(':user2', $userId2);
      
       if ($skillName && $direction) {
           $this->db->bind(':skill', $skillName);
           $this->db->bind(':direction', $direction);
       }
      
       $chat = $this->db->single();
       if ($chat) {
           return $chat->id;
       }
      
       // Create new chat
       $this->db->query("
           INSERT INTO chats (user1_id, user2_id, skill_context, exchange_direction, created_at)
           VALUES (:user1, :user2, :skill, :direction, NOW())
       ");
       $this->db->bind(':user1', $userId1);
       $this->db->bind(':user2', $userId2);
       $this->db->bind(':skill', $skillName);
       $this->db->bind(':direction', $direction);
       $this->db->execute();
      
       return $this->db->lastInsertId();
   }
  
   /**
    * Get all chats for a user with detailed information
    */
   public function getUserChats($userId) {
       $this->db->query("
           SELECT
               c.id as chat_id,
               c.skill_context,
               c.exchange_direction,
               CASE
                   WHEN c.user1_id = :user_id THEN c.user2_id
                   ELSE c.user1_id
               END as partner_id,
               CASE
                   WHEN c.user1_id = :user_id THEN u2.username
                   ELSE u1.username
               END as partner_name,
               CASE
                   WHEN c.user1_id = :user_id THEN u2.profile_picture
                   ELSE u1.profile_picture
               END as partner_avatar,
               cm.message as last_message,
               COALESCE(cm.created_at, c.created_at) as last_message_time,
               (SELECT COUNT(*) FROM chat_messages
                WHERE chat_id = c.id
                AND sender_id != :user_id
                AND read_status = 0) as unread_count
           FROM chats c
           INNER JOIN users u1 ON c.user1_id = u1.id
           INNER JOIN users u2 ON c.user2_id = u2.id
           LEFT JOIN (
               SELECT chat_id, message, created_at
               FROM chat_messages cm1
               WHERE id = (
                   SELECT MAX(id)
                   FROM chat_messages cm2
                   WHERE cm2.chat_id = cm1.chat_id
               )
           ) cm ON c.id = cm.chat_id
           WHERE (c.user1_id = :user_id OR c.user2_id = :user_id)
           ORDER BY last_message_time DESC
       ");
      
       $this->db->bind(':user_id', $userId);
       return $this->db->resultSet();
   }
  
   /**
    * Format chat data for display (helper method)
    */
   public function formatChatsForDisplay($chats, $userId) {
       $formatted = [];
      
       foreach ($chats as $row) {
           // Build skill context label
           $skillLabel = '';
           if ($row->skill_context) {
               $isTeaching = ($row->exchange_direction === 'user1_teaches' && $row->partner_id > $userId) ||
                             ($row->exchange_direction === 'user2_teaches' && $row->partner_id < $userId);
               $direction = $isTeaching ? 'Teaching' : 'Learning';
               $skillLabel = " - {$direction} " . ucwords(str_replace('-', ' ', $row->skill_context));
           }
          
           $formatted[] = [
               'id' => $row->chat_id,
               'partner_id' => $row->partner_id,
               'name' => $row->partner_name,
               'display_name' => $row->partner_name . $skillLabel,
               'avatar' => $row->partner_avatar ?? strtoupper(substr($row->partner_name ?? 'U', 0, 2)),
               'lastMessage' => $row->last_message ?? 'No messages yet',
               'time' => $this->timeAgo($row->last_message_time),
               'unread' => $row->unread_count > 0,
               'unreadCount' => $row->unread_count ?? 0,
               'online' => false,
               'skill_context' => $row->skill_context,
               'direction' => $row->exchange_direction
           ];
       }
      
       return $formatted;
   }
  
   /**
    * Get messages for a specific chat
    */
   public function getChatMessages($chatId) {
       $this->db->query("
           SELECT
               m.id,
               m.message,
               m.created_at,
               m.sender_id,
               u.username AS sender_name,
               u.profile_picture AS sender_profile_pic
           FROM chat_messages m
           JOIN users u ON m.sender_id = u.id
           WHERE m.chat_id = :chat_id
           ORDER BY m.created_at ASC, m.id ASC
       ");
       $this->db->bind(':chat_id', $chatId);
       return $this->db->resultSet();
   }
  
   /**
    * Send a message
    */
   public function sendMessage($chatId, $senderId, $message) {
       $this->db->query("
           INSERT INTO chat_messages (chat_id, sender_id, message, created_at)
           VALUES (:chat_id, :sender_id, :message, NOW())
       ");
       $this->db->bind(':chat_id', $chatId);
       $this->db->bind(':sender_id', $senderId);
       $this->db->bind(':message', $message);
      
       return $this->db->execute();
   }
  
   /**
    * Mark messages as read
    */
   public function markAsRead($chatId, $userId) {
       $this->db->query("
           UPDATE chat_messages
           SET read_status = 1
           WHERE chat_id = :chat_id
           AND sender_id != :user_id
           AND read_status = 0
       ");
       $this->db->bind(':chat_id', $chatId);
       $this->db->bind(':user_id', $userId);
       return $this->db->execute();
   }
  
   /**
    * Verify user has access to chat
    */
   public function userHasAccess($chatId, $userId) {
       $this->db->query("
           SELECT * FROM chats
           WHERE id = :chat_id
           AND (user1_id = :user_id OR user2_id = :user_id)
       ");
       $this->db->bind(':chat_id', $chatId);
       $this->db->bind(':user_id', $userId);
      
       return $this->db->single() !== false;
   }
  
   /**
    * Get available skill contexts for a chat between two users
    */
   public function getAvailableSkillContexts($userId1, $userId2) {
       $this->db->query("
           SELECT
               teach_match.skill_name as can_teach,
               learn_match.skill_name as can_learn
           FROM users u
           LEFT JOIN user_skills teach_match
               ON teach_match.user_id = :user1
               AND teach_match.skill_type = 'teach'
           LEFT JOIN user_skills their_learn
               ON their_learn.user_id = :user2
               AND their_learn.skill_type = 'learn'
               AND their_learn.skill_name = teach_match.skill_name
           LEFT JOIN user_skills learn_match
               ON learn_match.user_id = :user1
               AND learn_match.skill_type = 'learn'
           LEFT JOIN user_skills their_teach
               ON their_teach.user_id = :user2
               AND their_teach.skill_type = 'teach'
               AND their_teach.skill_name = learn_match.skill_name
           WHERE u.id = :user2
           AND (teach_match.skill_name IS NOT NULL OR learn_match.skill_name IS NOT NULL)
       ");
      
       $this->db->bind(':user1', $userId1);
       $this->db->bind(':user2', $userId2);
      
       return $this->db->resultSet();
   }
  
   /**
    * Time ago helper
    */
   private function timeAgo($timestamp) {
       $time = strtotime($timestamp);
       $diff = time() - $time;
      
       if ($diff < 60) return 'just now';
       if ($diff < 3600) return floor($diff / 60) . 'm ago';
       if ($diff < 86400) return floor($diff / 3600) . 'h ago';
       if ($diff < 604800) return floor($diff / 86400) . 'd ago';
       return date('M j', $time);
   }
}
