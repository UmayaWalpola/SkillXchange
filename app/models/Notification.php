<?php

class Notification {
    private $db;
    private $notificationColumns = null;

    public function __construct() {
        $this->db = new Database();
    }

    public function createNotification($data) {
        $columns = $this->getNotificationColumns();

        $payload = [];

        if (in_array('user_id', $columns, true)) {
            $payload['user_id'] = $data['user_id'] ?? null;
        }

        if (in_array('type', $columns, true)) {
            $payload['type'] = $data['type'] ?? 'system';
        }

        if (in_array('title', $columns, true)) {
            $payload['title'] = $data['title'] ?? $this->buildTitleFromType($data['type'] ?? 'notification');
        }

        if (in_array('message', $columns, true)) {
            $payload['message'] = $data['message'] ?? '';
        }

        if (in_array('related_user_id', $columns, true)) {
            $payload['related_user_id'] = $data['related_user_id'] ?? $data['actor_user_id'] ?? null;
        }

        if (in_array('related_exchange_id', $columns, true)) {
            $payload['related_exchange_id'] = $data['related_exchange_id'] ?? $data['exchange_id'] ?? null;
        }

        if (in_array('project_id', $columns, true)) {
            $payload['project_id'] = $data['project_id'] ?? null;
        }

        if (in_array('task_id', $columns, true)) {
            $payload['task_id'] = $data['task_id'] ?? null;
        }

        if (in_array('is_read', $columns, true)) {
            $payload['is_read'] = $data['is_read'] ?? 0;
        }

        $insertColumns = array_keys($payload);
        $placeholders = array_map(function ($column) {
            return ':' . $column;
        }, $insertColumns);

        $this->db->query(
            "INSERT INTO notifications (" . implode(', ', $insertColumns) . ")
             VALUES (" . implode(', ', $placeholders) . ")"
        );

        foreach ($payload as $column => $value) {
            $this->db->bind(':' . $column, $value);
        }

        return $this->db->execute();
    }

    private function getNotificationColumns() {
        if ($this->notificationColumns !== null) {
            return $this->notificationColumns;
        }

        $this->db->query("SHOW COLUMNS FROM notifications");
        $rows = $this->db->resultSet();

        $this->notificationColumns = array_map(function ($row) {
            return $row->Field;
        }, $rows);

        return $this->notificationColumns;
    }

    private function buildTitleFromType($type) {
        $label = trim(str_replace('_', ' ', (string)$type));
        if ($label === '') {
            return 'Notification';
        }

        return ucwords($label);
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
