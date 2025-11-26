<?php
// app/controllers/TaskController.php

class TaskController extends Controller {
    
    private $taskModel;
    private $projectModel;
    private $notificationModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit();
        }

        $this->taskModel = $this->model('Task');
        $this->projectModel = $this->model('Project');
        $this->notificationModel = $this->model('Notification');
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
        // Check if AJAX request
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle AJAX request
            if ($isAjax) {
                $projectId = (int)($_POST['project_id'] ?? 0);
                
                if (!$projectId) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Project ID required']);
                    return;
                }

                // Verify organization owns the project
                $project = $this->projectModel->getProjectById($projectId);
                if (!$project || $project->organization_id != $_SESSION['user_id']) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Access denied']);
                    return;
                }

                $taskData = [
                    'project_id' => $projectId,
                    'task_name' => trim($_POST['task_name'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'assigned_to' => !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null,
                    'priority' => trim($_POST['priority'] ?? 'medium'),
                    'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
                    'status' => 'pending',
                    'created_by' => $_SESSION['user_id']
                ];

                if (empty($taskData['task_name'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Task name is required']);
                    return;
                }

                $taskId = $this->taskModel->createTask($taskData);
                if ($taskId) {
                    // Notify assigned user about new task
                    if (!empty($taskData['assigned_to'])) {
                        $msg = "You have been assigned the task: {$taskData['task_name']}";
                        $this->notificationModel->createNotification([
                            'user_id' => $taskData['assigned_to'],
                            'type' => 'task_assigned',
                            'message' => $msg,
                            'project_id' => $projectId,
                            'task_id' => $taskId
                        ]);
                    }

                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Task created successfully', 'task_id' => $taskId]);
                    return;
                }

                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to create task']);
                return;
            }
            
            // Handle regular form submission
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
                'task_name' => trim($_POST['title']),
                'description' => trim($_POST['description'] ?? ''),
                'assigned_to' => (int)$_POST['assigned_to'],
                'priority' => trim($_POST['priority']),
                'due_date' => !empty($_POST['deadline']) ? $_POST['deadline'] : null,
                'status' => 'pending',
                'created_by' => $_SESSION['user_id']
            ];

            $taskId = $this->taskModel->createTask($taskData);
            if ($taskId) {
                // Notify assigned user about new task
                if (!empty($taskData['assigned_to'])) {
<<<<<<< HEAD
                    $msg = "You have been assigned the task: {$taskData['task_name']}";
=======
                    $msg = "You have been assigned the task: {$taskData['title']}";
>>>>>>> dc8d8d6d35a9005a610d0a7b06967ac0ededd82d
                    $this->notificationModel->createNotification([
                        'user_id' => $taskData['assigned_to'],
                        'type' => 'task_assigned',
                        'message' => $msg,
                        'project_id' => $projectId,
                        'task_id' => $taskId
                    ]);
                }

                $_SESSION['success'] = 'Task created successfully!';
                header('Location: ' . URLROOT . '/tasks/project/' . $projectId);
                exit();
            }

            $_SESSION['error'] = 'Failed to create task. Try again.';
        }

        // GET request - show form
        if (!$projectId) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        $project = $this->projectModel->getProjectById($projectId);
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Project not found or access denied.';
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
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
                // Notify assigned user and team about task update
                $assignedId = $updateData['assigned_to'] ?: $task->assigned_to;
                $msg = "Task '{$updateData['title']}' was updated (details/priority/deadline).";
                if ($assignedId) {
                    $this->notificationModel->createNotification([
                        'user_id' => $assignedId,
                        'type' => 'task_update',
                        'message' => $msg,
                        'project_id' => $task->project_id,
                        'task_id' => $taskId
                    ]);
                }

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
            // Notify assignee about status change (org-side move)
            if ($task->assigned_to) {
                $msg = "Status of task '{$task->title}' changed to " . ucfirst(str_replace('-', ' ', $status));
                $this->notificationModel->createNotification([
                    'user_id' => $task->assigned_to,
                    'type' => 'task_update',
                    'message' => $msg,
                    'project_id' => $task->project_id,
                    'task_id' => $taskId
                ]);
            }

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
            if ($task->assigned_to) {
                $msg = "Task {$task->title} is due tomorrow";
                $this->notificationModel->createNotification([
                    'user_id' => $task->assigned_to,
                    'type' => 'deadline_due_soon',
                    'message' => $msg,
                    'project_id' => $task->project_id,
                    'task_id' => $task->id
                ]);
            }
            $count++;
        }
        echo 'Reminders generated for ' . $count . ' tasks due tomorrow.';
    }

    // Cron-style manual checks for deadlines
    public function checkDeadlines() {
        $this->sendDeadlineReminder();

        // Due today
        $this->taskModel->db->query("SELECT * FROM project_tasks WHERE deadline = CURDATE() AND status <> 'done'");
        $dueToday = $this->taskModel->db->resultSet();
        $todayCount = 0;
        foreach ($dueToday as $task) {
            if ($task->assigned_to) {
                $msg = "Task {$task->title} is due today";
                $this->notificationModel->createNotification([
                    'user_id' => $task->assigned_to,
                    'type' => 'deadline_due_today',
                    'message' => $msg,
                    'project_id' => $task->project_id,
                    'task_id' => $task->id
                ]);
                $todayCount++;
            }
        }

        echo 'Due-tomorrow and due-today checks completed.';
    }

    /* ============================================================
       USER SIDE - UPDATE OWN TASK STATUS
    ============================================================ */
    public function updateStatus($taskId = null) {
        // Check if AJAX request
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAjax) {
            $taskId = (int)($_POST['task_id'] ?? 0);
            $status = $_POST['status'] ?? null;
            
            if (!$taskId || !$status) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
                return;
            }

            $task = $this->taskModel->getTaskById($taskId);
            if (!$task) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Task not found']);
                return;
            }

            // Verify organization owns the project OR user is assigned
            $project = $this->projectModel->getProjectById($task->project_id);
            $isOwner = ($project && $project->organization_id == $_SESSION['user_id']);
            $isAssigned = ($task->assigned_to == $_SESSION['user_id']);
            
            if (!$isOwner && !$isAssigned) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                return;
            }

            if ($this->taskModel->updateTaskStatus($taskId, $status)) {
                // Notify project owner if updated by member
                if (!$isOwner && $isAssigned) {
                    $msg = "Task '{$task->task_name}' status changed to " . ucfirst(str_replace('_', ' ', $status));
                    $this->notificationModel->createNotification([
                        'user_id' => $project->organization_id,
                        'type' => 'task_update',
                        'message' => $msg,
                        'project_id' => $task->project_id,
                        'task_id' => $taskId
                    ]);
                }
                
                // Notify assigned user if updated by owner
                if ($isOwner && $task->assigned_to) {
                    $msg = "Task '{$task->task_name}' status changed to " . ucfirst(str_replace('_', ' ', $status));
                    $this->notificationModel->createNotification([
                        'user_id' => $task->assigned_to,
                        'type' => 'task_update',
                        'message' => $msg,
                        'project_id' => $task->project_id,
                        'task_id' => $taskId
                    ]);
                }

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Task status updated']);
                return;
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
            return;
        }
        
        // Handle regular form submission
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
