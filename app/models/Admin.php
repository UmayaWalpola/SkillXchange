<?php

class Admin {
    
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }

    // ========================================
    // STATISTICS & DASHBOARD
    // ========================================

    /**
     * Get admin dashboard statistics
     */
    public function getAdminStats() {
        try {
            // Total users (exclude admins)
            $this->db->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
            $result = $this->db->single();
            $totalUsers = $result->total ?? 0;
            
            // Active users
            $this->db->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin' AND status = 'active'");
            $result = $this->db->single();
            $activeUsers = $result->total ?? 0;
            
            // Suspended users
            $this->db->query("SELECT COUNT(*) as total FROM users WHERE status = 'suspended'");
            $result = $this->db->single();
            $suspendedUsers = $result->total ?? 0;
            
            // Pending reports
            $this->db->query("SELECT COUNT(*) as total FROM reports WHERE status = 'pending'");
            $result = $this->db->single();
            $pendingReports = $result->total ?? 0;
            
            // Total reports
            $this->db->query("SELECT COUNT(*) as total FROM reports");
            $result = $this->db->single();
            $totalReports = $result->total ?? 0;
            
            // Total unique skills
            $this->db->query("SELECT COUNT(DISTINCT skill_name) as total FROM user_skills");
            $result = $this->db->single();
            $totalSkills = $result->total ?? 0;
            
            // Total warnings issued
            $this->db->query("SELECT COUNT(*) as total FROM user_warnings");
            $result = $this->db->single();
            $totalWarnings = $result->total ?? 0;

            return [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'suspended_users' => $suspendedUsers,
                'pending_reports' => $pendingReports,
                'total_reports' => $totalReports,
                'total_skills' => $totalSkills,
                'total_warnings' => $totalWarnings
            ];
        } catch (Exception $e) {
            error_log("Admin stats error: " . $e->getMessage());
            return [
                'total_users' => 0,
                'active_users' => 0,
                'suspended_users' => 0,
                'pending_reports' => 0,
                'total_reports' => 0,
                'total_skills' => 0,
                'total_warnings' => 0
            ];
        }
    }

    /**
     * Get recent users
     */
    public function getRecentUsers($limit = 10) {
        try {
            $this->db->query("SELECT id, username, email, created_at, role, status, warning_count 
                       FROM users 
                       WHERE role != 'admin' 
                       ORDER BY created_at DESC 
                       LIMIT :limit");
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Recent users error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get popular skills
     */
    public function getPopularSkills($limit = 6) {
        try {
            $this->db->query("SELECT 
                           skill_name,
                           SUM(CASE WHEN skill_type = 'teach' THEN 1 ELSE 0 END) as teachers,
                           SUM(CASE WHEN skill_type = 'learn' THEN 1 ELSE 0 END) as learners,
                           COUNT(*) as total
                       FROM user_skills 
                       GROUP BY skill_name 
                       ORDER BY total DESC 
                       LIMIT :limit");
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Popular skills error: " . $e->getMessage());
            return [];
        }
    }

    // ========================================
    // USER MANAGEMENT
    // ========================================

    /**
     * Get all users (excluding admins)
     */
    public function getAllUsers() {
        try {
            $this->db->query("SELECT id, username, email, created_at, role, profile_completed, status, warning_count,
                       suspended_at, suspension_reason, suspension_expires_at
                       FROM users 
                       WHERE role != 'admin'");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Get all users error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        try {
            $this->db->query("SELECT * FROM users WHERE id = :id");
            $this->db->bind(':id', $userId);
            return $this->db->single();
        } catch (Exception $e) {
            error_log("Get user by ID error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user's skills
     */
    public function getUserSkills($userId) {
        try {
            $this->db->query("SELECT skill_name, skill_type FROM user_skills WHERE user_id = :user_id");
            $this->db->bind(':user_id', $userId);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Get user skills error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user's warnings
     */
    public function getUserWarnings($userId) {
        try {
            $this->db->query("SELECT w.*, a.username as admin_username 
                       FROM user_warnings w 
                       JOIN users a ON w.admin_id = a.id 
                       WHERE w.user_id = :user_id 
                       ORDER BY w.created_at DESC");
            $this->db->bind(':user_id', $userId);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Get user warnings error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get reports about a user
     */
    public function getReportsAboutUser($userId) {
        try {
            $this->db->query("SELECT r.*, u.username as reporter_username 
                       FROM reports r 
                       JOIN users u ON r.reporter_user_id = u.id 
                       WHERE r.reported_user_id = :user_id 
                       ORDER BY r.created_at DESC");
            $this->db->bind(':user_id', $userId);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Get reports about user error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user activity
     */
    public function getUserActivity($userId, $limit = 20) {
        try {
            $this->db->query("SELECT * FROM user_activity 
                       WHERE user_id = :user_id 
                       ORDER BY created_at DESC 
                       LIMIT :limit");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Get user activity error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Suspend user
     */
    public function suspendUser($userId, $adminId, $reason, $expiresAt = null) {
        try {
            $this->db->query("UPDATE users SET 
                       status = 'suspended',
                       suspended_at = NOW(),
                       suspended_by = :admin_id,
                       suspension_reason = :reason,
                       suspension_expires_at = :expires_at
                       WHERE id = :user_id");
            $this->db->bind(':admin_id', $adminId);
            $this->db->bind(':reason', $reason);
            $this->db->bind(':expires_at', $expiresAt);
            $this->db->bind(':user_id', $userId);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Suspend user error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Activate user (remove suspension)
     */
    public function activateUser($userId) {
        try {
            $this->db->query("UPDATE users SET 
                       status = 'active',
                       suspended_at = NULL,
                       suspended_by = NULL,
                       suspension_reason = NULL,
                       suspension_expires_at = NULL
                       WHERE id = :user_id");
            $this->db->bind(':user_id', $userId);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Activate user error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user permanently
     */
    public function deleteUser($userId) {
        try {
            $this->db->query("DELETE FROM users WHERE id = :user_id");
            $this->db->bind(':user_id', $userId);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Delete user error: " . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // REPORTS MANAGEMENT
    // ========================================

    /**
     * Get all reports
     */
    public function getAllReports() {
        try {
            $this->db->query("SELECT r.*, 
                       ru.username as reported_username,
                       rep.username as reporter_username,
                       res.username as resolved_by_username
                       FROM reports r
                       JOIN users ru ON r.reported_user_id = ru.id
                       JOIN users rep ON r.reporter_user_id = rep.id
                       LEFT JOIN users res ON r.resolved_by = res.id
                       ORDER BY 
                           CASE r.status
                               WHEN 'pending' THEN 1
                               WHEN 'reviewed' THEN 2
                               WHEN 'resolved' THEN 3
                               WHEN 'dismissed' THEN 4
                           END,
                           r.created_at DESC");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Get all reports error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent pending reports
     */
    public function getRecentReports($limit = 5) {
        try {
            $this->db->query("SELECT r.*, 
                       ru.username as reported_username,
                       rep.username as reporter_username
                       FROM reports r
                       JOIN users ru ON r.reported_user_id = ru.id
                       JOIN users rep ON r.reporter_user_id = rep.id
                       WHERE r.status = 'pending'
                       ORDER BY r.created_at DESC
                       LIMIT :limit");
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Get recent reports error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get report by ID
     */
    public function getReportById($reportId) {
        try {
            $this->db->query("SELECT r.*, 
                       ru.username as reported_username, ru.email as reported_email,
                       rep.username as reporter_username, rep.email as reporter_email,
                       res.username as resolved_by_username
                       FROM reports r
                       JOIN users ru ON r.reported_user_id = ru.id
                       JOIN users rep ON r.reporter_user_id = rep.id
                       LEFT JOIN users res ON r.resolved_by = res.id
                       WHERE r.id = :id");
            $this->db->bind(':id', $reportId);
            return $this->db->single();
        } catch (Exception $e) {
            error_log("Get report by ID error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get other reports about the same user
     */
    public function getOtherReportsAboutUser($userId, $excludeReportId) {
        try {
            $this->db->query("SELECT r.*, rep.username as reporter_username 
                       FROM reports r 
                       JOIN users rep ON r.reporter_user_id = rep.id 
                       WHERE r.reported_user_id = :user_id AND r.id != :report_id 
                       ORDER BY r.created_at DESC 
                       LIMIT 5");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':report_id', $excludeReportId);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Get other reports error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Resolve report
     */
    public function resolveReport($reportId, $adminId, $adminNotes, $actionTaken) {
        try {
            $this->db->query("UPDATE reports SET 
                       status = 'resolved',
                       admin_notes = :admin_notes,
                       action_taken = :action_taken,
                       resolved_by = :admin_id,
                       resolved_at = NOW()
                       WHERE id = :report_id");
            $this->db->bind(':admin_notes', $adminNotes);
            $this->db->bind(':action_taken', $actionTaken);
            $this->db->bind(':admin_id', $adminId);
            $this->db->bind(':report_id', $reportId);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Resolve report error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dismiss report
     */
    public function dismissReport($reportId, $adminId, $adminNotes) {
        try {
            $this->db->query("UPDATE reports SET 
                       status = 'dismissed',
                       admin_notes = :admin_notes,
                       resolved_by = :admin_id,
                       resolved_at = NOW()
                       WHERE id = :report_id");
            $this->db->bind(':admin_notes', $adminNotes);
            $this->db->bind(':admin_id', $adminId);
            $this->db->bind(':report_id', $reportId);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Dismiss report error: " . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // WARNINGS MANAGEMENT
    // ========================================

    /**
     * Send warning to user
     */
    public function sendWarning($userId, $adminId, $reportId, $warningType, $reason, $message) {
        try {
            // Insert warning
            $this->db->query("INSERT INTO user_warnings (user_id, admin_id, report_id, warning_type, reason, message) 
                       VALUES (:user_id, :admin_id, :report_id, :warning_type, :reason, :message)");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':admin_id', $adminId);
            $this->db->bind(':report_id', $reportId);
            $this->db->bind(':warning_type', $warningType);
            $this->db->bind(':reason', $reason);
            $this->db->bind(':message', $message);
            
            if (!$this->db->execute()) {
                return false;
            }

            $warningId = $this->db->lastInsertId();

            // Update user warning count
            $this->db->query("UPDATE users SET warning_count = warning_count + 1 WHERE id = :user_id");
            $this->db->bind(':user_id', $userId);
            $this->db->execute();

            return $warningId;
        } catch (Exception $e) {
            error_log("Send warning error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update report status after warning
     */
    public function updateReportAfterWarning($reportId, $adminId) {
        try {
            $this->db->query("UPDATE reports SET 
                       status = 'resolved',
                       action_taken = 'warning',
                       resolved_by = :admin_id,
                       resolved_at = NOW()
                       WHERE id = :report_id");
            $this->db->bind(':admin_id', $adminId);
            $this->db->bind(':report_id', $reportId);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Update report after warning error: " . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // ACTIVITY LOGS
    // ========================================

    /**
     * Get all user activities
     */
    public function getAllUserActivities($limit = 100) {
        try {
            $this->db->query("SELECT ua.*, u.username, u.email 
                       FROM user_activity ua
                       JOIN users u ON ua.user_id = u.id
                       ORDER BY ua.created_at DESC
                       LIMIT :limit");
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Get all user activities error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all admin actions
     */
    public function getAllAdminActions($limit = 100) {
        try {
            $this->db->query("SELECT aa.*, 
                       a.username as admin_username,
                       u.username as target_username
                       FROM admin_actions aa
                       JOIN users a ON aa.admin_id = a.id
                       LEFT JOIN users u ON aa.target_user_id = u.id
                       ORDER BY aa.created_at DESC
                       LIMIT :limit");
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Get all admin actions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent admin actions
     */
    public function getRecentAdminActions($limit = 5) {
        try {
            $this->db->query("SELECT aa.*, a.username as admin_username
                       FROM admin_actions aa
                       JOIN users a ON aa.admin_id = a.id
                       ORDER BY aa.created_at DESC
                       LIMIT :limit");
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Get recent admin actions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Log admin action
     */
    public function logAdminAction($adminId, $actionType, $targetUserId, $targetType, $targetId, $description, $ipAddress) {
        try {
            $this->db->query("INSERT INTO admin_actions 
                       (admin_id, action_type, target_user_id, target_type, target_id, description, ip_address) 
                       VALUES (:admin_id, :action_type, :target_user_id, :target_type, :target_id, :description, :ip)");
            $this->db->bind(':admin_id', $adminId);
            $this->db->bind(':action_type', $actionType);
            $this->db->bind(':target_user_id', $targetUserId);
            $this->db->bind(':target_type', $targetType);
            $this->db->bind(':target_id', $targetId);
            $this->db->bind(':description', $description);
            $this->db->bind(':ip', $ipAddress);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Log admin action error: " . $e->getMessage());
            return false;
        }
    }
}