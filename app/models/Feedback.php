<?php
/**
 * Feedback Model
 * Handles database operations for user feedback/ratings
 */
class Feedback extends Database {

    /**
     * Submit new feedback with context support
     * 
     * @param array $data - Feedback data (user_id, reviewer_id, context_type, context_id, rating, comment, tags)
     * @return bool - True on success, false on failure
     */
    public function submitFeedback($data) {
        $sql = "INSERT INTO user_feedback (user_id, reviewer_id, project_id, context_type, context_id, rating, comment, tags, created_at) 
                VALUES (:user_id, :reviewer_id, :project_id, :context_type, :context_id, :rating, :comment, :tags, NOW())";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $data['user_id']);
        $stmt->bindValue(':reviewer_id', $data['reviewer_id']);
        $stmt->bindValue(':project_id', $data['project_id'] ?? null);
        $stmt->bindValue(':context_type', $data['context_type'] ?? 'project');
        $stmt->bindValue(':context_id', $data['context_id'] ?? null);
        $stmt->bindValue(':rating', $data['rating']);
        $stmt->bindValue(':comment', $data['comment'] ?? '');
        $stmt->bindValue(':tags', $data['tags'] ?? '');
        
        return $stmt->execute();
    }

    /**
     * Check if feedback already exists for this context
     * 
     * @param int $user_id - User being rated
     * @param int $reviewer_id - User giving rating
     * @param string $context_type - 'project' or 'session'
     * @param int $context_id - Context ID
     * @return bool
     */
    public function feedbackExists($user_id, $reviewer_id, $context_type, $context_id) {
        $sql = "SELECT id FROM user_feedback 
                WHERE user_id = :user_id 
                AND reviewer_id = :reviewer_id 
                AND context_type = :context_type
                AND context_id = :context_id";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->bindValue(':reviewer_id', $reviewer_id);
        $stmt->bindValue(':context_type', $context_type);
        $stmt->bindValue(':context_id', $context_id);
        $stmt->execute();
        
        return $stmt->fetch() ? true : false;
    }

    /**
     * Get average rating for a user
     * 
     * @param int $user_id
     * @param int|null $project_id - Optional: filter by specific project
     * @return float
     */
    public function getAverageRating($user_id, $project_id = null) {
        $sql = "SELECT IFNULL(ROUND(AVG(rating), 2), 0) as avg_rating 
                FROM user_feedback 
                WHERE user_id = :user_id";
        
        if ($project_id !== null) {
            $sql .= " AND project_id = :project_id";
        }
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $user_id);
        
        if ($project_id !== null) {
            $stmt->bindValue(':project_id', $project_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (float)$result['avg_rating'] : 0.0;
    }

    /**
     * Get total feedback count for a user
     * 
     * @param int $user_id
     * @param int|null $project_id - Optional: filter by specific project
     * @return int
     */
    public function getFeedbackCount($user_id, $project_id = null) {
        $sql = "SELECT COUNT(*) as count 
                FROM user_feedback 
                WHERE user_id = :user_id";
        
        if ($project_id !== null) {
            $sql .= " AND project_id = :project_id";
        }
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $user_id);
        
        if ($project_id !== null) {
            $stmt->bindValue(':project_id', $project_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Get all feedback for a user with reviewer details
     * 
     * @param int $user_id
     * @param int|null $project_id - Optional: filter by project
     * @return array
     */
    public function getUserFeedback($user_id, $project_id = null) {
        $sql = "SELECT uf.*, 
                       u.username as reviewer_name, 
                       u.profile_picture as reviewer_picture,
                       p.title as project_title
                FROM user_feedback uf
                LEFT JOIN users u ON uf.reviewer_id = u.id
                LEFT JOIN projects p ON uf.project_id = p.id
                WHERE uf.user_id = :user_id";
        
        if ($project_id !== null) {
            $sql .= " AND uf.project_id = :project_id";
        }
        
        $sql .= " ORDER BY uf.created_at DESC";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $user_id);
        
        if ($project_id !== null) {
            $stmt->bindValue(':project_id', $project_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get feedback by ID
     * 
     * @param int $feedback_id
     * @return array|false
     */
    public function getFeedbackById($feedback_id) {
        $sql = "SELECT uf.*, 
                       u.username as reviewer_name,
                       p.title as project_title
                FROM user_feedback uf
                LEFT JOIN users u ON uf.reviewer_id = u.id
                LEFT JOIN projects p ON uf.project_id = p.id
                WHERE uf.id = :id";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':id', $feedback_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get feedback statistics for a project
     * 
     * @param int $project_id
     * @return array|false
     */
    public function getProjectFeedbackStats($project_id) {
        $sql = "SELECT 
                    COUNT(*) as total_feedback,
                    ROUND(AVG(rating), 2) as avg_rating,
                    COUNT(DISTINCT user_id) as users_rated,
                    COUNT(DISTINCT reviewer_id) as reviewers,
                    SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_feedback,
                    SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as negative_feedback
                FROM user_feedback
                WHERE project_id = :project_id";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':project_id', $project_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update existing feedback
     * 
     * @param int $feedback_id
     * @param int $rating
     * @param string $comment
     * @return bool
     */
    public function updateFeedback($feedback_id, $rating, $comment) {
        $sql = "UPDATE user_feedback 
                SET rating = :rating, comment = :comment
                WHERE id = :id";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':rating', $rating);
        $stmt->bindValue(':comment', $comment);
        $stmt->bindValue(':id', $feedback_id);
        
        return $stmt->execute();
    }

    /**
     * Delete feedback
     * 
     * @param int $feedback_id
     * @return bool
     */
    public function deleteFeedback($feedback_id) {
        $sql = "DELETE FROM user_feedback WHERE id = :id";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':id', $feedback_id);
        
        return $stmt->execute();
    }

    /**
     * Get rating distribution for a user
     * 
     * @param int $user_id
     * @return array - Array with counts for each rating (1-5 stars)
     */
    public function getRatingDistribution($user_id) {
        $sql = "SELECT 
                    rating,
                    COUNT(*) as count
                FROM user_feedback
                WHERE user_id = :user_id
                GROUP BY rating
                ORDER BY rating DESC";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Initialize all ratings to 0
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        
        // Fill in actual counts
        foreach ($results as $row) {
            $distribution[$row['rating']] = (int)$row['count'];
        }
        
        return $distribution;
    }

    /**
     * Get comprehensive stats for a user in a specific context
     * 
     * @param int $user_id
     * @param string $context_type - 'project' or 'session'
     * @param int|null $context_id - Optional: specific context
     * @return object - Contains avg_rating, total_count, and rating distribution
     */
    public function getUserStats($user_id, $context_type = null, $context_id = null) {
        $sql = "SELECT 
                    COUNT(*) as total_count,
                    IFNULL(ROUND(AVG(rating), 2), 0) as avg_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM user_feedback 
                WHERE user_id = :user_id";
        
        if ($context_type !== null) {
            $sql .= " AND context_type = :context_type";
        }
        
        if ($context_id !== null) {
            $sql .= " AND context_id = :context_id";
        }
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $user_id);
        
        if ($context_type !== null) {
            $stmt->bindValue(':context_type', $context_type);
        }
        
        if ($context_id !== null) {
            $stmt->bindValue(':context_id', $context_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $result ?: (object)[
            'total_count' => 0,
            'avg_rating' => 0,
            'five_star' => 0,
            'four_star' => 0,
            'three_star' => 0,
            'two_star' => 0,
            'one_star' => 0
        ];
    }

    /**
     * Get filtered and sorted feedback with pagination
     * 
     * @param int $user_id - User whose feedback to retrieve
     * @param array $filters - Filtering and sorting options
     * @return array - Contains 'items' and 'total_count'
     */
    public function getFilteredFeedback($user_id, $filters = []) {
        // Extract filters with defaults
        $context_type = $filters['context_type'] ?? null;
        $context_id = $filters['context_id'] ?? null;
        $rating = $filters['rating'] ?? null;
        $tag = $filters['tag'] ?? null;
        $reviewer_id = $filters['reviewer_id'] ?? null;
        $search = $filters['search'] ?? null;
        $sort = $filters['sort'] ?? 'newest';
        $page = max(1, (int)($filters['page'] ?? 1));
        $limit = max(1, min(100, (int)($filters['limit'] ?? 10)));
        
        // Build WHERE clause
        $where = ["uf.user_id = :user_id"];
        $params = [':user_id' => $user_id];
        
        if ($context_type) {
            $where[] = "uf.context_type = :context_type";
            $params[':context_type'] = $context_type;
        }
        
        if ($context_id) {
            $where[] = "uf.context_id = :context_id";
            $params[':context_id'] = $context_id;
        }
        
        if ($rating) {
            $where[] = "uf.rating = :rating";
            $params[':rating'] = $rating;
        }
        
        if ($tag) {
            $where[] = "FIND_IN_SET(:tag, uf.tags) > 0";
            $params[':tag'] = $tag;
        }
        
        if ($reviewer_id) {
            $where[] = "uf.reviewer_id = :reviewer_id";
            $params[':reviewer_id'] = $reviewer_id;
        }
        
        if ($search) {
            $where[] = "uf.comment LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Determine sort order
        $orderBy = match($sort) {
            'oldest' => 'uf.created_at ASC',
            'highest' => 'uf.rating DESC, uf.created_at DESC',
            'lowest' => 'uf.rating ASC, uf.created_at DESC',
            default => 'uf.created_at DESC' // newest
        };
        
        // Count total matching records
        $countSql = "SELECT COUNT(*) as total 
                     FROM user_feedback uf 
                     WHERE {$whereClause}";
        
        $countStmt = $this->connect()->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total_count = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get paginated items
        $offset = ($page - 1) * $limit;
        
        $itemsSql = "SELECT uf.*, 
                            u.username as reviewer_name, 
                            u.profile_picture as reviewer_picture,
                            CASE 
                                WHEN uf.context_type = 'project' THEN p.name
                                ELSE NULL
                            END as context_name
                     FROM user_feedback uf
                     LEFT JOIN users u ON uf.reviewer_id = u.id
                     LEFT JOIN projects p ON uf.context_type = 'project' AND uf.context_id = p.id
                     WHERE {$whereClause}
                     ORDER BY {$orderBy}
                     LIMIT :limit OFFSET :offset";
        
        $itemsStmt = $this->connect()->prepare($itemsSql);
        foreach ($params as $key => $value) {
            $itemsStmt->bindValue($key, $value);
        }
        $itemsStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $itemsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $itemsStmt->execute();
        
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'items' => $items,
            'total_count' => $total_count
        ];
    }
}

?>