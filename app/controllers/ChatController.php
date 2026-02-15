<?php
// app/controllers/ChatController.php

class ChatController extends Controller
{
    private $db;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit();
        }
        $this->db = new Database();
    }

    /**
     * User-to-User Chat - Main View
     * URL: /chat/user/{partnerId}
     */
    public function user($partnerId = null)
    {
        $userId = $_SESSION['user_id'];
        
        if (!$partnerId) {
            // Redirect to chats list if no partner specified
            header('Location: ' . URLROOT . '/userdashboard/chats');
            exit();
        }

        // Verify partner exists
        $this->db->query("SELECT id, username, profile_picture FROM users WHERE id = :partner_id");
        $this->db->bind(':partner_id', $partnerId);
        $partner = $this->db->single();

        if (!$partner) {
            $_SESSION['error'] = 'User not found.';
            header('Location: ' . URLROOT . '/userdashboard/chats');
            exit();
        }

        // Verify connection exists (users must be connected to chat)
        $this->db->query("
            SELECT id FROM exchanges 
            WHERE ((requester_id = :user_id AND receiver_id = :partner_id)
                OR (requester_id = :partner_id AND receiver_id = :user_id))
            AND status = 'active'
            LIMIT 1
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':partner_id', $partnerId);
        
        if (!$this->db->single()) {
            $_SESSION['error'] = 'You must be connected with this user to chat.';
            header('Location: ' . URLROOT . '/userdashboard/matches');
            exit();
        }

        // Get or create chat
        $chatId = $this->getOrCreateChat($userId, $partnerId);

        // Get all chats for sidebar
        $allChats = $this->getUserChats($userId);

        // NEW: Get active transaction for this chat (if any)
        $activeTransaction = $this->getActiveTransaction($chatId, $userId);

        // NEW: Get user's BuckX balance
        $this->db->query("SELECT buckx_balance, buckx_frozen FROM users WHERE id = :user_id");
        $this->db->bind(':user_id', $userId);
        $userBalance = $this->db->single();

        $data = [
            'chatId' => $chatId,
            'partnerId' => $partnerId,
            'partnerName' => $partner->username,
            'partnerAvatar' => $partner->profile_picture ?? strtoupper(substr($partner->username, 0, 2)),
            'allChats' => $allChats,
            'currentUserId' => $userId,
            // NEW: Transaction data
            'activeTransaction' => $activeTransaction,
            'buckxBalance' => $userBalance ? ($userBalance->buckx_balance - $userBalance->buckx_frozen) : 0
        ];

        $this->view('users/chats', $data);
    }

    /**
     * NEW: Get active transaction for a chat
     */
    private function getActiveTransaction($chatId, $userId)
    {
        $this->db->query("
            SELECT e.*, 
                   u1.username AS teacher_name,
                   u2.username AS learner_name
            FROM chat_transaction_events e
            INNER JOIN users u1 ON e.teacher_id = u1.id
            INNER JOIN users u2 ON e.learner_id = u2.id
            WHERE e.chat_id = :chat_id 
            AND e.status IN ('pending_learner', 'pending_teacher', 'active', 'teacher_completed')
            ORDER BY e.created_at DESC
            LIMIT 1
        ");
        $this->db->bind(':chat_id', $chatId);
        $event = $this->db->single();

        if (!$event) {
            return null;
        }

        // Determine user's role
        $userRole = ($event->teacher_id == $userId) ? 'teacher' : 'learner';
        $isCreator = ($event->status === 'pending_learner' && $userRole === 'teacher') ||
                     ($event->status === 'pending_teacher' && $userRole === 'learner');

        return [
            'id' => $event->id,
            'payment_type' => $event->payment_type,
            'amount' => $event->amount,
            'skill_debt_hours' => $event->skill_debt_hours,
            'skill_name' => $event->skill_name,
            'timeframe_hours' => $event->agreed_timeframe_hours,
            'status' => $event->status,
            'teacher_name' => $event->teacher_name,
            'learner_name' => $event->learner_name,
            'expires_at' => $event->expires_at,
            'teacher_completed_at' => $event->teacher_completed_at,
            'user_role' => $userRole,
            'is_creator' => $isCreator,
            'both_agreed_at' => $event->both_agreed_at
        ];
    }

    /**
     * Get or create a chat between two users
     */
    private function getOrCreateChat($userId1, $userId2)
    {
        // Check if chat exists
        $this->db->query("
            SELECT id FROM chats 
            WHERE (user1_id = :user1 AND user2_id = :user2)
               OR (user1_id = :user2 AND user2_id = :user1)
            LIMIT 1
        ");
        $this->db->bind(':user1', $userId1);
        $this->db->bind(':user2', $userId2);
        
        $chat = $this->db->single();
        
        if ($chat) {
            return $chat->id;
        }

        // Create new chat
        $this->db->query("
            INSERT INTO chats (user1_id, user2_id, created_at)
            VALUES (:user1, :user2, NOW())
        ");
        $this->db->bind(':user1', $userId1);
        $this->db->bind(':user2', $userId2);
        $this->db->execute();

        return $this->db->lastInsertId();
    }

    /**
     * Get all user's chats for sidebar
     */
    private function getUserChats($userId)
    {
        $this->db->query("
            SELECT 
                c.id as chat_id,
                CASE 
                    WHEN c.user1_id = :user_id THEN c.user2_id
                    ELSE c.user1_id
                END as partner_id,
                CASE 
                    WHEN c.user1_id = :user_id THEN u2.username
                    ELSE u1.username
                END as partner_name,
                CASE 
                    WHEN c.user1_id = :user_id THEN u2.profile_picture
                    ELSE u1.profile_picture
                END as partner_avatar,
                cm.message as last_message,
                cm.created_at as last_message_time,
                (SELECT COUNT(*) FROM chat_messages 
                 WHERE chat_id = c.id 
                 AND sender_id != :user_id 
                 AND read_status = 0) as unread_count
            FROM chats c
            INNER JOIN users u1 ON c.user1_id = u1.id
            INNER JOIN users u2 ON c.user2_id = u2.id
            LEFT JOIN (
                SELECT chat_id, message, created_at
                FROM chat_messages cm1
                WHERE id = (
                    SELECT MAX(id) 
                    FROM chat_messages cm2 
                    WHERE cm2.chat_id = cm1.chat_id
                )
            ) cm ON c.id = cm.chat_id
            WHERE c.user1_id = :user_id OR c.user2_id = :user_id
            ORDER BY cm.created_at DESC
        ");
        
        $this->db->bind(':user_id', $userId);
        $results = $this->db->resultSet();

        $chats = [];
        foreach ($results as $row) {
            $chats[] = [
                'id' => $row->chat_id,
                'partner_id' => $row->partner_id,
                'name' => $row->partner_name,
                'avatar' => $row->partner_avatar ?? strtoupper(substr($row->partner_name, 0, 2)),
                'lastMessage' => $row->last_message ?? 'No messages yet',
                'time' => $this->timeAgo($row->last_message_time ?? date('Y-m-d H:i:s')),
                'unread' => $row->unread_count > 0,
                'unreadCount' => $row->unread_count ?? 0
            ];
        }

        return $chats;
    }

    /**
     * Fetch messages for a chat
     * GET /chat/fetchUserMessages?chat_id=X
     */
    public function fetchUserMessages()
    {
        header('Content-Type: application/json');

        $chatId = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : 0;
        if ($chatId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid chat ID.']);
            return;
        }

        $userId = $_SESSION['user_id'];

        // Verify user is part of this chat
        $this->db->query("
            SELECT * FROM chats 
            WHERE id = :chat_id 
            AND (user1_id = :user_id OR user2_id = :user_id)
        ");
        $this->db->bind(':chat_id', $chatId);
        $this->db->bind(':user_id', $userId);
        
        if (!$this->db->single()) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            return;
        }

        // Get messages
        $this->db->query("
            SELECT 
                m.id, 
                m.message, 
                m.created_at, 
                m.sender_id,
                u.username AS sender_name, 
                u.profile_picture AS sender_profile_pic
            FROM chat_messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.chat_id = :chat_id
            ORDER BY m.created_at ASC, m.id ASC
        ");
        $this->db->bind(':chat_id', $chatId);
        $messages = $this->db->resultSet();

        // Mark messages as read
        $this->markMessagesAsRead($chatId, $userId);

        // NEW: Get active transaction
        $activeTransaction = $this->getActiveTransaction($chatId, $userId);

        echo json_encode([
            'success' => true,
            'messages' => $messages,
            'current_user_id' => $userId,
            'active_transaction' => $activeTransaction
        ]);
    }

    /**
     * Send a message
     * POST /chat/sendUserMessage
     */
    public function sendUserMessage()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $chatId = isset($_POST['chat_id']) ? (int)$_POST['chat_id'] : 0;
        $message = trim($_POST['message'] ?? '');

        if ($chatId <= 0 || $message === '') {
            echo json_encode(['success' => false, 'message' => 'Missing chat ID or message.']);
            return;
        }

        $userId = $_SESSION['user_id'];

        // Verify user is part of this chat
        $this->db->query("
            SELECT * FROM chats 
            WHERE id = :chat_id 
            AND (user1_id = :user_id OR user2_id = :user_id)
        ");
        $this->db->bind(':chat_id', $chatId);
        $this->db->bind(':user_id', $userId);
        
        if (!$this->db->single()) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            return;
        }

        // Insert message
        $this->db->query("
            INSERT INTO chat_messages (chat_id, sender_id, message, created_at) 
            VALUES (:chat_id, :sender_id, :message, NOW())
        ");
        $this->db->bind(':chat_id', $chatId);
        $this->db->bind(':sender_id', $userId);
        $this->db->bind(':message', $message);
        
        if ($this->db->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
        }
    }

    /**
     * Mark messages as read
     */
    private function markMessagesAsRead($chatId, $userId)
    {
        $this->db->query("
            UPDATE chat_messages 
            SET read_status = 1 
            WHERE chat_id = :chat_id 
            AND sender_id != :user_id 
            AND read_status = 0
        ");
        $this->db->bind(':chat_id', $chatId);
        $this->db->bind(':user_id', $userId);
        $this->db->execute();
    }

    /**
     * Time ago helper
     */
    private function timeAgo($timestamp)
    {
        $time = strtotime($timestamp);
        $diff = time() - $time;
        
        if ($diff < 60) return 'just now';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('M j', $time);
    }

    // ===== PROJECT CHAT METHODS (Keep existing) =====
    
    public function index($projectId = null)
    {
        if (!$projectId) {
            header('Location: ' . URLROOT . '/organization/chats');
            exit();
        }

        $projectModel = $this->model('Project');
        $project = $projectModel->getProjectById($projectId);
        
        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            header('Location: ' . URLROOT . '/organization/chats');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'] ?? null;

        $isOwner = ($role === 'organization' && $project->organization_id == $userId);
        $isMember = $projectModel->isUserMember($projectId, $userId);
        
        if (!$isOwner && !$isMember) {
            $_SESSION['error'] = 'You do not have access to this project chat.';
            header('Location: ' . URLROOT . '/');
            exit();
        }

        $data = [
            'project' => $project,
            'projectId' => $projectId,
            'members' => $projectModel->getProjectMembers($projectId)
        ];

        $this->view('organization/chats', $data);
    }

    public function fetchMessages()
    {
        header('Content-Type: application/json');

        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
        if ($projectId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid project.']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $projectModel = $this->model('Project');
        $project = $projectModel->getProjectById($projectId);
        
        if (!$project) {
            echo json_encode(['success' => false, 'message' => 'Project not found.']);
            return;
        }

        $this->db->query("
            SELECT m.id, m.message, m.created_at, 
                   u.username AS sender_name, 
                   u.profile_picture AS sender_profile_pic, 
                   u.id AS sender_id
            FROM project_chat_messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.project_id = :project_id
            ORDER BY m.created_at ASC, m.id ASC
        ");
        $this->db->bind(':project_id', $projectId);
        $rows = $this->db->resultSet();

        echo json_encode([
            'success' => true,
            'messages' => $rows,
            'current_user_id' => $userId
        ]);
    }

    public function sendMessage()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $projectId = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
        $message = trim($_POST['message'] ?? '');

        if ($projectId <= 0 || $message === '') {
            echo json_encode(['success' => false, 'message' => 'Missing project or message.']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $projectModel = $this->model('Project');
        $project = $projectModel->getProjectById($projectId);
        
        if (!$project) {
            echo json_encode(['success' => false, 'message' => 'Project not found.']);
            return;
        }

        $this->db->query("
            INSERT INTO project_chat_messages (project_id, sender_id, message) 
            VALUES (:project_id, :sender_id, :message)
        ");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':sender_id', $userId);
        $this->db->bind(':message', $message);
        
        if ($this->db->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
        }
    }
}
?>