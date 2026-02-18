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
        // Fixed to use Database class instead of global $pdo
        $this->db = new Database();
    }
    
    /**
     * Create a new quiz with questions and options
     * Adapted to work with your existing structure
     */
    public function createQuiz($quizData) {
        try {
            // Begin transaction
            $this->db->query("START TRANSACTION");
            $this->db->execute();
            
            // Insert quiz into 'quizzes' table
            $this->db->query("
                INSERT INTO quizzes 
                (title, description, difficulty_level, duration, category, status, total_questions, created_at) 
                VALUES (:title, :description, :difficulty, :duration, :category, :status, :total, NOW())
            ");
            
            $this->db->bind(':title', $quizData['title']);
            $this->db->bind(':description', $quizData['description']);
            $this->db->bind(':difficulty', $quizData['difficulty']);
            $this->db->bind(':duration', $quizData['duration']);
            $this->db->bind(':category', $quizData['category'] ?? 'General');
            $this->db->bind(':status', $quizData['status']);
            $this->db->bind(':total', count($quizData['questions']));
            
            $this->db->execute();
            $quiz_id = $this->db->lastInsertId();
            
            // Insert questions and options
            foreach ($quizData['questions'] as $index => $q) {
                // Insert question
                $this->db->query("
                    INSERT INTO quiz_questions 
                    (quiz_id, question_text, question_order) 
                    VALUES (:quiz_id, :question, :order)
                ");
                
                $this->db->bind(':quiz_id', $quiz_id);
                $this->db->bind(':question', $q['question']);
                $this->db->bind(':order', $index + 1);
                
                $this->db->execute();
                $question_id = $this->db->lastInsertId();
                
                // Insert options (A, B, C, D)
                $options = ['A', 'B', 'C', 'D'];
                foreach ($options as $idx => $option_letter) {
                    $is_correct = ($idx == $q['correctAnswer']) ? 1 : 0;
                    
                    $this->db->query("
                        INSERT INTO quiz_options 
                        (question_id, option_letter, option_text, is_correct) 
                        VALUES (:question_id, :letter, :text, :is_correct)
                    ");
                    
                    $this->db->bind(':question_id', $question_id);
                    $this->db->bind(':letter', $option_letter);
                    $this->db->bind(':text', $q['options'][$idx]);
                    $this->db->bind(':is_correct', $is_correct);
                    
                    $this->db->execute();
                }
            }
            
            // Commit transaction
            $this->db->query("COMMIT");
            $this->db->execute();
            
            return $quiz_id;
            
        } catch (Exception $e) {
            // Rollback on error
            $this->db->query("ROLLBACK");
            $this->db->execute();
            error_log("Quiz creation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all active quizzes
     */
    public function getAllActiveQuizzes() {
        $this->db->query("
            SELECT 
                q.*,
                COUNT(DISTINCT ua.user_id) as participant_count
            FROM quizzes q
            LEFT JOIN user_quiz_attempts ua ON q.id = ua.quiz_id AND ua.status = 'completed'
            WHERE q.status = 'active'
            GROUP BY q.id
            ORDER BY q.created_at DESC
        ");
        
        return $this->db->resultSet();
    }
    
    /**
     * Get quizzes with user status
     */
    public function getQuizzesForUser($user_id) {
        $this->db->query("
            SELECT 
                q.*,
                q.id as quiz_id,
                CASE 
                    WHEN sq.id IS NOT NULL THEN 'saved'
                    WHEN ua.status = 'completed' THEN 'completed'
                    ELSE 'not_started'
                END as user_status,
                ua.score as last_score
            FROM quizzes q
            LEFT JOIN user_saved_quizzes sq ON q.id = sq.quiz_id AND sq.user_id = :user_id
            LEFT JOIN (
                SELECT quiz_id, user_id, score, status
                FROM user_quiz_attempts
                WHERE user_id = :user_id
                ORDER BY completed_at DESC
            ) ua ON q.id = ua.quiz_id
            WHERE q.status = 'active'
            GROUP BY q.id
            ORDER BY q.created_at DESC
        ");
        
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }
    
    /**
     * Get all quizzes for a manager
     * Note: You may need to add manager_id column to quizzes table
     */
    public function getQuizzesByManager($manager_id) {
        $this->db->query("
            SELECT 
                q.*,
                COUNT(DISTINCT ua.user_id) as participant_count,
                COALESCE(AVG(CASE WHEN ua.status = 'completed' THEN ua.score END), 0) as avg_score
            FROM quizzes q
            LEFT JOIN user_quiz_attempts ua ON q.id = ua.quiz_id
            GROUP BY q.id
            ORDER BY q.created_at DESC
        ");
        
        // If you have manager_id, use this instead:
        // WHERE q.manager_id = :manager_id
        // $this->db->bind(':manager_id', $manager_id);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get quiz by ID
     */
    public function getQuizById($quiz_id) {
        $this->db->query("SELECT *, id as quiz_id FROM quizzes WHERE id = :id");
        $this->db->bind(':id', $quiz_id);
        $result = $this->db->single();
        
        // Convert object to array for compatibility
        return $result ? (array)$result : null;
    }
    
    /**
     * Get questions with options for a quiz
     */
    public function getQuizQuestions($quiz_id) {
        $this->db->query("
            SELECT 
                qq.id as question_id,
                qq.question_text,
                qq.question_order,
                GROUP_CONCAT(
                    CONCAT(qo.option_letter, ':', qo.option_text, ':', qo.is_correct)
                    ORDER BY qo.option_letter
                    SEPARATOR '|'
                ) as options_data
            FROM quiz_questions qq
            LEFT JOIN quiz_options qo ON qq.id = qo.question_id
            WHERE qq.quiz_id = :id
            GROUP BY qq.id, qq.question_text, qq.question_order
            ORDER BY qq.question_order ASC
        ");
        
        $this->db->bind(':id', $quiz_id);
        $questions = $this->db->resultSet();
        
        // Parse options data and convert to array
        $parsedQuestions = [];
        foreach ($questions as $q) {
            $questionArray = (array)$q;
            $questionArray['options'] = [];
            $questionArray['correct_answer'] = null;
            
            if (isset($questionArray['options_data']) && $questionArray['options_data']) {
                $options = explode('|', $questionArray['options_data']);
                foreach ($options as $idx => $opt) {
                    $parts = explode(':', $opt, 3);
                    if (count($parts) === 3) {
                        list($letter, $text, $is_correct) = $parts;
                        
                        // Store options by letter (A, B, C, D)
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
    
    /**
     * Update quiz status
     */
    public function updateQuizStatus($quiz_id, $status) {
        $this->db->query("
            UPDATE quizzes SET status = :status WHERE id = :id
        ");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $quiz_id);
        return $this->db->execute();
    }
    
    /**
     * Delete quiz
     */
    public function deleteQuiz($quiz_id) {
        $this->db->query("DELETE FROM quizzes WHERE id = :id");
        $this->db->bind(':id', $quiz_id);
        return $this->db->execute();
    }
    
    /**
     * Save quiz for user
     */
    public function saveQuizForUser($user_id, $quiz_id) {
        $this->db->query("
            INSERT IGNORE INTO user_saved_quizzes (user_id, quiz_id, saved_at) 
            VALUES (:user_id, :quiz_id, NOW())
        ");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':quiz_id', $quiz_id);
        return $this->db->execute();
    }
    
    /**
     * Unsave quiz for user
     */
    public function unsaveQuizForUser($user_id, $quiz_id) {
        $this->db->query("
            DELETE FROM user_saved_quizzes 
            WHERE user_id = :user_id AND quiz_id = :quiz_id
        ");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':quiz_id', $quiz_id);
        return $this->db->execute();
    }
    
    /**
     * Start quiz attempt
     */
    public function startAttempt($user_id, $quiz_id, $total_questions) {
        $this->db->query("
            INSERT INTO user_quiz_attempts 
            (user_id, quiz_id, total_questions, score, correct_answers, status, started_at) 
            VALUES (:user_id, :quiz_id, :total, 0, 0, 'in_progress', NOW())
        ");
        
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':quiz_id', $quiz_id);
        $this->db->bind(':total', $total_questions);
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Complete quiz attempt
     */
    public function completeAttempt($attempt_id, $correct_answers, $total_questions, $time_taken) {
        $score = ($correct_answers / $total_questions) * 100;
        
        $this->db->query("
            UPDATE user_quiz_attempts 
            SET score = :score, 
                correct_answers = :correct,
                time_taken = :time,
                status = 'completed',
                completed_at = NOW()
            WHERE id = :id
        ");
        
        $this->db->bind(':score', $score);
        $this->db->bind(':correct', $correct_answers);
        $this->db->bind(':time', $time_taken);
        $this->db->bind(':id', $attempt_id);
        
        return $this->db->execute();
    }
    
    /**
     * Save individual answer
     */
    public function saveAnswer($attempt_id, $question_id, $selected_option_id, $is_correct) {
        $this->db->query("
            INSERT INTO user_quiz_answers 
            (attempt_id, question_id, selected_option_id, is_correct) 
            VALUES (:attempt_id, :question_id, :option_id, :is_correct)
        ");
        
        $this->db->bind(':attempt_id', $attempt_id);
        $this->db->bind(':question_id', $question_id);
        $this->db->bind(':option_id', $selected_option_id);
        $this->db->bind(':is_correct', $is_correct);
        
        return $this->db->execute();
    }
}