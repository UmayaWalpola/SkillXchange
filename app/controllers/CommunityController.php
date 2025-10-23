<?php
// app/controllers/CommunityController.php

class CommunityController extends Controller {


    public function __construct() {
        // TEMPORARILY DISABLED - Authentication will be added later
        /*
        if(!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/users/login');
            exit;
        }

        if($_SESSION['user_role'] !== 'manager') {
            header('Location: ' . URLROOT . '/pages/index');
            exit;
        }
        */
    }

    // Main dashboard page
    public function index() {
        

        $this->view('cmmanager/dashboard', );


    }

    // Show community creation form
    public function create() {
        $data = [
            'title' => 'Create New Community',
            'community_name' => '',
            'category' => '',
            'description' => '',
            'name_err' => '',
            'category_err' => '',
            'description_err' => ''
        ];

        $this->view('cmmanager/community_create', $data);
    }

    // Save community (create or update)
    public function save() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get JSON data from request
            $json = file_get_contents('php://input');
            $communityData = json_decode($json, true);

            // Validate inputs
            $errors = [];

            if(empty($communityData['name'])) {
                $errors[] = 'Community name is required';
            }

            if(empty($communityData['category'])) {
                $errors[] = 'Category is required';
            }

            if(empty($communityData['description'])) {
                $errors[] = 'Description is required';
            }

            if(!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }

            // TODO: Save to database when ready
            echo json_encode([
                'success' => true,
                'message' => 'Community saved successfully!',
                'status' => $communityData['status'] ?? 'active'
            ]);
            exit;
        } else {
            header('Location: ' . URLROOT . '/cmanager');
            exit;
        }
    }

    // View community details
    

    // Delete community
    public function delete() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if(empty($data['id'])) {
                echo json_encode(['success' => false, 'message' => 'Community ID is required']);
                exit;
            }

            // TODO: Delete from database later
            echo json_encode(['success' => true, 'message' => 'Community deleted successfully!']);
            exit;
        } else {
            header('Location: ' . URLROOT . '/cmmanager');
            exit;
        }
    }
}

