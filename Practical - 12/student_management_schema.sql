-- StudentHub / Lumiora - Practical 11: Student Management CRUD
-- Import via phpMyAdmin AFTER selecting the existing studenthub_pr8_db database.
-- Designed for XAMPP MariaDB 10.4+; does not drop/reset existing tables or data.
USE `studenthub_pr8_db`;

-- Soft-delete extension: a deleted student stays in the database, preserving
-- existing event_registrations foreign keys and Practical 8 reports.
ALTER TABLE `students`
  ADD COLUMN IF NOT EXISTS `status` ENUM('active','deleted') NOT NULL DEFAULT 'active' AFTER `semester`;

CREATE INDEX IF NOT EXISTS `idx_students_status_course` ON `students` (`status`, `course`);

-- 20 additional fictional students. Re-import is safe: existing rows are not overwritten.
-- Combined with 3 Practical 8 sample students, there should be at least 23 records.
INSERT INTO `students` (`enrollment_no`, `full_name`, `email`, `course`, `semester`) VALUES
('PR11CE001','Dev Mehta','dev.mehta11@example.com','B.Tech Computer Engineering',3),
('PR11CE002','Mira Shah','mira.shah11@example.com','B.Tech Computer Engineering',4),
('PR11IT003','Ishan Patel','ishan.patel11@example.com','B.Tech Information Technology',2),
('PR11CE004','Diya Trivedi','diya.trivedi11@example.com','B.Tech Computer Engineering',1),
('PR11CS005','Krish Joshi','krish.joshi11@example.com','B.Tech Computer Science',5),
('PR11IT006','Anaya Desai','anaya.desai11@example.com','B.Tech Information Technology',4),
('PR11CE007','Om Rathod','om.rathod11@example.com','B.Tech Computer Engineering',6),
('PR11BCA008','Siya Nair','siya.nair11@example.com','BCA',2),
('PR11CS009','Aryan Verma','aryan.verma11@example.com','B.Tech Computer Science',3),
('PR11IT010','Niyati Dave','niyati.dave11@example.com','B.Tech Information Technology',1),
('PR11CE011','Pranav Bhatt','pranav.bhatt11@example.com','B.Tech Computer Engineering',8),
('PR11BCA012','Aditi Rao','aditi.rao11@example.com','BCA',4),
('PR11CS013','Yash Soni','yash.soni11@example.com','B.Tech Computer Science',7),
('PR11CE014','Riddhi Parmar','riddhi.parmar11@example.com','B.Tech Computer Engineering',5),
('PR11IT015','Harsh Kapoor','harsh.kapoor11@example.com','B.Tech Information Technology',6),
('PR11BCA016','Tanya Mishra','tanya.mishra11@example.com','BCA',3),
('PR11CS017','Kavya Singh','kavya.singh11@example.com','B.Tech Computer Science',2),
('PR11IT018','Neel Gandhi','neel.gandhi11@example.com','B.Tech Information Technology',8),
('PR11CE019','Pooja Sharma','pooja.sharma11@example.com','B.Tech Computer Engineering',7),
('PR11BCA020','Rohan Iyer','rohan.iyer11@example.com','BCA',1)
ON DUPLICATE KEY UPDATE `student_id` = `student_id`;

-- Check installed data:
-- SELECT status, COUNT(*) AS total FROM students GROUP BY status;
-- SELECT COUNT(*) AS total_students FROM students;
