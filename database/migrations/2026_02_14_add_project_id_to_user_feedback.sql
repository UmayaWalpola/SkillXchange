-- Migration to add project_id column to user_feedback table
-- Date: 2026-02-14
-- Purpose: Allow feedback to be associated with a specific project

-- Step 1: Add project_id column (nullable)
ALTER TABLE `user_feedback`
ADD COLUMN `project_id` INT(11) NULL AFTER `reviewer_id`;

-- Step 2: Add foreign key constraint to projects table
ALTER TABLE `user_feedback`
ADD CONSTRAINT `fk_user_feedback_project`
FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
ON DELETE SET NULL
ON UPDATE CASCADE;

-- Step 3: Add unique constraint to prevent duplicate feedback per project/user/reviewer combination
-- Note: This allows the same reviewer to give feedback to the same user on different projects
ALTER TABLE `user_feedback`
ADD CONSTRAINT `unique_project_user_reviewer_feedback`
UNIQUE (`project_id`, `user_id`, `reviewer_id`);

-- Step 4: Add index for performance
ALTER TABLE `user_feedback`
ADD INDEX `idx_project_feedback` (`project_id`, `created_at`);

-- Note: Existing feedbacks without project_id will remain (project_id = NULL)
-- This maintains backward compatibility with general user feedback not tied to a project
