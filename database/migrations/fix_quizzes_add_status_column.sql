-- Fix: Add missing status column to quizzes table
-- Run this in phpMyAdmin if you get "Unknown column 'status'" error

-- Check if status column exists, if not add it
ALTER TABLE `quizzes` 
ADD COLUMN IF NOT EXISTS `status` enum('active','inactive') DEFAULT 'active' 
AFTER `created_by`;

-- Alternatively, if the above doesn't work (older MySQL versions):
-- ALTER TABLE `quizzes` ADD `status` enum('active','inactive') DEFAULT 'active' AFTER `created_by`;

-- Update existing records to have active status
UPDATE `quizzes` SET `status` = 'active' WHERE `status` IS NULL;

-- Add index for better performance
CREATE INDEX IF NOT EXISTS `idx_status` ON `quizzes`(`status`);
