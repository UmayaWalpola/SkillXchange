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

    // ============================================================
    // VIEW ALL NOTIFICATIONS PAGE (Assigned to: Ibrahim / Kithsara)
    // Load the Notification Center UI
    // ============================================================
    public function index()
    {
        // Step 1: Find running active user's ID
        $userId = $_SESSION['user_id'];
        
        // Step 2: Grab the latest 50 notifications from the database
        $notifications = $this->notificationModel->getUserNotifications($userId, 50);

        // Step 3: Bundle data and send to View ('notifications/index.php')
        $data = [
            'title' => 'Notifications',
            'notifications' => $notifications
        ];

        $this->view('notifications/index', $data);
    }

    // ============================================================
    // MARK SINGLE NOTIFICATION AS READ 
    // /notifications/read/{id}
    // ============================================================
    public function markAsRead($id = null)
    {
        // Step 1: Make sure ID is set
        if (!$id) {
            header('Location: ' . URLROOT . '/notifications');
            exit();
        }
        
        // Step 2: Call the Model function to change 'is_read' from 0 (Unread) to 1 (Read)
        $userId = $_SESSION['user_id'];
        $this->notificationModel->markAsRead($id, $userId);
        
        // Step 3: Reload the page
        header('Location: ' . URLROOT . '/notifications');
        exit();
    }

    // ============================================================
    // MARK ALL AS READ (Bulk update)
    // /notifications/readAll
    // ============================================================
    public function markAllAsRead()
    {
        $userId = $_SESSION['user_id'];
        
        // Run update query on ALL unread records for this user
        $this->notificationModel->markAllAsRead($userId);
        
        header('Location: ' . URLROOT . '/notifications');
        exit();
    }

}

?>
