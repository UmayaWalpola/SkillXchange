<?php
session_start();
require_once "../app/config/config.php";
require_once "../core/Database.php";
require_once "../app/models/Project.php";

// Simulate org session (use org id that exists in your DB)
$_SESSION['user_id'] = 37;
$_SESSION['role'] = 'organization';

$db = new Database();

// Create temp project
$db->query("INSERT INTO projects (organization_id, name, description, category, status, required_skills, max_members, created_at) VALUES (37, 'Tmp RemoveReport', 'tmp', 'web', 'active', 'php', 5, NOW())");
$db->execute();
$projectId = $db->connect()->lastInsertId();

echo "Created project id: $projectId\n";

// Create or pick a user to be member/reported. Prefer existing temp user if present.
$db->query("SELECT id FROM users WHERE username = 'tmp_user_for_test' LIMIT 1");
$existing = $db->single();
if ($existing && !empty($existing->id)) {
	$userId = $existing->id;
	echo "Using existing user id: $userId\n";
} else {
	$db->query("INSERT INTO users (username, email, password, role, created_at) VALUES ('tmp_user_for_test', 'tmp_user_for_test@example.com', '', 'user', NOW())");
	$db->execute();
	$userId = $db->connect()->lastInsertId();
	echo "Created user id: $userId\n";
}

// Insert project_members record
$db->query("INSERT INTO project_members (project_id, user_id, role, joined_at, status) VALUES (:pid, :uid, 'Member', NOW(), 'active')");
$db->bind(':pid', $projectId);
$db->bind(':uid', $userId);
$db->execute();
$memberId = $db->connect()->lastInsertId();

echo "Inserted member id: $memberId\n";

// Now call controller methods
require_once "../core/Controller.php";
require_once "../app/controllers/OrganizationController.php";
$ctrl = new OrganizationController();

// Simulate removeMember POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['project_id' => $projectId, 'member_id' => $memberId];

echo "Calling removeMember...\n";
ob_start();
$ctrl->removeMember();
$out = ob_get_clean();
echo "Response: $out\n";

// Check member status
$db->query("SELECT status FROM project_members WHERE id = :mid");
$db->bind(':mid', $memberId);
$res = $db->single();
echo "Member status after removal: " . ($res->status ?? 'NULL') . "\n";

// Now test reportUser
$_POST = ['project_id' => $projectId, 'reported_user_id' => $userId, 'reason' => 'Test misbehavior', 'details' => 'Details here'];

echo "Calling reportUser...\n";
ob_start();
$db->query("CREATE TABLE IF NOT EXISTS `user_reports` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`project_id` int(11) NOT NULL,
	`reported_user_id` int(11) NOT NULL,
	`reporter_org_id` int(11) NOT NULL,
	`reason` varchar(255) NOT NULL,
	`details` text DEFAULT NULL,
	`status` enum('pending','reviewed','dismissed') NOT NULL DEFAULT 'pending',
	`reported_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)");
$db->execute();

$ctrl->reportUser();
$out = ob_get_clean();
echo "Response: $out\n";

// Verify report created
$db->query("SELECT id, reason, status FROM user_reports WHERE reported_user_id = :uid ORDER BY reported_at DESC LIMIT 1");
$db->bind(':uid', $userId);
$rep = $db->single();
if ($rep) echo "Report saved: id={$rep->id}, reason={$rep->reason}, status={$rep->status}\n"; else echo "No report found\n";

// Cleanup: optional (not deleting user/project to keep logs), but you can delete manually.

?>