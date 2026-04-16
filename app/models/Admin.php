<?php
class Admin {

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // ── Stats ──────────────────────────────────────────────
    public function getAdminStats() {
        try {
            $stats = [];

            $this->db->query("SELECT COUNT(*) AS n FROM users WHERE role != 'admin'");
            $stats['total_users'] = $this->db->single()->n ?? 0;

            $this->db->query("SELECT COUNT(*) AS n FROM users WHERE role != 'admin' AND status = 'active'");
            $stats['active_users'] = $this->db->single()->n ?? 0;

            $this->db->query("SELECT COUNT(*) AS n FROM users WHERE status = 'suspended'");
            $stats['suspended_users'] = $this->db->single()->n ?? 0;

            $this->db->query("SELECT COUNT(*) AS n FROM reports WHERE status = 'pending'");
            $stats['pending_reports'] = $this->db->single()->n ?? 0;

            $this->db->query("SELECT COUNT(*) AS n FROM reports");
            $stats['total_reports'] = $this->db->single()->n ?? 0;

            $this->db->query("SELECT COUNT(*) AS n FROM user_warnings");
            $stats['total_warnings'] = $this->db->single()->n ?? 0;

            return $stats;
        } catch (Exception $e) {
            error_log("getAdminStats: " . $e->getMessage());
            return array_fill_keys([
                'total_users','active_users','suspended_users',
                'pending_reports','total_reports','total_warnings'
            ], 0);
        }
    }

    // ── Users ──────────────────────────────────────────────
    public function getRecentUsers($limit = 5) {
        try {
            $this->db->query(
                "SELECT id, username, email, role, status, created_at, warning_count
                 FROM users WHERE role != 'admin'
                 ORDER BY created_at DESC LIMIT :limit"
            );
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    public function getAllUsers() {
        try {
            $this->db->query(
                "SELECT id, username, email, role, status, created_at, warning_count,
                        suspended_at, suspension_reason, suspension_expires_at
                 FROM users WHERE role != 'admin'
                 ORDER BY created_at DESC"
            );
            return $this->db->resultSet();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    public function getUserById($userId) {
        try {
            $this->db->query("SELECT * FROM users WHERE id = :id");
            $this->db->bind(':id', $userId);
            return $this->db->single();
        } catch (Exception $e) { error_log($e->getMessage()); return null; }
    }

    public function getUserSkills($userId) {
        try {
            $this->db->query(
                "SELECT skill_name, skill_type FROM user_skills WHERE user_id = :uid"
            );
            $this->db->bind(':uid', $userId);
            return $this->db->resultSet();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    public function getUserWarnings($userId) {
        try {
            $this->db->query(
                "SELECT w.*, a.username AS admin_username
                 FROM user_warnings w
                 JOIN users a ON w.admin_id = a.id
                 WHERE w.user_id = :uid
                 ORDER BY w.created_at DESC"
            );
            $this->db->bind(':uid', $userId);
            return $this->db->resultSet();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    public function getReportsAboutUser($userId) {
        try {
            $this->db->query(
                "SELECT ur.reason, ur.description, ur.status, ur.created_at,
                        reporter.username AS reporter_username,
                        p.name AS project_name
                FROM user_reports ur
                JOIN users reporter ON ur.reporter_id = reporter.id
                JOIN projects p ON ur.project_id = p.id
                WHERE ur.reported_user_id = :uid
                ORDER BY ur.created_at DESC"
            );
            $this->db->bind(':uid', $userId);
            return $this->db->resultSet();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    public function getUserActivity($userId, $limit = 15) {
        try {
            $this->db->query(
                "SELECT activity_type, description, created_at
                 FROM user_activity
                 WHERE user_id = :uid
                 ORDER BY created_at DESC LIMIT :limit"
            );
            $this->db->bind(':uid',   $userId);
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    // ── Suspend / Reactivate ───────────────────────────────
    public function suspendUser($userId, $adminId, $reason, $expiresAt = null) {
        try {
            $this->db->query(
                "UPDATE users SET
                    status = 'suspended',
                    suspended_at = NOW(),
                    suspended_by = :admin_id,
                    suspension_reason = :reason,
                    suspension_expires_at = :expires_at
                 WHERE id = :uid"
            );
            $this->db->bind(':admin_id',  $adminId);
            $this->db->bind(':reason',    $reason);
            $this->db->bind(':expires_at', $expiresAt);
            $this->db->bind(':uid',       $userId);
            return $this->db->execute();
        } catch (Exception $e) { error_log($e->getMessage()); return false; }
    }

    public function reactivateUser($userId) {
        try {
            $this->db->query(
                "UPDATE users SET
                    status = 'active',
                    suspended_at = NULL,
                    suspended_by = NULL,
                    suspension_reason = NULL,
                    suspension_expires_at = NULL
                 WHERE id = :uid"
            );
            $this->db->bind(':uid', $userId);
            return $this->db->execute();
        } catch (Exception $e) { error_log($e->getMessage()); return false; }
    }

    // ── Activity Logs ──────────────────────────────────────
    public function getAllUserActivities($limit = 100) {
        try {
            $this->db->query(
                "SELECT ua.activity_type, ua.description, ua.created_at,
                        u.username, u.email
                 FROM user_activity ua
                 JOIN users u ON ua.user_id = u.id
                 ORDER BY ua.created_at DESC LIMIT :limit"
            );
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    public function getAllAdminActions($limit = 100) {
        try {
            $this->db->query(
                "SELECT aa.action_type, aa.description, aa.created_at,
                        a.username AS admin_username,
                        u.username AS target_username
                 FROM admin_actions aa
                 JOIN users a ON aa.admin_id = a.id
                 LEFT JOIN users u ON aa.target_user_id = u.id
                 ORDER BY aa.created_at DESC LIMIT :limit"
            );
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    public function getRecentAdminActions($limit = 5) {
        try {
            $this->db->query(
                "SELECT aa.action_type, aa.description, aa.created_at,
                        a.username AS admin_username
                 FROM admin_actions aa
                 JOIN users a ON aa.admin_id = a.id
                 ORDER BY aa.created_at DESC LIMIT :limit"
            );
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    public function logAdminAction($adminId, $actionType, $targetUserId, $targetType, $targetId, $description, $ipAddress) {
        try {
            $this->db->query(
                "INSERT INTO admin_actions
                    (admin_id, action_type, target_user_id, target_type, target_id, description, ip_address)
                 VALUES
                    (:admin_id, :action_type, :target_user_id, :target_type, :target_id, :description, :ip)"
            );
            $this->db->bind(':admin_id',      $adminId);
            $this->db->bind(':action_type',   $actionType);
            $this->db->bind(':target_user_id', $targetUserId);
            $this->db->bind(':target_type',   $targetType);
            $this->db->bind(':target_id',     $targetId);
            $this->db->bind(':description',   $description);
            $this->db->bind(':ip',            $ipAddress);
            return $this->db->execute();
        } catch (Exception $e) { error_log($e->getMessage()); return false; }
    }

    public function getProjectMemberReports() {
        try {
            $this->db->query(
                "SELECT ur.*,
                        reporter.username AS reporter_name,
                        reported.username AS reported_name,
                        reported.email    AS reported_email,
                        reported.warning_count,
                        p.name            AS project_name
                FROM user_reports ur
                JOIN users reporter ON ur.reporter_id  = reporter.id
                JOIN users reported ON ur.reported_user_id = reported.id
                JOIN projects p     ON ur.project_id   = p.id
                ORDER BY ur.created_at DESC"
            );
            return $this->db->resultSet();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    public function warnUser($userId, $adminId, $reason, $warningType = 'minor') {
        try {
            $this->db->query(
                "INSERT INTO user_warnings (user_id, admin_id, warning_type, reason, message, created_at)
                VALUES (:uid, :admin_id, :type, :reason, :message, NOW())"
            );
            $this->db->bind(':uid',      $userId);
            $this->db->bind(':admin_id', $adminId);
            $this->db->bind(':type',     $warningType);
            $this->db->bind(':reason',   $reason);
            $this->db->bind(':message',  'Warning issued based on project member report: ' . $reason);
            $this->db->execute();

            $this->db->query("UPDATE users SET warning_count = warning_count + 1 WHERE id = :uid");
            $this->db->bind(':uid', $userId);
            return $this->db->execute();
        } catch (Exception $e) { error_log($e->getMessage()); return false; }
    }
    public function updateProjectReport($reportId, $status) {
        try {
            $this->db->query("UPDATE user_reports SET status = :status WHERE id = :id");
            $this->db->bind(':status', $status);
            $this->db->bind(':id',     $reportId);
            return $this->db->execute();
        } catch (Exception $e) { error_log($e->getMessage()); return false; }
    }
}