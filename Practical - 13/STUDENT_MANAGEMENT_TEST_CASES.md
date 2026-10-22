# Practical 11 — Student Management Test Report

**System:** StudentHub / Lumiora · **Database:** studenthub_pr8_db · **Module:** Admin-only MySQLi CRUD

Actual test statuses are **Not Tested** until you run the program in XAMPP. Mark each row Passed/Failed and attach your own screenshot.

| TC | Feature | Steps / Input | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|
| 01 | Read (SELECT) | Open management as admin | List with 20+ sample students; 8 rows per page | To be verified | Not Tested |
| 02 | Insert | Add unique enrollment & email | Student added; success message | To be verified | Not Tested |
| 03 | Update | Edit new student's name | Database updated; success message | To be verified | Not Tested |
| 04 | Duplicate validation | Insert existing email or enrollment | Clear error; no duplicate row | To be verified | Not Tested |
| 05 | Search by name | Search `Mira` | Only matching students listed | To be verified | Not Tested |
| 06 | Search by email | Search `@example.com` | Matching students listed | To be verified | Not Tested |
| 07 | Search by course | Search `Information Technology` | Matching records listed | To be verified | Not Tested |
| 08 | Course filter | Select `BCA`, click Search / Filter | Only BCA rows appear | To be verified | Not Tested |
| 09 | Pagination | Click Next and Previous | Moves across matching records | To be verified | Not Tested |
| 10 | Delete confirmation | Click Delete; cancel confirmation | Record stays active | To be verified | Not Tested |
| 11 | Soft delete | Confirm Delete on test row | Status becomes deleted; record archived | To be verified | Not Tested |
| 12 | Restore | Filter Archived, click Restore | Record active again | To be verified | Not Tested |
| 13 | CSV export | Filter and select Export CSV | Filtered rows downloaded as CSV | To be verified | Not Tested |
| 14 | Unauthorized access | Open management URL logged out | Redirected to login | To be verified | Not Tested |
| 15 | Student role denied | Student account opens admin management | 403 Forbidden | To be verified | Not Tested |
| 16 | CSRF security | Submit POST without valid token | Request rejected | To be verified | Not Tested |

**Result / Conclusion:** Complete after local screenshots. Do not mark test cases Passed without verifying them.
