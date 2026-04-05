<?php

class ReportController extends Controller
{
    private $db;
    private $notificationModel; // 1. Add property for Notification Model

    public function __construct()
    {
        $this->db = new Database();
        // 2. Load the Notification Model
        $this->notificationModel = $this->model('Notification');
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
        $this->db->query("SELECT id FROM reports WHERE reporter_user_id = :reporter_id AND reported_user_id = :reported_user_id AND status = 'pending'");
        $this->db->bind(':reporter_id', $reporterId);
        $this->db->bind(':reported_user_id', $reportedUserId);
        $existing = $this->db->single();

        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'You have already reported this user.']);
            exit;
        }

        // Insert report
        $this->db->query("INSERT INTO reports (reporter_user_id, reported_user_id, reason, description, status, created_at) 
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
        header('Content-Type: application/json');

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

        $this->db->query("SELECT id, organization_id FROM projects WHERE id = :project_id LIMIT 1");
        $this->db->bind(':project_id', $projectId);
        $project = $this->db->single();

        if (!$project) {
            echo json_encode(['success' => false, 'message' => 'Project not found.']);
            exit;
        }

        $this->db->query("SELECT id FROM project_members WHERE project_id = :project_id AND user_id = :user_id AND status = 'active' LIMIT 1");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':user_id', $reporterId);
        $reporterMembership = $this->db->single();

        $isProjectOwner = isset($project->organization_id) && (int)$project->organization_id === (int)$reporterId;
        if (!$reporterMembership && !$isProjectOwner) {
            echo json_encode(['success' => false, 'message' => 'Only active project participants can report a project member.']);
            exit;
        }

        $this->db->query("SELECT id FROM project_members WHERE project_id = :project_id AND user_id = :user_id AND status = 'active' LIMIT 1");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':user_id', $reportedUserId);
        $reportedMembership = $this->db->single();

        if (!$reportedMembership) {
            echo json_encode(['success' => false, 'message' => 'That user is not an active member of this project.']);
            exit;
        }

        // Check for duplicate report
        $this->db->query("SELECT id FROM user_reports WHERE reporter_org_id = :reporter_id AND reported_user_id = :reported_user_id AND project_id = :project_id AND status = 'pending'");
        $this->db->bind(':reporter_id', $reporterId);
        $this->db->bind(':reported_user_id', $reportedUserId);
        $this->db->bind(':project_id', $projectId);
        $existing = $this->db->single();

        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'You have already reported this member.']);
            exit;
        }

        // Insert report using the existing DB.sql schema.
        $this->db->query("INSERT INTO user_reports (project_id, reported_user_id, reporter_org_id, reason, details, status, reported_at) 
                         VALUES (:project_id, :reported_user_id, :reporter_id, :reason, :details, 'pending', NOW())");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':reported_user_id', $reportedUserId);
        $this->db->bind(':reporter_id', $reporterId);
        $this->db->bind(':reason', $reason);
        $this->db->bind(':details', $description);

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

    /**
     * Admin/Manager view to list all reports
     * GET /report/manage
     */
    public function manage()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'organization', 'manager'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }

        $reportType = $_GET['type'] ?? 'all';
        $status = $_GET['status'] ?? 'all';
        $dateFilter = $_GET['date'] ?? 'all';

        $data = [
            'title' => 'Manage Reports',
            'reports' => $this->getAllReports($reportType, $status, $dateFilter),
            'currentType' => $reportType,
            'currentStatus' => $status,
            'currentDate' => $dateFilter
        ];

        $this->view('admin/reports', $data);
    }

    /**
     * Get all reports with filters
     */
    private function getAllReports($type = 'all', $status = 'all', $date = 'all')
    {
        $reports = [];

        // Get content reports (posts and chat messages)
        if ($type === 'all' || $type === 'content') {
            $query = "SELECT cr.*, u.username as reporter_name, cr.content_type, cr.content_id, cr.created_at
                     FROM content_reports cr
                     JOIN users u ON cr.reporter_id = u.id";
            
            $conditions = [];
            if ($status !== 'all') {
                $conditions[] = "cr.status = :status";
            }
            if ($date !== 'all') {
                $conditions[] = $this->getDateCondition('cr.created_at', $date);
            }
            
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }
            
            $query .= " ORDER BY cr.created_at DESC";
            
            $this->db->query($query);
            if ($status !== 'all') {
                $this->db->bind(':status', $status);
            }
            
            $contentReports = $this->db->resultSet();
            foreach ($contentReports as $report) {
                $report->report_type = 'content';
                $reports[] = $report;
            }
        }

        // Get user reports
        if ($type === 'all' || $type === 'user') {
            $query = "SELECT r.*, u1.username as reporter_name, u2.username as reported_user_name, r.created_at
                     FROM reports r
                     JOIN users u1 ON r.reporter_user_id = u1.id
                     JOIN users u2 ON r.reported_user_id = u2.id";
            
            $conditions = [];
            if ($status !== 'all') {
                $conditions[] = "r.status = :status";
            }
            if ($date !== 'all') {
                $conditions[] = $this->getDateCondition('r.created_at', $date);
            }
            
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }
            
            $query .= " ORDER BY r.created_at DESC";
            
            $this->db->query($query);
            if ($status !== 'all') {
                $this->db->bind(':status', $status);
            }
            
            $userReports = $this->db->resultSet();
            foreach ($userReports as $report) {
                $report->report_type = 'user';
                $reports[] = $report;
            }
        }

        // Get project member reports
        if ($type === 'all' || $type === 'project_member') {
            $query = "SELECT ur.*, u1.username as reporter_name, u2.username as reported_user_name, p.name as project_name, ur.reported_at as created_at
                     FROM user_reports ur
                     JOIN users u1 ON ur.reporter_org_id = u1.id
                     JOIN users u2 ON ur.reported_user_id = u2.id
                     JOIN projects p ON ur.project_id = p.id";
            
            $conditions = [];
            if ($status !== 'all') {
                $conditions[] = "ur.status = :status";
            }
            if ($date !== 'all') {
                $conditions[] = $this->getDateCondition('ur.reported_at', $date);
            }
            
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }
            
            $query .= " ORDER BY ur.reported_at DESC";
            
            $this->db->query($query);
            if ($status !== 'all') {
                $this->db->bind(':status', $status);
            }
            
            $projectReports = $this->db->resultSet();
            foreach ($projectReports as $report) {
                $report->report_type = 'project_member';
                $reports[] = $report;
            }
        }

        // Sort all reports by created_at
        usort($reports, function($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });

        return $reports;
    }

    /**
     * Helper to generate date filter SQL condition
     */
    private function getDateCondition($field, $filter)
    {
        switch ($filter) {
            case 'today':
                return "$field >= CURDATE()";
            case 'week':
                return "$field >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'month':
                return "$field >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            default:
                return "1=1";
        }
    }

    /**
     * Update report status AND notify user
     * POST /report/updateStatus
     */
    public function updateStatus()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'organization', 'manager'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        $reportId = filter_var($_POST['report_id'] ?? 0, FILTER_VALIDATE_INT);
        $reportType = trim($_POST['report_type'] ?? '');
        $action = trim($_POST['status'] ?? ''); 

        // 1. Identify the Table and get the Offender's ID
        // We need to know WHO we are warning/banning!
        $table = '';
        $offenderId = null;
        $offenderName = 'User';

        if ($reportType === 'user') {
            $table = 'reports';
            // Fetch the reported user's ID
            $this->db->query("SELECT reported_user_id FROM reports WHERE id = :id");
            $this->db->bind(':id', $reportId);
            $report = $this->db->single();
            if ($report) $offenderId = $report->reported_user_id;

        } elseif ($reportType === 'content') {
            $table = 'content_reports';
            // For content, we need to join with the content table to find the author
            // This is complex, so for now let's assume we fetch it via a separate query if needed.
            // Simplified for learning: We will focus on User Reports for notifications.
        } elseif ($reportType === 'project_member') {
            $table = 'user_reports';
            // Fetch the reported user's ID from project member reports
            $this->db->query("SELECT reported_user_id FROM user_reports WHERE id = :id");
            $this->db->bind(':id', $reportId);
            $report = $this->db->single();
            if ($report) $offenderId = $report->reported_user_id;
        }

        // 2. Handle The Logic
        $newStatus = $action;
        $notificationMessage = "";
        $notificationType = "";

        if ($action === 'banned') {
            if ($offenderId) {
                // Update User Table to SUSPENDED
                $this->db->query("UPDATE users SET status = 'suspended', suspended_until = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = :uid");
                $this->db->bind(':uid', $offenderId);
                $this->db->execute();

                // Prepare Notification (They will see this if they ever get unbanned or via email)
                $notificationType = 'account_ban';
                $notificationMessage = "Your account has been suspended due to severe policy violations.";
            }
            $newStatus = 'resolved';
        } 
        elseif ($action === 'warned') {
            if ($offenderId) {
                // Prepare Warning Notification
                $notificationType = 'system_warning';
                $notificationMessage = "⚠️ Official Warning: Your recent activity violated our community guidelines. Please review our rules to avoid suspension.";
            }
            $newStatus = 'warned';
        }

        elseif ($action === 'content_removed') {
            if ($reportType === 'content') {
                // 1. Fetch the content first (to get the author ID for notification)
                $authorId = null;
                $contentTable = '';
                
                // We need to know if it's a 'post' or 'chat_message'
                // For this example, we'll query the content_reports table to find out the specific subtype
                $this->db->query("SELECT content_type, content_id FROM content_reports WHERE id = :id");
                $this->db->bind(':id', $reportId);
                $cr = $this->db->single();

                if ($cr) {
                    if ($cr->content_type === 'post') {
                        $contentTable = 'posts';
                        // Get Author ID before deleting
                        $this->db->query("SELECT user_id FROM posts WHERE id = :id");
                        $this->db->bind(':id', $cr->content_id);
                        $post = $this->db->single();
                        if ($post) $authorId = $post->user_id;
                    } 
                    // Add chat_message logic here if needed later
                }

                // 2. Delete the Content
                if ($contentTable && $cr->content_id) {
                    $this->db->query("DELETE FROM $contentTable WHERE id = :id");
                    $this->db->bind(':id', $cr->content_id);
                    $this->db->execute();
                }

                // 3. Notify the Author
                if ($authorId) {
                    $notificationType = 'system_warning'; // Or create a new type 'content_removed'
                    $notificationMessage = "Your content was removed because it violated our community guidelines.";
                    
                    $this->notificationModel->createNotification([
                        'user_id' => $authorId,
                        'type' => $notificationType,
                        'message' => $notificationMessage,
                        'project_id' => null,
                        'task_id' => null,
                        'is_read' => 0
                    ]);
                }
                
                $newStatus = 'resolved';
            }
        }
        
        elseif ($action === 'resolved') {
             // Optional: You could notify them that "No action was taken" or just leave it silent.
             $newStatus = 'resolved';
        }
        elseif ($action === 'dismissed') {
             $newStatus = 'dismissed';
        }

        // 3. Send the Notification (If message is set and we found the user)
        if ($offenderId && !empty($notificationMessage)) {
            $this->notificationModel->createNotification([
                'user_id' => $offenderId,
                'type' => $notificationType,
                'message' => $notificationMessage,
                'project_id' => null,
                'task_id' => null,
                'is_read' => 0
            ]);
        }

        // 4. Update the Report Status in DB
        if ($table) {
            $this->db->query("UPDATE $table SET status = :status WHERE id = :id");
            $this->db->bind(':status', $newStatus);
            $this->db->bind(':id', $reportId);

            if ($this->db->execute()) {
                echo json_encode(['success' => true, 'message' => 'Action completed & user notified.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid report type.']);
        }
    }
}
