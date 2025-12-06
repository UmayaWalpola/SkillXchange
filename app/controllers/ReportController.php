<?php

class ReportController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Report a user profile
     * POST /report/user
     */
    public function reportUser()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to report.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        $reporterId = $_SESSION['user_id'];
        $reportedUserId = filter_var($_POST['reported_user_id'] ?? 0, FILTER_VALIDATE_INT);
        $reason = trim($_POST['reason'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Validation
        if (!$reportedUserId) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
            exit;
        }

        if ($reporterId == $reportedUserId) {
            echo json_encode(['success' => false, 'message' => 'You cannot report yourself.']);
            exit;
        }

        if (empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Please select a reason for reporting.']);
            exit;
        }

        // Check for duplicate report
        $this->db->query("SELECT id FROM reports WHERE reporter_id = :reporter_id AND reported_user_id = :reported_user_id AND status = 'pending'");
        $this->db->bind(':reporter_id', $reporterId);
        $this->db->bind(':reported_user_id', $reportedUserId);
        $existing = $this->db->single();

        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'You have already reported this user.']);
            exit;
        }

        // Insert report
        $this->db->query("INSERT INTO reports (reporter_id, reported_user_id, reason, description, status, created_at) 
                         VALUES (:reporter_id, :reported_user_id, :reason, :description, 'pending', NOW())");
        $this->db->bind(':reporter_id', $reporterId);
        $this->db->bind(':reported_user_id', $reportedUserId);
        $this->db->bind(':reason', $reason);
        $this->db->bind(':description', $description);

        if ($this->db->execute()) {
            echo json_encode(['success' => true, 'message' => 'Your report has been submitted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit report. Please try again.']);
        }
    }

    /**
     * Report a project member (within a project context)
     * POST /report/projectUser
     */
    public function reportProjectUser()
    {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to report.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        $reporterId = $_SESSION['user_id'];
        $reportedUserId = filter_var($_POST['reported_user_id'] ?? 0, FILTER_VALIDATE_INT);
        $projectId = filter_var($_POST['project_id'] ?? 0, FILTER_VALIDATE_INT);
        $reason = trim($_POST['reason'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Validation
        if (!$reportedUserId || !$projectId) {
            echo json_encode(['success' => false, 'message' => 'Invalid user or project ID.']);
            exit;
        }

        if ($reporterId == $reportedUserId) {
            echo json_encode(['success' => false, 'message' => 'You cannot report yourself.']);
            exit;
        }

        if (empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Please select a reason for reporting.']);
            exit;
        }

        // Check for duplicate report
        $this->db->query("SELECT id FROM user_reports WHERE reporter_id = :reporter_id AND reported_user_id = :reported_user_id AND project_id = :project_id AND status = 'pending'");
        $this->db->bind(':reporter_id', $reporterId);
        $this->db->bind(':reported_user_id', $reportedUserId);
        $this->db->bind(':project_id', $projectId);
        $existing = $this->db->single();

        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'You have already reported this member.']);
            exit;
        }

        // Insert report
        $this->db->query("INSERT INTO user_reports (reporter_id, reported_user_id, project_id, reason, description, status, created_at) 
                         VALUES (:reporter_id, :reported_user_id, :project_id, :reason, :description, 'pending', NOW())");
        $this->db->bind(':reporter_id', $reporterId);
        $this->db->bind(':reported_user_id', $reportedUserId);
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':reason', $reason);
        $this->db->bind(':description', $description);

        if ($this->db->execute()) {
            echo json_encode(['success' => true, 'message' => 'Your report has been submitted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit report. Please try again.']);
        }
    }

    /**
     * Report content (community posts or chat messages)
     * POST /report/content
     */
    public function reportContent()
    {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to report.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        $reporterId = $_SESSION['user_id'];
        $contentType = trim($_POST['content_type'] ?? '');
        $contentId = filter_var($_POST['content_id'] ?? 0, FILTER_VALIDATE_INT);
        $reason = trim($_POST['reason'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Validation
        if (!in_array($contentType, ['post', 'chat_message'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid content type.']);
            exit;
        }

        if (!$contentId) {
            echo json_encode(['success' => false, 'message' => 'Invalid content ID.']);
            exit;
        }

        if (empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Please select a reason for reporting.']);
            exit;
        }

        // Check if user is reporting their own content
        if ($contentType === 'post') {
            $this->db->query("SELECT user_id FROM community_posts WHERE id = :id");
            $this->db->bind(':id', $contentId);
            $post = $this->db->single();
            
            if ($post && $post->user_id == $reporterId) {
                echo json_encode(['success' => false, 'message' => 'You cannot report your own post.']);
                exit;
            }
        } elseif ($contentType === 'chat_message') {
            $this->db->query("SELECT user_id FROM project_chat_messages WHERE id = :id");
            $this->db->bind(':id', $contentId);
            $message = $this->db->single();
            
            if ($message && $message->user_id == $reporterId) {
                echo json_encode(['success' => false, 'message' => 'You cannot report your own message.']);
                exit;
            }
        }

        // Check for duplicate report
        $this->db->query("SELECT id FROM content_reports WHERE reporter_id = :reporter_id AND content_type = :content_type AND content_id = :content_id AND status = 'pending'");
        $this->db->bind(':reporter_id', $reporterId);
        $this->db->bind(':content_type', $contentType);
        $this->db->bind(':content_id', $contentId);
        $existing = $this->db->single();

        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'You have already reported this content.']);
            exit;
        }

        // Insert report
        $this->db->query("INSERT INTO content_reports (reporter_id, content_type, content_id, reason, description, status, created_at) 
                         VALUES (:reporter_id, :content_type, :content_id, :reason, :description, 'pending', NOW())");
        $this->db->bind(':reporter_id', $reporterId);
        $this->db->bind(':content_type', $contentType);
        $this->db->bind(':content_id', $contentId);
        $this->db->bind(':reason', $reason);
        $this->db->bind(':description', $description);

        if ($this->db->execute()) {
            echo json_encode(['success' => true, 'message' => 'Your report has been submitted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit report. Please try again.']);
        }
    }
}
