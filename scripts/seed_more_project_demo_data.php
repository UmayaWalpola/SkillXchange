<?php

require __DIR__ . '/../app/config/config.php';
require __DIR__ . '/../core/Database.php';

$db = new Database();
$pdo = $db->connect();

const USER_ABCD = 36;
const USER_NADISHA = 57;
const USER_UMAYA = 52;
const USER_AVA = 53;
const USER_BEN = 54;
const USER_DILAN = 56;
const USER_KASUN = 58;
const USER_THARUSHI = 59;
const USER_CHATHURA = 60;

const ORG_UCSC = 51;
const ORG_MIT = 64;
const ORG_HARVARD = 65;
const ORG_STANFORD = 66;
const ORG_SLIIT = 67;
const ORG_NSBM = 68;

function ensureUser(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    if (!$stmt->fetchColumn()) {
        throw new RuntimeException("Required user {$userId} does not exist.");
    }
}

function upsertSkill(PDO $pdo, int $userId, string $skill, string $type, string $level): void
{
    $select = $pdo->prepare("
        SELECT id FROM user_skills
        WHERE user_id = :user_id AND skill_name = :skill_name AND skill_type = :skill_type
        LIMIT 1
    ");
    $select->execute([
        ':user_id' => $userId,
        ':skill_name' => $skill,
        ':skill_type' => $type,
    ]);

    $existingId = $select->fetchColumn();
    if ($existingId) {
        $update = $pdo->prepare('UPDATE user_skills SET proficiency_level = :level WHERE id = :id');
        $update->execute([':level' => $level, ':id' => $existingId]);
        return;
    }

    $insert = $pdo->prepare("
        INSERT INTO user_skills (user_id, skill_name, skill_type, proficiency_level, created_at)
        VALUES (:user_id, :skill_name, :skill_type, :level, NOW())
    ");
    $insert->execute([
        ':user_id' => $userId,
        ':skill_name' => $skill,
        ':skill_type' => $type,
        ':level' => $level,
    ]);
}

function upsertProject(PDO $pdo, array $project): int
{
    $select = $pdo->prepare('SELECT id FROM projects WHERE organization_id = :org_id AND name = :name LIMIT 1');
    $select->execute([
        ':org_id' => $project['organization_id'],
        ':name' => $project['name'],
    ]);

    $existingId = $select->fetchColumn();
    if ($existingId) {
        $update = $pdo->prepare("
            UPDATE projects
            SET description = :description,
                category = :category,
                status = :status,
                required_skills = :required_skills,
                max_members = :max_members,
                start_date = :start_date,
                end_date = :end_date,
                updated_at = NOW()
            WHERE id = :id
        ");
        $update->execute([
            ':description' => $project['description'],
            ':category' => $project['category'],
            ':status' => $project['status'],
            ':required_skills' => $project['required_skills'],
            ':max_members' => $project['max_members'],
            ':start_date' => $project['start_date'],
            ':end_date' => $project['end_date'],
            ':id' => $existingId,
        ]);
        return (int) $existingId;
    }

    $insert = $pdo->prepare("
        INSERT INTO projects
            (organization_id, name, description, category, status, required_skills, max_members, current_members, start_date, end_date, created_at, updated_at)
        VALUES
            (:organization_id, :name, :description, :category, :status, :required_skills, :max_members, 0, :start_date, :end_date, :created_at, NOW())
    ");
    $insert->execute([
        ':organization_id' => $project['organization_id'],
        ':name' => $project['name'],
        ':description' => $project['description'],
        ':category' => $project['category'],
        ':status' => $project['status'],
        ':required_skills' => $project['required_skills'],
        ':max_members' => $project['max_members'],
        ':start_date' => $project['start_date'],
        ':end_date' => $project['end_date'],
        ':created_at' => $project['created_at'],
    ]);

    return (int) $pdo->lastInsertId();
}

function upsertApplication(PDO $pdo, int $projectId, array $app): void
{
    $select = $pdo->prepare('SELECT id FROM project_applications WHERE project_id = :project_id AND user_id = :user_id LIMIT 1');
    $select->execute([
        ':project_id' => $projectId,
        ':user_id' => $app['user_id'],
    ]);

    $payload = [
        ':project_id' => $projectId,
        ':user_id' => $app['user_id'],
        ':message' => $app['message'],
        ':experience' => $app['experience'],
        ':skills' => $app['skills'],
        ':contribution' => $app['contribution'],
        ':commitment' => $app['commitment'],
        ':duration' => $app['duration'],
        ':motivation' => $app['motivation'],
        ':portfolio' => $app['portfolio'],
        ':status' => $app['status'],
        ':applied_at' => $app['applied_at'],
        ':reviewed_at' => $app['reviewed_at'],
        ':relevant_experience' => $app['relevant_experience'],
        ':matching_skills' => $app['matching_skills'],
        ':available_time' => $app['available_time'],
        ':expected_duration' => $app['expected_duration'],
    ];

    $existingId = $select->fetchColumn();
    if ($existingId) {
        $payload[':id'] = $existingId;
        $update = $pdo->prepare("
            UPDATE project_applications
            SET message = :message,
                experience = :experience,
                skills = :skills,
                contribution = :contribution,
                commitment = :commitment,
                duration = :duration,
                motivation = :motivation,
                portfolio = :portfolio,
                status = :status,
                applied_at = :applied_at,
                reviewed_at = :reviewed_at,
                relevant_experience = :relevant_experience,
                matching_skills = :matching_skills,
                available_time = :available_time,
                expected_duration = :expected_duration
            WHERE id = :id
        ");
        $update->execute($payload);
        return;
    }

    $insert = $pdo->prepare("
        INSERT INTO project_applications
            (project_id, user_id, message, experience, skills, contribution, commitment, duration, motivation, portfolio, status, applied_at, reviewed_at, relevant_experience, matching_skills, available_time, expected_duration)
        VALUES
            (:project_id, :user_id, :message, :experience, :skills, :contribution, :commitment, :duration, :motivation, :portfolio, :status, :applied_at, :reviewed_at, :relevant_experience, :matching_skills, :available_time, :expected_duration)
    ");
    $insert->execute($payload);
}

function upsertMember(PDO $pdo, int $projectId, int $userId, string $role, string $joinedAt): void
{
    $select = $pdo->prepare('SELECT id FROM project_members WHERE project_id = :project_id AND user_id = :user_id LIMIT 1');
    $select->execute([':project_id' => $projectId, ':user_id' => $userId]);
    $existingId = $select->fetchColumn();

    if ($existingId) {
        $update = $pdo->prepare("UPDATE project_members SET role = :role, joined_at = :joined_at, status = 'active' WHERE id = :id");
        $update->execute([':role' => $role, ':joined_at' => $joinedAt, ':id' => $existingId]);
        return;
    }

    $insert = $pdo->prepare("
        INSERT INTO project_members (project_id, user_id, role, joined_at, status)
        VALUES (:project_id, :user_id, :role, :joined_at, 'active')
    ");
    $insert->execute([
        ':project_id' => $projectId,
        ':user_id' => $userId,
        ':role' => $role,
        ':joined_at' => $joinedAt,
    ]);
}

function upsertTask(PDO $pdo, int $projectId, array $task): void
{
    $select = $pdo->prepare('SELECT id FROM project_tasks WHERE project_id = :project_id AND title = :title LIMIT 1');
    $select->execute([':project_id' => $projectId, ':title' => $task['title']]);
    $existingId = $select->fetchColumn();

    $payload = [
        ':project_id' => $projectId,
        ':title' => $task['title'],
        ':description' => $task['description'],
        ':status' => $task['status'],
        ':priority' => $task['priority'],
        ':deadline' => $task['deadline'],
        ':buckx_allocated' => $task['buckx_allocated'],
        ':buckx_distributed' => $task['buckx_distributed'],
        ':buckx_distributed_at' => $task['buckx_distributed_at'],
        ':assigned_to' => $task['assigned_to'],
    ];

    if ($existingId) {
        $payload[':id'] = $existingId;
        $update = $pdo->prepare("
            UPDATE project_tasks
            SET description = :description,
                status = :status,
                priority = :priority,
                deadline = :deadline,
                buckx_allocated = :buckx_allocated,
                buckx_distributed = :buckx_distributed,
                buckx_distributed_at = :buckx_distributed_at,
                assigned_to = :assigned_to,
                updated_at = NOW()
            WHERE id = :id
        ");
        $update->execute($payload);
        return;
    }

    $insert = $pdo->prepare("
        INSERT INTO project_tasks
            (project_id, title, description, status, priority, deadline, buckx_allocated, buckx_distributed, buckx_distributed_at, assigned_to, created_at, updated_at)
        VALUES
            (:project_id, :title, :description, :status, :priority, :deadline, :buckx_allocated, :buckx_distributed, :buckx_distributed_at, :assigned_to, NOW(), NOW())
    ");
    $insert->execute($payload);
}

function upsertChat(PDO $pdo, int $projectId, int $senderId, string $message, string $createdAt): void
{
    $select = $pdo->prepare("
        SELECT id FROM project_chat_messages
        WHERE project_id = :project_id AND sender_id = :sender_id AND message = :message
        LIMIT 1
    ");
    $select->execute([':project_id' => $projectId, ':sender_id' => $senderId, ':message' => $message]);
    if ($select->fetchColumn()) {
        return;
    }

    $insert = $pdo->prepare("
        INSERT INTO project_chat_messages (project_id, sender_id, message, created_at)
        VALUES (:project_id, :sender_id, :message, :created_at)
    ");
    $insert->execute([
        ':project_id' => $projectId,
        ':sender_id' => $senderId,
        ':message' => $message,
        ':created_at' => $createdAt,
    ]);
}

function syncMemberCount(PDO $pdo, int $projectId): void
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM project_members WHERE project_id = :project_id AND status = 'active'");
    $stmt->execute([':project_id' => $projectId]);
    $count = (int) $stmt->fetchColumn();

    $update = $pdo->prepare('UPDATE projects SET current_members = :count WHERE id = :project_id');
    $update->execute([':count' => $count, ':project_id' => $projectId]);
}

function appData(int $userId, string $status, string $skills, string $message, string $appliedAt, ?string $reviewedAt = null): array
{
    return [
        'user_id' => $userId,
        'message' => $message,
        'experience' => 'Hands-on coursework and team project experience related to ' . $skills . '.',
        'skills' => $skills,
        'contribution' => 'I can take ownership of assigned deliverables, communicate blockers early, and help with testing/documentation.',
        'commitment' => '10-20 hours per week',
        'duration' => '1-3 months',
        'motivation' => 'This project matches my current portfolio goals and gives me a chance to contribute to a realistic team workflow.',
        'portfolio' => 'https://github.com/demo-user-' . $userId,
        'status' => $status,
        'applied_at' => $appliedAt,
        'reviewed_at' => $reviewedAt,
        'relevant_experience' => 'Relevant class projects, peer collaboration, and practical implementation work.',
        'matching_skills' => $skills,
        'available_time' => '10-20',
        'expected_duration' => '1-3 months',
    ];
}

try {
    foreach ([USER_ABCD, USER_NADISHA, USER_UMAYA, USER_AVA, USER_BEN, USER_DILAN, USER_KASUN, USER_THARUSHI, USER_CHATHURA, ORG_MIT, ORG_HARVARD, ORG_STANFORD, ORG_SLIIT, ORG_NSBM, ORG_UCSC] as $userId) {
        ensureUser($pdo, $userId);
    }

    $pdo->beginTransaction();

    // Keep the demo matches healthy for the project suggestion engine.
    upsertSkill($pdo, USER_ABCD, 'github', 'teach', 'advanced');
    upsertSkill($pdo, USER_ABCD, 'web-development', 'teach', 'intermediate');
    upsertSkill($pdo, USER_NADISHA, 'cloud', 'teach', 'intermediate');
    upsertSkill($pdo, USER_NADISHA, 'frontend', 'teach', 'advanced');

    $projects = [
        [
            'key' => 'open_source',
            'organization_id' => ORG_NSBM,
            'name' => 'Open Source Issue Triage Dataset',
            'description' => 'Build a GitHub-powered dashboard that categorizes issue reports from a CSV export, groups beginner-friendly tasks, and tracks contributor response times. Demo dataset: 250 anonymized GitHub issues with labels, assignees, and resolution notes.',
            'category' => 'web',
            'status' => 'active',
            'required_skills' => 'github and git, web development',
            'max_members' => 6,
            'start_date' => '2026-04-24',
            'end_date' => '2026-06-14',
            'created_at' => '2026-04-18 09:15:00',
        ],
        [
            'key' => 'energy',
            'organization_id' => ORG_MIT,
            'name' => 'Campus Energy Analytics Dashboard',
            'description' => 'Create a frontend dashboard backed by cloud-hosted meter readings to visualize building energy usage, peak-hour spikes, and sustainability goals. Demo dataset: hourly electricity CSV readings for 12 campus buildings plus weather JSON snapshots.',
            'category' => 'data',
            'status' => 'active',
            'required_skills' => 'cloud computing, frontend frameworks',
            'max_members' => 5,
            'start_date' => '2026-04-28',
            'end_date' => '2026-07-05',
            'created_at' => '2026-04-18 10:20:00',
        ],
        [
            'key' => 'research_portal',
            'organization_id' => ORG_STANFORD,
            'name' => 'Research Dataset Portal',
            'description' => 'Design a searchable portal for public research datasets with upload metadata, contributor history, and quality tags. Demo dataset: 40 sample dataset records across CSV, JSON, and image annotation formats.',
            'category' => 'web',
            'status' => 'in-progress',
            'required_skills' => 'github and git, web development, frontend frameworks',
            'max_members' => 7,
            'start_date' => '2026-04-20',
            'end_date' => '2026-07-20',
            'created_at' => '2026-04-18 11:35:00',
        ],
        [
            'key' => 'library_sync',
            'organization_id' => ORG_SLIIT,
            'name' => 'Smart Library Cloud Sync',
            'description' => 'Prototype a lightweight app that syncs library borrowing events to a cloud service and displays overdue-risk alerts. Demo dataset: 600 synthetic borrow/return events, book categories, and device sync logs.',
            'category' => 'mobile',
            'status' => 'active',
            'required_skills' => 'cloud computing, frontend frameworks',
            'max_members' => 5,
            'start_date' => '2026-05-01',
            'end_date' => '2026-06-30',
            'created_at' => '2026-04-18 12:10:00',
        ],
        [
            'key' => 'alumni',
            'organization_id' => ORG_HARVARD,
            'name' => 'Alumni Mentorship Web Platform',
            'description' => 'Create a web platform that matches students with alumni mentors using profile tags, availability windows, and feedback records. Demo dataset: anonymized alumni mentor profiles, student interests, and prior session ratings.',
            'category' => 'web',
            'status' => 'active',
            'required_skills' => 'web development, github and git',
            'max_members' => 8,
            'start_date' => '2026-04-26',
            'end_date' => '2026-07-12',
            'created_at' => '2026-04-18 13:00:00',
        ],
    ];

    $projectIds = [];
    foreach ($projects as $project) {
        $key = $project['key'];
        unset($project['key']);
        $projectIds[$key] = upsertProject($pdo, $project);
    }

    $applications = [
        'open_source' => [
            appData(USER_ABCD, 'pending', 'github, web-development', 'I can help organize the issue dataset and build the dashboard workflow.', '2026-04-21 09:05:00'),
            appData(USER_KASUN, 'accepted', 'github', 'I can own repository setup and issue import scripts.', '2026-04-20 14:10:00', '2026-04-20 16:20:00'),
            appData(USER_AVA, 'rejected', 'frontend, github', 'I can help with frontend QA if there is space.', '2026-04-19 12:30:00', '2026-04-20 08:30:00'),
        ],
        'energy' => [
            appData(USER_NADISHA, 'pending', 'cloud, frontend', 'I can help connect the cloud dataset and build the dashboard cards.', '2026-04-21 09:25:00'),
            appData(USER_AVA, 'accepted', 'cloud, frontend', 'I can support dashboard implementation and cloud documentation.', '2026-04-20 10:45:00', '2026-04-20 15:00:00'),
            appData(USER_CHATHURA, 'rejected', 'marketing', 'I can help with presentation material for the project.', '2026-04-19 16:00:00', '2026-04-20 10:00:00'),
        ],
        'research_portal' => [
            appData(USER_ABCD, 'accepted', 'github, web-development', 'I can manage repository flow and implement dataset browsing pages.', '2026-04-19 09:10:00', '2026-04-19 18:15:00'),
            appData(USER_NADISHA, 'accepted', 'frontend, cloud', 'I can work on the frontend filters and cloud-hosted metadata demo.', '2026-04-19 09:22:00', '2026-04-19 18:18:00'),
            appData(USER_BEN, 'pending', 'github', 'I can help maintain branches and review pull requests.', '2026-04-21 10:10:00'),
        ],
        'library_sync' => [
            appData(USER_NADISHA, 'accepted', 'cloud, frontend', 'I can own the cloud sync checklist and dashboard screens.', '2026-04-20 11:15:00', '2026-04-20 16:00:00'),
            appData(USER_DILAN, 'pending', 'documentation, testing', 'I can help with test cases and project notes.', '2026-04-21 10:40:00'),
        ],
        'alumni' => [
            appData(USER_ABCD, 'accepted', 'web-development, github', 'I can build the student-facing pages and help with GitHub workflow.', '2026-04-20 09:00:00', '2026-04-20 12:00:00'),
            appData(USER_THARUSHI, 'accepted', 'web-development', 'I can help with profile and search pages.', '2026-04-20 09:45:00', '2026-04-20 12:05:00'),
            appData(USER_NADISHA, 'rejected', 'frontend, cloud', 'I can contribute frontend polish if needed.', '2026-04-19 15:15:00', '2026-04-20 11:00:00'),
        ],
    ];

    foreach ($applications as $key => $apps) {
        foreach ($apps as $app) {
            upsertApplication($pdo, $projectIds[$key], $app);
        }
    }

    $members = [
        'open_source' => [[USER_KASUN, 'Repository Lead', '2026-04-20 16:20:00']],
        'energy' => [[USER_AVA, 'Dashboard Contributor', '2026-04-20 15:00:00']],
        'research_portal' => [
            [USER_ABCD, 'Web Contributor', '2026-04-19 18:15:00'],
            [USER_NADISHA, 'Frontend + Cloud Contributor', '2026-04-19 18:18:00'],
        ],
        'library_sync' => [[USER_NADISHA, 'Cloud Sync Contributor', '2026-04-20 16:00:00']],
        'alumni' => [
            [USER_ABCD, 'Frontend Contributor', '2026-04-20 12:00:00'],
            [USER_THARUSHI, 'Web Contributor', '2026-04-20 12:05:00'],
        ],
    ];

    foreach ($members as $key => $projectMembers) {
        foreach ($projectMembers as [$userId, $role, $joinedAt]) {
            upsertMember($pdo, $projectIds[$key], $userId, $role, $joinedAt);
        }
    }

    $tasks = [
        'open_source' => [
            ['title' => 'Import issue CSV sample', 'description' => 'Normalize labels, assignees, and timestamps from the issue export.', 'status' => 'in-progress', 'priority' => 'high', 'deadline' => '2026-04-29', 'buckx_allocated' => 60.00, 'buckx_distributed' => 0, 'buckx_distributed_at' => null, 'assigned_to' => USER_KASUN],
            ['title' => 'Design triage card layout', 'description' => 'Prepare the card design for beginner-friendly issues and urgent issues.', 'status' => 'todo', 'priority' => 'medium', 'deadline' => '2026-05-03', 'buckx_allocated' => 45.00, 'buckx_distributed' => 0, 'buckx_distributed_at' => null, 'assigned_to' => null],
        ],
        'energy' => [
            ['title' => 'Create meter summary cards', 'description' => 'Build dashboard cards for peak load, average use, and monthly comparison.', 'status' => 'in-progress', 'priority' => 'high', 'deadline' => '2026-05-02', 'buckx_allocated' => 75.00, 'buckx_distributed' => 0, 'buckx_distributed_at' => null, 'assigned_to' => USER_AVA],
            ['title' => 'Document cloud dataset structure', 'description' => 'Write notes explaining meter CSV fields and weather JSON examples.', 'status' => 'todo', 'priority' => 'medium', 'deadline' => '2026-05-05', 'buckx_allocated' => 35.00, 'buckx_distributed' => 0, 'buckx_distributed_at' => null, 'assigned_to' => null],
        ],
        'research_portal' => [
            ['title' => 'Dataset search filters', 'description' => 'Implement filters for format, domain, license, and contributor.', 'status' => 'in-progress', 'priority' => 'high', 'deadline' => '2026-04-27', 'buckx_allocated' => 90.00, 'buckx_distributed' => 0, 'buckx_distributed_at' => null, 'assigned_to' => USER_NADISHA],
            ['title' => 'Repository workflow setup', 'description' => 'Set branch naming, PR checklist, and sample dataset seed files.', 'status' => 'done', 'priority' => 'medium', 'deadline' => '2026-04-20', 'buckx_allocated' => 80.00, 'buckx_distributed' => 1, 'buckx_distributed_at' => '2026-04-20 19:20:00', 'assigned_to' => USER_ABCD],
        ],
        'library_sync' => [
            ['title' => 'Sync log timeline', 'description' => 'Show device sync events and failed uploads in chronological order.', 'status' => 'in-progress', 'priority' => 'high', 'deadline' => '2026-05-04', 'buckx_allocated' => 70.00, 'buckx_distributed' => 0, 'buckx_distributed_at' => null, 'assigned_to' => USER_NADISHA],
            ['title' => 'Borrowing event fixture', 'description' => 'Prepare sample borrow and return events for demo testing.', 'status' => 'todo', 'priority' => 'low', 'deadline' => '2026-05-07', 'buckx_allocated' => 30.00, 'buckx_distributed' => 0, 'buckx_distributed_at' => null, 'assigned_to' => null],
        ],
        'alumni' => [
            ['title' => 'Mentor profile grid', 'description' => 'Build the mentor card grid and tag filters for student browsing.', 'status' => 'done', 'priority' => 'high', 'deadline' => '2026-04-20', 'buckx_allocated' => 85.00, 'buckx_distributed' => 1, 'buckx_distributed_at' => '2026-04-20 17:10:00', 'assigned_to' => USER_ABCD],
            ['title' => 'Session feedback summary', 'description' => 'Add average ratings and latest feedback snippets to mentor profiles.', 'status' => 'in-progress', 'priority' => 'medium', 'deadline' => '2026-04-30', 'buckx_allocated' => 50.00, 'buckx_distributed' => 0, 'buckx_distributed_at' => null, 'assigned_to' => USER_THARUSHI],
        ],
    ];

    foreach ($tasks as $key => $projectTasks) {
        foreach ($projectTasks as $task) {
            upsertTask($pdo, $projectIds[$key], $task);
        }
    }

    $chatMessages = [
        'research_portal' => [
            [ORG_STANFORD, 'Welcome team. For the first milestone, focus on searchable dataset metadata and clean filter behavior.', '2026-04-19 18:30:00'],
            [USER_ABCD, 'I will set up the repository workflow and sample dataset seed files first.', '2026-04-19 18:33:00'],
            [USER_NADISHA, 'I can take the filters and cloud metadata demo after that.', '2026-04-19 18:36:00'],
        ],
        'library_sync' => [
            [ORG_SLIIT, 'Nadeesha, please start with the sync log timeline so we can demo failed uploads clearly.', '2026-04-20 16:15:00'],
            [USER_NADISHA, 'Noted. I will use the borrowing-event fixture and keep the cloud notes updated.', '2026-04-20 16:18:00'],
        ],
        'alumni' => [
            [ORG_HARVARD, 'The mentor profile grid is the first demo milestone. Please keep the dataset tags visible.', '2026-04-20 12:15:00'],
            [USER_ABCD, 'I finished the mentor card grid draft and added the first filter pass.', '2026-04-20 16:55:00'],
            [USER_THARUSHI, 'I will continue with the feedback summary block.', '2026-04-20 17:00:00'],
        ],
        'open_source' => [
            [ORG_NSBM, 'Kasun, start with the issue CSV import. We will review pending applicants after the first import demo.', '2026-04-20 16:30:00'],
            [USER_KASUN, 'CSV import is underway. Labels and assignees are mostly normalized.', '2026-04-20 17:05:00'],
        ],
        'energy' => [
            [ORG_MIT, 'Ava, please prioritize the meter summary cards and keep the dataset assumptions documented.', '2026-04-20 15:10:00'],
            [USER_AVA, 'I have the first dashboard cards ready with peak and average usage values.', '2026-04-20 17:30:00'],
        ],
    ];

    foreach ($chatMessages as $key => $messages) {
        foreach ($messages as [$senderId, $message, $createdAt]) {
            upsertChat($pdo, $projectIds[$key], $senderId, $message, $createdAt);
        }
    }

    foreach ($projectIds as $projectId) {
        syncMemberCount($pdo, $projectId);
    }

    $pdo->commit();

    echo "More project demo data seeded.\n";
    foreach ($projectIds as $key => $projectId) {
        echo "{$key}: {$projectId}\n";
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Project demo seed failed: " . $e->getMessage() . "\n");
    exit(1);
}
