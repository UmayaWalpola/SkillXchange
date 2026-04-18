<?php

class NotificationController extends Controller
{
    private $notificationModel;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit();
        }
        $this->notificationModel = $this->model('Notification');
    }

    // Show the full notification inbox for the logged-in user.
    public function index()
    {
        $userId = $_SESSION['user_id'];
        $notifications = $this->notificationModel->getUserNotifications($userId, 50);
        $unreadCount = $this->notificationModel->getUnreadCount($userId);

        $data = [
            'title' => 'Notifications',
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ];

        $this->view('notifications/index', $data);
    }

    // Mark one notification as read, then go back to the inbox.
    public function markAsRead($id = null)
    {
        $this->read($id);
    }

    // Backward-compatible alias used by the UI links.
    public function read($id = null)
    {
        if (!$id) {
            header('Location: ' . URLROOT . '/notifications');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $notification = $this->notificationModel->getNotificationById($id, $userId);
        $this->notificationModel->markAsRead($id, $userId);

        $targetUrl = URLROOT . '/notifications';
        if ($notification) {
            if (!empty($notification->target_url)) {
                $targetUrl = $notification->target_url;
            } else {
                $targetUrl = $this->resolveTargetUrl($notification);
            }
        }

        header('Location: ' . $targetUrl);
        exit();
    }

    // Mark all notifications as read for this user.
    public function markAllAsRead()
    {
        $this->readAll();
    }

    // Backward-compatible alias used by the notifications view.
    public function readAll()
    {
        $userId = $_SESSION['user_id'];
        $this->notificationModel->markAllAsRead($userId);
        header('Location: ' . URLROOT . '/notifications');
        exit();
    }

    private function resolveTargetUrl($notification)
    {
        $type = $notification->type ?? '';
        $userId = (int)($notification->user_id ?? 0);

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

        return URLROOT . '/notifications';
    }

}

?>
