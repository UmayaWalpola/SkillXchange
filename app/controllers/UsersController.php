<?php
// app/controllers/UsersController.php

class UsersController extends Controller {

    public function index() {
        // Redirect to home page
        header('Location: /pages/index');
        exit;
    }

    // Show regular user profile
    public function userprofile($userId = null) {
        if (!$userId) {
            if (!isset($_SESSION['user_id'])) {
                header('Location: /auth/login');
                exit;
            }
            $userId = $_SESSION['user_id'];
        }

        // Mock data (replace with DB queries)
        $user = $this->getUserData($userId);
        $userSkills = $this->getUserSkills($userId);
        $userProjects = $this->getUserProjects($userId);
        $userBadges = $this->getUserBadges($userId);
        $userFeedback = $this->getUserFeedback($userId);
        $userActivity = $this->getUserActivity($userId);

        if (!$user) {
            header('Location: /dashboard');
            exit;
        }

        $data = [
            'user' => $user,
            'skills' => $userSkills,
            'projects' => $userProjects,
            'badges' => $userBadges,
            'feedback' => $userFeedback,
            'activity' => $userActivity
        ];

        $this->view('users/profile', $data);
    }

    // Show admin profile
    public function adminprofile() {
        $adminData = [
            'name' => 'Admin Jane',
            'role' => 'Administrator',
            'email' => 'admin@example.com'
        ];

        $data = ['admin' => $adminData];

        $this->view('users/adminprofile', $data);
    }

    // Show manager profile
    public function managerprofile() {
        $managerData = [
            'name' => 'Manager Bob',
            'role' => 'Manager',
            'email' => 'manager@example.com'
        ];

        $data = ['manager' => $managerData];

        $this->view('users/managerprofile', $data);
    }

    // ----------------------------
    // Mock methods for user data
    // ----------------------------
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
            'hours_exchanged' => 156,
            'rating' => 4.8,
            'reviews_count' => 24
        ];
    }

    private function getUserSkills($userId) {
        return [
            'teaches' => ['Web Development', 'UI/UX Design', 'JavaScript'],
            'learns' => ['Data Science', 'Machine Learning']
        ];
    }

    private function getUserProjects($userId) {
        return [
            'completed' => [
                ['title' => 'E-commerce Redesign', 'description' => 'Improved conversion by 25%.']
            ],
            'in_progress' => [
                ['title' => 'AI Chatbot', 'description' => 'Building an NLP chatbot.']
            ]
        ];
    }

    private function getUserBadges($userId) {
        return [
            ['icon' => '⭐', 'name' => 'Top Contributor'],
            ['icon' => '🎓', 'name' => 'Mentor']
        ];
    }

    private function getUserFeedback($userId) {
        return [
            ['reviewer_name' => 'Alex Chen', 'date' => '2 weeks ago', 'rating' => 5, 'comment' => 'Excellent mentor!']
        ];
    }

    private function getUserActivity($userId) {
        return [
            ['date' => '2 days ago', 'description' => 'Completed "E-commerce Redesign" project']
        ];
    }

} // End of UsersController class
