# Practical 15 – individual end-to-end integration test plan

Fill actual results in `docs/TEST_REPORT.md`. Do not mark Passed until screenshots/results exist.

| ID | Test case / steps | Expected | Evidence filename |
|---|---|---|---|
| T01 | Extract to `htdocs`; start Apache/MySQL; open `/StudentHub_MySQL_Practical_15/` | Integrated portal loads | `01-integrated-portal.png` |
| T02 | Login as existing admin | Admin Control Center, role-only menu | `02-admin-login.png` |
| T03 | Open Integration Center as admin | Required files & DB tables PASS | `03-integration-center.png` |
| T04 | Compare KPI counts to phpMyAdmin tables | Dynamic SQL aggregates match | `04-kpi-db.png` |
| T05 | Open student, event and AJAX modules from admin navigation | All links load, no 404 | `05-navigation.png` |
| T06 | Student account login in separate Incognito profile | Student menu with no admin links | `06-student-role.png` |
| T07 | Student requests `admin-dashboard.php` | HTTP 403 admin access only | `07-rbac-403.png` |
| T08 | Student role edits their own profile (optional) + admin add/edit/archive/restore TEST student | DB update and status responses | `08-student-crud.png` |
| T09 | Add/edit/archive/restore a TEST event | Changes visible in event list, no accidental data loss | `09-event-crud.png` |
| T10 | Upload valid test poster, reject invalid file type | Image preview and error message | `10-poster-validation.png` |
| T11 | In Network > Fetch/XHR: GET/POST/PATCH/DELETE on TEST event | JSON, appropriate 200/201, no full reload | `11-ajax-network.png` |
| T12 | Live search with Cloud or another existing keyword | Fetch GET and filtered results | `12-live-search.png` |
| T13 | DevTools Network Offline > Refresh List > No throttling | Error displayed then live recovered; record any stale alert issue | `13-offline-recovery.png` |
| T14 | Admin account deactivates/reactivates TEST student | Audit entries and `is_active` reflect actions | `14-user-status.png` |
| T15 | Open `admin-audit-logs.php` | Login/action history recorded | `15-admin-audit.png` |
| T16 | Use 375px phone viewport and keyboard Tab | Responsive layout, labels, reachable actions | `16-mobile-a11y.png` |
| T17 | Configure optional `lumiora.local` Apache VirtualHost | Local domain opens site, admin sign-in works | `17-vhost.png` |
| T18 | Export full database privately; check backup exists | Valid local .sql file, not public GitHub | `18-sql-export.png` |
| T19 | `git init`, add, commit, push to a real GitHub repository | `git status` clean and repo URL accessible | `19-github.png` |

**Test data note:** Use a clearly named disposable test event/student for CRUD; preserve the 11 existing events and 24 student records. Restore deactivated accounts. Optional size-limit poster test must not be listed as Passed if skipped.
