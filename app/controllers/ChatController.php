<?php
class ChatController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit();
        }
    }

    public function user($partnerId = null)
    {
        $currentUserId = $_SESSION['user_id'];

        if (!$partnerId) {
            header('Location: ' . URLROOT . '/userdashboard/chats');
            exit();
        }

        $chatModel = $this->model('Chat');
        $userModel = $this->model('User');

        $chatId = $chatModel->getOrCreateChat($currentUserId, $partnerId);

        $rawChats = $chatModel->getUserChats($currentUserId);
        $allChats = $chatModel->formatChatsForDisplay($rawChats, $currentUserId);

        $partner = $userModel->getUserById($partnerId);
        if (!$partner) {
            header('Location: ' . URLROOT . '/userdashboard/chats');
            exit();
        }

        $partnerName = $partner['username'] ?? 'Unknown User';
        $partnerAvatar = !empty($partner['profile_picture'])
            ? $partner['profile_picture']
            : strtoupper(substr($partnerName, 0, 2));

        // optional: current user balance for transaction modal
        $db = new Database();
        $db->query("SELECT buckx_balance FROM users WHERE id = :id");
        $db->bind(':id', $currentUserId);
        $me = $db->single();

        $data = [
            'title' => 'Chats',
            'page' => 'chats',
            'allChats' => $allChats,
            'partnerId' => $partnerId,
            'partnerName' => $partnerName,
            'partnerAvatar' => $partnerAvatar,
            'chatId' => $chatId,
            'buckxBalance' => $me->buckx_balance ?? 0
        ];

        $this->view('users/chats', $data);
    }

    public function fetchUserMessages()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['user_id'];
        $chatId = isset($_GET['chat_id']) ? (int) $_GET['chat_id'] : 0;

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

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $chatId = isset($_POST['chat_id']) ? (int) $_POST['chat_id'] : 0;
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
