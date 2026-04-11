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

} // End of DashboardController