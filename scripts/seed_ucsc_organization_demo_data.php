<?php

require __DIR__ . '/../app/config/config.php';
require __DIR__ . '/../core/Database.php';

$db = new Database();
$pdo = $db->connect();

const ORG_UCSC = 51;

$users = [
    'abcd' => 36,
    'umaya' => 52,
    'ava' => 53,
    'ben' => 54,
    'dilan' => 56,
    'nadisha' => 57,
    'kasun' => 58,
    'tharushi' => 59,
    'chathura' => 60,
    'kithsara' => 62,
    'ibrahim' => 63,
];

function ensureUser(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    if (!$stmt->fetchColumn()) {
        throw new RuntimeException("Required demo user {$userId} does not exist.");
    }
}

function upsertProject(PDO $pdo, array $project): int
{
    $select = $pdo->prepare('SELECT id FROM projects WHERE organization_id = :org_id AND name = :name LIMIT 1');
    $select->execute([':org_id' => ORG_UCSC, ':name' => $project['name']]);
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
        ':organization_id' => ORG_UCSC,
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

function appPayload(int $userId, string $status, string $skills, string $message, string $appliedAt, ?string $reviewedAt = null): array
{
    return [
        'user_id' => $userId,
        'message' => $message,
        'experience' => 'Relevant coursework, team collaboration, and hands-on practice with ' . $skills . '.',
        'skills' => $skills,
        'contribution' => 'I can complete assigned tasks, communicate progress clearly, and help with testing or documentation.',
        'commitment' => '10-20 hours per week',
        'duration' => '1-3 months',
        'motivation' => 'This UCSC project is a good fit for my skills and portfolio goals.',
        'portfolio' => 'https://github.com/ucsc-demo-user-' . $userId,
        'status' => $status,
        'applied_at' => $appliedAt,
        'reviewed_at' => $reviewedAt,
        'relevant_experience' => 'Worked on similar academic or self-guided project tasks.',
        'matching_skills' => $skills,
        'available_time' => '10-20',
        'expected_duration' => '1-3 months',
    ];
}

function upsertApplication(PDO $pdo, int $projectId, array $app): void
{
    $select = $pdo->prepare('SELECT id FROM project_applications WHERE project_id = :project_id AND user_id = :user_id LIMIT 1');
    $select->execute([':project_id' => $projectId, ':user_id' => $app['user_id']]);
    $existingId = $select->fetchColumn();

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

function upsertMember(PDO $pdo, int $projectId, int $userId, string $role, string $joinedAt, string $status = 'active'): void
{
    $select = $pdo->prepare('SELECT id FROM project_members WHERE project_id = :project_id AND user_id = :user_id LIMIT 1');
    $select->execute([':project_id' => $projectId, ':user_id' => $userId]);
    $existingId = $select->fetchColumn();

    if ($existingId) {
        $update = $pdo->prepare('UPDATE project_members SET role = :role, joined_at = :joined_at, status = :status WHERE id = :id');
        $update->execute([':role' => $role, ':joined_at' => $joinedAt, ':status' => $status, ':id' => $existingId]);
        return;
    }

    $insert = $pdo->prepare('INSERT INTO project_members (project_id, user_id, role, joined_at, status) VALUES (:project_id, :user_id, :role, :joined_at, :status)');
    $insert->execute([':project_id' => $projectId, ':user_id' => $userId, ':role' => $role, ':joined_at' => $joinedAt, ':status' => $status]);
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
    $select = $pdo->prepare('SELECT id FROM project_chat_messages WHERE project_id = :project_id AND sender_id = :sender_id AND message = :message LIMIT 1');
    $select->execute([':project_id' => $projectId, ':sender_id' => $senderId, ':message' => $message]);
    if ($select->fetchColumn()) {
        return;
    }

    $insert = $pdo->prepare('INSERT INTO project_chat_messages (project_id, sender_id, message, created_at) VALUES (:project_id, :sender_id, :message, :created_at)');
    $insert->execute([':project_id' => $projectId, ':sender_id' => $senderId, ':message' => $message, ':created_at' => $createdAt]);
}

function upsertFeedback(PDO $pdo, int $projectId, int $userId, int $rating, string $comment): void
{
    $select = $pdo->prepare('SELECT id FROM user_feedback WHERE user_id = :user_id AND reviewer_id = :reviewer_id AND project_id = :project_id LIMIT 1');
    $select->execute([':user_id' => $userId, ':reviewer_id' => ORG_UCSC, ':project_id' => $projectId]);
    $existingId = $select->fetchColumn();

    if ($existingId) {
        $update = $pdo->prepare('UPDATE user_feedback SET rating = :rating, comment = :comment, tags = :tags, updated_at = NOW() WHERE id = :id');
        $update->execute([':rating' => $rating, ':comment' => $comment, ':tags' => 'ucsc,project,demo', ':id' => $existingId]);
        return;
    }

    $insert = $pdo->prepare("
        INSERT INTO user_feedback (user_id, reviewer_id, project_id, context_type, context_id, rating, comment, tags, created_at, updated_at, report_count)
        VALUES (:user_id, :reviewer_id, :project_id, 'project', :context_id, :rating, :comment, :tags, NOW(), NOW(), 0)
    ");
    $insert->execute([
        ':user_id' => $userId,
        ':reviewer_id' => ORG_UCSC,
        ':project_id' => $projectId,
        ':context_id' => $projectId,
        ':rating' => $rating,
        ':comment' => $comment,
        ':tags' => 'ucsc,project,demo',
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

try {
    ensureUser($pdo, ORG_UCSC);
    foreach ($users as $userId) {
        ensureUser($pdo, $userId);
    }

    $pdo->beginTransaction();

    $projects = [
        'survey' => upsertProject($pdo, [
            'name' => 'Student Survey Insights Platform',
            'description' => 'Analyze anonymized student survey responses and build a dashboard for satisfaction, course load, transport, and facility feedback. Demo dataset: 1,200 CSV survey responses with department, year, rating, and open-comment fields.',
            'category' => 'data',
            'status' => 'active',
            'required_skills' => 'frontend frameworks, cloud computing',
            'max_members' => 6,
            'start_date' => '2026-04-25',
            'end_date' => '2026-06-25',
            'created_at' => '2026-04-18 08:10:00',
        ]),
        'attendance' => upsertProject($pdo, [
            'name' => 'Attendance Risk Early Warning Tool',
            'description' => 'Build a web tool that flags attendance risk using weekly attendance exports and assessment submission records. Demo dataset: synthetic attendance logs, course modules, and assignment submission timestamps.',
            'category' => 'web',
            'status' => 'in-progress',
            'required_skills' => 'web development, github and git',
            'max_members' => 5,
            'start_date' => '2026-04-12',
            'end_date' => '2026-06-10',
            'created_at' => '2026-04-16 09:30:00',
        ]),
        'alumni' => upsertProject($pdo, [
            'name' => 'UCSC Alumni Career Map',
            'description' => 'Create an interactive map of anonymized alumni career paths, skills, industries, and mentoring availability. Demo dataset: 350 anonymized alumni records with graduation year, role, skills, and region.',
            'category' => 'web',
            'status' => 'completed',
            'required_skills' => 'web development, frontend frameworks',
            'max_members' => 4,
            'start_date' => '2026-02-01',
            'end_date' => '2026-04-10',
            'created_at' => '2026-02-01 10:00:00',
        ]),
        'cancelled' => upsertProject($pdo, [
            'name' => 'Legacy Lab Booking Migration',
            'description' => 'Cancelled demo project for showing archived/cancelled organization-side state. Dataset: old lab booking CSV exports and room availability snapshots.',
            'category' => 'other',
            'status' => 'cancelled',
            'required_skills' => 'github and git, web development',
            'max_members' => 3,
            'start_date' => '2026-03-05',
            'end_date' => '2026-04-01',
            'created_at' => '2026-03-05 11:20:00',
        ]),
    ];

    $applications = [
        'survey' => [
            appPayload($users['nadisha'], 'pending', 'frontend, cloud', 'I can build dashboard cards and help prepare the cloud-hosted survey dataset.', '2026-04-21 09:45:00'),
            appPayload($users['ava'], 'accepted', 'frontend, cloud', 'I can own visual dashboard components and chart testing.', '2026-04-20 10:10:00', '2026-04-20 14:00:00'),
            appPayload($users['ibrahim'], 'pending', 'cloud', 'I can help test the cloud deployment notes.', '2026-04-21 11:15:00'),
            appPayload($users['chathura'], 'rejected', 'marketing', 'I can support presentation and outreach material.', '2026-04-19 15:00:00', '2026-04-20 09:15:00'),
        ],
        'attendance' => [
            appPayload($users['abcd'], 'accepted', 'github, web-development', 'I can build the repository workflow and web pages for the risk tool.', '2026-04-16 12:30:00', '2026-04-16 17:00:00'),
            appPayload($users['ben'], 'accepted', 'github', 'I can support branches, pull requests, and code review.', '2026-04-16 13:00:00', '2026-04-16 17:05:00'),
            appPayload($users['kasun'], 'pending', 'github', 'I can help with import scripts and issue tracking.', '2026-04-21 08:30:00'),
        ],
        'alumni' => [
            appPayload($users['abcd'], 'accepted', 'web-development, github', 'I can implement the profile grid and filtering experience.', '2026-02-02 09:00:00', '2026-02-02 16:00:00'),
            appPayload($users['nadisha'], 'accepted', 'frontend', 'I can support UI polish and responsive charts.', '2026-02-02 09:20:00', '2026-02-02 16:05:00'),
            appPayload($users['tharushi'], 'accepted', 'web-development', 'I can build the alumni profile details page.', '2026-02-02 09:45:00', '2026-02-02 16:10:00'),
        ],
        'cancelled' => [
            appPayload($users['umaya'], 'rejected', 'frontend, marketing', 'I was interested before the migration was paused.', '2026-03-06 14:00:00', '2026-03-10 10:00:00'),
            appPayload($users['dilan'], 'pending', 'testing, documentation', 'I can help document migration risk if the project reopens.', '2026-03-07 09:30:00'),
        ],
    ];

    foreach ($applications as $key => $apps) {
        foreach ($apps as $app) {
            upsertApplication($pdo, $projects[$key], $app);
        }
    }

    $members = [
        'survey' => [
            [$users['ava'], 'Visualization Contributor', '2026-04-20 14:00:00'],
        ],
        'attendance' => [
            [$users['abcd'], 'Web + Git Lead', '2026-04-16 17:00:00'],
            [$users['ben'], 'Repository Reviewer', '2026-04-16 17:05:00'],
        ],
        'alumni' => [
            [$users['abcd'], 'Web Contributor', '2026-02-02 16:00:00'],
            [$users['nadisha'], 'Frontend Contributor', '2026-02-02 16:05:00'],
            [$users['tharushi'], 'Profile Page Contributor', '2026-02-02 16:10:00'],
        ],
    ];

    foreach ($members as $key => $rows) {
        foreach ($rows as [$userId, $role, $joinedAt]) {
            upsertMember($pdo, $projects[$key], $userId, $role, $joinedAt);
        }
    }

    $tasks = [
        'survey' => [
            ['title' => 'Survey chart cards', 'description' => 'Create cards for satisfaction, workload, transport, and facility ratings.', 'status' => 'in-progress', 'priority' => 'high', 'deadline' => '2026-04-30', 'buckx_allocated' => 70.00, 'buckx_distributed' => 0, 'buckx_distributed_at' => null, 'assigned_to' => $users['ava']],
            ['title' => 'Open comment clustering notes', 'description' => 'Summarize sample free-text comments into simple feedback themes.', 'status' => 'todo', 'priority' => 'medium', 'deadline' => '2026-05-06', 'buckx_allocated' => 40.00, 'buckx_distributed' => 0, 'buckx_distributed_at' => null, 'assigned_to' => null],
        ],
        'attendance' => [
            ['title' => 'Attendance CSV importer', 'description' => 'Normalize weekly attendance exports and map them to course modules.', 'status' => 'done', 'priority' => 'high', 'deadline' => '2026-04-18', 'buckx_allocated' => 90.00, 'buckx_distributed' => 1, 'buckx_distributed_at' => '2026-04-18 18:30:00', 'assigned_to' => $users['abcd']],
            ['title' => 'Risk rules UI', 'description' => 'Build the interface for selecting attendance and submission thresholds.', 'status' => 'in-progress', 'priority' => 'high', 'deadline' => '2026-04-27', 'buckx_allocated' => 85.00, 'buckx_distributed' => 0, 'buckx_distributed_at' => null, 'assigned_to' => $users['ben']],
            ['title' => 'Advisor export', 'description' => 'Create a simple advisor CSV export for at-risk students.', 'status' => 'todo', 'priority' => 'medium', 'deadline' => '2026-05-02', 'buckx_allocated' => 55.00, 'buckx_distributed' => 0, 'buckx_distributed_at' => null, 'assigned_to' => null],
        ],
        'alumni' => [
            ['title' => 'Alumni profile grid', 'description' => 'Completed responsive alumni card grid with industry and skill filters.', 'status' => 'done', 'priority' => 'high', 'deadline' => '2026-03-15', 'buckx_allocated' => 100.00, 'buckx_distributed' => 1, 'buckx_distributed_at' => '2026-03-15 17:00:00', 'assigned_to' => $users['abcd']],
            ['title' => 'Career path chart', 'description' => 'Completed chart showing alumni roles by graduation year.', 'status' => 'done', 'priority' => 'medium', 'deadline' => '2026-03-28', 'buckx_allocated' => 80.00, 'buckx_distributed' => 1, 'buckx_distributed_at' => '2026-03-28 16:20:00', 'assigned_to' => $users['nadisha']],
            ['title' => 'Mentor availability cleanup', 'description' => 'Completed mentor availability cleanup and profile copy review.', 'status' => 'done', 'priority' => 'low', 'deadline' => '2026-04-08', 'buckx_allocated' => 50.00, 'buckx_distributed' => 1, 'buckx_distributed_at' => '2026-04-08 15:30:00', 'assigned_to' => $users['tharushi']],
        ],
        'cancelled' => [
            ['title' => 'Migration risk review', 'description' => 'Initial review paused when the project was cancelled.', 'status' => 'todo', 'priority' => 'low', 'deadline' => '2026-03-20', 'buckx_allocated' => 0.00, 'buckx_distributed' => 0, 'buckx_distributed_at' => null, 'assigned_to' => null],
        ],
    ];

    foreach ($tasks as $key => $rows) {
        foreach ($rows as $task) {
            upsertTask($pdo, $projects[$key], $task);
        }
    }

    $messages = [
        'survey' => [
            [ORG_UCSC, 'Welcome to the student survey project. The first demo should highlight survey insights and outlier comments.', '2026-04-20 14:10:00'],
            [$users['ava'], 'I will start with the four rating cards and chart layout.', '2026-04-20 14:15:00'],
        ],
        'attendance' => [
            [ORG_UCSC, 'Attendance importer is the first milestone. After that, move into the risk rules UI.', '2026-04-16 17:20:00'],
            [$users['abcd'], 'Importer is done and ready for review. I used the synthetic weekly export dataset.', '2026-04-18 18:20:00'],
            [$users['ben'], 'I am working on the threshold UI and PR checklist.', '2026-04-19 10:00:00'],
        ],
        'alumni' => [
            [ORG_UCSC, 'Final review complete. Please use this project as the completed-project demo with feedback and paid-out tasks.', '2026-04-10 10:30:00'],
            [$users['nadisha'], 'Career path chart and responsive polish are complete.', '2026-04-10 10:35:00'],
        ],
    ];

    foreach ($messages as $key => $rows) {
        foreach ($rows as [$senderId, $message, $createdAt]) {
            upsertChat($pdo, $projects[$key], $senderId, $message, $createdAt);
        }
    }

    upsertFeedback($pdo, $projects['alumni'], $users['abcd'], 5, 'Excellent implementation quality and very reliable GitHub workflow during the completed UCSC alumni project.');
    upsertFeedback($pdo, $projects['alumni'], $users['nadisha'], 5, 'Strong frontend polish and chart work. Communicated clearly and delivered the dataset visualization on time.');
    upsertFeedback($pdo, $projects['attendance'], $users['abcd'], 4, 'Good ownership of the importer and repository setup. Next step is tighter documentation for handoff.');

    foreach ($projects as $projectId) {
        syncMemberCount($pdo, $projectId);
    }

    $pdo->commit();

    echo "UCSC organization demo data seeded.\n";
    foreach ($projects as $key => $projectId) {
        echo "{$key}: {$projectId}\n";
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "UCSC organization demo seed failed: " . $e->getMessage() . "\n");
    exit(1);
}
