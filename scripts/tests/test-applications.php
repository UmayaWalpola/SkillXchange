<?php
session_start();

// Simulate a logged-in individual user (not organization)
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'individual';

// Include database configuration
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../app/models/Project.php';

try {
    // Create database connection and model
    $db = new Database();
    $projectModel = new Project($db);
    
    echo "<h2>Testing Application Workflow</h2>";
    
    // Test 1: Check if user exists
    echo "<h3>Step 1: Verify User Exists</h3>";
    $database = new Database();
    $database->query("SELECT id, username, role FROM users WHERE id = 2");
    $user = $database->single();
    
    if ($user) {
        echo "<p style='color: green;'>✓ User found: {$user->username} (Role: {$user->role})</p>";
    } else {
        echo "<p style='color: red;'>✗ User not found</p>";
    }
    
    // Test 2: Create test applications
    echo "<h3>Step 2: Creating Test Applications</h3>";
    
    $testApplications = [
        ['projectId' => 6, 'userId' => 2, 'message' => 'I am very interested in cloud computing and have experience with AWS and Azure.'],
        ['projectId' => 7, 'userId' => 2, 'message' => 'I love coding and would like to contribute to this exciting project!'],
        ['projectId' => 8, 'userId' => 2, 'message' => 'Looking forward to collaborating with the team on this code collaboration platform.'],
    ];
    
    $successCount = 0;
    foreach ($testApplications as $app) {
        $result = $projectModel->applyToProject($app['projectId'], $app['userId'], $app['message']);
        if ($result) {
            echo "<p style='color: green;'>✓ Application created for project ID: {$app['projectId']}</p>";
            $successCount++;
        } else {
            echo "<p style='color: orange;'>⚠ Application may already exist for project {$app['projectId']}</p>";
        }
    }
    
    echo "<p><strong>Total successful applications: {$successCount}</strong></p>";
    
    // Test 3: Verify applications were created
    echo "<h3>Step 3: Verifying Applications in Database</h3>";
    $database->query("SELECT COUNT(*) as count FROM project_applications");
    $count = $database->single();
    echo "<p>Total applications in database: <strong>{$count->count}</strong></p>";
    
    if ($count->count > 0) {
        $database->query("SELECT pa.id, pa.project_id, p.name, pa.user_id, u.username, pa.status, pa.applied_at FROM project_applications pa JOIN projects p ON pa.project_id = p.id JOIN users u ON pa.user_id = u.id ORDER BY pa.applied_at DESC");
        $apps = $database->resultSet();
        
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Project</th><th>User</th><th>Status</th><th>Applied At</th></tr>";
        foreach ($apps as $app) {
            echo "<tr><td>{$app->id}</td><td>{$app->name}</td><td>{$app->username}</td><td>{$app->status}</td><td>{$app->applied_at}</td></tr>";
        }
        echo "</table>";
    }
    
    // Test 4: Test retrieval for organization
    echo "<h3>Step 4: Testing Organization Retrieval (for org_id 37)</h3>";
    $database->query("SELECT id, name, organization_id FROM projects WHERE organization_id = 37");
    $orgProjects = $database->resultSet();
    echo "<p>Projects owned by org 37: " . count($orgProjects) . "</p>";
    
    // Get applications for organization
    $database->query("
        SELECT 
            pa.id, 
            pa.project_id, 
            p.name as project_name,
            u.username, 
            pa.status, 
            pa.applied_at
        FROM project_applications pa
        JOIN projects p ON pa.project_id = p.id
        JOIN users u ON pa.user_id = u.id
        WHERE p.organization_id = 37
        ORDER BY pa.applied_at DESC
    ");
    $orgApps = $database->resultSet();
    
    echo "<p>Applications for org 37: " . count($orgApps) . "</p>";
    if (count($orgApps) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Project</th><th>User</th><th>Status</th><th>Applied At</th></tr>";
        foreach ($orgApps as $app) {
            echo "<tr><td>{$app->id}</td><td>{$app->project_name}</td><td>{$app->username}</td><td>{$app->status}</td><td>{$app->applied_at}</td></tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Login as organization (ID 37) to view applications at /organization/applications</li>";
    echo "<li>The dashboard should now show the applications instead of 0/0/0/0</li>";
    echo "<li>You can test accepting/rejecting applications from the dashboard</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
