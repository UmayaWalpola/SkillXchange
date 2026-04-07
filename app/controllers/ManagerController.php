<?php
class ManagerController extends Controller {

    private $managerModel;

    public function __construct() {
        require_once '../app/models/Manager.php'; 
        $this->managerModel = new Manager();
    }


    // DASHBOARD

    public function index() {
        $stats = $this->managerModel->getDashboardStats();

        $data = [
            'title' => 'Manager Dashboard',
            'page'  => 'dashboard',
            'stats' => $stats
        ];

        $this->view('managerdashboard/index', $data);
    }


    // ORGANIZATIONS

    public function organizations() {
        $organizations = $this->managerModel->getAllOrganizations();

        $data = [
            'title'         => 'Organizations Management',
            'page'          => 'organizations',
            'organizations' => $organizations,
            'success'       => $_SESSION['success'] ?? '',
            'error'         => $_SESSION['error'] ?? ''
        ];

        unset($_SESSION['success'], $_SESSION['error']);

        $this->view('managerdashboard/organizations', $data);
    }

    // View organization certificate (manager-only)
    public function viewCertificate($orgId = null) {
        if (($_SESSION['role'] ?? null) !== 'manager') {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        $orgId = is_numeric($orgId) ? (int)$orgId : 0;
        if ($orgId <= 0) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        $org = $this->managerModel->getOrganizationById($orgId);
        if (!$org || empty($org->org_cert)) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        // The DB may store a server path (e.g. ../public/uploads/...) or a URL-ish path.
        // To keep this reliable, extract the filename and only serve from uploads/org_certs.
        $stored = str_replace('\\', '/', (string)$org->org_cert);
        $fileName = basename($stored);
        if (empty($fileName) || $fileName === '.' || $fileName === '..') {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        // Basic filename allow-list (prevents traversal / weird characters)
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $fileName)) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        $publicRelative = 'uploads/org_certs/' . $fileName;
        $diskPath = __DIR__ . '/../../public/' . $publicRelative;
        if (!is_file($diskPath)) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        header('Location: ' . URLROOT . '/' . $publicRelative);
        exit;
    }

    // Suspend organization
    public function suspendOrganization() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/manager/organizations');
            exit;
        }

        $orgId     = $_POST['org_id'] ?? null;
        $reason    = trim($_POST['reason'] ?? 'Suspended by manager');
        $managerId = $_SESSION['user_id'] ?? 0;

        if (empty($orgId)) {
            $_SESSION['error'] = 'Organization ID is required';
            header('Location: ' . URLROOT . '/manager/organizations');
            exit;
        }

        if ($this->managerModel->suspendOrganization($orgId, $managerId, $reason)) {
            $_SESSION['success'] = 'Organization suspended successfully';
        } else {
            $_SESSION['error'] = 'Failed to suspend organization';
        }

        header('Location: ' . URLROOT . '/manager/organizations');
        exit;
    }

    // Reactivate organization
    public function reactivateOrganization() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/manager/organizations');
            exit;
        }

        $orgId = $_POST['org_id'] ?? null;

        if (empty($orgId)) {
            $_SESSION['error'] = 'Organization ID is required';
            header('Location: ' . URLROOT . '/manager/organizations');
            exit;
        }

        if ($this->managerModel->reactivateOrganization($orgId)) {
            $_SESSION['success'] = 'Organization reactivated successfully';
        } else {
            $_SESSION['error'] = 'Failed to reactivate organization';
        }

        header('Location: ' . URLROOT . '/manager/organizations');
        exit;
    }

    // USER MANAGEMENT
    public function users() {
        $users = $this->managerModel->getAllAdminUsers();

        $data = [
            'title'   => 'User Management',
            'page'    => 'users',
            'users'   => $users,
            'success' => $_SESSION['success'] ?? '',
            'error'   => $_SESSION['error'] ?? ''
        ];

        unset($_SESSION['success'], $_SESSION['error']);

        $this->view('managerdashboard/users', $data);
    }

    // Add user
    public function addUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/manager/users');
            exit;
        }

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $role     = trim($_POST['role'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($email) || empty($role) || empty($password)) {
            $_SESSION['error'] = 'All fields are required';
            header('Location: ' . URLROOT . '/manager/users');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email format';
            header('Location: ' . URLROOT . '/manager/users');
            exit;
        }

       $result = $this->managerModel->addUser($name, $email, $role, $password);

        if ($result['success']) {
            // send email with credentials
            $mailer = new Mailer();
            $sent   = $mailer->sendNewUserCredentials($name, $email, $password, $role);

            if ($sent) {
                $_SESSION['success'] = 'User added and credentials emailed successfully';
            } else {
                $_SESSION['success'] = 'User added but email could not be sent';
            }
        } else {
            $_SESSION['error'] = $result['message'];
        }

        header('Location: ' . URLROOT . '/manager/users');
        exit;
    }

    // Update user
    public function updateUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/manager/users');
            exit;
        }

        $userId   = trim($_POST['user_id'] ?? '');
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $role     = trim($_POST['role'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($userId) || empty($name) || empty($email) || empty($role)) {
            $_SESSION['error'] = 'All fields except password are required';
            header('Location: ' . URLROOT . '/manager/users');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email format';
            header('Location: ' . URLROOT . '/manager/users');
            exit;
        }

        $result = $this->managerModel->updateUser($userId, $name, $email, $role, $password);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];

        header('Location: ' . URLROOT . '/manager/users');
        exit;
    }

    // Remove user
    public function removeUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/manager/users');
            exit;
        }

        $userId = $_POST['user_id'] ?? null;

        if (empty($userId)) {
            $_SESSION['error'] = 'User ID is required';
            header('Location: ' . URLROOT . '/manager/users');
            exit;
        }

        $result = $this->managerModel->removeUser($userId);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];

        header('Location: ' . URLROOT . '/manager/users');
        exit;
    }


    // ANNOUNCEMENTS
    public function announcements() {
        $announcements = $this->managerModel->getAllAnnouncements();

        $data = [
            'title'         => 'System Announcements',
            'page'          => 'announcements',
            'announcements' => $announcements,
            'success'       => $_SESSION['success'] ?? '',
            'error'         => $_SESSION['error'] ?? ''
        ];

        unset($_SESSION['success'], $_SESSION['error']);
        $this->view('managerdashboard/announcements', $data);
    }

    public function addAnnouncement() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/manager/announcements');
            exit;
        }

        $title     = trim($_POST['title'] ?? '');
        $content   = trim($_POST['content'] ?? '');
        $managerId = $_SESSION['user_id'] ?? 0;

        if (empty($title) || empty($content)) {
            $_SESSION['error'] = 'Title and content are required';
            header('Location: ' . URLROOT . '/manager/announcements');
            exit;
        }

        $announcementId = $this->managerModel->addAnnouncement($title, $content, $managerId);

        if ($announcementId) {
            // notify all active users
            $users = $this->managerModel->getAllUserIds($managerId);
            foreach ($users as $user) {
                $this->managerModel->sendNotification(
                    $user->id,
                    $title,
                    $content
                );
            }
            $_SESSION['success'] = 'Announcement posted and all users notified';
        } else {
            $_SESSION['error'] = 'Failed to post announcement';
        }

        header('Location: ' . URLROOT . '/manager/announcements');
        exit;
    }

            public function updateAnnouncement() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/manager/announcements');
            exit;
        }

        $announcementId = $_POST['announcement_id'] ?? null;
        $title          = trim($_POST['title'] ?? '');
        $content        = trim($_POST['content'] ?? '');
        $managerId      = $_SESSION['user_id'] ?? 0;

        if (empty($announcementId) || empty($title) || empty($content)) {
            $_SESSION['error'] = 'All fields are required';
            header('Location: ' . URLROOT . '/manager/announcements');
            exit;
        }

        if ($this->managerModel->updateAnnouncement($announcementId, $title, $content)) {
            // notify all active users about the updated announcement
            $users = $this->managerModel->getAllUserIds($managerId);
            foreach ($users as $user) {
                $this->managerModel->sendNotification(
                    $user->id,
                    '📢 Updated: ' . $title,
                    $content
                );
            }
            $_SESSION['success'] = 'Announcement updated and all users notified';
        } else {
            $_SESSION['error'] = 'Failed to update announcement';
        }

        header('Location: ' . URLROOT . '/manager/announcements');
        exit;
    }

    
    // FEEDBACK 
    public function feedback() {
        $feedbacks = $this->managerModel->getAllFeedbacks();

        $data = [
            'title'     => 'Platform Feedback',
            'page'      => 'feedback',
            'feedbacks' => $feedbacks
        ];

        $this->view('managerdashboard/feedback', $data);
    }

    // INSIGHTS
    public function insights() {
        $insights = $this->managerModel->getUserInsights();

        $data = [
            'title'    => 'User Insights',
            'page'     => 'insights',
            'insights' => $insights
        ];

        $this->view('managerdashboard/insights', $data);
    }

} 