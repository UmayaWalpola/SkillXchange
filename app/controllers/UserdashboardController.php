<?php

class UserdashboardController extends Controller {
    
    private $db;
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->db = new Database();
    }
    
    private function checkAuth($expectsJson = false) {
        if (!isset($_SESSION['user_id'])) {
            if ($expectsJson) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }

            header('Location: /auth/signin');
            exit;
        }
        return $_SESSION['user_id'];
    }

    // PUBLIC ROUTE METHODS
    // ============================================

    public function index() {
        $userId = $this->checkAuth();
        
        $userData = $this->getUserData($userId);
        $userSkills = $this->getUserSkills($userId);
        $userProjects = $this->getUserProjects($userId);
        $userFeedback = $this->getUserFeedback($userId);
        $userBadges = $this->getUserBadges($userId);
        
        if (!is_array($userData)) {
            die("ERROR: getUserData returned: " . print_r($userData, true));
        }
        
        $userData['badge_count'] = count($userBadges);
        
        $data = [
            'title' => 'My Profile',
            'user' => $userData,
            'skills' => $userSkills,
            'projects' => $userProjects,
            'feedback' => $userFeedback,
            'badges' => $userBadges,
            'is_own_profile' => true,
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
        $chatModel = $this->model('Chat');

       $rawChats = $chatModel->getUserChats($userId);
       $allChats = $chatModel->formatChatsForDisplay($rawChats, $userId);


   $data = [
       'title' => 'Chats',
       'user' => $user,
       'page' => 'chats',
       'allChats' => $allChats,
       'buckxBalance' => 0
   ];


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
            echo json_encode(['success' => true, 'message' => 'Connection request sent successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send connection request']);
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
        
        echo json_encode(['success' => true, 'matches' => $matches]);
        exit;
    }

    // ============================================
    // COMMUNITY METHODS
    // ============================================

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
            echo json_encode(['success' => true, 'message' => 'Successfully joined community!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to join community']);
        }
        exit;
    }

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
            echo json_encode(['success' => true, 'message' => 'Successfully left community']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to leave community (owners cannot leave)']);
        }
        exit;
    }

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
        
        if (!$communityModel->isMember($userId, $communityId)) {
            echo json_encode(['success' => false, 'message' => 'You must be a member to post']);
            exit;
        }
        
        $postId = $communityModel->createPost($userId, $communityId, $content);
        
        if ($postId) {
            echo json_encode(['success' => true, 'message' => 'Message posted successfully', 'post_id' => $postId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to post message']);
        }
        exit;
    }

    public function getCommunityMessages() {
        header('Content-Type: application/json');
        
        $userId = $this->checkAuth();
        $communityId = $_GET['community_id'] ?? null;
        
        if (!$communityId) {
            echo json_encode(['success' => false, 'message' => 'Community ID required']);
            exit;
        }
        
        $communityModel = $this->model('Community');
        
        if (!$communityModel->isMember($userId, $communityId)) {
            echo json_encode(['success' => false, 'message' => 'Not a member']);
            exit;
        }
        
        $posts = $communityModel->getCommunityPosts($communityId);
        
        echo json_encode(['success' => true, 'posts' => $posts]);
        exit;
    }

    public function createCommunity() {
        $userId = $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name        = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $about       = trim($_POST['about'] ?? '');
            $icon        = $_POST['icon'] ?? '🌐';
            $category    = $_POST['category'] ?? null;
            
            $errors = [];
            
            if (empty($name)) $errors[] = 'Community name is required';
            if (empty($description)) $errors[] = 'Description is required';
            
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
    // QUIZ METHODS
    // ============================================

    public function quiz() {
        $userId = $this->checkAuth();
        $user = $this->getUserData($userId);
        
        $quizModel = $this->model('Quiz');
        $dbQuizzes = $quizModel->getQuizzesForUser($userId);
        
        $formattedQuizzes = array_map(function($quiz) {
            $quizArray = is_object($quiz) ? (array)$quiz : $quiz;
            return [
                'id'            => $quizArray['quiz_id'] ?? $quizArray['id'],
                'title'         => $quizArray['title'],
                'description'   => $quizArray['description'] ?? '',
                'difficulty'    => $quizArray['difficulty_level'],
                'rewardAmount'  => (int)($quizArray['reward_amount'] ?? 0),
                'category'      => $quizArray['category'] ?? 'General',
                'questionCount' => $quizArray['total_questions'],
                'timeLimit'     => $quizArray['duration'],
                'status'        => $quizArray['user_status'] ?? 'not_started',
                'lastScore'     => isset($quizArray['last_score']) ? round($quizArray['last_score'], 1) : null,
                'isPremium'     => false,
                'badge'         => null
            ];
        }, $dbQuizzes);
        
        $data = [
            'title'   => 'Quizzes',
            'user'    => $user,
            'page'    => 'quiz',
            'quizzes' => $formattedQuizzes
        ];
        
        $this->view('users/quiz', $data);
    }

    public function toggleSaveQuiz() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        
        $userId = $this->checkAuth();
        $quizId = $_POST['quiz_id'] ?? null;
        $action = $_POST['action'] ?? null;
        
        if (!$quizId || !$action) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit;
        }
        
        $quizModel = $this->model('Quiz');
        
        if ($action === 'save') {
            $result  = $quizModel->saveQuizForUser($userId, $quizId);
            $message = 'Quiz saved for later!';
        } else {
            $result  = $quizModel->unsaveQuizForUser($userId, $quizId);
            $message = 'Quiz removed from saved';
        }
        
        echo json_encode(['success' => $result, 'message' => $message]);
        exit;
    }

    public function takeQuiz($quizId = null) {
        if (!$quizId) {
            header('Location: ' . URLROOT . '/userdashboard/quiz');
            exit;
        }
        
        $userId = $this->checkAuth();
        $user   = $this->getUserData($userId);
        
        $quizModel = $this->model('Quiz');
        $quiz      = $quizModel->getQuizById($quizId);
        
        if (!$quiz || $quiz['status'] !== 'active') {
            header('Location: ' . URLROOT . '/userdashboard/quiz');
            exit;
        }
        
        $dbQuestions = $quizModel->getQuizQuestions($quizId);
        
        $quizData = [
            'id'            => $quiz['quiz_id'] ?? $quiz['id'],
            'title'         => $quiz['title'],
            'description'   => $quiz['description'] ?? '',
            'difficulty'    => $quiz['difficulty_level'],
            'rewardAmount'  => (int)($quiz['reward_amount'] ?? 0),
            'questionCount' => $quiz['total_questions'],
            'timeLimit'     => $quiz['duration'],
            'badge'         => null,
            'questions'     => array_map(function($q) {
                return [
                    'id'       => $q['question_id'],
                    'question' => $q['question_text'],
                    'options'  => [
                        $q['option_a'] ?? '',
                        $q['option_b'] ?? '',
                        $q['option_c'] ?? '',
                        $q['option_d'] ?? ''
                    ]
                ];
            }, $dbQuestions)
        ];
        
        $attemptId          = $quizModel->startAttempt($userId, $quizId, count($dbQuestions));
        $quizData['attempt_id'] = $attemptId;
        
        $data = [
            'title' => 'Take Quiz - ' . $quiz['title'],
            'user'  => $user,
            'page'  => 'quiz',
            'quiz'  => $quizData
        ];
        
        $this->view('users/take_quiz', $data);
    }

    // ============================================
    // SUBMIT QUIZ - FIXED
    // ============================================

    public function submitQuiz() {
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        error_reporting(E_ALL);
        if (ob_get_level() === 0) {
            ob_start();
        }

        header('Content-Type: application/json');

        $sendJson = function(array $payload, int $statusCode = 200) {
            if (ob_get_level() > 0) {
                ob_clean();
            }
            http_response_code($statusCode);
            echo json_encode($payload);
            exit;
        };

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $sendJson(['success' => false, 'message' => 'Invalid request'], 405);
        }

        $userId = $this->checkAuth(true);

        // Accept both JSON and form-urlencoded submissions
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $data = null;
        if (stripos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            if (!is_array($data)) {
                $sendJson(['success' => false, 'message' => 'Invalid JSON payload'], 400);
            }
        } else {
            $answersRaw     = $_POST['answers'] ?? null;
            $answersDecoded = null;
            if (is_string($answersRaw) && $answersRaw !== '') {
                $answersDecoded = json_decode($answersRaw, true);
            }
            $data = [
                'attempt_id' => $_POST['attempt_id'] ?? null,
                'quiz_id'    => $_POST['quiz_id'] ?? null,
                'answers'    => $answersDecoded ?? $answersRaw,
                'time_taken' => $_POST['time_taken'] ?? 0,
            ];
        }

        error_log("Quiz submission data: " . print_r($data, true));

        $attemptId = $data['attempt_id'] ?? null;
        $quizId    = $data['quiz_id'] ?? null;
        $answers   = $data['answers'] ?? [];
        $timeTaken = $data['time_taken'] ?? 0;

        if (!$quizId) {
            $sendJson(['success' => false, 'message' => 'Quiz ID missing'], 400);
        }

        $quizModel = $this->model('Quiz');

        // Find in-progress attempt if not provided
        if (!$attemptId) {
            $this->db->query("SELECT id FROM user_quiz_attempts WHERE user_id = :uid AND quiz_id = :qid AND status = 'in_progress' ORDER BY started_at DESC LIMIT 1");
            $this->db->bind(':uid', $userId);
            $this->db->bind(':qid', $quizId);
            $row = $this->db->single();
            if ($row && isset($row->id)) {
                $attemptId = $row->id;
            }
        }

        if (!$attemptId) {
            $dbQuestionsForAttempt = $quizModel->getQuizQuestions($quizId);
            $attemptId = $quizModel->startAttempt($userId, $quizId, is_array($dbQuestionsForAttempt) ? count($dbQuestionsForAttempt) : 0);
        }

        if (!$attemptId) {
            $sendJson(['success' => false, 'message' => 'Attempt could not be created'], 500);
        }

        if (empty($answers) || !is_array($answers)) {
            $sendJson(['success' => false, 'message' => 'No answers provided'], 400);
        }

        // Get quiz details
        $quiz = $quizModel->getQuizById($quizId);
        if (!$quiz) {
            $sendJson(['success' => false, 'message' => 'Quiz not found'], 404);
        }

        // Get questions
        $questions = $quizModel->getQuizQuestions($quizId);
        if (empty($questions)) {
            $sendJson(['success' => false, 'message' => 'No questions found'], 404);
        }

        // Normalize answers
        $first        = $answers[0] ?? null;
        $isIndexArray = !is_array($first) && !is_object($first);
        if ($isIndexArray) {
            $normalized = [];
            foreach ($questions as $idx => $q) {
                if (!array_key_exists($idx, $answers)) continue;
                $selected = $answers[$idx];
                if ($selected === null || $selected === '') continue;
                $normalized[] = [
                    'question_id'     => $q['question_id'],
                    'selected_answer' => (int)$selected,
                ];
            }
            $answers = $normalized;
        }

        if (empty($answers)) {
            $sendJson(['success' => false, 'message' => 'No answers provided'], 400);
        }

        // Calculate score
        $correctCount   = 0;
        $totalQuestions = count($questions);

        foreach ($answers as $answer) {
            if (!is_array($answer)) continue;
            $questionId     = $answer['question_id'] ?? null;
            $selectedAnswer = isset($answer['selected_answer']) ? intval($answer['selected_answer']) : null;
            if (!$questionId || $selectedAnswer === null) continue;

            foreach ($questions as $q) {
                if ($q['question_id'] == $questionId) {
                    if (isset($q['correct_answer']) && intval($q['correct_answer']) === $selectedAnswer) {
                        $correctCount++;
                    }
                    break;
                }
            }
        }

        $score  = ($correctCount / $totalQuestions) * 100;
        $passed = $score >= 70;

        // FIX 1: Use stored reward_amount from DB (set by trigger), fallback to difficulty_level
        $rewardAmount = (int)($quiz['reward_amount'] ?? 0);
        if ($rewardAmount <= 0) {
            $difficulty   = $quiz['difficulty_level'] ?? '';
            $key          = strtolower(trim($difficulty));
            $map          = ['beginner' => 10, 'intermediate' => 20, 'expert' => 30];
            $rewardAmount = (int)($map[$key] ?? 0);
        }

        // FIX 2: Check for first completion BEFORE marking attempt complete
        $isFirstCompletion = false;
        if ($passed && $rewardAmount > 0) {
            $this->db->query("
                SELECT COUNT(*) AS cnt FROM user_quiz_attempts 
                WHERE user_id = :uid AND quiz_id = :qid AND status = 'completed'
            ");
            $this->db->bind(':uid', $userId);
            $this->db->bind(':qid', $quizId);
            $prev              = $this->db->single();
            $isFirstCompletion = !($prev && (int)$prev->cnt > 0);
        }

        // FIX 3: NOW mark attempt as complete (after the retake check)
        $quizModel->completeAttempt($attemptId, $correctCount, $totalQuestions, $timeTaken);

        // Check badge eligibility
        $badgeEarned = null;
        if ($passed) {
            $this->db->query("
                SELECT b.* FROM badges b
                INNER JOIN quizzes q ON b.id = q.badge_id
                WHERE q.id = :quiz_id LIMIT 1
            ");
            $this->db->bind(':quiz_id', $quizId);
            $badge = $this->db->single();

            if ($badge) {
                $this->db->query("SELECT id FROM user_badges WHERE user_id = :user_id AND badge_id = :badge_id");
                $this->db->bind(':user_id', $userId);
                $this->db->bind(':badge_id', $badge->id);
                $existingBadge = $this->db->single();

                if (!$existingBadge) {
                    $this->db->query("INSERT INTO user_badges (user_id, badge_id, earned_at) VALUES (:user_id, :badge_id, NOW())");
                    $this->db->bind(':user_id', $userId);
                    $this->db->bind(':badge_id', $badge->id);
                    $this->db->execute();

                    $badgeEarned = [
                        'id'          => $badge->id,
                        'name'        => $badge->name,
                        'description' => $badge->description,
                        'icon'        => $badge->icon
                    ];
                }
            }
        }

        // FIX 4: Award BuckX only when passed AND first completion
        $rewardGiven   = 0;
        $newBalance    = null;
        $rewardMessage = null;

        if ($rewardAmount > 0 && $passed && $isFirstCompletion) {
            $walletModel = $this->model('Wallet');
            $quizTitle   = $quiz['title'] ?? null;
            $ok          = $walletModel->creditQuizReward($userId, $rewardAmount, $quizId, $quizTitle);

            if ($ok) {
                $rewardGiven   = $rewardAmount;
                $newBalance    = $walletModel->getBalance($userId);
                $rewardMessage = "You received {$rewardAmount} Buckx";

                $notificationModel = $this->model('Notification');
                $notificationModel->createNotification([
                    'user_id'    => $userId,
                    'type'       => 'reward',
                    'message'    => $rewardMessage,
                    'project_id' => null,
                    'task_id'    => null,
                    'is_read'    => 0
                ]);
            } else {
                error_log("Quiz reward credit failed for user {$userId}, quiz {$quizId}, amount {$rewardAmount}");
            }
        }

        $sendJson([
            'success'       => true,
            'score'         => round($score, 2),
            'correct'       => $correctCount,
            'total'         => $totalQuestions,
            'passed'        => $passed,
            'badgeEarned'   => $badgeEarned,
            'reward'        => $rewardGiven,
            'newBalance'    => $newBalance,
            'rewardMessage' => $rewardMessage
        ]);
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
                    'name'  => ucwords(str_replace('-', ' ', $skill->skill_name)),
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
                    'title'       => $project->title,
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
                    'date'          => $this->timeAgo($review->created_at),
                    'rating'        => (int)$review->rating,
                    'comment'       => $review->comment
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
        
        if ($diff < 60)       return 'just now';
        if ($diff < 3600)     return floor($diff / 60) . ' minutes ago';
        if ($diff < 86400)    return floor($diff / 3600) . ' hours ago';
        if ($diff < 604800)   return floor($diff / 86400) . ' days ago';
        if ($diff < 2592000)  return floor($diff / 604800) . ' weeks ago';
        if ($diff < 31536000) return floor($diff / 2592000) . ' months ago';
        return floor($diff / 31536000) . ' years ago';
    }

    // ============================================
    // FALLBACK METHODS
    // ============================================

    private function createUserDataFromMatch($match) {
        return [
            'id'              => $match['id'],
            'name'            => $match['name'],
            'username'        => strtolower(str_replace(' ', '', $match['name'])),
            'email'           => $match['email'] ?? strtolower(str_replace(' ', '', $match['name'])) . '@example.com',
            'bio'             => $match['skill'] ?? 'No bio available',
            'avatar'          => $match['avatar'] ?? strtoupper(substr($match['name'], 0, 2)),
            'connections'     => rand(20, 100),
            'skills_taught'   => rand(3, 10),
            'skills_learning' => rand(2, 8),
            'rating'          => 4.5,
            'reviews_count'   => rand(5, 50)
        ];
    }

    private function getSkillsForMatch($userId) {
        return [
            'teaches' => [['name' => 'Web Development', 'level' => 'Intermediate']],
            'learns'  => [['name' => 'Advanced Topics', 'level' => 'Beginner']]
        ];
    }

    private function getProjectsForMatch($userId) {
        return [
            'completed'   => [['title' => 'Sample Project', 'description' => 'A completed project.']],
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
            
            $this->db->query("
                SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
                FROM user_feedback
                WHERE user_id = :user_id
            ");
            $this->db->bind(':user_id', $userId);
            $feedback = $this->db->single();
            
            return [
                'id'              => $user->id,
                'name'            => $user->username,
                'username'        => $user->username,
                'email'           => $user->email,
                'bio'             => $user->bio ?? 'No bio yet.',
                'avatar'          => strtoupper(substr($user->username, 0, 2)),
                'profile_picture' => $user->profile_picture,
                'connections'     => $user->connections,
                'skills_taught'   => $user->skills_taught,
                'skills_learning' => $user->skills_learning,
                'hours_exchanged' => $user->hours_exchanged,
                'rating'          => $feedback->avg_rating ? round($feedback->avg_rating, 1) : 0,
                'reviews_count'   => $feedback->review_count
            ];
            
        } catch (Exception $e) {
            error_log("getUserData error: " . $e->getMessage());
            return [
                'id'              => $userId,
                'name'            => 'User',
                'username'        => 'user',
                'email'           => '',
                'bio'             => 'No bio yet.',
                'avatar'          => 'U',
                'profile_picture' => null,
                'connections'     => 0,
                'skills_taught'   => 0,
                'skills_learning' => 0,
                'hours_exchanged' => 0,
                'rating'          => 0,
                'reviews_count'   => 0
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
                    'title'       => $project->title,
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
                    'date'          => $this->timeAgo($item->created_at),
                    'rating'        => $item->rating,
                    'comment'       => $item->comment
                ];
            }
            
            return $feedback;
            
        } catch (Exception $e) {
            error_log("getUserFeedback error: " . $e->getMessage());
            return [];
        }
    }

    private function getNotifications($userId) {
    $notificationModel = $this->model('Notification');
    $results = $notificationModel->getUserNotifications($userId);
    
    $notifications = [];
    foreach ($results as $n) {
        $notifications[] = [
            'id'      => $n->id,
            'type'    => $n->type,
            'title'   => $n->type === 'reward' ? 'BuckX Reward' : ucfirst($n->type),
            'message' => $n->message,
            'time'    => $this->timeAgo($n->created_at),
            'read'    => (bool)$n->is_read
        ];
    }
    
    return $notifications;
}


    private function getAllCommunities() {
        try {
            $this->db->query("
                SELECT 
                    c.id, c.name, c.description, c.privacy, c.status,
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
                    'id'          => $community->id,
                    'name'        => $community->name,
                    'description' => $community->description ?? 'Join this community',
                    'icon'        => $icons[$index % count($icons)],
                    'members'     => $community->members ?? 0,
                    'totalPosts'  => $community->totalPosts ?? 0,
                    'privacy'     => $community->privacy,
                    'status'      => $community->status
                ];
            }
            
            return $communities;
            
        } catch (Exception $e) {
            error_log("getAllCommunities error: " . $e->getMessage());
            return [];
        }
    }


    private function getUserBadges($userId) {
        try {
            $this->db->query("
                SELECT 
                    b.id, b.name, b.description, b.icon,
                    b.badge_type, b.requirement_type, b.requirement_value,
                    b.color, ub.earned_at
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
                    'id'         => $badge->id,
                    'name'       => $badge->name,
                    'description'=> $badge->description,
                    'icon'       => $badge->icon,
                    'badge_type' => $badge->badge_type,
                    'color'      => $badge->color ?? '#3b82f6',
                    'earned_at'  => $this->timeAgo($badge->earned_at),
                    'earned_date'=> date('M d, Y', strtotime($badge->earned_at))
                ];
            }
            
            return $badges;
            
        } catch (Exception $e) {
            error_log("getUserBadges error: " . $e->getMessage());
            return [];
        }
    }

    private function getUserBadgeCount($userId) {
        try {
            $this->db->query("SELECT COUNT(*) as badge_count FROM user_badges WHERE user_id = :user_id");
            $this->db->bind(':user_id', $userId);
            $result = $this->db->single();
            return $result ? $result->badge_count : 0;
        } catch (Exception $e) {
            error_log("getUserBadgeCount error: " . $e->getMessage());
            return 0;
        }
    }
}