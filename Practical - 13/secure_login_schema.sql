-- Import in phpMyAdmin after selecting studenthub_pr8_db; no existing rows removed.
USE `studenthub_pr8_db`;
ALTER TABLE `user_accounts` ADD COLUMN IF NOT EXISTS `role` ENUM('student','admin') NOT NULL DEFAULT 'student';
ALTER TABLE `user_accounts` ADD COLUMN IF NOT EXISTS `last_login_at` DATETIME NULL;
CREATE TABLE IF NOT EXISTS `remember_login_tokens` (
  `selector` CHAR(24) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
  `token_hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_token_user` (`user_id`), INDEX `idx_token_expiry` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `user_accounts`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
