<?php
/** Shared helpers for Practical 11 admin-only student management. */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

/** Call before doing anything else in a student-management route. */
function requireStudentAdmin(): void {
    requireLogin('admin');
}

function studentCsrfValid(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST'
        && is_string($_POST['csrf'] ?? null)
        && hash_equals((string)($_SESSION['csrf'] ?? ''), $_POST['csrf']);
}

function studentEscape(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function studentNotice(string $type, string $message): void {
    $_SESSION['student_notice'] = ['type' => $type === 'success' ? 'success' : 'error', 'message' => $message];
}

function takeStudentNotice(): ?array {
    $notice = $_SESSION['student_notice'] ?? null;
    unset($_SESSION['student_notice']);
    return is_array($notice) ? $notice : null;
}

function redirectStudents(): never {
    header('Location: student-management.php', true, 303);
    exit;
}

/** Same validated search controls used by UI, export and pagination. */
function studentFilters(): array {
    $search = $_GET['search'] ?? '';
    $course = $_GET['course'] ?? '';
    $status = $_GET['status'] ?? 'active';
    $search = is_string($search) ? substr(trim($search), 0, 100) : '';
    $course = is_string($course) ? substr(trim($course), 0, 80) : '';
    $status = is_string($status) && in_array($status, ['active','deleted','all'], true) ? $status : 'active';
    return [$search, $course, $status];
}

/** All user-controlled values are passed via MySQLi bound parameters. */
function studentWhere(array $filters): array {
    [$search, $course, $status] = $filters;
    $clauses = [];
    $params = [];
    $types = '';
    if ($status !== 'all') {
        $clauses[] = 'status = ?';
        $params[] = $status;
        $types .= 's';
    }
    if ($search !== '') {
        $clauses[] = '(full_name LIKE ? OR email LIKE ? OR course LIKE ? OR enrollment_no LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like, $like);
        $types .= 'ssss';
    }
    if ($course !== '') {
        $clauses[] = 'course = ?';
        $params[] = $course;
        $types .= 's';
    }
    $where = $clauses ? ' WHERE ' . implode(' AND ', $clauses) : '';
    return [$where, $types, $params];
}

/** Performs the query through MySQLi prepared statements, including reads. */
function studentQuery(mysqli $db, string $sql, string $types = '', array $params = []): mysqli_stmt {
    $stmt = $db->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

/** Every displayed data field has a corresponding server-side validation. */
function studentValidatePost(): array {
    $get = static function(string $name): string {
        $value = $_POST[$name] ?? '';
        return is_string($value) ? trim($value) : '';
    };
    $data = [
        'enrollment_no' => strtoupper($get('enrollment_no')),
        'full_name' => $get('full_name'),
        'email' => strtolower($get('email')),
        'course' => $get('course'),
        'semester' => $get('semester'),
    ];
    $errors = [];
    if (!preg_match('/^[A-Z0-9_-]{3,20}$/D', $data['enrollment_no'])) $errors[] = 'Enrollment must contain 3-20 letters, numbers, _ or -.';
    if (!preg_match("/^[\\p{L}][\\p{L} .'-]{1,99}$/uD", $data['full_name'])) $errors[] = 'Enter a valid full name (2-100 characters).';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 150) $errors[] = 'Enter a valid email address.';
    if ($data['course'] === '' || strlen($data['course']) > 80) $errors[] = 'Enter a course name (maximum 80 characters).';
    if (!preg_match('/^[1-8]$/D', $data['semester'])) $errors[] = 'Choose a semester from 1 to 8.';
    return [$data, $errors];
}
