# Practical 11 — Student Management CRUD with Search and Filter

**Project:** StudentHub / Lumiora (continued directly from Practical 10)

## Step-by-step setup in XAMPP

1. Download and extract the new ZIP. Copy the whole folder `StudentHub_MySQL_Practical_11` into `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** in XAMPP.
3. Open `http://localhost/phpmyadmin/`. Select the **existing** database `studenthub_pr8_db` on the left, then **Import** `student_management_schema.sql` from the new project folder. Click **Go**.
4. SQL adds a `status` column to `students` and **20 new fictional student rows**. **It does not delete or overwrite Practical 8, 9, or 10 records.** With your original 3 records, there should be at least 23 students. Re-import does not duplicate rows.
5. Open `http://localhost/StudentHub_MySQL_Practical_11/login.php`. Sign in with the **admin account you created for Practical 10** (`admin@example.test` unless you used another address). You don't need to create a second admin.
6. In the Admin Dashboard, click **Manage Students (Practical 11)**, or open `http://localhost/StudentHub_MySQL_Practical_11/student-management.php` directly *while logged in as admin*.
7. A **student** account must not access the management page; it will receive a 403 response.

## What to test (one action at a time)

- **Read:** The table lists records; use **Next** and **Previous** to test pagination (8 rows per page).
- **Search:** Search a student's name, email, or course (e.g. `B.Tech`). Check matching rows.
- **Filter:** Choose a course and click **Search / Filter**; combine filters if desired. Test Reset.
- **Insert:** **Add Student** → enter test values such as `PR11CE021`, `New Student`, `newstudent21@example.com`, `B.Tech Computer Engineering`, semester `3` → **Add Student**. A green success message appears.
- **Update:** Click **Edit** on the newly created test row → change the name → **Save Changes**. Verify the updated row.
- **Duplicate:** Try adding a different enrollment with the **same email**; expect a clear error. No duplicate should be added.
- **Delete:** Click **Delete** → confirm the browser prompt. The student is **archived**, not physically deleted, and disappears from Active filter.
- **Restore:** Switch **Record status** to **Archived**, find the test student, click **Restore** → confirm. It reappears in Active.
- **CSV export:** Apply a filter and click **Export CSV**. File `student_management_export.csv` downloads with matching student rows.
- **Admin security:** Logout, then open student-management URL. It should redirect to login. With student login, it must return **403 Forbidden**.

## Required screenshot names for Practical 11 report

1. **Figure 1 — MySQL Students Table (20+ Records)**: phpMyAdmin studenthub_pr8_db → students → Browse and/or Structure.
2. **Figure 2 — Student Management List (READ & Pagination)**: `student-management.php` with records and page links visible.
3. **Figure 3 — New Student Added (INSERT)**: success message and newly added record.
4. **Figure 4 — Student Updated (UPDATE)**: success message and changed name.
5. **Figure 5 — Search / Filter (SELECT WHERE)**: a search term and matching results.
6. **Figure 6 — Delete Confirmation & Archived Record**: confirm prompt screenshot (optional) and archived record after delete.
7. **Figure 7 — CSV Export (Intermediate)**: downloaded CSV result, if required.
8. **Figure 8 — Access Control**: unauthorized/students can't access admin management page.

Screenshots should show **complete output** without cropping the important UI. Use *test data* only and mask any sensitive real user details.

## SQL dump for submission

phpMyAdmin → select `studenthub_pr8_db` → **Export** → **Quick** → **SQL** → **Export**. Name it `studenthub_student_management_dump.sql`. This contains the full project database; remove real personal data before sharing with others.

## Final practical report requirements

Match **Practical 10 FULL_PHOTOS** format: same cover, font sizes, header/footer, and syntax colors (blue keywords, red strings, green comments); include **only necessary new PHP/SQL code**, complete screenshots, result/test cases, and conclusion. **No Key Questions section.** Build the final PDF only after the screenshots from your running local XAMPP are supplied.

## Security and preservation notes

- All SQL operations (SELECT, INSERT, UPDATE, soft-DELETE and CSV export) use MySQLi, with bound values in prepared statements where filters or input values are involved.
- All modification requests are POST-only and require CSRF protection; each Delete/Restore action also requires browser confirmation.
- Admin-role check happens **server-side**, before CRUD page content or CSV data is returned.
- `status = 'deleted'` enables reversible **soft delete**, so existing `event_registrations` foreign keys are preserved.
- Practical 11 PHP session and remember-me cookies have a separate name/path from Practical 10, so both projects can be used simultaneously on localhost.
- Do **not** import a new `studenthub_schema.sql` from an earlier practical; import only `student_management_schema.sql` for Practical 11.
- The XAMPP default root/no-password database credentials are suitable for a *local lab only*, not production.
