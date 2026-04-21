<?php
class User extends Database {

    // CODECHECK GUIDE: Organization account insert.
    // If the controller sends a new organization field, add the column, placeholder, and bind here.
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

    // CODECHECK GUIDE: Individual account insert.
    // Registration-only fields are added to this INSERT after AuthController validates them.
    public function registerIndividual($name, $email, $password) {

        $sql = "INSERT INTO users (username, email, password, role, profile_completed)
                VALUES (:name, :email, :password, 'individual', 0)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        // Always hash passwords before saving; never store the plain password.
        $stmt->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
        
        
        if ($stmt->execute()) {
            $userId = $this->connect()->lastInsertId();
            // New individual users need a stats row for profile counters and dashboards.
            $this->initializeUserStats($userId);
            return $userId;
        }
        return false;
    }

    
    // CODECHECK GUIDE: Login lookup + password verification.
    // AuthController decides the redirect; this model only returns the matched user or false.
    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // password_verify() checks the submitted password against the saved hash.
        if ($user && password_verify($password, $user['password'])) {

            $status = strtolower(trim((string)($user['status'] ?? '')));
            if ($status === 'suspended') {

                // Auto-lift if expiry time has passed
                if (!empty($user['suspension_expires_at']) && strtotime($user['suspension_expires_at']) < time()) {
                    $liftSql = "UPDATE users SET
                                    status = 'active',
                                    suspended_at = NULL,
                                    suspended_by = NULL,
                                    suspension_reason = NULL,
                                    suspension_expires_at = NULL
                                WHERE id = :id";
                    $stmt2 = $this->connect()->prepare($liftSql);
                    $stmt2->bindValue(':id', $user['id']);
                    $stmt2->execute();

                    // continue login as normal
                    $user['status'] = 'active';
                    return $user;
                }

                return 'suspended|';
            }

            return $user;
        }
        return false;
    }

    // CODECHECK GUIDE: Most profile pages use this SELECT * result.
    // If a saved field is not showing, check whether the controller remaps this array before the view.
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // CODECHECK GUIDE: Profile setup update helper.
    // Add profile-completion fields here only if the controller calls this helper for that flow.
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

    // CODECHECK GUIDE: Skill setup insert.
    // Profile setup passes teach/learn skill arrays here or inserts them directly in UsersController.
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

    public function getAllSkills() {
        $sql = "SELECT id, skill_name, description FROM skills ORDER BY skill_name ASC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function skillExists($skillName) {
        $sql = "SELECT id FROM skills WHERE LOWER(skill_name) = LOWER(:skill_name) LIMIT 1";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':skill_name', trim($skillName));
        $stmt->execute();
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //  Get User Skills
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

    //  Get User Projects
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

    //  Get User Badges
    public function getUserBadges($userId) {
        $sql = "SELECT
                    COALESCE(b.name, ub.badge_name) as badge_name,
                    COALESCE(b.icon, ub.badge_icon) as badge_icon,
                    ub.earned_at
                FROM user_badges ub
                LEFT JOIN badges b ON ub.badge_id = b.id
                WHERE ub.user_id = :user_id 
                ORDER BY ub.earned_at DESC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        
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

    //  Get User Feedback/Reviews
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

    //  Update Profile (for editing)
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

    //  Delete User Skills (before updating)
    public function deleteUserSkills($userId) {
        $sql = "DELETE FROM user_skills WHERE user_id = :user_id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        return $stmt->execute();
    }

    //  Get User Stats
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

    public function getLiveUserStats($userId) {
        $sql = "SELECT
                    (
                        SELECT COUNT(*)
                        FROM exchanges
                        WHERE (requester_id = :user_id OR receiver_id = :user_id)
                          AND status = 'active'
                    ) AS connections_count,
                    (
                        SELECT COUNT(*)
                        FROM user_skills
                        WHERE user_id = :user_id
                          AND skill_type = 'teach'
                    ) AS skills_taught_count,
                    (
                        SELECT COUNT(*)
                        FROM user_skills
                        WHERE user_id = :user_id
                          AND skill_type = 'learn'
                    ) AS skills_learning_count,
                    COALESCE((
                        SELECT hours_exchanged
                        FROM user_stats
                        WHERE user_id = :user_id
                        LIMIT 1
                    ), 0) AS hours_exchanged";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$stats) {
            return [
                'connections_count' => 0,
                'skills_taught_count' => 0,
                'skills_learning_count' => 0,
                'hours_exchanged' => 0
            ];
        }

        return $stats;
    }

    //  Get Average Rating
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

    //  Initialize User Stats
    private function initializeUserStats($userId) {
        $sql = "INSERT INTO user_stats (user_id) VALUES (:user_id)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        return $stmt->execute();
    }

    //  Update Skill Stats
    private function updateSkillStats($userId) {
        $sql = "UPDATE user_stats SET 
                skills_taught_count = (SELECT COUNT(*) FROM user_skills WHERE user_id = :user_id AND skill_type = 'teach'),
                skills_learning_count = (SELECT COUNT(*) FROM user_skills WHERE user_id = :user_id AND skill_type = 'learn')
                WHERE user_id = :user_id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        return $stmt->execute();
    }

    //  Log User Activity
    public function logActivity($userId, $activityType, $description) {
        $sql = "INSERT INTO user_activity (user_id, activity_type, description) 
                VALUES (:user_id, :type, :description)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':type', $activityType);
        $stmt->bindValue(':description', $description);
        return $stmt->execute();
    }

    //  Award Badge to User
    public function awardBadge($userId, $badgeName, $badgeIcon) {
        $sql = "INSERT INTO user_badges (user_id, badge_name, badge_icon) 
                VALUES (:user_id, :name, :icon)";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':name', $badgeName);
        $stmt->bindValue(':icon', $badgeIcon);
        return $stmt->execute();
    }

    //  Get User Activity
    public function getUserActivity($userId) {
        $sql = "SELECT * FROM user_activity 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper: Time Ago
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

    // Check if username exists
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


    //  Create OTP for password reset
    public function createPasswordResetOTP($email) {
        $userSql = "SELECT id FROM users WHERE email = :email AND status != 'suspended'";
        $stmt = $this->connect()->prepare($userSql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return false;

        // Delete any existing OTPs for this user
        $deleteSql = "DELETE FROM password_reset_tokens WHERE user_id = :user_id";
        $stmt = $this->connect()->prepare($deleteSql);
        $stmt->bindValue(':user_id', $user['id']);
        $stmt->execute();

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $insertSql = "INSERT INTO password_reset_tokens (user_id, otp, expires_at, used)
                    VALUES (:user_id, :otp, :expires_at, 0)";
        $stmt = $this->connect()->prepare($insertSql);
        $stmt->bindValue(':user_id', $user['id']);
        $stmt->bindValue(':otp', $otp);
        $stmt->bindValue(':expires_at', $expiresAt);
        $stmt->execute();

        return $otp;
    }

    //  Verify OTP and get user
    public function verifyPasswordResetOTP($email, $otp) {
        $sql = "SELECT prt.id, prt.user_id, prt.expires_at
                FROM password_reset_tokens prt
                JOIN users u ON prt.user_id = u.id
                WHERE u.email = :email
                AND prt.otp = :otp
                AND prt.used = 0
                AND prt.expires_at > NOW()";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':otp', $otp);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //  Reset password and mark OTP as used
    public function resetPasswordByOTP($email, $otp, $newPassword) {
        $token = $this->verifyPasswordResetOTP($email, $otp);
        if (!$token) return false;

        // Update password
        $updateSql = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->connect()->prepare($updateSql);
        $stmt->bindValue(':password', password_hash($newPassword, PASSWORD_BCRYPT));
        $stmt->bindValue(':id', $token['user_id']);
        $stmt->execute();

        // Mark OTP as used
        $markSql = "UPDATE password_reset_tokens SET used = 1 WHERE id = :id";
        $stmt = $this->connect()->prepare($markSql);
        $stmt->bindValue(':id', $token['id']);
        $stmt->execute();

        return true;
    }

}
