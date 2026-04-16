<?php
class QuizmanagerController extends Controller {

    private $quizModel;

    public function __construct() {
        $this->quizModel = $this->model('Quiz');

        // Re-enable auth
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }
        if ($_SESSION['role'] !== 'quiz_manager') {
            header('Location: ' . URLROOT . '/pages/index');
            exit;
        }
    }

    public function index() {
        // Load real quizzes from DB for the dashboard table
        $quizzes = $this->quizModel->getAllQuizzesForManager();

        $data = [
            'title'       => 'Quiz Manager Dashboard',
            'active_page' => 'dashboard',
            'quizzes'     => $quizzes
        ];
        $this->view('quizmanager/dashboard', $data);
    }

    public function create() {
        $data = [
            'title'       => 'Create New Quiz',
            'quiz_title'  => '',
            'badge'       => '',
            'description' => '',
        ];
        $this->view('quizmanager/quiz_create', $data);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/quizmanager');
            exit;
        }

        header('Content-Type: application/json');

        $json     = file_get_contents('php://input');
        $quizData = json_decode($json, true);

        // Validate
        $errors = [];
        if (empty($quizData['title']))     $errors[] = 'Quiz title is required';
        if (empty($quizData['badge']))     $errors[] = 'Difficulty level is required';
        if (empty($quizData['questions']) || count($quizData['questions']) < 1)
                                           $errors[] = 'At least one question is required';

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // ✅ Save quiz to DB
        $quizId = $this->quizModel->createQuiz([
            'title'            => trim($quizData['title']),
            'description'      => trim($quizData['description'] ?? ''),
            'difficulty_level' => $quizData['badge'],
            'duration'         => intval($quizData['duration'] ?? 30),
            'status'           => $quizData['status'] ?? 'draft',
            'category'         => $quizData['category'] ?? 'General',
            'reward_amount'    => intval($quizData['reward'] ?? 0),
            'created_by'       => $_SESSION['user_id']
        ]);

        if (!$quizId) {
            echo json_encode(['success' => false, 'errors' => ['Failed to save quiz to database']]);
            exit;
        }

        // ✅ Save each question
        $questionsSaved = 0;
        foreach ($quizData['questions'] as $q) {
            $saved = $this->quizModel->addQuestion($quizId, [
                'question_text'  => trim($q['question'] ?? ''),
                'option_a'       => trim($q['options'][0] ?? ''),
                'option_b'       => trim($q['options'][1] ?? ''),
                'option_c'       => trim($q['options'][2] ?? ''),
                'option_d'       => trim($q['options'][3] ?? ''),
                'correct_answer' => intval($q['correct'] ?? 0)
            ]);
            if ($saved) $questionsSaved++;
        }

        // Update total_questions count on the quiz row
        $this->quizModel->updateQuestionCount($quizId, $questionsSaved);

        echo json_encode([
            'success'  => true,
            'message'  => 'Quiz saved successfully!',
            'quiz_id'  => $quizId,
            'status'   => $quizData['status'],
            'questions'=> $questionsSaved
        ]);
        exit;
    }

    public function preview() {
        $data = ['title' => 'Quiz Preview'];
        $this->view('quizmanager/quiz_preview', $data);
    }

    public function getQuizzes() {
        header('Content-Type: application/json');
        
        // Load quizzes from DB for the dashboard
        $quizzes = $this->quizModel->getAllQuizzesForManager();
        
        echo json_encode([
            'success' => true,
            'quizzes' => $quizzes ?: []
        ]);
        exit;
    }
}