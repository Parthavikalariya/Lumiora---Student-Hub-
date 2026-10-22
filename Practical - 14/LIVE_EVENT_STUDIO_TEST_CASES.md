# Live Event Studio — Practical 13 Test Cases & Screenshots

**Testing environment:** Chrome, XAMPP Apache/MySQL, PHP 8.0+, `studenthub_pr8_db`. Take screenshots as you finish each case; make sure the whole relevant screen is visible.

| Figure | Test/action | Expected outcome |
| --- | --- | --- |
| 1 | Admin Dashboard with `Lumiora Live Event Studio` link | Admin authenticated and link available |
| 2 | Live Event Studio opens | Events, counts and action controls load from GET JSON |
| 3 | DevTools → Network → Fetch/XHR → select `event-api.php` GET | Status **200**, Preview/Response shows valid JSON with `ok`, `data`, `pagination`, `counts` |
| 4 | Click **Add New Event** | Modal form appears without leaving the page |
| 5 | Submit unique valid event | Success message; list updates, HTTP **201 POST** |
| 6 | Edit newly added event | New values shown; HTTP **200 PATCH** |
| 7 | Type part of event name in search (wait ~350ms) | Live search results, JSON **GET** calls; full page does not reload |
| 8 | Filter category and status | List shows matching results only (GET) |
| 9 | Go to page 2 using Next | Page 2 loaded via JSON GET, without full page navigation |
| 10 | Delete/Archive the new event, confirm | HTTP **200 DELETE**, success message, active count decreases |
| 11 | Record State → Archived | Archived record visible and Restore button available |
| 12 | Restore event | HTTP **200 POST**; record returns to active list |
| 13 | Close/Open your test event | HTTP **200 POST** and status badge changes |
| 14 | Input validation error (see below) | JSON error with **422**, no event inserted |
| 15 | phpMyAdmin `events` and `event_change_audit` tables | Event changes and audit log stored |
| 16 | phpMyAdmin export | Save `events.sql` and `event_change_audit.sql` for submission |

## Check the Network tab easily

1. Open the Live Event Studio webpage in Chrome.
2. Press `F12`, open **Network**, then select **Fetch/XHR**. Keep DevTools visible beside the page.
3. Click Refresh List, Add New Event, Edit, Delete, Restore. For each operation, click the `event-api.php` entry and review **Headers → Request Method / Status Code** and **Response → JSON**.
4. For **no page reload**, note that the main document is never reloaded during these actions; only Fetch/XHR calls appear.
5. Take one complete screenshot per operation, or a screenshot that clearly includes its status and response.

## Valid test data (use a new test record)

- Event Name: `Cloud Computing Bootcamp`
- Category: `Workshop`
- Event Date: `2026-12-22`
- Venue: `Computer Lab 1`
- Description: `Hands-on cloud basics using secure REST APIs.`
- Registration Status: `Open`

## Show a server-side validation error (without modifying any data)

When logged in as admin on the Live Event Studio page, open DevTools → Console and run the following **test request**. It intentionally sends invalid fields. It does not create a record.

```javascript
fetch('event-api.php', {
  method: 'POST',
  credentials: 'same-origin',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
  },
  body: JSON.stringify({
    event_name: 'A', category: 'Workshop', event_date: '2026-12-22',
    venue: 'X', description: '', event_status: 'open'
  })
}).then(async response => console.log(response.status, await response.json()));
```

Expected HTTP status is `422`, with a JSON error about invalid event name/venue. Take a screenshot of **Network → event-api.php → Response**, or Console result. Do **not** run a command that changes the live database directly in DevTools.

## Submission contents

- Updated source files from the project ZIP (`event-api.php`, `event-api-support.php`, `live-event-studio.php`, `.js`, `.css`).
- Actual application screenshots and API network response screenshots.
- Event database and audit SQL dumps exported from phpMyAdmin.
- Testing observations and a short conclusion.

Previous Practical 12 poster upload remains accessible through Poster Management. This practical specifically demonstrates JSON + Fetch async CRUD, not asynchronous poster uploads.
