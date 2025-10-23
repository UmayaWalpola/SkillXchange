<?php
// app/controllers/OrganizationController.php

class OrganizationController extends Controller {
    private $organizationModel;
    private $projectModel;
    
    public function __construct() {
        // Check if user is logged in and is organization type
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
            header('Location: ' . URLROOT . '/auth/signin');
            exit();
        }
        
        // Initialize models
        $this->projectModel = $this->model('Project');
        // $this->organizationModel = $this->model('Organization');
    }
    
    // Default method - redirect to profile
    public function index() {
        $this->profile();
    }
    
    // Profile page
    public function profile() {
        $data = [
            'title' => 'Organization Profile',
            'org_id' => $_SESSION['user_id']
        ];
        
        // Temporary mock data
        $data['organization'] = (object)[
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['username'],
            'email' => '',
            'phone' => '',
            'website' => '',
            'description' => '',
            'address' => '',
            'city' => '',
            'country' => '',
            'postal_code' => '',
            'linkedin' => '',
            'twitter' => '',
            'github' => ''
        ];
        
        // Get real stats from database
        $stats = $this->projectModel->getOrganizationStats($_SESSION['user_id']);
        $data['stats'] = $stats ?: (object)[
            'total_projects' => 0,
            'active_projects' => 0,
            'in_progress_projects' => 0,
            'completed_projects' => 0
        ];
        
        $this->view('organization/profile', $data);
    }
    
    // Projects management page
    public function projects() {
        $data = [
            'title' => 'My Projects',
            'org_id' => $_SESSION['user_id']
        ];
        
        // Fetch projects from database
        $data['projects'] = $this->projectModel->getProjectsByOrganization($_SESSION['user_id']);
        
        $this->view('organization/projects', $data);
    }
    
    // Create project page
    public function createProject() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validate input
            $errors = [];
            
            if (empty(trim($_POST['name']))) {
                $errors['name'] = 'Project name is required';
            }
            
            if (empty(trim($_POST['description']))) {
                $errors['description'] = 'Description is required';
            }
            
            if (empty($_POST['category'])) {
                $errors['category'] = 'Category is required';
            }
            
            if (empty($_POST['max_members']) || $_POST['max_members'] < 1) {
                $errors['max_members'] = 'Valid max members is required';
            }
            
            if (empty($errors)) {
                $projectData = [
                    'org_id' => $_SESSION['user_id'],
                    'name' => trim(htmlspecialchars($_POST['name'])),
                    'category' => trim(htmlspecialchars($_POST['category'])),
                    'status' => !empty($_POST['status']) ? trim(htmlspecialchars($_POST['status'])) : 'active',
                    'description' => trim(htmlspecialchars($_POST['description'])),
                    'max_members' => (int)$_POST['max_members'],
                    'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
                    'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                    'required_skills' => trim(htmlspecialchars($_POST['required_skills']))
                ];
                
                // Save to database
                if ($this->projectModel->createProject($projectData)) {
                    $_SESSION['success'] = 'Project created successfully!';
                    header('Location: ' . URLROOT . '/organization/projects');
                    exit();
                } else {
                    $_SESSION['error'] = 'Failed to create project. Please try again.';
                }
            } else {
                $_SESSION['errors'] = $errors;
            }
        }
        
        $data = [
            'title' => 'Create Project',
            'errors' => $_SESSION['errors'] ?? [],
            'project' => null
        ];
        
        unset($_SESSION['errors']);
        $this->view('organization/createProject', $data);
    }
    
    // Edit project
    public function editProject($projectId = null) {
        if (!$projectId) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }
        
        // Get project details
        $project = $this->projectModel->getProjectById($projectId);
        
        // Check if project exists and belongs to this organization
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Project not found or access denied';
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $errors = [];
            
            if (empty(trim($_POST['name']))) {
                $errors['name'] = 'Project name is required';
            }
            
            if (empty(trim($_POST['description']))) {
                $errors['description'] = 'Description is required';
            }
            
            if (empty($errors)) {
                $projectData = [
                    'id' => $projectId,
                    'org_id' => $_SESSION['user_id'],
                    'name' => trim(htmlspecialchars($_POST['name'])),
                    'category' => trim(htmlspecialchars($_POST['category'])),
                    'status' => trim(htmlspecialchars($_POST['status'])),
                    'description' => trim(htmlspecialchars($_POST['description'])),
                    'max_members' => (int)$_POST['max_members'],
                    'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
                    'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                    'required_skills' => trim(htmlspecialchars($_POST['required_skills']))
                ];
                
                if ($this->projectModel->updateProject($projectData)) {
                    $_SESSION['success'] = 'Project updated successfully!';
                    header('Location: ' . URLROOT . '/organization/projects');
                    exit();
                } else {
                    $_SESSION['error'] = 'Failed to update project';
                }
            } else {
                $_SESSION['errors'] = $errors;
            }
        }
        
        $data = [
            'title' => 'Edit Project',
            'project' => $project,
            'errors' => $_SESSION['errors'] ?? []
        ];
        
        unset($_SESSION['errors']);
        $this->view('organization/createProject', $data);
    }
    
    // Delete project (AJAX)
    public function deleteProject() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $projectId = $_POST['project_id'] ?? null;
            
            if (!$projectId) {
                echo json_encode(['success' => false, 'message' => 'Project ID required']);
                exit();
            }
            
            // Verify ownership
            $project = $this->projectModel->getProjectById($projectId);
            if (!$project || $project->organization_id != $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit();
            }
            
            if ($this->projectModel->deleteProject($projectId, $_SESSION['user_id'])) {
                echo json_encode(['success' => true, 'message' => 'Project deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete project']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
        }
    }
    
    // View specific project
    public function viewProject($projectId = null) {
        if (!$projectId) {
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }
        
        $project = $this->projectModel->getProjectById($projectId);
        
        if (!$project || $project->organization_id != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Project not found';
            header('Location: ' . URLROOT . '/organization/projects');
            exit();
        }
        
        $data = [
            'title' => 'View Project',
            'project' => $project
        ];
        
        $this->view('organization/viewProject', $data);
    }
    
    // Applications management page
    public function applications() {
        $data = [
            'title' => 'Project Applications',
            'org_id' => $_SESSION['user_id'],
            'applications' => []
        ];
        
        $this->view('organization/applications', $data);
    }
    
    // Project chats page
    public function chats() {
        $data = [
            'title' => 'Project Chats',
            'org_id' => $_SESSION['user_id'],
            'projects' => []
        ];
        
        $this->view('organization/chats', $data);
    }
    
    // Update organization profile (AJAX)
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'org_id' => $_SESSION['user_id'],
                'org_name' => trim(htmlspecialchars($_POST['org_name'] ?? '')),
                'email' => trim(htmlspecialchars($_POST['email'] ?? '')),
                'phone' => trim(htmlspecialchars($_POST['phone'] ?? '')),
                'website' => trim(htmlspecialchars($_POST['website'] ?? '')),
                'description' => trim(htmlspecialchars($_POST['description'] ?? '')),
                'address' => trim(htmlspecialchars($_POST['address'] ?? '')),
                'city' => trim(htmlspecialchars($_POST['city'] ?? '')),
                'country' => trim(htmlspecialchars($_POST['country'] ?? '')),
                'postal_code' => trim(htmlspecialchars($_POST['postal_code'] ?? '')),
                'linkedin' => trim(htmlspecialchars($_POST['linkedin'] ?? '')),
                'twitter' => trim(htmlspecialchars($_POST['twitter'] ?? '')),
                'github' => trim(htmlspecialchars($_POST['github'] ?? ''))
            ];
            
            if (!empty($data['org_name'])) {
                $_SESSION['username'] = $data['org_name'];
            }
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        }
    }
    
    // Get organization statistics (AJAX)
    public function getStats() {
        $stats = $this->projectModel->getOrganizationStats($_SESSION['user_id']);
        
        if ($stats) {
            echo json_encode(['success' => true, 'data' => $stats]);
        } else {
            echo json_encode([
                'success' => true, 
                'data' => [
                    'total_projects' => 0,
                    'active_projects' => 0,
                    'in_progress_projects' => 0,
                    'completed_projects' => 0
                ]
            ]);
        }
    }
}
?>