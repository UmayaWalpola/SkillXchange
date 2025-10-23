<?php
// app/models/Project.php

class Project {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Create a new project
    public function createProject($data) {
        $this->db->query('INSERT INTO projects (
            organization_id, 
            name, 
            description, 
            category, 
            status, 
            required_skills, 
            max_members, 
            start_date, 
            end_date
        ) VALUES (
            :organization_id, 
            :name, 
            :description, 
            :category, 
            :status, 
            :required_skills, 
            :max_members, 
            :start_date, 
            :end_date
        )');
        
        // Bind values
        $this->db->bind(':organization_id', $data['org_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':required_skills', $data['required_skills']);
        $this->db->bind(':max_members', $data['max_members']);
        $this->db->bind(':start_date', $data['start_date']);
        $this->db->bind(':end_date', $data['end_date']);
        
        // Execute
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    // Get all projects by organization
    public function getProjectsByOrganization($organizationId) {
        $this->db->query('SELECT * FROM projects 
                         WHERE organization_id = :org_id 
                         ORDER BY created_at DESC');
        $this->db->bind(':org_id', $organizationId);
        
        return $this->db->resultSet();
    }
    
    // Get project by ID
    public function getProjectById($projectId) {
        $this->db->query('SELECT p.*, 
                         (SELECT COUNT(*) FROM project_members WHERE project_id = p.id AND status = "active") as current_members
                         FROM projects p 
                         WHERE p.id = :id');
        $this->db->bind(':id', $projectId);
        
        return $this->db->single();
    }
    
    // Update project
    public function updateProject($data) {
        $this->db->query('UPDATE projects SET 
            name = :name,
            description = :description,
            category = :category,
            status = :status,
            required_skills = :required_skills,
            max_members = :max_members,
            start_date = :start_date,
            end_date = :end_date,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND organization_id = :org_id');
        
        // Bind values
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':org_id', $data['org_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':required_skills', $data['required_skills']);
        $this->db->bind(':max_members', $data['max_members']);
        $this->db->bind(':start_date', $data['start_date']);
        $this->db->bind(':end_date', $data['end_date']);
        
        return $this->db->execute();
    }
    
    // Delete project
    public function deleteProject($projectId, $organizationId) {
        $this->db->query('DELETE FROM projects 
                         WHERE id = :id AND organization_id = :org_id');
        $this->db->bind(':id', $projectId);
        $this->db->bind(':org_id', $organizationId);
        
        return $this->db->execute();
    }
    
    // Get project statistics for organization
    public function getOrganizationStats($organizationId) {
        $this->db->query('SELECT 
            COUNT(*) as total_projects,
            SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_projects,
            SUM(CASE WHEN status = "in-progress" THEN 1 ELSE 0 END) as in_progress_projects,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_projects
            FROM projects 
            WHERE organization_id = :org_id');
        $this->db->bind(':org_id', $organizationId);
        
        return $this->db->single();
    }
    
    // Search and filter projects
    public function searchProjects($organizationId, $filters = []) {
        $query = 'SELECT * FROM projects WHERE organization_id = :org_id';
        
        if (!empty($filters['search'])) {
            $query .= ' AND (name LIKE :search OR description LIKE :search)';
        }
        
        if (!empty($filters['status']) && $filters['status'] != 'all') {
            $query .= ' AND status = :status';
        }
        
        if (!empty($filters['category']) && $filters['category'] != 'all') {
            $query .= ' AND category = :category';
        }
        
        $query .= ' ORDER BY created_at DESC';
        
        $this->db->query($query);
        $this->db->bind(':org_id', $organizationId);
        
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $this->db->bind(':search', $searchTerm);
        }
        
        if (!empty($filters['status']) && $filters['status'] != 'all') {
            $this->db->bind(':status', $filters['status']);
        }
        
        if (!empty($filters['category']) && $filters['category'] != 'all') {
            $this->db->bind(':category', $filters['category']);
        }
        
        return $this->db->resultSet();
    }
}
?>