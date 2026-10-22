# Practical 8 — StudentHub MySQL Database (XAMPP)

## Setup (Windows)
1. Extract the entire `StudentHub_MySQL_Practical_8` folder into `C:\xampp\htdocs\`. Keep original Practical 7 pages in the same folder.
2. Open the XAMPP Control Panel. Start **Apache** and **MySQL**.
3. Open `http://localhost/phpmyadmin/` in your browser.
4. Select **Import**, choose `studenthub_schema.sql`, and click **Import/Go**. The SQL creates the `studenthub_pr8_db` database, 3 tables and sample records.
5. The included `db.php` uses XAMPP default settings: host `127.0.0.1`, database `studenthub_pr8_db`, username `root`, empty password. Change them if your XAMPP settings differ. Never deploy a root/blank-password connection online.
6. Visit `http://localhost/StudentHub_MySQL_Practical_8/connection-test.php` and verify the green success message.
7. Visit `http://localhost/StudentHub_MySQL_Practical_8/database-demo.php`. View students, events, registrations; choose a student and event and click **Register for Event**.
8. Check phpMyAdmin > `studenthub_pr8_db` > `event_registrations` > Browse to verify the new row.

## Screenshots for lab report
- Figure 1: phpMyAdmin database `studenthub_pr8_db` showing all three tables.
- Figure 2: `students` table **Structure** (PK, unique enrollment_no/email).
- Figure 3: `event_registrations` **Relation view** (two foreign keys); or use `ER_MODEL.md` diagram.
- Figure 4: `connection-test.php` with **Connection Successful** green message.
- Figure 5: `database-demo.php` with sample students, events and registrations.
- Figure 6: Successful event registration followed by updated record in phpMyAdmin.
- Figure 7 (optional): `CALL GetEventRegistrationCount(1);` output under phpMyAdmin > SQL.

## Submission files
- `studenthub_schema.sql` — importable schema plus seed records and optional procedure.
- `db.php` — PDO connection function.
- `connection-test.php` — screenshot of successful connection after running XAMPP on your PC.
- `ER_MODEL.md` — relationship diagram and normalization explanation.

## Post-lab viva quick answers
1. **Database:** A structured collection of related data.
2. **PK:** Unique identifier of each row (`student_id`, `event_id`, `registration_id`).
3. **FK:** References a PK in another table (`student_id` and `event_id` in registrations).
4. **3NF:** Non-key columns depend on the key, the whole key, and no other non-key column.
5. **PDO:** PHP Data Objects is a database-access interface supporting prepared statements.
6. **Prepared statement:** A SQL statement with placeholders; data is bound separately to reduce SQL injection risk.
7. **ER model:** Students and Events have a many-to-many relationship through Registrations.
8. **Connection error handling:** Catch PDOException and log details rather than displaying passwords or SQL internals.

## Scope of Practical 7 versus 8
The **existing `register.php` retains Practical 7 CSV registration** deliberately; it does not write to MySQL.
Practical 8 is a separate working MySQL database demonstration and event registration feature. This avoids breaking your previously completed CSV lab.
The database examples use fictional sample records; change seed data if your instructor provides specific test data.

## Note
A connection screenshot must be taken on your own running XAMPP server. A zip file alone cannot prove your local MySQL connection succeeded.
