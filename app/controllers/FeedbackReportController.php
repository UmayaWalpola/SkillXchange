<?php
/**
 * FeedbackReportController
 * Handles feedback reporting functionality (users report abusive/fake feedback)
 */
class FeedbackReportController extends Controller {

    private $feedbackReportModel;
    private $feedbackModel;
    private $notificationModel;

    // Allowed reasons for feedback report flow only.
    private const FEEDBACK_REASONS = ['abusive', 'fake', 'spam', 'inappropriate', 'other'];

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
            // Get POST data with proper validation
            $feedbackId = filter_var($_POST['feedback_id'] ?? 0, FILTER_VALIDATE_INT);
            $reason = trim((string)($_POST['reason'] ?? ''));
            $details = trim((string)($_POST['details'] ?? ''));
            $reporterId = (int)($_SESSION['user_id'] ?? 0);

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

            // Create report
            $reportData = [
                'feedback_id' => $feedbackId,
                'reporter_id' => $reporterId,
                'reason' => $reason,
                'details' => $details
            ];

            $reportId = $this->feedbackReportModel->createReport($reportData);

            // Handle different return values
            if ($reportId === 'duplicate') {
                echo json_encode(['success' => false, 'message' => 'You have already reported this feedback']);
                return;
            }

            if ($reportId && is_numeric($reportId)) {
                // Send notification to admins
                $this->notifyAdmins($reportId, $feedbackId, $reason);

                echo json_encode([
                    'success' => true,
                    'message' => 'Report submitted successfully. Our team will review it shortly.',
                    'report_id' => $reportId
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to submit report. Please try again.']);
            }

        } catch (Throwable $e) {
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

        // HARD FILTER: Only show feedback report rows (never user/org/project-member reports).
        $filteredReports = $this->filterFeedbackOnlyReports($result['items']);

        // Prepare data for view
        $data = [
            'title' => 'Reported Feedback',
            'reports' => $filteredReports,
            'total_count' => count($filteredReports),
            'current_page' => $page,
            'total_pages' => max(1, (int)ceil(max(1, count($filteredReports)) / $limit)),
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

            // HARD FILTER: return only valid feedback reports to UI.
            $filteredReports = $this->filterFeedbackOnlyReports($result['items']);

            echo json_encode([
                'success' => true,
                'reports' => $filteredReports,
                'total_count' => count($filteredReports),
                'current_page' => $page,
                'total_pages' => max(1, (int)ceil(max(1, count($filteredReports)) / $limit))
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

    /**
     * Keep only user-submitted feedback report records.
     * This protects Reported Feedback page from mixed report sources.
     */
    private function filterFeedbackOnlyReports($items) {
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, function ($row) {
            $reason = strtolower(trim((string)($row['reason'] ?? '')));
            $feedbackId = isset($row['feedback_id']) ? (int)$row['feedback_id'] : 0;

            if ($feedbackId <= 0) {
                return false;
            }

            return in_array($reason, self::FEEDBACK_REASONS, true);
        }));
    }

    /**
     * Send a warning notification to the user who wrote reported feedback
     * POST /FeedbackReport/warnUser
     */
    public function warnUser() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $reportId      = (int)($_POST['report_id']       ?? 0);
            $feedbackUserId = (int)($_POST['feedback_user_id'] ?? 0);

            if (!$reportId || !$feedbackUserId) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return;
            }

            // Send warning notification to the user who wrote the feedback
            $this->notificationModel->createNotification([
                'user_id'   => $feedbackUserId,
                'type'      => 'warning',
                'title'     => 'Content Warning',
                'message'   => 'Your feedback has been reported and reviewed by our moderation team. Please ensure your feedback follows our community guidelines. Repeated violations may result in account restrictions.',
                'link'      => URLROOT . '/Feedback/index/' . $feedbackUserId,
                'sender_id' => $_SESSION['user_id']
            ]);

            // Also mark report as reviewed if it was pending
            $this->feedbackReportModel->updateReportStatus(
                $reportId,
                'reviewed',
                $_SESSION['user_id'],
                'Warning notification sent to feedback author.'
            );

            echo json_encode(['success' => true, 'message' => 'Warning notification sent to user.']);

        } catch (Exception $e) {
            error_log("FeedbackReport warnUser error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to send warning.']);
        }
    }
}
