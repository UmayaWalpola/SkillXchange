<?php
// app/controllers/TaskController.php

class TaskController extends Controller {
    
    private $taskModel;
    private $projectModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit();
        }

        $this->taskModel = $this->model('Task');
        $this->projectModel = $this->model('Project');
    }

     /* ============================================================
         ORGANIZATION SIDE - VIEW ALL TASKS (projectTasks)
     ============================================================ */
     public function projectTasks($projectId = null) {
        if (!$projectId) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        // Verify organization owns the project
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Project not found or access denied.';
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        // Fetch tasks and members
        $tasks = $this->taskModel->getTasksByProjectId($projectId);
        $members = $this->projectModel->getMembersByProject($projectId);
        $stats = $this->taskModel->getTaskStats($projectId);

        // Deadline-based buckets (for banner + badges)
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $overdueList = [];
        $dueTodayList = [];
        $dueTomorrowList = [];
        foreach ($tasks as $t) {
            if (empty($t->deadline) || $t->status === 'done') continue;
            if ($t->deadline < $today) {
                $overdueList[] = $t;
            } elseif ($t->deadline === $today) {
                $dueTodayList[] = $t;
            } elseif ($t->deadline === $tomorrow) {
                $dueTomorrowList[] = $t;
            }
        }

        // Grouped for kanban columns
        $tasksGrouped = $this->taskModel->getTasksGrouped($projectId);

        $data = [
            'title' => 'Task Manager - ' . $project->name,
            'project' => $project,
            'tasks' => $tasks,
            'members' => $members,
            'stats' => $stats,
            'projectId' => $projectId,
            'tasksGrouped' => $tasksGrouped,
            'overdueTasks' => $overdueList,
            'dueTodayTasks' => $dueTodayList,
            'dueTomorrowTasks' => $dueTomorrowList
        ];

        $this->view('tasks/projectTasks', $data);
    }

    /* ============================================================
       ORGANIZATION SIDE - CREATE TASK (GET + POST)
    ============================================================ */
    public function create($projectId = null) {
        if (!$projectId) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        // Verify organization owns the project
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Project not found or access denied.';
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];

            if (empty(trim($_POST['title']))) {
                $errors['title'] = 'Task title is required';
            }

            if (empty($_POST['assigned_to'])) {
                $errors['assigned_to'] = 'Please assign task to a team member';
            }

            if (empty($_POST['priority']) || !in_array($_POST['priority'], ['low', 'medium', 'high'])) {
                $errors['priority'] = 'Priority is required';
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                header('Location: ' . URLROOT . '/tasks/create/' . $projectId);
                exit();
            }

            $taskData = [
                'project_id' => $projectId,
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description'] ?? ''),
                'assigned_to' => (int)$_POST['assigned_to'],
                'priority' => trim($_POST['priority']),
                'deadline' => !empty($_POST['deadline']) ? $_POST['deadline'] : null,
                'status' => 'todo',
                'created_by' => $_SESSION['user_id']
            ];

            $taskId = $this->taskModel->createTask($taskData);
            if ($taskId) {
                $_SESSION['success'] = 'Task created successfully!';
                header('Location: ' . URLROOT . '/tasks/project/' . $projectId);
                exit();
            }

            $_SESSION['error'] = 'Failed to create task. Try again.';
        }

        $members = $this->projectModel->getMembersByProject($projectId);

        $data = [
            'title' => 'Create Task - ' . $project->name,
            'project' => $project,
            'members' => $members,
            'projectId' => $projectId,
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['errors']);

        $this->view('tasks/addTask', $data);
    }

    /* ============================================================
       ORGANIZATION SIDE - EDIT TASK (GET + POST)
    ============================================================ */
    public function edit($taskId = null) {
        if (!$taskId) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        $task = $this->taskModel->getTaskById($taskId);
        if (!$task) {
            $_SESSION['error'] = 'Task not found.';
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        // Verify organization owns the project
        $project = $this->projectModel->getProjectById($task->project_id);
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];

            if (empty(trim($_POST['title']))) {
                $errors['title'] = 'Task title is required';
            }

            if (empty($_POST['priority']) || !in_array($_POST['priority'], ['low', 'medium', 'high'])) {
                $errors['priority'] = 'Priority is required';
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                header('Location: ' . URLROOT . '/tasks/edit/' . $taskId);
                exit();
            }

            $updateData = [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description'] ?? ''),
                'assigned_to' => !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null,
                'priority' => trim($_POST['priority']),
                'deadline' => !empty($_POST['deadline']) ? $_POST['deadline'] : null,
                'updated_by' => $_SESSION['user_id']
            ];

            if ($this->taskModel->updateTask($taskId, $updateData)) {
                $_SESSION['success'] = 'Task updated successfully!';
                header('Location: ' . URLROOT . '/tasks/project/' . $task->project_id);
                exit();
            }

            $_SESSION['error'] = 'Failed to update task.';
        }

        $members = $this->projectModel->getMembersByProject($task->project_id);

        $data = [
            'title' => 'Edit Task',
            'task' => $task,
            'project' => $project,
            'members' => $members,
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['errors']);

        $this->view('tasks/editTask', $data);
    }

    /* ============================================================
       ORGANIZATION SIDE - UPDATE TASK STATUS
    ============================================================ */
    // Admin/org side status change (board move)
    public function status($taskId = null, $status = null) {
        if (!$taskId || !$status) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        $task = $this->taskModel->getTaskById($taskId);
        if (!$task) {
            echo json_encode(['success' => false, 'message' => 'Task not found']);
            return;
        }

        // Verify organization owns the project
        $project = $this->projectModel->getProjectById($task->project_id);
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        if ($this->taskModel->updateTaskStatus($taskId, $status)) {
            $_SESSION['success'] = 'Task status updated!';
            echo json_encode(['success' => true, 'message' => 'Task status updated']);
            return;
        }

        echo json_encode(['success' => false, 'message' => 'Failed to update task status']);
    }

    /* ============================================================
       ORGANIZATION SIDE - DELETE TASK
    ============================================================ */
    public function delete($taskId = null) {
        if (!$taskId) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        $task = $this->taskModel->getTaskById($taskId);
        if (!$task) {
            $_SESSION['error'] = 'Task not found.';
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        // Verify organization owns the project
        $project = $this->projectModel->getProjectById($task->project_id);
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        if ($this->taskModel->deleteTask($taskId)) {
            $_SESSION['success'] = 'Task deleted successfully!';
            header('Location: ' . URLROOT . '/tasks/project/' . $task->project_id);
            exit();
        }

        $_SESSION['error'] = 'Failed to delete task.';
        header('Location: ' . URLROOT . '/tasks/project/' . $task->project_id);
        exit();
    }

    /* ============================================================
       USER SIDE - VIEW ASSIGNED TASKS
    ============================================================ */
    // Tasks for logged-in user across projects
    public function userTasks() {
        $userId = $_SESSION['user_id'];
        $tasks = $this->taskModel->getTasksForUser($userId);

        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $overdueList = [];
        $dueTodayList = [];
        $dueTomorrowList = [];
        foreach ($tasks as $t) {
            if (empty($t->deadline) || $t->status === 'done') continue;
            if ($t->deadline < $today) {
                $overdueList[] = $t;
            } elseif ($t->deadline === $today) {
                $dueTodayList[] = $t;
            } elseif ($t->deadline === $tomorrow) {
                $dueTomorrowList[] = $t;
            }
        }

        $data = [
            'tasks' => $tasks,
            'overdueTasks' => $overdueList,
            'dueTodayTasks' => $dueTodayList,
            'dueTomorrowTasks' => $dueTomorrowList
        ];

        $this->view('tasks/userTasks', $data);
    }

    /* ============================================================
       DEADLINE REMINDER HOOK (for cron / manual call)
    ============================================================ */
    public function sendDeadlineReminder() {
        $dueTomorrow = $this->taskModel->detectDueTomorrowTasks();
        $count = 0;
        foreach ($dueTomorrow as $task) {
            $this->taskModel->addHistory($task->id, $task->assigned_to, 'deadline_reminder_24h');
            $count++;
        }
        echo 'Reminders generated for ' . $count . ' tasks due tomorrow.';
    }

    /* ============================================================
       USER SIDE - UPDATE OWN TASK STATUS
    ============================================================ */
    public function updateStatus($taskId = null) {
        $status = $_POST['status'] ?? null;
        if (!$taskId || !$status) {
            $_SESSION['error'] = 'Invalid request';
            header('Location: ' . URLROOT . '/tasks/user');
            return;
        }

        $task = $this->taskModel->getTaskById($taskId);
        if (!$task) {
            $_SESSION['error'] = 'Task not found';
            header('Location: ' . URLROOT . '/tasks/user');
            return;
        }

        // Only assigned member may update their task
        if ($task->assigned_to != $_SESSION['user_id']) {
            $_SESSION['error'] = 'You are not allowed to update this task';
            header('Location: ' . URLROOT . '/tasks/user');
            return;
        }

        if ($this->taskModel->updateTaskStatus($taskId, $status)) {
            $_SESSION['success'] = 'Task status updated!';
        } else {
            $_SESSION['error'] = 'Failed to update task status';
        }

        header('Location: ' . URLROOT . '/tasks/user');
    }

}

?>
