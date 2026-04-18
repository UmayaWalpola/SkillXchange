<?php
class QuizmanagerController extends Controller {

    private $quizModel;

    public function __construct() {
        $this->quizModel = $this->model('Quiz');

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
        $quizzes = $this->quizModel->getAllQuizzesForManager();

        $data = array(
            'title'       => 'Quiz Manager Dashboard',
            'active_page' => 'dashboard',
            'quizzes'     => $quizzes
        );
        $this->view('quizmanager/dashboard', $data);
    }

    public function create() {
        // Load available badges for the dropdown
        $badgesData = $this->quizModel->getAvailableBadges();

        // Debug: log how many badges came back so you can confirm the query works
        error_log('[QuizManager] Available badges count: ' . count($badgesData));

        $data = array(
            'title'            => 'Create New Quiz',
            'quiz_title'       => '',
            'badge'            => '',
            'description'      => '',
            'available_badges' => $badgesData
        );
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
        $errors = array();
        if (empty($quizData['title']))    $errors[] = 'Quiz title is required';
        if (empty($quizData['badge']))    $errors[] = 'Difficulty level is required';
        if (empty($quizData['questions']) || count($quizData['questions']) < 1)
                                          $errors[] = 'At least one question is required';

        if (!empty($errors)) {
            echo json_encode(array('success' => false, 'errors' => $errors));
            exit;
        }

        $buckReward = intval(isset($quizData['buckReward']) ? $quizData['buckReward'] : 0);

        $quizId = $this->quizModel->createQuiz(array(
            'title'            => trim($quizData['title']),
            'description'      => trim(isset($quizData['description']) ? $quizData['description'] : ''),
            'difficulty_level' => $quizData['badge'],
            'duration'         => intval(isset($quizData['duration']) ? $quizData['duration'] : 30),
            'status'           => isset($quizData['status']) ? $quizData['status'] : 'draft',
            'category'         => isset($quizData['category']) ? $quizData['category'] : 'General',
            'reward_amount'    => $buckReward,
            'badge_id'         => !empty($quizData['badgeToAward']) ? intval($quizData['badgeToAward']) : null,
            'created_by'       => $_SESSION['user_id']
        ));

        if (!$quizId) {
            echo json_encode(array('success' => false, 'errors' => array('Failed to save quiz to database')));
            exit;
        }

        // Save each question
        $questionsSaved = 0;
        foreach ($quizData['questions'] as $q) {
            $saved = $this->quizModel->addQuestion($quizId, array(
                'question_text'  => trim(isset($q['question']) ? $q['question'] : ''),
                'option_a'       => trim(isset($q['options'][0]) ? $q['options'][0] : ''),
                'option_b'       => trim(isset($q['options'][1]) ? $q['options'][1] : ''),
                'option_c'       => trim(isset($q['options'][2]) ? $q['options'][2] : ''),
                'option_d'       => trim(isset($q['options'][3]) ? $q['options'][3] : ''),
                'correct_answer' => intval(isset($q['correct']) ? $q['correct'] : 0)
            ));
            if ($saved) $questionsSaved++;
        }

        $this->quizModel->updateQuestionCount($quizId, $questionsSaved);

        echo json_encode(array(
            'success'   => true,
            'message'   => 'Quiz saved successfully!',
            'quiz_id'   => $quizId,
            'status'    => $quizData['status'],
            'questions' => $questionsSaved
        ));
        exit;
    }

    public function preview($quizId = null) {
        if (!$quizId) {
            header('Location: ' . URLROOT . '/quizmanager');
            exit;
        }

        $quiz = $this->quizModel->getQuizById($quizId);

        // FIX (Bug 2): cast both sides to int to avoid type-mismatch false failures
        if (!$quiz || (int)$quiz['manager_id'] !== (int)$_SESSION['user_id']) {
            header('Location: ' . URLROOT . '/quizmanager');
            exit;
        }

        $questions = $this->quizModel->getQuizQuestions($quizId);

        $data = array(
            'title'     => 'Preview Quiz',
            'quiz'      => $quiz,
            'questions' => $questions
        );
        $this->view('quizmanager/quiz_preview', $data);
    }

    public function edit($quizId = null) {
        if (!$quizId) {
            header('Location: ' . URLROOT . '/quizmanager');
            exit;
        }

        $quiz = $this->quizModel->getQuizById($quizId);

        // FIX (Bug 2): cast both sides to int
        if (!$quiz || (int)$quiz['manager_id'] !== (int)$_SESSION['user_id']) {
            header('Location: ' . URLROOT . '/quizmanager');
            exit;
        }

        $badgesData = $this->quizModel->getAvailableBadges();
        $questions  = $this->quizModel->getQuizQuestions($quizId);

        $data = array(
            'title'            => 'Edit Quiz',
            'quiz'             => $quiz,
            'questions'        => $questions,
            'available_badges' => $badgesData
        );
        $this->view('quizmanager/quiz_edit', $data);
    }

    public function getQuizzes() {
        header('Content-Type: application/json');

        $quizzes = $this->quizModel->getAllQuizzesForManager();

        echo json_encode(array(
            'success' => true,
            'quizzes' => $quizzes ?: array()
        ));
        exit;
    }

    public function updateStatus($quizId = null, $status = null) {
        if (!$quizId || !$status) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Quiz ID and status required'));
            exit;
        }

        $quiz = $this->quizModel->getQuizById($quizId);

        // FIX (Bug 2): cast both sides to int
        if (!$quiz || (int)$quiz['manager_id'] !== (int)$_SESSION['user_id']) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(array('success' => false, 'message' => 'Unauthorized'));
            exit;
        }

        $updated = $this->quizModel->updateQuizStatus($quizId, $status);

        header('Content-Type: application/json');
        if ($updated) {
            echo json_encode(array('success' => true, 'message' => "Quiz {$status} successfully"));
        } else {
            http_response_code(500);
            echo json_encode(array('success' => false, 'message' => 'Failed to update quiz status'));
        }
        exit;
    }

    public function delete($quizId = null) {
        if (!$quizId) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Quiz ID required'));
            exit;
        }

        $quiz = $this->quizModel->getQuizById($quizId);

        // FIX (Bug 2): cast both sides to int
        if (!$quiz || (int)$quiz['manager_id'] !== (int)$_SESSION['user_id']) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(array('success' => false, 'message' => 'Unauthorized'));
            exit;
        }

        $deleted = $this->quizModel->deleteQuiz($quizId);

        header('Content-Type: application/json');
        if ($deleted) {
            echo json_encode(array('success' => true, 'message' => 'Quiz deleted successfully'));
        } else {
            http_response_code(500);
            echo json_encode(array('success' => false, 'message' => 'Failed to delete quiz'));
        }
        exit;
    }
}