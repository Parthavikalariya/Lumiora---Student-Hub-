# Architecture and data flow

Browser (HTML, CSS, JavaScript)
  → Apache/XAMPP (PHP 8)
    → `auth.php`: PHP sessions, ID rotation, role and account-status checks
    → `role-menu.php`: admin/student sidebar from trusted server role
    → `admin-dashboard-data.php`: SQL aggregation and latest registrations
    → `student-management.php` / `event-management.php`: MySQLi prepared CRUD
    → `event-api.php`: JSON CRUD endpoints consumed by `live-event-studio.js` via Fetch
    → `admin-audit-helper.php`: `audit_logs` entries for administrative activity
    → `integration-center.php`: read-only readiness diagnostics (admin only)
      → MySQL/MariaDB `studenthub_pr8_db`

Core DB tables: `students`, `events`, `event_registrations`, `user_accounts`, `audit_logs`. Auxiliary tables: `event_change_audit`, `registration_audit`, `remember_login_tokens`.

Access boundary: `portal.php` is public. Sensitive admin PHP endpoints call `requireLogin('admin')`. Student workspace PHP endpoints call `requireLogin('student')`. Static historical `.html` demonstration content is **public**, so it must not contain personal/private database records. Event-posters are allowed image uploads only.

GitHub Pages can render static files only; PHP + MySQL need local XAMPP or compatible hosting.
