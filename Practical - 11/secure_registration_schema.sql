-- Practical 9: run ONCE in phpMyAdmin > studenthub_pr8_db > Import.
-- Adds new tables only; preserves Practical 8 tables and their data.
CREATE DATABASE IF NOT EXISTS studenthub_pr8_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studenthub_pr8_db;

CREATE TABLE IF NOT EXISTS user_accounts (
  user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(30) NOT NULL UNIQUE,
  first_name VARCHAR(30) NOT NULL,
  last_name VARCHAR(30) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  mobile VARCHAR(10) NOT NULL,
  student_code VARCHAR(30) NOT NULL,
  course VARCHAR(80) NOT NULL,
  semester TINYINT UNSIGNED NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional advanced extension: successful registration audit trail (no passwords).
CREATE TABLE IF NOT EXISTS registration_audit (
  audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  action VARCHAR(32) NOT NULL DEFAULT 'registered',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES user_accounts(user_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
