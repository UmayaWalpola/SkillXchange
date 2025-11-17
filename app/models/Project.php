<?php
// app/models/Project.php

class Project {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }

    /* ============================================================
       CREATE PROJECT
    ============================================================ */
    public function createProject($data) {
        $this->db->query("
            INSERT INTO projects (
                organization_id, name, description, category, status,
                required_skills, max_members, start_date, end_date
            )
            VALUES (
                :organization_id, :name, :description, :category, :status,
                :required_skills, :max_members, :start_date, :end_date
            )
        ");

        $this->db->bind(':organization_id', $data['org_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':required_skills', $data['required_skills']);
        $this->db->bind(':max_members', $data['max_members']);
        $this->db->bind(':start_date', $data['start_date']);
        $this->db->bind(':end_date', $data['end_date']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /* ============================================================
       READ PROJECTS
    ============================================================ */

    // For Organization Dashboard (projects list)
    public function getProjectsByOrganization($org_id) {
        $this->db->query("
            SELECT * FROM projects 
            WHERE organization_id = :org_id 
            ORDER BY created_at DESC
        ");

        $this->db->bind(':org_id', $org_id);
        return $this->db->resultSet();
    }

    // For editing / viewing a single project
    public function getProjectById($id) {
        $this->db->query("
            SELECT p.*, 
            (
                SELECT COUNT(*) 
                FROM project_members 
                WHERE project_id = p.id AND status = 'active'
            ) AS current_members
            FROM projects p
            WHERE p.id = :id
        ");

        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /* ============================================================
       UPDATE PROJECT
    ============================================================ */
    public function updateProject($data) {
        $this->db->query("
            UPDATE projects SET 
                name = :name,
                description = :description,
                category = :category,
                status = :status,
                required_skills = :required_skills,
                max_members = :max_members,
                start_date = :start_date,
                end_date = :end_date,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND organization_id = :org_id
        ");

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

    /* ============================================================
       DELETE PROJECT
    ============================================================ */
    public function deleteProject($projectId, $orgId) {
        $this->db->query("
            DELETE FROM projects 
            WHERE id = :id AND organization_id = :org_id
        ");

        $this->db->bind(':id', $projectId);
        $this->db->bind(':org_id', $orgId);

        return $this->db->execute();
    }

    /* ============================================================
       ORGANIZATION DASHBOARD STATS
       (Fixes your profile fatal error)
    ============================================================ */
    public function getOrganizationStats($org_id) {
        $this->db->query("
            SELECT 
                COUNT(*) AS total_projects,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_projects,
                SUM(CASE WHEN status = 'in-progress' THEN 1 ELSE 0 END) AS in_progress_projects,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_projects
            FROM projects
            WHERE organization_id = :org_id
        ");

        $this->db->bind(':org_id', $org_id);
        return $this->db->single();
    }

    /* ============================================================
       APPLICATION STATS
       (Fixes applications dashboard zero-count issues)
    ============================================================ */
    public function getApplicationStats($org_id) {
        $this->db->query("
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) AS accepted,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected
            FROM project_applications
            WHERE project_id IN (
                SELECT id FROM projects WHERE organization_id = :org_id
            )
        ");

        $this->db->bind(':org_id', $org_id);
        return $this->db->single();
    }

    /* ============================================================
       SEARCH/FILTER PROJECTS
    ============================================================ */
    public function searchProjects($org_id, $filters = []) {
        $query = "SELECT * FROM projects WHERE organization_id = :org_id";

        if (!empty($filters['search'])) {
            $query .= " AND (name LIKE :search OR description LIKE :search)";
        }

        if (!empty($filters['status']) && $filters['status'] != 'all') {
            $query .= " AND status = :status";
        }

        if (!empty($filters['category']) && $filters['category'] != 'all') {
            $query .= " AND category = :category";
        }

        $query .= " ORDER BY created_at DESC";

        $this->db->query($query);
        $this->db->bind(':org_id', $org_id);

        if (!empty($filters['search'])) {
            $term = "%" . $filters['search'] . "%";
            $this->db->bind(':search', $term);
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
