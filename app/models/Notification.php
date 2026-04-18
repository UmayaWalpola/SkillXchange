<?php

class Notification {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function createNotification($data) {
        $title = trim((string)($data['title'] ?? ''));
        if ($title === '') {
            $title = ucwords(str_replace(['_', '-'], ' ', (string)($data['type'] ?? 'notification')));
        }

        $this->db->query("INSERT INTO notifications (user_id, type, title, message, related_user_id, related_exchange_id, is_read, announcement_id, created_at) VALUES (:user_id, :type, :title, :message, :related_user_id, :related_exchange_id, :is_read, :announcement_id, CURRENT_TIMESTAMP)");
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':title', $title);
        $this->db->bind(':message', $data['message']);
        $this->db->bind(':related_user_id', $data['related_user_id'] ?? null);
        $this->db->bind(':related_exchange_id', $data['related_exchange_id'] ?? null);
        $this->db->bind(':is_read', $data['is_read'] ?? 0);
        $this->db->bind(':announcement_id', $data['announcement_id'] ?? null);
        return $this->db->execute();
    }

    public function getUserNotifications($userId, $limit = 50) {
        $this->db->query("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', (int)$limit);
        return $this->db->resultSet();
    }

    public function getUnreadCount($userId) {
        $this->db->query("SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = :user_id AND is_read = 0");
        $this->db->bind(':user_id', $userId);
        $row = $this->db->single();
        return $row ? (int)$row->cnt : 0;
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
