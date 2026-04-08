<?php

class ProjectController extends Controller
{
    private $projectModel;

    public function __construct()
    {
        $this->projectModel = $this->model('Project');
    }

    // List all projects for logged org
    public function index()
    {
        $org_id = $_SESSION['user_id'];

        $projects = $this->projectModel->getByOrg($org_id);

        $this->view('organization/projects', ['projects' => $projects]);
    }

    // Show create form
    public function create()
    {
        $this->view('organization/createProject');
    }

    // Store project
    public function store()
    {
        $data = [
            'organization_id' => $_SESSION['user_id'],
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'required_skills' => $_POST['required_skills'],
            'max_members' => $_POST['max_members'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date']
        ];

        $this->projectModel->create($data);

        header("Location: /SkillXchange/public/ProjectController/index");
        exit;
    }

    // Edit form
    public function edit($id)
    {
        $project = $this->projectModel->getProject($id);
        $this->view('organization/editProject', ['project' => $project]);
    }

    // Update action
    public function update($id)
    {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'required_skills' => $_POST['required_skills'],
            'max_members' => $_POST['max_members'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date']
        ];

        $this->projectModel->update($id, $data);

        header("Location: /SkillXchange/public/ProjectController/index");
        exit;
    }

    // Delete
    public function delete($id)
    {
        $this->projectModel->delete($id);

        header("Location: /SkillXchange/public/ProjectController/index");
        exit;
    }

    // Public project detail view (user-facing)
    public function browse()
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Please sign in to browse projects.';
            header('Location: ' . URLROOT . '/auth/signin');
            exit();
        }

        if (isset($_SESSION['role']) && $_SESSION['role'] === 'organization') {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        $userId = (int)$_SESSION['user_id'];
        $db = new Database();

        // Fetch the latest application status per project for the current user.
        $db->query("SELECT pa.project_id, pa.status
                    FROM project_applications pa
                    INNER JOIN (
                        SELECT project_id, MAX(id) AS latest_id
                        FROM project_applications
                        WHERE user_id = :user_id
                        GROUP BY project_id
                    ) latest ON latest.latest_id = pa.id");
        $db->bind(':user_id', $userId);
        $applicationRows = $db->resultSet();
        $applicationStatusByProject = [];
        foreach ($applicationRows as $row) {
            $applicationStatusByProject[(int)$row->project_id] = strtolower($row->status);
        }

        // Fetch member projects with join date for top banner
        $db->query("SELECT p.*, pm.joined_at, pm.role,
                    (SELECT COUNT(*) FROM project_members WHERE project_id = p.id AND status='active') AS current_members
                    FROM projects p
                    JOIN project_members pm ON pm.project_id = p.id
                    WHERE pm.user_id = :user_id AND pm.status = 'active'
                    ORDER BY pm.joined_at DESC");
        $db->bind(':user_id', $userId);
        $memberProjects = $db->resultSet();

        $memberProjectIds = [];
        foreach ($memberProjects as $mp) {
            $memberProjectIds[(int)$mp->id] = true;
        }

        // Fetch ALL projects from DB for the bottom discovery section
        $allProjects = $this->projectModel->getAllProjects();

        $data = [
            'title'                      => 'Discover Projects',
            'projects'                   => $allProjects,       // all projects (bottom grid)
            'memberProjects'             => $memberProjects,    // user's member projects (top)
            'page'                       => 'discover-projects',
            'applicationStatusByProject' => $applicationStatusByProject,
            'memberProjectIds'           => $memberProjectIds
        ];

        $this->view('projects/browse', $data);
    }

    // Public project detail view (user-facing)
    public function detail($id = null)
    {
        if (!$id) {
            header('Location: ' . URLROOT . '/');
            exit();
        }

        $project = $this->projectModel->getProjectById($id);
        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            header('Location: ' . URLROOT . '/');
            exit();
        }


        $application = null;
        $is_member = false;
        if (isset($_SESSION['user_id'])) {
            $application = $this->projectModel->getUserApplication($id, $_SESSION['user_id']);
            // Check membership
            $is_member = $this->projectModel->isUserMember($id, $_SESSION['user_id']);
        }

        // Get team members
        $members = $this->projectModel->getMembersByProject($id);

        // Progress overview (tasks + members)
        $progress = $this->projectModel->getProjectProgress($id);

        // Load task model for statistics
        $taskModel = $this->model('Task');
        $taskStats = $taskModel->getTaskStats($id);

        // Load tasks assigned to the current user for this project (member view)
        $myTasks = [];
        if (isset($_SESSION['user_id']) && $is_member) {
            $myTasks = $taskModel->getTasksByMember($id, $_SESSION['user_id']);
        }

        $data = [
            'title'       => $project->name,
            'project'     => $project,
            'application' => $application,
            'is_member'   => $is_member,
            'members'     => $members ?? [],
            'progress'    => $progress,
            'taskModel'   => $taskModel,
            'taskStats'   => $taskStats,
            'myTasks'     => $myTasks
        ];

        parent::view('projects/view', $data);
    }

    /**
     * Handle project application submission
     * POST: /project/submitApplication/{projectId}
     */
    public function submitApplication($projectId = null)
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
                return;
            }
            $_SESSION['error'] = 'Invalid request method.';
            header('Location: ' . URLROOT . '/project/detail/' . ($projectId ?? ''));
            exit();
        }

        if (!$projectId) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Project ID missing.']);
                return;
            }
            $_SESSION['error'] = 'Project ID missing.';
            header('Location: ' . URLROOT . '/');
            exit();
        }

        // Ensure logged in
        if (!isset($_SESSION['user_id'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'You must be logged in to apply.']);
                return;
            }
            $_SESSION['error'] = 'You must be logged in to apply.';
            header('Location: ' . URLROOT . '/auth/signin');
            exit();
        }

        // Collect and normalize fields (support legacy and new names)
        $data = [];
        $data['project_id'] = (int)$projectId;
        $data['user_id'] = (int)$_SESSION['user_id'];

        // Accept both name variants for compatibility
        $data['relevant_experience'] = trim($_POST['relevant_experience'] ?? $_POST['experience'] ?? '');
        $data['matching_skills'] = trim($_POST['matching_skills'] ?? $_POST['skills'] ?? '');
        $data['contribution'] = trim($_POST['contribution'] ?? '');
        $data['availability'] = trim($_POST['availability'] ?? $_POST['commitment'] ?? '');
        $data['expected_duration'] = trim($_POST['expected_duration'] ?? $_POST['duration'] ?? '');
        $data['motivation'] = trim($_POST['motivation'] ?? '');
        $data['portfolio_link'] = trim($_POST['portfolio'] ?? '');
        $data['agreement'] = isset($_POST['agreement']) ? 1 : (isset($_POST['agree_terms']) ? 1 : 0);

        // Basic validation
        $errors = [];
        if (empty($data['relevant_experience'])) $errors[] = 'Relevant experience is required.';
        if (empty($data['matching_skills'])) $errors[] = 'Matching skills are required.';
        if (empty($data['contribution'])) $errors[] = 'Contribution details are required.';
        if (empty($data['availability'])) $errors[] = 'Availability is required.';
        if (empty($data['expected_duration'])) $errors[] = 'Expected duration is required.';
        if (empty($data['motivation'])) $errors[] = 'Motivation is required.';
        if (empty($data['agreement'])) $errors[] = 'You must agree to the project guidelines.';

        if (!empty($errors)) {
            $msg = implode(' ', $errors);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
            header('Location: ' . URLROOT . '/project/detail/' . $projectId);
            exit();
        }

        // Optionally prevent duplicate pending application
        $existing = $this->projectModel->getUserApplication($projectId, $data['user_id']);
        if ($existing && $existing->status === 'pending') {
            $msg = 'You already have a pending application for this project.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
            header('Location: ' . URLROOT . '/project/detail/' . $projectId);
            exit();
        }

        // Save via model
        $saved = $this->projectModel->saveFullApplication($data);

        if ($isAjax) {
            header('Content-Type: application/json');
            if ($saved) {
                echo json_encode(['success' => true, 'message' => 'Application submitted successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to submit application. Please try again.']);
            }
            return;
        }

        if ($saved) {
            $_SESSION['success'] = 'Application submitted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to submit application. Please try again.';
        }

        header('Location: ' . URLROOT . '/project/detail/' . $projectId);
        exit();
    }

    /**
     * AJAX: Mark a task as complete (called by member)
     * POST JSON: { task_id, project_id }
     */
    public function completeTask()
    {
        ob_clean();
        header('Content-Type: application/json');

        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }

            $rawInput = file_get_contents('php://input');
            $input    = json_decode($rawInput, true) ?: $_POST;

            $taskId    = (int)($input['task_id'] ?? 0);
            $projectId = (int)($input['project_id'] ?? 0);

            if (!$taskId || !$projectId) {
                echo json_encode(['success' => false, 'message' => 'Missing task_id or project_id']);
                exit;
            }

            $taskModel = $this->model('Task');
            $task = $taskModel->getTaskById($taskId);

            if (!$task || (int)$task->project_id !== $projectId) {
                echo json_encode(['success' => false, 'message' => 'Task not found']);
                exit;
            }

            if ((int)$task->assigned_to !== (int)$_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'You are not assigned to this task']);
                exit;
            }

            if ($taskModel->updateTaskStatus($taskId, 'done', (int)$_SESSION['user_id'])) {
                try {
                    $project = $this->projectModel->getProjectById($projectId);
                    if ($project && !empty($project->organization_id)) {
                        $notifModel = $this->model('Notification');
                        $memberName = $_SESSION['username'] ?? 'A member';
                        $notifModel->createNotification([
                            'user_id'    => $project->organization_id,
                            'type'       => 'task_completed',
                            'message'    => "{$memberName} completed the task: {$task->title}",
                            'project_id' => $projectId,
                            'task_id'    => $taskId
                        ]);
                    }
                } catch (Throwable $e) {
                    error_log('Notify org on task complete: ' . $e->getMessage());
                }

                echo json_encode(['success' => true, 'message' => 'Task marked as complete!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update task status']);
            }
        } catch (Throwable $e) {
            error_log('Project completeTask error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to complete task. Please try again.']);
        }
        exit;
    }
}
