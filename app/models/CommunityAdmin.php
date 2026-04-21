<?php
/**
 * CommunityAdmin Model
 * Handles all community admin operations: creation, management, skill handling, etc.
 * Place in: app/models/CommunityAdmin.php
 */
class CommunityAdmin {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // ==========================================
    // COMMUNITY OPERATIONS
    // ==========================================
    
    /**
     * CREATE - Create a new community
     */
    public function create($data) {
        $this->db->query("
            INSERT INTO communities 
            (name, description, privacy, rules, tags, status, created_by, created_at) 
            VALUES (:name, :description, :privacy, :rules, :tags, :status, :created_by, NOW())
        ");
        
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':privacy', 'public');
        $this->db->bind(':rules', '[]');
        $this->db->bind(':tags', '[]');
        $this->db->bind(':status', 'active');
        $this->db->bind(':created_by', $data['created_by']);
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * READ - Get all communities for admin dashboard
     */
    public function getAllCommunities() {
        $this->db->query("
            SELECT 
                c.id,
                c.name,
                c.description,
                c.status,
                c.created_by,
                c.created_at,
                COUNT(DISTINCT cm.user_id) as member_count,
                COUNT(DISTINCT cp.id) as post_count
            FROM communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN community_posts cp ON c.id = cp.community_id
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        
        return $this->db->resultSet();
    }
    
    /**
     * READ - Get community by ID
     */
    public function getCommunityById($id) {
        $this->db->query("
            SELECT 
                c.*,
                COUNT(DISTINCT cm.user_id) as member_count,
                COUNT(DISTINCT cp.id) as post_count
            FROM communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN community_posts cp ON c.id = cp.community_id
            WHERE c.id = :id
            GROUP BY c.id
        ");
        
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    
    /**
     * UPDATE - Activate community
     */
    public function activateCommunity($id) {
        $this->db->query("UPDATE communities SET status = 'active', updated_at = NOW() WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
    
    /**
     * UPDATE - Deactivate community
     */
    public function deactivateCommunity($id) {
        $this->db->query("UPDATE communities SET status = 'inactive', updated_at = NOW() WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
    
    /**
     * UPDATE - Update community status
     */
    public function updateStatus($id, $status) {
        $this->db->query("UPDATE communities SET status = :status, updated_at = NOW() WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        return $this->db->execute();
    }
    
    
    // ==========================================
    // SKILL OPERATIONS
    // ==========================================
    
    /**
     * READ - Get all skills for dropdown
     */
    public function getAllSkills() {
        $this->db->query("SELECT id, skill_name FROM skills ORDER BY skill_name ASC");
        return $this->db->resultSet();
    }

    private function normalizeSkillCommunityName($name) {
        $name = strtolower(trim((string)$name));
        $name = str_replace(['_', '-', '/', '&'], ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name);

        $aliases = [
            'ai' => 'ai',
            'ai and ml' => 'ai',
            'artificial intelligence' => 'ai',
            'data science ai' => 'data-science',
            'data science and ai' => 'data-science',
            'data science' => 'data-science',
            'data analytics' => 'data-analytics',
            'data analysis' => 'data-analytics',
            'web developers hub' => 'web-development',
            'web developers' => 'web-development',
            'web development' => 'web-development',
            'web dev' => 'web-development',
            'ui ux design' => 'frontend',
            'frontend frameworks' => 'frontend',
            'front end' => 'frontend',
            'cloud computing' => 'cloud',
            'mobile development' => 'mobile',
            'mobile app development' => 'mobile',
            'digital marketing' => 'marketing',
            'github and git' => 'github',
            'git and github' => 'github',
            'github git' => 'github',
            'db management' => 'database',
            'database management' => 'database',
        ];

        if (isset($aliases[$name])) {
            return $aliases[$name];
        }

        return str_replace(' ', '-', $name);
    }

    /**
     * READ - Get only skills that do not already have a community.
     */
    public function getSkillsWithoutCommunities() {
        $skills = $this->getAllSkills();

        $this->db->query("SELECT name FROM communities");
        $communities = $this->db->resultSet() ?: [];

        $usedSkillKeys = [];
        foreach ($communities as $community) {
            $usedSkillKeys[$this->normalizeSkillCommunityName($community->name ?? '')] = true;
        }

        $availableSkills = [];
        foreach ($skills as $skill) {
            $skillKey = $this->normalizeSkillCommunityName($skill->skill_name ?? '');
            if ($skillKey !== '' && !isset($usedSkillKeys[$skillKey])) {
                $availableSkills[] = $skill;
            }
        }

        return $availableSkills;
    }

    public function skillHasCommunity($skillName) {
        $skillKey = $this->normalizeSkillCommunityName($skillName);

        $this->db->query("SELECT name FROM communities");
        $communities = $this->db->resultSet() ?: [];

        foreach ($communities as $community) {
            if ($this->normalizeSkillCommunityName($community->name ?? '') === $skillKey) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * READ - Get skill by ID
     */
    public function getSkillById($id) {
        $this->db->query("SELECT * FROM skills WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    /**
     * CREATE - Add new skill
     */
    public function createSkill($skillName, $description = null) {
        // Check for duplicate
        $this->db->query("SELECT id FROM skills WHERE LOWER(skill_name) = LOWER(:name)");
        $this->db->bind(':name', $skillName);
        if ($this->db->single()) {
            return ['success' => false, 'message' => 'Skill already exists'];
        }
        
        // Insert skill
        $this->db->query("
            INSERT INTO skills (skill_name, description, created_at) 
            VALUES (:name, :description, NOW())
        ");
        $this->db->bind(':name', $skillName);
        $this->db->bind(':description', $description ?: null);
        
        if ($this->db->execute()) {
            $skillId = $this->db->lastInsertId();
            return ['success' => true, 'skill_id' => $skillId, 'skill_name' => $skillName];
        }
        
        return ['success' => false, 'message' => 'Failed to create skill'];
    }
    
    // ==========================================
    // ACTION LOGGING
    // ==========================================
    
    /**
     * Log community admin action
     */
    public function logAction($adminId, $communityId, $actionType, $description) {
        $this->db->query("
            INSERT INTO community_admin_actions 
            (admin_id, community_id, action_type, description, created_at)
            VALUES (:admin_id, :community_id, :action_type, :description, NOW())
        ");
        
        $this->db->bind(':admin_id', $adminId);
        $this->db->bind(':community_id', $communityId);
        $this->db->bind(':action_type', $actionType);
        $this->db->bind(':description', $description);
        
        return $this->db->execute();
    }
    
    /**
     * Log skill creation action
     */
    public function logSkillCreation($adminId, $skillName) {
        $this->db->query("
            INSERT INTO admin_actions 
            (admin_id, action_type, target_type, description, ip_address, created_at)
            VALUES (:admin_id, 'skill_created', 'skill', :description, :ip, NOW())
        ");
        
        $this->db->bind(':admin_id', $adminId);
        $this->db->bind(':description', "Added new skill: {$skillName}");
        $this->db->bind(':ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        
        return $this->db->execute();
    }

    public function getCommunityPosts($communityId) {
        $this->db->query("
            SELECT cp.*, u.username AS author_name, u.profile_picture
            FROM community_posts cp
            JOIN users u ON cp.user_id = u.id
            WHERE cp.community_id = :community_id AND cp.parent_id IS NULL
            ORDER BY cp.is_pinned DESC, cp.created_at DESC
        ");
        $this->db->bind(':community_id', $communityId);
        return $this->db->resultSet();
    }

    public function getCommunityPostById($postId, $communityId) {
        $this->db->query("
            SELECT cp.*, u.username AS author_name
            FROM community_posts cp
            JOIN users u ON cp.user_id = u.id
            WHERE cp.id = :post_id AND cp.community_id = :community_id
            LIMIT 1
        ");
        $this->db->bind(':post_id', $postId);
        $this->db->bind(':community_id', $communityId);
        return $this->db->single();
    }

    public function createAnnouncement($communityId, $adminId, $title, $content) {
        $this->db->query("
            INSERT INTO community_posts (
                community_id,
                user_id,
                title,
                content,
                post_type,
                is_pinned,
                created_at,
                updated_at
            ) VALUES (
                :community_id,
                :user_id,
                :title,
                :content,
                'announcement',
                1,
                NOW(),
                NOW()
            )
        ");
        $this->db->bind(':community_id', $communityId);
        $this->db->bind(':user_id', $adminId);
        $this->db->bind(':title', $title);
        $this->db->bind(':content', $content);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    public function deleteCommunityPost($postId, $communityId) {
        try {
            $this->db->query('START TRANSACTION');

            $this->db->query("DELETE FROM community_posts WHERE parent_id = :post_id AND community_id = :community_id");
            $this->db->bind(':post_id', $postId);
            $this->db->bind(':community_id', $communityId);
            $this->db->execute();

            $this->db->query("DELETE FROM community_posts WHERE id = :post_id AND community_id = :community_id");
            $this->db->bind(':post_id', $postId);
            $this->db->bind(':community_id', $communityId);
            $deleted = $this->db->execute();

            $this->db->query('COMMIT');
            return $deleted;
        } catch (Exception $e) {
            $this->db->query('ROLLBACK');
            error_log('deleteCommunityPost error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendPostWarning($postId, $communityId, $adminId, $reason) {
        $post = $this->getCommunityPostById($postId, $communityId);
        if (!$post) {
            return false;
        }

        $community = $this->getCommunityById($communityId);
        $communityName = $community->name ?? 'community';

        $this->db->query("
            INSERT INTO notifications (
                user_id,
                type,
                title,
                message,
                related_user_id,
                is_read,
                created_at
            ) VALUES (
                :user_id,
                'system_warning',
                'Community Post Warning',
                :message,
                :related_user_id,
                0,
                NOW()
            )
        ");
        $this->db->bind(':user_id', $post->user_id);
        $this->db->bind(':message', 'Your post in the ' . $communityName . ' community has been flagged by a community manager. Reason: ' . $reason);
        $this->db->bind(':related_user_id', $adminId);

        return $this->db->execute();
    }

    public function getCommunityMembers($communityId) {
        $this->db->query("
            SELECT u.id AS user_id, u.username AS name, u.profile_picture, cm.joined_at, cm.role
            FROM community_members cm
            JOIN users u ON cm.user_id = u.id
            WHERE cm.community_id = :community_id
            ORDER BY cm.joined_at DESC
        ");
        $this->db->bind(':community_id', $communityId);
        return $this->db->resultSet();
    }


}
