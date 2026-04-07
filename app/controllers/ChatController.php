<?php
// app/controllers/ChatController.php

class ChatController extends Controller
{
    private $projectModel;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit();
        }
        $this->projectModel = $this->model('Project');
    }

    // /chat/index/{projectId}
    public function index($projectId = null)
    {
        if (!$projectId) {
            header('Location: ' . URLROOT . '/organization/chats');
            exit();
        }

        $project = $this->projectModel->getProjectById($projectId);
        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            header('Location: ' . URLROOT . '/organization/chats');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'] ?? null;

        // Security: must be owner or active member
        $isOwner = ($role === 'organization' && $project->organization_id == $userId);
        $isMember = $this->projectModel->isUserMember($projectId, $userId);
        if (!$isOwner && !$isMember) {
            $_SESSION['error'] = 'You do not have access to this project chat.';
            header('Location: ' . URLROOT . '/');
            exit();
        }

        // Reuse existing organization chats page for layout
        // The JS on that page will call fetchMessages/sendMessage
        $data = [
            'project' => $project,
            'projectId' => $projectId,
            'members' => $this->projectModel->getProjectMembers($projectId)
        ];

        $this->view('organization/chats', $data);
    }

    // GET /chat/fetchMessages?project_id=..
    public function fetchMessages()
    {
        header('Content-Type: application/json');

        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
        if ($projectId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid project.']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'] ?? null;

        // Security: owner or member only
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project) {
            echo json_encode(['success' => false, 'message' => 'Project not found.']);
            return;
        }
        $isOwner = ($role === 'organization' && $project->organization_id == $userId);
        $isMember = $this->projectModel->isUserMember($projectId, $userId);
        if (!$isOwner && !$isMember) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            return;
        }

        $db = new Database();
        $db->query("SELECT m.id, m.message, m.created_at, u.username AS sender_name, u.profile_picture AS sender_profile_pic, u.id AS sender_id
                    FROM project_chat_messages m
                    JOIN users u ON m.sender_id = u.id
                    WHERE m.project_id = :project_id
                    ORDER BY m.created_at ASC, m.id ASC");
        $db->bind(':project_id', $projectId);
        $rows = $db->resultSet();

        echo json_encode([
            'success' => true,
            'messages' => $rows,
            'current_user_id' => $userId
        ]);
    }

    // POST /chat/sendMessage
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
        $role = $_SESSION['role'] ?? null;
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project) {
            echo json_encode(['success' => false, 'message' => 'Project not found.']);
            return;
        }

        $isOwner = ($role === 'organization' && $project->organization_id == $userId);
        $isMember = $this->projectModel->isUserMember($projectId, $userId);
        if (!$isOwner && !$isMember) {
            echo json_encode(['success' => false, 'message' => 'You are not a member of this project.']);
            return;
        }

        $db = new Database();
        $db->query("INSERT INTO project_chat_messages (project_id, sender_id, message) VALUES (:project_id, :sender_id, :message)");
        $db->bind(':project_id', $projectId);
        $db->bind(':sender_id', $userId);
        $db->bind(':message', $message);
        $ok = $db->execute();

        if ($ok) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
        }
    }

    public function user($partnerId = null)
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . URLROOT . '/auth/signin');
        exit();
    }

    $currentUserId = $_SESSION['user_id'];

    if (!$partnerId) {
        header('Location: ' . URLROOT . '/userdashboard/chats');
        exit();
    }

    $chatModel = $this->model('Chat');
    $userModel = $this->model('User');

    // get or create chat between users
    $chatId = $chatModel->getOrCreateChat($currentUserId, $partnerId);

    // get chat list for sidebar
    $rawChats = $chatModel->getUserChats($currentUserId);
    $allChats = $chatModel->formatChatsForDisplay($rawChats, $currentUserId);

    // get partner info
    $partner = $userModel->getUserById($partnerId);
    if (!$partner) {
        header('Location: ' . URLROOT . '/userdashboard/chats');
        exit();
    }

    $data = [
        'title' => 'Chats',
        'page' => 'chats',
        'allChats' => $allChats,
        'partnerId' => $partnerId,
        'partnerName' => $partner->username,
        'partnerAvatar' => $partner->profile_picture ?? strtoupper(substr($partner->username, 0, 2)),
        'chatId' => $chatId,
        'buckxBalance' => 0
    ];

    $this->view('users/chats', $data);
}

public function fetchUserMessages()
{
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $userId = $_SESSION['user_id'];
    $chatId = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : 0;

    if ($chatId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid chat ID']);
        return;
    }

    $chatModel = $this->model('Chat');

    if (!$chatModel->userHasAccess($chatId, $userId)) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        return;
    }

    $messages = $chatModel->getChatMessages($chatId);
    $chatModel->markAsRead($chatId, $userId);

    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'current_user_id' => $userId
    ]);
}

public function sendUserMessage()
{
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        return;
    }

    $userId = $_SESSION['user_id'];
    $chatId = isset($_POST['chat_id']) ? (int)$_POST['chat_id'] : 0;
    $message = trim($_POST['message'] ?? '');

    if ($chatId <= 0 || $message === '') {
        echo json_encode(['success' => false, 'message' => 'Missing chat or message']);
        return;
    }

    $chatModel = $this->model('Chat');

    if (!$chatModel->userHasAccess($chatId, $userId)) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        return;
    }

    $ok = $chatModel->sendMessage($chatId, $userId, $message);

    echo json_encode([
        'success' => $ok,
        'message' => $ok ? 'Message sent' : 'Failed to send message'
    ]);
}
}

?>
