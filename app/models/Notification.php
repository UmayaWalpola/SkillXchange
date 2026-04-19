<?php

class Notification {
    private $db;
    private $supportsTargetColumns = false;

    public function __construct() {
        $this->db = new Database();
        $this->supportsTargetColumns = $this->hasTargetColumns();
    }

    // Save one new notification row.
    public function createNotification($data) {
        $title = trim((string)($data['title'] ?? ''));
        if ($title === '') {
            $title = ucwords(str_replace(['_', '-'], ' ', (string)($data['type'] ?? 'notification')));
        }

        $targetUrl = trim((string)($data['target_url'] ?? $data['link'] ?? ''));
        if ($targetUrl === '') {
            $targetUrl = $this->resolveTargetUrl($data);
        }

        $entityType = trim((string)($data['entity_type'] ?? '')) ?: null;
        $entityId = isset($data['entity_id']) && $data['entity_id'] !== '' ? (int)$data['entity_id'] : null;

        if ($this->supportsTargetColumns) {
            $this->db->query("INSERT INTO notifications (user_id, type, title, message, target_url, entity_type, entity_id, actor_user_id, related_user_id, related_exchange_id, is_read, announcement_id, created_at) VALUES (:user_id, :type, :title, :message, :target_url, :entity_type, :entity_id, :actor_user_id, :related_user_id, :related_exchange_id, :is_read, :announcement_id, CURRENT_TIMESTAMP)");
        } else {
            $this->db->query("INSERT INTO notifications (user_id, type, title, message, related_user_id, related_exchange_id, is_read, announcement_id, created_at) VALUES (:user_id, :type, :title, :message, :related_user_id, :related_exchange_id, :is_read, :announcement_id, CURRENT_TIMESTAMP)");
        }
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':title', $title);
        $this->db->bind(':message', $data['message']);
        if ($this->supportsTargetColumns) {
            $this->db->bind(':target_url', $targetUrl !== '' ? $targetUrl : null);
            $this->db->bind(':entity_type', $entityType);
            $this->db->bind(':entity_id', $entityId);
            $this->db->bind(':actor_user_id', $data['actor_user_id'] ?? $data['sender_id'] ?? null);
        }
        $this->db->bind(':related_user_id', $data['related_user_id'] ?? null);
        $this->db->bind(':related_exchange_id', $data['related_exchange_id'] ?? null);
        $this->db->bind(':is_read', $data['is_read'] ?? 0);
        $this->db->bind(':announcement_id', $data['announcement_id'] ?? null);
        return $this->db->execute();
    }

    // Get the latest notifications for the inbox screen.
    public function getUserNotifications($userId, $limit = 50) {
        $this->db->query("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', (int)$limit);
        return $this->db->resultSet();
    }

    public function getNotificationById($notificationId, $userId) {
        $this->db->query("SELECT * FROM notifications WHERE id = :id AND user_id = :user_id LIMIT 1");
        $this->db->bind(':id', (int)$notificationId);
        $this->db->bind(':user_id', (int)$userId);
        return $this->db->single();
    }

    // Count how many notifications are still unread.
    public function getUnreadCount($userId) {
        $this->db->query("SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = :user_id AND is_read = 0");
        $this->db->bind(':user_id', $userId);
        $row = $this->db->single();
        return $row ? (int)$row->cnt : 0;
    }

    // Mark one notification as read.
    public function markAsRead($id, $userId) {
        $this->db->query("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    // Mark every unread notification as read.
    public function markAllAsRead($userId) {
        $this->db->query("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    private function resolveTargetUrl($data) {
        $type = (string)($data['type'] ?? '');
        $userId = isset($data['user_id']) ? (int)$data['user_id'] : 0;
        $projectId = isset($data['project_id']) ? (int)$data['project_id'] : 0;
        $taskId = isset($data['task_id']) ? (int)$data['task_id'] : 0;

        if (in_array($type, ['task_assigned', 'task_update', 'task_removed', 'deadline_due_soon', 'deadline_due_today', 'deadline_warning', 'buckx_reward'], true)) {
            return URLROOT . '/task/userTasks';
        }

        if ($type === 'feedback_received' && $userId > 0) {
            return URLROOT . '/Feedback/index/' . $userId;
        }

        if ($type === 'feedback_report') {
            return URLROOT . '/FeedbackReport/index';
        }

        if (in_array($type, ['warning', 'system_warning', 'account_ban'], true)) {
            return URLROOT . '/notifications';
        }

        if (in_array($type, ['application_accepted', 'application_rejected'], true)) {
            return URLROOT . '/organization/applications';
        }

        if (($projectId > 0 || $taskId > 0) && in_array($type, ['task_assigned', 'task_update', 'task_removed'], true)) {
            return URLROOT . '/task/userTasks';
        }

        return URLROOT . '/notifications';
    }

    private function hasTargetColumns() {
        $this->db->query("SHOW COLUMNS FROM notifications LIKE 'target_url'");
        $column = $this->db->single();
        return !empty($column);
    }

}

?>
