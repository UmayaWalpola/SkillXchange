-- Drop unused rating column from platform_feedback (platform/management feedback)
-- Safe/idempotent: only runs if the column exists.

SET @col_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'platform_feedback'
      AND COLUMN_NAME = 'rating'
);

SET @sql := IF(
    @col_exists > 0,
    'ALTER TABLE platform_feedback DROP COLUMN rating',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
