<?php
session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();

echo "<h2>Creating Test Applications</h2>";

// Insert test applications for projects owned by organization 37 (Pretty Software)
$testData = [
    ['project_id' => 7, 'user_id' => 41, 'message' => 'I have extensive experience with Flutter and NodeJS. I am very interested in contributing to the ZCode project!'],
    ['project_id' => 8, 'user_id' => 41, 'message' => 'I am proficient in HTML5, CSS3, JavaScript, PHP and MySQL. I would love to work on CodeCollab Hub!'],
    ['project_id' => 7, 'user_id' => 5, 'message' => 'I am enthusiastic about mobile app development and want to learn Flutter while contributing to ZCode.'],
    ['project_id' => 9, 'user_id' => 20, 'message' => 'With my data science background, I believe I can contribute significantly to SkillMentor project.'],
    ['project_id' => 10, 'user_id' => 30, 'message' => 'I have design skills and UI/UX experience. I am excited about the LearnLab project!'],
];

$successCount = 0;
$errorCount = 0;

foreach ($testData as $app) {
    try {
        $db->query("INSERT INTO project_applications (project_id, user_id, message, status, applied_at) 
                    VALUES (:project_id, :user_id, :message, 'pending', NOW())");
        $db->bind(':project_id', $app['project_id']);
        $db->bind(':user_id', $app['user_id']);
        $db->bind(':message', $app['message']);
        
        if ($db->execute()) {
            echo "<p style='color: green;'>✓ Created application: User {$app['user_id']} → Project {$app['project_id']}</p>";
            $successCount++;
        } else {
            echo "<p style='color: orange;'>⚠ Failed to create application</p>";
            $errorCount++;
        }
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        $errorCount++;
    }
}

echo "<hr>";
echo "<p><strong>Summary: {$successCount} created, {$errorCount} failed</strong></p>";

// Verify the data
echo "<h3>Verifying Data:</h3>";
$db->query("SELECT COUNT(*) as count FROM project_applications");
$result = $db->single();
echo "<p>Total applications now: <strong>{$result->count}</strong></p>";

// Show applications by organization
echo "<h3>Applications for Organization 37 (Pretty Software):</h3>";
$db->query("
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
$apps = $db->resultSet();

if (count($apps) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>Project</th><th>Applicant</th><th>Status</th><th>Applied</th></tr>";
    foreach ($apps as $app) {
        echo "<tr><td>{$app->id}</td><td>{$app->project_name}</td><td>{$app->username}</td><td>{$app->status}</td><td>{$app->applied_at}</td></tr>";
    }
    echo "</table>";
    echo "<p style='margin-top: 1rem; color: green;'><strong>✓ Now you can login as organization 37 and view these applications at /organization/applications</strong></p>";
} else {
    echo "<p style='color: red;'>No applications found for this organization.</p>";
}
?>
