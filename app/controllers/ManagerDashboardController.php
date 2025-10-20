<?php
/**
 * Manager Dashboard Controller
 * Handles all manager-related operations
 */

class ManagerDashboardController extends Controller {
    
    public function __construct() {
        // Check if user is logged in (commented out for now - no auth yet)
        // if (!isset($_SESSION['user_id'])) {
        //     header('Location: /auth/login');
        //     exit;
        // }
    }
    
    /**
     * Manager Dashboard Index/Overview
     */
    public function index() {
        // Mock statistics data
        $stats = [
            'total_organizations' => 45,
            'total_users' => 1250,
            'total_admins' => 12,
            'total_announcements' => 8
        ];
        
        $data = [
            'title' => 'Manager Dashboard',
            'page' => 'dashboard',
            'stats' => $stats
        ];
        
        $this->view('managerdashboard/index', $data);
    }
    
    /**
     * Organizations Management Page
     */
    public function organizations() {
        // Mock organizations data - all registered organizations
        $organizations = [
            [
                'id' => 1,
                'name' => 'Tech Innovators Inc',
                'email' => 'contact@techinnovators.com',
                'phone' => '+94 77 123 4567',
                'address' => '123 Tech Street, Colombo',
                'created_at' => '2025-01-15'
            ],
            [
                'id' => 2,
                'name' => 'Design Studio Pro',
                'email' => 'hello@designstudio.com',
                'phone' => '+94 71 987 6543',
                'address' => '456 Creative Lane, Kandy',
                'created_at' => '2025-01-10'
            ],
            [
                'id' => 3,
                'name' => 'Creative Minds LLC',
                'email' => 'info@creativeminds.com',
                'phone' => '+94 76 555 8888',
                'address' => '789 Innovation Road, Galle',
                'created_at' => '2025-01-08'
            ],
            [
                'id' => 4,
                'name' => 'Digital Solutions',
                'email' => 'contact@digitalsolutions.com',
                'phone' => '+94 75 444 9999',
                'address' => '321 Digital Ave, Colombo',
                'created_at' => '2025-01-05'
            ],
            [
                'id' => 5,
                'name' => 'Startup Hub',
                'email' => 'admin@startuphub.com',
                'phone' => '+94 77 222 3333',
                'address' => '654 Startup Plaza, Negombo',
                'created_at' => '2025-01-03'
            ]
        ];
        
        $data = [
            'title' => 'Organizations Management',
            'page' => 'organizations',
            'organizations' => $organizations
        ];
        
        $this->view('managerdashboard/organizations', $data);
    }
    
    /**
     * Remove Organization (AJAX endpoint)
     */
    public function removeOrganization() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $orgId = $_POST['org_id'] ?? null;
            
            // TODO: Implement actual database deletion
            // For now, return success
            echo json_encode(['success' => true, 'message' => 'Organization removed successfully']);
        }
    }
    
    /**
     * User Management Page
     */
    public function users() {
        // Mock users data
        $users = [
            [
                'id' => 1,
                'name' => 'John Admin',
                'email' => 'john@admin.com',
                'role' => 'Admin',
                'created_at' => '2024-12-15'
            ],
            [
                'id' => 2,
                'name' => 'Sarah Quiz Manager',
                'email' => 'sarah@quizmanager.com',
                'role' => 'Quiz Manager',
                'created_at' => '2024-12-20'
            ],
            [
                'id' => 3,
                'name' => 'Mike Community Admin',
                'email' => 'mike@community.com',
                'role' => 'Community Admin',
                'created_at' => '2025-01-05'
            ]
        ];
        
        $data = [
            'title' => 'User Management',
            'page' => 'users',
            'users' => $users
        ];
        
        $this->view('managerdashboard/users', $data);
    }
    
    /**
     * Add User (AJAX endpoint)
     */
    public function addUser() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $role = $_POST['role'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // TODO: Implement actual database insertion with password hashing
            // For now, return success
            echo json_encode(['success' => true, 'message' => 'User added successfully']);
        }
    }
    
    /**
     * Remove User (AJAX endpoint)
     */
    public function removeUser() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userId = $_POST['user_id'] ?? null;
            
            // TODO: Implement actual database deletion
            // For now, return success
            echo json_encode(['success' => true, 'message' => 'User removed successfully']);
        }
    }
    
    /**
     * Announcements Page
     */
    public function announcements() {
        // Mock announcements data
        $announcements = [
            [
                'id' => 1,
                'title' => 'Platform Update v2.0',
                'content' => 'We are excited to announce new features including improved quiz engine and community forums.',
                'created_at' => '2025-01-10',
                'author' => 'System Manager'
            ],
            [
                'id' => 2,
                'title' => 'Scheduled Maintenance',
                'content' => 'The platform will undergo maintenance on January 25th from 2 AM to 4 AM.',
                'created_at' => '2025-01-08',
                'author' => 'System Manager'
            ],
            [
                'id' => 3,
                'title' => 'New Quiz Categories Available',
                'content' => 'Check out our newly added quiz categories in Science and Technology!',
                'created_at' => '2025-01-05',
                'author' => 'Quiz Team'
            ]
        ];
        
        $data = [
            'title' => 'System Announcements',
            'page' => 'announcements',
            'announcements' => $announcements
        ];
        
        $this->view('managerdashboard/announcements', $data);
    }
    
    /**
     * Add Announcement (AJAX endpoint)
     */
    public function addAnnouncement() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            
            // TODO: Implement actual database insertion
            // For now, return success
            echo json_encode(['success' => true, 'message' => 'Announcement posted successfully']);
        }
    }
    
    /**
     * Delete Announcement (AJAX endpoint)
     */
    public function deleteAnnouncement() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $announcementId = $_POST['announcement_id'] ?? null;
            
            // TODO: Implement actual database deletion
            // For now, return success
            echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
        }
    }
    
    /**
     * Feedback/Suggestions Page
     */
    public function feedback() {
        // Mock feedback data
        $feedbacks = [
            [
                'id' => 1,
                'user_name' => 'Alice Johnson',
                'user_email' => 'alice@example.com',
                'subject' => 'Great Platform!',
                'message' => 'I love the quiz features. Very intuitive and easy to use.',
                'rating' => 5,
                'created_at' => '2025-01-12',
                'status' => 'new'
            ],
            [
                'id' => 2,
                'user_name' => 'Bob Smith',
                'user_email' => 'bob@example.com',
                'subject' => 'Suggestion for Improvement',
                'message' => 'Would be great to have a dark mode option for better night viewing.',
                'rating' => 4,
                'created_at' => '2025-01-10',
                'status' => 'reviewed'
            ],
            [
                'id' => 3,
                'user_name' => 'Carol Williams',
                'user_email' => 'carol@example.com',
                'subject' => 'Bug Report',
                'message' => 'Sometimes the quiz timer does not start correctly on mobile devices.',
                'rating' => 3,
                'created_at' => '2025-01-08',
                'status' => 'new'
            ],
            [
                'id' => 4,
                'user_name' => 'David Brown',
                'user_email' => 'david@example.com',
                'subject' => 'Excellent Experience',
                'message' => 'The community forum is very helpful. Keep up the good work!',
                'rating' => 5,
                'created_at' => '2025-01-05',
                'status' => 'reviewed'
            ]
        ];
        
        $data = [
            'title' => 'Platform Feedback',
            'page' => 'feedback',
            'feedbacks' => $feedbacks
        ];
        
        $this->view('managerdashboard/feedback', $data);
    }
    
    /**
     * Mark Feedback as Reviewed (AJAX endpoint)
     */
    public function markFeedbackReviewed() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $feedbackId = $_POST['feedback_id'] ?? null;
            
            // TODO: Implement actual database update
            // For now, return success
            echo json_encode(['success' => true, 'message' => 'Feedback marked as reviewed']);
        }
    }
    
} // End of ManagerDashboardController class