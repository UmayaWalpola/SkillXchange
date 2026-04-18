<?php
//controller class
class AdminController extends Controller {

    private $adminModel;

    //check if user is logged in and is admin, otherwise redirect to signin or home
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }
        if ($_SESSION['role'] !== 'admin') {
            header('Location: ' . URLROOT . '/home');
            exit;
        }
        // Load Admin model
        $this->adminModel = $this->model('Admin');
    }

    // Default admin dashboard
    public function index() { $this->dashboard(); }

    public function dashboard() {
        $data = [
            'stats'          => $this->adminModel->getAdminStats(),
        ];
        $this->view('admin/admin', $data);
    }

    //user management- get all users, view user details
    public function users() {
        $data = ['users' => $this->adminModel->getAllUsers()];
        $this->view('admin/admin_users', $data);
    }

    //user detail page with suspend/reactivate options, warnings, reports, activity log
    public function viewUser($userId) {
        $user = $this->adminModel->getUserById($userId);
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            header('Location: ' . URLROOT . '/admin/users');
            exit;
        }
        $data = [
            'user'       => $user,
            'skills'     => $this->adminModel->getUserSkills($userId),
            'warnings'   => $this->adminModel->getUserWarnings($userId),
            'reports'    => $this->adminModel->getReportsAboutUser($userId),
            'activities' => $this->adminModel->getUserActivity($userId, 15),
        ];
        $this->view('admin/admin_user_detail', $data);
    }

    public function suspendUser() {
        //only accepts POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admin/users'); exit;
        }
        $userId   = $_POST['user_id']  ?? null;
        $reason   = trim($_POST['reason']   ?? '');
        $duration = $_POST['duration'] ?? 'permanent'; //optional

        if (!$userId || !$reason) {
            $_SESSION['error'] = 'User ID and reason are required';
            header('Location: ' . URLROOT . '/admin/viewUser/' . $userId); exit;
        }

        $expiresAt = null;
        //calculate the expiry date based on duration (e.g. 7 days, 30 days, or permanent)
        if ($duration !== 'permanent') {
            $days      = (int) $duration;
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        }

        if ($this->adminModel->suspendUser($userId, $_SESSION['user_id'], $reason, $expiresAt)) {
            $this->adminModel->logAdminAction(
                //logs admin action
                $_SESSION['user_id'], 'user_suspended', $userId,
                'user', $userId, "Suspended user for: {$reason}",
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            );
            $_SESSION['success'] = 'User suspended successfully';
        } else {
            $_SESSION['error'] = 'Failed to suspend user';
        }
        header('Location: ' . URLROOT . '/admin/viewUser/' . $userId); exit;
    }

    public function reactivateUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admin/users'); exit;
        }
        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            $_SESSION['error'] = 'User ID required';
            header('Location: ' . URLROOT . '/admin/users'); exit;
        }
        if ($this->adminModel->reactivateUser($userId)) {
            $this->adminModel->logAdminAction(
                $_SESSION['user_id'], 'user_activated', $userId,
                'user', $userId, 'Reactivated previously suspended user',
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            );
            $_SESSION['success'] = 'User reactivated successfully';
        } else {
            $_SESSION['error'] = 'Failed to reactivate user';
        }
        header('Location: ' . URLROOT . '/admin/viewUser/' . $userId); exit;
    }

    //Activity Logs
    public function activityLogs() {
        $data = [
            'user_activities' => $this->adminModel->getAllUserActivities(25),
            'admin_actions'   => $this->adminModel->getAllAdminActions(25),
        ];
        $this->view('admin/admin_activity_logs', $data);
    }

    public function reports() {
    $data = ['reports' => $this->adminModel->getProjectMemberReports()];
    $this->view('admin/admin_reports', $data);
}

//issue warning to user based on report, update report status, log admin action, send notification to user
public function warnUser() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . URLROOT . '/admin/reports'); exit;
    }
    $userId   = $_POST['user_id'] ?? null;
    $reportId = $_POST['report_id'] ?? null;
    $reason   = trim($_POST['reason'] ?? '');

    if (!$userId || !$reason) {
        $_SESSION['error'] = 'Missing required fields';
        header('Location: ' . URLROOT . '/admin/reports'); exit;
    }

    if ($this->adminModel->warnUser($userId, $_SESSION['user_id'], $reason)) {
        $this->adminModel->updateProjectReport($reportId, 'warned');

        $db = new Database();
        $db->query(
            "INSERT INTO notifications (user_id, type, title, message, related_user_id, is_read)
             VALUES (:user_id, 'system_warning', 'Official Warning', :message, :related_user_id, 0)"
        );
        $db->bind(':user_id',         $userId);
        $db->bind(':message',         '⚠️ You have received an official warning: ' . $reason);
        $db->bind(':related_user_id', $_SESSION['user_id']);
        $db->execute();

        $this->adminModel->logAdminAction(
            $_SESSION['user_id'], 'user_warned', $userId,
            'user', $userId, "Warned user: {$reason}",
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        );
        $_SESSION['success'] = 'Warning issued successfully';
    } else {
        $_SESSION['error'] = 'Failed to issue warning';
    }
    header('Location: ' . URLROOT . '/admin/reports'); exit;
}

public function dismissReport() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . URLROOT . '/admin/reports'); exit;
    }
    $reportId = $_POST['report_id'] ?? null;
    if ($this->adminModel->updateProjectReport($reportId, 'dismissed')) {
        $_SESSION['success'] = 'Report dismissed';
    } else {
        $_SESSION['error'] = 'Failed to dismiss report';
    }
    header('Location: ' . URLROOT . '/admin/reports'); exit;
}
}