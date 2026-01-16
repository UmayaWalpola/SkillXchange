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

    // /notifications -> full inbox
    public function index()
    {
        $userId = $_SESSION['user_id'];
        $notifications = $this->notificationModel->getUserNotifications($userId, 50);

        $data = [
            'title' => 'Notifications',
            'notifications' => $notifications
        ];

        $this->view('notifications/index', $data);
    }

    // /notifications/read/{id}
    public function markAsRead($id = null)
    {
        if (!$id) {
            header('Location: ' . URLROOT . '/notifications');
            exit();
        }
        $userId = $_SESSION['user_id'];
        $this->notificationModel->markAsRead($id, $userId);
        header('Location: ' . URLROOT . '/notifications');
        exit();
    }

    // /notifications/readAll
    public function markAllAsRead()
    {
        $userId = $_SESSION['user_id'];
        $this->notificationModel->markAllAsRead($userId);
        header('Location: ' . URLROOT . '/notifications');
        exit();
    }

}

?>
