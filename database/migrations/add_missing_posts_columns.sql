-- Add missing columns to posts table for community forum functionality

ALTER TABLE `posts` 
ADD COLUMN `post_type` ENUM('message', 'announcement', 'discussion') DEFAULT 'message' AFTER `content`,
ADD COLUMN `parent_id` INT(11) NULL DEFAULT NULL AFTER `post_type`,
ADD COLUMN `is_pinned` TINYINT(1) DEFAULT 0 AFTER `parent_id`,
ADD INDEX `idx_community_id` (`community_id`),
ADD INDEX `idx_parent_id` (`parent_id`),
ADD INDEX `idx_created_at` (`created_at`);

-- Update existing posts to have default post_type if not set
UPDATE `posts` SET `post_type` = 'message' WHERE `post_type` IS NULL;
