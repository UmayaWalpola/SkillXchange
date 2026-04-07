<?php

class Manager {

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // ORGANIZATIONS

    // Get all organizations with wallet balance and project count
    public function getAllOrganizations() {
        $this->db->query("
            SELECT 
                u.id,
                u.username AS name,
                u.email,
                u.status,
                u.created_at,
                u.suspended_at,
                u.suspension_reason,
                u.org_cert,
                COALESCE(w.balance, 0) AS wallet_balance,
                COUNT(DISTINCT p.id) AS project_count
            FROM users u
            LEFT JOIN wallets w ON w.user_id = u.id
            LEFT JOIN projects p ON p.organization_id = u.id
            WHERE u.role = 'organization'
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ");
        return $this->db->resultSet();
    }

    // Get a single organization by ID
    public function getOrganizationById($orgId) {
        $this->db->query("
            SELECT 
                u.id,
                u.username AS name,
                u.email,
                u.status,
                u.created_at,
                u.suspended_at,
                u.suspension_reason,
                u.org_cert,
                COALESCE(w.balance, 0) AS wallet_balance,
                COUNT(DISTINCT p.id) AS project_count
            FROM users u
            LEFT JOIN wallets w ON w.user_id = u.id
            LEFT JOIN projects p ON p.organization_id = u.id
            WHERE u.id = :id AND u.role = 'organization'
            GROUP BY u.id
        ");
        $this->db->bind(':id', $orgId);
        return $this->db->single();
    }

    // Suspend an organization
    public function suspendOrganization($orgId, $managerId, $reason) {
        $this->db->query("
            UPDATE users 
            SET 
                status = 'suspended',
                suspended_at = NOW(),
                suspended_by = :manager_id,
                suspension_reason = :reason
            WHERE id = :id AND role = 'organization'
        ");
        $this->db->bind(':id', $orgId);
        $this->db->bind(':manager_id', $managerId);
        $this->db->bind(':reason', $reason);
        return $this->db->execute();
    }

    // Reactivate a suspended organization
    public function reactivateOrganization($orgId) {
        $this->db->query("
            UPDATE users 
            SET 
                status = 'active',
                suspended_at = NULL,
                suspended_by = NULL,
                suspension_reason = NULL
            WHERE id = :id AND role = 'organization'
        ");
        $this->db->bind(':id', $orgId);
        return $this->db->execute();
    }

    // USERS (admin, quiz_manager, manager, community_admin)
    // Get all admin-type users
    public function getAllAdminUsers() {
        $this->db->query("
            SELECT id, username AS name, email, role, created_at 
            FROM users 
            WHERE role IN ('admin', 'quiz_manager', 'manager', 'community_admin')
            ORDER BY created_at DESC
        ");
        return $this->db->resultSet();
    }

    // Add a new admin user
    public function addUser($name, $email, $role, $password) {
        // Check if email already exists
        $this->db->query("SELECT id FROM users WHERE email = :email");
        $this->db->bind(':email', $email);
        if ($this->db->single()) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $this->db->query("
            INSERT INTO users (username, email, password, role, profile_completed, created_at) 
            VALUES (:username, :email, :password, :role, 1, NOW())
        ");
        $this->db->bind(':username', $name);
        $this->db->bind(':email', $email);
        $this->db->bind(':password', $hashedPassword);
        $this->db->bind(':role', $role);

        if ($this->db->execute()) {
            return ['success' => true, 'message' => 'User added successfully'];
        }
        return ['success' => false, 'message' => 'Failed to add user'];
    }

    // Update an existing user
    public function updateUser($userId, $name, $email, $role, $password = '') {
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $this->db->query("
                UPDATE users 
                SET username = :username, email = :email, role = :role, password = :password 
                WHERE id = :id
            ");
            $this->db->bind(':password', $hashedPassword);
        } else {
            $this->db->query("
                UPDATE users 
                SET username = :username, email = :email, role = :role 
                WHERE id = :id
            ");
        }

        $this->db->bind(':username', $name);
        $this->db->bind(':email', $email);
        $this->db->bind(':role', $role);
        $this->db->bind(':id', $userId);

        if ($this->db->execute()) {
            return ['success' => true, 'message' => 'User updated successfully'];
        }
        return ['success' => false, 'message' => 'Failed to update user'];
    }

    // Remove a user
    public function removeUser($userId) {
        $this->db->query("DELETE FROM users WHERE id = :id");
        $this->db->bind(':id', $userId);

        if ($this->db->execute()) {
            return ['success' => true, 'message' => 'User removed successfully'];
        }
        return ['success' => false, 'message' => 'Failed to remove user'];
    }


    // DASHBOARD STATS
    public function getDashboardStats() {
        // Total organizations
        $this->db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'organization'");
        $orgs = $this->db->single();

        // Total regular users
        $this->db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'individual'");
        $users = $this->db->single();

        // Total admin users
        $this->db->query("SELECT COUNT(*) AS total FROM users WHERE role IN ('admin', 'quiz_manager', 'manager', 'community_admin')");
        $admins = $this->db->single();

        return [
            'total_organizations' => $orgs->total ?? 0,
            'total_users'         => $users->total ?? 0,
            'total_admins'        => $admins->total ?? 0,
            'total_announcements' => 0
        ];
    }

    
    // ANNOUNCEMENTS
    // Get all announcements with author name
    public function getAllAnnouncements() {
        $this->db->query("
            SELECT a.id, a.title, a.content, a.created_at,
                u.username AS author
            FROM announcements a
            JOIN users u ON u.id = a.created_by
            ORDER BY a.created_at DESC
        ");
        return $this->db->resultSet();
    }

    // Add a new announcement and return its new ID
    public function addAnnouncement($title, $content, $managerId) {
        $this->db->query("
            INSERT INTO announcements (title, content, created_by, created_at)
            VALUES (:title, :content, :created_by, NOW())
        ");
        $this->db->bind(':title', $title);
        $this->db->bind(':content', $content);
        $this->db->bind(':created_by', $managerId);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateAnnouncement($announcementId, $title, $content) {
        $this->db->query("
            UPDATE announcements 
            SET title = :title, content = :content 
            WHERE id = :id
        ");
        $this->db->bind(':title', $title);
        $this->db->bind(':content', $content);
        $this->db->bind(':id', $announcementId);
        return $this->db->execute();
    }

    public function removeAnnouncement($announcementId) {
        $this->db->query("DELETE FROM announcements WHERE id = :id");
        $this->db->bind(':id', $announcementId);
        return $this->db->execute();
    }
    // Get all active user IDs except the manager posting
    public function getAllUserIds($excludeId) {
        $this->db->query("
            SELECT id FROM users 
            WHERE id != :exclude_id
            AND status = 'active'
        ");
        $this->db->bind(':exclude_id', $excludeId);
        return $this->db->resultSet();
    }

    // Send a notification to one user
    public function sendNotification($userId, $title, $message, $announcementId = null) {
        try {
            $this->db->query("
                INSERT INTO notifications (user_id, type, title, message, is_read, created_at, announcement_id)
                VALUES (:user_id, 'system_announcement', :title, :message, 0, NOW(), :announcement_id)
            ");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':title', $title);
            $this->db->bind(':message', $message);
            $this->db->bind(':announcement_id', $announcementId);
            return $this->db->execute();
        } catch (PDOException $e) {
            // Backward-compatible fallback if the DB doesn't have the new columns yet.
            $this->db->query("
                INSERT INTO notifications (user_id, type, message, is_read, created_at)
                VALUES (:user_id, 'system_announcement', :message, 0, NOW())
            ");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':message', $message);
            return $this->db->execute();
        }
    }

    public function updateAnnouncementNotifications($announcementId, $title, $message, $excludeUserId = 0) {
        try {
            $this->db->query("
                UPDATE notifications
                SET title = :title, message = :message
                WHERE type = 'system_announcement'
                  AND announcement_id = :announcement_id
                  AND user_id != :exclude_user_id
            ");
            $this->db->bind(':title', $title);
            $this->db->bind(':message', $message);
            $this->db->bind(':announcement_id', $announcementId);
            $this->db->bind(':exclude_user_id', (int)$excludeUserId);
            return $this->db->execute();
        } catch (PDOException $e) {
            // If announcement_id/title doesn't exist yet, we can't reliably update legacy notifications.
            return false;
        }
    }

    public function removeAnnouncementNotifications($announcementId, $excludeUserId = 0) {
        try {
            $this->db->query("
                DELETE FROM notifications
                WHERE type = 'system_announcement'
                  AND announcement_id = :announcement_id
                  AND user_id != :exclude_user_id
            ");
            $this->db->bind(':announcement_id', $announcementId);
            $this->db->bind(':exclude_user_id', (int)$excludeUserId);
            return $this->db->execute();
        } catch (PDOException $e) {
            return false;
        }
    }


    // FEEDBACK

    // Submit platform feedback from a user (no rating)
    public function submitPlatformFeedback($userId, $subject, $message) {
        $this->db->query("\n            INSERT INTO platform_feedback (user_id, subject, message, status, created_at)\n            VALUES (:user_id, :subject, :message, :status, NOW())\n        ");
        $this->db->bind(':user_id', (int)$userId);
        $this->db->bind(':subject', $subject);
        $this->db->bind(':message', $message);
        $this->db->bind(':status', 'new');
        return $this->db->execute();
    }

    // Get all platform feedback with user details
    public function getAllFeedbacks() {
        $this->db->query("
            SELECT 
                f.id,
                f.subject,
                f.message,
                f.status,
                f.created_at,
                u.username AS user_name,
                u.email AS user_email
            FROM platform_feedback f
            JOIN users u ON u.id = f.user_id
            ORDER BY f.created_at DESC
        ");
        return $this->db->resultSet();
    }

    // =========================================================
// USER INSIGHTS
// =========================================================

public function getUserInsights() {
    // Total individual users
    $this->db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'individual'");
    $totalUsers = $this->db->single();

    // Total organizations
    $this->db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'organization'");
    $totalOrgs = $this->db->single();

    // Suspended users
    $this->db->query("SELECT COUNT(*) AS total FROM users WHERE status = 'suspended'");
    $suspended = $this->db->single();

    // New users this month
    $this->db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'individual' AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
    $newThisMonth = $this->db->single();

    // Total completed BuckX purchases
    $this->db->query("SELECT COUNT(*) AS total FROM buckx_purchases WHERE status = 'completed'");
    $totalPurchases = $this->db->single();

    // Total BuckX purchased
    $this->db->query("SELECT COALESCE(SUM(buckx_amount), 0) AS total FROM buckx_purchases WHERE status = 'completed'");
    $totalBuckx = $this->db->single();

    // Total revenue in LKR
    $this->db->query("SELECT COALESCE(SUM(price_lkr), 0) AS total FROM buckx_purchases WHERE status = 'completed'");
    $totalRevenue = $this->db->single();

    // Total quiz attempts
    $this->db->query("SELECT COUNT(*) AS total FROM user_quiz_attempts");
    $totalAttempts = $this->db->single();

    // Completed quiz attempts
    $this->db->query("SELECT COUNT(*) AS total FROM user_quiz_attempts WHERE status = 'completed'");
    $completedAttempts = $this->db->single();

    // Most attempted quiz
    $this->db->query("
        SELECT q.title, COUNT(a.id) AS attempt_count
        FROM user_quiz_attempts a
        JOIN quizzes q ON q.id = a.quiz_id
        GROUP BY a.quiz_id
        ORDER BY attempt_count DESC
        LIMIT 1
    ");
    $mostAttempted = $this->db->single();

    // Total skill exchanges
    $this->db->query("SELECT COUNT(*) AS total FROM exchanges");
    $totalExchanges = $this->db->single();

    // Accepted exchanges
    $this->db->query("SELECT COUNT(*) AS total FROM exchanges WHERE status = 'accepted'");
    $acceptedExchanges = $this->db->single();

    // Pending exchanges
    $this->db->query("SELECT COUNT(*) AS total FROM exchanges WHERE status = 'pending'");
    $pendingExchanges = $this->db->single();

    // Total communities
    $this->db->query("SELECT COUNT(*) AS total FROM communities");
    $totalCommunities = $this->db->single();

    // Total community members
    $this->db->query("SELECT COUNT(*) AS total FROM community_members");
    $totalCommunityMembers = $this->db->single();

    return [
        'users' => [
            'total'         => $totalUsers->total ?? 0,
            'organizations' => $totalOrgs->total ?? 0,
            'suspended'     => $suspended->total ?? 0,
            'new_this_month'=> $newThisMonth->total ?? 0
        ],
        'buckx' => [
            'total_purchases' => $totalPurchases->total ?? 0,
            'total_buckx'     => $totalBuckx->total ?? 0,
            'total_revenue'   => $totalRevenue->total ?? 0
        ],
        'quizzes' => [
            'total_attempts'     => $totalAttempts->total ?? 0,
            'completed_attempts' => $completedAttempts->total ?? 0,
            'most_attempted'     => $mostAttempted->title ?? 'N/A',
            'most_attempted_count' => $mostAttempted->attempt_count ?? 0
        ],
        'exchanges' => [
            'total'    => $totalExchanges->total ?? 0,
            'accepted' => $acceptedExchanges->total ?? 0,
            'pending'  => $pendingExchanges->total ?? 0
        ],
        'communities' => [
            'total'   => $totalCommunities->total ?? 0,
            'members' => $totalCommunityMembers->total ?? 0
        ]
    ];
}

}