<?php

class UserdashboardController extends Controller {
    
    private $db;
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->db = new Database();
    }
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/signin');
            exit;
        }
        return $_SESSION['user_id'];
    }

    // ====================
    
    

    // PUBLIC ROUTE METHODS
    // ============================================

    public function index() {
        $userId = $this->checkAuth();
        
        $userData = $this->getUserData($userId);
        $userSkills = $this->getUserSkills($userId);
        $userProjects = $this->getUserProjects($userId);
        $userFeedback = $this->getUserFeedback($userId);
        $matches = $this->skillMatchModel->getAllMatchesWithScores($userId);

        if (!is_array($userData)) {
            die("ERROR: getUserData returned: " . print_r($userData, true));
        }
        
        $data = [
            'title' => 'My Profile',
            'user' => $userData,
            'skills' => $userSkills,
            'projects' => $userProjects,
            'feedback' => $userFeedback,
            'page' => 'profile'
        ];
        
        $this->view('users/profile', $data);
    }
    
    // Add badge count to user data
    $userData['badge_count'] = count($userBadges);
    
    $data = [
        'title' => 'My Profile',
        'user' => $userData,
        'skills' => $userSkills,
        'projects' => $userProjects,
        'feedback' => $userFeedback,
        'badges' => $userBadges, // NEW: Pass badges to view
        'page' => 'profile'
    ];
    
    $this->view('users/profile', $data);
}

    public function notifications() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        $notifications = $this->getNotifications($userId);
        
        $data = [
            'title' => 'Notifications',
            'user' => $user,
            'page' => 'notifications',
            'notifications' => $notifications
        ];
        
        $this->view('users/notifications', $data);
    }

public function chats() {
    $userId = $this->checkAuth();
    $user = $this->getUserData($userId);
    $chats = $this->getActiveChats($userId);

    // Prepare base data (sidebar expects `allChats`)
    $data = [
        'title' => 'Chats',
        'user' => $user,
        'page' => 'chats',
        'chats' => $chats,
        'allChats' => $chats
    ];

    // If partnerId provided, load active chat context so the same route shows the conversation
    if (isset($_GET['partnerId']) && is_numeric($_GET['partnerId'])) {
        $partnerId = (int) $_GET['partnerId'];

        // Verify partner exists
        $this->db->query("SELECT id, username, profile_picture FROM users WHERE id = :partner_id");
        $this->db->bind(':partner_id', $partnerId);
        $partner = $this->db->single();

        if (!$partner) {
            $_SESSION['error'] = 'User not found.';
            // Render chats list without active chat
            $this->view('users/chats', $data);
            return;
        }

        // Verify active connection exists (users must be connected to chat)
        $this->db->query("\n            SELECT id FROM exchanges \n            WHERE ((requester_id = :user_id AND receiver_id = :partner_id)\n                OR (requester_id = :partner_id AND receiver_id = :user_id))\n            AND status = 'active'\n            LIMIT 1\n        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':partner_id', $partnerId);
        $connection = $this->db->single();

        if (!$connection) {
            $_SESSION['error'] = 'You must be connected with this user to chat.';
            header('Location: ' . URLROOT . '/userdashboard/matches');
            exit();
        }

        // Get or create chat between users
        $this->db->query("\n            SELECT id FROM chats \n            WHERE (user1_id = :user1 AND user2_id = :user2)\n               OR (user1_id = :user2 AND user2_id = :user1)\n            LIMIT 1\n        ");
        $this->db->bind(':user1', $userId);
        $this->db->bind(':user2', $partnerId);
        $chat = $this->db->single();

        if ($chat) {
            $chatId = $chat->id;
        } else {
            $this->db->query("INSERT INTO chats (user1_id, user2_id, created_at) VALUES (:user1, :user2, NOW())");
            $this->db->bind(':user1', $userId);
            $this->db->bind(':user2', $partnerId);
            $this->db->execute();
            $chatId = $this->db->lastInsertId();
        }

        // Get active transaction for this chat (if any)
        $this->db->query("\n            SELECT e.*, \n                   u1.username AS teacher_name,\n                   u2.username AS learner_name\n            FROM chat_transaction_events e\n            INNER JOIN users u1 ON e.teacher_id = u1.id\n            INNER JOIN users u2 ON e.learner_id = u2.id\n            WHERE e.chat_id = :chat_id \n            AND e.status IN ('pending_learner', 'pending_teacher', 'active', 'teacher_completed')\n            ORDER BY e.created_at DESC\n            LIMIT 1\n        ");
        $this->db->bind(':chat_id', $chatId);
        $event = $this->db->single();

        $activeTransaction = null;
        if ($event) {
            $userRole = ($event->teacher_id == $userId) ? 'teacher' : 'learner';
            $isCreator = ($event->status === 'pending_learner' && $userRole === 'teacher') ||
                         ($event->status === 'pending_teacher' && $userRole === 'learner');

            $activeTransaction = [
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

        // Get user's BuckX balance
        $this->db->query("SELECT buckx_balance, buckx_frozen FROM users WHERE id = :user_id");
        $this->db->bind(':user_id', $userId);
        $userBalance = $this->db->single();

        // Merge active chat data into view data
        $data = array_merge($data, [
            'chatId' => $chatId,
            'partnerId' => $partnerId,
            'partnerName' => $partner->username,
            'partnerAvatar' => $partner->profile_picture ?? strtoupper(substr($partner->username, 0, 2)),
            'allChats' => $chats,
            'activeTransaction' => $activeTransaction,
            'buckxBalance' => $userBalance ? ($userBalance->buckx_balance - $userBalance->buckx_frozen) : 0
        ]);
    }

    $this->view('users/chats', $data);
}

private function getActiveChats($userId) {
    try {
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
                COALESCE(cm.created_at, c.created_at) as last_message_time,
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
            WHERE (c.user1_id = :user_id OR c.user2_id = :user_id)
            ORDER BY last_message_time DESC
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
                'time' => $this->timeAgo($row->last_message_time),
                'unread' => $row->unread_count > 0,
                'unreadCount' => $row->unread_count ?? 0,
                'online' => false
            ];
        }
        
        return $chats;
        
    } catch (Exception $e) {
        error_log("getActiveChats error: " . $e->getMessage());
        return [];
    }
}


public function matches() {
    $userId = $this->checkAuth();
    
    $skillMatchModel = $this->model('SkillMatch');
    $exchangeModel = $this->model('Exchange');
    
    // Get all matches with new tier system (mutual, multi, single)
    $allMatches = $skillMatchModel->getAllMatchesWithScores($userId);

    // Ensure arrays exist (in case model returns empty)
    $mutual = isset($allMatches['mutual']) && is_array($allMatches['mutual']) ? $allMatches['mutual'] : [];
    $multi = isset($allMatches['multi']) && is_array($allMatches['multi']) ? $allMatches['multi'] : [];
    $single = isset($allMatches['single']) && is_array($allMatches['single']) ? $allMatches['single'] : [];
    
    // Get pending connection requests
    $pendingRequests = $exchangeModel->getExchangeRequests($userId);

    $formattedRequests = [];
    foreach ($pendingRequests as $request) {
        $formattedRequests[] = [
            'exchange_id' => $request->id,
            'sender_id' => $request->requester_id,
            'sender_name' => $request->sender_name,
            'sender_email' => $request->sender_email,
            'sender_avatar' => $request->sender_avatar ?? strtoupper(substr($request->sender_name, 0, 2)),
            'skill_offered' => $request->skill_offered,
            'skill_wanted' => $request->skill_wanted,
            'time_ago' => $this->timeAgo($request->created_at)
        ];
    }   
    
    $userSkillsData = $skillMatchModel->getUserSkillsForFilter($userId);
    $user = $this->getUserData($userId);
    
    $data = [
        'title' => 'Matches',
        'user' => $user,
        'page' => 'matches',
        // Pass the three tier arrays
        'mutual' => $mutual,
        'multi' => $multi,
        'single' => $single,
        // Match statistics
        'matchStats' => [
            'total_count' => count($mutual) + count($multi) + count($single),
            'mutual_count' => count($mutual),
            'multi_count' => count($multi),
            'single_count' => count($single)
        ],
        'userSkills' => $userSkillsData,
        'pendingRequests' => $formattedRequests
    ];
    
    $this->view('users/matches', $data);
}
/**
 * Handle accept/reject connection requests
 */
public function handleRequest() {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    
    $currentUserId = $this->checkAuth();
    $exchangeId = $_POST['exchange_id'] ?? null;
    $action = $_POST['action'] ?? null;
    
    if (!$exchangeId || !$action) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }
    
    $exchangeModel = $this->model('Exchange');
    
    if ($action === 'accept') {
        $result = $exchangeModel->acceptExchange($exchangeId, $currentUserId);
        $message = $result ? 'Connection request accepted!' : 'Failed to accept request';
    } elseif ($action === 'reject') {
        $result = $exchangeModel->rejectExchange($exchangeId, $currentUserId);
        $message = $result ? 'Connection request rejected' : 'Failed to reject request';
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }
    
    echo json_encode([
        'success' => $result,
        'message' => $message
    ]);
    exit;
}
    public function viewProfile($userId = null) {
        $currentUserId = $this->checkAuth();
        
        if (!$userId || $userId == $currentUserId) {
            header('Location: ' . URLROOT . '/userdashboard/matches');
            exit;
        }
        
        try {
            $userModel = $this->model('User');
            $userData = $userModel->getUserById($userId);
            
            if (!$userData) {
                throw new Exception('User not found');
            }
            
            $userArray = [
                'id' => $userData->id,
                'name' => $userData->name ?? 'Unknown User',
                'username' => $userData->username ?? strtolower(str_replace(' ', '', $userData->name ?? '')),
                'email' => $userData->email ?? '',
                'bio' => $userData->bio ?? 'No bio available',
                'avatar' => $userData->avatar ?? strtoupper(substr($userData->name ?? 'U', 0, 2)),
                'connections' => $userData->connections ?? 0,
                'rating' => $userData->rating ?? 0.0,
                'reviews_count' => $userData->reviews_count ?? 0
            ];
            
            $userSkills = $this->getUserSkillsFromDB($userId);
            $userProjects = $this->getUserProjectsFromDB($userId);
            $userFeedback = $this->getUserFeedbackFromDB($userId);
            
        } catch (Exception $e) {
            $allMatches = array_merge(
                $this->getTeachMatches($currentUserId), 
                $this->getLearnMatches($currentUserId)
            );
            
            foreach ($allMatches as $match) {
                if ($match['id'] == $userId) {
                    $userArray = $this->createUserDataFromMatch($match);
                    $userSkills = $this->getSkillsForMatch($userId);
                    $userProjects = $this->getProjectsForMatch($userId);
                    $userFeedback = $this->getFeedbackForMatch($userId);
                    break;
                }
            }
            
            if (!isset($userArray)) {
                header('Location: ' . URLROOT . '/userdashboard/matches');
                exit;
            }
        }
        
        $userArray['skills_taught'] = count($userSkills['teaches'] ?? []);
        $userArray['skills_learning'] = count($userSkills['learns'] ?? []);
        
        $data = [
            'title' => $userArray['name'] . "'s Profile",
            'user' => $userArray,
            'skills' => $userSkills,
            'projects' => $userProjects,
            'feedback' => $userFeedback,
            'page' => 'matches',
            'currentUserId' => $currentUserId
        ];
        
        $this->view('users/view_profile', $data);
    }

    public function connect() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $currentUserId = $this->checkAuth();
        $targetUserId = $_POST['user_id'] ?? null;
        
        if (!$targetUserId) {
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            exit;
        }
        
        $exchangeModel = $this->model('Exchange');
        $result = $exchangeModel->createExchangeRequest($currentUserId, $targetUserId);
        
        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Connection request sent successfully!'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to send connection request'
            ]);
        }
        exit;
    }

    public function searchMatches() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        
        $currentUserId = $this->checkAuth();
        $skillName = $_POST['skill'] ?? '';
        $matchType = $_POST['type'] ?? 'all';
        
        if (empty($skillName)) {
            echo json_encode(['success' => false, 'message' => 'Skill name is required']);
            exit;
        }
        
        $skillMatchModel = $this->model('SkillMatch');
        $matches = $skillMatchModel->searchMatchesBySkill($currentUserId, $skillName, $matchType);
        
        echo json_encode([
            'success' => true,
            'matches' => $matches
        ]);
        exit;
    }

   
// Add these methods to your UserdashboardController class

public function communities() {
    $userId = $this->checkAuth();
    $user = $this->getUserData($userId);
    
    $communityModel = $this->model('Community');
    $communities = $communityModel->getAllCommunitiesForUser($userId);
    
    $data = [
        'title' => 'Communities',
        'user' => $user,
        'page' => 'communities',
        'communities' => $communities
    ];
    
    $this->view('users/communities', $data);
}

/**
 * View single community
 */
public function viewCommunity($communityId = null) {
    $userId = $this->checkAuth();
    $user = $this->getUserData($userId);
    
    if (!$communityId) {
        header('Location: ' . URLROOT . '/userdashboard/communities');
        exit;
    }
    
    $communityModel = $this->model('Community');
    $community = $communityModel->getCommunityById($communityId, $userId);
    
    if (!$community) {
        $_SESSION['error'] = 'Community not found';
        header('Location: ' . URLROOT . '/userdashboard/communities');
        exit;
    }
    
    $members = $communityModel->getCommunityMembers($communityId);
    $posts = $communityModel->getCommunityPosts($communityId);
    
    $data = [
        'title' => $community->name,
        'user' => $user,
        'page' => 'communities',
        'community' => $community,
        'members' => $members,
        'posts' => $posts
    ];
    
    $this->view('users/community_detail', $data);
}

/**
 * Join community
 */
public function joinCommunity() {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    $userId = $this->checkAuth();
    $communityId = $_POST['community_id'] ?? null;
    
    if (!$communityId) {
        echo json_encode(['success' => false, 'message' => 'Community ID required']);
        exit;
    }
    
    $communityModel = $this->model('Community');
    $result = $communityModel->joinCommunity($userId, $communityId);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Successfully joined community!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to join community'
        ]);
    }
    exit;
}

/**
 * Leave community
 */
public function leaveCommunity() {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    $userId = $this->checkAuth();
    $communityId = $_POST['community_id'] ?? null;
    
    if (!$communityId) {
        echo json_encode(['success' => false, 'message' => 'Community ID required']);
        exit;
    }
    
    $communityModel = $this->model('Community');
    $result = $communityModel->leaveCommunity($userId, $communityId);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Successfully left community'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to leave community (owners cannot leave)'
        ]);
    }
    exit;
}

/**
 * Post message to community
 */
public function postToCommunity() {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    $userId = $this->checkAuth();
    $communityId = $_POST['community_id'] ?? null;
    $content = trim($_POST['content'] ?? '');
    
    if (!$communityId || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    $communityModel = $this->model('Community');
    
    // Check if user is a member
    if (!$communityModel->isMember($userId, $communityId)) {
        echo json_encode(['success' => false, 'message' => 'You must be a member to post']);
        exit;
    }
    
    $postId = $communityModel->createPost($userId, $communityId, $content);
    
    if ($postId) {
        echo json_encode([
            'success' => true,
            'message' => 'Message posted successfully',
            'post_id' => $postId
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to post message'
        ]);
    }
    exit;
}

/**
 * Get community messages (AJAX)
 */
public function getCommunityMessages() {
    header('Content-Type: application/json');
    
    $userId = $this->checkAuth();
    $communityId = $_GET['community_id'] ?? null;
    
    if (!$communityId) {
        echo json_encode(['success' => false, 'message' => 'Community ID required']);
        exit;
    }
    
    $communityModel = $this->model('Community');
    
    // Check if user is a member
    if (!$communityModel->isMember($userId, $communityId)) {
        echo json_encode(['success' => false, 'message' => 'Not a member']);
        exit;
    }
    
    $posts = $communityModel->getCommunityPosts($communityId);
    
    echo json_encode([
        'success' => true,
        'posts' => $posts
    ]);
    exit;
}

/**
 * Create new community
 */
public function createCommunity() {
    $userId = $this->checkAuth();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $about = trim($_POST['about'] ?? '');
        $icon = $_POST['icon'] ?? '🌐';
        $category = $_POST['category'] ?? null;
        
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Community name is required';
        }
        if (empty($description)) {
            $errors[] = 'Description is required';
        }
        
        if (empty($errors)) {
            $communityModel = $this->model('Community');
            $communityId = $communityModel->createCommunity($userId, $name, $description, $about, $icon, $category);
            
            if ($communityId) {
                $_SESSION['success'] = 'Community created successfully!';
                header('Location: ' . URLROOT . '/userdashboard/viewCommunity/' . $communityId);
                exit;
            } else {
                $errors[] = 'Failed to create community';
            }
        }
        
        // Show form with errors
        $user = $this->getUserData($userId);
        $data = [
            'title' => 'Create Community',
            'user' => $user,
            'page' => 'communities',
            'errors' => $errors,
            'old' => $_POST
        ];
        
        $this->view('users/create_community', $data);
    } else {
        // Show create form
        $user = $this->getUserData($userId);
        $data = [
            'title' => 'Create Community',
            'user' => $user,
            'page' => 'communities',
            'errors' => []
        ];
        
        $this->view('users/create_community', $data);
    }
}

// ============================================
// QUIZ METHODS - UPDATED
// ============================================

public function quiz() {
    $userId = $this->checkAuth();
    $user = $this->getUserData($userId);
    
    // Load the Quiz model
    $quizModel = $this->model('Quiz');
    
    // Fetch quizzes from database
    $dbQuizzes = $quizModel->getQuizzesForUser($userId);
    
    // Format for your existing JavaScript
    $formattedQuizzes = array_map(function($quiz) {
        // Convert object to array if needed
        $quizArray = is_object($quiz) ? (array)$quiz : $quiz;
        
        return [
            'id' => $quizArray['quiz_id'] ?? $quizArray['id'],
            'title' => $quizArray['title'],
            'description' => $quizArray['description'] ?? '',
            'difficulty' => $quizArray['difficulty_level'],
            'category' => $quizArray['category'] ?? 'General',
            'questionCount' => $quizArray['total_questions'],
            'timeLimit' => $quizArray['duration'],
            'status' => $quizArray['user_status'] ?? 'not_started',
            'lastScore' => isset($quizArray['last_score']) ? round($quizArray['last_score'], 1) : null,
            'isPremium' => false,
            'badge' => null
        ];
    }, $dbQuizzes);
    
    $data = [
        'title' => 'Quizzes',
        'user' => $user,
        'page' => 'quiz',
        'quizzes' => $formattedQuizzes
    ];
    
    $this->view('users/quiz', $data);
}

/**
 * Save/unsave quiz (AJAX endpoint)
 */
public function toggleSaveQuiz() {
    header('Content-Type: application/json');
    
    if($_SERVER['REQUEST_METHOD'] != 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    $userId = $this->checkAuth();
    $quizId = $_POST['quiz_id'] ?? null;
    $action = $_POST['action'] ?? null;
    
    if(!$quizId || !$action) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        exit;
    }
    
    $quizModel = $this->model('Quiz');
    
    if($action === 'save') {
        $result = $quizModel->saveQuizForUser($userId, $quizId);
        $message = 'Quiz saved for later!';
    } else {
        $result = $quizModel->unsaveQuizForUser($userId, $quizId);
        $message = 'Quiz removed from saved';
    }
    
    echo json_encode([
        'success' => $result,
        'message' => $message
    ]);
    exit;
}

/**
 * Take quiz - load quiz data from database
 */
public function takeQuiz($quizId = null) {
    if(!$quizId) {
        header('Location: ' . URLROOT . '/userdashboard/quiz');
        exit;
    }
    
    $userId = $this->checkAuth();
    $user = $this->getUserData($userId);
    
    $quizModel = $this->model('Quiz');
    
    // Get quiz from database
    $quiz = $quizModel->getQuizById($quizId);
    
    if(!$quiz || $quiz['status'] !== 'active') {
        header('Location: ' . URLROOT . '/userdashboard/quiz');
        exit;
    }
    
    // Get questions
    $dbQuestions = $quizModel->getQuizQuestions($quizId);
    
    // Format quiz data for your existing view
    $quizData = [
        'id' => $quiz['quiz_id'] ?? $quiz['id'],
        'title' => $quiz['title'],
        'description' => $quiz['description'] ?? '',
        'difficulty' => $quiz['difficulty_level'],
        'questionCount' => $quiz['total_questions'],
        'timeLimit' => $quiz['duration'],
        'badge' => null,
        'questions' => array_map(function($q) {
            return [
                'id' => $q['question_id'],
                'question' => $q['question_text'],
                'options' => [
                    $q['option_a'] ?? '',
                    $q['option_b'] ?? '',
                    $q['option_c'] ?? '',
                    $q['option_d'] ?? ''
                ]
            ];
        }, $dbQuestions)
    ];
    
    // Create attempt record
    $attemptId = $quizModel->startAttempt($userId, $quizId, count($dbQuestions));
    $quizData['attempt_id'] = $attemptId;
    
    $data = [
        'title' => 'Take Quiz - ' . $quiz['title'],
        'user' => $user,
        'page' => 'quiz',
        'quiz' => $quizData
    ];
    
    $this->view('users/take_quiz', $data);
}

/**
 * Submit quiz answers
 */
public function submitQuiz() {
    header('Content-Type: application/json');
    
    if($_SERVER['REQUEST_METHOD'] != 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    $userId = $this->checkAuth();
    
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Log received data for debugging
    error_log("Quiz submission data: " . print_r($data, true));
    
    $attemptId = $data['attempt_id'] ?? null;
    $quizId = $data['quiz_id'] ?? null;
    $answers = $data['answers'] ?? [];
    $timeTaken = $data['time_taken'] ?? 0;
    
    // Validation
    if(!$attemptId) {
        echo json_encode(['success' => false, 'message' => 'Attempt ID missing']);
        exit;
    }
    
    if(!$quizId) {
        echo json_encode(['success' => false, 'message' => 'Quiz ID missing']);
        exit;
    }
    
    if(empty($answers)) {
        echo json_encode(['success' => false, 'message' => 'No answers provided']);
        exit;
    }
    
    $quizModel = $this->model('Quiz');
    
    // Get quiz details
    $quiz = $quizModel->getQuizById($quizId);
    if (!$quiz) {
        echo json_encode(['success' => false, 'message' => 'Quiz not found']);
        exit;
    }
    
    // Get all questions with correct answers
    $questions = $quizModel->getQuizQuestions($quizId);
    
    if (empty($questions)) {
        echo json_encode(['success' => false, 'message' => 'No questions found']);
        exit;
    }
    
    // Calculate score
    $correctCount = 0;
    $totalQuestions = count($answers);
    
    foreach($answers as $answer) {
        $questionId = $answer['question_id'];
        $selectedAnswer = intval($answer['selected_answer']);
        
        // Find the matching question
        $matchedQuestion = null;
        foreach ($questions as $q) {
            if ($q['question_id'] == $questionId) {
                $matchedQuestion = $q;
                break;
            }
        }
        
        if ($matchedQuestion && isset($matchedQuestion['correct_answer'])) {
            if (intval($matchedQuestion['correct_answer']) === $selectedAnswer) {
                $correctCount++;
            }
        }
    }
    
    // Calculate percentage score
    $score = ($correctCount / $totalQuestions) * 100;
    $passed = $score >= 70; // 70% passing score
    
    // Save attempt to database
    $quizModel->completeAttempt($attemptId, $correctCount, $totalQuestions, $timeTaken);
    
    // CHECK FOR BADGE ELIGIBILITY
    $badgeEarned = null;
    
    if ($passed) {
        // Check if quiz has associated badge
        $this->db->query("
            SELECT b.* 
            FROM badges b
            INNER JOIN quizzes q ON b.id = q.badge_id
            WHERE q.id = :quiz_id
            LIMIT 1
        ");
        $this->db->bind(':quiz_id', $quizId);
        $badge = $this->db->single();
        
        if ($badge) {
            // Check if user already has this badge
            $this->db->query("
                SELECT id FROM user_badges 
                WHERE user_id = :user_id AND badge_id = :badge_id
            ");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':badge_id', $badge->id);
            $existingBadge = $this->db->single();
            
            if (!$existingBadge) {
                // Award badge to user
                $this->db->query("
                    INSERT INTO user_badges (user_id, badge_id, earned_at) 
                    VALUES (:user_id, :badge_id, NOW())
                ");
                $this->db->bind(':user_id', $userId);
                $this->db->bind(':badge_id', $badge->id);
                $this->db->execute();
                
                // Return badge info
                $badgeEarned = [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'description' => $badge->description,
                    'icon' => $badge->icon
                ];
            }
        }
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'score' => round($score, 2),
        'correct' => $correctCount,
        'total' => $totalQuestions,
        'passed' => $passed,
        'badgeEarned' => $badgeEarned
    ]);
    exit;
}
    public function wallet() {
        require_once '../app/controllers/WalletController.php';
        $walletController = new WalletController();
        return $walletController->index();
    }

    // ============================================
    // DATABASE HELPER METHODS
    // ============================================

    private function getUserSkillsFromDB($userId) {
        try {
            $this->db->query("
                SELECT skill_name, skill_type, proficiency_level
                FROM user_skills
                WHERE user_id = :user_id
                ORDER BY skill_type DESC, proficiency_level DESC
            ");
            
            $this->db->bind(':user_id', $userId);
            $results = $this->db->resultSet();
            
            $skills = ['teaches' => [], 'learns' => []];
            
            foreach ($results as $skill) {
                $skillData = [
                    'name' => ucwords(str_replace('-', ' ', $skill->skill_name)),
                    'level' => ucfirst($skill->proficiency_level)
                ];
                
                if ($skill->skill_type === 'teach') {
                    $skills['teaches'][] = $skillData;
                } else {
                    $skills['learns'][] = $skillData;
                }
            }
            
            return $skills;
            
        } catch (Exception $e) {
            return $this->getSkillsForMatch($userId);
        }
    }

    private function getUserProjectsFromDB($userId) {
        try {
            $this->db->query("
                SELECT up.project_id, p.title, p.description, p.status
                FROM user_projects up
                INNER JOIN projects p ON up.project_id = p.id
                WHERE up.user_id = :user_id
                ORDER BY p.created_at DESC
            ");
            
            $this->db->bind(':user_id', $userId);
            $results = $this->db->resultSet();
            
            $projects = ['completed' => [], 'in_progress' => []];
            
            foreach ($results as $project) {
                $projectData = [
                    'title' => $project->title,
                    'description' => $project->description
                ];
                
                if ($project->status === 'completed') {
                    $projects['completed'][] = $projectData;
                } else {
                    $projects['in_progress'][] = $projectData;
                }
            }
            
            return $projects;
            
        } catch (Exception $e) {
            return $this->getProjectsForMatch($userId);
        }
    }

    private function getUserFeedbackFromDB($userId) {
        try {
            $this->db->query("
                SELECT uf.rating, uf.comment, uf.created_at, u.name as reviewer_name
                FROM user_feedback uf
                INNER JOIN users u ON uf.reviewer_id = u.id
                WHERE uf.user_id = :user_id
                ORDER BY uf.created_at DESC
                LIMIT 10
            ");
            
            $this->db->bind(':user_id', $userId);
            $results = $this->db->resultSet();
            
            $feedback = [];
            foreach ($results as $review) {
                $feedback[] = [
                    'reviewer_name' => $review->reviewer_name,
                    'date' => $this->timeAgo($review->created_at),
                    'rating' => (int)$review->rating,
                    'comment' => $review->comment
                ];
            }
            
            return $feedback;
            
        } catch (Exception $e) {
            return $this->getFeedbackForMatch($userId);
        }
    }

    private function timeAgo($timestamp) {
        $time = strtotime($timestamp);
        $diff = time() - $time;
        
        if ($diff < 60) return 'just now';
        if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        if ($diff < 604800) return floor($diff / 86400) . ' days ago';
        if ($diff < 2592000) return floor($diff / 604800) . ' weeks ago';
        if ($diff < 31536000) return floor($diff / 2592000) . ' months ago';
        return floor($diff / 31536000) . ' years ago';
    }

    // ============================================
    // FALLBACK METHODS
    // ============================================

    private function createUserDataFromMatch($match) {
        return [
            'id' => $match['id'],
            'name' => $match['name'],
            'username' => strtolower(str_replace(' ', '', $match['name'])),
            'email' => $match['email'] ?? strtolower(str_replace(' ', '', $match['name'])) . '@example.com',
            'bio' => $match['skill'] ?? 'No bio available',
            'avatar' => $match['avatar'] ?? strtoupper(substr($match['name'], 0, 2)),
            'connections' => rand(20, 100),
            'skills_taught' => rand(3, 10),
            'skills_learning' => rand(2, 8),
            'rating' => 4.5,
            'reviews_count' => rand(5, 50)
        ];
    }

    private function getSkillsForMatch($userId) {
        return [
            'teaches' => [['name' => 'Web Development', 'level' => 'Intermediate']],
            'learns' => [['name' => 'Advanced Topics', 'level' => 'Beginner']]
        ];
    }

    private function getProjectsForMatch($userId) {
        return [
            'completed' => [['title' => 'Sample Project', 'description' => 'A completed project.']],
            'in_progress' => []
        ];
    }

    private function getFeedbackForMatch($userId) {
        return [
            ['reviewer_name' => 'John Doe', 'date' => '1 week ago', 'rating' => 5, 'comment' => 'Great to work with!']
        ];
    }

    // ============================================
    // DUMMY DATA METHODS
    // ============================================

    private function getUserData($userId) {
        try {
            // Get user basic info
            $this->db->query("
                SELECT u.id, u.username, u.email, u.bio, u.profile_picture,
                       COALESCE(us.connections_count, 0) as connections,
                       COALESCE(us.skills_taught_count, 0) as skills_taught,
                       COALESCE(us.skills_learning_count, 0) as skills_learning,
                       COALESCE(us.hours_exchanged, 0) as hours_exchanged
                FROM users u
                LEFT JOIN user_stats us ON u.id = us.user_id
                WHERE u.id = :user_id
            ");
            
            $this->db->bind(':user_id', $userId);
            $user = $this->db->single();
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Calculate average rating
            $this->db->query("
                SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
                FROM user_feedback
                WHERE user_id = :user_id
            ");
            $this->db->bind(':user_id', $userId);
            $feedback = $this->db->single();
            
            return [
                'id' => $user->id,
                'name' => $user->username, // Use username as display name
                'username' => $user->username,
                'email' => $user->email,
                'bio' => $user->bio ?? 'No bio yet.',
                'avatar' => strtoupper(substr($user->username, 0, 2)),
                'profile_picture' => $user->profile_picture,
                'connections' => $user->connections,
                'skills_taught' => $user->skills_taught,
                'skills_learning' => $user->skills_learning,
                'hours_exchanged' => $user->hours_exchanged,
                'rating' => $feedback->avg_rating ? round($feedback->avg_rating, 1) : 0,
                'reviews_count' => $feedback->review_count
            ];
            
        } catch (Exception $e) {
            error_log("getUserData error: " . $e->getMessage());
            // Return fallback data
            return [
                'id' => $userId,
                'name' => 'User',
                'username' => 'user',
                'email' => '',
                'bio' => 'No bio yet.',
                'avatar' => 'U',
                'profile_picture' => null,
                'connections' => 0,
                'skills_taught' => 0,
                'skills_learning' => 0,
                'hours_exchanged' => 0,
                'rating' => 0,
                'reviews_count' => 0
            ];
        }
    }

    private function getUserSkills($userId) {
        return $this->getUserSkillsFromDB($userId);
    }

    private function getUserProjects($userId) {
        try {
            $this->db->query("
                SELECT p.id, p.name as title, p.description, p.status
                FROM project_members pm
                INNER JOIN projects p ON pm.project_id = p.id
                WHERE pm.user_id = :user_id AND pm.status = 'active'
                ORDER BY pm.joined_at DESC
            ");
            
            $this->db->bind(':user_id', $userId);
            $results = $this->db->resultSet();
            
            $projects = ['completed' => [], 'in_progress' => []];
            
            foreach ($results as $project) {
                $projectData = [
                    'title' => $project->title,
                    'description' => substr($project->description, 0, 100) . '...'
                ];
                
                if ($project->status === 'completed') {
                    $projects['completed'][] = $projectData;
                } else {
                    $projects['in_progress'][] = $projectData;
                }
            }
            
            return $projects;
            
        } catch (Exception $e) {
            error_log("getUserProjects error: " . $e->getMessage());
            return ['completed' => [], 'in_progress' => []];
        }
    }

    private function getUserFeedback($userId) {
        try {
            $this->db->query("
                SELECT uf.rating, uf.comment, uf.created_at,
                       u.username as reviewer_name
                FROM user_feedback uf
                INNER JOIN users u ON uf.reviewer_id = u.id
                WHERE uf.user_id = :user_id
                ORDER BY uf.created_at DESC
                LIMIT 10
            ");
            
            $this->db->bind(':user_id', $userId);
            $results = $this->db->resultSet();
            
            $feedback = [];
            foreach ($results as $item) {
                $feedback[] = [
                    'reviewer_name' => $item->reviewer_name,
                    'date' => $this->timeAgo($item->created_at),
                    'rating' => $item->rating,
                    'comment' => $item->comment
                ];
            }
            
            return $feedback;
            
        } catch (Exception $e) {
            error_log("getUserFeedback error: " . $e->getMessage());
            return [];
        }
    }

    private function getNotifications($userId) {
        return [
            [
                'id' => 1,
                'type' => 'match',
                'icon' => '❤️',
                'title' => 'New Match!',
                'message' => 'You have a new match with Dr. Kamal Silva',
                'time' => '5 minutes ago',
                'read' => false
            ],
            [
                'id' => 2,
                'type' => 'message',
                'icon' => '💬',
                'title' => 'New Message',
                'message' => 'Sophia Chen sent you a message',
                'time' => '1 hour ago',
                'read' => false
            ]
        ];
    }
    
    private function getChats($userId) {
        return [
            [
                'id' => 1,
                'name' => 'Sophia Chen',
                'lastMessage' => 'Hey! Would love to learn Web Development',
                'time' => '5 min ago',
                'unread' => true,
                'unreadCount' => 3,
                'online' => true,
                'messages' => []
            ]
        ];
    }

    private function getAllCommunities() {
        try {
            $this->db->query("
                SELECT 
                    c.id,
                    c.name,
                    c.description,
                    c.privacy,
                    c.status,
                    COUNT(DISTINCT cm.id) as members,
                    COUNT(DISTINCT p.id) as totalPosts
                FROM communities c
                LEFT JOIN community_members cm ON c.id = cm.community_id
                LEFT JOIN posts p ON c.id = p.community_id
                WHERE c.status = 'active'
                GROUP BY c.id, c.name, c.description, c.privacy, c.status
                ORDER BY members DESC
            ");
            
            $results = $this->db->resultSet();
            
            $communities = [];
            $icons = ['🌐', '🤖', '💻', '📊', '🎨', '📱', '🔧', '📚'];
            
            foreach ($results as $index => $community) {
                $communities[] = [
                    'id' => $community->id,
                    'name' => $community->name,
                    'description' => $community->description ?? 'Join this community',
                    'icon' => $icons[$index % count($icons)],
                    'members' => $community->members ?? 0,
                    'totalPosts' => $community->totalPosts ?? 0,
                    'privacy' => $community->privacy,
                    'status' => $community->status
                ];
            }
            
            return $communities;
            
        } catch (Exception $e) {
            error_log("getAllCommunities error: " . $e->getMessage());
            return [
                [
                    'id' => 1, 
                    'name' => 'Web Development', 
                    'description' => 'A community for web developers',
                    'icon' => '🌐', 
                    'members' => 1250,
                    'totalPosts' => 320,
                    'privacy' => 'public',
                    'status' => 'active'
                ],
                [
                    'id' => 2, 
                    'name' => 'Data Science & AI', 
                    'description' => 'Learn and share data science knowledge',
                    'icon' => '🤖', 
                    'members' => 856,
                    'totalPosts' => 215,
                    'privacy' => 'public',
                    'status' => 'active'
                ]
            ];
        }
    }
    
    private function getTeachMatches($userId) {
        return [
            ['id' => 101, 'name' => 'Sophia Chen', 'skill' => 'Wants to learn Web Development'],
            ['id' => 102, 'name' => 'Ethan Williams', 'skill' => 'Wants to learn UI/UX Design']
        ];
    }

    private function getLearnMatches($userId) {
        return [
            ['id' => 201, 'name' => 'Dr. Kamal Silva', 'skill' => 'Teaches Data Science'],
            ['id' => 202, 'name' => 'Linda Zhang', 'skill' => 'Teaches Machine Learning']
        ];
    }



private function getUserBadges($userId) {
    try {
        $this->db->query("
            SELECT 
                b.id,
                b.name,
                b.description,
                b.icon,
                b.badge_type,
                b.requirement_type,
                b.requirement_value,
                b.color,
                ub.earned_at
            FROM user_badges ub
            INNER JOIN badges b ON ub.badge_id = b.id
            WHERE ub.user_id = :user_id
            ORDER BY ub.earned_at DESC
        ");
        
        $this->db->bind(':user_id', $userId);
        $results = $this->db->resultSet();
        
        $badges = [];
        foreach ($results as $badge) {
            $badges[] = [
                'id' => $badge->id,
                'name' => $badge->name,
                'description' => $badge->description,
                'icon' => $badge->icon,
                'badge_type' => $badge->badge_type,
                'color' => $badge->color ?? '#3b82f6',
                'earned_at' => $this->timeAgo($badge->earned_at),
                'earned_date' => date('M d, Y', strtotime($badge->earned_at))
            ];
        }
        
        return $badges;
        
    } catch (Exception $e) {
        error_log("getUserBadges error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get badge count for user
 */
private function getUserBadgeCount($userId) {
    try {
        $this->db->query("
            SELECT COUNT(*) as badge_count
            FROM user_badges
            WHERE user_id = :user_id
        ");
        
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        
        return $result ? $result->badge_count : 0;
        
    } catch (Exception $e) {
        error_log("getUserBadgeCount error: " . $e->getMessage());
        return 0;
    }
}
}