<?php
// app/models/Project.php

class Project {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    
       //CREATE / READ / UPDATE / DELETE

    // ============================================================
    // 1. CREATE PROJECT (Insert new project into database)
    // ============================================================
    public function createProject($data) {
        $this->db->query("INSERT INTO projects (organization_id, name, description, category, status, required_skills, max_members, start_date, end_date) VALUES (:organization_id, :name, :description, :category, :status, :required_skills, :max_members, :start_date, :end_date)");
        
        // Bind array keys from Controller to Query Placeholders
        $this->db->bind(':organization_id', $data['org_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':required_skills', $data['required_skills']);
        $this->db->bind(':max_members', $data['max_members']);
        $this->db->bind(':start_date', $data['start_date']);
        $this->db->bind(':end_date', $data['end_date']);
        
        // Execute the query. If it successfully saves to DB, get the ID.
        if ($this->db->execute()) {
            return $this->db->lastInsertId(); // Return new created project ID
        }
        return false;
    }

    // ============================================================
    // 2. READ ALL PROJECTS (Get all projects for an organization)
    // ============================================================
    public function getProjectsByOrganization($org_id) {
        $this->db->query("SELECT * FROM projects WHERE organization_id = :org_id ORDER BY created_at DESC");
        $this->db->bind(':org_id', $org_id);
        
        // We use resultSet() because we need MANY records (array)
        return $this->db->resultSet();
    }

    // ============================================================
    // 3. READ SINGLE PROJECT (Get one project by ID)
    // ============================================================
    public function getProjectById($id) {
        $this->db->query("SELECT p.*, (SELECT COUNT(*) FROM project_members WHERE project_id = p.id AND status = 'active') AS current_members FROM projects p WHERE p.id = :id");
        $this->db->bind(':id', $id);
        
        // We use single() because we need only ONE record (object)
        return $this->db->single();
    }

    // Get active members with basic profile info for chats
    public function getProjectMembers($projectId) {
        $this->db->query("SELECT u.id, u.username, u.profile_picture FROM project_members pm JOIN users u ON pm.user_id = u.id WHERE pm.project_id = :project_id AND pm.status = 'active' ORDER BY u.username ASC");
        $this->db->bind(':project_id', $projectId);
        return $this->db->resultSet();
    }

    // ============================================================
    // 4. UPDATE PROJECT (Edit project details)
    // ============================================================
    public function updateProject($data) {
        // Write UPDATE query. Make sure we check for organization_id so a user can't edit someone else's project
        $this->db->query("UPDATE projects SET name = :name, description = :description, category = :category, status = :status, required_skills = :required_skills, max_members = :max_members, start_date = :start_date, end_date = :end_date, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND organization_id = :org_id");
        
        // Bind array keys from Controller to Query Placeholders
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
        
        // Execute the query. Returns true if it updates.
        return $this->db->execute();
    }

    // ============================================================
    // 5. DELETE PROJECT (Remove project)
    // ============================================================
    public function deleteProject($projectId, $orgId) {
        // Write DELETE query. Check organization_id for security!
        $this->db->query("DELETE FROM projects WHERE id = :id AND organization_id = :org_id");
        $this->db->bind(':id', $projectId);
        $this->db->bind(':org_id', $orgId);
        
        // Execute query. Returns true if deleted.
        return $this->db->execute();
    }

    /* ============================================================
       STATS / SEARCH
    ============================================================ */
    public function getOrganizationStats($org_id) {
        $this->db->query("SELECT COUNT(*) AS total_projects, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_projects, SUM(CASE WHEN status = 'in-progress' THEN 1 ELSE 0 END) AS in_progress_projects, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_projects FROM projects WHERE organization_id = :org_id");
        $this->db->bind(':org_id', $org_id);
        return $this->db->single();
    }

    public function getApplicationStats($org_id) {
        $this->db->query("SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending, SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) AS accepted, SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected FROM project_applications WHERE project_id IN (SELECT id FROM projects WHERE organization_id = :org_id)");
        $this->db->bind(':org_id', $org_id);
        return $this->db->single();
    }
           //Filter Logic
    public function searchProjects($org_id, $filters = []) {
        $query = "SELECT * FROM projects WHERE organization_id = :org_id";
        if (!empty($filters['search'])) $query .= " AND (name LIKE :search OR description LIKE :search)";
        if (!empty($filters['status']) && $filters['status'] != 'all') $query .= " AND status = :status";
        if (!empty($filters['category']) && $filters['category'] != 'all') $query .= " AND category = :category";
        $query .= " ORDER BY created_at DESC";
        $this->db->query($query);
        $this->db->bind(':org_id', $org_id);
        if (!empty($filters['search'])) $this->db->bind(':search', "%" . $filters['search'] . "%");
        if (!empty($filters['status']) && $filters['status'] != 'all') $this->db->bind(':status', $filters['status']);
        if (!empty($filters['category']) && $filters['category'] != 'all') $this->db->bind(':category', $filters['category']);
        return $this->db->resultSet();
    }

    /* ============================================================
       APPLICATIONS
    ============================================================ */

    public function getUserApplication($projectId, $userId) {
        // Return the latest application by id (most recent)
        $this->db->query("SELECT * FROM project_applications WHERE project_id = :project_id AND user_id = :user_id ORDER BY id DESC LIMIT 1");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':user_id', $userId);
        return $this->db->single();
    }

    /**
     * Check if a user is an active member of a project
     * @return bool
     */
    public function isUserMember($projectId, $userId)
    {
        $this->db->query("SELECT id FROM project_members WHERE project_id = :project_id AND user_id = :user_id AND status = 'active' LIMIT 1");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':user_id', $userId);
        $row = $this->db->single();
        return !empty($row);
    }

    public function applyToProject($projectId, $userId, $message = null) {
        // prevent duplicate
        $this->db->query("SELECT id FROM project_applications WHERE project_id = :project_id AND user_id = :user_id");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':user_id', $userId);
        if ($this->db->single()) return false;

        $this->db->query("INSERT INTO project_applications (project_id, user_id, message, status, applied_at) VALUES (:project_id, :user_id, :message, 'pending', CURRENT_TIMESTAMP)");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':message', $message);
        return $this->db->execute();
    }

    public function applyToProjectAdvanced($projectId, $userId, $experience, $skills, $contribution, $commitment, $duration, $motivation, $portfolio = '') {
        // Prevent duplicate applications
        $this->db->query("SELECT id FROM project_applications WHERE project_id = :project_id AND user_id = :user_id");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':user_id', $userId);
        if ($this->db->single()) {
            return false;
        }

        // Insert application with all fields
        $this->db->query("
            INSERT INTO project_applications 
            (project_id, user_id, message, experience, skills, contribution, commitment, duration, motivation, portfolio, status, applied_at) 
            VALUES 
            (:project_id, :user_id, :message, :experience, :skills, :contribution, :commitment, :duration, :motivation, :portfolio, 'pending', NOW())
        ");
        
        $this->db->bind(':project_id', (int)$projectId);
        $this->db->bind(':user_id', (int)$userId);
        $this->db->bind(':message', 'Advanced Application');
        $this->db->bind(':experience', $experience);
        $this->db->bind(':skills', $skills);
        $this->db->bind(':contribution', $contribution);
        $this->db->bind(':commitment', $commitment);
        $this->db->bind(':duration', $duration);
        $this->db->bind(':motivation', $motivation);
        $this->db->bind(':portfolio', $portfolio ?: null);
        
        return $this->db->execute();
    }

    public function cancelApplication($projectId, $userId) {
        $this->db->query("DELETE FROM project_applications WHERE project_id = :project_id AND user_id = :user_id AND status = 'pending'");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    public function getApplicationsByProject($projectId) {
        $this->db->query("SELECT pa.*, u.username, u.email, u.profile_picture, u.bio, GROUP_CONCAT(us.skill_name SEPARATOR ', ') AS user_skills, (SELECT COUNT(*) FROM user_projects WHERE user_id = u.id AND status = 'completed') AS completed_projects, (SELECT AVG(rating) FROM user_feedback WHERE user_id = u.id) AS user_rating FROM project_applications pa JOIN users u ON pa.user_id = u.id LEFT JOIN user_skills us ON us.user_id = u.id WHERE pa.project_id = :project_id GROUP BY pa.id ORDER BY pa.applied_at DESC");
        $this->db->bind(':project_id', $projectId);
        return $this->db->resultSet();
    }

    public function getAllApplicationsForOrganization($org_id) {
        $this->db->query("SELECT pa.id, pa.project_id, pa.user_id, pa.message, pa.experience, pa.skills, pa.contribution, pa.commitment, pa.duration, pa.motivation, pa.portfolio, pa.status, pa.applied_at, p.name AS project_name, u.username AS user_name, u.email AS user_email, u.profile_picture, u.bio AS user_title, GROUP_CONCAT(DISTINCT us.skill_name SEPARATOR ', ') AS user_skills, (SELECT COUNT(*) FROM user_projects WHERE user_id = u.id AND status = 'completed') AS completed_projects, (SELECT IFNULL(ROUND(AVG(rating),2),0) FROM user_feedback WHERE user_id = u.id) AS user_rating FROM project_applications pa JOIN projects p ON pa.project_id = p.id JOIN users u ON pa.user_id = u.id LEFT JOIN user_skills us ON us.user_id = u.id WHERE p.organization_id = :org_id GROUP BY pa.id ORDER BY pa.applied_at DESC");
        $this->db->bind(':org_id', $org_id);
        return $this->db->resultSet();
    }

    /**
     * Save a full application with extended fields
     * Expects $data array with keys:
     * project_id, user_id, relevant_experience, matching_skills, contribution,
     * availability, expected_duration, motivation, portfolio_link
     * Returns boolean
     */
    public function saveFullApplication($data)
    {
        // minimal sanitation
        $projectId = (int)($data['project_id'] ?? 0);
        $userId = (int)($data['user_id'] ?? 0);
        $relevant = trim($data['relevant_experience'] ?? '');
        $matching = trim($data['matching_skills'] ?? '');
        $contribution = trim($data['contribution'] ?? '');
        $availability = trim($data['availability'] ?? '');
        $duration = trim($data['expected_duration'] ?? ($data['duration'] ?? ''));
        $motivation = trim($data['motivation'] ?? '');
        $portfolio = trim($data['portfolio_link'] ?? $data['portfolio'] ?? '');

        if ($projectId <= 0 || $userId <= 0) return false;

        // Many installations use the older column names (experience, skills, commitment, duration, portfolio)
        // Attempt to insert using the most common column names so views and existing queries continue to work.
        $this->db->query("INSERT INTO project_applications (project_id, user_id, experience, skills, contribution, commitment, duration, motivation, portfolio, status, applied_at) VALUES (:project_id, :user_id, :experience, :skills, :contribution, :commitment, :duration, :motivation, :portfolio, 'pending', CURRENT_TIMESTAMP)");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':experience', $relevant);
        $this->db->bind(':skills', $matching);
        $this->db->bind(':contribution', $contribution);
        $this->db->bind(':commitment', $availability);
        $this->db->bind(':duration', $duration);
        $this->db->bind(':motivation', $motivation);
        $this->db->bind(':portfolio', $portfolio ?: null);

        return $this->db->execute();
    }

    // helper: check capacity
    public function checkProjectCapacity($projectId) {
        $this->db->query("SELECT max_members, current_members FROM projects WHERE id = :id");
        $this->db->bind(':id', $projectId);
        $p = $this->db->single();
        if (!$p) return ['exists' => false];
        return ['exists' => true, 'max' => (int)$p->max_members, 'current' => (int)$p->current_members, 'full' => ((int)$p->current_members >= (int)$p->max_members)];
    }

    // ============================================================
    // ACCEPT PROJECT APPLICATION (Assigned to: Kithsara)
    // ============================================================
    public function acceptApplication($applicationId, $org_id) {
        
        // Step 1: Find the application in the database
        $this->db->query("SELECT * FROM project_applications WHERE id = :id");
        $this->db->bind(':id', $applicationId);
        $app = $this->db->single();
        
        // Error checking: if application is missing or already processed
        if (!$app) return ['success' => false, 'message' => 'Application not found.'];
        if ($app->status !== 'pending') return ['success' => false, 'message' => 'Application already ' . $app->status . '.'];

        // Step 2: Find the related project and verify if this organization owns it
        $this->db->query("SELECT id, organization_id, max_members, current_members FROM projects WHERE id = :pid");
        $this->db->bind(':pid', $app->project_id);
        $project = $this->db->single();
        
        if (!$project) return ['success' => false, 'message' => 'Project not found.'];
        if ($project->organization_id != $org_id) return ['success' => false, 'message' => 'Access denied.'];

        // Step 3: Check if the project already has maximum members
        if ((int)$project->current_members >= (int)$project->max_members) {
            return ['success' => false, 'message' => 'Project is full.'];
        }

        // Step 4: Database Transactions (Run multiple queries safely)
        $pdo = $this->db->connect();
        try {
            $pdo->beginTransaction(); // Start tracking changes

            // Query A: Add the accepted user as a new "Member" in project_members table
            $this->db->query("INSERT INTO project_members (project_id, user_id, role, joined_at, status) VALUES (:project_id, :user_id, 'Member', CURRENT_TIMESTAMP, 'active')");
            $this->db->bind(':project_id', $app->project_id);
            $this->db->bind(':user_id', $app->user_id);
            $this->db->execute();

            // Query B: Update the application status from 'pending' to 'accepted'
            $this->db->query("UPDATE project_applications SET status = 'accepted' WHERE id = :id");
            $this->db->bind(':id', $applicationId);
            $this->db->execute();

            // Query C: Increase the current_members count in the projects table by 1
            $this->db->query("UPDATE projects SET current_members = current_members + 1 WHERE id = :pid");
            $this->db->bind(':pid', $project->id);
            $this->db->execute();

            $pdo->commit(); // Save all changes
            return ['success' => true, 'message' => 'Applicant accepted.'];
            
        } catch (Exception $e) {
            $pdo->rollBack(); // If any query fails, undo ALL changes
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // ============================================================
    // REJECT PROJECT APPLICATION (Assigned to: Kithsara)
    // ============================================================
    public function rejectApplication($applicationId, $org_id) {
        
        // Step 1: Load application from DB
        $this->db->query("SELECT * FROM project_applications WHERE id = :id");
        $this->db->bind(':id', $applicationId);
        $app = $this->db->single();
        
        if (!$app) return ['success' => false, 'message' => 'Application not found.'];
        if ($app->status === 'accepted') return ['success' => false, 'message' => 'Cannot reject an already accepted application.'];

        // Step 2: Verify the organization owns this project
        $this->db->query("SELECT organization_id FROM projects WHERE id = :pid");
        $this->db->bind(':pid', $app->project_id);
        $project = $this->db->single();
        
        if (!$project) return ['success' => false, 'message' => 'Project not found.'];
        if ($project->organization_id != $org_id) return ['success' => false, 'message' => 'Access denied.'];

        // Step 3: Update application status to 'rejected'
        $this->db->query("UPDATE project_applications SET status = 'rejected' WHERE id = :id");
        $this->db->bind(':id', $applicationId);
        
        $ok = $this->db->execute();
        
        if ($ok) return ['success' => true, 'message' => 'Application rejected.'];
        return ['success' => false, 'message' => 'Failed to update application.'];
    }

    // ============================================================
    // ROLE ASSIGNMENT / MEMBERS MANAGEMENT (Assigned to: Kithsara)
    // ============================================================
    
    // Get all accepted members for a project
    public function getMembersByProject($projectId) {
        // This query joins project_members, users, and user_skills to get full member details
        $this->db->query("SELECT pm.id, pm.project_id, pm.user_id, pm.role, pm.joined_at, pm.status, u.username, u.email, u.profile_picture, GROUP_CONCAT(DISTINCT us.skill_name SEPARATOR ', ') AS user_skills, (SELECT COUNT(*) FROM user_projects WHERE user_id = u.id AND status = 'completed') AS completed_projects, (SELECT IFNULL(ROUND(AVG(rating),2),0) FROM user_feedback WHERE user_id = u.id) AS user_rating FROM project_members pm JOIN users u ON pm.user_id = u.id LEFT JOIN user_skills us ON us.user_id = u.id WHERE pm.project_id = :project_id AND pm.status = 'active' GROUP BY pm.id ORDER BY pm.joined_at ASC");
        $this->db->bind(':project_id', $projectId);
        return $this->db->resultSet();
    }

    // ============================================================
    // ASSIGN ROLES TO MEMBERS (Assigned to: Kithsara)
    // ============================================================
    public function updateMemberRoleWithOrg($memberId, $role, $org_id) {
        
        // Step 1: Find if member exists and belongs to a project owned by this organization
        $this->db->query("SELECT pm.id, pm.project_id, p.organization_id FROM project_members pm JOIN projects p ON pm.project_id = p.id WHERE pm.id = :member_id");
        $this->db->bind(':member_id', $memberId);
        $member = $this->db->single();

        if (!$member) return ['success' => false, 'message' => 'Member not found.'];
        if ($member->organization_id != $org_id) return ['success' => false, 'message' => 'Access denied.'];

        // Step 2: Make sure role text is not empty or too long
        $role = trim($role);
        if (empty($role) || strlen($role) > 100) {
            return ['success' => false, 'message' => 'Invalid role.'];
        }

        // Step 3: Update role in project_members table
        $this->db->query("UPDATE project_members SET role = :role WHERE id = :member_id");
        $this->db->bind(':role', $role);
        $this->db->bind(':member_id', $memberId);
        $ok = $this->db->execute();

        if ($ok) return ['success' => true, 'message' => 'Role updated successfully.'];
        return ['success' => false, 'message' => 'Failed to update role.'];
    }

    /**
     * Update a project member's role (simple version)
     * This uses prepared statements and returns boolean true/false
     * Signature: updateMemberRole($memberId, $role)
     */
    public function updateMemberRoleSimple($memberId, $role)
    {
        $memberId = (int)$memberId;
        $role = trim($role);
        if ($memberId <= 0 || $role === '') return false;

        // Use the Database wrapper (prepared statements)
        $this->db->query("UPDATE project_members SET role = :role WHERE id = :id");
        $this->db->bind(':role', $role);
        $this->db->bind(':id', $memberId);
        return $this->db->execute();
    }

    // Backwards-compatible alias matching requested signature
    public function updateMemberRole($memberId, $role)
    {
        return $this->updateMemberRoleSimple($memberId, $role);
    }

    public function isOrganizationOwner($projectId, $org_id) {
        $this->db->query("SELECT organization_id FROM projects WHERE id = :project_id");
        $this->db->bind(':project_id', $projectId);
        $project = $this->db->single();

        if (!$project) return false;
        return $project->organization_id == $org_id;
    }

    /**
     * Remove a member from a project (by project_members.id)
     * Ensures the organization owns the project and adjusts current_members.
     */
    public function removeMember($memberId, $projectId, $org_id) {
        // verify member and ownership
        $this->db->query("SELECT pm.id, pm.project_id, pm.user_id, p.organization_id FROM project_members pm JOIN projects p ON pm.project_id = p.id WHERE pm.id = :member_id AND pm.project_id = :project_id");
        $this->db->bind(':member_id', $memberId);
        $this->db->bind(':project_id', $projectId);
        $row = $this->db->single();

        if (!$row) return ['success' => false, 'message' => 'Member not found.'];
        if ($row->organization_id != $org_id) return ['success' => false, 'message' => 'Access denied.'];

        $pdo = $this->db->connect();
        try {
            $pdo->beginTransaction();

            // mark member as removed or delete (use soft delete: set status = 'removed')
            $this->db->query("UPDATE project_members SET status = 'removed' WHERE id = :member_id");
            $this->db->bind(':member_id', $memberId);
            $this->db->execute();

            // decrement project current_members but not below 0
            $this->db->query("UPDATE projects SET current_members = GREATEST(current_members - 1, 0) WHERE id = :project_id");
            $this->db->bind(':project_id', $projectId);
            $this->db->execute();

            $pdo->commit();
            return ['success' => true, 'message' => 'Member removed from project.'];
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Create a user report tied to a project (reported by an organization)
     */
    public function createUserReport($projectId, $reportedUserId, $reporterOrgId, $reason, $details = null) {
        $this->db->query("INSERT INTO user_reports (project_id, reported_user_id, reporter_org_id, reason, details, status, reported_at) VALUES (:project_id, :reported_user_id, :reporter_org_id, :reason, :details, 'pending', CURRENT_TIMESTAMP)");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':reported_user_id', $reportedUserId);
        $this->db->bind(':reporter_org_id', $reporterOrgId);
        $this->db->bind(':reason', $reason);
        $this->db->bind(':details', $details);
        if ($this->db->execute()) return ['success' => true, 'message' => 'Report submitted.'];
        return ['success' => false, 'message' => 'Failed to submit report.'];
    }

    /* ============================================================
       USER PROJECTS (For Individual User Dashboard)
    ============================================================ */

    public function getProjectsForUser($userId) {
        // Get projects where user is an active member
        $this->db->query(
            "SELECT p.*, 
            (SELECT COUNT(*) FROM project_members WHERE project_id = p.id AND status='active') AS current_members 
            FROM projects p 
            WHERE p.id IN (
                SELECT project_id FROM project_members 
                WHERE user_id = :user_id AND status = 'active'
            )
            ORDER BY p.created_at DESC"
        );
        $this->db->bind(':user_id', $userId);
        $results = $this->db->resultSet();
        
        error_log('DEBUG: getProjectsForUser returned ' . count($results ?? []) . ' projects for user ' . $userId);
        
        return $results;
    }

    // Get ALL active projects (for debugging / browse page)
    public function getAllActiveProjects() {
        $this->db->query(
            "SELECT p.*, 
            (SELECT COUNT(*) FROM project_members WHERE project_id = p.id AND status='active') AS current_members 
            FROM projects p 
            WHERE p.status IN ('active', 'in-progress')
            ORDER BY p.created_at DESC"
        );
        return $this->db->resultSet();
    }

    // Get every project from DB (all statuses) for discover page
    public function getAllProjects() {
        $this->db->query(
            "SELECT p.*, 
            (SELECT COUNT(*) FROM project_members WHERE project_id = p.id AND status='active') AS current_members 
            FROM projects p 
            ORDER BY p.created_at DESC"
        );
        return $this->db->resultSet();
    }

    /**
     * Normalize skill labels for reliable matching (case-insensitive, dash/underscore tolerant).
     */
    private function normalizeSkillName($skill)
    {
        $skill = strtolower(trim((string)$skill));
        $skill = str_replace(['_', '-'], ' ', $skill);
        $skill = preg_replace('/\s+/', ' ', $skill);
        $skill = trim($skill);

        $aliases = [
            'db management' => 'database management',
            'database' => 'database management',
            'database development' => 'database management',
            'github & git' => 'github and git',
            'git and github' => 'github and git',
            'github' => 'github and git',
            'dev ops' => 'devops',
            'frontend' => 'frontend frameworks',
            'front end frameworks' => 'frontend frameworks',
            'backend' => 'backend development',
            'back end development' => 'backend development',
            'mobile' => 'mobile app development',
            'cloud' => 'cloud computing',
            'web dev' => 'web development',
            'ai' => 'ai and ml',
            'ai ml' => 'ai and ml',
            'marketing' => 'digital marketing',
            'data analytics' => 'data analysis & visualization',
            'data analysis and visualization' => 'data analysis & visualization'
        ];

        if (isset($aliases[$skill])) {
            return $aliases[$skill];
        }

        return $skill;
    }

    /**
     * Split comma-separated skills into normalized key + display label pairs.
     */
    private function parseSkillEntries($csv)
    {
        $raw = explode(',', (string)$csv);
        $entries = [];
        $seen = [];

        foreach ($raw as $item) {
            $label = trim((string)$item);
            $key = $this->normalizeSkillName($label);

            if ($key === '' || isset($seen[$key])) {
                continue;
            }

            $entries[] = [
                'key' => $key,
                'label' => $label !== '' ? $label : ucwords($key)
            ];
            $seen[$key] = true;
        }

        return $entries;
    }

    /**
     * Split comma-separated skill string and normalize each token.
     */
    private function parseSkillList($csv)
    {
        $skills = [];

        foreach ($this->parseSkillEntries($csv) as $entry) {
            $skills[$entry['key']] = true;
        }

        return array_keys($skills);
    }

    /**
     * Fetch user's teach skills with proficiency levels keyed by normalized skill.
     */
    public function getUserTeachSkillsWithLevel($userId)
    {
        $this->db->query("SELECT skill_name, proficiency_level FROM user_skills WHERE user_id = :user_id AND skill_type = 'teach'");
        $this->db->bind(':user_id', (int)$userId);
        $rows = $this->db->resultSet();

        $rank = [
            'beginner' => 1,
            'intermediate' => 2,
            'advanced' => 3
        ];

        $skills = [];
        foreach ($rows as $row) {
            $rawName = trim((string)($row->skill_name ?? ''));
            $normalized = $this->normalizeSkillName($rawName);
            if ($normalized === '') {
                continue;
            }

            $levelRaw = strtolower(trim((string)($row->proficiency_level ?? '')));
            $level = isset($rank[$levelRaw]) ? $levelRaw : 'beginner';

            if (!isset($skills[$normalized])) {
                $skills[$normalized] = [
                    'name' => $rawName,
                    'level' => $level
                ];
                continue;
            }

            $existing = strtolower((string)$skills[$normalized]['level']);
            $existingRank = $rank[$existing] ?? 0;
            $currentRank = $rank[$level] ?? 0;
            if ($currentRank > $existingRank) {
                $skills[$normalized]['level'] = $level;
            }
        }

        return $skills;
    }

    /**
     * Fetch user's teach skills as normalized values.
     */
    public function getUserTeachSkills($userId)
    {
        return array_keys($this->getUserTeachSkillsWithLevel($userId));
    }

    /**
     * Return only projects whose required skills overlap with user's teach skills.
     */
    public function getSuggestedProjectsForUser($userId)
    {
        $projects = $this->getAllProjects();
        $teachSkills = $this->getUserTeachSkillsWithLevel($userId);

        if (empty($teachSkills)) {
            return [];
        }

        $teachSkillSet = array_fill_keys(array_keys($teachSkills), true);
        $suggested = [];

        foreach ($projects as $project) {
            $requiredEntries = $this->parseSkillEntries($project->required_skills ?? '');
            $requiredSkills = [];
            foreach ($requiredEntries as $entry) {
                $requiredSkills[] = $entry['key'];
            }
            if (empty($requiredSkills)) {
                continue;
            }

            $matched = [];
            foreach ($requiredEntries as $requiredSkill) {
                if (isset($teachSkillSet[$requiredSkill['key']])) {
                    $matched[] = $requiredSkill['label'];
                }
            }

            if (!empty($matched)) {
                $project->matched_skills = $matched;
                $project->matched_skill_count = count($matched);
                $project->required_skill_count = count($requiredSkills);
                $suggested[] = $project;
            }
        }

        return $suggested;
    }

    /**
     * Validate whether user is allowed to apply based on required skill overlap.
     */
    public function getSkillMatchSummaryForProject($projectId, $userId)
    {
        $project = $this->getProjectById((int)$projectId);
        if (!$project) {
            return [
                'allowed' => false,
                'required_skills' => [],
                'user_teach_skills' => [],
                'matched_skills' => []
            ];
        }

        $requiredEntries = $this->parseSkillEntries($project->required_skills ?? '');
        $requiredSkills = [];
        foreach ($requiredEntries as $entry) {
            $requiredSkills[] = $entry['key'];
        }

        $userTeachSkillMap = $this->getUserTeachSkillsWithLevel($userId);
        $userTeachSkills = array_keys($userTeachSkillMap);

        if (empty($requiredSkills) || empty($userTeachSkills)) {
            return [
                'allowed' => false,
                'required_skills' => $requiredSkills,
                'user_teach_skills' => $userTeachSkills,
                'matched_skills' => []
            ];
        }

        $userSkillSet = array_fill_keys($userTeachSkills, true);
        $matched = [];
        foreach ($requiredSkills as $requiredSkill) {
            if (isset($userSkillSet[$requiredSkill])) {
                $matched[] = $requiredSkill;
            }
        }

        return [
            'allowed' => !empty($matched),
            'required_skills' => $requiredSkills,
            'user_teach_skills' => $userTeachSkills,
            'matched_skills' => $matched
        ];
    }

    /**
     * Return only project-matched teach skills with level labels for an organization view.
     */
    public function getMatchedTeachSkillsForProject($projectId, $userId)
    {
        $project = $this->getProjectById((int)$projectId);
        if (!$project) {
            return [];
        }

        $requiredEntries = $this->parseSkillEntries($project->required_skills ?? '');
        if (empty($requiredEntries)) {
            return [];
        }

        $userTeachMap = $this->getUserTeachSkillsWithLevel($userId);
        if (empty($userTeachMap)) {
            return [];
        }

        $matched = [];
        foreach ($requiredEntries as $entry) {
            $key = $entry['key'];
            if (!isset($userTeachMap[$key])) {
                continue;
            }

            $level = strtolower((string)($userTeachMap[$key]['level'] ?? 'beginner'));
            $levelLabel = ucfirst($level);
            $matched[] = $entry['label'] . ' (' . $levelLabel . ')';
        }

        return $matched;
    }

    /* ============================================================
       PROJECT PROGRESS (Tasks + Members)
    ============================================================ */

    /**
     * Get aggregated progress metrics for a project.
     * Returns associative array:
     *  - total_tasks
     *  - completed_tasks
     *  - progress_percent (0-100 float)
     *  - active_members
     */
    public function getProjectProgress($projectId)
    {
        $projectId = (int)$projectId;
        if ($projectId <= 0) {
            return [
                'total_tasks' => 0,
                'completed_tasks' => 0,
                'progress_percent' => 0,
                'active_members' => 0
            ];
        }

        // Total & completed tasks
        $this->db->query("SELECT 
                COUNT(*) AS total_tasks,
                SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS completed_tasks
            FROM project_tasks
            WHERE project_id = :project_id");
        $this->db->bind(':project_id', $projectId);
        $taskRow = $this->db->single();

        $totalTasks = (int)($taskRow->total_tasks ?? 0);
        $completedTasks = (int)($taskRow->completed_tasks ?? 0);

        // Active members
        $this->db->query("SELECT COUNT(*) AS active_members
            FROM project_members
            WHERE project_id = :project_id AND status = 'active'");
        $this->db->bind(':project_id', $projectId);
        $memberRow = $this->db->single();

        $activeMembers = (int)($memberRow->active_members ?? 0);

        $progressPercent = 0;
        if ($totalTasks > 0) {
            $progressPercent = round(($completedTasks / $totalTasks) * 100, 1);
        }

        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'progress_percent' => $progressPercent,
            'active_members' => $activeMembers
        ];
    }
  
// tasks assignining fixing
    public function getMemberRole($projectId, $userId) {
        $this->db->query("SELECT role FROM project_members WHERE project_id = :project_id AND user_id = :user_id AND status = 'active'");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? $result->role : null;
    }

    public function isProjectMember($projectId, $userId) {
        $this->db->query("SELECT id FROM project_members WHERE project_id = :project_id AND user_id = :user_id AND status = 'active'");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':user_id', $userId);
        return $this->db->single() ? true : false;
    }

}

?>
