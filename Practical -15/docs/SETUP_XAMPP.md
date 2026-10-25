# Step-by-step XAMPP installation

## A. Upgrade the existing working Practical 14 environment (recommended)

1. In `C:\xampp\htdocs\`, **keep** `StudentHub_MySQL_Practical_12`, `_13`, and `_14`; do not overwrite or delete them yet.
2. Extract the Practical 15 ZIP and paste folder `StudentHub_MySQL_Practical_15` inside `C:\xampp\htdocs\`.
3. Start XAMPP Apache + MySQL. Use the **existing** database named `studenthub_pr8_db`.
4. **Do not import any old SQL files again**; the database already contains your students, events and audit logs.
5. Browse `http://localhost/StudentHub_MySQL_Practical_15/`. It should redirect to `portal.php`.
6. Login with the existing admin account. Open `http://localhost/StudentHub_MySQL_Practical_15/integration-center.php`.
7. Check all tables and then complete `docs/TEST_PLAN.md` tests in a real browser.
8. **Poster migration:** Old uploads are not shipped in source ZIP. If `events.poster_path` points to `event-posters/filename.png` and posters are missing, **copy** actual poster files from the older `StudentHub_MySQL_Practical_12/event-posters/` folder into the new `..._15/event-posters/`, retaining exactly the filenames. Do not change SQL paths or overwrite unrelated files. The PHP event view can temporarily fall back to older folders.

## B. A completely fresh empty installation (not needed for an upgrade)

1. Create an empty `studenthub_pr8_db` in phpMyAdmin (or allow base script to create it).
2. Import once, **in this order**: `studenthub_schema.sql`, `secure_registration_schema.sql`, `secure_login_schema.sql`, `student_management_schema.sql`, `event_management_schema.sql`, `admin_control_schema.sql`.
3. `studenthub_schema.sql`, `student_management_schema.sql`, and `event_management_schema.sql` contain **demo seed data** and should only be run on a disposable fresh DB. They are not a complete copy of your semester's current state.
4. Secure admin accounts are **not generated automatically**. For a fresh, local-only setup, run `php create-admin-cli.php you@example.test` from a terminal after the schema is ready; provide a unique password. Do not run this on an already provisioned system.
5. A fresh DB with only demo data will not match prior screenshot counts (e.g. 24 students, 11 events). This is expected.

## Optional database environment variables

`db.php` and `mysqli-db.php` read DB_HOST, DB_NAME, DB_USER, DB_PASS from the Apache process environment. XAMPP default localhost root/no password works for local demos, but **must not** be used on a public production host. `.env.example` is only documentation, **not auto-loaded**.

## Apache paths

If `http://localhost/StudentHub_MySQL_Practical_15/` returns 404, check folder nesting: ensure `C:\xampp\htdocs\StudentHub_MySQL_Practical_15\portal.php` exists (not `...\StudentHub_MySQL_Practical_15\StudentHub_MySQL_Practical_15\portal.php`).

## Basic smoke test

From `C:\xampp\htdocs\StudentHub_MySQL_Practical_15`:

```powershell
C:\xampp\php\php.exe tools\project-check.php --db
```

This is a preflight, not a replacement for live functional and UI testing.
