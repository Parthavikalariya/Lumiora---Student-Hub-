<?php
/** Admin-only, read-only integration readiness diagnostics. No database modifications. */
declare(strict_types=1);
require_once __DIR__.'/auth.php';
requireLogin('admin');
$files=['portal.php'=>'Integrated portal','login.php'=>'Secure Login','student-dashboard.php'=>'Student dashboard','admin-dashboard.php'=>'Admin control center','student-management.php'=>'Student CRUD','event-management.php'=>'Event CRUD','live-event-studio.php'=>'Fetch/AJAX CRUD','event-api.php'=>'JSON API','admin-audit-logs.php'=>'Audit log','admin_control_schema.sql'=>'P14 migration'];
$checks=[];
foreach($files as $file=>$label) $checks[]=['name'=>$label,'detail'=>$file,'passed'=>is_file(__DIR__.'/'.$file)];
$dbError=null;
$tableCounts=[]; $columns=[];
try {
    $db=getMySQLiConnection();
    $required=['students','events','event_registrations','user_accounts','audit_logs','event_change_audit','remember_login_tokens'];
    $tables=$db->query('SHOW TABLES')->fetch_all(MYSQLI_NUM);
    $available=array_column($tables,0);
    foreach($required as $name) {
        $present=in_array($name,$available,true);
        $checks[]=['name'=>'Database table: '.$name,'detail'=>$present?'Available':'Missing','passed'=>$present];
        if ($present) {
            $known=['students'=>'student_id','events'=>'event_id','event_registrations'=>'registration_id','user_accounts'=>'user_id','audit_logs'=>'log_id','event_change_audit'=>'audit_id','remember_login_tokens'=>'selector'];
            // Whitelisted table name only; never interpolate user input into SQL.
            $res=$db->query('SELECT COUNT('.$known[$name].') AS n FROM `'.$name.'`');
            $tableCounts[$name]=(int)$res->fetch_assoc()['n']; $res->free();
        }
    }
    foreach (['user_accounts'=>['role','is_active','password_hash'], 'events'=>['event_status','is_archived','poster_path'], 'students'=>['status']] as $table=>$expected) {
        if (!in_array($table,$available,true)) continue;
        $colRows=$db->query('SHOW COLUMNS FROM `'.$table.'`')->fetch_all(MYSQLI_ASSOC);
        $have=array_column($colRows,'Field');
        foreach($expected as $col) $checks[]=['name'=>'Column: '.$table.'.'.$col, 'detail'=>in_array($col,$have,true)?'Available':'Missing', 'passed'=>in_array($col,$have,true)];
    }
    $db->close();
} catch (Throwable $e) {
    error_log('Integration diagnostics: '.$e->getMessage());
    $dbError='MySQL unavailable or schema incomplete. Check XAMPP, credentials and the Practical 8–14 migrations.';
}
$passed=count(array_filter($checks,static fn($c)=>$c['passed']));
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Integration Center | Lumiora</title><link rel="stylesheet" href="integration-style.css"></head><body><div class="portal-shell">
<header class="portal-header"><a class="portal-brand" href="admin-dashboard.php"><span class="brand-tile">L</span><span>Lumiora<small>Admin · Integration Center</small></span></a><nav aria-label="Integration navigation"><a href="portal.php">Portal</a><a href="admin-dashboard.php">Dashboard</a><a href="live-event-studio.php">AJAX Studio</a><a href="admin-audit-logs.php">Audit Logs</a></nav></header>
<main><section class="portal-hero"><div><p class="eyebrow">PRACTICAL 15 · QUALITY GATE</p><h1>Integration readiness<span class="accent">.</span></h1><p>Read-only file, database-table and required-column checks, performed on this local installation. Results below do not replace functional browser testing.</p><p class="text-note"><?=e((string)$passed)?> / <?=count($checks)?> available checks passed. <?= $dbError?'Database connection needs attention.':'Database connection established.' ?></p></div><div class="hero-mark" aria-hidden="true"><span>✓</span><small>LOCAL TESTS</small></div></section>
<?php if($dbError): ?><p role="alert" class="check-row"><span><?=e($dbError)?></span></p><?php endif; ?>
<h2>Installed modules and schema</h2><div class="readiness">
<?php foreach ($checks as $check): ?><div class="check-row"><span><strong><?=e((string)$check['name'])?></strong><br><small><?=e((string)$check['detail'])?></small></span><span class="status <?=$check['passed']?'pass':'fail'?>"><?=$check['passed']?'PASS':'CHECK'?></span></div><?php endforeach; ?></div>
<?php if($tableCounts): ?><h2>Current MySQL records</h2><table class="portal-table"><thead><tr><th>Table</th><th>Rows</th></tr></thead><tbody><?php foreach ($tableCounts as $table=>$count): ?><tr><td><?=e((string)$table)?></td><td><?=number_format($count)?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?>
<h2>Manual end-to-end checks required</h2><p class="text-note">In separate browser sessions, verify student/admin RBAC, invalid login, student/event create-edit-archive-restore, poster upload validation, Fetch API requests, network error handling, mobile navigation and Git submission. Record evidence in <code>docs/TEST_REPORT.md</code>.</p>
<div class="qa-links"><a href="admin-dashboard.php">← Dashboard</a><a href="student-management.php">Students</a><a href="event-management.php">Events</a><a href="live-event-studio.php">AJAX Studio</a><a href="admin-audit-logs.php">Audit history</a></div></main><footer class="portal-footer">Checks are read-only and available only to authenticated administrators.</footer></div></body></html>
