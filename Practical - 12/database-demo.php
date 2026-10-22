<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/db.php';
function h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
if (empty($_SESSION['db_csrf'])) { $_SESSION['db_csrf'] = bin2hex(random_bytes(32)); }
$students=[]; $events=[]; $registrations=[]; $error=null;
try {
    $pdo=getDatabaseConnection();
    $students=$pdo->query('SELECT student_id,enrollment_no,full_name,course,semester FROM students ORDER BY student_id')->fetchAll();
    $events=$pdo->query('SELECT e.event_id,e.event_name,e.category,e.event_date,e.venue,COUNT(r.registration_id) AS total_registrations FROM events e LEFT JOIN event_registrations r ON e.event_id=r.event_id GROUP BY e.event_id,e.event_name,e.category,e.event_date,e.venue ORDER BY e.event_date')->fetchAll();
    $registrations=$pdo->query('SELECT s.enrollment_no,s.full_name,e.event_name,r.registered_at FROM event_registrations r JOIN students s ON s.student_id=r.student_id JOIN events e ON e.event_id=r.event_id ORDER BY r.registered_at DESC,r.registration_id DESC LIMIT 30')->fetchAll();
} catch (PDOException $e) {
    error_log('StudentHub database demo: '.$e->getMessage());
    $error='Could not load MySQL data. Start Apache and MySQL in XAMPP, import studenthub_schema.sql, and check db.php.';
}
$flash=$_SESSION['db_flash']??null; unset($_SESSION['db_flash']);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>StudentHub Database | Lumiora</title><link rel="stylesheet" href="database-style.css"></head><body>
<main class="db-shell"><nav class="db-nav"><a href="dashboard.html">← Dashboard</a><a href="event.html">Events</a><a href="connection-test.php">Test Connection</a></nav>
<header class="db-hero"><span class="eyebrow">LUMIORA · PRACTICAL 8</span><h1>StudentHub MySQL Database</h1><p>Normalized database design, PHP PDO, prepared statements and event registration.</p></header>
<?php if ($error): ?><div class="status error"><strong>Database setup required</strong><p><?= h($error) ?></p><a href="connection-test.php">Run Connection Test →</a></div><?php else: ?>
<?php if ($flash): ?><div class="status <?= $flash['success'] ? 'ok' : 'error' ?>"><?= h($flash['message']) ?></div><?php endif; ?>
<div class="db-stats"><div><strong><?= count($students) ?></strong><span>Students</span></div><div><strong><?= count($events) ?></strong><span>Events</span></div><div><strong><?= count($registrations) ?></strong><span>Recent Registrations</span></div></div>
<section class="db-panel"><h2>Register for an event</h2><p>This submits through a validated PDO prepared INSERT query. One student can register for an event only once.</p><form action="student-event-register.php" method="post" class="db-form"><input type="hidden" name="csrf" value="<?= h($_SESSION['db_csrf']) ?>"><label>Student enrollment number<select name="student_id" required><option value="">Choose a student</option><?php foreach ($students as $s): ?><option value="<?= (int)$s['student_id'] ?>"><?= h($s['enrollment_no'].' · '.$s['full_name']) ?></option><?php endforeach; ?></select></label><label>Event<select name="event_id" required><option value="">Choose an event</option><?php foreach ($events as $e): ?><option value="<?= (int)$e['event_id'] ?>"><?= h($e['event_name'].' · '.$e['event_date']) ?></option><?php endforeach; ?></select></label><button class="db-button" type="submit">Register for Event</button></form></section>
<section class="db-panel"><h2>Students</h2><div class="table-scroll"><table><thead><tr><th>ID</th><th>Enrollment</th><th>Name</th><th>Course</th><th>Sem</th></tr></thead><tbody><?php foreach ($students as $s): ?><tr><td><?= (int)$s['student_id'] ?></td><td><?= h($s['enrollment_no']) ?></td><td><?= h($s['full_name']) ?></td><td><?= h($s['course']) ?></td><td><?= (int)$s['semester'] ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<section class="db-panel"><h2>Upcoming events</h2><div class="table-scroll"><table><thead><tr><th>Event</th><th>Category</th><th>Date</th><th>Venue</th><th>Registered</th></tr></thead><tbody><?php foreach ($events as $e): ?><tr><td><?= h($e['event_name']) ?></td><td><?= h($e['category']) ?></td><td><?= h($e['event_date']) ?></td><td><?= h($e['venue']) ?></td><td><?= (int)$e['total_registrations'] ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<section class="db-panel"><h2>Student event registrations (JOIN)</h2><div class="table-scroll"><table><thead><tr><th>Student</th><th>Event</th><th>Registered at</th></tr></thead><tbody><?php foreach ($registrations as $x): ?><tr><td><?= h($x['full_name'].' ('.$x['enrollment_no'].')') ?></td><td><?= h($x['event_name']) ?></td><td><?= h($x['registered_at']) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php endif; ?><footer>StudentHub · MySQL Schema Design · CO1 / CO5</footer></main></body></html>
