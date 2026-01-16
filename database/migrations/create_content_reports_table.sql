-- Create content_reports table for reporting posts and chat messages
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
