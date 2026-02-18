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

    private function getAdminStats() {
    try {
        $db = new Database();
        
        // Total users (exclude admins)
        $db->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
        $result = $db->single();
        $totalUsers = $result->total ?? 0;  // Changed from ['total'] to ->total
        
        // Total unique skills
        $db->query("SELECT COUNT(DISTINCT skill_name) as total FROM user_skills");
        $result = $db->single();
        $totalSkills = $result->total ?? 0;  // Changed from ['total'] to ->total
        
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
        try {
            $db = new Database();
            
            // This query is a bit complex! It combines two tables.
            // 1. Get User Reports
            $sql = "SELECT 
                        r.id, r.reason, r.created_at, r.status, 
                        'user' as type,
                        u1.username as reporter_name, 
                        u2.username as reported_name,
                        NULL as content_preview
                    FROM reports r
                    JOIN users u1 ON r.reporter_user_id = u1.id
                    JOIN users u2 ON r.reported_user_id = u2.id
                    
                    UNION ALL
                    
                    SELECT 
                        cr.id, cr.reason, cr.created_at, cr.status,
                        'content' as type,
                        u.username as reporter_name,
                        'Content Author' as reported_name, -- We could join posts to get real name, but keeping it simple
                        p.content as content_preview
                    FROM content_reports cr
                    JOIN users u ON cr.reporter_id = u.id
                    LEFT JOIN posts p ON cr.content_id = p.id
                    
                    ORDER BY created_at DESC";
                      
            $db->query($sql);
            $reports = $db->resultSet();

            $data = ['reports' => $reports];
            $this->view('users/admin_reports', $data);

        } catch (Exception $e) {
            error_log("Error fetching reports: " . $e->getMessage());
            $data = ['reports' => []];
            $this->view('users/admin_reports', $data);
        }
    }
}
