# Practical 9 — StudentHub Secure Registration

This project continues Practical 8. **Keep the existing `studenthub_pr8_db`; do not delete or re-import Practical 8 seed data.**

## Setup
1. Extract the folder `StudentHub_MySQL_Practical_9` into `C:\xampp\htdocs\`.
2. Start Apache + MySQL in XAMPP.
3. phpMyAdmin → click **studenthub_pr8_db** → **Import** → select **secure_registration_schema.sql** → Go. It adds `user_accounts` and `registration_audit` only.
4. Open `http://localhost/StudentHub_MySQL_Practical_9/register.html` (not file://).
5. Enter first/last name, **username** (new), email, mobile, student ID, course, semester, valid password & confirmation, and check terms. Click Create Account.
6. Verify `studenthub_pr8_db → user_accounts → Browse`. The `password_hash` cell should be a long hash; it must not match the password you entered. Check `registration_audit → Browse` for a row.
7. Test invalid email, weak password, duplicate email and duplicate username. Fill `registration-test-report.html` and print/save as PDF.

## Required screenshots
- Form UI with username, validation attributes
- Successful registration message
- Invalid input case (browser or server validation)
- Duplicate email or username error
- phpMyAdmin `user_accounts` table with hashed password (do not disclose actual test password)
- Optional advanced: `registration_audit` table

## Files added or changed
- NEW: secure_registration_schema.sql — account and audit tables.
- NEW: mysqli-db.php — MySQLi connection; **Practical 8 `db.php` PDO remains unchanged**.
- NEW: secure-register.php — server-side validation, prepared SELECT/INSERT, password_hash, duplicate check, and successful-registration audit.
- UPDATED: register.html — new required username field and action secure-register.php.
- NEW: registration-test-report.html — actual-results checklist.
- OLD: register.php — preserved CSV-based Practical 7 functionality but no longer used by register.html.

## Notes
- XAMPP defaults to MySQL user `root` with blank password. Set environment variables DB_HOST, DB_NAME, DB_USER, DB_PASS if yours differs.
- Existing `login.html` is only a frontend page; **Practical 9 creates accounts but does not implement MySQL login/authentication**.
- No CSRF feature in this version; optional advanced audit logging is included.
- For real internet deployment add CSRF protection, HTTPS, email verification and rate limiting.
