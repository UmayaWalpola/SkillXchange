<?php
// app/controllers/OrganizationController.php

class OrganizationController extends Controller {
    private $organizationModel;
    private $projectModel;
    
    public function __construct() {
        // Initialize models (uncomment when models are ready)
        // $this->organizationModel = $this->model('Organization');
        // $this->projectModel = $this->model('Project');
        
        // Check if user is logged in and is organization type
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organization') {
            header('Location: ' . URLROOT . '/login');
            exit();
        }
    }
    
    // Profile page (Landing page for organizations)
    public function profile() {
        $data = [
            'title' => 'Organization Profile',
            'org_id' => $_SESSION['user_id']
            // TODO: Fetch organization data from database
            // 'organization' => $this->organizationModel->getOrganizationById($_SESSION['user_id'])
        ];
        
        $this->view('organization/profile', $data);
    }
    
    // Projects management page
    public function projects() {
        $data = [
            'title' => 'My Projects',
            'org_id' => $_SESSION['user_id']
            // TODO: Fetch projects
            // 'projects' => $this->projectModel->getProjectsByOrganization($_SESSION['user_id'])
        ];
        
        $this->view('organization/projects', $data);
    }
    
    // Applications management page
    public function applications() {
        $data = [
            'title' => 'Project Applications',
            'org_id' => $_SESSION['user_id']
            // TODO: Fetch applications
            // 'applications' => $this->projectModel->getApplicationsByOrganization($_SESSION['user_id'])
        ];
        
        $this->view('organization/applications', $data);
    }
    
    // Project chats page
    public function chats() {
        $data = [
            'title' => 'Project Chats',
            'org_id' => $_SESSION['user_id']
            // TODO: Fetch chats
            // 'projects' => $this->projectModel->getProjectsWithChats($_SESSION['user_id'])
        ];
        
        $this->view('organization/chats', $data);
    }
    
    // Update organization profile (AJAX)
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'org_id' => $_SESSION['user_id'],
                'org_name' => trim($_POST['org_name']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']),
                'website' => trim($_POST['website']),
                'description' => trim($_POST['description']),
                'address' => trim($_POST['address']),
                'city' => trim($_POST['city']),
                'country' => trim($_POST['country']),
                'postal_code' => trim($_POST['postal_code']),
                'linkedin' => trim($_POST['linkedin']),
                'twitter' => trim($_POST['twitter']),
                'github' => trim($_POST['github'])
            ];
            
            // TODO: Update in database
            // if ($this->organizationModel->updateProfile($data)) {
            //     echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
            // } else {
            //     echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
            // }
            
            // Temporary response
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        }
    }
    
    // Get organization statistics (AJAX)
    public function getStats() {
        // TODO: Fetch real stats from database
        $stats = [
            'total_projects' => 0,
            'active_projects' => 0,
            'total_applications' => 0,
            'pending_applications' => 0,
            'total_members' => 0
        ];
        
        echo json_encode(['success' => true, 'data' => $stats]);
    }
    
    // Handle application action (AJAX)
    public function handleApplication() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $applicationId = $_POST['application_id'] ?? null;
            $action = $_POST['action'] ?? null; // 'accept' or 'reject'
            
            if (!$applicationId || !$action) {
                echo json_encode(['success' => false, 'message' => 'Missing parameters']);
                exit();
            }
            
            // TODO: Update application status in database
            // $result = $this->projectModel->updateApplicationStatus($applicationId, $action);
            
            echo json_encode(['success' => true, 'message' => 'Application ' . $action . 'ed successfully']);
        }
    }
    
    // Send message in project chat (AJAX)
    public function sendMessage() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $projectId = $_POST['project_id'] ?? null;
            $message = $_POST['message'] ?? null;
            
            if (!$projectId || !$message) {
                echo json_encode(['success' => false, 'message' => 'Missing parameters']);
                exit();
            }
            
            // TODO: Save message to database
            // $result = $this->projectModel->saveMessage($projectId, $_SESSION['user_id'], $message);
            
            echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
        }
    }
    
    // Get messages for a project (AJAX)
    public function getMessages($projectId) {
        // TODO: Fetch messages from database
        // $messages = $this->projectModel->getProjectMessages($projectId);
        
        $messages = [];
        
        echo json_encode(['success' => true, 'data' => $messages]);
    }
}
?>