-- Migration: add extended application fields to project_applications
-- Run this in your database (phpMyAdmin or CLI)

ALTER TABLE project_applications
  ADD COLUMN IF NOT EXISTS relevant_experience TEXT,
  ADD COLUMN IF NOT EXISTS matching_skills TEXT,
  ADD COLUMN IF NOT EXISTS contribution TEXT,
  ADD COLUMN IF NOT EXISTS available_time VARCHAR(50),
  ADD COLUMN IF NOT EXISTS expected_duration VARCHAR(50),
  ADD COLUMN IF NOT EXISTS motivation TEXT,
  ADD COLUMN IF NOT EXISTS portfolio VARCHAR(255);

-- Note: some MySQL/MariaDB versions do not support IF NOT EXISTS for ADD COLUMN.
-- If your server errors, run the ALTER statements individually after checking existing columns.
