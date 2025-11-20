<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../app/models/Project.php';

// Check if logged in as individual
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'organization') {
    echo "<p style='color: red;'>❌ Please login as individual user first</p>";
    exit;
}

$db = new Database();
$projectModel = new Project();

echo "<h2>Manual Test: Submit Application</h2>";

// Get list of all projects
$db->query("SELECT id, name, organization_id FROM projects LIMIT 10");
$projects = $db->resultSet();

echo "<h3>Step 1: Select a Project</h3>";
echo "<form method='POST'>";
echo "<select name='project_id' required>";
echo "<option value=''>-- Select Project --</option>";
foreach ($projects as $p) {
    echo "<option value='{$p->id}'>{$p->name} (Org: {$p->organization_id})</option>";
}
echo "</select>";

echo "<h3>Step 2: Fill Application Details</h3>";
echo "<textarea name='experience' placeholder='Experience...' rows='3' required></textarea><br><br>";
echo "<textarea name='skills' placeholder='Skills...' rows='3' required></textarea><br><br>";
echo "<textarea name='contribution' placeholder='Contribution...' rows='3' required></textarea><br><br>";
echo "<input type='text' name='commitment' value='10-20' placeholder='Commitment' required /><br><br>";
echo "<input type='text' name='duration' value='3-6' placeholder='Duration' required /><br><br>";
echo "<textarea name='motivation' placeholder='Motivation...' rows='3' required></textarea><br><br>";
echo "<input type='url' name='portfolio' placeholder='Portfolio URL (optional)' /><br><br>";

echo "<button type='submit'>Submit Application</button>";
echo "</form>";

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = $_POST['project_id'] ?? null;
    $userId = $_SESSION['user_id'];
    $experience = $_POST['experience'] ?? '';
    $skills = $_POST['skills'] ?? '';
    $contribution = $_POST['contribution'] ?? '';
    $commitment = $_POST['commitment'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $motivation = $_POST['motivation'] ?? '';
    $portfolio = $_POST['portfolio'] ?? null;

    echo "<h3>Submitting...</h3>";
    echo "<p>Project ID: {$projectId}</p>";
    echo "<p>User ID: {$userId}</p>";

    try {
        $result = $projectModel->applyToProjectAdvanced(
            $projectId, 
            $userId, 
            $experience, 
            $skills, 
            $contribution, 
            $commitment, 
            $duration, 
            $motivation, 
            $portfolio
        );

        if ($result) {
            echo "<p style='color: green;'><strong>✅ Application submitted successfully!</strong></p>";
            
            // Verify it was inserted
            $db->query("SELECT * FROM project_applications WHERE project_id = :pid AND user_id = :uid ORDER BY applied_at DESC LIMIT 1");
            $db->bind(':pid', $projectId);
            $db->bind(':uid', $userId);
            $app = $db->single();
            
            if ($app) {
                echo "<h4>Application Details:</h4>";
                echo "<pre>";
                print_r($app);
                echo "</pre>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Application not submitted - you may have already applied to this project</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>❌ Error: " . $e->getMessage() . "</strong></p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}

?>
