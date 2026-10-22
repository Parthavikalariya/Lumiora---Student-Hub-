<?php
/** Add student (INSERT) and edit student (UPDATE), with backend validation. */
declare(strict_types=1);
require_once __DIR__ . '/student-records.php';
requireStudentAdmin();
$idRaw = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['student_id'] ?? '0') : ($_GET['id'] ?? '0');
$id = filter_var($idRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$editing = $id > 0;
$student = ['enrollment_no' => '', 'full_name' => '', 'email' => '', 'course' => '', 'semester' => '3'];
$errors = [];
try {
    $db = getMySQLiConnection();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!studentCsrfValid()) {
            http_response_code(403);
            exit('Invalid request token. Return to the form and try again.');
        }
        [$student, $errors] = studentValidatePost();
        if (!$errors) {
            $sem = (int)$student['semester'];
            if ($editing) {
                $existsStmt = studentQuery($db, 'SELECT student_id FROM students WHERE student_id=? LIMIT 1', 'i', [$id]);
                $exists = (bool)$existsStmt->get_result()->fetch_assoc();
                $existsStmt->close();
                if (!$exists) {
                    $errors[] = 'Student no longer exists. Reload the student list.';
                } else {
                    $query = studentQuery($db, 'UPDATE students SET enrollment_no=?, full_name=?, email=?, course=?, semester=? WHERE student_id=?', 'ssssii', [$student['enrollment_no'],$student['full_name'],$student['email'],$student['course'],$sem,$id]);
                    $query->close();
                    studentNotice('success', 'Student updated successfully (ID ' . $id . ').');
                }
            } else {
                $query = studentQuery($db, 'INSERT INTO students (enrollment_no, full_name, email, course, semester) VALUES (?, ?, ?, ?, ?)', 'ssssi', [$student['enrollment_no'],$student['full_name'],$student['email'],$student['course'],$sem]);
                $query->close();
                studentNotice('success', 'Student added successfully (ID ' . $db->insert_id . ').');
            }
            if (!$errors) redirectStudents();
        }
    } elseif ($editing) {
        $stmt = studentQuery($db, 'SELECT enrollment_no, full_name, email, course, semester FROM students WHERE student_id=?', 'i', [$id]);
        $found = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$found) {
            http_response_code(404);
            exit('Student not found. <a href="student-management.php">Return to student management</a>');
        }
        $student = $found;
    }
    $db->close();
} catch (mysqli_sql_exception $exception) {
    error_log('Student form database error: ' . $exception->getMessage());
    if ((int)$exception->getCode() === 1062) {
        $errors[] = 'Enrollment number or email already exists. Enter unique values.';
    } else {
        $errors[] = 'Could not save the student. Check that MySQL and Practical 11 schema are ready.';
    }
} catch (Throwable $exception) {
    error_log('Student form error: ' . $exception->getMessage());
    $errors[] = 'An unexpected error occurred. Check the server setup.';
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=($editing?'Edit':'Add')?> Student | Lumiora</title><link rel="stylesheet" href="student-management-style.css"></head><body><main class="shell">
<a href="student-management.php">&larr; Back to Student Management</a>
<section class="hero"><h1><?=($editing?'Edit Student':'Add New Student')?></h1><p><?=($editing?'Update an existing database record using MySQLi UPDATE.':'Create a new student record using MySQLi INSERT.')?></p></section>
<section class="panel" style="max-width:760px">
<?php if ($errors): ?><div class="error-list" role="alert"><b>Please correct these errors:</b><ul><?php foreach($errors as $error): ?><li><?=studentEscape($error)?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form action="student-form.php" method="post">
<input type="hidden" name="csrf" value="<?=studentEscape($_SESSION['csrf'])?>">
<input type="hidden" name="student_id" value="<?=$id?>">
<div class="form-grid">
<div class="field"><label for="enrollment_no">Enrollment Number *</label><input id="enrollment_no" name="enrollment_no" required maxlength="20" pattern="[A-Za-z0-9_-]{3,20}" value="<?=studentEscape($student['enrollment_no'])?>" placeholder="e.g. PR11CE021"></div>
<div class="field"><label for="full_name">Full Name *</label><input id="full_name" name="full_name" required maxlength="100" minlength="2" value="<?=studentEscape($student['full_name'])?>" placeholder="e.g. Dev Patel"></div>
<div class="field"><label for="email">Email Address *</label><input id="email" type="email" name="email" required maxlength="150" value="<?=studentEscape($student['email'])?>" placeholder="student@example.com"></div>
<div class="field"><label for="course">Course *</label><input id="course" name="course" list="course-options" required maxlength="80" value="<?=studentEscape($student['course'])?>" placeholder="B.Tech Computer Engineering"><datalist id="course-options"><option value="B.Tech Computer Engineering"><option value="B.Tech Information Technology"><option value="B.Tech Computer Science"><option value="BCA"><option value="BBA"></datalist></div>
<div class="field"><label for="semester">Semester *</label><select name="semester" id="semester" required><?php for($n=1;$n<=8;$n++): ?><option value="<?=$n?>" <?=(int)$student['semester']===$n?'selected':''?>>Semester <?=$n?></option><?php endfor; ?></select></div>
</div>
<p class="help">All fields marked * are validated in the browser and again on the PHP server. Duplicate email or enrollment numbers are rejected by MySQL UNIQUE constraints.</p>
<div class="form-actions"><button type="submit"><?=($editing?'Save Changes':'Add Student')?></button><a class="btn secondary" href="student-management.php">Cancel</a></div>
</form></section></main></body></html>
