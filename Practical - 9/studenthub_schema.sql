-- StudentHub / Lumiora: Practical 8 MySQL schema and seed data
-- Import using phpMyAdmin > Import; MySQL 8 / MariaDB, utf8mb4
CREATE DATABASE IF NOT EXISTS studenthub_pr8_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studenthub_pr8_db;

CREATE TABLE IF NOT EXISTS students (
  student_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  enrollment_no VARCHAR(20) NOT NULL UNIQUE,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  course VARCHAR(80) NOT NULL,
  semester TINYINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_students_semester CHECK (semester BETWEEN 1 AND 8)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
  event_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_name VARCHAR(150) NOT NULL,
  category VARCHAR(40) NOT NULL,
  event_date DATE NOT NULL,
  venue VARCHAR(120) NOT NULL,
  description TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS event_registrations (
  registration_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  event_id INT UNSIGNED NOT NULL,
  registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_student_event UNIQUE (student_id, event_id),
  CONSTRAINT fk_reg_student FOREIGN KEY (student_id) REFERENCES students(student_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_reg_event FOREIGN KEY (event_id) REFERENCES events(event_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_reg_event (event_id)
) ENGINE=InnoDB;

-- Repeatable sample data (IDs assigned automatically; enrollment/email used as natural identifiers).
INSERT INTO students (enrollment_no,full_name,email,course,semester) VALUES
('DEMOCE101','Aarav Patel','aarav.demo@example.com','B.Tech Computer Engineering',3),
('DEMOCE102','Nisha Shah','nisha.demo@example.com','B.Tech Computer Engineering',3),
('DEMOIT103','Riya Mehta','riya.demo@example.com','B.Tech Information Technology',3)
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name);

INSERT INTO events (event_id,event_name,category,event_date,venue,description) VALUES
(1,'Web Development Workshop','Workshop','2026-11-05','Computer Lab 1','Introduction to full-stack web development'),
(2,'Coding Challenge','Technical','2026-11-12','Seminar Hall','Problem solving and programming competition'),
(3,'College Cultural Fest','Cultural','2026-12-03','Auditorium','Student performances and cultural activities')
ON DUPLICATE KEY UPDATE event_name=VALUES(event_name);

INSERT INTO event_registrations (student_id,event_id)
SELECT s.student_id,1 FROM students AS s WHERE s.enrollment_no='DEMOCE101'
ON DUPLICATE KEY UPDATE registration_id=registration_id;
INSERT INTO event_registrations (student_id,event_id)
SELECT s.student_id,2 FROM students AS s WHERE s.enrollment_no='DEMOCE102'
ON DUPLICATE KEY UPDATE registration_id=registration_id;

-- Optional advanced extension: stored procedure returns registrations per event
DROP PROCEDURE IF EXISTS GetEventRegistrationCount;
DELIMITER $$
CREATE PROCEDURE GetEventRegistrationCount(IN requested_event_id INT)
BEGIN
  SELECT e.event_id, e.event_name, COUNT(r.registration_id) AS total_registrations
  FROM events e LEFT JOIN event_registrations r ON e.event_id=r.event_id
  WHERE e.event_id=requested_event_id
  GROUP BY e.event_id, e.event_name;
END $$
DELIMITER ;
-- Example usage: CALL GetEventRegistrationCount(1);
