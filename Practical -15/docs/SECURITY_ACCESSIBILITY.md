# Security & accessibility review (evidence-based)

## Implemented in supplied source (verify on XAMPP)

- Sessions use HttpOnly cookies, `SameSite=Lax`, 15-minute inactivity expiry and session ID rotation at login.
- Session roles/account status are rechecked against MySQL on protected requests.
- Admin routes/JSON CRUD/POST updates use authorization; forms use CSRF tokens.
- Prepared MySQL statements used for user, student and event modifications.
- Password hashes verified with `password_verify()`; no plaintext stored in the schema.
- Poster uploads check MIME/size and restrict executable file types inside `event-posters/.htaccess`.
- PHP headers include `X-Content-Type-Options`, `X-Frame-Options` and `Referrer-Policy` for session-backed routes.
- Public portal uses semantic nav/main sections and focus-visible styles, with responsive breakpoints.

## Manual tests required (mark after observing)

- [ ] Admin login and student login, in independent browser sessions
- [ ] Logged-out user denied admin-only data
- [ ] Student direct admin URL yields HTTP 403
- [ ] Inactive test account denied login; reactivate afterward
- [ ] Invalid form values do not update DB; CSRF verification rejects invalid token
- [ ] File extension / MIME validation rejects a non-image poster; 2 MB size test only if actually performed
- [ ] Account/logout routes do not expose private data in cache
- [ ] Keyboard Tab can reach links, filters and all form fields; labels and visible focus
- [ ] Phone width 375px and 768px tablet: no inaccessible controls, no horizontal page clipping
- [ ] Charts have textual counts/labels, color is not sole status indication
- [ ] Refresh/recovery state of AJAX after Offline test (known prior behavior: stale error banner may persist until full page reload)
- [ ] Source files, .sql, .git and backups inaccessible via browser

## Limitations

Project is intended for localhost coursework, **not production hardened**. Old standalone HTML demonstrations are public and not backed by access-restricted student data. Bootstrap grid CDN means some optional layout styling needs internet; critical control center styling is local. Production would need enforced HTTPS, limited DB user, centralized rate-limiting, test coverage, secure deployments, trusted certificates, dependency review and secrets management. Legacy root/no-password XAMPP defaults are unsafe outside local learning.
