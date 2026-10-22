# StudentHub / Lumiora — Event Management Setup

## Files and naming

This folder is a complete copy of the previous StudentHub project with Event Management added. New files are named for their purpose:

| File | Purpose |
| --- | --- |
| `event_management_schema.sql` | Safe database migration + six sample events + audit log |
| `event-management.php` | Event list, poster previews, search, filters and pagination |
| `event-form.php` | Add/edit event form and MySQL INSERT/UPDATE |
| `event-records.php` | Shared secure MySQLi helpers, validation, search |
| `event-poster-upload.php` | File size/type/image integrity validation and storage |
| `event-status.php` | Confirmed soft delete, restore and open/closed status |
| `event-audit-log.php` | Admin-only event change history |
| `event-management-style.css` | Matching dark-theme layout |
| `event-posters/.htaccess` | Disable directory listing and block executable uploads |
| `sample-event-poster.png` | Sample image for poster upload testing |
| `EVENT_MANAGEMENT_TEST_CASES.md` | Screenshot/checklist guide |

## Step-by-step setup (XAMPP)

1. Extract the folder to `C:\xampp\htdocs\StudentHub_MySQL_Practical_12\`.
2. Start Apache and MySQL in XAMPP. Keep earlier Practical 11 project unchanged.
3. Open `http://localhost/phpmyadmin/` and select **studenthub_pr8_db** on the left.
4. Click **Import**, choose `event_management_schema.sql`, and click **Go**. **Never re-import older schema dumps** and do not delete/drop the database. This migration adds new columns, logs and 6 sample events without erasing old events/student registrations.
5. Visit `http://localhost/StudentHub_MySQL_Practical_12/login.php`. Sign in with the same admin account you created earlier. Admin credentials live in the shared database.
6. From Admin Dashboard choose **Manage Events & Posters**, or open `/StudentHub_MySQL_Practical_12/event-management.php` while logged in.
7. Add an event and choose `sample-event-poster.png` from the project folder as its upload. JPG/PNG/WebP up to 2 MB supported.
8. Edit event name/venue and replace poster, test category filter, Open/Closed, archive/restore, then look at Event Audit Log.
9. After screenshots, export the **events** and **event_change_audit** tables from phpMyAdmin in SQL format. Do not export the `user_accounts` table (not needed for this submission).

## Security and operation notes

- Only authenticated admins may access the new PHP routes; student accounts are blocked.
- INSERT/UPDATE/status changes use MySQLi prepared statements and validate CSRF tokens.
- File uploads check `$_FILES` status, max size **2 MiB**, `finfo` MIME, image header and pixel limits. SVG/PDF/PHP/other files are rejected; stored filenames are random. File folder `.htaccess` disables PHP execution and indexes when Apache allows overrides.
- For training on `localhost` (HTTP), cookies cannot carry HTTPS-only protection. Use HTTPS and hardened server configuration before deployment on the public web.
- Event Delete is intentionally a **soft delete / Archive** so earlier `event_registrations` foreign keys remain valid. `Restore` returns the event to the active list. Audit log records admin actions.
- Changing event status to **Closed** updates its record. The older Practical 8 event-registration demonstration remains unchanged; if you publish student self-service registrations, enforce `event_status` and `is_archived` in that registration endpoint too.
- `sample-event-poster.png` is a fictional test image, not copyrighted event artwork.

## If something goes wrong

- **Unknown column / missing table**: import `event_management_schema.sql` into selected `studenthub_pr8_db`.
- **Upload not saving**: check the `event-posters/` directory exists, is writable and `upload_max_filesize` / `post_max_size` in XAMPP's `php.ini` are at least 2 MB / 3 MB.
- **Session not working**: sign in again via Practical 12's `login.php` (session cookie is project-specific).
- **Admin refused**: verify `user_accounts.role='admin'`; do not weaken the access check.
