-- Migration: Create Chat Transaction Events Table
-- Date: 2026-04-17
-- Purpose: Create chat_transaction_events table for tracking BuckX and SkillX transactions in chat sessions

CREATE TABLE IF NOT EXISTS `chat_transaction_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `learner_id` int(11) NOT NULL,
  `payment_type` enum('buckx','skillx') NOT NULL,
  `amount` decimal(10,2) DEFAULT 0,
  `skill_debt_hours` decimal(8,2) DEFAULT 0,
  `skill_name` varchar(255) DEFAULT NULL,
  `agreed_timeframe_hours` int(11) DEFAULT 1,
  `status` enum('pending_learner','pending_teacher','active','teacher_completed','completed','terminated','disputed') DEFAULT 'pending_learner',
  `expires_at` timestamp NULL DEFAULT NULL,
  `both_agreed_at` timestamp NULL DEFAULT NULL,
  `teacher_completed_at` timestamp NULL DEFAULT NULL,
  `learner_verified_at` timestamp NULL DEFAULT NULL,
  `terminated_by` int(11) DEFAULT NULL,
  `terminated_at` timestamp NULL DEFAULT NULL,
  `dispute_flag` tinyint(1) DEFAULT 0,
  `dispute_reason` text DEFAULT NULL,
  `dispute_created_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chat_id` (`chat_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `learner_id` (`learner_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `fk_chat_transaction_events_chat` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_chat_transaction_events_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_chat_transaction_events_learner` FOREIGN KEY (`learner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_chat_transaction_events_terminated_by` FOREIGN KEY (`terminated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
