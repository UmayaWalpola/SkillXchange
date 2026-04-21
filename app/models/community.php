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
    
    /**
     * Get all communities for user view (shows membership status, only active communities)
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
            LEFT JOIN community_posts p ON c.id = p.community_id
            LEFT JOIN community_members cm2 ON c.id = cm2.community_id AND cm2.user_id = :user_id
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
     * Get one active community for the detail view, including user membership state.
     */
    public function getCommunityById($communityId, $userId = null) {
        $this->db->query("
            SELECT
                c.*,
                COUNT(DISTINCT cm.user_id) as member_count,
                COUNT(DISTINCT cm.user_id) as members,
                COUNT(DISTINCT p.id) as post_count,
                COUNT(DISTINCT p.id) as totalPosts,
                COUNT(DISTINCT p.id) as posts,
                MAX(CASE
                    WHEN cm2.user_id IS NOT NULL OR c.created_by = :user_id THEN 1
                    ELSE 0
                END) as is_member,
                MAX(CASE WHEN c.created_by = :user_id THEN 1 ELSE 0 END) as is_owner,
                COALESCE(
                    MAX(CASE WHEN c.created_by = :user_id THEN 'admin' ELSE NULL END),
                    MAX(cm2.role),
                    'guest'
                ) as user_role
            FROM communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN community_posts p ON c.id = p.community_id
            LEFT JOIN community_members cm2
                ON c.id = cm2.community_id
                AND cm2.user_id = :user_id
            WHERE c.id = :community_id
              AND c.status = 'active'
            GROUP BY c.id
            LIMIT 1
        ");

        $this->db->bind(':community_id', $communityId);
        $this->db->bind(':user_id', $userId);
        return $this->db->single();
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
public function getCommunityPosts($communityId, $userId = null) {
        $this->db->query("
            SELECT
                p.*,
                u.username AS author_name,
                (SELECT COUNT(*) FROM community_post_reactions r
                 WHERE r.post_id = p.id AND r.reaction_type = 'like') AS like_count,
                " . ($userId ? "
                (SELECT COUNT(*) FROM community_post_reactions r2
                 WHERE r2.post_id = p.id AND r2.user_id = :user_id AND r2.reaction_type = 'like') AS user_reacted
                " : "0 AS user_reacted") . "
            FROM community_posts p
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.community_id = :community_id
            ORDER BY p.is_pinned DESC, p.created_at ASC
        ");
        $this->db->bind(':community_id', $communityId);
        if ($userId) $this->db->bind(':user_id', $userId);
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
     * Get a single user reaction on a post (or null if none).
     */
    public function getUserReaction($userId, $postId) {
        $this->db->query("
            SELECT * FROM community_post_reactions
            WHERE user_id = :user_id AND post_id = :post_id
            LIMIT 1
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':post_id', $postId);
        return $this->db->single(); // returns object or false
    }
 
    /**
     * Remove a user's reaction from a post.
     */
    public function removeReaction($userId, $postId) {
        $this->db->query("
            DELETE FROM community_post_reactions
            WHERE user_id = :user_id AND post_id = :post_id
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':post_id', $postId);
        return $this->db->execute();
    }
 
    /**
     * Get total reaction count for a post (filtered by type).
     */
    public function getReactionCount($postId, $type = 'like') {
        $this->db->query("
            SELECT COUNT(*) as cnt FROM community_post_reactions
            WHERE post_id = :post_id AND reaction_type = :type
        ");
        $this->db->bind(':post_id', $postId);
        $this->db->bind(':type', $type);
        $row = $this->db->single();
        return $row ? $row->cnt : 0;
    }
}
