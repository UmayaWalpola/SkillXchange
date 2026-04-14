<?php

class Notification {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // ============================================================
    // CREATE NOTIFICATION (Backend to generate notification - Task 2)
    // Assigned to: Kithsara
    // ============================================================
    public function createNotification($data) {
        // Step 1: Write SQL query to insert new notification details
        $this->db->query("INSERT INTO notifications (user_id, type, message, project_id, task_id, is_read) 
                          VALUES (:user_id, :type, :message, :project_id, :task_id, :is_read)");
        
        // Step 2: Bind variables from Controller's array to secure placeholders
        $this->db->bind(':user_id', $data['user_id']); // Who gets it?
        $this->db->bind(':type', $data['type']); // Like 'task_assigned', 'account_ban'
        $this->db->bind(':message', $data['message']); // E.g., You have a new task!
        $this->db->bind(':project_id', $data['project_id'] ?? null); // Optional link to project
        $this->db->bind(':task_id', $data['task_id'] ?? null); // Optional link to task
        $this->db->bind(':is_read', $data['is_read'] ?? 0); // Default to unread (0)
        
        return $this->db->execute();
    }

    // ============================================================
    // GET ALL NOTIFICATIONS (For user Notification Center UI)
    // ============================================================
    public function getUserNotifications($userId, $limit = 50) {
        // Fetch the newest notifications first (ORDER BY created_at DESC)
        $this->db->query("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', (int)$limit);
        return $this->db->resultSet(); // Return array of notification records
    }

    // ============================================================
    // DISPLAY UNREAD COUNT on Nav Bar (bell icon) (Assigned to: Kithsara - Task 4)
    // ============================================================
    public function getUnreadCount($userId) {
        // Simple SQL COUNT query asking for records where is_read = 0
        $this->db->query("SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = :user_id AND is_read = 0");
        $this->db->bind(':user_id', $userId);
        $row = $this->db->single(); // Get single row
        
        return $row ? (int)$row->cnt : 0; // Return number (1, 5, etc)
    }

    public function markAsRead($id, $userId) {
        $this->db->query("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    public function markAllAsRead($userId) {
        $this->db->query("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

}

?>
