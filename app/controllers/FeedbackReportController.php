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
                $this->notifyAdmins($reportId, $feedbackId, $reason);

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
        // Check if user is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . URLROOT . '/auth/login');
            exit;
        }

        // Get filter from query params
        $status = $_GET['status'] ?? 'all';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get reports
        $result = $this->feedbackReportModel->getReports([
            'status' => $status,
            'limit' => $limit,
            'offset' => $offset
        ]);

        // Get statistics
        $stats = $this->feedbackReportModel->getReportStats();

        // Prepare data for view
        $data = [
            'title' => 'Reported Feedback',
            'reports' => $result['items'],
            'total_count' => $result['total_count'],
            'current_page' => $page,
            'total_pages' => ceil($result['total_count'] / $limit),
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
        // Check if user is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            $status = $_GET['status'] ?? 'all';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;

            $result = $this->feedbackReportModel->getReports([
                'status' => $status,
                'limit' => $limit,
                'offset' => $offset
            ]);

            echo json_encode([
                'success' => true,
                'reports' => $result['items'],
                'total_count' => $result['total_count'],
                'current_page' => $page,
                'total_pages' => ceil($result['total_count'] / $limit)
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
        // Check if user is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
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
        // Check if user is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
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

            // Delete the actual feedback (this will cascade delete reports due to FK)
            $success = $this->feedbackModel->deleteFeedback($feedbackId);

            if ($success) {
                // Update report status to action_taken
                $this->feedbackReportModel->updateReportStatus(
                    $reportId,
                    'action_taken',
                    $_SESSION['user_id'],
                    'Feedback removed by admin'
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
        // Check if user is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            $stats = $this->feedbackReportModel->getReportStats();
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
     * @param int $reportId
     * @param int $feedbackId
     * @param string $reason
     */
    private function notifyAdmins($reportId, $feedbackId, $reason) {
        try {
            // Get all admin users
            $adminSql = "SELECT id FROM users WHERE role = 'admin'";
            $stmt = $this->feedbackReportModel->connect()->prepare($adminSql);
            $stmt->execute();
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Create notification for each admin
            foreach ($admins as $admin) {
                $this->notificationModel->createNotification([
                    'user_id' => $admin['id'],
                    'type' => 'feedback_report',
                    'title' => 'New Feedback Report',
                    'message' => "A feedback has been reported as {$reason}. Please review.",
                    'link' => URLROOT . '/FeedbackReport/index',
                    'sender_id' => $_SESSION['user_id']
                ]);
            }
        } catch (Exception $e) {
            error_log("Failed to notify admins: " . $e->getMessage());
        }
    }
}
