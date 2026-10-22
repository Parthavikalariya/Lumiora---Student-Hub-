# Lumiora – Practical 14: Admin Control Center

## Project and file names

- Folder: `StudentHub_MySQL_Practical_14`
- Entry screen: `admin-dashboard.php` — **Lumiora Admin Control Center**
- Student role screen: `student-dashboard.php`
- Permission/menu helper: `role-menu.php`
- Statistics and analytics queries: `admin-dashboard-data.php`
- Admin account status changes: `user-access-action.php`
- Audit history view: `admin-audit-logs.php`
- Safe database migration: `admin_control_schema.sql`
- Central audit helper: `admin-audit-helper.php`
- Styling/UX: `admin-control-center.css`, `admin-control-center.js`

All previously supplied Practical 7–13 pages/modules are included.

## Installation (read carefully)

1. **Keep** existing `C:\xampp\htdocs\StudentHub_MySQL_Practical_12\` and `StudentHub_MySQL_Practical_13\` folders if using their stored poster files.
2. Extract the ZIP and copy **only** the folder `StudentHub_MySQL_Practical_14` into `C:\xampp\htdocs\`.
3. Start **Apache** and **MySQL** in XAMPP.
4. Open <http://localhost/phpmyadmin/>. Select **existing** database `studenthub_pr8_db` → **Import** → choose `StudentHub_MySQL_Practical_14/admin_control_schema.sql` → **Go**.
5. This import is an **additive migration**. It adds `user_accounts.is_active` and creates `audit_logs`; it does NOT drop tables, overwrite users, or seed fake dashboard data. **DO NOT import** old `studenthub_schema.sql` or reset the database.
6. Visit <http://localhost/StudentHub_MySQL_Practical_14/login.php> and log in with your **existing admin credentials** from Practical 10. Do not create another admin unnecessarily.
7. Open <http://localhost/StudentHub_MySQL_Practical_14/admin-dashboard.php>.

## Practical 14 testing – take screenshots one at a time

| Figure | Test and what should appear |
|---|---|
| 1 | Admin Dashboard overview, role-only sidebar and login success |
| 2 | Database-driven user/student/event/registration count cards |
| 3 | Latest registrations from `event_registrations` + monthly analytics chart |
| 4 | User account list with role and active/inactive badge |
| 5 | Confirm dialog before deactivating a **test student** account |
| 6 | Successful account deactivation + inactive badge |
| 7 | MySQL `user_accounts` Browse, selected test student `is_active=0` |
| 8 | Admin Audit Trail showing `user_deactivated` |
| 9 | Re-activate **the same test student** + success message |
| 10 | Audit Trail showing `user_activated` and previous records |
| 11 | Student role login shows **student-only** sidebar |
| 12 | Student opening `admin-dashboard.php` is rejected with HTTP **403** |
| 13 | User deactivation prevents that account from logging in (optional, use test account only) |
| 14 | Previous AJAX Event Studio, Student Management and Event Management still open |
| 15 | SQL `audit_logs` / `user_accounts` final verification in phpMyAdmin |

### User status action safety

- **Never deactivate the only admin account.** The UI and PHP server deliberately forbid toggling admin accounts.
- Toggle only an existing **test student login account** (not an arbitrary row from the `students` table).
- After deactivation, the affected user cannot sign in or restore a remembered session. The system checks the user's current database role/status on protected requests.
- The toggle uses POST + CSRF + `SELECT ... FOR UPDATE`, and the account update + new audit row are in **one transaction**.
- Existing student table records and event registrations are unaffected.

### What dashboard counters mean

- Registered Users: all `user_accounts` rows, including inactive.
- Active Students: `students` rows where `status='active'`.
- Active Events: `events` rows where `is_archived=0`.
- Event Registrations: all `event_registrations` records.
- Latest Registrations: 8 most recent rows from `event_registrations`, with joined names.
- Analytics Chart: event registrations by month, last six calendar months including current, with zero months included. **Zero bar values are valid when there are no registrations in that month.**

### Audit logs

The new `audit_logs` table stores `admin_login`, `admin_logout`, `user_deactivated`, `user_activated`, and newly performed Student Management or Event Management/Live Event Studio create/update/archive/restore/status changes.

**Important**: This is a new table. It does **not** retroactively import old `event_change_audit` rows from Practical 12–13. Those remain intact in their original table.

## Submission later

Submit admin dashboard screenshots, student RBAC denial, account toggle and audit logs, SQL dump of `audit_logs` / `user_accounts` (only if requested), and a PDF matching **Original Practical 13** fonts/layout, main new PHP/JS/SQL code with syntax highlights, all real screenshots aligned uncropped, conclusion, and **no Key Questions**. Do not claim tests passed before screenshots are verified.
