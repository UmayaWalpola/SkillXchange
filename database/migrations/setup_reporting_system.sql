-- SkillXchange Reporting System Database Setup
-- Run this file to create all reporting tables

-- 1. Content Reports Table (for posts and chat messages)
CREATE TABLE IF NOT EXISTS content_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT NOT NULL,
  content_type ENUM('post','chat_message') NOT NULL,
  content_id INT NOT NULL,
  reason VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  status ENUM('pending','reviewed','dismissed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_content_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_content_lookup (content_type, content_id),
  INDEX idx_status (status),
  INDEX idx_reporter (reporter_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Verify reports table exists (for user profile reports)
CREATE TABLE IF NOT EXISTS reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT NOT NULL,
  reported_user_id INT NOT NULL,
  reason VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  status ENUM('pending','reviewed','dismissed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_report_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_report_reported_user FOREIGN KEY (reported_user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_report_status (status),
  INDEX idx_reported_user (reported_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Verify user_reports table exists (for project member reports)
CREATE TABLE IF NOT EXISTS user_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT NOT NULL,
  reported_user_id INT NOT NULL,
  project_id INT NOT NULL,
  reason VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  status ENUM('pending','reviewed','dismissed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_report_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_report_reported FOREIGN KEY (reported_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_report_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  INDEX idx_user_report_status (status),
  INDEX idx_user_report_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Display success message
SELECT 'Reporting system tables created successfully!' AS status;

-- Display table counts
SELECT 
    'Content Reports' AS table_name,
    COUNT(*) AS record_count 
FROM content_reports
UNION ALL
SELECT 
    'User Profile Reports' AS table_name,
    COUNT(*) AS record_count 
FROM reports
UNION ALL
SELECT 
    'Project Member Reports' AS table_name,
    COUNT(*) AS record_count 
FROM user_reports;
