-- Migration: Add BuckX Allocation to Tasks
-- Date: 2026-04-09
-- Purpose: Add columns to support BuckX allocation for task rewards

ALTER TABLE `project_tasks` 
ADD COLUMN `buckx_allocated` DECIMAL(10, 2) DEFAULT 0.00 AFTER `deadline`,
ADD COLUMN `buckx_distributed` TINYINT(1) DEFAULT 0 AFTER `buckx_allocated`,
ADD COLUMN `buckx_distributed_at` TIMESTAMP NULL DEFAULT NULL AFTER `buckx_distributed`;

-- Add index for easier queries
ALTER TABLE `project_tasks` 
ADD INDEX `idx_buckx_allocated` (`buckx_allocated`),
ADD INDEX `idx_buckx_distributed` (`buckx_distributed`);
