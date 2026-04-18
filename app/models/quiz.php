<?php
/**
 * Quiz Model - Works with YOUR existing database structure
 * Place in: app/models/Quiz.php
 *
 * YOUR EXISTING TABLES:
 * - quizzes
 * - quiz_questions
 * - quiz_options
 * - user_quiz_attempts
 * - user_quiz_answers
 * - user_saved_quizzes
 */
class Quiz {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    private function computeRewardAmount($difficultyLevel) {
        $key = strtolower(trim((string)$difficultyLevel));
        if ($key === 'beginner')     return 10;
        if ($key === 'intermediate') return 20;
        if ($key === 'expert')       return 30;
        return 0;
    }

    // -------------------------------------------------------------------------
    // CREATE QUIZ
    // -------------------------------------------------------------------------

    /**
     * Create a new quiz (simple insert, returns new quiz ID)
     * Used by QuizmanagerController::save()
     */
   public function createQuiz($quizData) {
    $this->db->query("
        INSERT INTO quizzes
            (title, description, difficulty_level, duration, category,
             status, total_questions, badge_id, manager_id, created_at)
        VALUES
            (:title, :description, :difficulty, :duration, :category,
             :status, 0, :badge_id, :manager_id, NOW())
    ");

    $this->db->bind(':title',       $quizData['title']);
    $this->db->bind(':description', $quizData['description']);
    $this->db->bind(':difficulty',  $quizData['difficulty_level']);
    $this->db->bind(':duration',    $quizData['duration']);
    $this->db->bind(':category',    isset($quizData['category']) ? $quizData['category'] : 'General');
    $this->db->bind(':status',      isset($quizData['status']) ? $quizData['status'] : 'draft');
    $this->db->bind(':badge_id',    isset($quizData['badge_id']) ? $quizData['badge_id'] : null);
    $this->db->bind(':manager_id',  $quizData['created_by']);

    if ($this->db->execute()) {
        return $this->db->lastInsertId();
    }
    return false;
}

    /**
     * Add a single question to a quiz
     * Used by QuizmanagerController::save()
     */
    public function addQuestion($quiz_id, $questionData) {
        // Insert the question row
        $this->db->query("
            INSERT INTO quiz_questions (quiz_id, question_text, question_order)
            VALUES (:quiz_id, :text, (
                SELECT COALESCE(MAX(q2.question_order), 0) + 1
                FROM quiz_questions q2
                WHERE q2.quiz_id = :quiz_id2
            ))
        ");
        $this->db->bind(':quiz_id',  $quiz_id);
        $this->db->bind(':quiz_id2', $quiz_id);
        $this->db->bind(':text',     $questionData['question_text']);

        if (!$this->db->execute()) {
            return false;
        }

        $question_id = $this->db->lastInsertId();

        // Insert the four options (A, B, C, D)
        $letters = ['A', 'B', 'C', 'D'];
        $fields  = ['option_a', 'option_b', 'option_c', 'option_d'];
        $correctAnswerIndex = intval(isset($questionData['correct_answer']) ? $questionData['correct_answer'] : 0);

        foreach ($letters as $idx => $letter) {
            $is_correct = ($idx === $correctAnswerIndex) ? 1 : 0;
            $option_text = isset($questionData[$fields[$idx]]) ? $questionData[$fields[$idx]] : '';

            $this->db->query("
                INSERT INTO quiz_options (question_id, option_letter, option_text, is_correct)
                VALUES (:question_id, :letter, :text, :is_correct)
            ");
            $this->db->bind(':question_id', $question_id);
            $this->db->bind(':letter',      $letter);
            $this->db->bind(':text',        $option_text);
            $this->db->bind(':is_correct',  $is_correct);

            if (!$this->db->execute()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Update total_questions count on a quiz row
     * Used by QuizmanagerController::save()
     */
    public function updateQuestionCount($quiz_id, $count) {
        $this->db->query("UPDATE quizzes SET total_questions = :count WHERE id = :id");
        $this->db->bind(':count', $count);
        $this->db->bind(':id',    $quiz_id);
        return $this->db->execute();
    }

    // -------------------------------------------------------------------------
    // MANAGER QUERIES
    // -------------------------------------------------------------------------

    /**
     * Get all quizzes created by a specific manager.
     * Alias used by QuizmanagerController::index()
     */
    public function getAllQuizzesForManager() {
        return $this->getQuizzesByManager($_SESSION['user_id']);
    }

    /**
     * Get all quizzes created by a specific manager (with stats).
     */
    public function getQuizzesByManager($manager_id) {
        $this->db->query("
            SELECT
                q.*,
                COUNT(DISTINCT ua.user_id)                                          AS participant_count,
                COALESCE(AVG(CASE WHEN ua.status = 'completed' THEN ua.score END), 0) AS avg_score
            FROM quizzes q
            LEFT JOIN user_quiz_attempts ua ON q.id = ua.quiz_id
            WHERE q.manager_id = :manager_id
            GROUP BY q.id
            ORDER BY q.created_at DESC
        ");
        $this->db->bind(':manager_id', $manager_id);
        return $this->db->resultSet() ?: array();
    }

    // -------------------------------------------------------------------------
    // USER-FACING QUERIES
    // -------------------------------------------------------------------------

    /**
     * Get all active quizzes (public listing)
     */
    public function getAllActiveQuizzes() {
        $this->db->query("
            SELECT
                q.*,
                COUNT(DISTINCT ua.user_id) AS participant_count
            FROM quizzes q
            LEFT JOIN user_quiz_attempts ua ON q.id = ua.quiz_id AND ua.status = 'completed'
            WHERE q.status = 'active'
            GROUP BY q.id
            ORDER BY q.created_at DESC
        ");

        $rows = $this->db->resultSet();
        if (!$rows) return array();

        foreach ($rows as $row) {
            if (!is_object($row)) continue;
            if (!isset($row->reward_amount) || (int)$row->reward_amount <= 0) {
                $row->reward_amount = $this->computeRewardAmount(isset($row->difficulty_level) ? $row->difficulty_level : '');
            }
        }

        return $rows;
    }

    /**
     * Get quizzes with per-user status (saved / completed / not_started)
     */
    public function getQuizzesForUser($user_id) {
        $this->db->query("
            SELECT
                q.*,
                q.id AS quiz_id,
                CASE
                    WHEN sq.id IS NOT NULL      THEN 'saved'
                    WHEN ua.status = 'completed' THEN 'completed'
                    ELSE 'not_started'
                END AS user_status,
                ua.score AS last_score
            FROM quizzes q
            LEFT JOIN user_saved_quizzes sq
                ON q.id = sq.quiz_id AND sq.user_id = :user_id
            LEFT JOIN user_quiz_attempts ua
                ON ua.id = (
                    SELECT uqa.id
                    FROM user_quiz_attempts uqa
                    WHERE uqa.quiz_id = q.id
                      AND uqa.user_id = :user_id
                    ORDER BY uqa.completed_at DESC
                    LIMIT 1
                )
            WHERE q.status = 'active'
            ORDER BY q.created_at DESC
        ");

        $this->db->bind(':user_id', $user_id);
        $rows = $this->db->resultSet();
        if (!$rows) return array();

        foreach ($rows as $row) {
            if (!is_object($row)) continue;
            if (!isset($row->reward_amount) || (int)$row->reward_amount <= 0) {
                $row->reward_amount = $this->computeRewardAmount(isset($row->difficulty_level) ? $row->difficulty_level : '');
            }
        }

        return $rows;
    }

    // -------------------------------------------------------------------------
    // SINGLE QUIZ
    // -------------------------------------------------------------------------

    /**
     * Get quiz by ID
     */
    public function getQuizById($quiz_id) {
        $this->db->query("SELECT *, id AS quiz_id FROM quizzes WHERE id = :id");
        $this->db->bind(':id', $quiz_id);
        $result = $this->db->single();

        if (!$result) return null;

        $quizArray = (array)$result;
        if (!isset($quizArray['reward_amount']) || (int)$quizArray['reward_amount'] <= 0) {
            $quizArray['reward_amount'] = $this->computeRewardAmount(isset($quizArray['difficulty_level']) ? $quizArray['difficulty_level'] : '');
        }
        return $quizArray;
    }

    /**
     * Get questions with options for a quiz
     */
    public function getQuizQuestions($quiz_id) {
        $this->db->query("
            SELECT
                qq.id            AS question_id,
                qq.question_text,
                qq.question_order,
                GROUP_CONCAT(
                    CONCAT(qo.option_letter, ':', qo.option_text, ':', qo.is_correct)
                    ORDER BY qo.option_letter
                    SEPARATOR '|'
                ) AS options_data
            FROM quiz_questions qq
            LEFT JOIN quiz_options qo ON qq.id = qo.question_id
            WHERE qq.quiz_id = :id
            GROUP BY qq.id, qq.question_text, qq.question_order
            ORDER BY qq.question_order ASC
        ");

        $this->db->bind(':id', $quiz_id);
        $questions = $this->db->resultSet();

        $parsedQuestions = array();
        foreach ($questions as $q) {
            $questionArray = (array)$q;
            $questionArray['options']        = array();
            $questionArray['correct_answer'] = null;

            if (!empty($questionArray['options_data'])) {
                $options = explode('|', $questionArray['options_data']);
                foreach ($options as $idx => $opt) {
                    $parts = explode(':', $opt, 3);
                    if (count($parts) === 3) {
                        [$letter, $text, $is_correct] = $parts;
                        $questionArray['option_' . strtolower($letter)] = $text;
                        if ($is_correct == 1) {
                            $questionArray['correct_answer'] = $idx;
                        }
                    }
                }
            }
            unset($questionArray['options_data']);
            $parsedQuestions[] = $questionArray;
        }

        return $parsedQuestions;
    }

    // -------------------------------------------------------------------------
    // STATUS / DELETE
    // -------------------------------------------------------------------------

    /**
     * Update quiz status (active / paused / draft)
     */
    public function updateQuizStatus($quiz_id, $status) {
        $this->db->query("UPDATE quizzes SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id',     $quiz_id);
        return $this->db->execute();
    }

    /**
     * Delete quiz (cascade must be set on quiz_questions and quiz_options FK)
     */
    public function deleteQuiz($quiz_id) {
        $this->db->query("DELETE FROM quizzes WHERE id = :id");
        $this->db->bind(':id', $quiz_id);
        return $this->db->execute();
    }

    // -------------------------------------------------------------------------
    // SAVE / UNSAVE
    // -------------------------------------------------------------------------

    public function saveQuizForUser($user_id, $quiz_id) {
        $this->db->query("
            INSERT IGNORE INTO user_saved_quizzes (user_id, quiz_id, saved_at)
            VALUES (:user_id, :quiz_id, NOW())
        ");
        $this->db->bind(':user_id',  $user_id);
        $this->db->bind(':quiz_id',  $quiz_id);
        return $this->db->execute();
    }

    public function unsaveQuizForUser($user_id, $quiz_id) {
        $this->db->query("
            DELETE FROM user_saved_quizzes
            WHERE user_id = :user_id AND quiz_id = :quiz_id
        ");
        $this->db->bind(':user_id',  $user_id);
        $this->db->bind(':quiz_id',  $quiz_id);
        return $this->db->execute();
    }

    // -------------------------------------------------------------------------
    // ATTEMPTS
    // -------------------------------------------------------------------------

    /**
     * Start a new quiz attempt
     */
    public function startAttempt($user_id, $quiz_id, $total_questions) {
        $this->db->query("
            INSERT INTO user_quiz_attempts
                (user_id, quiz_id, total_questions, score, correct_answers, status, started_at)
            VALUES
                (:user_id, :quiz_id, :total, 0, 0, 'in_progress', NOW())
        ");
        $this->db->bind(':user_id',  $user_id);
        $this->db->bind(':quiz_id',  $quiz_id);
        $this->db->bind(':total',    $total_questions);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Complete a quiz attempt
     */
    public function completeAttempt($attempt_id, $correct_answers, $total_questions, $time_taken) {
        $score  = ($correct_answers / $total_questions) * 100;
        $passed = ($score >= 70) ? 1 : 0;

        $this->db->query("
            UPDATE user_quiz_attempts
            SET score           = :score,
                correct_answers = :correct,
                time_taken      = :time,
                status          = 'completed',
                passed          = :passed,
                completed_at    = NOW()
            WHERE id = :id
        ");
        $this->db->bind(':score',   $score);
        $this->db->bind(':correct', $correct_answers);
        $this->db->bind(':time',    $time_taken);
        $this->db->bind(':passed',  $passed);
        $this->db->bind(':id',      $attempt_id);

        return $this->db->execute();
    }

    /**
     * Save an individual answer within an attempt
     */
    public function saveAnswer($attempt_id, $question_id, $selected_option_id, $is_correct) {
        $this->db->query("
            INSERT INTO user_quiz_answers
                (attempt_id, question_id, selected_option_id, is_correct)
            VALUES
                (:attempt_id, :question_id, :option_id, :is_correct)
        ");
        $this->db->bind(':attempt_id',  $attempt_id);
        $this->db->bind(':question_id', $question_id);
        $this->db->bind(':option_id',   $selected_option_id);
        $this->db->bind(':is_correct',  $is_correct);

        return $this->db->execute();
    }

    /**
     * Get available badges for quiz creation
     */
    public function getAvailableBadges() {
        $this->db->query("SELECT id, name, icon FROM badges ORDER BY name ASC");
        $result = $this->db->resultSet();
        error_log("DEBUG MODEL: Query result: " . (is_array($result) ? count($result) : 'not array'));
        if (is_array($result) && count($result) > 0) {
            error_log("DEBUG MODEL: First badge: " . $result[0]->name);
        }
        return $result ?: array();
    }
}