<?php
/** Soft delete / restore; never DELETE physical student records. */
declare(strict_types=1);
require_once __DIR__ . '/student-records.php';
require_once __DIR__ . '/admin-audit-helper.php';
requireStudentAdmin();
if (!studentCsrfValid() || ($_POST['confirm_change'] ?? '') !== 'YES') {
    http_response_code(403);
    exit('Invalid or unconfirmed status change.');
}
$id = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$action = $_POST['action'] ?? '';
if (!$id || !in_array($action, ['archive','restore'], true)) {
    studentNotice('error', 'Invalid student or action.');
    redirectStudents();
}
$newStatus = $action === 'archive' ? 'deleted' : 'active';
$oldStatus = $action === 'archive' ? 'active' : 'deleted';
try {
    $db = getMySQLiConnection();
    $db->begin_transaction();
    $stmt = studentQuery($db, 'UPDATE students SET status=? WHERE student_id=? AND status=?', 'sis', [$newStatus,$id,$oldStatus]);
    $changed = $stmt->affected_rows;
    $stmt->close();
    if ($changed) writeAdminAudit($db, (int)$_SESSION['user_id'], 'student_' . ($action === 'archive' ? 'archived' : 'restored'), 'student', (int)$id, 'Student record ' . $newStatus . ' through Student Management');
    $db->commit();
    $db->close();
    studentNotice($changed ? 'success' : 'error', $changed ? ($action === 'archive' ? 'Student archived successfully; data was not permanently deleted.' : 'Student restored successfully.') : 'Student was not changed; verify its current status.');
} catch (Throwable $exception) {
    error_log('Student status change failed: '.$exception->getMessage());
    studentNotice('error', 'Database error: could not update the student status.');
}
redirectStudents();
