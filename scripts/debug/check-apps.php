<?php
session_start();

// Include database configuration
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';

try {
    // Create database connection
    $db = new Database();
    
    echo "<h2>Database Check</h2>";
    
    // Check if project_applications table exists
    $db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = 'project_applications'");
    $result = $db->resultSet();
    
    if (count($result) > 0) {
        echo "<p style='color: green;'>✓ project_applications table EXISTS</p>";
        
        // Get table structure
        $db->query("DESCRIBE project_applications");
        $columns = $db->resultSet();
        
        echo "<h3>Table Structure:</h3>";
        echo "<ul>";
        foreach ($columns as $col) {
            echo "<li>{$col->Field}: {$col->Type}</li>";
        }
        echo "</ul>";
        
        // Count applications
        $db->query("SELECT COUNT(*) as count FROM project_applications");
        $count = $db->single();
        
        echo "<h3>Data Count:</h3>";
        echo "<p>Total applications: <strong>{$count->count}</strong></p>";
        
        if ($count->count > 0) {
            echo "<h3>Applications Data:</h3>";
            $db->query("SELECT * FROM project_applications ORDER BY applied_at DESC");
            $apps = $db->resultSet();
            
            echo "<table border='1' cellpadding='10'>";
            echo "<tr><th>ID</th><th>Project ID</th><th>User ID</th><th>Status</th><th>Applied At</th><th>Message</th></tr>";
            foreach ($apps as $app) {
                $msg = substr($app->message, 0, 50) . '...';
                echo "<tr><td>{$app->id}</td><td>{$app->project_id}</td><td>{$app->user_id}</td><td>{$app->status}</td><td>{$app->applied_at}</td><td>{$msg}</td></tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>✗ project_applications table DOES NOT EXIST</p>";
        echo "<p>You need to create this table first.</p>";
        echo "<h3>CREATE TABLE Statement:</h3>";
        echo "<pre>";
        echo htmlspecialchars("CREATE TABLE project_applications (
  id INT PRIMARY KEY AUTO_INCREMENT,
  project_id INT NOT NULL,
  user_id INT NOT NULL,
  message TEXT,
  status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_application (project_id, user_id)
);");
        echo "</pre>";
    }
    
    // Check projects table
    echo "<h3>Projects Table Check:</h3>";
    $db->query("SELECT COUNT(*) as count FROM projects");
    $count = $db->single();
    echo "<p>Total projects: <strong>{$count->count}</strong></p>";
    
    if ($count->count > 0) {
        $db->query("SELECT id, name, organization_id FROM projects LIMIT 5");
        $projects = $db->resultSet();
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Name</th><th>Organization ID</th></tr>";
        foreach ($projects as $proj) {
            echo "<tr><td>{$proj->id}</td><td>{$proj->name}</td><td>{$proj->organization_id}</td></tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
