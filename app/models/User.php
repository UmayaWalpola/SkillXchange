<?php
class User extends Database {

    // 🔹 Register Organization
    public function registerOrganization($name, $email, $password, $certPath) {
        $username = $this->generateUniqueUsername($name);
        if (!$username) {
            return false;
        }

        $sql = "INSERT INTO users (username, email, password, role, org_cert)
                VALUES (:username, :email, :password, 'organization', :cert)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
        $stmt->bindValue(':cert', $certPath);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return false;
            }
            throw $e;
        }
    }

    // 🔹 Register Individual
    public function registerIndividual($name, $email, $password) {
        $username = $this->generateUniqueUsername($name);
        if (!$username) {
            return false;
        }

        $conn = $this->connect();

        $sql = "INSERT INTO users (username, email, password, role, profile_completed)
                VALUES (:username, :email, :password, 'individual', 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));

        try {
            if ($stmt->execute()) {
                $userId = $conn->lastInsertId();
                // Initialize user stats
                $this->initializeUserStats($userId);
                return $userId;
            }
            return false;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return false;
            }
            throw $e;
        }
    }

    // 🔹 Login (Updated with Suspension Logic)
    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {

            // Suspension: block login if account is suspended.
            // Avoid reliance on optional DB columns (e.g. timed suspension fields).
            $status = strtolower(trim((string)($user['status'] ?? '')));
            if ($status === 'suspended') {
                return 'suspended|';
            }

            return $user;
        }
        return false;
    }
    // 🔹 Find user by ID
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 Complete Profile Setup
    public function completeProfile($userId, $username, $profilePicture, $bio = null) {
        $sql = "UPDATE users 
                SET username = :username, 
                    profile_picture = :picture, 
                    bio = :bio,
                    profile_completed = 1 
                WHERE id = :id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':picture', $profilePicture);
        $stmt->bindValue(':bio', $bio);
        $stmt->bindValue(':id', $userId);
        return $stmt->execute();
    }

    // 🔹 Add User Skills (both teach and learn)
    public function addUserSkills($userId, $skills, $levels, $type) {
        $sql = "INSERT INTO user_skills (user_id, skill_name, skill_type, proficiency_level) 
                VALUES (:user_id, :skill_name, :type, :level)";
        $stmt = $this->connect()->prepare($sql);
        
        $success = true;
        foreach ($skills as $index => $skill) {
            if (!empty($skill) && !empty($levels[$index])) {
                $stmt->bindValue(':user_id', $userId);
                $stmt->bindValue(':skill_name', $skill);
                $stmt->bindValue(':type', $type);
                $stmt->bindValue(':level', $levels[$index]);
                
                if (!$stmt->execute()) {
                    $success = false;
                }
            }
        }
        
        // Update stats
        if ($success) {
            $this->updateSkillStats($userId);
        }
        
        return $success;
    }

    // 🔹 Get User Skills
    public function getUserSkills($userId) {
        $sql = "SELECT skill_name, skill_type, proficiency_level 
                FROM user_skills 
                WHERE user_id = :user_id 
                ORDER BY skill_type, skill_name";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        
        $skills = [
            'teaches' => [],
            'learns' => []
        ];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $skillData = [
                'name' => $row['skill_name'],
                'level' => $row['proficiency_level']
            ];
            
            if ($row['skill_type'] === 'teach') {
                $skills['teaches'][] = $skillData;
            } else {
                $skills['learns'][] = $skillData;
            }
        }
        
        return $skills;
    }

    // 🔹 Get User Projects
    public function getUserProjects($userId) {
        $sql = "SELECT * FROM user_projects 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        
        $projects = [
            'completed' => [],
            'in_progress' => []
        ];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $project = [
                'title' => $row['title'],
                'description' => $row['description'],
                'created_at' => $row['created_at']
            ];
            
            if ($row['status'] === 'completed') {
                $projects['completed'][] = $project;
            } else {
                $projects['in_progress'][] = $project;
            }
        }
        
        return $projects;
    }

    // 🔹 Get User Badges
    public function getUserBadges($userId) {
        $conn = $this->connect();

        try {
            // Current schema: user_badges(user_id, badge_id) + badges table.
            $sql = "SELECT b.name as badge_name, b.icon as badge_icon, ub.earned_at 
                    FROM user_badges ub
                    JOIN badges b ON ub.badge_id = b.id
                    WHERE ub.user_id = :user_id 
                    ORDER BY ub.earned_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId);
            $stmt->execute();
        } catch (PDOException $e) {
            // Legacy fallback: user_badges stores badge_name and badge_icon directly.
            if (!in_array($e->getCode(), ['42S22', '42S02'])) {
                throw $e;
            }

            $sql = "SELECT badge_name, badge_icon, earned_at
                    FROM user_badges
                    WHERE user_id = :user_id
                    ORDER BY earned_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId);
            $stmt->execute();
        }
        
        $badges = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $badges[] = [
                'icon' => $row['badge_icon'],
                'name' => $row['badge_name'],
                'earned_at' => $row['earned_at']
            ];
        }
        
        return $badges;
    }

    // 🔹 Get User Feedback/Reviews
    public function getUserFeedback($userId) {
        $sql = "SELECT uf.rating, uf.comment, uf.created_at, u.username as reviewer_name
                FROM user_feedback uf
                JOIN users u ON uf.reviewer_id = u.id
                WHERE uf.user_id = :user_id
                ORDER BY uf.created_at DESC
                LIMIT 10";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        
        $feedback = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $feedback[] = [
                'reviewer_name' => $row['reviewer_name'],
                'rating' => $row['rating'],
                'comment' => $row['comment'],
                'date' => $this->timeAgo($row['created_at'])
            ];
        }
        
        return $feedback;
    }

    // 🔹 Update Profile (for editing)
    public function updateProfile($userId, $username, $profilePicture, $bio = null) {
        $sql = "UPDATE users 
                SET username = :username, 
                    profile_picture = :picture, 
                    bio = :bio
                WHERE id = :id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':picture', $profilePicture);
        $stmt->bindValue(':bio', $bio);
        $stmt->bindValue(':id', $userId);
        return $stmt->execute();
    }

    // 🔹 Delete User Skills (before updating)
    public function deleteUserSkills($userId) {
        $sql = "DELETE FROM user_skills WHERE user_id = :user_id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        return $stmt->execute();
    }

    // 🔹 Get User Stats
    public function getUserStats($userId) {
        $sql = "SELECT * FROM user_stats WHERE user_id = :user_id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$stats) {
            // Initialize if doesn't exist
            $this->initializeUserStats($userId);
            return [
                'connections_count' => 0,
                'skills_taught_count' => 0,
                'skills_learning_count' => 0,
                'hours_exchanged' => 0
            ];
        }
        
        return $stats;
    }

    // 🔹 Get Average Rating
    public function getAverageRating($userId) {
        $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                FROM user_feedback 
                WHERE user_id = :user_id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'rating' => $result['avg_rating'] ? round($result['avg_rating'], 1) : 0,
            'count' => $result['review_count']
        ];
    }

    // 🔹 Initialize User Stats
    private function initializeUserStats($userId) {
        $sql = "INSERT INTO user_stats (user_id) VALUES (:user_id)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        return $stmt->execute();
    }

    // 🔹 Update Skill Stats
    private function updateSkillStats($userId) {
        $sql = "UPDATE user_stats SET 
                skills_taught_count = (SELECT COUNT(*) FROM user_skills WHERE user_id = :user_id AND skill_type = 'teach'),
                skills_learning_count = (SELECT COUNT(*) FROM user_skills WHERE user_id = :user_id AND skill_type = 'learn')
                WHERE user_id = :user_id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        return $stmt->execute();
    }

    // 🔹 Log User Activity
    public function logActivity($userId, $activityType, $description) {
        $sql = "INSERT INTO user_activity (user_id, activity_type, description) 
                VALUES (:user_id, :type, :description)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':type', $activityType);
        $stmt->bindValue(':description', $description);
        return $stmt->execute();
    }

    // 🔹 Award Badge to User
    public function awardBadge($userId, $badgeName, $badgeIcon) {
        $conn = $this->connect();

        try {
            // Current schema path: resolve/create badge, then link by badge_id.
            $findBadge = $conn->prepare("SELECT id FROM badges WHERE name = :name LIMIT 1");
            $findBadge->bindValue(':name', $badgeName);
            $findBadge->execute();
            $badgeId = $findBadge->fetchColumn();

            if (!$badgeId) {
                $createBadge = $conn->prepare(
                    "INSERT INTO badges (name, description, icon, type)
                     VALUES (:name, :description, :icon, 'community')"
                );
                $createBadge->bindValue(':name', $badgeName);
                $createBadge->bindValue(':description', 'Platform achievement badge');
                $createBadge->bindValue(':icon', $badgeIcon);
                $createBadge->execute();
                $badgeId = $conn->lastInsertId();
            }

            $linkBadge = $conn->prepare(
                "INSERT IGNORE INTO user_badges (user_id, badge_id, earned_at)
                 VALUES (:user_id, :badge_id, NOW())"
            );
            $linkBadge->bindValue(':user_id', $userId);
            $linkBadge->bindValue(':badge_id', $badgeId);
            return $linkBadge->execute();
        } catch (PDOException $e) {
            // Legacy fallback for environments where user_badges has badge_name/badge_icon.
            if (!in_array($e->getCode(), ['42S22', '42S02'])) {
                throw $e;
            }

            $sql = "INSERT INTO user_badges (user_id, badge_name, badge_icon) 
                    VALUES (:user_id, :name, :icon)";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId);
            $stmt->bindValue(':name', $badgeName);
            $stmt->bindValue(':icon', $badgeIcon);
            return $stmt->execute();
        }
    }

    // 🔹 Get User Activity
    public function getUserActivity($userId) {
        $sql = "SELECT * FROM user_activity 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 Helper: Time Ago
    private function timeAgo($timestamp) {
        $time = strtotime($timestamp);
        $diff = time() - $time;
        
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        if ($diff < 604800) return floor($diff / 86400) . ' days ago';
        if ($diff < 2592000) return floor($diff / 604800) . ' weeks ago';
        return date('M j, Y', $time);
    }

    // 🔹 Check if username exists
    public function usernameExists($username, $excludeUserId = null) {
        $sql = "SELECT id FROM users WHERE username = :username";
        if ($excludeUserId) {
            $sql .= " AND id != :exclude_id";
        }
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':username', $username);
        if ($excludeUserId) {
            $stmt->bindValue(':exclude_id', $excludeUserId);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    // 🔹 Build a unique username for registration
    private function generateUniqueUsername($preferredName) {
        $baseUsername = trim((string)$preferredName);
        if ($baseUsername === '') {
            $baseUsername = 'user';
        }

        $baseUsername = preg_replace('/\s+/', ' ', $baseUsername);
        $maxLength = 50;
        $baseUsername = substr($baseUsername, 0, $maxLength);

        $candidate = $baseUsername;
        $counter = 1;

        while ($this->usernameExists($candidate)) {
            $suffix = '_' . $counter;
            $trimmedBase = substr($baseUsername, 0, $maxLength - strlen($suffix));
            $candidate = $trimmedBase . $suffix;
            $counter++;

            if ($counter > 9999) {
                return false;
            }
        }

        return $candidate;
    }

    // Get total users count
    public function getTotalUsers() {
        $this->db->query('SELECT COUNT(*) as count FROM users WHERE role = "user"');
        $result = $this->db->single();
        return $result['count'] ?? 0;
    }

    // Get recent users
    public function getRecentUsers($limit = 5) {
        $this->db->query('
            SELECT id, username, email, created_at, status 
            FROM users 
            WHERE role = "user"
            ORDER BY created_at DESC 
            LIMIT :limit
        ');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    // Get all users with stats
    public function getAllUsersWithStats() {
        $this->db->query('
            SELECT 
                u.id,
                u.username,
                u.email,
                u.created_at,
                u.status,
                COUNT(DISTINCT CASE WHEN us.type = "teach" THEN us.skill_id END) as total_skills,
                COUNT(DISTINCT e.id) as total_exchanges
            FROM users u
            LEFT JOIN user_skills us ON u.id = us.user_id
            LEFT JOIN exchanges e ON (u.id = e.requester_id OR u.id = e.provider_id)
            WHERE u.role = "user"
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ');
        return $this->db->resultSet();
    }

    // Update user status
    public function updateUserStatus($userId, $status) {
        $this->db->query('UPDATE users SET status = :status WHERE id = :id');
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $userId);
        return $this->db->execute();
    }

    // Delete user
    public function deleteUser($userId) {
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Delete user skills
            $this->db->query('DELETE FROM user_skills WHERE user_id = :user_id');
            $this->db->bind(':user_id', $userId);
            $this->db->execute();
            
            // Delete user exchanges (or update them to mark as cancelled)
            $this->db->query('UPDATE exchanges SET status = "cancelled" WHERE requester_id = :user_id OR provider_id = :user_id');
            $this->db->bind(':user_id', $userId);
            $this->db->execute();
            
            // Delete user
            $this->db->query('DELETE FROM users WHERE id = :id');
            $this->db->bind(':id', $userId);
            $this->db->execute();
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    // Clear suspension date (used when auto-reactivating)
    public function clearSuspensionDate($userId) {
        $sql = "UPDATE users SET suspension_end_date = NULL WHERE id = :id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':id', $userId);
        return $stmt->execute();
    }


}
