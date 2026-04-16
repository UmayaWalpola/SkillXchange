<?php
/**
 * Feedback Controller
 * Handles feedback submission and management
 */
class FeedbackController extends Controller {
    
    private $feedbackModel;
    private $notificationModel;
    private $managerModel;

    public function __construct() {
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit();
        }

        $this->feedbackModel = $this->model('Feedback');
        $this->notificationModel = $this->model('Notification');
        $this->managerModel = null;
    }

    /**
     * Submit platform feedback to management
     * POST /feedback/submit
     * Expected: feedback_message, feedback_type
     */
    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/users/userprofile');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $message = trim($_POST['feedback_message'] ?? '');
        $type = trim($_POST['feedback_type'] ?? 'other');

        if ($userId <= 0 || $message === '') {
            $_SESSION['error'] = 'Please provide your feedback message.';
            header('Location: ' . URLROOT . '/users/userprofile');
            exit;
        }

        $allowedTypes = ['suggestion', 'bug', 'feature', 'other'];
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'other';
        }

        $subjectMap = [
            'suggestion' => 'Suggestion',
            'bug' => 'Bug Report',
            'feature' => 'Feature Request',
            'other' => 'Other'
        ];
        $subject = $subjectMap[$type] ?? 'Other';

        if ($this->managerModel === null) {
            $managerModelPath = dirname(__DIR__) . '/models/Manager.php';
            if (!file_exists($managerModelPath)) {
                $_SESSION['error'] = 'Platform feedback is currently unavailable.';
                header('Location: ' . URLROOT . '/users/userprofile');
                exit;
            }

            $this->managerModel = $this->model('Manager');
        }

        $ok = $this->managerModel->submitPlatformFeedback($userId, $subject, $message);
        if ($ok) {
            $_SESSION['success'] = 'Feedback sent to management.';
        } else {
            $_SESSION['error'] = 'Failed to send feedback. Please try again.';
        }

        header('Location: ' . URLROOT . '/users/userprofile');
        exit;
    }

    /**
     * Store new feedback (AJAX endpoint with context support)
     * POST /organization/feedback/store
     * Expected: user_id, context_type, context_id, rating, comment
     */
    public function store() {
        // Set JSON header
        header('Content-Type: application/json');

        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please log in to submit feedback']);
            exit;
        }

        // Get and validate inputs
        $userId = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT);
        $contextType = trim(strip_tags((string)($_POST['context_type'] ?? 'project')));
        $contextId = filter_var($_POST['context_id'] ?? 0, FILTER_VALIDATE_INT);
        $rating = filter_var($_POST['rating'] ?? 0, FILTER_VALIDATE_INT);
        $comment = trim($_POST['comment'] ?? '');
        $reviewerId = $_SESSION['user_id'];

        // Validate required fields
        if (!$userId || !$contextId || !$rating) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        // Validate rating range
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
            exit;
        }

        // Validate context type
        if (!in_array($contextType, ['project', 'session'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid context type']);
            exit;
        }

        // Permission checks based on context
        if ($contextType === 'project') {
            // Only organizations can give project feedback
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organization') {
                echo json_encode(['success' => false, 'message' => 'Only organizations can submit project feedback']);
                exit;
            }

            // Verify organization owns the project
            if (!$this->verifyProjectAccess($contextId, $reviewerId)) {
                echo json_encode(['success' => false, 'message' => 'You do not have permission to give feedback on this project']);
                exit;
            }
        } elseif ($contextType === 'session') {
            // TODO: Add session-specific permission checks
            // - Verify user participated in the session
            // - Verify session is completed
        }

        // Check for duplicate feedback
        if ($this->feedbackModel->feedbackExists($userId, $reviewerId, $contextType, $contextId)) {
            echo json_encode(['success' => false, 'message' => 'You have already submitted feedback for this user in this context']);
            exit;
        }

        // Prepare feedback data
        $data = [
            'user_id' => $userId,
            'reviewer_id' => $reviewerId,
            'project_id' => $contextType === 'project' ? $contextId : null,
            'context_type' => $contextType,
            'context_id' => $contextId,
            'rating' => $rating,
            'comment' => $comment,
            'tags' => trim($_POST['tags'] ?? '')
        ];

        // Submit feedback
        if ($this->feedbackModel->submitFeedback($data)) {
            // Create notification
            $this->createFeedbackNotification($userId, $reviewerId, $rating, $contextType, $contextId);

            // Get updated stats
            $stats = $this->feedbackModel->getUserStats($userId, $contextType, $contextId);

            echo json_encode([
                'success' => true,
                'message' => 'Feedback submitted successfully!',
                'stats' => [
                    'avg_rating' => $stats->avg_rating ?? 0,
                    'total_count' => $stats->total_count ?? 0
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit feedback. Please try again.']);
        }
        exit;
    }

    /**
     * Get feedback statistics (AJAX endpoint)
     * GET /organization/feedback/stats?user_id=X&context_type=Y&context_id=Z
     */
    public function stats() {
        // Set JSON header
        header('Content-Type: application/json');

        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // Get query parameters
        $userId = filter_var($_GET['user_id'] ?? 0, FILTER_VALIDATE_INT);
        $contextType = trim(strip_tags((string)($_GET['context_type'] ?? 'project')));
        $contextId = filter_var($_GET['context_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            exit;
        }

        // Get stats
        $stats = $this->feedbackModel->getUserStats($userId, $contextType, $contextId);

        echo json_encode([
            'success' => true,
            'stats' => [
                'avg_rating' => $stats->avg_rating ?? 0,
                'total_count' => $stats->total_count ?? 0,
                'five_star' => $stats->five_star ?? 0,
                'four_star' => $stats->four_star ?? 0,
                'three_star' => $stats->three_star ?? 0,
                'two_star' => $stats->two_star ?? 0,
                'one_star' => $stats->one_star ?? 0
            ]
        ]);
        exit;
    }

    /**
     * Verify that the reviewer (organization) owns the project
     * 
     * @param int $projectId
     * @param int $reviewerId
     * @return bool
     */
    private function verifyProjectAccess($projectId, $reviewerId) {
        $db = new Database();
        $db->query("SELECT id FROM projects WHERE id = :project_id AND organization_id = :org_id");
        $db->bind(':project_id', $projectId);
        $db->bind(':org_id', $reviewerId);
        
        return $db->single() !== false;
    }

    /**
     * Create a notification for the user receiving feedback
     * 
     * @param int $userId - User receiving feedback
     * @param int $reviewerId - User giving feedback
     * @param int $rating - Rating value
     * @param string $contextType - 'project' or 'session'
     * @param int $contextId - Context ID
     */
    private function createFeedbackNotification($userId, $reviewerId, $rating, $contextType, $contextId) {
        try {
            // Get reviewer name
            $db = new Database();
            $db->query("SELECT username FROM users WHERE id = :id");
            $db->bind(':id', $reviewerId);
            $reviewer = $db->single();
            $reviewerName = $reviewer ? $reviewer->username : 'Someone';

            // Build notification message based on context
            $message = "{$reviewerName} gave you a {$rating}/5 rating";
            
            if ($contextType === 'project' && $contextId) {
                // Get project name
                $db->query("SELECT name FROM projects WHERE id = :id");
                $db->bind(':id', $contextId);
                $project = $db->single();
                if ($project) {
                    $message .= " for your work on '{$project->name}'";
                }
            } elseif ($contextType === 'session' && $contextId) {
                $message .= " for the learning session";
            }

            // Create notification
            $this->notificationModel->createNotification([
                'user_id' => $userId,
                'type' => 'feedback_received',
                'message' => $message,
                'project_id' => null,
                'task_id' => $reviewerId,
                'is_read' => 0
            ]);
        } catch (Throwable $e) {
            // Log error but don't fail feedback submission
            error_log("Failed to create feedback notification: " . $e->getMessage());
        }
    }

    /**
     * Get filtered feedback list (AJAX endpoint)
     * GET /Feedback/list?user_id=X&context_type=project&rating=5&tag=communication&sort=newest&search=abc&page=1
     */
    public function list() {
        // Set JSON header
        header('Content-Type: application/json');

        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // Get query parameters
        $userId = filter_var($_GET['user_id'] ?? 0, FILTER_VALIDATE_INT);
        
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            exit;
        }

        // Build filters array from query parameters
        $filters = [
            'context_type' => isset($_GET['context_type']) ? htmlspecialchars(strip_tags($_GET['context_type'])) : null,
            'context_id' => filter_var($_GET['context_id'] ?? null, FILTER_VALIDATE_INT),
            'rating' => filter_var($_GET['rating'] ?? null, FILTER_VALIDATE_INT),
            'tag' => isset($_GET['tag']) ? htmlspecialchars(strip_tags($_GET['tag'])) : null,
            'reviewer_id' => filter_var($_GET['reviewer_id'] ?? null, FILTER_VALIDATE_INT),
            'search' => isset($_GET['search']) ? htmlspecialchars(strip_tags($_GET['search'])) : null,
            'sort' => isset($_GET['sort']) ? htmlspecialchars(strip_tags($_GET['sort'])) : 'newest',
            'page' => filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT),
            'limit' => filter_var($_GET['limit'] ?? 10, FILTER_VALIDATE_INT)
        ];

        // Remove null values from filters
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '' && $value !== false;
        });

        // Get filtered feedback
        $result = $this->feedbackModel->getFilteredFeedback($userId, $filters);

        // Calculate pagination
        $page = $filters['page'] ?? 1;
        $limit = $filters['limit'] ?? 10;
        $totalPages = ceil($result['total_count'] / $limit);

        echo json_encode([
            'success' => true,
            'items' => $result['items'],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $result['total_count'],
                'total_pages' => $totalPages
            ]
        ]);
        exit;
    }

    /**
     * View feedback page (default action)
     * Route: /Feedback/index or /Feedback
     */
    public function index($userId = null) {
        if (!$userId) {
            $userId = $_SESSION['user_id'];
        }

        // Get basic stats
        $stats = $this->feedbackModel->getUserStats($userId);

        $data = [
            'userId' => $userId,
            'stats' => $stats
        ];

        $this->view('feedback/view', $data);
    }
}

