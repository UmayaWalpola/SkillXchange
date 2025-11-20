-- Alter project_applications table to add new fields for detailed application information
-- Run this SQL in phpMyAdmin to update your database structure

ALTER TABLE `project_applications` ADD COLUMN `experience` LONGTEXT NULL AFTER `message`;
ALTER TABLE `project_applications` ADD COLUMN `skills` LONGTEXT NULL AFTER `experience`;
ALTER TABLE `project_applications` ADD COLUMN `contribution` LONGTEXT NULL AFTER `skills`;
ALTER TABLE `project_applications` ADD COLUMN `commitment` VARCHAR(100) NULL AFTER `contribution`;
ALTER TABLE `project_applications` ADD COLUMN `duration` VARCHAR(100) NULL AFTER `commitment`;
ALTER TABLE `project_applications` ADD COLUMN `motivation` LONGTEXT NULL AFTER `duration`;
ALTER TABLE `project_applications` ADD COLUMN `portfolio` VARCHAR(500) NULL AFTER `motivation`;

-- Add index for faster queries
ALTER TABLE `project_applications` ADD INDEX `idx_applied_at` (`applied_at`);

-- Verify the new structure
DESCRIBE project_applications;

