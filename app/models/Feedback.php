<?php
/**
 * Feedback Model
 * Handles database operations for user feedback
 */
class Feedback extends Database {

    /**
     * Submit feedback for a user
     * 
     * @param int $userId - The user being rated
     * @param int $reviewerId - The user giving the rating
     * @param int $rating - Rating value (1-5)
     * @param string|null $comment - Optional feedback comment
     * @param int|null $projectId - Optional project association
     * @return bool|string - True on success, error message on failure
     */
    public function submitFeedback($userId, $reviewerId, $rating, $comment = null, $projectId = null) {
        try {
            // Validate rating
            if ($rating < 1 || $rating > 5) {
                return 'Rating must be between 1 and 5';
            }

            // Prevent self-feedback
            if ($userId === $reviewerId) {
                return 'You cannot give feedback to yourself';
            }

            // Check for duplicate feedback (same project/user/reviewer combination)
            if ($this->hasDuplicateFeedback($userId, $reviewerId, $projectId)) {
                return 'You have already provided feedback for this user on this project';
            }

            // Insert feedback
            $sql = "INSERT INTO user_feedback (user_id, reviewer_id, rating, comment, project_id, created_at) 
                    VALUES (:user_id, :reviewer_id, :rating, :comment, :project_id, NOW())";
            
            $this->query($sql);
            $this->bind(':user_id', $userId);
            $this->bind(':reviewer_id', $reviewerId);
            $this->bind(':rating', $rating);
            $this->bind(':comment', $comment);
            $this->bind(':project_id', $projectId);

            if ($this->execute()) {
                return true;
            }

            return 'Failed to submit feedback';
        } catch (PDOException $e) {
            error_log("Feedback submission error: " . $e->getMessage());
            return 'An error occurred while submitting feedback';
        }
    }

    /**
     * Check if feedback already exists for this user/project/reviewer combination
     * 
     * @param int $userId
     * @param int $reviewerId
     * @param int|null $projectId
     * @return bool
     */
    private function hasDuplicateFeedback($userId, $reviewerId, $projectId) {
        $sql = "SELECT id FROM user_feedback 
                WHERE user_id = :user_id 
                AND reviewer_id = :reviewer_id 
                AND (project_id = :project_id OR (project_id IS NULL AND :project_id IS NULL))";
        
        $this->query($sql);
        $this->bind(':user_id', $userId);
        $this->bind(':reviewer_id', $reviewerId);
        $this->bind(':project_id', $projectId);
        
        return $this->single() !== false;
    }

    /**
     * Get all feedback for a user
     * 
     * @param int $userId
     * @param int|null $projectId - Optional: filter by project
     * @return array
     */
    public function getFeedbackForUser($userId, $projectId = null) {
        $sql = "SELECT uf.*, u.username as reviewer_name, u.profile_picture as reviewer_picture
                FROM user_feedback uf
                JOIN users u ON uf.reviewer_id = u.id
                WHERE uf.user_id = :user_id";
        
        if ($projectId !== null) {
            $sql .= " AND uf.project_id = :project_id";
        }
        
        $sql .= " ORDER BY uf.created_at DESC";
        
        $this->query($sql);
        $this->bind(':user_id', $userId);
        
        if ($projectId !== null) {
            $this->bind(':project_id', $projectId);
        }
        
        return $this->resultSet();
    }

    /**
     * Get average rating for a user
     * 
     * @param int $userId
     * @param int|null $projectId - Optional: filter by project
     * @return float
     */
    public function getAverageRating($userId, $projectId = null) {
        $sql = "SELECT IFNULL(ROUND(AVG(rating), 2), 0) as avg_rating
                FROM user_feedback
                WHERE user_id = :user_id";
        
        if ($projectId !== null) {
            $sql .= " AND project_id = :project_id";
        }
        
        $this->query($sql);
        $this->bind(':user_id', $userId);
        
        if ($projectId !== null) {
            $this->bind(':project_id', $projectId);
        }
        
        $result = $this->single();
        return $result ? (float)$result->avg_rating : 0.0;
    }

    /**
     * Get feedback count for a user
     * 
     * @param int $userId
     * @param int|null $projectId - Optional: filter by project
     * @return int
     */
    public function getFeedbackCount($userId, $projectId = null) {
        $sql = "SELECT COUNT(*) as count
                FROM user_feedback
                WHERE user_id = :user_id";
        
        if ($projectId !== null) {
            $sql .= " AND project_id = :project_id";
        }
        
        $this->query($sql);
        $this->bind(':user_id', $userId);
        
        if ($projectId !== null) {
            $this->bind(':project_id', $projectId);
        }
        
        $result = $this->single();
        return $result ? (int)$result->count : 0;
    }

    /**
     * Get feedback statistics for a project
     * 
     * @param int $projectId
     * @return object|null
     */
    public function getProjectFeedbackStats($projectId) {
        $sql = "SELECT 
                    COUNT(*) as total_feedback,
                    AVG(rating) as avg_rating,
                    COUNT(DISTINCT user_id) as users_rated,
                    COUNT(DISTINCT reviewer_id) as reviewers
                FROM user_feedback
                WHERE project_id = :project_id";
        
        $this->query($sql);
        $this->bind(':project_id', $projectId);
        
        return $this->single();
    }

    /**
     * Check if reviewer can give feedback to user on this project
     * (Validates that reviewer is part of the project organization)
     * 
     * @param int $reviewerId
     * @param int $projectId
     * @return bool
     */
    public function canReviewerGiveFeedback($reviewerId, $projectId) {
        $sql = "SELECT p.organization_id
                FROM projects p
                WHERE p.id = :project_id";
        
        $this->query($sql);
        $this->bind(':project_id', $projectId);
        
        $project = $this->single();
        
        if (!$project) {
            return false;
        }

        // Check if reviewer is the organization owner
        $sql = "SELECT id FROM users WHERE id = :reviewer_id AND role = 'organization'";
        $this->query($sql);
        $this->bind(':reviewer_id', $reviewerId);
        
        return $this->single() !== false;
    }
}
