-- Sync patch based on:
--   c:\xampp\htdocs\SkillXchange\DB.sql
--   c:\2nd year project\UmayaDB.sql
--
-- Goal:
--   1. Add tables that exist in UmayaDB.sql but not in DB.sql
--   2. Add missing columns to shared tables
--   3. Backfill compatible data from the current schema
--
-- This patch is additive. It does not drop your existing tables/columns.

START TRANSACTION;

-- ---------------------------------------------------------------------------
-- Shared tables: add missing columns from UmayaDB.sql
-- ---------------------------------------------------------------------------

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'buckx_balance'
  ),
  'SELECT 1',
  'ALTER TABLE `users` ADD COLUMN `buckx_balance` decimal(10,2) DEFAULT 0.00 AFTER `status`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'buckx_frozen'
  ),
  'SELECT 1',
  'ALTER TABLE `users` ADD COLUMN `buckx_frozen` decimal(10,2) DEFAULT 0.00 AFTER `buckx_balance`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'skillx_debt_hours'
  ),
  'SELECT 1',
  'ALTER TABLE `users` ADD COLUMN `skillx_debt_hours` decimal(10,2) DEFAULT 0.00 AFTER `buckx_frozen`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exchanges' AND COLUMN_NAME = 'skill_offered'
  ),
  'SELECT 1',
  'ALTER TABLE `exchanges` ADD COLUMN `skill_offered` varchar(255) DEFAULT NULL AFTER `skill_id`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exchanges' AND COLUMN_NAME = 'skill_wanted'
  ),
  'SELECT 1',
  'ALTER TABLE `exchanges` ADD COLUMN `skill_wanted` varchar(255) DEFAULT NULL AFTER `skill_offered`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications' AND COLUMN_NAME = 'title'
  ),
  'SELECT 1',
  'ALTER TABLE `notifications` ADD COLUMN `title` varchar(255) DEFAULT NULL AFTER `type`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications' AND COLUMN_NAME = 'related_user_id'
  ),
  'SELECT 1',
  'ALTER TABLE `notifications` ADD COLUMN `related_user_id` int(11) DEFAULT NULL AFTER `message`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications' AND COLUMN_NAME = 'related_exchange_id'
  ),
  'SELECT 1',
  'ALTER TABLE `notifications` ADD COLUMN `related_exchange_id` int(11) DEFAULT NULL AFTER `related_user_id`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quizzes' AND COLUMN_NAME = 'quiz_id'
  ),
  'SELECT 1',
  'ALTER TABLE `quizzes` ADD COLUMN `quiz_id` int(11) DEFAULT NULL AFTER `id`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quizzes' AND COLUMN_NAME = 'manager_id'
  ),
  'SELECT 1',
  'ALTER TABLE `quizzes` ADD COLUMN `manager_id` int(11) DEFAULT NULL AFTER `quiz_id`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quizzes' AND COLUMN_NAME = 'difficulty_level'
  ),
  'SELECT 1',
  'ALTER TABLE `quizzes` ADD COLUMN `difficulty_level` enum(''Beginner'',''Intermediate'',''Expert'') DEFAULT ''Intermediate'' AFTER `description`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quizzes' AND COLUMN_NAME = 'duration'
  ),
  'SELECT 1',
  'ALTER TABLE `quizzes` ADD COLUMN `duration` int(11) DEFAULT 30 COMMENT ''Duration in minutes'' AFTER `difficulty_level`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quizzes' AND COLUMN_NAME = 'total_questions'
  ),
  'SELECT 1',
  'ALTER TABLE `quizzes` ADD COLUMN `total_questions` int(11) DEFAULT 0 AFTER `status`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quiz_questions' AND COLUMN_NAME = 'question_id'
  ),
  'SELECT 1',
  'ALTER TABLE `quiz_questions` ADD COLUMN `question_id` int(11) DEFAULT NULL AFTER `id`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quiz_questions' AND COLUMN_NAME = 'option_a'
  ),
  'SELECT 1',
  'ALTER TABLE `quiz_questions` ADD COLUMN `option_a` varchar(200) DEFAULT NULL AFTER `question_text`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quiz_questions' AND COLUMN_NAME = 'option_b'
  ),
  'SELECT 1',
  'ALTER TABLE `quiz_questions` ADD COLUMN `option_b` varchar(200) DEFAULT NULL AFTER `option_a`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quiz_questions' AND COLUMN_NAME = 'option_c'
  ),
  'SELECT 1',
  'ALTER TABLE `quiz_questions` ADD COLUMN `option_c` varchar(200) DEFAULT NULL AFTER `option_b`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quiz_questions' AND COLUMN_NAME = 'option_d'
  ),
  'SELECT 1',
  'ALTER TABLE `quiz_questions` ADD COLUMN `option_d` varchar(200) DEFAULT NULL AFTER `option_c`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quiz_questions' AND COLUMN_NAME = 'correct_answer'
  ),
  'SELECT 1',
  'ALTER TABLE `quiz_questions` ADD COLUMN `correct_answer` tinyint(4) DEFAULT NULL COMMENT ''0=A, 1=B, 2=C, 3=D'' AFTER `option_d`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quiz_questions' AND COLUMN_NAME = 'question_order'
  ),
  'SELECT 1',
  'ALTER TABLE `quiz_questions` ADD COLUMN `question_order` int(11) DEFAULT 0 AFTER `correct_answer`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_reports' AND COLUMN_NAME = 'reporter_id'
  ),
  'SELECT 1',
  'ALTER TABLE `user_reports` ADD COLUMN `reporter_id` int(11) DEFAULT NULL AFTER `id`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_reports' AND COLUMN_NAME = 'description'
  ),
  'SELECT 1',
  'ALTER TABLE `user_reports` ADD COLUMN `description` text DEFAULT NULL AFTER `reason`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_reports' AND COLUMN_NAME = 'created_at'
  ),
  'SELECT 1',
  'ALTER TABLE `user_reports` ADD COLUMN `created_at` timestamp NULL DEFAULT current_timestamp() AFTER `status`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_badges' AND COLUMN_NAME = 'badge_name'
  ),
  'SELECT 1',
  'ALTER TABLE `user_badges` ADD COLUMN `badge_name` varchar(100) DEFAULT NULL AFTER `user_id`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_badges' AND COLUMN_NAME = 'badge_icon'
  ),
  'SELECT 1',
  'ALTER TABLE `user_badges` ADD COLUMN `badge_icon` varchar(10) DEFAULT ''?'' AFTER `badge_name`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'wallet_notifications' AND COLUMN_NAME = 'transaction_id'
  ),
  'SELECT 1',
  'ALTER TABLE `wallet_notifications` ADD COLUMN `transaction_id` int(11) DEFAULT NULL AFTER `user_id`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'wallet_notifications' AND COLUMN_NAME = 'title'
  ),
  'SELECT 1',
  'ALTER TABLE `wallet_notifications` ADD COLUMN `title` varchar(255) DEFAULT NULL AFTER `type`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'wallet_transactions' AND COLUMN_NAME = 'transaction_type'
  ),
  'SELECT 1',
  'ALTER TABLE `wallet_transactions` ADD COLUMN `transaction_type` enum(''transfer'',''reward'',''penalty'') NOT NULL DEFAULT ''transfer'' AFTER `note`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------------
-- Backfill data for the new compatibility columns
-- ---------------------------------------------------------------------------

UPDATE `users`
SET
  `buckx_balance` = COALESCE(`buckx_balance`, 0.00),
  `buckx_frozen` = COALESCE(`buckx_frozen`, 0.00),
  `skillx_debt_hours` = COALESCE(`skillx_debt_hours`, 0.00);

UPDATE `exchanges` e
LEFT JOIN `skills` s ON s.`id` = e.`skill_id`
SET
  e.`skill_offered` = COALESCE(e.`skill_offered`, s.`skill_name`)
WHERE e.`skill_offered` IS NULL;

UPDATE `notifications`
SET `title` = COALESCE(`title`, 'Notification')
WHERE `title` IS NULL;

UPDATE `quizzes`
SET
  `quiz_id` = COALESCE(`quiz_id`, `id`),
  `manager_id` = COALESCE(`manager_id`, `created_by`),
  `difficulty_level` = COALESCE(
    `difficulty_level`,
    CASE
      WHEN `difficulty` = 'beginner' THEN 'Beginner'
      WHEN `difficulty` = 'intermediate' THEN 'Intermediate'
      WHEN `difficulty` = 'advanced' THEN 'Expert'
      ELSE 'Intermediate'
    END
  ),
  `duration` = COALESCE(`duration`, `time_limit`, 30),
  `total_questions` = COALESCE(
    `total_questions`,
    (SELECT COUNT(*) FROM `quiz_questions` qq WHERE qq.`quiz_id` = `quizzes`.`id`)
  );

UPDATE `quiz_questions`
SET
  `question_id` = COALESCE(`question_id`, `id`),
  `question_order` = COALESCE(`question_order`, `order_number`, 0);

UPDATE `quiz_questions` qq
SET
  qq.`option_a` = COALESCE(
    qq.`option_a`,
    (SELECT qqo.`option_text` FROM `quiz_question_options` qqo WHERE qqo.`question_id` = qq.`id` AND qqo.`order_number` = 0 LIMIT 1)
  ),
  qq.`option_b` = COALESCE(
    qq.`option_b`,
    (SELECT qqo.`option_text` FROM `quiz_question_options` qqo WHERE qqo.`question_id` = qq.`id` AND qqo.`order_number` = 1 LIMIT 1)
  ),
  qq.`option_c` = COALESCE(
    qq.`option_c`,
    (SELECT qqo.`option_text` FROM `quiz_question_options` qqo WHERE qqo.`question_id` = qq.`id` AND qqo.`order_number` = 2 LIMIT 1)
  ),
  qq.`option_d` = COALESCE(
    qq.`option_d`,
    (SELECT qqo.`option_text` FROM `quiz_question_options` qqo WHERE qqo.`question_id` = qq.`id` AND qqo.`order_number` = 3 LIMIT 1)
  ),
  qq.`correct_answer` = COALESCE(
    qq.`correct_answer`,
    (SELECT qqo.`order_number` FROM `quiz_question_options` qqo WHERE qqo.`question_id` = qq.`id` AND qqo.`is_correct` = 1 LIMIT 1)
  );

UPDATE `user_reports`
SET
  `reporter_id` = COALESCE(`reporter_id`, `reporter_org_id`),
  `description` = COALESCE(`description`, `details`),
  `created_at` = COALESCE(`created_at`, `reported_at`);

UPDATE `user_badges` ub
INNER JOIN `badges` b ON b.`id` = ub.`badge_id`
SET
  ub.`badge_name` = COALESCE(ub.`badge_name`, b.`name`),
  ub.`badge_icon` = COALESCE(ub.`badge_icon`, b.`icon`)
WHERE ub.`badge_name` IS NULL OR ub.`badge_icon` IS NULL;

UPDATE `wallet_notifications`
SET `title` = COALESCE(
  `title`,
  CASE
    WHEN `type` = 'payment_received' THEN 'Payment Received'
    WHEN `type` = 'payment_sent' THEN 'Payment Sent'
    WHEN `type` = 'low_balance' THEN 'Low Balance'
    ELSE 'Wallet Notification'
  END
)
WHERE `title` IS NULL;

-- ---------------------------------------------------------------------------
-- Compatibility indexes for Umaya's quiz schema
-- ---------------------------------------------------------------------------

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quizzes' AND INDEX_NAME = 'uq_quizzes_quiz_id'
  ),
  'SELECT 1',
  'ALTER TABLE `quizzes` ADD UNIQUE KEY `uq_quizzes_quiz_id` (`quiz_id`)'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quizzes' AND INDEX_NAME = 'idx_quizzes_manager_id_compat'
  ),
  'SELECT 1',
  'ALTER TABLE `quizzes` ADD KEY `idx_quizzes_manager_id_compat` (`manager_id`)'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quiz_questions' AND INDEX_NAME = 'uq_quiz_questions_question_id'
  ),
  'SELECT 1',
  'ALTER TABLE `quiz_questions` ADD UNIQUE KEY `uq_quiz_questions_question_id` (`question_id`)'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quiz_questions' AND INDEX_NAME = 'idx_quiz_questions_order_compat'
  ),
  'SELECT 1',
  'ALTER TABLE `quiz_questions` ADD KEY `idx_quiz_questions_order_compat` (`quiz_id`, `question_order`)'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------------
-- New tables from UmayaDB.sql
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `buckx_packages` (
  `package_id` int NOT NULL AUTO_INCREMENT,
  `package_name` varchar(100) NOT NULL,
  `buckx_amount` int NOT NULL COMMENT 'Amount of BuckX in this package',
  `price_usd` decimal(10,2) NOT NULL COMMENT 'Price in USD',
  `price_lkr` decimal(10,2) DEFAULT NULL COMMENT 'Price in LKR (optional)',
  `discount_percentage` int DEFAULT 0 COMMENT 'Discount % for display',
  `is_popular` tinyint(1) DEFAULT 0 COMMENT 'Mark as popular package',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Enable/disable package',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `buckx_purchases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `org_id` int NOT NULL,
  `package_id` int DEFAULT NULL,
  `buckx_amount` int NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `payment_gateway` varchar(50) DEFAULT 'stripe',
  `stripe_session_id` varchar(255) DEFAULT NULL,
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','processing','completed','failed','refunded') DEFAULT 'pending',
  `price_lkr` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_org_status` (`org_id`, `payment_status`),
  KEY `idx_stripe_session` (`stripe_session_id`),
  CONSTRAINT `buckx_purchases_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user1_id` int NOT NULL,
  `user2_id` int NOT NULL,
  `skill_context` varchar(100) DEFAULT NULL,
  `exchange_direction` enum('teach','learn') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user2_id` (`user2_id`),
  KEY `idx_users` (`user1_id`, `user2_id`),
  CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `chat_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `read_status` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `idx_chat` (`chat_id`, `created_at`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `chat_transaction_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `chat_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `learner_id` int NOT NULL,
  `payment_type` enum('buckx','skillx') NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `skill_debt_hours` decimal(5,2) DEFAULT NULL,
  `skill_name` varchar(255) DEFAULT NULL,
  `agreed_timeframe_hours` int NOT NULL,
  `offer_created_at` timestamp NULL DEFAULT current_timestamp(),
  `both_agreed_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending_learner','pending_teacher','active','teacher_completed','completed','expired','learner_timeout','terminated','disputed') DEFAULT 'pending_learner',
  `teacher_completed_at` timestamp NULL DEFAULT NULL,
  `learner_verified_at` timestamp NULL DEFAULT NULL,
  `auto_completed_at` timestamp NULL DEFAULT NULL,
  `terminated_by` int DEFAULT NULL,
  `terminated_at` timestamp NULL DEFAULT NULL,
  `dispute_flag` tinyint(1) DEFAULT 0,
  `dispute_reason` text DEFAULT NULL,
  `dispute_created_at` timestamp NULL DEFAULT NULL,
  `dispute_resolved_by` int DEFAULT NULL,
  `dispute_resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `terminated_by` (`terminated_by`),
  KEY `dispute_resolved_by` (`dispute_resolved_by`),
  KEY `idx_chat_id` (`chat_id`),
  KEY `idx_teacher_id` (`teacher_id`),
  KEY `idx_learner_id` (`learner_id`),
  KEY `idx_status` (`status`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_dispute_flag` (`dispute_flag`),
  KEY `idx_timeout_processing` (`status`, `expires_at`),
  KEY `idx_verification_timeout` (`status`, `teacher_completed_at`),
  CONSTRAINT `chat_transaction_events_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_transaction_events_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_transaction_events_ibfk_3` FOREIGN KEY (`learner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_transaction_events_ibfk_4` FOREIGN KEY (`terminated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chat_transaction_events_ibfk_5` FOREIGN KEY (`dispute_resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `frozen_buckx` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('frozen','released','transferred') DEFAULT 'frozen',
  `frozen_at` timestamp NULL DEFAULT current_timestamp(),
  `released_at` timestamp NULL DEFAULT NULL,
  `transferred_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `frozen_buckx_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `chat_transaction_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `frozen_buckx_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payment_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `purchase_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `event_type` varchar(50) NOT NULL COMMENT 'session_created, payment_completed, error, etc.',
  `event_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_purchase_logs` (`purchase_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `payment_logs_chk_1` CHECK (json_valid(`event_data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quiz_attempts` (
  `attempt_id` int NOT NULL AUTO_INCREMENT,
  `quiz_id` int NOT NULL,
  `user_id` int NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `total_questions` int NOT NULL,
  `correct_answers` int NOT NULL,
  `time_taken` int DEFAULT NULL COMMENT 'Time taken in seconds',
  `status` enum('completed','in_progress','abandoned') DEFAULT 'in_progress',
  `started_at` timestamp NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`attempt_id`),
  KEY `idx_quiz` (`quiz_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `saved_quizzes` (
  `saved_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `saved_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`saved_id`),
  UNIQUE KEY `unique_user_quiz` (`user_id`, `quiz_id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `saved_quizzes_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `skill_debt` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `debtor_id` int NOT NULL,
  `creditor_id` int NOT NULL,
  `hours_owed` decimal(5,2) NOT NULL,
  `skill_name` varchar(255) NOT NULL,
  `status` enum('pending','active','voided','fulfilled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `activated_at` timestamp NULL DEFAULT NULL,
  `voided_at` timestamp NULL DEFAULT NULL,
  `fulfilled_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_debtor_id` (`debtor_id`),
  KEY `idx_creditor_id` (`creditor_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `skill_debt_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `chat_transaction_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `skill_debt_ibfk_2` FOREIGN KEY (`debtor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `skill_debt_ibfk_3` FOREIGN KEY (`creditor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `skill_matches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `match_score` decimal(5,2) DEFAULT 0.00,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `transaction_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `type` enum('buckx_freeze','buckx_release','buckx_transfer','skillx_create','skillx_void','skillx_transfer') NOT NULL,
  `from_user_id` int DEFAULT NULL,
  `to_user_id` int DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `hours` decimal(5,2) DEFAULT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_from_user` (`from_user_id`),
  KEY `idx_to_user` (`to_user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `transaction_history_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `chat_transaction_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaction_history_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaction_history_ibfk_3` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transaction_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` enum('offer_received','offer_accepted','session_started','reminder_incomplete','teacher_completed','expiry_warning','timeout_warning','payment_transferred','payment_released','dispute_created') NOT NULL,
  `message` text NOT NULL,
  `sent` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `scheduled_for` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_sent` (`sent`),
  KEY `idx_scheduled_for` (`scheduled_for`),
  CONSTRAINT `transaction_notifications_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `chat_transaction_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaction_notifications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Existing tables from other patches: align them to Umaya's version
-- ---------------------------------------------------------------------------

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'buckx_packages' AND COLUMN_NAME = 'price_usd'
  ),
  'SELECT 1',
  'ALTER TABLE `buckx_packages` ADD COLUMN `price_usd` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT ''Price in USD'' AFTER `buckx_amount`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'buckx_packages' AND COLUMN_NAME = 'discount_percentage'
  ),
  'SELECT 1',
  'ALTER TABLE `buckx_packages` ADD COLUMN `discount_percentage` int DEFAULT 0 COMMENT ''Discount % for display'' AFTER `price_lkr`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'buckx_packages' AND COLUMN_NAME = 'is_popular'
  ),
  'SELECT 1',
  'ALTER TABLE `buckx_packages` ADD COLUMN `is_popular` tinyint(1) DEFAULT 0 COMMENT ''Mark as popular package'' AFTER `discount_percentage`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'buckx_purchases' AND COLUMN_NAME = 'amount_paid'
  ),
  'SELECT 1',
  'ALTER TABLE `buckx_purchases` ADD COLUMN `amount_paid` decimal(10,2) DEFAULT NULL AFTER `buckx_amount`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'buckx_purchases' AND COLUMN_NAME = 'currency'
  ),
  'SELECT 1',
  'ALTER TABLE `buckx_purchases` ADD COLUMN `currency` varchar(3) DEFAULT ''USD'' AFTER `amount_paid`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------------
-- Seed data and data migration for equivalent existing tables
-- ---------------------------------------------------------------------------

INSERT IGNORE INTO `buckx_packages`
  (`package_id`, `package_name`, `buckx_amount`, `price_usd`, `price_lkr`, `discount_percentage`, `is_popular`, `is_active`, `created_at`, `updated_at`)
VALUES
  (1, 'Starter Pack', 500, 5.00, 1500.00, 0, 0, 1, '2026-01-11 13:32:08', '2026-01-11 13:32:08'),
  (2, 'Basic Pack', 1000, 9.00, 2700.00, 10, 0, 1, '2026-01-11 13:32:08', '2026-01-11 13:32:08'),
  (3, 'Pro Pack', 2500, 20.00, 6000.00, 20, 1, 1, '2026-01-11 13:32:08', '2026-01-11 13:32:08'),
  (4, 'Business Pack', 5000, 35.00, 10500.00, 30, 0, 1, '2026-01-11 13:32:08', '2026-01-11 13:32:08'),
  (5, 'Enterprise Pack', 10000, 60.00, 18000.00, 40, 0, 1, '2026-01-11 13:32:08', '2026-01-11 13:32:08');

INSERT IGNORE INTO `quiz_attempts`
  (`attempt_id`, `quiz_id`, `user_id`, `score`, `total_questions`, `correct_answers`, `time_taken`, `status`, `started_at`, `completed_at`)
SELECT
  uqa.`id`,
  q.`quiz_id`,
  uqa.`user_id`,
  uqa.`score`,
  uqa.`total_questions`,
  uqa.`correct_answers`,
  uqa.`time_taken`,
  uqa.`status`,
  uqa.`started_at`,
  uqa.`completed_at`
FROM `user_quiz_attempts` uqa
INNER JOIN `quizzes` q ON q.`id` = uqa.`quiz_id`;

INSERT IGNORE INTO `saved_quizzes`
  (`saved_id`, `user_id`, `quiz_id`, `saved_at`)
SELECT
  usq.`id`,
  usq.`user_id`,
  q.`quiz_id`,
  usq.`saved_at`
FROM `user_saved_quizzes` usq
INNER JOIN `quizzes` q ON q.`id` = usq.`quiz_id`;

COMMIT;

-- ---------------------------------------------------------------------------
-- Manual follow-up differences
-- ---------------------------------------------------------------------------
--
-- 1. Your DB still keeps old quiz tables:
--    `user_quiz_attempts`, `user_saved_quizzes`, `quiz_question_options`
--    Umaya DB uses:
--    `quiz_attempts`, `saved_quizzes`, inline options in `quiz_questions`
--
-- 2. `wallet_notifications.type` values are still your original enum values.
--    This patch only adds Umaya's missing columns.
--
-- 3. `skill_matches` in UmayaDB.sql has no foreign keys. Same pattern is kept here.
--
-- 4. `quiz_question_options.order_number` is assumed to be 0-based when
--    backfilling `option_a`..`option_d` and `correct_answer`.
