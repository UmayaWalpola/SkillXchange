<?php
/**
 * FeedbackReportController
 * Handles feedback reporting functionality (users report abusive/fake feedback)
 */
class FeedbackReportController extends Controller {

    private $feedbackReportModel;
    private $feedbackModel;
    private $notificationModel;

    public function __construct() {
        $this->feedbackReportModel = $this->model('FeedbackReport');
        $this->feedbackModel = $this->model('Feedback');
        $this->notificationModel = $this->model('Notification');
    }

    /**
     * Roles that can moderate reported feedback.
     */
    private function isModerator() {
        if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
            return false;
        }

        return in_array($_SESSION['role'], ['admin', 'manager', 'community_admin'], true);
    }

    /**
     * Guard for page routes.
     */
    private function requireModeratorPage() {
        if (!$this->isModerator()) {
            header('Location: ' . URLROOT . '/auth/login');
            exit;
        }
    }

    /**
     * Guard for JSON routes.
     */
    private function requireModeratorJson() {
        if (!$this->isModerator()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return false;
        }

        return true;
    }

    /**
     * Submit a new feedback report (AJAX endpoint)
     * POST /FeedbackReport/submit
     */
    public function submit() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please log in first']);
            return;
        }

        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            // Get POST data
            $feedbackId = $_POST['feedback_id'] ?? null;
            $reason = $_POST['reason'] ?? null;
            $details = trim($_POST['details'] ?? '');
            $reporterId = $_SESSION['user_id'];

            // Validate required fields
            if (!$feedbackId || !$reason) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return;
            }

            // Validate reason
            $validReasons = ['abusive', 'fake', 'spam', 'inappropriate', 'other'];
            if (!in_array($reason, $validReasons)) {
                echo json_encode(['success' => false, 'message' => 'Invalid reason']);
                return;
            }

            // Check if user already reported this feedback
            if ($this->feedbackReportModel->hasUserReported($feedbackId, $reporterId)) {
                echo json_encode(['success' => false, 'message' => 'You have already reported this feedback']);
                return;
            }

            // Create report
            $reportData = [
                'feedback_id' => $feedbackId,
                'reporter_id' => $reporterId,
                'reason' => $reason,
                'details' => $details
            ];

            $reportId = $this->feedbackReportModel->createReport($reportData);

            if ($reportId) {
                // Send notification to admins (optional)
                $this->notifyAdmins($reason);

                echo json_encode([
                    'success' => true,
                    'message' => 'Report submitted successfully. Our team will review it shortly.',
                    'report_id' => $reportId
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to submit report']);
            }

        } catch (Exception $e) {
            error_log("FeedbackReport submit error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
        }
    }

    /**
     * Admin page: List all reported feedback
     * GET /FeedbackReport/index
     */
    public function index($status = 'all') {
        $this->requireModeratorPage();

        // Get filter from query params
        $status = $_GET['status'] ?? 'all';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;

        $allReports = $this->getCombinedReports($status);
        $totalCount = count($allReports);
        $totalPages = max(1, (int)ceil($totalCount / $limit));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $limit;
        $pagedReports = array_slice($allReports, $offset, $limit);

        // Get statistics
        $stats = $this->getCombinedReportStats();

        // Prepare data for view
        $data = [
            'title' => 'Reported Feedback',
            'reports' => $pagedReports,
            'total_count' => $totalCount,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'current_status' => $status,
            'stats' => $stats
        ];

        $this->view('admin/reported_feedback', $data);
    }

    /**
     * Get reports via AJAX (for dynamic filtering)
     * GET /FeedbackReport/list
     */
    public function list() {
        if (!$this->requireModeratorJson()) {
            return;
        }

        try {
            $status = $_GET['status'] ?? 'all';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 20;
            $allReports = $this->getCombinedReports($status);
            $totalCount = count($allReports);
            $totalPages = max(1, (int)ceil($totalCount / $limit));
            $page = max(1, min($page, $totalPages));
            $offset = ($page - 1) * $limit;
            $pagedReports = array_slice($allReports, $offset, $limit);

            echo json_encode([
                'success' => true,
                'reports' => $pagedReports,
                'total_count' => $totalCount,
                'current_page' => $page,
                'total_pages' => $totalPages
            ]);

        } catch (Exception $e) {
            error_log("FeedbackReport list error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to load reports']);
        }
    }

    /**
     * Update report status (admin action via AJAX)
     * POST /FeedbackReport/updateStatus
     */
    public function updateStatus() {
        if (!$this->requireModeratorJson()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $reportId = $_POST['report_id'] ?? null;
            $status = $_POST['status'] ?? null;
            $adminNotes = trim($_POST['admin_notes'] ?? '');
            $adminId = $_SESSION['user_id'];

            // Validate
            if (!$reportId || !$status) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return;
            }

            $validStatuses = ['pending', 'reviewed', 'dismissed', 'action_taken'];
            if (!in_array($status, $validStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
                return;
            }

            // Update status
            $success = $this->feedbackReportModel->updateReportStatus(
                $reportId,
                $status,
                $adminId,
                $adminNotes
            );

            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Report status updated successfully'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update status']);
            }

        } catch (Exception $e) {
            error_log("FeedbackReport updateStatus error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
    }

    /**
     * Remove reported feedback (admin deletes the actual feedback)
     * POST /FeedbackReport/removeFeedback
     */
    public function removeFeedback() {
        if (!$this->requireModeratorJson()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $reportId = $_POST['report_id'] ?? null;
            $feedbackId = $_POST['feedback_id'] ?? null;

            if (!$reportId || !$feedbackId) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return;
            }

            // Avoid false-success responses for invalid feedback IDs.
            $checkSql = "SELECT id FROM user_feedback WHERE id = :feedback_id LIMIT 1";
            $checkStmt = $this->feedbackReportModel->connect()->prepare($checkSql);
            $checkStmt->bindValue(':feedback_id', $feedbackId);
            $checkStmt->execute();

            if (!$checkStmt->fetch(PDO::FETCH_ASSOC)) {
                echo json_encode(['success' => false, 'message' => 'Feedback not found']);
                return;
            }

            // Delete the actual feedback (this will cascade delete reports due to FK)
            $success = $this->feedbackModel->deleteFeedback($feedbackId);

            if ($success) {
                // Update report status to action_taken
                $this->feedbackReportModel->updateReportStatus(
                    $reportId,
                    'action_taken',
                    $_SESSION['user_id'],
                    'Feedback removed by moderator'
                );

                echo json_encode([
                    'success' => true,
                    'message' => 'Feedback removed successfully'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove feedback']);
            }

        } catch (Exception $e) {
            error_log("FeedbackReport removeFeedback error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
    }

    /**
     * Get report statistics (AJAX endpoint for dashboard)
     * GET /FeedbackReport/stats
     */
    public function stats() {
        if (!$this->requireModeratorJson()) {
            return;
        }

        try {
            $stats = $this->getCombinedReportStats();
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            error_log("FeedbackReport stats error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to load statistics']);
        }
    }

    /**
     * Send notification to admins about new report
     * 
     * @param string $reason
     */
    private function notifyAdmins($reason) {
        try {
            // Get all admin users
            $adminSql = "SELECT id FROM users WHERE role IN ('admin', 'manager', 'community_admin')";
            $stmt = $this->feedbackReportModel->connect()->prepare($adminSql);
            $stmt->execute();
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Create notification for each admin
            foreach ($admins as $admin) {
                $this->notificationModel->createNotification([
                    'user_id' => $admin['id'],
                    'type' => 'feedback_report',
                    'message' => "A feedback has been reported as {$reason}. Please review."
                ]);
            }
        } catch (Exception $e) {
            error_log("Failed to notify admins: " . $e->getMessage());
        }
    }

    private function getCombinedReports($status = 'all') {
        $result = $this->feedbackReportModel->getReports([
            'status' => $status,
            'limit' => 1000,
            'offset' => 0
        ]);

        $reports = $result['items'] ?? [];
        foreach ($reports as &$report) {
            $report['report_kind'] = 'feedback';
            $report['ui_status'] = $report['status'] ?? 'pending';
        }
        unset($report);

        return $reports;
    }

    private function getCombinedReportStats() {
        return $this->feedbackReportModel->getReportStats();
    }
}
