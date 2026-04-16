-- Optional performance migration for skill-based project suggestions and apply validation.
-- This migration is NOT mandatory for correctness, but recommended for faster filtering.

ALTER TABLE `user_skills`
  ADD INDEX `idx_user_skills_match` (`user_id`, `skill_type`, `skill_name`);

ALTER TABLE `projects`
  ADD INDEX `idx_projects_status_created` (`status`, `created_at`);

ALTER TABLE `project_applications`
  ADD INDEX `idx_project_app_user_project_status` (`user_id`, `project_id`, `status`);
