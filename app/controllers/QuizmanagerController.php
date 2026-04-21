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
        $badgesData = $this->quizModel->getAvailableBadges();

        $data = [
            'title'            => 'Create New Quiz',
            'quiz_title'       => '',
            'badge'            => '',
            'description'      => '',
            'available_skills' => $this->quizModel->getAllSkills(),
            'available_badges' => $badgesData,
            'mode'             => 'create'
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
        if (empty($quizData['category']))  $errors[] = 'Quiz skill is required';
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
            'category'         => trim((string)($quizData['category'] ?? '')),
            'badge_id'         => !empty($quizData['badgeId']) ? intval($quizData['badgeId']) : null,
            'reward_amount'    => max(0, intval($quizData['rewardAmount'] ?? $quizData['buckReward'] ?? $quizData['reward'] ?? 0)),
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
                'option_e'       => trim($q['options'][4] ?? ''),
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
            'status'   => $quizData['status'] ?? 'draft',
            'questions'=> $questionsSaved
        ]);
        exit;
    }

    public function update($quizId = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/quizmanager');
            exit;
        }

        header('Content-Type: application/json');

        if (!$quizId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Quiz ID is required']]);
            exit;
        }

        $quiz = $this->quizModel->getQuizById($quizId);
        if (!$quiz || (int)$quiz['manager_id'] !== (int)$_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Unauthorized']]);
            exit;
        }

        $json = file_get_contents('php://input');
        $quizData = json_decode($json, true);

        $errors = [];
        if (empty($quizData['title'])) $errors[] = 'Quiz title is required';
        if (empty($quizData['category'])) $errors[] = 'Quiz skill is required';
        if (empty($quizData['badge'])) $errors[] = 'Difficulty level is required';
        if (empty($quizData['questions']) || count($quizData['questions']) < 1) {
            $errors[] = 'At least one question is required';
        }

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        $updated = $this->quizModel->updateQuiz($quizId, [
            'title'            => trim($quizData['title']),
            'description'      => trim($quizData['description'] ?? ''),
            'difficulty_level' => $quizData['badge'],
            'duration'         => intval($quizData['duration'] ?? 30),
            'status'           => $quizData['status'] ?? 'draft',
            'category'         => trim((string)($quizData['category'] ?? '')),
            'badge_id'         => !empty($quizData['badgeId']) ? intval($quizData['badgeId']) : null,
            'reward_amount'    => max(0, intval($quizData['rewardAmount'] ?? $quizData['buckReward'] ?? $quizData['reward'] ?? 0))
        ]);

        if (!$updated) {
            http_response_code(500);
            echo json_encode(['success' => false, 'errors' => ['Failed to update quiz']]);
            exit;
        }

        if (!$this->quizModel->replaceQuestions($quizId, $quizData['questions'])) {
            http_response_code(500);
            echo json_encode(['success' => false, 'errors' => ['Failed to update quiz questions']]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Quiz updated successfully!',
            'quiz_id' => $quizId,
            'status' => $quizData['status'] ?? 'draft',
            'questions' => count($quizData['questions'])
        ]);
        exit;
    }

    public function preview($quizId = null) {
        if (!$quizId) {
            header('Location: ' . URLROOT . '/quizmanager');
            exit;
        }

        $quiz = $this->quizModel->getQuizById($quizId);
        if (!$quiz || (int)$quiz['manager_id'] !== (int)$_SESSION['user_id']) {
            header('Location: ' . URLROOT . '/quizmanager');
            exit;
        }

        $questions = $this->quizModel->getQuizQuestions($quizId);

        $data = [
            'title'     => 'Preview Quiz',
            'quiz'      => $quiz,
            'questions' => $questions
        ];
        $this->view('quizmanager/quiz_preview', $data);
    }

    public function edit($quizId = null) {
        if (!$quizId) {
            header('Location: ' . URLROOT . '/quizmanager');
            exit;
        }

        $quiz = $this->quizModel->getQuizById($quizId);
        if (!$quiz || (int)$quiz['manager_id'] !== (int)$_SESSION['user_id']) {
            header('Location: ' . URLROOT . '/quizmanager');
            exit;
        }

        $data = [
            'title'            => 'Edit Quiz',
            'quiz'             => $quiz,
            'questions'        => $this->quizModel->getQuizQuestions($quizId),
            'available_skills' => $this->quizModel->getAllSkills(),
            'available_badges' => $this->quizModel->getAvailableBadges(),
            'mode'             => 'edit'
        ];
        $this->view('quizmanager/quiz_create', $data);
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

    public function updateStatus($quizId = null, $status = null) {
        header('Content-Type: application/json');

        if (!$quizId || !$status) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Quiz ID and status required']);
            exit;
        }

        $allowedStatuses = ['draft', 'active', 'paused'];
        if (!in_array($status, $allowedStatuses, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid quiz status']);
            exit;
        }

        $quiz = $this->quizModel->getQuizById($quizId);
        if (!$quiz || (int)$quiz['manager_id'] !== (int)$_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $updated = $this->quizModel->updateQuizStatus($quizId, $status);
        if ($updated) {
            echo json_encode(['success' => true, 'message' => "Quiz {$status} successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update quiz status']);
        }
        exit;
    }

    public function delete($quizId = null) {
        header('Content-Type: application/json');

        if (!$quizId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Quiz ID required']);
            exit;
        }

        $quiz = $this->quizModel->getQuizById($quizId);
        if (!$quiz || (int)$quiz['manager_id'] !== (int)$_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $deleted = $this->quizModel->deleteQuiz($quizId);
        if ($deleted) {
            echo json_encode(['success' => true, 'message' => 'Quiz deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete quiz']);
        }
        exit;
    }
}
