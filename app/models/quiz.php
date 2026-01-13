<?php

class Quiz {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // =============================================
    // GET QUIZZES
    // =============================================
    
    /**
     * Get all quizzes with user attempt status
     */
    public function getAllQuizzesForUser($userId) {
    $this->db->query("
        SELECT DISTINCT
            q.id,
            q.title,
            q.description,
            q.category,
            q.difficulty,
            q.passing_score,
            q.time_limit,
            q.is_premium,
            q.badge_id,
            b.name as badge_name,
            b.icon as badge_icon,
            CASE 
                WHEN uqa_passed.id IS NOT NULL THEN 'completed'
                WHEN usq.id IS NOT NULL THEN 'saved'
                ELSE 'not_started'
            END as status,
            uqa_latest.score as last_score,
            uqa_latest.completed_at as last_attempt,
            (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
        FROM quizzes q
        LEFT JOIN badges b ON q.badge_id = b.id
        LEFT JOIN (
            SELECT quiz_id, MAX(id) as id
            FROM user_quiz_attempts
            WHERE user_id = :user_id AND passed = 1
            GROUP BY quiz_id
        ) uqa_passed ON q.id = uqa_passed.quiz_id
        LEFT JOIN user_quiz_attempts uqa_latest ON uqa_passed.id = uqa_latest.id
        LEFT JOIN user_saved_quizzes usq ON q.id = usq.quiz_id 
            AND usq.user_id = :user_id
        GROUP BY q.id
        ORDER BY q.category, q.difficulty
    ");
    
    $this->db->bind(':user_id', $userId);
    $results = $this->db->resultSet();
    
    // Remove any remaining duplicates in PHP
    $seenIds = [];
    $quizzes = [];
    
    foreach ($results as $quiz) {
        // Skip if we've already seen this quiz ID
        if (in_array($quiz->id, $seenIds)) {
            continue;
        }
        
        $seenIds[] = $quiz->id;
        
        $quizzes[] = [
            'id' => (int)$quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'category' => $quiz->category,
            'difficulty' => $quiz->difficulty,
            'passingScore' => (int)$quiz->passing_score,
            'timeLimit' => $quiz->time_limit ? (int)$quiz->time_limit : null,
            'isPremium' => (bool)$quiz->is_premium,
            'status' => $quiz->status,
            'questionCount' => (int)$quiz->question_count,
            'lastScore' => $quiz->last_score ? (float)$quiz->last_score : null,
            'badge' => $quiz->badge_id ? [
                'name' => $quiz->badge_name,
                'icon' => $quiz->badge_icon
            ] : null
        ];
    }
    
    return $quizzes;
}
    /**
     * Get single quiz with all questions and options
     */
    public function getQuizById($quizId) {
        // Get quiz details
        $this->db->query("
            SELECT 
                q.*,
                b.name as badge_name,
                b.icon as badge_icon,
                b.description as badge_description,
                (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
            FROM quizzes q
            LEFT JOIN badges b ON q.badge_id = b.id
            WHERE q.id = :quiz_id
        ");
        
        $this->db->bind(':quiz_id', $quizId);
        $quiz = $this->db->single();
        
        if (!$quiz) {
            return null;
        }
        
        // Get questions with options
        $this->db->query("
            SELECT 
                qq.id,
                qq.question_text,
                qq.question_order,
                qq.points
            FROM quiz_questions qq
            WHERE qq.quiz_id = :quiz_id
            ORDER BY qq.question_order
        ");
        
        $this->db->bind(':quiz_id', $quizId);
        $questions = $this->db->resultSet();
        
        $questionsArray = [];
        foreach ($questions as $question) {
            // Get options for this question
            $this->db->query("
                SELECT 
                    id,
                    option_text,
                    is_correct,
                    option_order
                FROM quiz_options
                WHERE question_id = :question_id
                ORDER BY option_order
            ");
            
            $this->db->bind(':question_id', $question->id);
            $options = $this->db->resultSet();
            
            $optionsArray = [];
            $correctAnswer = null;
            
            foreach ($options as $index => $option) {
                $optionsArray[] = $option->option_text;
                if ($option->is_correct) {
                    $correctAnswer = $index;
                }
            }
            
            $questionsArray[] = [
                'id' => (int)$question->id,
                'question' => $question->question_text,
                'options' => $optionsArray,
                'correct' => $correctAnswer,
                'points' => (int)$question->points
            ];
        }
        
        return [
            'id' => (int)$quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'category' => $quiz->category,
            'difficulty' => $quiz->difficulty,
            'passingScore' => (int)$quiz->passing_score,
            'timeLimit' => $quiz->time_limit ? (int)$quiz->time_limit : null,
            'questionCount' => (int)$quiz->question_count,
            'questions' => $questionsArray,
            'badge' => $quiz->badge_name ? [
                'name' => $quiz->badge_name,
                'icon' => $quiz->badge_icon,
                'description' => $quiz->badge_description
            ] : null
        ];
    }
    
    // =============================================
    // QUIZ ATTEMPTS
    // =============================================
    
    /**
     * Save quiz attempt results
     */
    public function saveQuizAttempt($userId, $quizId, $userAnswers, $timeTaken = null) {
    $quiz = $this->getQuizById($quizId);
    
    if (!$quiz) {
        error_log("Quiz not found: ID $quizId");
        return ['success' => false, 'message' => 'Quiz not found'];
    }
    
    // Calculate score
    $correctCount = 0;
    $totalQuestions = count($quiz['questions']);
    
    foreach ($quiz['questions'] as $index => $question) {
        if (isset($userAnswers[$index]) && $userAnswers[$index] === $question['correct']) {
            $correctCount++;
        }
    }
    
    $scorePercentage = ($correctCount / $totalQuestions) * 100;
    $passed = $scorePercentage >= $quiz['passingScore'];
    
    error_log("Quiz attempt - Correct: $correctCount, Total: $totalQuestions, Score: $scorePercentage%, Passed: " . ($passed ? 'Yes' : 'No'));
    
    try {
        // Start transaction
        $this->db->query("START TRANSACTION");
        
        // Insert quiz attempt
        $this->db->query("
            INSERT INTO user_quiz_attempts 
            (user_id, quiz_id, score, correct_answers, total_questions, time_taken, passed)
            VALUES 
            (:user_id, :quiz_id, :score, :correct_answers, :total_questions, :time_taken, :passed)
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':quiz_id', $quizId);
        $this->db->bind(':score', round($scorePercentage, 2));
        $this->db->bind(':correct_answers', $correctCount);
        $this->db->bind(':total_questions', $totalQuestions);
        $this->db->bind(':time_taken', $timeTaken);
        $this->db->bind(':passed', $passed ? 1 : 0);
        
        if (!$this->db->execute()) {
            throw new Exception("Failed to insert quiz attempt");
        }
        
        $attemptId = $this->db->lastInsertId();
        error_log("Quiz attempt saved with ID: $attemptId");
        
        // Award badge if passed and badge exists
        $badgeEarned = null;
        if ($passed && isset($quiz['badge']) && $quiz['badge']) {
            error_log("Attempting to award badge: " . $quiz['badge']['name']);
            
            // Get the badge_id directly from the quiz
            $this->db->query("SELECT badge_id FROM quizzes WHERE id = :quiz_id");
            $this->db->bind(':quiz_id', $quizId);
            $quizRecord = $this->db->single();
            
            if ($quizRecord && $quizRecord->badge_id) {
                $badgeId = $quizRecord->badge_id;
                
                // Check if user already has this badge
                $this->db->query("
                    SELECT id FROM user_badges 
                    WHERE user_id = :user_id AND badge_id = :badge_id
                ");
                $this->db->bind(':user_id', $userId);
                $this->db->bind(':badge_id', $badgeId);
                $existing = $this->db->single();
                
                if (!$existing) {
                    // Award badge
                    $this->db->query("
                        INSERT INTO user_badges (user_id, badge_id, source_type, source_id)
                        VALUES (:user_id, :badge_id, 'quiz', :source_id)
                    ");
                    $this->db->bind(':user_id', $userId);
                    $this->db->bind(':badge_id', $badgeId);
                    $this->db->bind(':source_id', $quizId);
                    
                    if ($this->db->execute()) {
                        $badgeEarned = $quiz['badge'];
                        error_log("Badge awarded successfully!");
                    }
                } else {
                    error_log("User already has this badge");
                }
            }
        }
        
        // Remove from saved quizzes if it was saved
        $this->db->query("
            DELETE FROM user_saved_quizzes 
            WHERE user_id = :user_id AND quiz_id = :quiz_id
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':quiz_id', $quizId);
        $this->db->execute();
        
        // Commit transaction
        $this->db->query("COMMIT");
        
        error_log("Quiz submission completed successfully!");
        
        return [
            'success' => true,
            'passed' => $passed,
            'score' => round($scorePercentage, 2),
            'correctAnswers' => $correctCount,
            'totalQuestions' => $totalQuestions,
            'badgeEarned' => $badgeEarned
        ];
        
    } catch (Exception $e) {
        $this->db->query("ROLLBACK");
        error_log("Quiz Attempt Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return [
            'success' => false, 
            'message' => 'Failed to save quiz attempt: ' . $e->getMessage()
        ];
    }
}
    /**
     * Get user's quiz attempt history
     */
    public function getUserAttempts($userId, $quizId = null) {
        $sql = "
            SELECT 
                uqa.*,
                q.title as quiz_title,
                q.category,
                q.difficulty
            FROM user_quiz_attempts uqa
            INNER JOIN quizzes q ON uqa.quiz_id = q.id
            WHERE uqa.user_id = :user_id
        ";
        
        if ($quizId) {
            $sql .= " AND uqa.quiz_id = :quiz_id";
        }
        
        $sql .= " ORDER BY uqa.completed_at DESC";
        
        $this->db->query($sql);
        $this->db->bind(':user_id', $userId);
        
        if ($quizId) {
            $this->db->bind(':quiz_id', $quizId);
        }
        
        return $this->db->resultSet();
    }
    
    // =============================================
    // SAVED QUIZZES
    // =============================================
    
    /**
     * Save quiz for later
     */
    public function saveQuizForLater($userId, $quizId) {
        try {
            $this->db->query("
                INSERT INTO user_saved_quizzes (user_id, quiz_id)
                VALUES (:user_id, :quiz_id)
                ON DUPLICATE KEY UPDATE saved_at = CURRENT_TIMESTAMP
            ");
            
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':quiz_id', $quizId);
            
            return $this->db->execute();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Remove quiz from saved
     */
    public function unsaveQuiz($userId, $quizId) {
        $this->db->query("
            DELETE FROM user_saved_quizzes 
            WHERE user_id = :user_id AND quiz_id = :quiz_id
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':quiz_id', $quizId);
        
        return $this->db->execute();
    }
    
    // =============================================
    // BADGES
    // =============================================
    
    /**
     * Award badge to user
     */
    private function awardBadge($userId, $badgeName, $sourceId = null) {
        // Get badge by name
        $this->db->query("SELECT id FROM badges WHERE name = :badge_name");
        $this->db->bind(':badge_name', $badgeName);
        $badge = $this->db->single();
        
        if (!$badge) {
            return false;
        }
        
        // Check if user already has this badge
        $this->db->query("
            SELECT id FROM user_badges 
            WHERE user_id = :user_id AND badge_id = :badge_id
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':badge_id', $badge->id);
        $existing = $this->db->single();
        
        if ($existing) {
            return true; // Already has badge
        }
        
        // Award badge
        $this->db->query("
            INSERT INTO user_badges (user_id, badge_id, source_type, source_id)
            VALUES (:user_id, :badge_id, 'quiz', :source_id)
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':badge_id', $badge->id);
        $this->db->bind(':source_id', $sourceId);
        
        return $this->db->execute();
    }
    
    /**
     * Get all user badges
     */
    public function getUserBadges($userId) {
        $this->db->query("
            SELECT 
                b.id,
                b.name,
                b.description,
                b.icon,
                b.badge_type,
                b.color,
                ub.earned_at,
                q.title as quiz_title
            FROM user_badges ub
            INNER JOIN badges b ON ub.badge_id = b.id
            LEFT JOIN quizzes q ON ub.source_id = q.id AND ub.source_type = 'quiz'
            WHERE ub.user_id = :user_id
            ORDER BY ub.earned_at DESC
        ");
        
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }
    
    // =============================================
    // STATISTICS
    // =============================================
    
    /**
     * Get quiz statistics for user
     */
    public function getUserQuizStats($userId) {
        $this->db->query("
            SELECT 
                COUNT(DISTINCT quiz_id) as quizzes_completed,
                SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as quizzes_passed,
                AVG(score) as average_score,
                COUNT(*) as total_attempts
            FROM user_quiz_attempts
            WHERE user_id = :user_id
        ");
        
        $this->db->bind(':user_id', $userId);
        return $this->db->single();
    }
}