<?php

class UserdashboardController extends Controller {
    
    public function __construct() {
        // Start session if not started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // TODO: Load User model when database is ready
        // $this->userModel = $this->model('User');
    }
    
    // Check if user is logged in and is a regular user
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/signin');
            exit;
        }
        
        // Optional: Check if user role is 'user' (not admin or manager)
        // Uncomment if you're using roles
        // if (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'user') {
        //     header('Location: /dashboard');
        //     exit;
        // }
        
        return $_SESSION['user_id'];
    }

    // MAIN PAGE - Profile/Dashboard Landing Page
public function index() {
    $userId = $this->checkAuth();
    
    // Get user data
    $userData = $this->getUserData($userId);
    $userSkills = $this->getUserSkills($userId);
    $userProjects = $this->getUserProjects($userId);
    $userFeedback = $this->getUserFeedback($userId);
    
    // DEBUG: Check if data is correct
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

    // Notifications page
    public function notifications() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        // TODO: Get notifications from database
        
        $data = [
            'title' => 'Notifications',
            'user' => $user,
            'page' => 'notifications',
            'notifications' => [] // Add your notifications data here
        ];
        
        $this->view('users/notifications', $data);
    }

    // Chats page
    public function chats() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        // TODO: Get chats from database
        
        $data = [
            'title' => 'Chats',
            'user' => $user,
            'page' => 'chats',
            'chats' => [] // Add your chats data here
        ];
        
        $this->view('users/chats', $data);
    }

    // Matches page
    public function matches() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        // TODO: Get matches from database
        
        $data = [
            'title' => 'Matches',
            'user' => $user,
            'page' => 'matches',
            'matches' => [] // Add your matches data here
        ];
        
        $this->view('users/matches', $data);
    }

    // Communities page
    public function communities() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        // TODO: Get communities from database
        
        $data = [
            'title' => 'Communities',
            'user' => $user,
            'page' => 'communities',
            'communities' => [] // Add your communities data here
        ];
        
        $this->view('users/communities', $data);
    }

    // Quiz page
    public function quiz() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        // TODO: Get quiz data from database
        
        $data = [
            'title' => 'Quiz',
            'user' => $user,
            'page' => 'quiz',
            'quizzes' => [] // Add your quiz data here
        ];
        
        $this->view('users/quiz', $data);
    }

    // Projects page
    public function projects() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        // TODO: Get projects from database
        
        $data = [
            'title' => 'Projects',
            'user' => $user,
            'page' => 'projects',
            'projects' => [] // Add your projects data here
        ];
        
        $this->view('users/projects', $data);
    }

    // Wallet page
    public function wallet() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        
        // Mock wallet data
        $sentTransactions = [
            ['receiver' => 'John Doe', 'amount' => 50, 'timestamp' => '2 hours ago'],
            ['receiver' => 'Jane Smith', 'amount' => 30, 'timestamp' => '1 day ago'],
            ['receiver' => 'Bob Wilson', 'amount' => 20, 'timestamp' => '3 days ago']
        ];
        
        $receivedTransactions = [
            ['sender' => 'Alice Brown', 'amount' => 75, 'timestamp' => '1 hour ago'],
            ['sender' => 'Mike Johnson', 'amount' => 45, 'timestamp' => '2 days ago'],
            ['sender' => 'Sarah Davis', 'amount' => 60, 'timestamp' => '4 days ago']
        ];
        
        // Calculate totals
        $totalSent = array_sum(array_column($sentTransactions, 'amount'));
        $totalReceived = array_sum(array_column($receivedTransactions, 'amount'));
        $balance = 250; // Mock balance
        
        $data = [
            'title' => 'Wallet',
            'user' => $user,
            'page' => 'wallet',
            'balance' => $balance,
            'totalSent' => $totalSent,
            'totalReceived' => $totalReceived,
            'sentTransactions' => $sentTransactions,
            'receivedTransactions' => $receivedTransactions
        ];
        
        $this->view('users/wallet', $data);
    }

    // ==================================================
    // MOCK DATA METHODS (Replace with database later)
    // ==================================================
    
    private function getUserData($userId) {
        return [
        'id' => $userId,
        'name' => 'Sarah Johnson',
        'username' => 'sarahjohnson',
        'email' => 'sarah@example.com',
        'bio' => 'Passionate educator and developer.',
        'avatar' => 'SJ',
        'connections' => 87,
        'skills_taught' => 24,
        'skills_learning' => 12,
        'rating' => 4.8,
        'reviews_count' => 24
        ];
    }

    private function getUserSkills($userId) {
        return [
           'teaches' => [
            ['name' => 'Web Development', 'level' => 'Advanced'],
            ['name' => 'UI/UX Design', 'level' => 'Intermediate'],
            ['name' => 'JavaScript', 'level' => 'Advanced'],
            ['name' => 'React', 'level' => 'Intermediate']
        ],
        'learns' => [
            ['name' => 'Data Science', 'level' => 'Beginner'],
            ['name' => 'Machine Learning', 'level' => 'Beginner'],
            ['name' => 'Python', 'level' => 'Intermediate']
        ]
        ];
    }

    private function getUserProjects($userId) {
        return [
            'completed' => [
                ['title' => 'E-commerce Redesign', 'description' => 'Improved conversion by 25%.'],
                ['title' => 'Portfolio Website', 'description' => 'Built responsive portfolio site.']
            ],
            'in_progress' => [
                ['title' => 'AI Chatbot', 'description' => 'Building an NLP chatbot.']
            ]
        ];
    }

    private function getUserFeedback($userId) {
        return [
            ['reviewer_name' => 'Alex Chen', 'date' => '2 weeks ago', 'rating' => 5, 'comment' => 'Excellent mentor! Very helpful.'],
            ['reviewer_name' => 'Maria Garcia', 'date' => '1 month ago', 'rating' => 5, 'comment' => 'Great teacher, patient and clear.']
        ];
    }

}
