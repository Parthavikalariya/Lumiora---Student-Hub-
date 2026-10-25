# Lumiora Live Event Studio — Setup Guide

**Practical 13: PHP JSON API + AJAX/Fetch CRUD Without Full Page Reload**

This project is an updated copy of the previous Lumiora Event Management website. Registration, Login/Logout, Student Management, the original poster upload module, and Event Audit Log remain available. The AJAX module uses existing MySQL tables in **`studenthub_pr8_db`**; it does not drop, truncate, reset, or overwrite any data.

## New meaningful file names

| File | Purpose |
| --- | --- |
| `live-event-studio.php` | The admin-only Live Event Studio page and event editor |
| `live-event-studio.js` | JavaScript Fetch API calls, debounce search, async CRUD, loading and alerts |
| `live-event-studio.css` | Responsive Lumiora dark-theme design |
| `event-api.php` | JSON endpoint supporting GET, POST, PATCH and DELETE |
| `event-api-support.php` | Centralized validation, API error handling, JSON responses and CSRF checks |
| `LIVE_EVENT_STUDIO_SETUP.md` | Easy installation steps |
| `LIVE_EVENT_STUDIO_TEST_CASES.md` | Network tab tests, screenshot guide and expected HTTP status codes |

## Step-by-step XAMPP installation

1. Extract the ZIP to `C:\xampp\htdocs\StudentHub_MySQL_Practical_13\` (the folder itself must be directly inside `htdocs`).
2. Start Apache and MySQL using the XAMPP Control Panel.
3. **Do not create a new database and do not import/reset previous SQL files.** Practical 12 already prepared `studenthub_pr8_db`, with `events` and `event_change_audit` tables.
4. Keep the old `StudentHub_MySQL_Practical_12` folder in `htdocs`: uploaded posters from Practical 12 are referenced from that folder automatically if absent in the new one. Alternatively, copy its `event-posters/` image files into the new project’s `event-posters/` folder. Do not delete the old folder.
5. Open `http://localhost/StudentHub_MySQL_Practical_13/login.php` in Chrome.
6. Sign in with the **admin account** from Practical 10, not with a student account.
7. On Admin Dashboard, click **Lumiora Live Event Studio (AJAX CRUD)**.
8. Or open `http://localhost/StudentHub_MySQL_Practical_13/live-event-studio.php` while signed in as admin.
9. Click **Add New Event** to create a new record without reloading the page. Choose a unique test name (e.g. `Cloud Computing Bootcamp`), Category `Workshop`, Date `2026-12-22`, Venue `Computer Lab 1`, Status `Open`.
10. Click **Edit** to change event name/date/venue; click **Delete** to archive. Use Record State **Archived** to restore it.
11. Type in Search; after a 350ms pause the list refreshes automatically. Category, status, and pagination also use AJAX without full navigation.

## API documentation

Only an authenticated **admin** can use these endpoints. Calls use the same-origin session cookie. The CSRF token is supplied by the page through a meta tag; mutations include `X-CSRF-Token`.

| Operation | Method / endpoint | JSON body | Success |
| --- | --- | --- | --- |
| List/search | `GET event-api.php?search=&category=&status=all&archive=active&page=1` | None | `200` |
| Add event | `POST event-api.php` | `event_name`, `category`, `event_date`, `venue`, `description`, `event_status` | `201` |
| Edit event | `PATCH event-api.php` | Same event details plus `event_id` | `200` |
| Delete/archive | `DELETE event-api.php` | `{"event_id":10}` (use your own test ID) | `200` |
| Restore | `POST event-api.php` | `{"action":"restore","event_id":10}` | `200` |
| Open/Close | `POST event-api.php` | `{"action":"change_status","event_id":10,"event_status":"closed"}` | `200` |

Sample successful JSON:

```json
{
  "ok": true,
  "message": "Event added successfully without a page reload.",
  "event_id": 11
}
```

Sample error JSON:

```json
{
  "ok": false,
  "message": "Event name must contain 3–150 characters."
}
```

Possible errors: `400` malformed JSON/action, `401` login required or timeout, `403` student/invalid CSRF, `405` unsupported method, `409` missing/already archived event, `413` oversized API request, `415` non-JSON request, `422` invalid event data, `500` database error. Errors use a consistent JSON shape.

## Security and previous practicals

- Existing admin session and role checks protect both the PHP page and API; a student cannot modify events.
- MySQLi **prepared statements** for INSERT, SELECT filters, UPDATE and archive/restore.
- Token verification protects all POST/PATCH/DELETE calls from CSRF. Same-origin cookies are sent with Fetch.
- Input length, allowed categories/status, date, and venue are revalidated in PHP even if the browser validates first.
- Event deletion means **archive**, not permanent removal; `event_registrations` remain untouched. Changes are recorded in `event_change_audit`.
- Any event posters uploaded in Practical 12 are preserved and shown in the AJAX list. To upload or replace posters, open **Poster Management** (the separate existing module).
- Files are written for PHP **8.0+**, MySQLi and the XAMPP setup already used in Practical 12. No extra migration is needed.
- Keep the project local for testing; production use needs HTTPS and a hardened PHP/MySQL deployment.

If you see `401`, sign in again as admin. If you see a JSON `500` error, check MySQL is started and that `studenthub_pr8_db` contains `events`, `event_registrations`, and `event_change_audit`. If a newly created event seems missing, reset the Category, Status, or Record State filter.
