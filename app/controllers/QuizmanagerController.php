<?php
/**
 * QuizmanagerController - UPDATED for Database Integration
 * Place in: app/controllers/QuizmanagerController.php
 */

class QuizmanagerController extends Controller {
    private $quizModel;
    
    public function __construct() {
        // Load Quiz model
        require_once '../app/models/Quiz.php';
        $this->quizModel = new Quiz();
        
        // Authentication check (uncomment when ready)
        /*
        if(!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/users/login');
            exit;
        }
        */
    }
    
    /**
     * Dashboard - shows all manager's quizzes
     */
    public function index() {
        // Get manager ID from session (use 1 for testing)
        $manager_id = $_SESSION['user_id'] ?? 1;
        
        // Fetch quizzes from database
        $quizzes = $this->quizModel->getQuizzesByManager($manager_id);
        
        $data = [
            'title' => 'Quiz Manager Dashboard',
            'quizzes' => $quizzes
        ];
        
        $this->view('quizmanager/dashboard', $data);
    }
    
    /**
     * Show create quiz form
     */
    public function create() {
        $data = ['title' => 'Create New Quiz'];
        $this->view('quizmanager/quiz_create', $data);
    }
    
    /**
     * Save quiz to database (AJAX endpoint)
     */
    public function save() {
        header('Content-Type: application/json');
        
        if($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        
        // Get JSON data
        $json = file_get_contents('php://input');
        $quizData = json_decode($json, true);
        
        // Validate
        if(empty($quizData['title']) || empty($quizData['questions'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        // Prepare data for database
        $data = [
            'manager_id' => $_SESSION['user_id'] ?? 1, // Use session when ready
            'title' => trim($quizData['title']),
            'description' => trim($quizData['description'] ?? ''),
            'difficulty' => $quizData['badge'], // 'Beginner', 'Intermediate', 'Expert'
            'duration' => intval($quizData['duration']),
            'category' => $quizData['category'] ?? 'General',
            'status' => $quizData['status'], // 'draft' or 'active'
            'questions' => $quizData['questions']
        ];
        
        // Save to database
        $quiz_id = $this->quizModel->createQuiz($data);
        
        if($quiz_id) {
            echo json_encode([
                'success' => true,
                'message' => $data['status'] === 'active' ? 'Quiz published!' : 'Quiz saved as draft!',
                'quiz_id' => $quiz_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save quiz']);
        }
        
        exit;
    }
    
    /**
     * Get quizzes (AJAX endpoint for dashboard)
     */
    public function getQuizzes() {
        header('Content-Type: application/json');
        
        $manager_id = $_SESSION['user_id'] ?? 1;
        $quizzes = $this->quizModel->getQuizzesByManager($manager_id);
        
        echo json_encode(['success' => true, 'quizzes' => $quizzes]);
        exit;
    }
    
    /**
     * Update quiz status (activate/pause/draft)
     */
    public function updateStatus() {
        header('Content-Type: application/json');
        
        if($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['success' => false]);
            exit;
        }
        
        $quiz_id = $_POST['quiz_id'] ?? null;
        $status = $_POST['status'] ?? null;
        
        if(!$quiz_id || !in_array($status, ['draft', 'active', 'paused'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }
        
        $result = $this->quizModel->updateQuizStatus($quiz_id, $status);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Status updated!' : 'Update failed'
        ]);
        exit;
    }
    
    /**
     * Delete quiz
     */
    public function delete() {
        header('Content-Type: application/json');
        
        if($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['success' => false]);
            exit;
        }
        
        $quiz_id = $_POST['quiz_id'] ?? null;
        
        if(!$quiz_id) {
            echo json_encode(['success' => false, 'message' => 'Quiz ID required']);
            exit;
        }
        
        $result = $this->quizModel->deleteQuiz($quiz_id);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Quiz deleted!' : 'Delete failed'
        ]);
        exit;
    }
}