# Lumiora / StudentHub – Practical 15 Final Integration

**A single PHP + MySQL + JavaScript full-stack semester project.** This final integration is based on the user's Practical 14 package, preserving its existing modules and database structure.

## What is included

- `portal.php` / `index.php`: integrated entry point, guest, student and admin links
- `admin-dashboard.php`: database-driven admin KPI dashboard, registration analytics and account toggle
- `student-dashboard.php`: role-specific student navigation; `student-profile.php` self-service edits for the logged-in student
- `student-management.php`: student CRUD, filtering and soft deletion
- `event-management.php`: event CRUD, poster validation, archives and history
- `live-event-studio.php`, `event-api.php`: AJAX/Fetch JSON API + filtering + errors
- `admin-audit-logs.php`: central admin action audit history
- `integration-center.php`: **read-only, admin-only** file/database readiness checks
- `docs/`: installation, virtual-host setup, GitHub steps, test checklist and report templates
- `tools/`: CLI-only project preflight and private database backup script
- prior Practical 8–14 SQL schema/migration files and static legacy demonstration pages

## Quick start (existing XAMPP installation)

1. Keep the old Practical 12/13/14 folders until posters have been migrated and verified.
2. Extract the ZIP. Copy the folder `StudentHub_MySQL_Practical_15` to `C:\xampp\htdocs\`.
3. Start Apache and MySQL. Keep your **existing** `studenthub_pr8_db` database.
4. **Do not re-import** the old `studenthub_schema.sql` or seed SQL. No new migration is required for this release if Practical 14 already worked.
5. Open <http://localhost/StudentHub_MySQL_Practical_15/>. Use your existing admin/student credentials.
6. Admin: open `integration-center.php` and inspect schema and file checks. Then perform browser tests in `docs/TEST_PLAN.md`.

**Do not rely on the file/DB check alone:** actual login, CRUD, AJAX, accessibility and GitHub repository submission must be tested on your own system.

## Database / SQL dump

The application uses `studenthub_pr8_db` (students, events, event_registrations, user_accounts, audit_logs and auxiliary tables).

- Existing DB: no import required. First create a **private full backup** using phpMyAdmin → select `studenthub_pr8_db` → Export → Quick → SQL (or `tools/backup-database.ps1` on a default XAMPP installation).
- Brand-new empty DB: see `docs/SETUP_XAMPP.md` for ordered historical migrations. Do not seed onto an existing DB unnecessarily.
- Actual account hashes and student data are **not bundled in this GitHub-ready source ZIP**. A full operational SQL dump must be exported from the user's own local MySQL and submitted privately where required. Keep it out of public GitHub.

## GitHub

See `docs/GITHUB_SUBMISSION.md`. This ZIP contains Git-ready source, but **does not claim a repository was created, committed, pushed or published**. Those steps need to be run using your own GitHub account.

## Requirements

XAMPP Apache 2.4+, PHP 8.0+, MySQL/MariaDB, enabled `mysqli`, `pdo_mysql`, `fileinfo`, sessions; Chrome/Edge for manual testing. Bootstrap grid is referenced by one existing dashboard via CDN but local dashboard styling otherwise works on its own.

## Current test status

Static PHP lint, syntax and archive integrity can be verified before delivery. Local functional features and virtual-host deployment remain **unverified until screenshots/evidence are collected**. See `docs/TEST_REPORT.md`.

## Safety notes

Use this as a **local educational project**. Do not publish the PHP backend on a publicly accessible hosting account without HTTPS, secure DB accounts, secrets management, access-control review and a real production-hardening pass. The previous static HTML demonstrations are not private student records.
