<?php
/**
 * Feedback Controller
 * Handles feedback submission and management
 */
class FeedbackController extends Controller {
    
    private $feedbackModel;
    private $notificationModel;

    public function __construct() {
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit();
        }

        $this->feedbackModel = $this->model('Feedback');
        $this->notificationModel = $this->model('Notification');
    }

    /**
     * Submit feedback for a user
     * POST endpoint for AJAX requests
     * Expected POST params: user_id, rating, comment (optional), project_id (optional)
     */
    public function submit() {
        // Set JSON header
        header('Content-Type: application/json');

        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
            exit;
        }

        // Only ORGANIZATION users can submit feedback
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organization') {
            echo json_encode([
                'success' => false,
                'message' => 'Only organizations can submit feedback'
            ]);
            exit;
        }

        // Get and validate inputs
        $userId = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT);
        $rating = filter_var($_POST['rating'] ?? 0, FILTER_VALIDATE_INT);
        $comment = trim($_POST['comment'] ?? '');
        $projectId = filter_var($_POST['project_id'] ?? null, FILTER_VALIDATE_INT);
        $reviewerId = $_SESSION['user_id'];

        // Validate required fields
        if (!$userId) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid user ID'
            ]);
            exit;
        }

        if (!$rating || $rating < 1 || $rating > 5) {
            echo json_encode([
                'success' => false,
                'message' => 'Please select a rating between 1 and 5 stars'
            ]);
            exit;
        }

        // If project_id is provided, verify access
        if ($projectId) {
            $hasAccess = $this->verifyProjectAccess($projectId, $reviewerId);
            if (!$hasAccess) {
                echo json_encode([
                    'success' => false,
                    'message' => 'You do not have permission to give feedback on this project'
                ]);
                exit;
            }
        }

        // Submit feedback
        $result = $this->feedbackModel->submitFeedback(
            $userId,
            $reviewerId,
            $rating,
            !empty($comment) ? $comment : null,
            $projectId
        );

        // Check result
        if ($result === true) {
            // Create notification for the user receiving feedback
            $this->createFeedbackNotification($userId, $reviewerId, $rating, $projectId);

            echo json_encode([
                'success' => true,
                'message' => 'Feedback submitted successfully! Thank you for your input.'
            ]);
        } else {
            // $result contains error message
            echo json_encode([
                'success' => false,
                'message' => $result
            ]);
        }
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
     * @param int $rating
     * @param int|null $projectId
     */
    private function createFeedbackNotification($userId, $reviewerId, $rating, $projectId = null) {
        try {
            // Get reviewer name
            $db = new Database();
            $db->query("SELECT username FROM users WHERE id = :id");
            $db->bind(':id', $reviewerId);
            $reviewer = $db->single();
            $reviewerName = $reviewer ? $reviewer->username : 'An organization';

            // Build notification message
            $message = "{$reviewerName} submitted a {$rating}/5 performance rating for you";
            
            if ($projectId) {
                // Get project name
                $db->query("SELECT name FROM projects WHERE id = :id");
                $db->bind(':id', $projectId);
                $project = $db->single();
                if ($project) {
                    $message .= " for your work on '{$project->name}'";
                }
            }

            // Create notification
            $this->notificationModel->create(
                $userId,
                'feedback_received',
                $message,
                null, // No specific link
                $reviewerId
            );
        } catch (Exception $e) {
            // Log error but don't fail feedback submission
            error_log("Failed to create feedback notification: " . $e->getMessage());
        }
    }

    /**
     * View all feedback for a user
     * GET /feedback/view/{userId}
     */
    public function view($userId = null) {
        if (!$userId) {
            $userId = $_SESSION['user_id'];
        }

        $feedback = $this->feedbackModel->getFeedbackForUser($userId);
        $avgRating = $this->feedbackModel->getAverageRating($userId);
        $count = $this->feedbackModel->getFeedbackCount($userId);

        $data = [
            'feedback' => $feedback,
            'avgRating' => $avgRating,
            'count' => $count,
            'userId' => $userId
        ];

        $this->view('feedback/view', $data);
    }

    /**
     * Get feedback statistics (AJAX endpoint)
     * GET /feedback/stats/{userId}/{projectId}
     */
    public function stats($userId, $projectId = null) {
        header('Content-Type: application/json');

        $avgRating = $this->feedbackModel->getAverageRating($userId, $projectId);
        $count = $this->feedbackModel->getFeedbackCount($userId, $projectId);

        echo json_encode([
            'success' => true,
            'avgRating' => $avgRating,
            'count' => $count
        ]);
        exit;
    }
}
