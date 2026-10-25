-- Lumiora Practical 14: additive migration ONLY; do not drop any existing data.
-- phpMyAdmin -> select studenthub_pr8_db -> Import this file exactly once.
USE `studenthub_pr8_db`;

ALTER TABLE `user_accounts`
  ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) NOT NULL DEFAULT 1;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `log_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `admin_user_id` INT UNSIGNED NOT NULL,
  `action` VARCHAR(60) NOT NULL,
  `target_type` VARCHAR(40) NOT NULL,
  `target_id` INT UNSIGNED DEFAULT NULL,
  `details` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_audit_logs_created` (`created_at`, `log_id`),
  INDEX `idx_audit_logs_actor` (`admin_user_id`, `created_at`),
  INDEX `idx_audit_logs_target` (`target_type`, `target_id`),
  CONSTRAINT `fk_audit_logs_admin` FOREIGN KEY (`admin_user_id`)
    REFERENCES `user_accounts` (`user_id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify after import:
-- SHOW COLUMNS FROM user_accounts LIKE 'is_active';
-- SHOW TABLES LIKE 'audit_logs';
-- SELECT COUNT(*) AS existing_users_preserved FROM user_accounts;
