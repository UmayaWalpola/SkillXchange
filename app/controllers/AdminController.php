<?php
class AdminController extends Controller {

    private $adminModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }
        if ($_SESSION['role'] !== 'admin') {
            header('Location: ' . URLROOT . '/home');
            exit;
        }
        $this->adminModel = $this->model('Admin');
    }

    // ── Dashboard ──────────────────────────────────────────
    public function index() { $this->dashboard(); }

    public function dashboard() {
        $data = [
            'stats'          => $this->adminModel->getAdminStats(),
            'recent_users'   => $this->adminModel->getRecentUsers(5),
            'recent_actions' => $this->adminModel->getRecentAdminActions(5),
        ];
        $this->view('admin/admin', $data);
    }

    // ── User Management ────────────────────────────────────
    public function users() {
        $data = ['users' => $this->adminModel->getAllUsers()];
        $this->view('admin/admin_users', $data);
    }

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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admin/users'); exit;
        }
        $userId   = $_POST['user_id']  ?? null;
        $reason   = trim($_POST['reason']   ?? '');
        $duration = $_POST['duration'] ?? 'permanent';

        if (!$userId || !$reason) {
            $_SESSION['error'] = 'User ID and reason are required';
            header('Location: ' . URLROOT . '/admin/viewUser/' . $userId); exit;
        }

        $expiresAt = null;
        if ($duration !== 'permanent') {
            $days      = (int) $duration;
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        }

        if ($this->adminModel->suspendUser($userId, $_SESSION['user_id'], $reason, $expiresAt)) {
            $this->adminModel->logAdminAction(
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

    // ── Activity Logs ──────────────────────────────────────
    public function activityLogs() {
        $data = [
            'user_activities' => $this->adminModel->getAllUserActivities(100),
            'admin_actions'   => $this->adminModel->getAllAdminActions(100),
        ];
        $this->view('admin/admin_activity_logs', $data);
    }
}