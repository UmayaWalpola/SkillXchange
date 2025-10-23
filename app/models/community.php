<?php
// app/models/Community.php

class Community {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // CREATE - Add new community
    public function create($data) {
        $this->db->query('INSERT INTO communities 
            (name, category, description, privacy, rules, tags, status, created_by, created_at) 
            VALUES 
            (:name, :category, :description, :privacy, :rules, :tags, :status, :created_by, NOW())');
        
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':privacy', $data['privacy']);
        $this->db->bind(':rules', $data['rules']);
        $this->db->bind(':tags', $data['tags']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':created_by', $data['created_by']);

        if($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // READ - Get all communities
    public function getAllCommunities() {
        $this->db->query('SELECT 
                c.*,
                COUNT(DISTINCT cm.user_id) as members,
                COUNT(DISTINCT p.id) as posts
            FROM communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN posts p ON c.id = p.community_id
            GROUP BY c.id
            ORDER BY c.created_at DESC');

        return $this->db->resultSet();
    }

    // READ - Get community by ID
    public function getCommunityById($id) {
        $this->db->query('SELECT 
                c.*,
                COUNT(DISTINCT cm.user_id) as members,
                COUNT(DISTINCT p.id) as posts
            FROM communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN posts p ON c.id = p.community_id
            WHERE c.id = :id
            GROUP BY c.id');
        
        $this->db->bind(':id', $id);

        return $this->db->single();
    }

    // READ - Get communities by category
    public function getCommunitiesByCategory($category) {
        $this->db->query('SELECT 
                c.*,
                COUNT(DISTINCT cm.user_id) as members,
                COUNT(DISTINCT p.id) as posts
            FROM communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN posts p ON c.id = p.community_id
            WHERE c.category = :category
            GROUP BY c.id
            ORDER BY c.created_at DESC');
        
        $this->db->bind(':category', $category);

        return $this->db->resultSet();
    }

    // READ - Get communities by status
    public function getCommunitiesByStatus($status) {
        $this->db->query('SELECT 
                c.*,
                COUNT(DISTINCT cm.user_id) as members,
                COUNT(DISTINCT p.id) as posts
            FROM communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN posts p ON c.id = p.community_id
            WHERE c.status = :status
            GROUP BY c.id
            ORDER BY c.created_at DESC');
        
        $this->db->bind(':status', $status);

        return $this->db->resultSet();
    }

    // UPDATE - Update community
    public function update($data) {
        $this->db->query('UPDATE communities 
            SET 
                name = :name,
                category = :category,
                description = :description,
                privacy = :privacy,
                rules = :rules,
                tags = :tags,
                status = :status,
                updated_at = NOW()
            WHERE id = :id');
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':privacy', $data['privacy']);
        $this->db->bind(':rules', $data['rules']);
        $this->db->bind(':tags', $data['tags']);
        $this->db->bind(':status', $data['status']);

        if($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // UPDATE - Update community status only
    public function updateStatus($id, $status) {
        $this->db->query('UPDATE communities 
            SET status = :status, updated_at = NOW()
            WHERE id = :id');
        
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);

        if($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // DELETE - Delete community
    public function delete($id) {
        $this->db->query('DELETE FROM communities WHERE id = :id');
        $this->db->bind(':id', $id);

        if($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Get dashboard statistics
   
// Get dashboard statistics - FIXED VERSION
public function getStats() {
    // Get total and active communities
    $this->db->query('SELECT 
        COUNT(*) as total_communities,
        SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_communities
    FROM communities');
    
    $communityStats = $this->db->single();
    
    // Get total members
    $this->db->query('SELECT COUNT(*) as total_members FROM community_members');
    $memberStats = $this->db->single();
    
    // Get total posts
    $this->db->query('SELECT COUNT(*) as total_posts FROM posts');
    $postStats = $this->db->single();
    
    // Debug: Log the results
    error_log("Community Stats: " . print_r($communityStats, true));
    error_log("Member Stats: " . print_r($memberStats, true));
    error_log("Post Stats: " . print_r($postStats, true));
    
    return [
        'total_communities' => (int)($communityStats->total_communities ?? 0),
        'active_communities' => (int)($communityStats->active_communities ?? 0),
        'total_members' => (int)($memberStats->total_members ?? 0),
        'total_posts' => (int)($postStats->total_posts ?? 0)
    ];
}

    // Check if community name already exists
    public function communityNameExists($name, $excludeId = null) {
        if($excludeId) {
            $this->db->query('SELECT id FROM communities WHERE name = :name AND id != :id');
            $this->db->bind(':id', $excludeId);
        } else {
            $this->db->query('SELECT id FROM communities WHERE name = :name');
        }
        
        $this->db->bind(':name', $name);
        
        $row = $this->db->single();
        
        return $row ? true : false;
    }
}