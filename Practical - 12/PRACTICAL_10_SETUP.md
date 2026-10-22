# Practical 10 — Secure Login / Logout (StudentHub / Lumiora)

## Setup
1. Extract folder into `C:\xampp\htdocs\StudentHub_MySQL_Practical_10`.
2. Start Apache + MySQL in XAMPP. **Keep the existing `studenthub_pr8_db`** from Practicals 8 and 9.
3. phpMyAdmin → select `studenthub_pr8_db` → Import → `secure_login_schema.sql` → Go. Verify new `role` / `last_login_at` columns and `remember_login_tokens` table.
4. Open `http://localhost/StudentHub_MySQL_Practical_10/login.php` and sign in using email/password from Practical 9 registration.
5. Default account role is `student` → redirected to `student-dashboard.php`.

## Admin test
Run in XAMPP's PHP CLI (example Windows CMD, from this folder):
`C:\xampp\php\php.exe create-admin-cli.php admin@example.test`
Enter a NEW 12+ character password for a local test account. Open the login page and use it to access `admin-dashboard.php`. Do not publish the admin seeding script in production.

## Evidence/screenshots needed
1. Successful student login & secure dashboard.
2. Failed login (wrong password; use a test account).
3. Logout → `login.php?status=logout`, then direct access to student dashboard redirects to login.
4. Unauthorized dashboard URL while logged out; student trying `/admin-dashboard.php` returns 403.
5. Timeout: default 15 min idle. For quick local testing set `SESSION_IDLE_LIMIT = 60` in `auth.php`, sign in *without* Remember Me, wait over 60 seconds, refresh dashboard, screenshot `status=timeout`; restore 900 afterward.
6. Admin login goes to admin dashboard; `last_login_at` in phpMyAdmin; optional Remember Me cookie in DevTools.

## Important notes
- Old `dashboard.html` now redirects to protected PHP dashboard; old `login.html` points to secure PHP login.
- `login.php` uses `password_verify`, prepared statements, session regeneration, CSRF token, safe generic login failures.
- Remember-me stores selector + a **SHA-256 hash** of a random secret in DB, with 14-day expiry and rotation; only the secret goes in the HttpOnly cookie.
- In localhost over HTTP, Secure cookies cannot be required for standard local testing; deploy under HTTPS for Secure cookies.
- Remember-me auto-login can restore access if you start a fresh browser session. For logout and timeout screenshots test *without* Remember Me checked.
- Existing static pages such as `timetable.html` or `attendance.html` are not protected by sessions; the role-protected practical dashboards are PHP pages. To protect all pages later, convert them to PHP and call `requireLogin`.
- The earlier `register.html` and Practical 9 `secure-register.php` stay intact; the new schema is additive.
