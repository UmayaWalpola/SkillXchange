<?php

class Community {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // =============================================
    // GET COMMUNITIES
    // =============================================
    
    /**
     * Get all communities with stats and user membership status
     */
    public function getAllCommunitiesForUser($userId) {
        $this->db->query("
            SELECT 
                c.*,
                COUNT(DISTINCT cm.user_id) as members,
                COUNT(DISTINCT cp.id) as posts,
                CASE WHEN ucm.id IS NOT NULL THEN 1 ELSE 0 END as is_member,
                ucm.role as user_role
            FROM communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN posts cp ON c.id = cp.community_id
            LEFT JOIN community_members ucm ON c.id = ucm.community_id AND ucm.user_id = :user_id
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }
    
    /**
     * Get single community with details
     */
    public function getCommunityById($communityId, $userId = null) {
        $this->db->query("
            SELECT 
                c.*,
                u.username as creator_name,
                COUNT(DISTINCT cm.user_id) as members,
                COUNT(DISTINCT cp.id) as posts,
                " . ($userId ? "CASE WHEN ucm.id IS NOT NULL THEN 1 ELSE 0 END as is_member,
                ucm.role as user_role" : "0 as is_member, NULL as user_role") . "
            FROM communities c
            INNER JOIN users u ON c.created_by = u.id
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN posts cp ON c.id = cp.community_id
            " . ($userId ? "LEFT JOIN community_members ucm ON c.id = ucm.community_id AND ucm.user_id = :user_id" : "") . "
            WHERE c.id = :community_id
            GROUP BY c.id
        ");
        
        $this->db->bind(':community_id', $communityId);
        if ($userId) {
            $this->db->bind(':user_id', $userId);
        }
        
        return $this->db->single();
    }
    
    // =============================================
    // COMMUNITY MEMBERSHIP
    // =============================================
    
    /**
     * Join a community
     */
    public function joinCommunity($userId, $communityId) {
        try {
            $this->db->query("
                INSERT INTO community_members (community_id, user_id, role)
                VALUES (:community_id, :user_id, 'member')
                ON DUPLICATE KEY UPDATE joined_at = CURRENT_TIMESTAMP
            ");
            
            $this->db->bind(':community_id', $communityId);
            $this->db->bind(':user_id', $userId);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Join community error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Leave a community
     */
    public function leaveCommunity($userId, $communityId) {
        // Don't allow owner to leave
        $this->db->query("
            SELECT role FROM community_members 
            WHERE community_id = :community_id AND user_id = :user_id
        ");
        $this->db->bind(':community_id', $communityId);
        $this->db->bind(':user_id', $userId);
        $member = $this->db->single();
        
        if ($member && $member->role === 'owner') {
            return false; // Owners can't leave
        }
        
        $this->db->query("
            DELETE FROM community_members 
            WHERE community_id = :community_id AND user_id = :user_id
        ");
        
        $this->db->bind(':community_id', $communityId);
        $this->db->bind(':user_id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Get community members
     */
    public function getCommunityMembers($communityId) {
        $this->db->query("
            SELECT 
                cm.id,
                cm.role,
                cm.joined_at,
                u.id as user_id,
                u.username as name,
                u.username,
                u.profile_picture
            FROM community_members cm
            INNER JOIN users u ON cm.user_id = u.id
            WHERE cm.community_id = :community_id
            ORDER BY 
                CASE cm.role
                    WHEN 'owner' THEN 1
                    WHEN 'moderator' THEN 2
                    ELSE 3
                END,
                cm.joined_at ASC
        ");
        
        $this->db->bind(':community_id', $communityId);
        return $this->db->resultSet();
    }
    
    // =============================================
    // COMMUNITY POSTS/MESSAGES
    // =============================================
    
    /**
     * Get community posts
     */
    public function getCommunityPosts($communityId, $limit = 50, $offset = 0) {
        $this->db->query("
            SELECT 
                cp.*,
                u.username as author_name,
                u.username as author_username,
                u.profile_picture as author_avatar,
                COUNT(DISTINCT cpr.id) as reaction_count
            FROM posts cp
            INNER JOIN users u ON cp.user_id = u.id
            LEFT JOIN community_post_reactions cpr ON cp.id = cpr.post_id
            WHERE cp.community_id = :community_id AND cp.parent_id IS NULL
            GROUP BY cp.id
            ORDER BY cp.is_pinned DESC, cp.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        
        $this->db->bind(':community_id', $communityId);
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);
        
        return $this->db->resultSet();
    }
    
    /**
     * Create a post/message
     */
    public function createPost($userId, $communityId, $content, $postType = 'message') {
        // Check if user is a member
        if (!$this->isMember($userId, $communityId)) {
            return false;
        }
        
        try {
            $this->db->query("
                INSERT INTO posts (community_id, user_id, content, post_type)
                VALUES (:community_id, :user_id, :content, :post_type)
            ");
            
            $this->db->bind(':community_id', $communityId);
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':content', $content);
            $this->db->bind(':post_type', $postType);
            
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Create post error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a post
     */
    public function deletePost($userId, $postId) {
        // Check if user owns the post or is moderator/owner
        $this->db->query("
            SELECT cp.user_id, cp.community_id, cm.role
            FROM posts cp
            LEFT JOIN community_members cm ON cp.community_id = cm.community_id AND cm.user_id = :user_id
            WHERE cp.id = :post_id
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':post_id', $postId);
        $post = $this->db->single();
        
        if (!$post) return false;
        
        // Allow deletion if: owner of post, or moderator/owner of community
        if ($post->user_id == $userId || in_array($post->role, ['owner', 'moderator'])) {
            $this->db->query("DELETE FROM posts WHERE id = :post_id");
            $this->db->bind(':post_id', $postId);
            return $this->db->execute();
        }
        
        return false;
    }
    
    // =============================================
    // HELPER METHODS
    // =============================================
    
    /**
     * Check if user is a member
     */
    public function isMember($userId, $communityId) {
        $this->db->query("
            SELECT id FROM community_members 
            WHERE community_id = :community_id AND user_id = :user_id
        ");
        
        $this->db->bind(':community_id', $communityId);
        $this->db->bind(':user_id', $userId);
        
        return $this->db->single() !== false;
    }
    
    /**
     * Check if user is moderator or owner
     */
    public function isModerator($userId, $communityId) {
        $this->db->query("
            SELECT role FROM community_members 
            WHERE community_id = :community_id AND user_id = :user_id
        ");
        
        $this->db->bind(':community_id', $communityId);
        $this->db->bind(':user_id', $userId);
        
        $member = $this->db->single();
        return $member && in_array($member->role, ['owner', 'moderator']);
    }
    
    /**
     * Create a new community
     */
    public function createCommunity($userId, $name, $description, $about, $icon = '🌐', $category = null) {
        try {
            $this->db->query("START TRANSACTION");
            
            // Create community
            $this->db->query("
                INSERT INTO communities (name, description, about, icon, category, created_by)
                VALUES (:name, :description, :about, :icon, :category, :created_by)
            ");
            
            $this->db->bind(':name', $name);
            $this->db->bind(':description', $description);
            $this->db->bind(':about', $about);
            $this->db->bind(':icon', $icon);
            $this->db->bind(':category', $category);
            $this->db->bind(':created_by', $userId);
            
            if (!$this->db->execute()) {
                throw new Exception("Failed to create community");
            }
            
            $communityId = $this->db->lastInsertId();
            
            // Add creator as owner
            $this->db->query("
                INSERT INTO community_members (community_id, user_id, role)
                VALUES (:community_id, :user_id, 'owner')
            ");
            
            $this->db->bind(':community_id', $communityId);
            $this->db->bind(':user_id', $userId);
            
            if (!$this->db->execute()) {
                throw new Exception("Failed to add owner");
            }
            
            $this->db->query("COMMIT");
            return $communityId;
            
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            error_log("Create community error: " . $e->getMessage());
            return false;
        }
    }
}