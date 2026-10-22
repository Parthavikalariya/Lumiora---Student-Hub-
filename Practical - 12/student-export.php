<?php
/** CSV export of the same filtered student list (admin only). */
declare(strict_types=1);
require_once __DIR__ . '/student-records.php';
requireStudentAdmin();
[$where, $types, $values] = studentWhere(studentFilters());
function safeCsvCell(mixed $value): string {
    $cell = (string)$value;
    // Prevent a CSV import into spreadsheet software from interpreting cells as formulas.
    if (preg_match('/^[\s\x00-\x1F]*[=+\-@]/u', $cell)) return "'" . $cell;
    return $cell;
}
try {
    $db = getMySQLiConnection();
    $statement = studentQuery($db,
        'SELECT student_id, enrollment_no, full_name, email, course, semester, status FROM students'
        . $where . ' ORDER BY student_id DESC', $types, $values);
    $records = $statement->get_result();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="student_management_export.csv"');
    header('Cache-Control: private, no-store, max-age=0');
    $output = fopen('php://output', 'wb');
    if (!$output) throw new RuntimeException('Could not open CSV output.');
    fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM for common spreadsheet viewers.
    fputcsv($output, ['Student ID','Enrollment Number','Full Name','Email','Course','Semester','Status']);
    while ($row = $records->fetch_assoc()) {
        fputcsv($output, array_map('safeCsvCell', array_values($row)));
    }
    fclose($output);
    $statement->close();
    $db->close();
} catch (Throwable $exception) {
    error_log('CSV export failed: '.$exception->getMessage());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Could not export students. Verify the Practical 11 database setup.';
    }
}
