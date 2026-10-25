-- StudentHub / Lumiora Event Management schema upgrade
-- Run in phpMyAdmin AFTER choosing the existing studenthub_pr8_db database.
-- Designed for XAMPP's MariaDB 10.4. NO existing table or data is dropped.
USE `studenthub_pr8_db`;

ALTER TABLE `events`
  ADD COLUMN IF NOT EXISTS `poster_path` VARCHAR(255) NULL AFTER `description`,
  ADD COLUMN IF NOT EXISTS `event_status` ENUM('open','closed') NOT NULL DEFAULT 'open' AFTER `poster_path`,
  ADD COLUMN IF NOT EXISTS `is_archived` TINYINT(1) NOT NULL DEFAULT 0 AFTER `event_status`,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL AFTER `is_archived`;

CREATE INDEX IF NOT EXISTS `idx_events_status_archived_date`
  ON `events` (`is_archived`, `event_status`, `event_date`);
CREATE INDEX IF NOT EXISTS `idx_events_category` ON `events` (`category`);

CREATE TABLE IF NOT EXISTS `event_change_audit` (
  `audit_id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT UNSIGNED NOT NULL,
  `admin_user_id` INT UNSIGNED NOT NULL,
  `action` ENUM('created','updated','archived','restored','opened','closed') NOT NULL,
  `details` VARCHAR(220) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_event_audit_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_event_audit_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `user_accounts` (`user_id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX `idx_event_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Six extra fictional events, safe to import repeatedly.
-- Existing events (IDs 1, 2, 3) and their student registrations remain intact.
INSERT INTO `events` (`event_name`,`category`,`event_date`,`venue`,`description`,`event_status`)
SELECT 'AI Innovation Seminar','Seminar','2026-11-20','Seminar Hall 2','Guest talk and practical AI applications.','open'
WHERE NOT EXISTS (SELECT 1 FROM `events` WHERE `event_name`='AI Innovation Seminar' AND `event_date`='2026-11-20');
INSERT INTO `events` (`event_name`,`category`,`event_date`,`venue`,`description`,`event_status`)
SELECT 'Intercollege Sports Meet','Sports','2026-11-26','Sports Ground','Football and track competitions.','open'
WHERE NOT EXISTS (SELECT 1 FROM `events` WHERE `event_name`='Intercollege Sports Meet' AND `event_date`='2026-11-26');
INSERT INTO `events` (`event_name`,`category`,`event_date`,`venue`,`description`,`event_status`)
SELECT 'Cybersecurity Awareness Workshop','Workshop','2026-12-08','Computer Lab 3','Safe browsing, password hygiene and security fundamentals.','open'
WHERE NOT EXISTS (SELECT 1 FROM `events` WHERE `event_name`='Cybersecurity Awareness Workshop' AND `event_date`='2026-12-08');
INSERT INTO `events` (`event_name`,`category`,`event_date`,`venue`,`description`,`event_status`)
SELECT 'Campus Startup Pitch','Technical','2026-12-12','Innovation Center','Student teams present startup ideas.','open'
WHERE NOT EXISTS (SELECT 1 FROM `events` WHERE `event_name`='Campus Startup Pitch' AND `event_date`='2026-12-12');
INSERT INTO `events` (`event_name`,`category`,`event_date`,`venue`,`description`,`event_status`)
SELECT 'Photography Exhibition','Cultural','2026-12-15','Exhibition Hall','Showcase of student photography projects.','closed'
WHERE NOT EXISTS (SELECT 1 FROM `events` WHERE `event_name`='Photography Exhibition' AND `event_date`='2026-12-15');
INSERT INTO `events` (`event_name`,`category`,`event_date`,`venue`,`description`,`event_status`)
SELECT 'Data Science Bootcamp','Workshop','2026-12-19','Computer Lab 2','Hands-on Python data analysis.','open'
WHERE NOT EXISTS (SELECT 1 FROM `events` WHERE `event_name`='Data Science Bootcamp' AND `event_date`='2026-12-19');

-- Verify: SELECT event_id,event_name,category,event_status,is_archived FROM events;
-- Verify: SHOW TABLES LIKE 'event_change_audit';
