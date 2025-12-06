<?php
// app/controllers/QuizmanagerController.php

class QuizmanagerController extends Controller {
    
    public function __construct() {
        // TEMPORARILY DISABLED - Authentication will be added later
        /*
        // Check if user is logged in
        if(!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/users/login');
            exit;
        }
        
        // Check if user is a manager
        if($_SESSION['user_role'] !== 'manager') {
            header('Location: ' . URLROOT . '/pages/index');
            exit;
        }
        */
    }
    
    // Main dashboard page
    public function index() {
        $data = [
            'title' => 'Quiz Manager Dashboard',
            'active_page' => 'dashboard'
        ];
        
        $this->view('quizmanager/dashboard', $data);
    }
    
    // Show quiz builder form
    public function create() {
        $data = [
            'title' => 'Create New Quiz',
            'quiz_title' => '',
            'badge' => '',
            'description' => '',
            'title_err' => '',
            'badge_err' => '',
            'description_err' => ''
        ];
        
        $this->view('quizmanager/quiz_create', $data);
    }
    
    // Save quiz (draft or publish)
    public function save() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get JSON data from request
            $json = file_get_contents('php://input');
            $quizData = json_decode($json, true);
            
            // Validate basic info
            $errors = [];
            
            if(empty($quizData['title'])) {
                $errors[] = 'Quiz title is required';
            }
            
            if(empty($quizData['badge'])) {
                $errors[] = 'Difficulty level is required';
            }
            
            if(empty($quizData['questions']) || count($quizData['questions']) < 1) {
                $errors[] = 'At least one question is required';
            }
            
            if(!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }
            
            // TODO: Save to database when ready
            // For now, just return success
            echo json_encode([
                'success' => true, 
                'message' => 'Quiz saved successfully!',
                'status' => $quizData['status']
            ]);
            exit;
            
        } else {
            header('Location: ' . URLROOT . '/quizmanager');
            exit;
        }
    }
    
    // Preview quiz
    public function preview() {
        // TODO: Show preview of quiz
        $data = [
            'title' => 'Quiz Preview'
        ];
        
        $this->view('quizmanager/quiz_preview', $data);
    }
}