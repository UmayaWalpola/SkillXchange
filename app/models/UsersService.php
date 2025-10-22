<?php

class UserService {
    private $db;

    public function __construct() {
        // connect to database using your Database class
        $this->db = (new Database())->connect();
    }

    // ✅ Get total individual users
    public function getTotalUsers() {
        $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'individual'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    // ✅ Get total organizations
    public function getTotalOrganizations() {
        $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'organization'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    // ✅ Get total active projects
    public function getTotalProjects() {
        $sql = "SELECT COUNT(*) as count FROM projects WHERE status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    // ✅ Get recent users
    public function getRecentUsers($limit = 5) {
        $sql = "SELECT id, username, email, created_at, status 
                FROM users 
                WHERE role = 'individual'
                ORDER BY created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ✅ Get recent projects
    public function getRecentProjects($limit = 5) {
        $sql = "SELECT id, title, description, created_at, status 
                FROM projects 
                ORDER BY created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}