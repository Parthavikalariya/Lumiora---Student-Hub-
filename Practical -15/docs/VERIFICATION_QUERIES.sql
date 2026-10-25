-- Practical 15: READ-ONLY database inspection queries.
-- Select existing studenthub_pr8_db first. No inserts, deletes or updates.
USE `studenthub_pr8_db`;
SELECT 'registered_users' AS metric, COUNT(*) AS total FROM `user_accounts`
UNION ALL SELECT 'active_accounts',COUNT(*) FROM `user_accounts` WHERE `is_active`=1
UNION ALL SELECT 'active_students', COUNT(*) FROM `students` WHERE `status`='active'
UNION ALL SELECT 'active_events',COUNT(*) FROM `events` WHERE `is_archived`=0
UNION ALL SELECT 'registrations',COUNT(*) FROM `event_registrations`;
SELECT `action`,COUNT(*) AS records FROM `audit_logs` GROUP BY `action` ORDER BY `action`;
SELECT `role`,`is_active`,COUNT(*) AS accounts FROM `user_accounts` GROUP BY `role`,`is_active`;
