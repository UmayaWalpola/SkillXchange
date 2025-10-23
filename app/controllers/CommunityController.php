<?php
// app/controllers/CommunityController.php

class CommunityController extends Controller {
    
    private $communityModel;

    public function __construct() {
        require_once "../app/models/Community.php";
        $this->communityModel = new Community();
    }

    // Main dashboard page - READ all communities
    public function index() {
        $communities = $this->communityModel->getAllCommunities();
        
        $data = [
            'title' => 'Community Dashboard',
            'communities' => $communities
        ];

        $this->view('cmmanager/dashboard', $data);
    }

    // Show community creation form
    public function create() {
        $data = [
            'title' => 'Create New Community',
            'community_name' => '',
            'description' => '',
            'privacy' => '',
            'name_err' => '',
            'description_err' => ''
        ];

        $this->view('cmmanager/community_create', $data);
    }

    // CREATE - Save new community
    public function store() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get JSON data from request
            $json = file_get_contents('php://input');
            $communityData = json_decode($json, true);

            // Sanitize inputs - REMOVED category
            $data = [
                'name' => trim($communityData['name'] ?? ''),
                'description' => trim($communityData['description'] ?? ''),
                'privacy' => trim($communityData['privacy'] ?? 'public'),
                'rules' => json_encode($communityData['rules'] ?? []),
                'tags' => json_encode($communityData['tags'] ?? []),
                'status' => trim($communityData['status'] ?? 'active'),
                'created_by' => $_SESSION['user_id'] ?? 1, // Default to 1 for testing
            ];

            // Validate inputs
            $errors = [];

            if(empty($data['name'])) {
                $errors[] = 'Community name is required';
            } elseif(strlen($data['name']) > 100) {
                $errors[] = 'Community name cannot exceed 100 characters';
            }

            // REMOVED category validation

            if(empty($data['description'])) {
                $errors[] = 'Description is required';
            } elseif(strlen($data['description']) > 1000) {
                $errors[] = 'Description cannot exceed 1000 characters';
            }

            if(!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }

            // Save to database
            if($this->communityModel->create($data)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Community created successfully!',
                    'redirect' => URLROOT . '/community'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'errors' => ['Failed to create community. Please try again.']
                ]);
            }
            exit;
        } else {
            header('Location: ' . URLROOT . '/community');
            exit;
        }
    }

    // READ - Get all communities (API endpoint)
    public function getAll() {
        $communities = $this->communityModel->getAllCommunities();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $communities]);
        exit;
    }

    // Show edit form
    public function edit($id) {
        $community = $this->communityModel->getCommunityById($id);
        
        if(!$community) {
            header('Location: ' . URLROOT . '/community');
            exit;
        }

        $data = [
            'title' => 'Edit Community',
            'community' => $community
        ];

        $this->view('cmmanager/community_edit', $data);
    }

    // UPDATE - Update existing community
    public function update($id) {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $json = file_get_contents('php://input');
            $communityData = json_decode($json, true);

            // Check if community exists
            $community = $this->communityModel->getCommunityById($id);
            if(!$community) {
                echo json_encode(['success' => false, 'errors' => ['Community not found']]);
                exit;
            }

            // Sanitize inputs - REMOVED category
            $data = [
                'id' => $id,
                'name' => trim($communityData['name'] ?? ''),
                'description' => trim($communityData['description'] ?? ''),
                'privacy' => trim($communityData['privacy'] ?? 'public'),
                'rules' => json_encode($communityData['rules'] ?? []),
                'tags' => json_encode($communityData['tags'] ?? []),
                'status' => trim($communityData['status'] ?? 'active')
            ];

            // Validate inputs
            $errors = [];

            if(empty($data['name'])) {
                $errors[] = 'Community name is required';
            }

            // REMOVED category validation

            if(empty($data['description'])) {
                $errors[] = 'Description is required';
            }

            if(!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }

            // Update in database
            if($this->communityModel->update($data)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Community updated successfully!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'errors' => ['Failed to update community']
                ]);
            }
            exit;
        } else {
            header('Location: ' . URLROOT . '/community');
            exit;
        }
    }

    // UPDATE - Toggle community status
    public function toggleStatus() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if(empty($data['id'])) {
                echo json_encode(['success' => false, 'message' => 'Community ID is required']);
                exit;
            }

            $community = $this->communityModel->getCommunityById($data['id']);
            if(!$community) {
                echo json_encode(['success' => false, 'message' => 'Community not found']);
                exit;
            }

            $newStatus = $community->status === 'active' ? 'inactive' : 'active';
            
            if($this->communityModel->updateStatus($data['id'], $newStatus)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Community status updated successfully!',
                    'status' => $newStatus
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update status']);
            }
            exit;
        }
    }

    // DELETE - Remove community
    public function delete() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if(empty($data['id'])) {
                echo json_encode(['success' => false, 'message' => 'Community ID is required']);
                exit;
            }

            // Check if community exists
            $community = $this->communityModel->getCommunityById($data['id']);
            if(!$community) {
                echo json_encode(['success' => false, 'message' => 'Community not found']);
                exit;
            }

            // Delete from database
            if($this->communityModel->delete($data['id'])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Community deleted successfully!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete community'
                ]);
            }
            exit;
        } else {
            header('Location: ' . URLROOT . '/community');
            exit;
        }
    }

    // Get dashboard stats
    public function getStats() {
        $stats = $this->communityModel->getStats();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $stats]);
        exit;
    }
}