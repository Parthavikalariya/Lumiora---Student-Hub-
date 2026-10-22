<?php
declare(strict_types=1);
require_once __DIR__ . '/mysqli-db.php';
function resultPage(string $heading, array $details, bool $ok = false): never {
    http_response_code($ok ? 200 : 422);
    $title = htmlspecialchars($heading, ENT_QUOTES, 'UTF-8');
    $class = $ok ? 'success' : 'error';
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>StudentHub Registration</title>';
    echo '<link rel="stylesheet" href="database-style.css"><style>body{padding:40px 16px;background:#0b1120;color:#e8efff;font-family:Arial,sans-serif}main{max-width:680px;margin:50px auto;background:#172033;padding:30px;border-radius:14px}li{margin:10px 0}a{color:#90d8f8}.success{color:#87e2b1}.error{color:#ffc4bc}</style></head><body><main>';
    echo '<h1 class="' . $class . '">' . $title . '</h1><ul>';
    foreach ($details as $detail) echo '<li>' . htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') . '</li>';
    echo '</ul><p><a href="register.html">Back to Registration</a> | <a href="login.html">Login Page</a></p></main></body></html>';
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    resultPage('Invalid Request', ['Submit the StudentHub registration form.']);
}
function field(string $key): string {
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}
$firstName = field('firstName'); $lastName = field('lastName');
$username = field('username'); $email = strtolower(field('email'));
$mobile = field('mobile'); $studentCode = field('studentId');
$course = field('course'); $semester = field('semester');
// Accept numeric select values and older form labels while retaining 1–8 validation.
if (preg_match('/^Semester\s+([1-8])$/i', $semester, $match)) {
    $semester = $match[1];
}
$password = $_POST['password'] ?? ''; $confirm = $_POST['confirmPassword'] ?? '';
$errors = [];
if (!preg_match('/^[A-Za-z ]{2,30}$/D', $firstName)) $errors[] = 'Enter a valid first name (2–30 letters).';
if (!preg_match('/^[A-Za-z ]{2,30}$/D', $lastName)) $errors[] = 'Enter a valid last name (2–30 letters).';
if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{2,29}$/D', $username)) $errors[] = 'Username must start with a letter and contain 3–30 letters, digits or underscores.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) $errors[] = 'Enter a valid email address.';
if (!preg_match('/^[6-9][0-9]{9}$/D', $mobile)) $errors[] = 'Enter a valid 10-digit mobile number.';
if ($studentCode === '' || strlen($studentCode) > 30) $errors[] = 'Student ID is required (maximum 30 characters).';
if ($course === '' || strlen($course) > 80) $errors[] = 'Choose a valid course.';
if (!ctype_digit($semester) || (int)$semester < 1 || (int)$semester > 8) $errors[] = 'Select semester 1 to 8.';
if (!is_string($password) || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/sD', $password) || strlen($password) > 72) $errors[] = 'Password requires 8–72 characters, uppercase, lowercase, number and special character (@$!%*?&).';
if (!is_string($confirm) || $password !== $confirm) $errors[] = 'Passwords do not match.';
if (!isset($_POST['terms'])) $errors[] = 'Accept Terms and Conditions.';
if ($errors) resultPage('Registration Failed', $errors);
try {
    $db = getMySQLiConnection();
    // Pre-check gives clear messages; UNIQUE indexes also protect against races.
    $check = $db->prepare('SELECT username, email FROM user_accounts WHERE username = ? OR email = ? LIMIT 2');
    $check->bind_param('ss', $username, $email);
    $check->execute();
    $rows = $check->get_result();
    $already = [];
    while ($row = $rows->fetch_assoc()) {
        if (strcasecmp($row['username'], $username) === 0) $already[] = 'Username is already registered.';
        if (strcasecmp($row['email'], $email) === 0) $already[] = 'Email is already registered.';
    }
    $check->close();
    if ($already) resultPage('Duplicate Account', array_unique($already));
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($hash === false) throw new RuntimeException('Password hashing failed');
    $db->begin_transaction();
    $insert = $db->prepare('INSERT INTO user_accounts (username,first_name,last_name,email,mobile,student_code,course,semester,password_hash) VALUES (?,?,?,?,?,?,?,?,?)');
    $sem = (int)$semester;
    $insert->bind_param('sssssssis', $username, $firstName, $lastName, $email, $mobile, $studentCode, $course, $sem, $hash);
    $insert->execute();
    $userId = $db->insert_id;
    $insert->close();
    $audit = $db->prepare('INSERT INTO registration_audit (user_id,action) VALUES (?, ?)');
    $action = 'registered';
    $audit->bind_param('is', $userId, $action);
    $audit->execute(); $audit->close();
    $db->commit();
    resultPage('Registration Successful', ['Your StudentHub account was stored securely in MySQL.', 'Username: ' . $username, 'Password was stored as a hash (not plaintext).'], true);
} catch (mysqli_sql_exception $e) {
    if (isset($db)) { try { $db->rollback(); } catch (Throwable $ignore) {} }
    if ((int)$e->getCode() === 1062) resultPage('Duplicate Account', ['This email or username is already registered.']);
    error_log('StudentHub register DB error: ' . $e->getMessage());
    resultPage('Registration Unavailable', ['Check XAMPP MySQL and import secure_registration_schema.sql.']);
} catch (Throwable $e) {
    if (isset($db)) { try { $db->rollback(); } catch (Throwable $ignore) {} }
    error_log('StudentHub registration error: ' . $e->getMessage());
    resultPage('Registration Unavailable', ['Please try again later.']);
}
