<?php
// app/controllers/DashboardController.php

class DashboardController extends Controller {
    
    // Helper method to check authentication (DRY principle)
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/login');
            exit;
        }
        return $_SESSION['user_id'];
    }

    // Main dashboard overview
    public function index() {
        $userId = $this->checkAuth();

        $data = [
            'user' => $this->getUser($userId)
        ];

        $this->view('dashboard/index', $data);
    }

    // Profile page
    public function profile() {
        $userId = $this->checkAuth();

        $data = [
            'user' => $this->getUser($userId)
        ];

        $this->view('dashboard/profile', $data);
    }

    // Notifications page
    public function notifications() {
        $userId = $this->checkAuth();

        $data = [
            'user' => $this->getUser($userId),
            'notifications' => $this->getNotifications($userId)
        ];

        $this->view('dashboard/notifications', $data);
    }

    // Chats page
    public function chats() {
        $userId = $this->checkAuth();

        $data = [
            'user' => $this->getUser($userId),
            'chats' => $this->getChats($userId)
        ];

        $this->view('dashboard/chats', $data);
    }

    // Matches page
    public function matches() {
        $userId = $this->checkAuth();

        $data = [
            'user' => $this->getUser($userId),
            'matches' => $this->getMatches($userId)
        ];

        $this->view('dashboard/matches', $data);
    }

    // Communities page
    public function communities() {
        $userId = $this->checkAuth();

        $data = [
            'user' => $this->getUser($userId),
            'communities' => $this->getCommunities($userId)
        ];

        $this->view('dashboard/communities', $data);
    }

    // Quiz page
    public function quiz() {
        $userId = $this->checkAuth();

        $data = [
            'user' => $this->getUser($userId),
            'quizzes' => $this->getQuizzes()
        ];

        $this->view('dashboard/quiz', $data);
    }

    // Projects page
    public function projects() {
        $userId = $this->checkAuth();

        $data = [
            'user' => $this->getUser($userId),
            'projects' => $this->getProjects($userId)
        ];

        $this->view('dashboard/projects', $data);
    }

    // Wallet page
    public function wallet() {
        $userId = $this->checkAuth();

        $data = [
            'user' => $this->getUser($userId),
            'balance' => $this->getBalance($userId),
            'transactions' => $this->getTransactions($userId)
        ];

        $this->view('dashboard/wallet', $data);
    }

    // ========================================
    // SIMPLE MOCK DATA (Just basics)
    // ========================================

    private function getUser($userId) {
        return [
            'id' => $userId,
            'name' => 'Sarah Johnson',
            'email' => 'sarah@example.com',
            'avatar' => 'SJ'
        ];
    }

    private function getNotifications($userId) {
        return [
            ['message' => 'You have a new match!', 'time' => '1 hour ago'],
            ['message' => 'New message from Alex', 'time' => '2 hours ago']
        ];
    }

    private function getChats($userId) {
        return [
            ['partner' => 'Alex Chen', 'last_message' => 'Hey there!', 'time' => '10 min ago'],
            ['partner' => 'Maria Garcia', 'last_message' => 'See you tomorrow', 'time' => '1 hour ago']
        ];
    }

    private function getMatches($userId) {
        return [
            ['name' => 'Alex Chen', 'skill' => 'JavaScript', 'rating' => 4.9],
            ['name' => 'Emma Wilson', 'skill' => 'React', 'rating' => 4.7]
        ];
    }

    private function getCommunities($userId) {
        return [
            ['name' => 'Web Developers', 'members' => 1250],
            ['name' => 'UI/UX Designers', 'members' => 890]
        ];
    }

    private function getQuizzes() {
        return [
            ['title' => 'JavaScript Basics', 'questions' => 20],
            ['title' => 'React Fundamentals', 'questions' => 15]
        ];
    }

    private function getProjects($userId) {
        return [
            ['title' => 'E-commerce Platform', 'status' => 'In Progress'],
            ['title' => 'Portfolio Website', 'status' => 'Completed']
        ];
    }

    private function getBalance($userId) {
        return 1250.00;
    }

    private function getTransactions($userId) {
        return [
            ['type' => 'Credit', 'amount' => 50, 'description' => 'Session payment', 'date' => '2024-03-15'],
            ['type' => 'Debit', 'amount' => 30, 'description' => 'Withdrawal', 'date' => '2024-03-14']
        ];
    }

} // End of DashboardController