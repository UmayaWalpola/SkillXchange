<?php

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../core/Database.php';

$pdo = (new Database())->connect();
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$passwordHash = '$2y$10$27/QXBMdqyp/aEkqeijmT.fuEJgwSju.ZIObOBxRHFKg/AoEVOXHu'; // DemoPass123!

$users = [
    'main' => [
        'username' => 'Ava Demo',
        'email' => 'demo.ava@skillxchange.local',
        'role' => 'individual',
        'bio' => 'Frontend learner and collaborator used for the evaluator demo.',
        'profile_completed' => 1,
        'wallet_balance' => 210.00,
        'buckx_frozen' => 0.00,
        'skillx_debt_hours' => 1.50,
        'teach' => [
            ['frontend', 'advanced'],
            ['cloud', 'intermediate'],
        ],
        'learn' => [
            ['github', 'beginner'],
            ['marketing', 'beginner'],
            ['web-development', 'beginner'],
        ],
    ],
    'live' => [
        'username' => 'Ben Match',
        'email' => 'demo.ben@skillxchange.local',
        'role' => 'individual',
        'bio' => 'Live session partner for request and offer acceptance.',
        'profile_completed' => 1,
        'wallet_balance' => 250.00,
        'buckx_frozen' => 0.00,
        'skillx_debt_hours' => 0.00,
        'teach' => [
            ['github', 'advanced'],
        ],
        'learn' => [
            ['frontend', 'intermediate'],
        ],
    ],
    'archive' => [
        'username' => 'Cara Archive',
        'email' => 'demo.cara@skillxchange.local',
        'role' => 'individual',
        'bio' => 'Older terminated chat history for the evaluator demo.',
        'profile_completed' => 1,
        'wallet_balance' => 250.00,
        'buckx_frozen' => 0.00,
        'skillx_debt_hours' => 0.00,
        'teach' => [
            ['web-development', 'advanced'],
        ],
        'learn' => [
            ['cloud', 'beginner'],
        ],
    ],
    'history' => [
        'username' => 'Dilan History',
        'email' => 'demo.dilan@skillxchange.local',
        'role' => 'individual',
        'bio' => 'Completed BuckX and SkillX history for wallet and debt views.',
        'profile_completed' => 1,
        'wallet_balance' => 290.00,
        'buckx_frozen' => 0.00,
        'skillx_debt_hours' => 0.00,
        'teach' => [
            ['marketing', 'advanced'],
        ],
        'learn' => [
            ['frontend', 'intermediate'],
        ],
    ],
];

function fetchId(PDO $pdo, string $email): ?int
{
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch();
    return $row ? (int) $row['id'] : null;
}

function ensureUser(PDO $pdo, array $user, string $passwordHash): int
{
    $existingId = fetchId($pdo, $user['email']);

    if ($existingId) {
        $stmt = $pdo->prepare(
            'UPDATE users
             SET username = :username,
                 email = :email,
                 password = :password,
                 role = :role,
                 bio = :bio,
                 profile_picture = NULL,
                 profile_completed = :profile_completed,
                 status = \'active\',
                 buckx_frozen = :buckx_frozen,
                 skillx_debt_hours = :skillx_debt_hours
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $existingId,
            'username' => $user['username'],
            'email' => $user['email'],
            'password' => $passwordHash,
            'role' => $user['role'],
            'bio' => $user['bio'],
            'profile_completed' => $user['profile_completed'],
            'buckx_frozen' => $user['buckx_frozen'],
            'skillx_debt_hours' => $user['skillx_debt_hours'],
        ]);
        return $existingId;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO users
         (username, email, password, role, bio, profile_picture, profile_completed, status, buckx_frozen, skillx_debt_hours)
         VALUES
         (:username, :email, :password, :role, :bio, NULL, :profile_completed, \'active\', :buckx_frozen, :skillx_debt_hours)'
    );
    $stmt->execute([
        'username' => $user['username'],
        'email' => $user['email'],
        'password' => $passwordHash,
        'role' => $user['role'],
        'bio' => $user['bio'],
        'profile_completed' => $user['profile_completed'],
        'buckx_frozen' => $user['buckx_frozen'],
        'skillx_debt_hours' => $user['skillx_debt_hours'],
    ]);

    return (int) $pdo->lastInsertId();
}

function ensureUserStats(PDO $pdo, int $userId, array $user): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO user_stats (user_id, connections_count, skills_taught_count, skills_learning_count, hours_exchanged)
         VALUES (:user_id, 0, :teach_count, :learn_count, 0)
         ON DUPLICATE KEY UPDATE
             skills_taught_count = VALUES(skills_taught_count),
             skills_learning_count = VALUES(skills_learning_count)'
    );
    $stmt->execute([
        'user_id' => $userId,
        'teach_count' => count($user['teach']),
        'learn_count' => count($user['learn']),
    ]);
}

function ensureWallet(PDO $pdo, int $userId, float $balance): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO wallets (user_id, balance, created_at, updated_at)
         VALUES (:user_id, :balance, NOW(), NOW())
         ON DUPLICATE KEY UPDATE
             balance = VALUES(balance),
             updated_at = NOW()'
    );
    $stmt->execute([
        'user_id' => $userId,
        'balance' => $balance,
    ]);
}

function resetSkills(PDO $pdo, int $userId, array $user): void
{
    $pdo->prepare('DELETE FROM user_skills WHERE user_id = :user_id')->execute(['user_id' => $userId]);

    $insert = $pdo->prepare(
        'INSERT INTO user_skills (user_id, skill_name, skill_type, proficiency_level, created_at)
         VALUES (:user_id, :skill_name, :skill_type, :proficiency_level, NOW())'
    );

    foreach ($user['teach'] as [$skill, $level]) {
        $insert->execute([
            'user_id' => $userId,
            'skill_name' => $skill,
            'skill_type' => 'teach',
            'proficiency_level' => $level,
        ]);
    }

    foreach ($user['learn'] as [$skill, $level]) {
        $insert->execute([
            'user_id' => $userId,
            'skill_name' => $skill,
            'skill_type' => 'learn',
            'proficiency_level' => $level,
        ]);
    }
}

function deleteChatBundle(PDO $pdo, int $chatId): void
{
    $pdo->prepare('DELETE FROM transaction_notifications WHERE event_id IN (SELECT id FROM chat_transaction_events WHERE chat_id = :chat_id)')
        ->execute(['chat_id' => $chatId]);
    $pdo->prepare('DELETE FROM skill_debt WHERE event_id IN (SELECT id FROM chat_transaction_events WHERE chat_id = :chat_id)')
        ->execute(['chat_id' => $chatId]);
    $pdo->prepare('DELETE FROM frozen_buckx WHERE event_id IN (SELECT id FROM chat_transaction_events WHERE chat_id = :chat_id)')
        ->execute(['chat_id' => $chatId]);
    $pdo->prepare('DELETE FROM transaction_history WHERE event_id IN (SELECT id FROM chat_transaction_events WHERE chat_id = :chat_id)')
        ->execute(['chat_id' => $chatId]);
    $pdo->prepare('DELETE FROM chat_transaction_events WHERE chat_id = :chat_id')
        ->execute(['chat_id' => $chatId]);
    $pdo->prepare('DELETE FROM chat_messages WHERE chat_id = :chat_id')
        ->execute(['chat_id' => $chatId]);
    $pdo->prepare('DELETE FROM chats WHERE id = :chat_id')
        ->execute(['chat_id' => $chatId]);
}

function resetPair(PDO $pdo, int $userA, int $userB): void
{
    $stmt = $pdo->prepare(
        'SELECT id FROM chats
         WHERE (user1_id = :a AND user2_id = :b) OR (user1_id = :b AND user2_id = :a)'
    );
    $stmt->execute(['a' => $userA, 'b' => $userB]);

    foreach ($stmt->fetchAll() as $row) {
        deleteChatBundle($pdo, (int) $row['id']);
    }

    $pdo->prepare(
        'DELETE FROM notifications
         WHERE (user_id = :a OR user_id = :b)
           AND (message LIKE \'%session%\' OR message LIKE \'%transaction%\' OR message LIKE \'%connect%\')'
    )->execute(['a' => $userA, 'b' => $userB]);

    $pdo->prepare(
        'DELETE FROM exchanges
         WHERE (requester_id = :a AND receiver_id = :b) OR (requester_id = :b AND receiver_id = :a)'
    )->execute(['a' => $userA, 'b' => $userB]);
}

function createChat(PDO $pdo, int $user1, int $user2, string $createdAt): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO chats (user1_id, user2_id, skill_context, exchange_direction, created_at, updated_at)
         VALUES (:user1, :user2, NULL, NULL, :created_at, :created_at)'
    );
    $stmt->execute([
        'user1' => $user1,
        'user2' => $user2,
        'created_at' => $createdAt,
    ]);

    return (int) $pdo->lastInsertId();
}

function addMessage(PDO $pdo, int $chatId, int $senderId, string $message, string $createdAt): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO chat_messages (chat_id, sender_id, message, created_at, read_status)
         VALUES (:chat_id, :sender_id, :message, :created_at, 1)'
    );
    $stmt->execute([
        'chat_id' => $chatId,
        'sender_id' => $senderId,
        'message' => $message,
        'created_at' => $createdAt,
    ]);
}

function addExchange(PDO $pdo, int $requesterId, int $receiverId, string $skillOffered, string $skillWanted, string $status, string $createdAt): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO exchanges (requester_id, receiver_id, skill_id, skill_offered, skill_wanted, status, created_at)
         VALUES (:requester_id, :receiver_id, 1, :skill_offered, :skill_wanted, :status, :created_at)'
    );
    $stmt->execute([
        'requester_id' => $requesterId,
        'receiver_id' => $receiverId,
        'skill_offered' => $skillOffered,
        'skill_wanted' => $skillWanted,
        'status' => $status,
        'created_at' => $createdAt,
    ]);
}

function addTransactionEvent(PDO $pdo, array $event): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO chat_transaction_events
         (chat_id, teacher_id, learner_id, payment_type, amount, skill_debt_hours, skill_name, agreed_timeframe_hours,
          offer_created_at, both_agreed_at, expires_at, status, teacher_completed_at, learner_verified_at,
          terminated_by, terminated_at, dispute_flag, created_at, updated_at)
         VALUES
         (:chat_id, :teacher_id, :learner_id, :payment_type, :amount, :skill_debt_hours, :skill_name, :agreed_timeframe_hours,
          :offer_created_at, :both_agreed_at, :expires_at, :status, :teacher_completed_at, :learner_verified_at,
          :terminated_by, :terminated_at, 0, :created_at, :updated_at)'
    );
    $stmt->execute([
        'chat_id' => $event['chat_id'],
        'teacher_id' => $event['teacher_id'],
        'learner_id' => $event['learner_id'],
        'payment_type' => $event['payment_type'],
        'amount' => $event['amount'],
        'skill_debt_hours' => $event['skill_debt_hours'],
        'skill_name' => $event['skill_name'],
        'agreed_timeframe_hours' => $event['agreed_timeframe_hours'],
        'offer_created_at' => $event['offer_created_at'],
        'both_agreed_at' => $event['both_agreed_at'],
        'expires_at' => $event['expires_at'],
        'status' => $event['status'],
        'teacher_completed_at' => $event['teacher_completed_at'],
        'learner_verified_at' => $event['learner_verified_at'],
        'terminated_by' => $event['terminated_by'],
        'terminated_at' => $event['terminated_at'],
        'created_at' => $event['created_at'],
        'updated_at' => $event['updated_at'],
    ]);

    return (int) $pdo->lastInsertId();
}

$pdo->beginTransaction();

try {
    $ids = [];

    foreach ($users as $key => $user) {
        $ids[$key] = ensureUser($pdo, $user, $passwordHash);
    }

    foreach ($users as $key => $user) {
        ensureUserStats($pdo, $ids[$key], $user);
        ensureWallet($pdo, $ids[$key], $user['wallet_balance']);
        resetSkills($pdo, $ids[$key], $user);
    }

    $pairs = [
        ['main', 'live'],
        ['main', 'archive'],
        ['main', 'history'],
    ];

    foreach ($pairs as [$left, $right]) {
        resetPair($pdo, $ids[$left], $ids[$right]);
    }

    $archiveChatId = createChat($pdo, $ids['main'], $ids['archive'], '2026-04-10 09:00:00');
    addMessage($pdo, $archiveChatId, $ids['main'], 'Thanks for the earlier web development session. I had to step away before we finished.', '2026-04-10 09:02:00');
    addMessage($pdo, $archiveChatId, $ids['archive'], 'No problem, we can restart another time.', '2026-04-10 09:03:00');
    addMessage($pdo, $archiveChatId, $ids['main'], 'Keeping this here to show our older terminated chat history.', '2026-04-10 09:05:00');
    addTransactionEvent($pdo, [
        'chat_id' => $archiveChatId,
        'teacher_id' => $ids['archive'],
        'learner_id' => $ids['main'],
        'payment_type' => 'buckx',
        'amount' => 30.00,
        'skill_debt_hours' => null,
        'skill_name' => null,
        'agreed_timeframe_hours' => 24,
        'offer_created_at' => '2026-04-10 08:45:00',
        'both_agreed_at' => '2026-04-10 08:50:00',
        'expires_at' => '2026-04-11 08:50:00',
        'status' => 'terminated',
        'teacher_completed_at' => null,
        'learner_verified_at' => null,
        'terminated_by' => $ids['main'],
        'terminated_at' => '2026-04-10 09:04:00',
        'created_at' => '2026-04-10 08:45:00',
        'updated_at' => '2026-04-10 09:04:00',
    ]);

    addExchange($pdo, $ids['main'], $ids['history'], 'frontend', 'marketing', 'active', '2026-04-12 11:00:00');
    $historyChatId = createChat($pdo, $ids['main'], $ids['history'], '2026-04-12 11:05:00');
    addMessage($pdo, $historyChatId, $ids['main'], 'The previous marketing session worked well. I want to show this as a completed BuckX transfer in the wallet.', '2026-04-12 11:06:00');
    addMessage($pdo, $historyChatId, $ids['history'], 'Great, and the follow-up mentoring can stay as a SkillX debt example.', '2026-04-12 11:07:00');

    $buckxEventId = addTransactionEvent($pdo, [
        'chat_id' => $historyChatId,
        'teacher_id' => $ids['history'],
        'learner_id' => $ids['main'],
        'payment_type' => 'buckx',
        'amount' => 40.00,
        'skill_debt_hours' => null,
        'skill_name' => null,
        'agreed_timeframe_hours' => 2,
        'offer_created_at' => '2026-04-12 11:08:00',
        'both_agreed_at' => '2026-04-12 11:10:00',
        'expires_at' => '2026-04-12 13:10:00',
        'status' => 'completed',
        'teacher_completed_at' => '2026-04-12 12:00:00',
        'learner_verified_at' => '2026-04-12 12:05:00',
        'terminated_by' => null,
        'terminated_at' => null,
        'created_at' => '2026-04-12 11:08:00',
        'updated_at' => '2026-04-12 12:05:00',
    ]);

    $pdo->prepare(
        'INSERT INTO frozen_buckx (event_id, user_id, amount, status, frozen_at, transferred_at)
         VALUES (:event_id, :user_id, :amount, \'transferred\', :frozen_at, :transferred_at)'
    )->execute([
        'event_id' => $buckxEventId,
        'user_id' => $ids['main'],
        'amount' => 40.00,
        'frozen_at' => '2026-04-12 11:10:00',
        'transferred_at' => '2026-04-12 12:05:00',
    ]);

    $pdo->prepare(
        'INSERT INTO wallet_transactions (sender_id, receiver_id, amount, note, transaction_type, status, created_at)
         VALUES (:sender_id, :receiver_id, :amount, :note, \'transfer\', \'completed\', :created_at)'
    )->execute([
        'sender_id' => $ids['main'],
        'receiver_id' => $ids['history'],
        'amount' => 40.00,
        'note' => 'BuckX transferred for completed session #' . $buckxEventId,
        'created_at' => '2026-04-12 12:05:00',
    ]);

    $skillxEventId = addTransactionEvent($pdo, [
        'chat_id' => $historyChatId,
        'teacher_id' => $ids['history'],
        'learner_id' => $ids['main'],
        'payment_type' => 'skillx',
        'amount' => null,
        'skill_debt_hours' => 1.50,
        'skill_name' => 'marketing',
        'agreed_timeframe_hours' => 2,
        'offer_created_at' => '2026-04-13 15:00:00',
        'both_agreed_at' => '2026-04-13 15:05:00',
        'expires_at' => '2026-04-13 17:05:00',
        'status' => 'completed',
        'teacher_completed_at' => '2026-04-13 16:20:00',
        'learner_verified_at' => '2026-04-13 16:25:00',
        'terminated_by' => null,
        'terminated_at' => null,
        'created_at' => '2026-04-13 15:00:00',
        'updated_at' => '2026-04-13 16:25:00',
    ]);

    $pdo->prepare(
        'INSERT INTO skill_debt (event_id, debtor_id, creditor_id, hours_owed, skill_name, status, created_at, activated_at)
         VALUES (:event_id, :debtor_id, :creditor_id, :hours_owed, :skill_name, \'active\', :created_at, :activated_at)'
    )->execute([
        'event_id' => $skillxEventId,
        'debtor_id' => $ids['main'],
        'creditor_id' => $ids['history'],
        'hours_owed' => 1.50,
        'skill_name' => 'marketing',
        'created_at' => '2026-04-13 16:25:00',
        'activated_at' => '2026-04-13 16:25:00',
    ]);

    $pdo->prepare(
        'INSERT INTO transaction_history (event_id, type, from_user_id, to_user_id, amount, hours, description, created_at)
         VALUES
         (:buckx_event_id, \'buckx_transfer\', :main_id, :history_id, 40.00, NULL, :buckx_desc, :buckx_created_at),
         (:skillx_event_id, \'skillx_create\', :main_id, :history_id, NULL, 1.50, :skillx_create_desc, :skillx_created_at),
         (:skillx_event_id, \'skillx_transfer\', NULL, NULL, NULL, 1.50, :skillx_transfer_desc, :skillx_created_at)'
    )->execute([
        'buckx_event_id' => $buckxEventId,
        'skillx_event_id' => $skillxEventId,
        'main_id' => $ids['main'],
        'history_id' => $ids['history'],
        'buckx_desc' => 'BuckX transferred for completed transaction #' . $buckxEventId,
        'buckx_created_at' => '2026-04-12 12:05:00',
        'skillx_create_desc' => 'SkillX debt created for transaction #' . $skillxEventId,
        'skillx_transfer_desc' => 'SkillX debt activated for transaction #' . $skillxEventId,
        'skillx_created_at' => '2026-04-13 16:25:00',
    ]);

    $pdo->prepare(
        'UPDATE user_stats
         SET connections_count = :connections, hours_exchanged = :hours_exchanged
         WHERE user_id = :user_id'
    )->execute([
        'user_id' => $ids['main'],
        'connections' => 1,
        'hours_exchanged' => 3,
    ]);

    $pdo->prepare(
        'UPDATE user_stats
         SET connections_count = :connections, hours_exchanged = :hours_exchanged
         WHERE user_id = :user_id'
    )->execute([
        'user_id' => $ids['history'],
        'connections' => 1,
        'hours_exchanged' => 3,
    ]);

    $pdo->commit();

    echo "Evaluator demo data is ready.\n";
    echo "Main demo account: demo.ava@skillxchange.local / DemoPass123!\n";
    echo "Live request partner: demo.ben@skillxchange.local / DemoPass123!\n";
    echo "Archived chat partner: demo.cara@skillxchange.local / DemoPass123!\n";
    echo "Wallet history partner: demo.dilan@skillxchange.local / DemoPass123!\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Failed to seed evaluator demo data: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
