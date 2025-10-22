<?php
class AdminController extends Controller {

    private $userModel;

    public function __construct() {
        // Check if user is logged in and is admin
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }

        if ($_SESSION['role'] !== 'admin') {
            header('Location: ' . URLROOT . '/home');
            exit;
        }

        $this->userModel = $this->model('User');
    }

    // Default method - show admin dashboard
    public function index() {
        $this->dashboard();
    }

    // Admin Dashboard
    public function dashboard() {
        // Get statistics
        $stats = $this->getAdminStats();
        
        // Get recent users (limit 5 for dashboard)
        $recentUsers = $this->getRecentUsers(5);
        
        // Get popular skills
        $popularSkills = $this->getPopularSkills(6);
        
        // Get recent reports (placeholder for now)
        $recentReports = [];

        $data = [
            'stats' => $stats,
            'recent_users' => $recentUsers,
            'popular_skills' => $popularSkills,
            'recent_reports' => $recentReports
        ];

        $this->view('users/admin', $data);
    }

    // Get admin statistics
    private function getAdminStats() {
        try {
            $db = new Database();
            
            // Total users (exclude admins)
            $db->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
            $totalUsers = $db->single()['total'] ?? 0;
            
            // Total unique skills
            $db->query("SELECT COUNT(DISTINCT skill_name) as total FROM user_skills");
            $totalSkills = $db->single()['total'] ?? 0;
            
            // Active exchanges (placeholder - set to 0 for now)
            $activeExchanges = 0;
            
            // Completed exchanges (placeholder - set to 0 for now)
            $completedExchanges = 0;

            return [
                'total_users' => $totalUsers,
                'active_exchanges' => $activeExchanges,
                'completed_exchanges' => $completedExchanges,
                'total_skills' => $totalSkills
            ];
        } catch (Exception $e) {
            error_log("Admin stats error: " . $e->getMessage());
            return [
                'total_users' => 0,
                'active_exchanges' => 0,
                'completed_exchanges' => 0,
                'total_skills' => 0
            ];
        }
    }

    // Get recent users
    private function getRecentUsers($limit = 10) {
        try {
            $db = new Database();
            $db->query("SELECT id, username, email, created_at, role 
                       FROM users 
                       WHERE role != 'admin' 
                       ORDER BY created_at DESC 
                       LIMIT :limit");
            $db->bind(':limit', $limit);
            return $db->resultSet();
        } catch (Exception $e) {
            error_log("Recent users error: " . $e->getMessage());
            return [];
        }
    }

    // Get popular skills
    private function getPopularSkills($limit = 6) {
        try {
            $db = new Database();
            $db->query("SELECT 
                           skill_name,
                           SUM(CASE WHEN skill_type = 'teach' THEN 1 ELSE 0 END) as teachers,
                           SUM(CASE WHEN skill_type = 'learn' THEN 1 ELSE 0 END) as learners,
                           COUNT(*) as total
                       FROM user_skills 
                       GROUP BY skill_name 
                       ORDER BY total DESC 
                       LIMIT :limit");
            $db->bind(':limit', $limit);
            return $db->resultSet();
        } catch (Exception $e) {
            error_log("Popular skills error: " . $e->getMessage());
            return [];
        }
    }

    // View all users
    public function users() {
        try {
            $db = new Database();
            $db->query("SELECT id, username, email, created_at, role, profile_completed 
                       FROM users 
                       WHERE role != 'admin' 
                       ORDER BY created_at DESC");
            $users = $db->resultSet();

            $data = ['users' => $users];
            $this->view('users/admin_users', $data);
        } catch (Exception $e) {
            $data = ['users' => [], 'error' => 'Failed to load users'];
            $this->view('admin/users_list', $data);
        }
    }

    // Skills management
    public function skills() {
        $popularSkills = $this->getPopularSkills(20);
        
        $data = ['skills' => $popularSkills];
        $this->view('users/admin_skills', $data);
    }

    // Reports page
    public function reports() {
        $data = ['reports' => []];
        $this->view('users/admin_reports', $data);
    }
}
