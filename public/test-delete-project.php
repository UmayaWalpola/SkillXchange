<?php
session_start();
require_once "../app/config/config.php";
require_once "../core/Database.php";
require_once "../app/models/Project.php";

// Simulate org session
$_SESSION['user_id'] = 37;
$_SESSION['role'] = 'organization';

// Create a temporary project to delete
$db = new Database();
$db->query("INSERT INTO projects (organization_id, name, description, category, status, required_skills, max_members, created_at) VALUES (37, 'Tmp Delete', 'tmp', 'web', 'active', 'php', 3, NOW())");
$db->execute();
$projectId = $db->connect()->lastInsertId();

echo "Created project id: $projectId\n";

// Simulate POST

echo "Calling OrganizationController::deleteProject...\n";
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['project_id'] = $projectId;

require_once "../core/Controller.php";
require_once "../app/controllers/OrganizationController.php";
$ctrl = new OrganizationController();
$ctrl->deleteProject();

// Verify
$db->query("SELECT id FROM projects WHERE id = :id");
$db->bind(':id', $projectId);
$exists = $db->single();
if ($exists) echo "Project still exists\n"; else echo "Project deleted\n";
?>