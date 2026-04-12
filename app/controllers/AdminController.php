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

    // Data Reports Dashboard
    public function dataReports() {
        try {
            $db = new Database();
            $data = [];

            // ===== USER-LEVEL DATA =====

            // 1. Total users by role
            $db->query("SELECT role, COUNT(*) as count FROM users WHERE role != 'admin' GROUP BY role ORDER BY count DESC");
            $data['users_by_role'] = $db->resultSet();

            // 2. Total users count
            $db->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
            $result = $db->single();
            $data['total_users'] = $result->total ?? 0;

            // 3. New users this month
            $db->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin' AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')");
            $result = $db->single();
            $data['new_users_this_month'] = $result->total ?? 0;

            // 4. New users last month
            $db->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin' AND created_at >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01') AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')");
            $result = $db->single();
            $data['new_users_last_month'] = $result->total ?? 0;

            // 5. User registration trend (last 6 months)
            $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                        FROM users WHERE role != 'admin' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month ASC");
            $data['registration_trend'] = $db->resultSet();

            // 6. Top skilled users (most skills)
            $db->query("SELECT u.id, u.username, u.email, u.role, u.created_at, u.profile_picture,
                            COUNT(us.id) as skill_count,
                            SUM(CASE WHEN us.skill_type = 'teach' THEN 1 ELSE 0 END) as teach_count,
                            SUM(CASE WHEN us.skill_type = 'learn' THEN 1 ELSE 0 END) as learn_count
                        FROM users u
                        LEFT JOIN user_skills us ON u.id = us.user_id
                        WHERE u.role != 'admin'
                        GROUP BY u.id
                        ORDER BY skill_count DESC
                        LIMIT 10");
            $data['top_skilled_users'] = $db->resultSet();

            // 7. Most reported users
            $db->query("SELECT u.id, u.username, u.email, u.role, u.status,
                            COUNT(r.id) as report_count
                        FROM users u
                        INNER JOIN reports r ON u.id = r.reported_user_id
                        WHERE u.role != 'admin'
                        GROUP BY u.id
                        ORDER BY report_count DESC
                        LIMIT 10");
            $data['most_reported_users'] = $db->resultSet();

            // 8. Users with most feedback given
            $db->query("SELECT u.id, u.username, u.profile_picture,
                            COUNT(uf.id) as feedback_given,
                            ROUND(AVG(uf.rating), 1) as avg_rating_given
                        FROM users u
                        INNER JOIN user_feedback uf ON u.id = uf.reviewer_id
                        WHERE u.role != 'admin'
                        GROUP BY u.id
                        ORDER BY feedback_given DESC
                        LIMIT 10");
            $data['top_feedback_givers'] = $db->resultSet();

            // 9. Users with most feedback received
            $db->query("SELECT u.id, u.username, u.profile_picture,
                            COUNT(uf.id) as feedback_received,
                            ROUND(AVG(uf.rating), 1) as avg_rating
                        FROM users u
                        INNER JOIN user_feedback uf ON u.id = uf.user_id
                        WHERE u.role != 'admin'
                        GROUP BY u.id
                        ORDER BY feedback_received DESC
                        LIMIT 10");
            $data['top_feedback_receivers'] = $db->resultSet();

            // 10. Skill popularity
            $db->query("SELECT skill_name, 
                            COUNT(*) as total,
                            SUM(CASE WHEN skill_type = 'teach' THEN 1 ELSE 0 END) as teachers,
                            SUM(CASE WHEN skill_type = 'learn' THEN 1 ELSE 0 END) as learners
                        FROM user_skills 
                        GROUP BY skill_name 
                        ORDER BY total DESC
                        LIMIT 15");
            $data['skill_popularity'] = $db->resultSet();

            // 11. Proficiency level distribution
            $db->query("SELECT proficiency_level, COUNT(*) as count FROM user_skills GROUP BY proficiency_level");
            $data['proficiency_distribution'] = $db->resultSet();

            // ===== ORGANIZATION-LEVEL DATA =====

            // 12. Total organizations
            $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'organization'");
            $result = $db->single();
            $data['total_organizations'] = $result->total ?? 0;

            // 13. Organization project stats
            $db->query("SELECT u.id, u.username as org_name, u.email, u.created_at,
                            COUNT(p.id) as project_count,
                            SUM(CASE WHEN p.status = 'active' THEN 1 ELSE 0 END) as active_projects,
                            SUM(CASE WHEN p.status = 'in-progress' THEN 1 ELSE 0 END) as in_progress_projects,
                            SUM(CASE WHEN p.status = 'completed' THEN 1 ELSE 0 END) as completed_projects,
                            SUM(CASE WHEN p.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_projects
                        FROM users u
                        LEFT JOIN projects p ON u.id = p.organization_id
                        WHERE u.role = 'organization'
                        GROUP BY u.id
                        ORDER BY project_count DESC");
            $data['org_project_stats'] = $db->resultSet();

            // 14. Overall project status breakdown
            $db->query("SELECT status, COUNT(*) as count FROM projects GROUP BY status");
            $data['project_status_breakdown'] = $db->resultSet();

            // 15. Total projects
            $db->query("SELECT COUNT(*) as total FROM projects");
            $result = $db->single();
            $data['total_projects'] = $result->total ?? 0;

            // 16. Organization member counts (how many members across all their projects)
            $db->query("SELECT u.id, u.username as org_name,
                            COUNT(DISTINCT pm.user_id) as total_members
                        FROM users u
                        LEFT JOIN projects p ON u.id = p.organization_id
                        LEFT JOIN project_members pm ON p.id = pm.project_id
                        WHERE u.role = 'organization'
                        GROUP BY u.id
                        ORDER BY total_members DESC");
            $data['org_member_counts'] = $db->resultSet();

            // 17. Project categories distribution
            $db->query("SELECT category, COUNT(*) as count FROM projects GROUP BY category ORDER BY count DESC");
            $data['project_categories'] = $db->resultSet();

            // 18. Reports summary (all types)
            $db->query("SELECT 'User Reports' as type, COUNT(*) as count FROM reports
                        UNION ALL
                        SELECT 'Content Reports' as type, COUNT(*) as count FROM content_reports
                        UNION ALL
                        SELECT 'Feedback Reports' as type, COUNT(*) as count FROM feedback_reports
                        UNION ALL
                        SELECT 'Project Member Reports' as type, COUNT(*) as count FROM user_reports");
            $data['reports_summary'] = $db->resultSet();

            // 19. Total feedback count and average rating
            $db->query("SELECT COUNT(*) as total_feedback, ROUND(AVG(rating), 2) as avg_rating FROM user_feedback");
            $result = $db->single();
            $data['total_feedback'] = $result->total_feedback ?? 0;
            $data['avg_rating'] = $result->avg_rating ?? 0;

            // 20. Total exchanges
            $db->query("SELECT COUNT(*) as total FROM exchanges");
            $result = $db->single();
            $data['total_exchanges'] = $result->total ?? 0;

            // 21. Communities count
            $db->query("SELECT COUNT(*) as total FROM communities");
            $result = $db->single();
            $data['total_communities'] = $result->total ?? 0;

            $this->view('admin/data_reports', $data);

        } catch (Exception $e) {
            error_log("Data reports error: " . $e->getMessage());
            $data = ['error' => 'Failed to load data reports: ' . $e->getMessage()];
            $this->view('admin/data_reports', $data);
        }
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
