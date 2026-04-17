-- Migration: Community Admin Action Logs
-- Date: 2026-04-17
-- Purpose: Create community_admin_actions table for logging admin actions on communities
-- Note: Working with existing communities table - no additional community_management table needed

-- ===============================================
-- Create community admin action logs table
-- ===============================================
CREATE TABLE IF NOT EXISTS `community_admin_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `community_id` int(11) NOT NULL,
  `action_type` enum('create','activate','deactivate','delete') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `community_id` (`community_id`),
  CONSTRAINT `fk_community_admin_actions_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_community_admin_actions_community` FOREIGN KEY (`community_id`) REFERENCES `communities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
