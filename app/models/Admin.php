<?php
class Admin {

    private $db;
    private $userColumns = null;
    private $adminActionColumns = null;
    private $tables = null;

    public function __construct() {
        $this->db = new Database();
    }

    // stat cards
    public function getAdminStats() {
        return [
            'total_users' => $this->safeCount("
                SELECT COUNT(*) AS n
                FROM users
                WHERE role NOT IN ('admin', 'quiz_manager', 'community_admin', 'manager')
            "),
            'active_users' => $this->safeCount("
                SELECT COUNT(*) AS n
                FROM users
                WHERE role NOT IN ('admin', 'quiz_manager', 'community_admin', 'manager')
                  AND COALESCE(status, 'active') = 'active'
            "),
            'suspended_users' => $this->safeCount("
                SELECT COUNT(*) AS n
                FROM users
                WHERE role NOT IN ('admin', 'quiz_manager', 'community_admin', 'manager')
                  AND status = 'suspended'
            "),
            'pending_reports' => $this->safeCount("
                SELECT
                    COALESCE((SELECT COUNT(*) FROM user_reports WHERE status = 'pending'), 0) +
                    COALESCE((SELECT COUNT(*) FROM feedback_reports WHERE status = 'pending'), 0) AS n
            "),
            'total_reports' => $this->safeCount("
                SELECT
                    COALESCE((SELECT COUNT(*) FROM user_reports), 0) +
                    COALESCE((SELECT COUNT(*) FROM feedback_reports), 0) AS n
            "),
            'total_warnings' => $this->safeCount("
                SELECT COUNT(*) AS n
                FROM user_warnings
            "),
        ];
    }

    private function safeCount($sql) {
        try {
            $this->db->query($sql);
            $row = $this->db->single();
            return (int)($row->n ?? 0);
        } catch (Exception $e) {
            error_log("Admin stats query failed: " . $e->getMessage());
            return 0;
        }
    }

    // Get all manageable users for admin list (exclude only system admins)
    public function getAllNonStaffUsers() {
        try {
            $warningCountSelect = $this->hasUserColumn('warning_count')
                ? 'warning_count'
                : '0 AS warning_count';

            $this->db->query(
                "SELECT id,
                        username,
                        email,
                        role,
                        COALESCE(status, 'active') AS status,
                        created_at,
                        {$warningCountSelect}
                 FROM users 
                 WHERE role != 'admin'
                 ORDER BY created_at DESC"
            );
            return $this->db->resultSet();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    private function hasUserColumn($columnName) {
        if ($this->userColumns === null) {
            try {
                $this->db->query('SHOW COLUMNS FROM users');
                $rows = $this->db->resultSet();
                $this->userColumns = [];
                foreach ($rows as $row) {
                    if (isset($row->Field)) {
                        $this->userColumns[] = $row->Field;
                    }
                }
            } catch (Exception $e) {
                error_log('Unable to inspect users columns: ' . $e->getMessage());
                $this->userColumns = [];
            }
        }

        return in_array($columnName, $this->userColumns, true);
    }

    private function hasTable($tableName) {
        if ($this->tables === null) {
            try {
                $this->db->query('SHOW TABLES');
                $rows = $this->db->resultSet();
                $this->tables = [];

                foreach ($rows as $row) {
                    $values = array_values((array) $row);
                    if (!empty($values[0])) {
                        $this->tables[] = $values[0];
                    }
                }
            } catch (Exception $e) {
                error_log('Unable to inspect database tables: ' . $e->getMessage());
                $this->tables = [];
            }
        }

        return in_array($tableName, $this->tables, true);
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
                "SELECT ur.reason,  ur.details, ur.status, ur.reported_at AS created_at,
                        reporter.username AS reporter_username,
                        p.name AS project_name
                FROM user_reports ur
                JOIN users reporter ON ur.reporter_org_id = reporter.id
                JOIN projects p ON ur.project_id = p.id
                WHERE ur.reported_user_id = :uid
                ORDER BY ur.reported_at DESC"
            );
            $this->db->bind(':uid', $userId);
            return $this->db->resultSet();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    public function getUserActivity($userId, $limit = 5) {
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

    //suspend/reactivate users
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

    // Activity Logs 
    public function getAllUserActivities($limit = 25) {
        try {
            $this->db->query(
                "SELECT ua.activity_type, ua.description, ua.created_at,
                        COALESCE(u.username, 'Deleted User') AS username,
                        COALESCE(u.email, '') AS email
                 FROM user_activity ua
                 LEFT JOIN users u ON ua.user_id = u.id
                 ORDER BY ua.created_at DESC LIMIT :limit"
            );
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    public function getAllAdminActions($limit = 25) {
        try {
            if (!$this->hasTable('admin_actions')) {
                return [];
            }

            $targetUserSelect = $this->hasAdminActionColumn('target_user_id')
                ? 'u.username AS target_username'
                : 'NULL AS target_username';
            $targetUserJoin = $this->hasAdminActionColumn('target_user_id')
                ? 'LEFT JOIN users u ON aa.target_user_id = u.id'
                : '';

            $this->db->query(
                "SELECT aa.action_type, aa.description, aa.created_at,
                        COALESCE(a.username, 'Deleted Admin') AS admin_username,
                        {$targetUserSelect}
                 FROM admin_actions aa
                 LEFT JOIN users a ON aa.admin_id = a.id
                 {$targetUserJoin}
                 ORDER BY aa.created_at DESC LIMIT :limit"
            );
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) { error_log($e->getMessage()); return []; }
    }

    public function logAdminAction($adminId, $actionType, $targetUserId, $targetType, $targetId, $description, $ipAddress) {
        try {
            if (!$this->hasTable('admin_actions')) {
                return false;
            }

            $fieldMap = [
                'admin_id' => $adminId,
                'action_type' => $actionType,
                'target_user_id' => $targetUserId,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'description' => $description,
                'ip_address' => $ipAddress,
            ];

            $columns = [];
            $placeholders = [];
            foreach ($fieldMap as $column => $value) {
                if ($this->hasAdminActionColumn($column)) {
                    $columns[] = $column;
                    $placeholders[] = ':' . $column;
                }
            }

            $this->db->query(
                "INSERT INTO admin_actions
                    (" . implode(', ', $columns) . ")
                 VALUES
                    (" . implode(', ', $placeholders) . ")"
            );

            foreach ($columns as $column) {
                $this->db->bind(':' . $column, $fieldMap[$column]);
            }

            return $this->db->execute();
        } catch (Exception $e) { error_log($e->getMessage()); return false; }
    }

    private function hasAdminActionColumn($columnName) {
        if (!$this->hasTable('admin_actions')) {
            return false;
        }

        if ($this->adminActionColumns === null) {
            try {
                $this->db->query('SHOW COLUMNS FROM admin_actions');
                $rows = $this->db->resultSet();
                $this->adminActionColumns = [];
                foreach ($rows as $row) {
                    if (isset($row->Field)) {
                        $this->adminActionColumns[] = $row->Field;
                    }
                }
            } catch (Exception $e) {
                error_log('Unable to inspect admin_actions columns: ' . $e->getMessage());
                $this->adminActionColumns = [];
            }
        }

        return in_array($columnName, $this->adminActionColumns, true);
    }

    public function getProjectMemberReports() {
        try {
            $this->db->query(
                "SELECT ur.id,
                        ur.project_id,
                        ur.reported_user_id,
                        ur.reporter_org_id,
                        ur.reason,
                        ur.details AS description,
                        ur.status,
                        ur.reported_at,
                        reporter.username AS reporter_name,
                        reported.username AS reported_name,
                        reported.email    AS reported_email,
                        reported.warning_count,
                        p.name            AS project_name
                FROM user_reports ur
                LEFT JOIN users reporter ON ur.reporter_org_id  = reporter.id
                LEFT JOIN users reported ON ur.reported_user_id = reported.id
                LEFT JOIN projects p     ON ur.project_id   = p.id
                ORDER BY ur.reported_at DESC"
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
