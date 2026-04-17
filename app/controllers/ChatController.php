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

    /**
     * Project Chat - index method for accessing project-wide chat
     */
    public function index($projectId = null)
    {
        $currentUserId = $_SESSION['user_id'];

        if (!$projectId) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        $projectId = (int)$projectId;
        $projectModel = $this->model('Project');
        $project = $projectModel->getProjectById($projectId);

        if (!$project) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        // Verify user has access to this project
        if ($project->created_by != $currentUserId && !$projectModel->isProjectMember($projectId, $currentUserId)) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        $data = [
            'title'     => 'Project Chat',
            'page'      => 'project-chat',
            'project'   => $project,
            'projectId' => $projectId
        ];

        $this->view('organization/chats', $data);
    }

    public function user($partnerId = null)
    {
        $currentUserId = $_SESSION['user_id'];

        if (!$partnerId) {
            header('Location: ' . URLROOT . '/userdashboard/chats');
            exit();
        }

        $chatModel   = $this->model('Chat');
        $userModel   = $this->model('User');
        $walletModel = $this->model('Wallet');

        $chatId = $chatModel->getOrCreateChat($currentUserId, $partnerId);

        $rawChats  = $chatModel->getUserChats($currentUserId);
        $allChats  = $chatModel->formatChatsForDisplay($rawChats, $currentUserId);

        $partner = $userModel->getUserById($partnerId);
        if (!$partner) {
            header('Location: ' . URLROOT . '/userdashboard/chats');
            exit();
        }

        $partnerName   = $partner['username'] ?? 'Unknown User';
        $partnerAvatar = !empty($partner['profile_picture'])
            ? $partner['profile_picture']
            : strtoupper(substr($partnerName, 0, 2));

        $db = new Database();
        $walletModel->ensureWalletExists($currentUserId, $_SESSION['role'] ?? 'individual');
        $currentWalletBalance = $walletModel->getBalance($currentUserId);

        // ── Active transaction event ──────────────────────────────────────────
        $db->query("
            SELECT *
            FROM chat_transaction_events
            WHERE chat_id = :chat_id
              AND status IN ('pending_learner', 'pending_teacher', 'active', 'teacher_completed')
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $db->bind(':chat_id', $chatId);
        $event = $db->single();

        $activeTransaction = null;

        if ($event) {
            $activeTransaction = [
                'id'                  => $event->id,
                'payment_type'        => $event->payment_type,
                'amount'              => $event->amount,
                'skill_debt_hours'    => $event->skill_debt_hours,
                'skill_name'          => $event->skill_name,
                'timeframe_hours'     => $event->agreed_timeframe_hours,
                'status'              => $event->status,
                'expires_at'          => $event->expires_at,
                'teacher_completed_at'=> $event->teacher_completed_at,
                'user_role'           => ($event->teacher_id == $currentUserId) ? 'teacher' : 'learner',
                'is_creator'          => false
            ];

            if (
                ($event->status === 'pending_learner' && $event->teacher_id  == $currentUserId) ||
                ($event->status === 'pending_teacher' && $event->learner_id  == $currentUserId)
            ) {
                $activeTransaction['is_creator'] = true;
            }
        }

        // ── Match context from URL params (set by matches.js openSkillChat) ──
        // match_type: 'mutual' | 'multi' | 'single'
        // For mutual:       skill  = skill current user teaches, dir = skill current user learns
        // For single/multi: skill  = matched skill name,         dir = 'teacher' | 'learner'
        $matchType = isset($_GET['match_type']) ? trim($_GET['match_type']) : null;
        $matchSkill = isset($_GET['skill'])     ? trim($_GET['skill'])      : null;
        $matchDir   = isset($_GET['dir'])       ? trim($_GET['dir'])        : null;

        // Validate match_type value
        if (!in_array($matchType, ['mutual', 'multi', 'single'])) {
            // Fallback: try to infer from exchange + user_skills if param missing/invalid
            $matchType = $this->inferMatchType($db, $currentUserId, $partnerId);
        }

        // For single/multi: if no dir supplied, infer from user_skills
        if (in_array($matchType, ['single', 'multi']) && !$matchDir) {
            $matchDir = $this->inferDirection($db, $currentUserId, $partnerId);
        }

        $availableSkills = $userModel->getAllSkills();

        $data = [
            'title'             => 'Chats',
            'page'              => 'chats',
            'allChats'          => $allChats,
            'partnerId'         => $partnerId,
            'partnerName'       => $partnerName,
            'partnerAvatar'     => $partnerAvatar,
            'chatId'            => $chatId,
            'buckxBalance'      => $currentWalletBalance,
            'activeTransaction' => $activeTransaction,
            'availableSkills'   => $availableSkills,
            // Match context — consumed by chats.php to adapt the transaction modal
            'matchType'         => $matchType,  // 'mutual' | 'multi' | 'single'
            'matchSkill'        => $matchSkill, // skill name (teach skill for mutual)
            'matchDir'          => $matchDir,   // learn skill for mutual; 'teacher'|'learner' for single/multi
        ];

        $this->view('users/chats', $data);
    }

    /**
     * Infer match type by checking user_skills for both users.
     * Returns 'mutual', 'multi', or 'single'.
     */
    private function inferMatchType(Database $db, $userId, $partnerId)
    {
        // Skills current user teaches that partner wants to learn
        $db->query("
            SELECT COUNT(*) AS cnt
            FROM user_skills t
            INNER JOIN user_skills l ON l.skill_name = t.skill_name
            WHERE t.user_id = :uid AND t.skill_type = 'teach'
              AND l.user_id = :pid AND l.skill_type = 'learn'
        ");
        $db->bind(':uid', $userId);
        $db->bind(':pid', $partnerId);
        $iTeach = (int)($db->single()->cnt ?? 0);

        // Skills partner teaches that current user wants to learn
        $db->query("
            SELECT COUNT(*) AS cnt
            FROM user_skills t
            INNER JOIN user_skills l ON l.skill_name = t.skill_name
            WHERE t.user_id = :pid AND t.skill_type = 'teach'
              AND l.user_id = :uid AND l.skill_type = 'learn'
        ");
        $db->bind(':uid', $userId);
        $db->bind(':pid', $partnerId);
        $theyTeach = (int)($db->single()->cnt ?? 0);

        if ($iTeach > 0 && $theyTeach > 0) return 'mutual';
        $total = $iTeach + $theyTeach;
        return ($total >= 2) ? 'multi' : 'single';
    }

    /**
     * Infer whether current user is 'teacher' or 'learner' relative to partner.
     */
    private function inferDirection(Database $db, $userId, $partnerId)
    {
        $db->query("
            SELECT COUNT(*) AS cnt
            FROM user_skills t
            INNER JOIN user_skills l ON l.skill_name = t.skill_name
            WHERE t.user_id = :uid AND t.skill_type = 'teach'
              AND l.user_id = :pid AND l.skill_type = 'learn'
        ");
        $db->bind(':uid', $userId);
        $db->bind(':pid', $partnerId);
        $iTeach = (int)($db->single()->cnt ?? 0);
        return $iTeach > 0 ? 'teacher' : 'learner';
    }

    // ── AJAX endpoints ────────────────────────────────────────────────────────

    public function fetchUserMessages()
    {
        header('Content-Type: application/json');

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

        $messages = $chatModel->getChatTimeline($chatId);
        $chatModel->markAsRead($chatId, $userId);

        echo json_encode([
            'success'         => true,
            'messages'        => $messages,
            'current_user_id' => $userId,
            'last_item_token' => !empty($messages)
                ? (($messages[count($messages) - 1]->timeline_key ?? '') . '|' . ($messages[count($messages) - 1]->created_at ?? ''))
                : ''
        ]);
    }

    public function sendUserMessage()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $userId  = $_SESSION['user_id'];
        $chatId  = isset($_POST['chat_id']) ? (int)$_POST['chat_id'] : 0;
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
