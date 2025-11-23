<?php
// app/controllers/OrganizationController.php

class OrganizationController extends Controller {
    
    private $projectModel;
    private $taskModel;
    private $notificationModel;

    public function __construct() {

        // Require login + role check
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
            header('Location: ' . URLROOT . '/auth/signin');
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

    /* ============================================================
       PROJECT LIST PAGE
    ============================================================ */
    public function projects() {

        $projects = $this->projectModel->getProjectsByOrganization($_SESSION['user_id']);

        $data = [
            'title' => 'My Projects',
            'projects' => $projects
        ];

        $this->view('organization/projects', $data);
    }

    /* ============================================================
       CREATE PROJECT (GET + POST)
    ============================================================ */
    public function createProject() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $errors = [];

            if (empty(trim($_POST['name']))) {
                $errors['name'] = 'Project name is required';
            }

            if (empty(trim($_POST['description']))) {
                $errors['description'] = 'Project description is required';
            }

            if (empty(trim($_POST['required_skills']))) {
                $errors['required_skills'] = 'Please specify required skills';
            }

            if (empty($_POST['category'])) {
                $errors['category'] = 'Project category is required';
            }

            if (empty($_POST['max_members']) || $_POST['max_members'] < 1) {
                $errors['max_members'] = 'Max members must be at least 1';
            }

            if (empty($errors)) {

                $projectData = [
                    'org_id'          => $_SESSION['user_id'],
                    'name'            => trim($_POST['name']),
                    'category'        => trim($_POST['category']),
                    'status'          => !empty($_POST['status']) ? trim($_POST['status']) : 'active',
                    'description'     => trim($_POST['description']),
                    'max_members'     => (int)$_POST['max_members'],
                    'start_date'      => $_POST['start_date'] ?? null,
                    'end_date'        => $_POST['end_date'] ?? null,
                    'required_skills' => trim($_POST['required_skills']) // NO htmlspecialchars()
                ];

                if ($this->projectModel->createProject($projectData)) {
                    $_SESSION['success'] = 'Project created successfully!';
                    header('Location: ' . URLROOT . '/organization/projects');
                    exit();
                }

                $_SESSION['error'] = 'Failed to create project. Try again.';
            }

            $_SESSION['errors'] = $errors;
        }

        $data = [
            'title' => 'Create Project',
            'errors' => $_SESSION['errors'] ?? [],
            'project' => null
        ];

        unset($_SESSION['errors']);

        $this->view('organization/createProject', $data);
    }

    /* ============================================================
       EDIT PROJECT (GET + POST)
    ============================================================ */
    public function editProject($projectId = null) {

        if (!$projectId) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        $project = $this->projectModel->getProjectById($projectId);

        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Project not found or access denied';
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $errors = [];

            if (empty(trim($_POST['name']))) {
                $errors['name'] = 'Project name is required';
            }

            if (empty(trim($_POST['description']))) {
                $errors['description'] = 'Description required';
            }

            if (empty(trim($_POST['required_skills']))) {
                $errors['required_skills'] = 'Required skills missing';
            }

            if (empty($errors)) {

                $projectData = [
                    'id'              => $projectId,
                    'org_id'          => $_SESSION['user_id'],
                    'name'            => trim($_POST['name']),
                    'category'        => trim($_POST['category']),
                    'status'          => trim($_POST['status']),
                    'description'     => trim($_POST['description']),
                    'max_members'     => (int)$_POST['max_members'],
                    'start_date'      => $_POST['start_date'] ?? null,
                    'end_date'        => $_POST['end_date'] ?? null,
                    'required_skills' => trim($_POST['required_skills'])
                ];

                if ($this->projectModel->updateProject($projectData)) {
                    $_SESSION['success'] = 'Project updated!';
                    header('Location: ' . URLROOT . '/organization/projects');
                    exit();
                }

                $_SESSION['error'] = 'Failed to update project.';
            }

            $_SESSION['errors'] = $errors;
        }

        $data = [
            'title' => 'Edit Project',
            'project' => $project,
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['errors']);

        // Reuse createProject view (edit mode)
        $this->view('organization/createProject', $data);
    }

    /* ============================================================
       DELETE PROJECT (AJAX)
    ============================================================ */
    public function deleteProject() {

        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $projectId = $_POST['project_id'] ?? null;

        if (!$projectId) {
            echo json_encode(['success' => false, 'message' => 'Project ID missing']);
            return;
        }

        $project = $this->projectModel->getProjectById($projectId);

        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        if ($this->projectModel->deleteProject($projectId, $_SESSION['user_id'])) {
            echo json_encode(['success' => true, 'message' => 'Project deleted']);
            return;
        }

        echo json_encode(['success' => false, 'message' => 'Failed to delete']);
    }


    /* ============================================================
       APPLICATIONS PAGE
    ============================================================ */
    public function applications() {
        // Fetch all applications across this organization's projects
        $applications = $this->projectModel->getAllApplicationsForOrganization($_SESSION['user_id']);
        $stats = $this->projectModel->getApplicationStats($_SESSION['user_id']);

        $data = [
            'title' => 'Project Applications',
            'org_id' => $_SESSION['user_id'],
            'applications' => $applications,
            'stats' => $stats
        ];

        $this->view('organization/applications', $data);
    }

    /* ============================================================
       CHATS PAGE
    ============================================================ */
    public function chats() {
        $data = [
            'title' => 'Project Chats',
            'org_id' => $_SESSION['user_id']
        ];

        $this->view('organization/chats', $data);
    }

    // Handle a single application action (accept / reject)
    // POST: /organization/handleApplication/{id}/{action}
    public function handleApplication($applicationId = null, $action = null)
    {
        // Allow GET (anchor links) or POST
        if (!$applicationId || !in_array($action, ['accept','reject'])) {
            $_SESSION['error'] = 'Invalid action.';
            header('Location: ' . URLROOT . '/organization/applications');
            exit();
        }

        $org_id = $_SESSION['user_id'];

        if ($action === 'accept') {
            $res = $this->projectModel->acceptApplication($applicationId, $org_id);
        } else {
            $res = $this->projectModel->rejectApplication($applicationId, $org_id);
        }

        if (is_array($res)) {
            if (!empty($res['success'])) {
                $_SESSION['success'] = $res['message'] ?? 'Action completed.';
            } else {
                $_SESSION['error'] = $res['message'] ?? 'Action failed.';
            }
        } else {
            // fallback boolean
            if ($res) $_SESSION['success'] = 'Action completed.';
            else $_SESSION['error'] = 'Action failed.';
        }

        header('Location: ' . URLROOT . '/organization/applications');
        exit();
    }

    /* ============================================================
       MEMBERS & ROLE ASSIGNMENT
    ============================================================ */

    public function members($projectId = null) {
        if (!$projectId) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        // Verify organization owns this project
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Project not found or access denied.';
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }

        // Get all active members for this project
        $members = $this->projectModel->getMembersByProject($projectId);

        $data = [
            'title' => 'Manage Members - ' . $project->name,
            'project' => $project,
            'members' => $members,
            'projectId' => $projectId
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

    public function updateRole() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $memberId = $_POST['member_id'] ?? null;
        $newRole = $_POST['new_role'] ?? null;
        $projectId = $_POST['project_id'] ?? null;

        if (!$memberId || !$newRole || !$projectId) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        // Verify organization owns the project
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        // Update the role
        $result = $this->projectModel->updateMemberRoleWithOrg($memberId, $newRole, $_SESSION['user_id']);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            echo json_encode(['success' => true, 'message' => $result['message']]);
        } else {
            $_SESSION['error'] = $result['message'];
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
    }

    /**
     * AJAX: Remove a member from the project (owner only)
     * POST: project_id, member_id
     */
    public function removeMember() {
        header('Content-Type: application/json');
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

}

?>
