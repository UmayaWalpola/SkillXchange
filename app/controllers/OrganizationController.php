<?php
// app/controllers/OrganizationController.php

class OrganizationController extends Controller {
    
    private $projectModel;
    private $taskModel;
    private $notificationModel;

    public function __construct() {
        $isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // Require login + role check
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
            if ($isAjaxRequest) {
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Your organization session has expired. Please sign in again as the organization account.'
                ]);
            } else {
                header('Location: ' . URLROOT . '/auth/signin');
            }
            exit();
        }

        $this->projectModel = $this->model('Project');
        $this->taskModel = $this->model('Task');
        $this->notificationModel = $this->model('Notification');
    }

    /* ============================================================
       DEFAULT → PROFILE
    ============================================================ */
    public function index() {
        $this->profile();
    }

    /* ============================================================
       ORGANIZATION PROFILE
    ============================================================ */
    public function profile() {

        // Get full organization data from database
        $db = new Database();
        $db->query("SELECT * FROM users WHERE id = :id AND role = 'organization'");
        $db->bind(':id', $_SESSION['user_id']);
        $orgUser = $db->single();

        // Get project stats
        $stats = $this->projectModel->getOrganizationStats($_SESSION['user_id']);

        // Prepare organization data
        $organization = $orgUser ?: (object)[
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => '',
            'phone' => '',
            'website' => '',
            'bio' => '',
            'address' => '',
            'city' => '',
            'country' => '',
            'postal_code' => '',
            'linkedin' => '',
            'twitter' => '',
            'github' => ''
        ];

        $data = [
            'title' => 'Organization Profile',
            'org_id' => $_SESSION['user_id'],
            'organization' => $organization,
            'stats' => $stats ?: (object)[
                'total_projects' => 0,
                'active_projects' => 0,
                'in_progress_projects' => 0,
                'completed_projects' => 0
            ]
        ];

        $this->view('organization/profile', $data);
    }

    // ============================================================
    // PROJECT LIST PAGE (Read All Projects)
    // Assigned to: Kithsara
    // ============================================================
    public function projects() {
        // Step 1: Get all projects for the logged-in organization from the model
        $projects = $this->projectModel->getProjectsByOrganization($_SESSION['user_id']);

        // Step 2: Loop through each project and get its stats/metrics
        if ($projects) {
            foreach ($projects as $project) {
                $project->metrics = $this->taskModel->getProjectMetrics($project->id);
            }
        }

        // Step 3: Pack the variables needed for the UI view into a $data array
        $data = [
            'title' => 'My Projects',
            'projects' => $projects
        ];

        // Step 4: Load the "projects.php" view file and pass the $data
        $this->view('organization/projects', $data);
    }

    // ============================================================
    // CREATE PROJECT (Handles both loading the form and form submission)
    // Assigned to: Kithsara
    // ============================================================
    public function createProject() {
        
        // This if statement runs ONLY when the user clicks the 'Submit' button
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Initialize array to hold basic input errors
            $errors = [];
            
            // Clean up the user inputs
            $category = strtolower(trim((string)($_POST['category'] ?? '')));
            $rawRequiredSkills = trim((string)($_POST['required_skills'] ?? ''));
            $skillsValidation = null; // Will store skill checks later

            // --- Step 1: Form Validation ---
            
            // Check Project Name
            if (empty(trim($_POST['name']))) {
                $errors['name'] = 'Project name is required';
            }

            // Check Project Description
            if (empty(trim($_POST['description']))) {
                $errors['description'] = 'Project description is required';
            }

            // Check Required Skills are not empty
            if ($rawRequiredSkills === '') {
                $errors['required_skills'] = 'Please specify required skills';
            }
            
            // Check Category is valid
            if ($category === '') {
                $errors['category'] = 'Project category is required';
            } elseif (!isset($this->getCategorySkillMap()[$category])) {
                $errors['category'] = 'Invalid project category';
            }
            
            // Check Maximum Members count
            if (empty($_POST['max_members']) || $_POST['max_members'] < 1) {
                $errors['max_members'] = 'Max members must be at least 1';
            }
            
            // Validate specific skills against the chosen category
            if (!isset($errors['required_skills']) && !isset($errors['category'])) {
                // Call private function to check if typed skills map to real category skills
                $skillsValidation = $this->validateRequiredSkillsForCategory($category, $rawRequiredSkills);
                
                // If it fails, create an error
                if (!$skillsValidation['valid']) {
                    $errors['required_skills'] = 'Use only ' . ucfirst($category) . ' related skills. Invalid: ' . implode(', ', $skillsValidation['invalid']);
                }
            }

            // --- Step 2: Database Save ---
            
            // Proceed ONLY if there are exactly 0 errors
            if (empty($errors)) {
                
                // Pack form data into an array to send to the Model
                $projectData = [
                    'org_id'          => $_SESSION['user_id'], // Get organization ID from current logged in user
                    'name'            => trim($_POST['name']),
                    'category'        => $category,
                    'status'          => !empty($_POST['status']) ? trim($_POST['status']) : 'active', // Default is 'active'
                    'description'     => trim($_POST['description']),
                    'max_members'     => (int)$_POST['max_members'],
                    'start_date'      => $_POST['start_date'] ?? null,
                    'end_date'        => $_POST['end_date'] ?? null,
                    // If skills were valid, use the formatted version. Else, use raw text.
                    'required_skills' => $skillsValidation ? $skillsValidation['formatted'] : $rawRequiredSkills
                ];
                
                // Give the array to the model. If createProject() returns true, it worked!
                if ($this->projectModel->createProject($projectData)) {   
                    // Set success message
                    $_SESSION['success'] = 'Project created successfully!';
                    // Redirect to project list page
                    header('Location: ' . URLROOT . '/organization/projects');
                    exit(); // Stop code execution
                }

                // If Model fails, set error
                $_SESSION['error'] = 'Failed to create project. Try again.';
            }

            // Keep the errors in session so we can display them on the form
            $_SESSION['errors'] = $errors;
        }

        // --- Step 3: Load the Form (This runs when user first visits the page or after an error) ---
        $data = [
            'title' => 'Create Project',
            'errors' => $_SESSION['errors'] ?? [], // Pass errors to view
            'project' => null, // project is null because we are creating, not editing
            'categorySkillMap' => $this->getCategorySkillMap()
        ];

        // Clear errors so they don't show up again on refresh
        unset($_SESSION['errors']);

        // Load the view and parse $data array
        $this->view('organization/createProject', $data);
    }

    // ============================================================
    // EDIT PROJECT (Handles both loading the pre-filled form & submission)
    // Assigned to: Kithsara
    // ============================================================
    public function editProject($projectId = null) {
        
        // --- Step 1: Security & Loading Existing Data ---

        // If no ID is given in URL, go back to project list
        if (!$projectId) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        // Get the project details from Model to pre-fill the form
        $project = $this->projectModel->getProjectById($projectId);

        // Security check: Check if project exists OR if it belongs to someone else
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Project not found or access denied';
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        // --- Step 2: Handle Form Submit ---
        
        // This runs only when they click "Update"
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $errors = [];
            $category = strtolower(trim((string)($_POST['category'] ?? '')));
            $rawRequiredSkills = trim((string)($_POST['required_skills'] ?? ''));
            $skillsValidation = null;

            // Form validation - same basics as Create Project
            if (empty(trim($_POST['name']))) {
                $errors['name'] = 'Project name is required';
            }

            if (empty(trim($_POST['description']))) {
                $errors['description'] = 'Description required';
            }

            if ($rawRequiredSkills === '') {
                $errors['required_skills'] = 'Required skills missing';
            }

            if ($category === '') {
                $errors['category'] = 'Project category is required';
            } elseif (!isset($this->getCategorySkillMap()[$category])) {
                $errors['category'] = 'Invalid project category';
            }
                         
            // Only validate skills if category is valid and skills are provided
            if (!isset($errors['required_skills']) && !isset($errors['category'])) {   
                // Validate skills against the category's allowed skill set
                $skillsValidation = $this->validateRequiredSkillsForCategory($category, $rawRequiredSkills);          
                if (!$skillsValidation['valid']) {
                    // If not valid, add error message listing the invalid skills
                    $errors['required_skills'] = 'Use only ' . ucfirst($category) . ' related skills. Invalid: ' . implode(', ', $skillsValidation['invalid']);             
                }
            }

            // --- Step 3: Database Update ---

            if (empty($errors)) {

                // Pack updated data into an array
                $projectData = [
                    'id'              => $projectId, // We need ID for UPDATE query!
                    'org_id'          => $_SESSION['user_id'], // Need this for security (WHERE org_id = ?)
                    'name'            => trim($_POST['name']),
                    'category'        => $category,
                    'status'          => trim($_POST['status']), // Editing lets you change status
                    'description'     => trim($_POST['description']),
                    'max_members'     => (int)$_POST['max_members'],
                    'start_date'      => $_POST['start_date'] ?? null,
                    'end_date'        => $_POST['end_date'] ?? null,
                    'required_skills' => $skillsValidation ? $skillsValidation['formatted'] : $rawRequiredSkills
                ];

                // Send array to model. If updateProject() returns true, redirect success.
                if ($this->projectModel->updateProject($projectData)) {
                    $_SESSION['success'] = 'Project updated!';
                    header('Location: ' . URLROOT . '/organization/projects');
                    exit();
                }

                $_SESSION['error'] = 'Failed to update project.';
            }

            $_SESSION['errors'] = $errors;
        }

        // --- Step 4: Load the View ---

        $data = [
            'title' => 'Edit Project',
            'project' => $project, // We pass ($project) so the form can be pre-filled
            'errors' => $_SESSION['errors'] ?? [],
            'categorySkillMap' => $this->getCategorySkillMap()
        ];

        unset($_SESSION['errors']);

        // Reuse the exact same view as CreateProject, but give it data.
        $this->view('organization/createProject', $data);
    }

    // ============================================================
    // DELETE PROJECT (AJAX request - happens without page reload)
    // Assigned to: Kithsara
    // ============================================================
    public function deleteProject() {
        
        // Step 1: Tell the browser we are replying with JSON data
        header('Content-Type: application/json');
        
        // Security check: Must be a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        // Step 2: Grab the project ID from the frontend AJAX request
        $projectId = $_POST['project_id'] ?? null;

        if (!$projectId) {
            echo json_encode(['success' => false, 'message' => 'Project ID missing']);
            return;
        }

        // Step 3: Find the project from DB for security checks
        $project = $this->projectModel->getProjectById($projectId);

        // Security check: Ensure they don't delete another organization's project
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        // Step 4: Ask model to delete it. If true, send a "success" message back.
        if ($this->projectModel->deleteProject($projectId, $_SESSION['user_id'])) {
            echo json_encode(['success' => true, 'message' => 'Project deleted']);
            return;
        }

        // If something failed, send "false" message.
        echo json_encode(['success' => false, 'message' => 'Failed to delete']);
    }


    // ============================================================
    // VIEW PROJECT APPLICATIONS (List page for applications)
    // Assigned to: Kithsara
    // ============================================================
    public function applications() {
        // Step 1: Get all people who applied to your organization's projects
        $applications = $this->projectModel->getAllApplicationsForOrganization($_SESSION['user_id']);

        // Step 2: Get skills match level for each applicant (to show if they are a good fit)
        if (!empty($applications)) {
            foreach ($applications as $application) {
                $application->matched_skills_with_level = $this->projectModel->getMatchedTeachSkillsForProject(
                    (int)($application->project_id ?? 0),
                    (int)($application->user_id ?? 0)
                );
            }
        }

        // Step 3: Get total counts (pending, accepted, rejected numbers)
        $stats = $this->projectModel->getApplicationStats($_SESSION['user_id']);

        // Step 4: Send data to Applications view
        $data = [
            'title' => 'Project Applications',
            'org_id' => $_SESSION['user_id'],
            'applications' => $applications,
            'stats' => $stats
        ];

        $this->view('organization/applications', $data);
    }

    // ============================================================
    // CHATS PAGE (Group chat for project)
    // Assigned to: Kithsara / Umaya
    // ============================================================
    public function chats() {
        $data = [
            'title' => 'Project Chats',
            'org_id' => $_SESSION['user_id']
        ];

        $this->view('organization/chats', $data);
    }

    // ============================================================
    // ACCEPT/REJECT APPLICATIONS (Updates status in Database)
    // Assigned to: Kithsara
    // URL looks like: /organization/handleApplication/12/accept
    // ============================================================
    public function handleApplication($applicationId = null, $action = null)
    {
        // Must provide an ID and action MUST be 'accept' or 'reject'
        if (!$applicationId || !in_array($action, ['accept','reject'])) {
            $_SESSION['error'] = 'Invalid action.';
            header('Location: ' . URLROOT . '/organization/applications');
            exit();
        }

        $org_id = $_SESSION['user_id'];

        // Step 1: Tell Model to accept or reject based on action
        if ($action === 'accept') {
            $res = $this->projectModel->acceptApplication($applicationId, $org_id);
        } else {
            $res = $this->projectModel->rejectApplication($applicationId, $org_id);
        }

        // Step 2: Handle Model response and show success or error message
        if (is_array($res)) {
            if (!empty($res['success'])) {
                $_SESSION['success'] = $res['message'] ?? 'Action completed.';
            } else {
                $_SESSION['error'] = $res['message'] ?? 'Action failed.';
            }
        } else {
            // fallback boolean
            if ($res) {
                $_SESSION['success'] = 'Action completed.';
            } else {
                $_SESSION['error'] = 'Action failed.';
            }
        }

        // Step 3: Redirect back to applications page
        header('Location: ' . URLROOT . '/organization/applications');
        exit();
    }

    // ============================================================
    // MANAGE MEMBERS & ROLES
    // Assigned to: Kithsara
    // ============================================================
    public function members($projectId = null) {
        // Must have project ID
        if (!$projectId) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        // Step 1: Verify organization owns this project
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Project not found or access denied.';
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        // Step 2: Get all active members for this project
        $members = $this->projectModel->getMembersByProject($projectId);

        // Map their skills so the admin can see what they are good at
        if (!empty($members)) {
            foreach ($members as $member) {
                $member->matched_skills_with_level = $this->projectModel->getMatchedTeachSkillsForProject(
                    (int)$projectId,
                    (int)($member->user_id ?? 0)
                );
            }
        }

        // Step 3: Get metrics for Team dashboard progress overview
        $projectMetrics = $this->taskModel->getProjectMetrics($projectId);
        $memberBreakdown = $this->taskModel->getMemberTaskBreakdown($projectId);
        $overdueTasks = $this->taskModel->getOverdueTasksByProject($projectId);
        $recentActivity = $this->taskModel->getRecentTaskActivity($projectId, 5);
        $priorityDistribution = $this->taskModel->getTaskPriorityDistribution($projectId);

        // Step 4: Load the "members.php" view and pass all progress data
        $data = [
            'title' => 'Manage Members - ' . $project->name,
            'project' => $project,
            'members' => $members,
            'projectId' => $projectId,
            'taskModel' => $this->taskModel,
            'projectMetrics' => $projectMetrics,
            'memberBreakdown' => $memberBreakdown,
            'overdueTasks' => $overdueTasks,
            'recentActivity' => $recentActivity,
            'priorityDistribution' => $priorityDistribution
        ];

        $this->view('organization/members', $data);
    }

    /**
     * POST: /organization/updateMemberRole
     * Updates a member's role (form POST, not AJAX)
     */
    public function updateMemberRole()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        $memberId = isset($_POST['member_id']) ? (int)$_POST['member_id'] : 0;
        $projectId = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
        $role = isset($_POST['role']) ? trim($_POST['role']) : '';
        $custom = isset($_POST['custom_role']) ? trim($_POST['custom_role']) : '';

        // If role is custom keyword, use custom input
        if (strcasecmp($role, 'custom') === 0 && $custom !== '') {
            $role = $custom;
        }

        // Basic validation
        if (!$memberId || !$projectId || $role === '') {
            $_SESSION['error'] = 'Missing required fields.';
            header('Location: ' . URLROOT . '/organization/members/' . $projectId);
            exit();
        }

        // Ensure user is organization (constructor already guards but double-check)
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
            $_SESSION['error'] = 'Permission denied.';
            header('Location: ' . URLROOT . '/auth/signin');
            exit();
        }

        $orgId = $_SESSION['user_id'];

        // Verify project belongs to this organization
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project || $project->organization_id != $orgId) {
            $_SESSION['error'] = 'Project not found or access denied.';
            header('Location: ' . URLROOT . '/organization/members/' . $projectId);
            exit();
        }

        // Verify the member belongs to this project
        $members = $this->projectModel->getMembersByProject($projectId);
        $belongs = false;
        foreach ($members as $m) {
            if (isset($m->id) && (int)$m->id === $memberId) {
                $belongs = true;
                break;
            }
        }

        if (!$belongs) {
            $_SESSION['error'] = 'Member does not belong to this project.';
            header('Location: ' . URLROOT . '/organization/members/' . $projectId);
            exit();
        }

        // Call model (new method with 2 params)
        $ok = $this->projectModel->updateMemberRole($memberId, $role);

        if ($ok) {
            $_SESSION['success'] = 'Member role updated successfully.';
        } else {
            $_SESSION['error'] = 'Unable to update member role. Please try again.';
        }

        header('Location: ' . URLROOT . '/organization/members/' . $projectId);
        exit();
    }

    // ============================================================
    // EDIT MEMBER ROLE (Assigned to: Kithsara)
    // AJAX Request
    // ============================================================
    public function updateRole() {
        // Step 1: Ensure it's a POST request from the frontend
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        // Step 2: Grab the data sent from the AJAX request
        $memberId = $_POST['member_id'] ?? null;
        $newRole = $_POST['new_role'] ?? null;
        $projectId = $_POST['project_id'] ?? null;

        // Check if any data matches are missing
        if (!$memberId || !$newRole || !$projectId) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        // Step 3: Verify organization actually owns this project
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        // Step 4: Tell Database to update the role via Model
        $result = $this->projectModel->updateMemberRoleWithOrg($memberId, $newRole, $_SESSION['user_id']);

        // Step 5: Send Success or Error message back to frontend
        if ($result['success']) {
            $_SESSION['success'] = $result['message']; // Show green popup
            echo json_encode(['success' => true, 'message' => $result['message']]);
        } else {
            $_SESSION['error'] = $result['message']; // Show red popup
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
    }

    // ============================================================
    // REMOVE MEMBER FROM PROJECT (Assigned to: Kithsara)
    // AJAX Request
    // ============================================================
    public function removeMember() {
        header('Content-Type: application/json');
        
        // Ensure POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $projectId = $_POST['project_id'] ?? null;
        $memberId = $_POST['member_id'] ?? null;

        if (!$projectId || !$memberId) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        // Verify ownership
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        $res = $this->projectModel->removeMember($memberId, $projectId, $_SESSION['user_id']);
        echo json_encode($res);
        return;
    }

    /**
     * AJAX: Report a user for a project (organization reports a user)
     * POST: project_id, reported_user_id, reason, details
     */
    public function reportUser() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $projectId = $_POST['project_id'] ?? null;
        $reportedUserId = $_POST['reported_user_id'] ?? null;
        $reason = trim($_POST['reason'] ?? '');
        $details = trim($_POST['details'] ?? '');

        if (!$projectId || !$reportedUserId || empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        // Verify ownership
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        $res = $this->projectModel->createUserReport($projectId, $reportedUserId, $_SESSION['user_id'], $reason, $details);
        echo json_encode($res);
        return;
    }

    /* ============================================================
       TASK MANAGEMENT
    ============================================================ */
    public function tasks($projectId = null) {
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

        // Fetch tasks and related data
        $tasks = $this->taskModel->getTasksByProject($projectId);
        $members = $this->projectModel->getMembersByProject($projectId);
        $stats = $this->taskModel->getTaskStats($projectId);

        $data = [
            'title' => 'Task Manager - ' . $project->name,
            'project' => $project,
            'tasks' => $tasks,
            'members' => $members,
            'stats' => $stats,
            'projectId' => $projectId
        ];

        $this->view('organization/tasks', $data);
    }

    /* ============================================================
       PROFILE CRUD
    ============================================================ */
    
    public function updateProfile() {
        // Ensure this is a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method. Expected POST.']);
            exit();
        }

        // Ensure user is logged in and is organization
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $orgId = $_SESSION['user_id'];

        // Collect form data from all fields
        $orgName = isset($_POST['org_name']) ? trim($_POST['org_name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $website = isset($_POST['website']) ? trim($_POST['website']) : '';
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';
        $city = isset($_POST['city']) ? trim($_POST['city']) : '';
        $country = isset($_POST['country']) ? trim($_POST['country']) : '';
        $postalCode = isset($_POST['postal_code']) ? trim($_POST['postal_code']) : '';
        $linkedin = isset($_POST['linkedin']) ? trim($_POST['linkedin']) : '';
        $twitter = isset($_POST['twitter']) ? trim($_POST['twitter']) : '';
        $github = isset($_POST['github']) ? trim($_POST['github']) : '';

        // Validate required fields
        if (empty($orgName)) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Organization name is required']);
            exit();
        }

        // Update user profile in database with all fields
        $db = new Database();
        $db->query("UPDATE users SET 
                    username = :username,
                    email = :email,
                    bio = :bio,
                    phone = :phone,
                    website = :website,
                    address = :address,
                    city = :city,
                    country = :country,
                    postal_code = :postal_code,
                    linkedin = :linkedin,
                    twitter = :twitter,
                    github = :github
                    WHERE id = :id AND role = 'organization'");
        $db->bind(':id', $orgId);
        $db->bind(':username', $orgName);
        $db->bind(':email', $email);
        $db->bind(':bio', $description);
        $db->bind(':phone', $phone);
        $db->bind(':website', $website);
        $db->bind(':address', $address);
        $db->bind(':city', $city);
        $db->bind(':country', $country);
        $db->bind(':postal_code', $postalCode);
        $db->bind(':linkedin', $linkedin);
        $db->bind(':twitter', $twitter);
        $db->bind(':github', $github);

        header('Content-Type: application/json');
        
        if ($db->execute()) {
            // Update session username if changed
            $_SESSION['username'] = $orgName;
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'org_name' => $orgName,
                    'email' => $email,
                    'description' => $description,
                    'phone' => $phone,
                    'website' => $website,
                    'address' => $address,
                    'city' => $city,
                    'country' => $country,
                    'postal_code' => $postalCode,
                    'linkedin' => $linkedin,
                    'twitter' => $twitter,
                    'github' => $github
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update profile in database'
            ]);
        }
        exit();
    }

    public function getStats() {
        header('Content-Type: application/json');
        
        // Ensure user is logged in and is organization
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $orgId = $_SESSION['user_id'];
        $db = new Database();

        try {
            // Total projects
            $db->query("SELECT COUNT(*) as total FROM projects WHERE organization_id = :org_id");
            $db->bind(':org_id', $orgId);
            $totalProjects = $db->single()->total ?? 0;

            // Active projects
            $db->query("SELECT COUNT(*) as total FROM projects WHERE organization_id = :org_id AND status = 'active'");
            $db->bind(':org_id', $orgId);
            $activeProjects = $db->single()->total ?? 0;

            // Total applications
            $db->query("SELECT COUNT(*) as total FROM project_applications WHERE project_id IN (SELECT id FROM projects WHERE organization_id = :org_id)");
            $db->bind(':org_id', $orgId);
            $totalApplications = $db->single()->total ?? 0;

            // Total members
            $db->query("SELECT COUNT(DISTINCT user_id) as total FROM project_members WHERE project_id IN (SELECT id FROM projects WHERE organization_id = :org_id)");
            $db->bind(':org_id', $orgId);
            $totalMembers = $db->single()->total ?? 0;

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'total_projects' => (int)$totalProjects,
                    'active_projects' => (int)$activeProjects,
                    'total_applications' => (int)$totalApplications,
                    'total_members' => (int)$totalMembers
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching statistics: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    public function wallet() {
        require_once '../app/controllers/WalletController.php';
        $walletController = new WalletController();
        return $walletController->index();
    }

    // ============================================================
    // ASSIGN NEW TASK (Assigned to: Kithsara)
    // AJAX Request to give a task to a project member
    // ============================================================
    public function assignTask() {
        // Step 1: Clean output and set JSON header
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        
        try {
            // Step 2: Must be logged in
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized - Please login']);
                exit;
            }

            // Step 3: Get Form data or JSON payload sent via AJAX
            $data = $_POST;
            if (empty($data)) {
                $rawInput = file_get_contents('php://input');
                $decoded = json_decode($rawInput, true);
                $data = is_array($decoded) ? $decoded : [];
            }

            // Check required fields (member_id, task name, and project_id are a must)
            if (empty($data['member_id']) || empty($data['task_name']) || empty($data['project_id'])) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Missing required fields',
                    'received' => $data
                ]);
                exit;
            }

            $memberId = (int)$data['member_id'];
            $projectId = (int)$data['project_id'];
            
            // Step 4: Security - Project Exists? Does this Admin own the project?
            $project = $this->projectModel->getProjectById($projectId);
            
            if (!$project) {
                echo json_encode(['success' => false, 'message' => 'Project not found']);
                exit;
            }

            if ($project->organization_id != $_SESSION['user_id']) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Permission denied. Only organization owner can assign tasks'
                ]);
                exit;
            }

            // Step 5: Verify the selected user is actually an active member of THIS project
            $db = new Database();
            $db->query("SELECT id FROM project_members WHERE project_id = :project_id AND user_id = :user_id AND status = 'active' LIMIT 1");
            $db->bind(':project_id', $projectId);
            $db->bind(':user_id', $memberId);
            $memberRow = $db->single();

            if (!$memberRow) {
                echo json_encode(['success' => false, 'message' => 'User is not an active member of this project']);
                exit;
            }

            // Step 6: Package the exact values to send to Database task table
            $taskData = [
                'project_id' => $projectId,
                'assigned_to' => $memberId,
                'title' => $data['task_name'],
                'description' => $data['description'] ?? '',
                'priority' => strtolower($data['priority'] ?? 'medium'),
                'status' => 'todo',
                'deadline' => !empty($data['due_date']) ? $data['due_date'] : null,
                'buckx_allocated' => !empty($data['buckx_allocated']) ? (float)$data['buckx_allocated'] : 0
            ];

            // Send task data to Database Model to create
            $taskId = $this->taskModel->createTask($taskData);

            // Step 7: Notify User and return Success
            if ($taskId) {
                // Send an automated notification to the user who got the task
                try {
                    $this->notificationModel->createNotification([
                        'user_id'    => $memberId,
                        'type'       => 'task_assigned',
                        'message'    => 'You have been assigned a new task: ' . $taskData['title'],
                        'project_id' => $projectId,
                        'task_id'    => $taskId
                    ]);
                } catch (Throwable $e) {
                    // Ignore notification crashes so task creation still succeeds
                    error_log("Notification error: " . $e->getMessage());
                }

                // Return AJAX success!
                echo json_encode([
                    'success' => true, 
                    'message' => 'Task assigned successfully',
                    'task_id' => $taskId
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create task in database']);
            }
            
        } catch (Throwable $e) {
            error_log("Task assignment exception: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // ============================================================
    // DELETE TASK (Assigned to: Kithsara)
    // AJAX Request to delete a task completely
    // ============================================================
    public function removeTask() {
        ob_clean();
        header('Content-Type: application/json');

        try {
            // Step 1: Decode JSON request
            $rawInput = file_get_contents('php://input');
            $data     = json_decode($rawInput, true) ?: $_POST;
            $taskId   = (int)($data['task_id'] ?? 0);

            if (!$taskId) {
                echo json_encode(['success' => false, 'message' => 'Task ID required']);
                exit;
            }

            // Step 2: Fetch the task from Database
            $task = $this->taskModel->getTaskById($taskId);
            if (!$task) {
                echo json_encode(['success' => false, 'message' => 'Task not found']);
                exit;
            }

            // Step 3: Check if the person deleting the task actually owns the project it belongs to!
            $project = $this->projectModel->getProjectById($task->project_id);
            if (!$project || (int)$project->organization_id !== (int)$_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit;
            }

            // Step 4: Tell Database to delete the task!
            if ($this->taskModel->deleteTask($taskId)) {
                // If success, notify the assignee that the task was removed
                if (!empty($task->assigned_to)) {
                    try {
                        $this->notificationModel->createNotification([
                            'user_id'    => $task->assigned_to,
                            'type'       => 'task_removed',
                            'message'    => 'A task assigned to you was removed: ' . $task->title,
                            'project_id' => $task->project_id,
                            'task_id'    => null
                        ]);
                    } catch (Exception $e) {
                        error_log('Notify member on task remove: ' . $e->getMessage());
                    }
                }
                echo json_encode(['success' => true, 'message' => 'Task removed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove task']);
            }
        } catch (Exception $e) {
            error_log('removeTask error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }
         // category related skills map for validation and suggestions
    private function getCategorySkillMap()
    {
        return [
            'web' => [
                'Web Development',
                'Frontend Frameworks',
                'Backend Development',
                'Database Management',
                'GitHub and Git'
            ],
            'mobile' => [
                'Mobile App Development',
                'Frontend Frameworks',
                'Backend Development',
                'Database Management',
                'GitHub and Git'
            ],
            'data' => [
                'Data Science',
                'Data Analysis & Visualization',
                'Database Management',
                'AI and ML',
                'GitHub and Git'
            ],
            'design' => [
                'Web Development',
                'Frontend Frameworks',
                'Digital Marketing',
                'GitHub and Git'
            ],
            'other' => [
                'Cloud Computing',
                'Cybersecurity',
                'Devops',
                'AI and ML',
                'GitHub and Git'
            ]
        ];
    }

    private function normalizeSkillName($skill)
    {
        $skill = strtolower(trim((string)$skill));
        $skill = str_replace(['_', '-'], ' ', $skill);
        $skill = preg_replace('/\s+/', ' ', $skill);
        $skill = trim($skill);

        $aliases = [
            'db management' => 'database management',
            'database' => 'database management',
            'database development' => 'database management',
            'github & git' => 'github and git',
            'git and github' => 'github and git',
            'github' => 'github and git',
            'dev ops' => 'devops',
            'frontend' => 'frontend frameworks',
            'front end frameworks' => 'frontend frameworks',
            'backend' => 'backend development',
            'back end development' => 'backend development',
            'mobile' => 'mobile app development',
            'cloud' => 'cloud computing',
            'web dev' => 'web development',
            'ai' => 'ai and ml',
            'ai ml' => 'ai and ml',
            'marketing' => 'digital marketing',
            'data analytics' => 'data analysis & visualization',
            'data analysis and visualization' => 'data analysis & visualization'
        ];

        return $aliases[$skill] ?? $skill;
    }

    private function validateRequiredSkillsForCategory($category, $requiredSkillsCsv)
    {
        $categoryMap = $this->getCategorySkillMap();
        $allowedSkills = $categoryMap[$category] ?? [];

        $allowedByKey = [];
        foreach ($allowedSkills as $skillLabel) {
            $allowedByKey[$this->normalizeSkillName($skillLabel)] = $skillLabel;
        }

        $rawSkills = array_filter(array_map('trim', explode(',', (string)$requiredSkillsCsv)));
        $invalid = [];
        $validLabels = [];
        $seen = [];

        foreach ($rawSkills as $skill) {
            $key = $this->normalizeSkillName($skill);
            if ($key === '') {
                continue;
            }

            if (!isset($allowedByKey[$key])) {
                $invalid[] = $skill;
                continue;
            }

            if (!isset($seen[$key])) {
                $validLabels[] = $allowedByKey[$key];
                $seen[$key] = true;
            }
        }

        return [
            'valid' => empty($invalid) && !empty($validLabels),
            'invalid' => $invalid,
            'formatted' => implode(', ', $validLabels),
            'allowed' => $allowedSkills
        ];
    }

}
