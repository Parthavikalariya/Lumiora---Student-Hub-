# 3–5 minute project demo outline (simple English)

**1. Introduction (20 seconds):** This is Lumiora StudentHub, our integrated PHP and MySQL student portal. It combines secure login, students, events, AJAX CRUD and an admin dashboard.

**2. Portal (30 seconds):** Open the home page. Show the module cards and links. Explain that guests, students and admins see different entry paths.

**3. Admin login (40 seconds):** Sign in securely. Show live user/student/event statistics, latest registrations and the monthly registrations chart. Mention that values come from MySQL.

**4. CRUD + AJAX (70 seconds):** Show student management and event management. In Live Event Studio, demonstrate Fetch GET; if appropriate, create and edit one test event without reloading the page. Open Network tab to show JSON.

**5. RBAC + audit (50 seconds):** Show student-only menu in a separate session and that a student cannot open admin-dashboard.php (403). Show an admin action recorded in `audit_logs`.

**6. Deployment and submission (30 seconds):** Explain XAMPP localhost deployment and optional `lumiora.local` virtual host. Show `README.md`, private SQL backup existence and your actual GitHub repository page if pushed.

**End (10 seconds):** We verified the integration against the test checklist. Thank you.

Never show real passwords, cookies or tokens in the recording.
