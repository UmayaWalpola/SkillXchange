<?php
 
 //Controller  Template 
class ProjectController extends Controller
{
    private $projectModel;

    public function __construct()
    {
        $this->projectModel = $this->model('Project');
    }

   // 1. READ ALL (List) - For organization to view their projects
    public function index()
    {
        $org_id = $_SESSION['user_id'];

        $projects = $this->projectModel->getByOrg($org_id);

        $this->view('organization/projects', ['projects' => $projects]);
    }

     // 2. CREATE - Show form
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

    /*<?php
// app/controllers/Books.php
    ******Controller Template ***********

class Books extends Controller {
    private $bookModel;

    public function __construct() {
        // Load the model
        $this->bookModel = $this->model('Book');
    }

    // 1. READ ALL (List)
    public function index() {
        $books = $this->bookModel->getAll();
        $this->view('books/index', ['books' => $books]);
    }

    // 2. CREATE
    public function create() {
        // If the form is submitted (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $title = trim($_POST['title'] ?? '');
            
            // Validation
            if (empty($title)) {
                $data = ['error' => 'Title is required!'];
                $this->view('books/create', $data); // If invalid, show the form again
            } else {
                // Send to model and save
                if ($this->bookModel->create($title)) {
                    $_SESSION['success'] = "Book added successfully!";
                    header("Location: " . URLROOT . "/books/index"); // Redirect to main page
                    exit;
                }
            }
        } 
        // If the form is not submitted (GET) - just show empty form
        else {
            $this->view('books/create');
        }
    }

    // 3. UPDATE
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title']);
            
            if ($this->bookModel->update($id, $title)) {
                $_SESSION['success'] = "Changes saved!";
                header("Location: " . URLROOT . "/books/index");
                exit;
            }
        } else {
            // GET request: fetch existing data and send to form
            $book = $this->bookModel->getById($id);
            $this->view('books/edit', ['book' => $book]);
        }
    }

    // 4. DELETE
    public function delete($id) {
        if ($this->bookModel->delete($id)) {
            $_SESSION['success'] = "Book deleted!";
        }
        header("Location: " . URLROOT . "/books/index");
        exit;
    }
}*/

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

        // Suggest only projects that match user's teach skills and have required skills.
        $userTeachSkills = $this->projectModel->getUserTeachSkills($userId);
        $allProjects = $this->projectModel->getSuggestedProjectsForUser($userId);

        $data = [
            'title'                      => 'Discover Projects',
            'projects'                   => $allProjects,       // all projects (bottom grid)
            'memberProjects'             => $memberProjects,    // user's member projects (top)
            'page'                       => 'discover-projects',
            'applicationStatusByProject' => $applicationStatusByProject,
            'memberProjectIds'           => $memberProjectIds,
            'userTeachSkills'            => $userTeachSkills
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

        // Enforce required-skill overlap before accepting an application.
        $skillMatch = $this->projectModel->getSkillMatchSummaryForProject((int)$projectId, (int)$_SESSION['user_id']);
        if (empty($skillMatch['allowed'])) {
            $msg = 'You can only join projects where required skills match your teach skills.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
            header('Location: ' . URLROOT . '/project/detail/' . $projectId);
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
        if ($saved) {
            $this->notifyOrganizationOfApplication((int)$projectId, (int)$_SESSION['user_id']);
        }

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

    private function notifyOrganizationOfApplication(int $projectId, int $applicantId): void
    {
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project || empty($project->organization_id)) {
            return;
        }

        $db = new Database();
        $db->query("SELECT username FROM users WHERE id = :id LIMIT 1");
        $db->bind(':id', $applicantId);
        $applicant = $db->single();

        $applicantName = $applicant->username ?? 'A user';
        $notificationModel = $this->model('Notification');
        $notificationModel->createNotification([
            'user_id' => (int)$project->organization_id,
            'type' => 'project_application_submitted',
            'message' => $applicantName . ' applied to "' . ($project->name ?? 'your project') . '".',
            'project_id' => $projectId,
            'target_url' => URLROOT . '/organization/applications',
            'entity_type' => 'project_application',
            'entity_id' => $projectId,
            'actor_user_id' => $applicantId
        ]);
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
                $rewardMessage = '';
                if ((float)($task->buckx_allocated ?? 0) > 0 && empty($task->buckx_distributed) && !empty($task->assigned_to)) {
                    try {
                        $walletModel = $this->model('Wallet');
                        $project = $this->projectModel->getProjectById($projectId);

                        if ($project && !empty($project->organization_id)) {
                            $transferResult = $walletModel->transferTaskReward(
                                $taskId,
                                (int)$project->organization_id,
                                (int)$task->assigned_to,
                                (float)$task->buckx_allocated
                            );

                            if (!empty($transferResult['success'])) {
                                $taskModel->markBuckXDistributed($taskId);
                                $rewardMessage = ' You received ' . rtrim(rtrim(number_format((float)$task->buckx_allocated, 2, '.', ''), '0'), '.') . ' BuckX.';

                                try {
                                    $this->notificationModel->createNotification([
                                        'user_id'    => (int)$task->assigned_to,
                                        'type'       => 'buckx_reward',
                                        'message'    => "Congratulations! You received {$task->buckx_allocated} BuckX reward for completing task '{$task->title}'",
                                        'project_id' => $projectId,
                                        'task_id'    => $taskId,
                                        'target_url' => URLROOT . '/userdashboard/wallet'
                                    ]);
                                } catch (Throwable $notificationError) {
                                    error_log('Reward notification error: ' . $notificationError->getMessage());
                                }
                            } else {
                                error_log("Project completeTask BuckX transfer failed for task {$taskId}: " . ($transferResult['message'] ?? 'Unknown error'));
                            }
                        }
                    } catch (Throwable $buckxError) {
                        error_log('Project completeTask BuckX transfer error: ' . $buckxError->getMessage());
                    }
                }

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

                echo json_encode(['success' => true, 'message' => 'Task marked as complete!' . $rewardMessage]);
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
