# Start Here — Lumiora Live Event Studio

This project includes **all earlier StudentHub / Lumiora features** and adds **secure, no-page-reload AJAX CRUD** for campus events.

1. Extract to **`C:\xampp\htdocs\StudentHub_MySQL_Practical_13\`**.
2. Start **Apache** and **MySQL** in XAMPP.
3. Keep the existing `studenthub_pr8_db` database. **Do not import earlier schema files again.**
4. Keep the old Practical 12 folder to display event posters uploaded there (or copy its uploaded poster images to the new `event-posters` folder).
5. Open **`http://localhost/StudentHub_MySQL_Practical_13/login.php`** and log in as **Admin**.
6. Click **Lumiora Live Event Studio (AJAX CRUD)** on Admin Dashboard.
7. Use Add / Edit / Delete (archive) / Restore, then Search / Filter / Pagination. The page remains open while JSON requests update its contents.
8. Open Chrome DevTools **F12 → Network → Fetch/XHR** to capture GET / POST / PATCH / DELETE responses for your practical report.

**New features:** `live-event-studio.php`, `live-event-studio.js`, `live-event-studio.css`, `event-api.php`, `event-api-support.php`.

**Detailed help:** `LIVE_EVENT_STUDIO_SETUP.md` and `LIVE_EVENT_STUDIO_TEST_CASES.md`.

**Note:** Poster uploading remains in the older **Manage Events & Posters** module; the new AJAX page previews existing posters and changes textual event fields.
