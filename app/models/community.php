<?php
/**
 * Community Model - COMPLETE VERSION
 * Includes methods for both Community Managers AND Users
 * Place in: app/models/Community.php
 */
class Community {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // ==========================================
    // ADMIN/MANAGER METHODS (for CommunityController)
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
        $this->db->bind(':privacy', $data['privacy']);
        $this->db->bind(':rules', $data['rules']);
        $this->db->bind(':tags', $data['tags']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':created_by', $data['created_by']);
        
        return $this->db->execute();
    }
    
    /**
     * READ - Get all communities (admin view)
     */
    public function getAllCommunities() {
        $this->db->query("
            SELECT 
                c.*,
                COUNT(DISTINCT cm.user_id) as member_count,
                COUNT(DISTINCT cm.user_id) as members,
                COUNT(DISTINCT p.id) as post_count,
                COUNT(DISTINCT p.id) as totalPosts,
                COUNT(DISTINCT p.id) as posts
            FROM communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN community_posts p ON c.id = p.community_id
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        
        return $this->db->resultSet();
    }
    
    /**
     * READ - Get community by ID
     */
    public function getCommunityById($id, $userId = null) {
        $this->db->query("
            SELECT 
                c.*,
                COUNT(DISTINCT cm.user_id) as member_count,
                COUNT(DISTINCT cm.user_id) as members,
                COUNT(DISTINCT p.id) as post_count,
                COUNT(DISTINCT p.id) as totalPosts,
                COUNT(DISTINCT p.id) as posts,
                " . ($userId ? "MAX(CASE WHEN cm2.user_id = :user_id THEN 1 ELSE 0 END) as is_member" : "0 as is_member") . "
            FROM communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN community_posts p ON c.id = p.community_id
            " . ($userId ? "LEFT JOIN community_members cm2 ON c.id = cm2.community_id AND cm2.user_id = :user_id" : "") . "
            WHERE c.id = :id
            GROUP BY c.id
        ");
        
        $this->db->bind(':id', $id);
        if ($userId) {
            $this->db->bind(':user_id', $userId);
        }
        
        return $this->db->single();
    }
    
    /**
     * UPDATE - Update community
     */
    public function update($data) {
        $this->db->query("
            UPDATE communities 
            SET name = :name,
                description = :description,
                privacy = :privacy,
                rules = :rules,
                tags = :tags,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':privacy', $data['privacy']);
        $this->db->bind(':rules', $data['rules']);
        $this->db->bind(':tags', $data['tags']);
        $this->db->bind(':status', $data['status']);
        
        return $this->db->execute();
    }
    
    /**
     * UPDATE - Update community status
     */
    public function updateStatus($id, $status) {
        $this->db->query("
            UPDATE communities 
            SET status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");
        
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        
        return $this->db->execute();
    }
    
    /**
     * DELETE - Delete community
     */
    public function delete($id) {
        $this->db->query("DELETE FROM communities WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
    
    /**
     * Get dashboard statistics
     */
    public function getStats() {
        $this->db->query("
            SELECT 
                COUNT(*) as total_communities,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_communities,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_communities,
                SUM(CASE WHEN privacy = 'public' THEN 1 ELSE 0 END) as public_communities,
                SUM(CASE WHEN privacy = 'private' THEN 1 ELSE 0 END) as private_communities
            FROM communities
        ");
        
        $result = $this->db->single();
        
        if(!$result) {
            return (object)[
                'total_communities' => 0,
                'active_communities' => 0,
                'inactive_communities' => 0,
                'public_communities' => 0,
                'private_communities' => 0
            ];
        }
        
        return $result;
    }
    
    // ==========================================
    // USER-FACING METHODS (for UserdashboardController)
    // ==========================================
    
    /**
     * Get all communities for user view (shows membership status)
     * Returns both 'members' and 'member_count' for compatibility
     */
    public function getAllCommunitiesForUser($userId) {
        $this->db->query("
            SELECT 
                c.*,
                COUNT(DISTINCT cm.user_id) as member_count,
                COUNT(DISTINCT cm.user_id) as members,
                COUNT(DISTINCT p.id) as post_count,
                COUNT(DISTINCT p.id) as totalPosts,
                COUNT(DISTINCT p.id) as posts,
                MAX(CASE WHEN cm2.user_id = :user_id THEN 1 ELSE 0 END) as is_member,
                MAX(CASE WHEN c.created_by = :user_id THEN 1 ELSE 0 END) as is_owner
            FROM communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN community_posts p ON c.id = p.community_id            LEFT JOIN community_members cm2 ON c.id = cm2.community_id AND cm2.user_id = :user_id
            WHERE c.status = 'active'
            GROUP BY c.id
            ORDER BY is_member DESC, members DESC
        ");
        
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }
    
    /**
     * Get user's communities (communities they've joined)
     */
    public function getUserCommunities($userId) {
        $this->db->query("
            SELECT 
                c.*,
                COUNT(DISTINCT cm.user_id) as member_count,
                COUNT(DISTINCT cm.user_id) as members,
                COUNT(DISTINCT p.id) as post_count,
                COUNT(DISTINCT p.id) as totalPosts,
                COUNT(DISTINCT p.id) as posts,
                cm2.joined_at
            FROM communities c
            INNER JOIN community_members cm2 ON c.id = cm2.community_id AND cm2.user_id = :user_id
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN community_posts p ON c.id = p.community_id
            WHERE c.status = 'active'
            GROUP BY c.id
            ORDER BY cm2.joined_at DESC
        ");
        
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }
    
    /**
     * Join a community
     */
    public function joinCommunity($userId, $communityId) {
        // Check if already a member
        if ($this->isMember($userId, $communityId)) {
            return false;
        }
        
        $this->db->query("
            INSERT INTO community_members (user_id, community_id, joined_at, role) 
            VALUES (:user_id, :community_id, NOW(), 'member')
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':community_id', $communityId);
        
        return $this->db->execute();
    }
    
    /**
     * Leave a community
     */
    public function leaveCommunity($userId, $communityId) {
        // Check if user is the owner
        $this->db->query("SELECT created_by FROM communities WHERE id = :community_id");
        $this->db->bind(':community_id', $communityId);
        $community = $this->db->single();
        
        if ($community && $community->created_by == $userId) {
            return false; // Owners cannot leave
        }
        
        $this->db->query("
            DELETE FROM community_members 
            WHERE user_id = :user_id AND community_id = :community_id
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':community_id', $communityId);
        
        return $this->db->execute();
    }
    
    /**
     * Check if user is a member
     */
    public function isMember($userId, $communityId) {
        $this->db->query("
            SELECT COUNT(*) as count 
            FROM community_members 
            WHERE user_id = :user_id AND community_id = :community_id
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':community_id', $communityId);
        
        $result = $this->db->single();
        return $result && $result->count > 0;
    }
    
    /**
     * Get community members
     */
   public function getCommunityMembers($communityId) {
    $this->db->query("
        SELECT 
            u.id AS user_id,
            u.username as name, 
            u.email, 
            u.profile_picture, 
            cm.joined_at, 
            cm.role
        FROM community_members cm
        JOIN users u ON cm.user_id = u.id
        WHERE cm.community_id = :community_id
        ORDER BY cm.joined_at DESC
    ");
    
    $this->db->bind(':community_id', $communityId);
    return $this->db->resultSet();
}
 /**
  * Get posts in a community (for forum view)
 */
public function getCommunityPosts($communityId) {
    $this->db->query("
        SELECT 
            cp.id,
            cp.community_id,
            cp.user_id,
            cp.title,
            cp.content,
            cp.link_url,
            cp.image_path,
            cp.post_type,
            cp.parent_id,
            cp.is_pinned,
            cp.created_at,
            cp.updated_at,
            u.username AS author_name,
            u.profile_picture
        FROM community_posts cp
        JOIN users u ON cp.user_id = u.id
        WHERE cp.community_id = :community_id
          AND cp.parent_id IS NULL
        ORDER BY cp.is_pinned DESC, cp.created_at DESC
    ");

    $this->db->bind(':community_id', $communityId);
    return $this->db->resultSet();
}
    /**
     * Create a post in community
     */
    public function createPost($userId, $communityId, $title, $content, $postType = 'discussion', $linkUrl = null, $imagePath = null) {
    $this->db->query("
        INSERT INTO community_posts (
            user_id,
            community_id,
            title,
            content,
            link_url,
            image_path,
            post_type,
            created_at
        ) VALUES (
            :user_id,
            :community_id,
            :title,
            :content,
            :link_url,
            :image_path,
            :post_type,
            NOW()
        )
    ");

    $this->db->bind(':user_id', $userId);
    $this->db->bind(':community_id', $communityId);
    $this->db->bind(':title', $title);
    $this->db->bind(':content', $content);
    $this->db->bind(':link_url', $linkUrl);
    $this->db->bind(':image_path', $imagePath);
    $this->db->bind(':post_type', $postType);

    if ($this->db->execute()) {
        return $this->db->lastInsertId();
    }

    return false;
}

public function createComment($userId, $communityId, $parentId, $content) {
    $this->db->query("
        INSERT INTO community_posts (
            user_id,
            community_id,
            title,
            content,
            link_url,
            image_path,
            post_type,
            parent_id,
            is_pinned,
            created_at,
            updated_at
        ) VALUES (
            :user_id,
            :community_id,
            NULL,
            :content,
            NULL,
            NULL,
            'discussion',
            :parent_id,
            0,
            NOW(),
            NOW()
        )
    ");

    $this->db->bind(':user_id', $userId);
    $this->db->bind(':community_id', $communityId);
    $this->db->bind(':content', $content);
    $this->db->bind(':parent_id', $parentId);

    if ($this->db->execute()) {
        return $this->db->lastInsertId();
    }

    return false;
}

public function getCommentsForPost($postId) {
    $this->db->query("
        SELECT 
            cp.*,
            u.username AS author_name,
            u.profile_picture
        FROM community_posts cp
        JOIN users u ON cp.user_id = u.id
        WHERE cp.parent_id = :post_id
        ORDER BY cp.created_at ASC
    ");

    $this->db->bind(':post_id', $postId);
    return $this->db->resultSet();
}

public function addReaction($userId, $postId, $type) {
    $this->db->query("
        INSERT INTO community_post_reactions (user_id, post_id, reaction_type)
        VALUES (:user_id, :post_id, :type)
        ON DUPLICATE KEY UPDATE reaction_type = :type
    ");

    $this->db->bind(':user_id', $userId);
    $this->db->bind(':post_id', $postId);
    $this->db->bind(':type', $type);

    return $this->db->execute();
}

public function getMemberRole($userId, $communityId) {
    $this->db->query("
        SELECT role 
        FROM community_members 
        WHERE user_id = :user_id AND community_id = :community_id
    ");

    $this->db->bind(':user_id', $userId);
    $this->db->bind(':community_id', $communityId);

    return $this->db->single();
}
    /**
     * Create a new community (user-initiated)
     */
    public function createCommunity($userId, $name, $description, $about = '', $icon = '🌐', $category = null) {
        $this->db->query("
            INSERT INTO communities 
            (name, description, privacy, status, created_by, created_at) 
            VALUES (:name, :description, 'public', 'active', :created_by, NOW())
        ");
        
        $this->db->bind(':name', $name);
        $this->db->bind(':description', $description);
        $this->db->bind(':created_by', $userId);
        
        if ($this->db->execute()) {
            $communityId = $this->db->lastInsertId();
            
            // Auto-join creator as admin
            $this->db->query("
                INSERT INTO community_members (user_id, community_id, role, joined_at) 
                VALUES (:user_id, :community_id, 'admin', NOW())
            ");
            
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':community_id', $communityId);
            $this->db->execute();
            
            return $communityId;
        }
        
        return false;
    }
    
    /**
     * Search communities
     */
    public function searchCommunities($query) {
        $this->db->query("
            SELECT 
                c.*,
                COUNT(DISTINCT cm.user_id) as member_count,
                COUNT(DISTINCT cm.user_id) as members,
                COUNT(DISTINCT p.id) as post_count,
                COUNT(DISTINCT p.id) as totalPosts,
                COUNT(DISTINCT p.id) as posts
            FROM communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN community_posts p ON c.id = p.community_id
            WHERE (c.name LIKE :query OR c.description LIKE :query OR c.tags LIKE :query)
              AND c.status = 'active'
            GROUP BY c.id
            ORDER BY members DESC
        ");
        
        $searchQuery = '%' . $query . '%';
        $this->db->bind(':query', $searchQuery);
        
        return $this->db->resultSet();
    }
    
}
