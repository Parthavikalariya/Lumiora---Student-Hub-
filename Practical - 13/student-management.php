<?php
/** Admin-only list, SELECT/search/filter and paginated results (Practical 11). */
declare(strict_types=1);
require_once __DIR__.'/student-records.php';
requireStudentAdmin();
[$search, $course, $status] = studentFilters();
[$where, $types, $values] = studentWhere([$search, $course, $status]);
$rawPage = $_GET['page'] ?? '1';
$page = is_string($rawPage) && ctype_digit($rawPage) ? max(1, min(100000, (int)$rawPage)) : 1;
$perPage = 8;
$error = null;
$students = [];
$courses = [];
$total = 0;
$totalPages = 1;
try {
    $db = getMySQLiConnection();
    $coursesResult = studentQuery($db, 'SELECT DISTINCT course FROM students ORDER BY course');
    $courses = array_column($coursesResult->get_result()->fetch_all(MYSQLI_ASSOC), 'course');
    $coursesResult->close();

    $countStmt = studentQuery($db, 'SELECT COUNT(*) AS total FROM students' . $where, $types, $values);
    $total = (int)$countStmt->get_result()->fetch_assoc()['total'];
    $countStmt->close();
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;
    $listStmt = studentQuery($db,
        'SELECT student_id, enrollment_no, full_name, email, course, semester, status FROM students'
        . $where . ' ORDER BY student_id DESC LIMIT ? OFFSET ?',
        $types . 'ii', array_merge($values, [$perPage, $offset]));
    $students = $listStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $listStmt->close();
    $db->close();
} catch (Throwable $exception) {
    error_log('Student listing failed: ' . $exception->getMessage());
    $error = 'Unable to load students. Import student_management_schema.sql and ensure MySQL is running.';
}
$notice = takeStudentNotice();
function filterLink(int $pageNumber): string {
    $params = ['search' => $_GET['search'] ?? '', 'course' => $_GET['course'] ?? '', 'status' => $_GET['status'] ?? 'active', 'page' => $pageNumber];
    foreach ($params as &$param) if (!is_string($param) && !is_int($param)) $param = '';
    unset($param);
    return 'student-management.php?' . http_build_query($params);
}
$exportUrl = 'student-export.php?' . http_build_query(['search'=>$search,'course'=>$course,'status'=>$status]);
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Management | Lumiora</title><link rel="stylesheet" href="student-management-style.css"></head>
<body><main class="shell">
<div class="toolbar"><a href="admin-dashboard.php">&larr; Admin Dashboard</a><div class="toolbar-links">
<a class="btn" href="student-form.php">+ Add Student</a><a class="btn secondary" href="<?=studentEscape($exportUrl)?>">Export CSV</a>
</div></div>
<section class="hero"><h1>Student Management</h1><p>Practical 11 &bull; Secure MySQLi CRUD &bull; Search, filter, pagination &amp; soft delete</p></section>
<?php if ($notice): ?><div class="notice <?=studentEscape($notice['type'])?>" role="status"><?=studentEscape($notice['message'])?></div><?php endif; ?>
<?php if ($error): ?><div class="notice error" role="alert"><?=studentEscape($error)?></div><?php endif; ?>
<section class="panel"><form method="get" action="student-management.php" class="filters">
<div class="field wide"><label for="search">Search name, email, course or enrollment</label><input type="search" id="search" name="search" maxlength="100" placeholder="e.g. Nisha / @example.com / B.Tech" value="<?=studentEscape($search)?>"></div>
<div class="field"><label for="course">Filter by course</label><select id="course" name="course"><option value="">All courses</option><?php foreach ($courses as $choice): ?><option value="<?=studentEscape($choice)?>" <?=($choice===$course?'selected':'')?>><?=studentEscape($choice)?></option><?php endforeach; ?></select></div>
<div class="field"><label for="status">Record status</label><select id="status" name="status"><option value="active" <?=$status==='active'?'selected':''?>>Active</option><option value="deleted" <?=$status==='deleted'?'selected':''?>>Archived</option><option value="all" <?=$status==='all'?'selected':''?>>All records</option></select></div>
<div class="filter-buttons"><button type="submit">Search / Filter</button><a href="student-management.php" class="btn secondary">Reset</a></div>
</form></section>
<section class="panel"><div class="toolbar"><h2>Student Records</h2><span class="summary"><?=studentEscape($total)?> matching record(s) &middot; Page <?=studentEscape($page)?> of <?=studentEscape($totalPages)?></span></div>
<div class="table-scroller"><table><thead><tr><th>ID</th><th>Enrollment</th><th>Full Name</th><th>Email</th><th>Course</th><th>Sem</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($students as $student): ?>
<tr><td><?=studentEscape($student['student_id'])?></td><td><?=studentEscape($student['enrollment_no'])?></td><td><?=studentEscape($student['full_name'])?></td><td><?=studentEscape($student['email'])?></td><td><?=studentEscape($student['course'])?></td><td><?=studentEscape($student['semester'])?></td>
<td><span class="pill <?=$student['status']==='deleted'?'archived':''?>"><?=studentEscape($student['status'])?></span></td>
<td><div class="actions"><a class="btn small secondary" href="student-form.php?id=<?=(int)$student['student_id']?>">Edit</a>
<form method="post" action="student-status.php" onsubmit="return confirm('<?=($student['status']==='active'?'Archive this student? The record will be kept in MySQL.':'Restore this student account?')?>');">
<input type="hidden" name="csrf" value="<?=studentEscape($_SESSION['csrf'])?>">
<input type="hidden" name="student_id" value="<?=(int)$student['student_id']?>">
<input type="hidden" name="action" value="<?=$student['status']==='active'?'archive':'restore'?>">
<button class="small <?=$student['status']==='active'?'danger':'secondary'?>" type="submit" name="confirm_change" value="YES"><?=$student['status']==='active'?'Delete':'Restore'?></button>
</form></div></td></tr>
<?php endforeach; ?>
<?php if (!$students): ?><tr><td colspan="8" class="empty">No matching students found. Try another search/filter.</td></tr><?php endif; ?>
</tbody></table></div>
<?php if ($totalPages > 1): ?><nav class="pages" aria-label="Student list pages">
<?php if ($page>1): ?><a href="<?=studentEscape(filterLink($page-1))?>">&larr; Previous</a><?php endif; ?>
<?php for($p=max(1,$page-2);$p<=min($totalPages,$page+2);$p++): ?>
<?php if($p===$page): ?><span class="current" aria-current="page"><?=$p?></span><?php else: ?><a href="<?=studentEscape(filterLink($p))?>"><?=$p?></a><?php endif; ?>
<?php endfor; ?>
<?php if ($page<$totalPages): ?><a href="<?=studentEscape(filterLink($page+1))?>">Next &rarr;</a><?php endif; ?>
</nav><?php endif; ?>
<p class="footnote">Delete uses soft delete: the row remains in MySQL, preserving event registration references. Restore from the Archived filter. Each update uses prepared statements and a CSRF token.</p>
</section></main></body></html>
