<?php
// app/models/Task.php

class Task {
    private $db;
    private static $metricsCache = [];
    private static $cacheTimeout = 300; // 5 minutes cache

    public function __construct() {
        $this->db = new Database();
    }

    // Clear cache for a specific project
    private function clearProjectCache($projectId) {
        if (isset(self::$metricsCache[$projectId])) {
            unset(self::$metricsCache[$projectId]);
        }
    }

    // Check if cache is valid
    private function isCacheValid($projectId) {
        if (!isset(self::$metricsCache[$projectId])) {
            return false;
        }
        $cacheAge = time() - self::$metricsCache[$projectId]['timestamp'];
        return $cacheAge < self::$cacheTimeout;
    }

    /* ============================================================
       CREATE / READ / UPDATE / DELETE
    ============================================================ */

    // Requested API: createTask($data)
    public function createTask($data) {
        $this->db->query("INSERT INTO project_tasks (project_id, assigned_to, title, description, status, priority, deadline) VALUES (:project_id, :assigned_to, :title, :description, :status, :priority, :deadline)");
        $this->db->bind(':project_id', $data['project_id']);
        $this->db->bind(':assigned_to', $data['assigned_to'] ?? null);
        $this->db->bind(':title', $data['task_name'] ?? $data['title'] ?? '');
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'todo');
        $this->db->bind(':priority', $data['priority'] ?? 'medium');
        $this->db->bind(':deadline', $data['due_date'] ?? $data['deadline'] ?? null);
        
        if ($this->db->execute()) {
            $taskId = $this->db->lastInsertId();
            // Clear cache for this project
            $this->clearProjectCache($data['project_id']);
            // Log history
            $this->logHistory($taskId, $data['created_by'] ?? null, 'created');
            return $taskId;
        }
        return false;
    }

    // Backwards-compatible wrapper for existing code
    public function getTasksByProjectId($projectId) {
        $this->db->query("SELECT pt.*, u.username, u.profile_picture FROM project_tasks pt LEFT JOIN users u ON pt.assigned_to = u.id WHERE pt.project_id = :project_id ORDER BY pt.deadline ASC, pt.priority DESC, pt.created_at DESC");
        $this->db->bind(':project_id', $projectId);
        return $this->db->resultSet();
    }

    // Alias for older callers
    public function getTasksByProject($projectId) {
        return $this->getTasksByProjectId($projectId);
    }

    public function getTaskById($taskId) {
        $this->db->query("SELECT pt.*, u.username, u.email, u.profile_picture FROM project_tasks pt LEFT JOIN users u ON pt.assigned_to = u.id WHERE pt.id = :id");
        $this->db->bind(':id', $taskId);
        return $this->db->single();
    }

    // Requested API: updateTask($data) – keep old signature for compatibility
    public function updateTask($taskId, $data) {
        $this->db->query("UPDATE project_tasks SET title = :title, description = :description, assigned_to = :assigned_to, priority = :priority, deadline = :deadline, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $this->db->bind(':id', $taskId);
        $this->db->bind(':title', $data['task_name'] ?? $data['title'] ?? '');
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':assigned_to', $data['assigned_to'] ?? null);
        $this->db->bind(':priority', $data['priority'] ?? 'medium');
        $this->db->bind(':deadline', $data['due_date'] ?? $data['deadline'] ?? null);
        
        if ($this->db->execute()) {
            $this->logHistory($taskId, $data['updated_by'] ?? null, 'updated');
            return true;
        }
        return false;
    }

    public function updateTaskStatus($taskId, $status) {
        // Validate status
        $validStatuses = ['todo', 'in-progress', 'done'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        // Get project ID to clear cache
        $projectId = $this->getProjectIdForTask($taskId);

        $this->db->query("UPDATE project_tasks SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $taskId);
        
        if ($this->db->execute()) {
            // Clear cache for this project
            if ($projectId) {
                $this->clearProjectCache($projectId);
            }
            $action = 'marked_' . str_replace('-', '_', $status);
            $this->logHistory($taskId, null, $action);
            return true;
        }
        return false;
    }

    public function deleteTask($taskId) {
        // Get project ID to clear cache
        $projectId = $this->getProjectIdForTask($taskId);
        
        $this->db->query("DELETE FROM project_tasks WHERE id = :id");
        $this->db->bind(':id', $taskId);
        
        if ($this->db->execute()) {
            // Clear cache for this project
            if ($projectId) {
                $this->clearProjectCache($projectId);
            }
            return true;
        }
        return false;
    }

    public function assignTask($taskId, $userId) {
        $this->db->query("UPDATE project_tasks SET assigned_to = :assigned_to, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $this->db->bind(':assigned_to', $userId);
        $this->db->bind(':id', $taskId);
        
        if ($this->db->execute()) {
            $this->logHistory($taskId, $userId, 'assigned');
            return true;
        }
        return false;
    }

    /* ============================================================
       STATISTICS & FILTERING
    ============================================================ */

    public function getTaskAssignees($projectId) {
        $this->db->query("SELECT DISTINCT assigned_to, u.id, u.username, u.profile_picture FROM project_tasks pt JOIN users u ON pt.assigned_to = u.id WHERE pt.project_id = :project_id AND pt.assigned_to IS NOT NULL");
        $this->db->bind(':project_id', $projectId);
        return $this->db->resultSet();
    }

    /* ============================================================
       PROJECT PROGRESS METRICS (for Organization Dashboard)
    ============================================================ */

    // Get comprehensive project metrics (with caching)
    public function getProjectMetrics($projectId) {
        // Check cache first
        if ($this->isCacheValid($projectId)) {
            return self::$metricsCache[$projectId]['data'];
        }

        // Get task counts by status
        $this->db->query("
            SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = 'todo' THEN 1 ELSE 0 END) as todo_tasks,
                SUM(CASE WHEN status = 'in-progress' THEN 1 ELSE 0 END) as in_progress_tasks,
                SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_tasks,
                0 as on_hold_tasks,
                SUM(CASE WHEN deadline IS NOT NULL AND deadline < CURDATE() AND status != 'done' THEN 1 ELSE 0 END) as overdue_tasks,
                COUNT(DISTINCT assigned_to) as active_members
            FROM project_tasks 
            WHERE project_id = :project_id
        ");
        $this->db->bind(':project_id', $projectId);
        $metrics = $this->db->single();

        // Calculate completion percentage
        if ($metrics && $metrics->total_tasks > 0) {
            $metrics->completion_percentage = round(($metrics->completed_tasks / $metrics->total_tasks) * 100, 1);
        } else {
            $metrics->completion_percentage = 0;
        }

        // Cache the result
        self::$metricsCache[$projectId] = [
            'data' => $metrics,
            'timestamp' => time()
        ];

        return $metrics;
    }

    // Get task breakdown by status (detailed)
    public function getTaskStatusCounts($projectId) {
        $this->db->query("
            SELECT 
                status,
                COUNT(*) as count
            FROM project_tasks 
            WHERE project_id = :project_id
            GROUP BY status
        ");
        $this->db->bind(':project_id', $projectId);
        return $this->db->resultSet();
    }

    // Get member task breakdown with progress
    public function getMemberTaskBreakdown($projectId) {
        $this->db->query("
            SELECT 
                u.id as user_id,
                u.username,
                u.profile_picture,
                pm.role,
                COUNT(pt.id) as total_tasks,
                SUM(CASE WHEN pt.status = 'done' THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN pt.status = 'in-progress' THEN 1 ELSE 0 END) as in_progress_tasks,
                SUM(CASE WHEN pt.status = 'todo' THEN 1 ELSE 0 END) as pending_tasks,
                SUM(CASE WHEN pt.deadline IS NOT NULL AND pt.deadline < CURDATE() AND pt.status != 'done' THEN 1 ELSE 0 END) as overdue_tasks
            FROM project_members pm
            INNER JOIN users u ON pm.user_id = u.id
            LEFT JOIN project_tasks pt ON pt.assigned_to = u.id AND pt.project_id = :project_id
            WHERE pm.project_id = :project_id AND pm.status = 'active'
            GROUP BY u.id, u.username, u.profile_picture, pm.role
            ORDER BY completed_tasks DESC, total_tasks DESC
        ");
        $this->db->bind(':project_id', $projectId);
        $members = $this->db->resultSet();

        // Calculate completion percentage for each member
        if ($members) {
            foreach ($members as $member) {
                if ($member->total_tasks > 0) {
                    $member->completion_percentage = round(($member->completed_tasks / $member->total_tasks) * 100, 1);
                } else {
                    $member->completion_percentage = 0;
                }
            }
        }

        return $members;
    }

    // Get overdue tasks with details
    public function getOverdueTasksByProject($projectId) {
        $this->db->query("
            SELECT 
                pt.*,
                u.username,
                u.profile_picture,
                DATEDIFF(CURDATE(), pt.deadline) as days_overdue
            FROM project_tasks pt
            LEFT JOIN users u ON pt.assigned_to = u.id
            WHERE pt.project_id = :project_id 
            AND pt.deadline IS NOT NULL 
            AND pt.deadline < CURDATE() 
            AND pt.status != 'done'
            ORDER BY pt.deadline ASC
        ");
        $this->db->bind(':project_id', $projectId);
        return $this->db->resultSet();
    }

    // Get recent task activity
    public function getRecentTaskActivity($projectId, $limit = 10) {
        $this->db->query("
            SELECT 
                th.*,
                pt.title as task_name,
                u.username,
                u.profile_picture
            FROM task_history th
            INNER JOIN project_tasks pt ON th.task_id = pt.id
            LEFT JOIN users u ON th.user_id = u.id
            WHERE pt.project_id = :project_id
            ORDER BY th.timestamp DESC
            LIMIT :limit
        ");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    // Get task distribution by priority
    public function getTaskPriorityDistribution($projectId) {
        $this->db->query("
            SELECT 
                priority,
                COUNT(*) as count,
                SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_count
            FROM project_tasks 
            WHERE project_id = :project_id
            GROUP BY priority
            ORDER BY FIELD(priority, 'high', 'medium', 'low')
        ");
        $this->db->bind(':project_id', $projectId);
        return $this->db->resultSet();
    }

    public function getTaskStats($projectId) {
        $this->db->query("SELECT COUNT(*) AS total,
            SUM(CASE WHEN status = 'todo' THEN 1 ELSE 0 END) AS todo,
            SUM(CASE WHEN status = 'in-progress' THEN 1 ELSE 0 END) AS in_progress,
            SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS done,
            SUM(CASE WHEN deadline IS NOT NULL AND deadline < CURDATE() AND status <> 'done' THEN 1 ELSE 0 END) AS overdue,
            SUM(CASE WHEN deadline = CURDATE() AND status <> 'done' THEN 1 ELSE 0 END) AS due_today,
            SUM(CASE WHEN deadline = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND status <> 'done' THEN 1 ELSE 0 END) AS due_tomorrow
            FROM project_tasks WHERE project_id = :project_id");
        $this->db->bind(':project_id', $projectId);
        return $this->db->single();
    }

    public function getTasksByStatus($projectId, $status) {
        $this->db->query("SELECT pt.*, u.username, u.profile_picture FROM project_tasks pt LEFT JOIN users u ON pt.assigned_to = u.id WHERE pt.project_id = :project_id AND pt.status = :status ORDER BY pt.deadline ASC, pt.priority DESC");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':status', $status);
        return $this->db->resultSet();
    }

    public function getTasksByMember($projectId, $memberId) {
        $this->db->query("SELECT pt.*, u.username, u.profile_picture FROM project_tasks pt LEFT JOIN users u ON pt.assigned_to = u.id WHERE pt.project_id = :project_id AND pt.assigned_to = :assigned_to ORDER BY pt.status, pt.deadline ASC");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':assigned_to', $memberId);
        return $this->db->resultSet();
    }

    public function getTasksByPriority($projectId, $priority) {
        $this->db->query("SELECT pt.*, u.username, u.profile_picture FROM project_tasks pt LEFT JOIN users u ON pt.assigned_to = u.id WHERE pt.project_id = :project_id AND pt.priority = :priority ORDER BY pt.deadline ASC");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':priority', $priority);
        return $this->db->resultSet();
    }

    // Requested API: getTasksForUser($user_id)
    public function getTasksForUser($userId) {
        $this->db->query("SELECT pt.*, p.name AS project_name, p.id AS project_id FROM project_tasks pt JOIN projects p ON pt.project_id = p.id WHERE pt.assigned_to = :user_id ORDER BY pt.status, pt.deadline ASC");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    // Backwards-compatible alias
    public function getUserAssignedTasks($userId) {
        return $this->getTasksForUser($userId);
    }

    // Global overdue tasks (any project, any assignee)
    public function getOverdueTasks() {
        $this->db->query("SELECT pt.*, p.name AS project_name, u.username
            FROM project_tasks pt
            JOIN projects p ON pt.project_id = p.id
            LEFT JOIN users u ON pt.assigned_to = u.id
            WHERE pt.deadline IS NOT NULL
              AND pt.deadline < CURDATE()
              AND pt.status <> 'done'");
        return $this->db->resultSet();
    }

    // Tasks due tomorrow (any assignee)
    public function detectDueTomorrowTasks() {
        $this->db->query("SELECT pt.*, p.name AS project_name, u.username
            FROM project_tasks pt
            JOIN projects p ON pt.project_id = p.id
            LEFT JOIN users u ON pt.assigned_to = u.id
            WHERE pt.deadline = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
              AND pt.status <> 'done'");
        return $this->db->resultSet();
    }

    // Mark tasks as overdue in history (does not change status field)
    public function markOverdueTasks() {
        $overdueTasks = $this->getOverdueTasks();
        foreach ($overdueTasks as $task) {
            $this->addHistory($task->id, $task->assigned_to, 'auto_marked_overdue');
        }
        return count($overdueTasks);
    }

    /* ============================================================
       HISTORY & LOGGING
    ============================================================ */

    // Requested API: addHistory($task_id, $user_id, $action)
    public function addHistory($taskId, $userId, $action) {
        $this->db->query("INSERT INTO task_history (task_id, user_id, action) VALUES (:task_id, :user_id, :action)");
        $this->db->bind(':task_id', $taskId);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':action', $action);
        return $this->db->execute();
    }

    // Backwards-compatible alias
    public function logHistory($taskId, $userId, $action) {
        return $this->addHistory($taskId, $userId, $action);
    }

    // Requested API: getHistory($task_id)
    public function getHistory($taskId) {
        $this->db->query("SELECT th.*, u.username FROM task_history th LEFT JOIN users u ON th.user_id = u.id WHERE th.task_id = :task_id ORDER BY th.timestamp DESC");
        $this->db->bind(':task_id', $taskId);
        return $this->db->resultSet();
    }

    // Backwards-compatible alias
    public function getTaskHistory($taskId) {
        return $this->getHistory($taskId);
    }

    // Requested API: getTasksGrouped($project_id)
    public function getTasksGrouped($projectId) {
        $all = $this->getTasksByProjectId($projectId);
        $grouped = [
            'todo' => [],
            'in-progress' => [],
            'done' => []
        ];
        foreach ($all as $task) {
            $status = $task->status;
            if (!isset($grouped[$status])) {
                $grouped[$status] = [];
            }
            $grouped[$status][] = $task;
        }
        return $grouped;
    }

    /* ============================================================
       SECURITY & VALIDATION
    ============================================================ */

    public function isTaskInProject($taskId, $projectId) {
        $this->db->query("SELECT id FROM project_tasks WHERE id = :id AND project_id = :project_id");
        $this->db->bind(':id', $taskId);
        $this->db->bind(':project_id', $projectId);
        return $this->db->single() ? true : false;
    }

    public function isUserAssignedToTask($taskId, $userId) {
        $this->db->query("SELECT id FROM project_tasks WHERE id = :id AND assigned_to = :user_id");
        $this->db->bind(':id', $taskId);
        $this->db->bind(':user_id', $userId);
        return $this->db->single() ? true : false;
    }

    public function getProjectIdForTask($taskId) {
        $this->db->query("SELECT project_id FROM project_tasks WHERE id = :id");
        $this->db->bind(':id', $taskId);
        $result = $this->db->single();
        return $result ? $result->project_id : null;
    }

}

?>
