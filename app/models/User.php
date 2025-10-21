<?php
class User extends Database {

    // 🔹 Register Organization
    public function registerOrganization($name, $email, $password, $certPath) {
        $sql = "INSERT INTO users (username, email, password, role, org_cert)
                VALUES (:name, :email, :password, 'organization', :cert)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
        $stmt->bindValue(':cert', $certPath);
        return $stmt->execute();
    }

    // 🔹 Register Individual
    public function registerIndividual($name, $email, $password) {
        $sql = "INSERT INTO users (username, email, password, role, profile_completed)
                VALUES (:name, :email, :password, 'individual', 0)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
        
        if ($stmt->execute()) {
            $userId = $this->connect()->lastInsertId();
            // Initialize user stats
            $this->initializeUserStats($userId);
            return $userId;
        }
        return false;
    }

    // 🔹 Login (shared for both roles)
    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
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
        return $stmt->fetch();
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
        
        while ($row = $stmt->fetch()) {
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
        
        while ($row = $stmt->fetch()) {
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
        $sql = "SELECT badge_name, badge_icon, earned_at 
                FROM user_badges 
                WHERE user_id = :user_id 
                ORDER BY earned_at DESC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        
        $badges = [];
        while ($row = $stmt->fetch()) {
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
        while ($row = $stmt->fetch()) {
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
        
        $stats = $stmt->fetch();
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
        
        $result = $stmt->fetch();
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
        $sql = "INSERT INTO user_badges (user_id, badge_name, badge_icon) 
                VALUES (:user_id, :name, :icon)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':name', $badgeName);
        $stmt->bindValue(':icon', $badgeIcon);
        return $stmt->execute();
    }


    // 🔹 Get User Activity
public function getUserActivity($userId) {
    $sql = "SELECT * FROM user_activity 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC";
    $stmt = $this->connect()->prepare($sql);
    $stmt->bindValue(':user_id', $userId);
    $stmt->execute();
    return $stmt->fetchAll();
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
        return $stmt->fetch() ? true : false;
    }
}
