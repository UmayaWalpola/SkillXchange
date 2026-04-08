<?php
class AdminController extends Controller {

    private $adminModel;

    public function __construct() {
        // Check if user is logged in and is admin
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

    // ========================================
    // DASHBOARD & MAIN VIEWS
    // ========================================

    // Default method - show admin dashboard
    public function index() {
        $this->dashboard();
    }

    // Admin Dashboard
    public function dashboard() {
        // Get statistics
        $stats = $this->adminModel->getAdminStats();
        
        // Get recent users (limit 5 for dashboard)
        $recentUsers = $this->adminModel->getRecentUsers(5);
        
        // Get popular skills
        $popularSkills = $this->adminModel->getPopularSkills(6);
        
        // Get recent reports (limit 5 for dashboard)
        $recentReports = $this->adminModel->getRecentReports(5);
        
        // Get recent admin actions
        $recentActions = $this->adminModel->getRecentAdminActions(5);

        $data = [
            'stats' => $stats,
            'recent_users' => $recentUsers,
            'popular_skills' => $popularSkills,
            'recent_reports' => $recentReports,
            'recent_actions' => $recentActions
        ];

        $this->view('users/admin', $data);
    }

    // ========================================
    // USER MANAGEMENT
    // ========================================

    // View all users
    public function users() {
        $users = $this->adminModel->getAllUsers();
        $data = ['users' => $users];
        $this->view('users/admin_users', $data);
    }

    // View single user details
    public function viewUser($userId) {
        // Get user details
        $user = $this->adminModel->getUserById($userId);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            header('Location: ' . URLROOT . '/admin/users');
            exit;
        }

        // Get user's skills
        $skills = $this->adminModel->getUserSkills($userId);

        // Get user's warnings
        $warnings = $this->adminModel->getUserWarnings($userId);

        // Get user's reports (as reported user)
        $reports = $this->adminModel->getReportsAboutUser($userId);

        // Get user activity
        $activities = $this->adminModel->getUserActivity($userId, 20);

        $data = [
            'user' => $user,
            'skills' => $skills,
            'warnings' => $warnings,
            'reports' => $reports,
            'activities' => $activities
        ];

        $this->view('users/admin_user_detail', $data);
    }

    // Suspend user
    public function suspendUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admin/users');
            exit;
        }

        $userId = $_POST['user_id'] ?? null;
        $reason = $_POST['reason'] ?? '';
        $duration = $_POST['duration'] ?? 'permanent';
        
        if (!$userId || !$reason) {
            $_SESSION['error'] = 'Missing required fields';
            header('Location: ' . URLROOT . '/admin/users');
            exit;
        }

        // Calculate expiration date
        $expiresAt = null;
        if ($duration !== 'permanent') {
            $days = (int)str_replace('days', '', $duration);
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        }

        // Suspend user
        if ($this->adminModel->suspendUser($userId, $_SESSION['user_id'], $reason, $expiresAt)) {
            // Log admin action
            $this->adminModel->logAdminAction(
                $_SESSION['user_id'],
                'user_suspended',
                $userId,
                'user',
                $userId,
                "Suspended user for: {$reason}",
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            );

            $_SESSION['success'] = 'User suspended successfully';
        } else {
            $_SESSION['error'] = 'Failed to suspend user';
        }

        header('Location: ' . URLROOT . '/admin/viewUser/' . $userId);
        exit;
    }

    // Activate user (remove suspension)
    public function activateUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admin/users');
            exit;
        }

        $userId = $_POST['user_id'] ?? null;
        
        if (!$userId) {
            $_SESSION['error'] = 'User ID required';
            header('Location: ' . URLROOT . '/admin/users');
            exit;
        }

        // Activate user
        if ($this->adminModel->activateUser($userId)) {
            // Log admin action
            $this->adminModel->logAdminAction(
                $_SESSION['user_id'],
                'user_activated',
                $userId,
                'user',
                $userId,
                "Activated previously suspended user",
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            );

            $_SESSION['success'] = 'User activated successfully';
        } else {
            $_SESSION['error'] = 'Failed to activate user';
        }

        header('Location: ' . URLROOT . '/admin/viewUser/' . $userId);
        exit;
    }

    // Delete/Ban user permanently
    public function deleteUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admin/users');
            exit;
        }

        $userId = $_POST['user_id'] ?? null;
        $reason = $_POST['reason'] ?? '';
        
        if (!$userId || !$reason) {
            $_SESSION['error'] = 'Missing required fields';
            header('Location: ' . URLROOT . '/admin/users');
            exit;
        }

        // Log before deletion
        $this->adminModel->logAdminAction(
            $_SESSION['user_id'],
            'user_banned',
            $userId,
            'user',
            $userId,
            "Permanently banned user for: {$reason}",
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        );

        // Delete user
        if ($this->adminModel->deleteUser($userId)) {
            $_SESSION['success'] = 'User permanently removed';
            header('Location: ' . URLROOT . '/admin/users');
        } else {
            $_SESSION['error'] = 'Failed to delete user';
            header('Location: ' . URLROOT . '/admin/viewUser/' . $userId);
        }
        exit;
    }

    // ========================================
    // REPORTS MANAGEMENT
    // ========================================

    // View all reports
    public function reports() {
        $reports = $this->adminModel->getAllReports();
        $data = ['reports' => $reports];
        $this->view('users/admin_reports', $data);
    }

    // View single report details
    public function viewReport($reportId) {
        // Get report details
        $report = $this->adminModel->getReportById($reportId);
        
        if (!$report) {
            $_SESSION['error'] = 'Report not found';
            header('Location: ' . URLROOT . '/admin/reports');
            exit;
        }

        // Get reported user's warnings
        $userWarnings = $this->adminModel->getUserWarnings($report->reported_user_id);

        // Get other reports about this user
        $otherReports = $this->adminModel->getOtherReportsAboutUser($report->reported_user_id, $reportId);

        $data = [
            'report' => $report,
            'user_warnings' => $userWarnings,
            'other_reports' => $otherReports
        ];

        $this->view('users/admin_report_detail', $data);
    }

    // Send warning to user
    public function sendWarning() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admin/reports');
            exit;
        }

        $userId = $_POST['user_id'] ?? null;
        $reportId = $_POST['report_id'] ?? null;
        $warningType = $_POST['warning_type'] ?? 'minor';
        $reason = $_POST['reason'] ?? '';
        $message = $_POST['message'] ?? '';
        
        if (!$userId || !$reason || !$message) {
            $_SESSION['error'] = 'Missing required fields';
            header('Location: ' . URLROOT . '/admin/reports');
            exit;
        }

        // Send warning
        $warningId = $this->adminModel->sendWarning($userId, $_SESSION['user_id'], $reportId, $warningType, $reason, $message);
        
        if ($warningId) {
            // Update report status if report_id provided
            if ($reportId) {
                $this->adminModel->updateReportAfterWarning($reportId, $_SESSION['user_id']);
            }

            // Log admin action
            $this->adminModel->logAdminAction(
                $_SESSION['user_id'],
                'warning_sent',
                $userId,
                'warning',
                $warningId,
                "Sent {$warningType} warning: {$reason}",
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            );

            $_SESSION['success'] = 'Warning sent successfully';
        } else {
            $_SESSION['error'] = 'Failed to send warning';
        }
        
        // Redirect to report or user page
        if ($reportId) {
            header('Location: ' . URLROOT . '/admin/viewReport/' . $reportId);
        } else {
            header('Location: ' . URLROOT . '/admin/viewUser/' . $userId);
        }
        exit;
    }

    // Resolve report
    public function resolveReport() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admin/reports');
            exit;
        }

        $reportId = $_POST['report_id'] ?? null;
        $adminNotes = $_POST['admin_notes'] ?? '';
        $actionTaken = $_POST['action_taken'] ?? 'none';
        
        if (!$reportId) {
            $_SESSION['error'] = 'Report ID required';
            header('Location: ' . URLROOT . '/admin/reports');
            exit;
        }

        // Resolve report
        if ($this->adminModel->resolveReport($reportId, $_SESSION['user_id'], $adminNotes, $actionTaken)) {
            // Log admin action
            $this->adminModel->logAdminAction(
                $_SESSION['user_id'],
                'report_resolved',
                null,
                'report',
                $reportId,
                "Resolved report with action: {$actionTaken}",
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            );

            $_SESSION['success'] = 'Report resolved successfully';
        } else {
            $_SESSION['error'] = 'Failed to resolve report';
        }

        header('Location: ' . URLROOT . '/admin/reports');
        exit;
    }

    // Dismiss report
    public function dismissReport() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admin/reports');
            exit;
        }

        $reportId = $_POST['report_id'] ?? null;
        $adminNotes = $_POST['admin_notes'] ?? '';
        
        if (!$reportId) {
            $_SESSION['error'] = 'Report ID required';
            header('Location: ' . URLROOT . '/admin/reports');
            exit;
        }

        // Dismiss report
        if ($this->adminModel->dismissReport($reportId, $_SESSION['user_id'], $adminNotes)) {
            // Log admin action
            $this->adminModel->logAdminAction(
                $_SESSION['user_id'],
                'report_dismissed',
                null,
                'report',
                $reportId,
                "Dismissed report",
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            );

            $_SESSION['success'] = 'Report dismissed';
        } else {
            $_SESSION['error'] = 'Failed to dismiss report';
        }

        header('Location: ' . URLROOT . '/admin/reports');
        exit;
    }

    // ========================================
    // ACTIVITY LOGS
    // ========================================

    // View system activity logs
    public function activityLogs() {
        // Get user activities
        $userActivities = $this->adminModel->getAllUserActivities(100);

        // Get admin actions
        $adminActions = $this->adminModel->getAllAdminActions(100);

        $data = [
            'user_activities' => $userActivities,
            'admin_actions' => $adminActions
        ];

        $this->view('users/admin_activity_logs', $data);
    }
}
