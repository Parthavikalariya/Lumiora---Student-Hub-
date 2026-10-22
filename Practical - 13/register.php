<?php

// ONLY ACCEPT POST REQUEST

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

// GET FORM DATA

$firstName = $_POST["firstName"] ?? "";
$lastName = $_POST["lastName"] ?? "";
$email = $_POST["email"] ?? "";
$mobile = $_POST["mobile"] ?? "";
$student_id = $_POST["studentId"] ?? "";
$course = $_POST["course"] ?? "";
$semester = $_POST["semester"] ?? "";
$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirmPassword"] ?? "";
$terms = $_POST["terms"] ?? "";

$firstName = trim($firstName);
$lastName = trim($lastName);
$email = trim($email);
$mobile = trim($mobile);
$student_id = trim($student_id);
$course = trim($course);
$semester = trim($semester);
$name = trim($firstName . " " . $lastName);

$errors = [];

// First Name
if (empty($firstName)) {
    $errors[] = "First Name is required.";
}
elseif (!preg_match("/^[A-Za-z ]{2,30}$/", $firstName)) {
    $errors[] = "Enter a valid First Name.";
}

// Last Name
if (empty($lastName)) {
    $errors[] = "Last Name is required.";
}
elseif (!preg_match("/^[A-Za-z ]{2,30}$/", $lastName)) {
    $errors[] = "Enter a valid Last Name.";
}

// Email
if (empty($email)) {
    $errors[] = "Email is required.";
}
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Enter a valid Email address.";
}

// Mobile
if (empty($mobile)) {
    $errors[] = "Mobile Number is required.";
}
elseif (!preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
    $errors[] = "Enter a valid 10-digit Mobile Number.";
}

// Student ID
if (empty($student_id)) {
    $errors[] = "Student ID is required.";
}

// Course
if (empty($course)) {
    $errors[] = "Please select a Course.";
}

// Semester
if (empty($semester)) {
    $errors[] = "Please select a Semester.";
}

// Password
$passwordPattern =
    "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/";
if (empty($password)) {
    $errors[] = "Password is required.";
}
elseif (!preg_match($passwordPattern, $password)) {
    $errors[] =
        "Password must contain 8+ characters, uppercase, lowercase, number and special character.";
}

// Confirm Password
if (empty($confirm_password)) {
    $errors[] = "Confirm Password is required.";
}
elseif ($password !== $confirm_password) {
    $errors[] = "Password and Confirm Password do not match.";
}

// Terms
if (empty($terms)) {
    $errors[] = "Please accept the Terms and Conditions.";
}

if (!empty($errors)) {

    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Registration Failed</title>
        <link rel='stylesheet' href='style.css'>
    </head>

    <body>

    <div class='message-box'>

        <h2>❌ Registration Failed</h2>

        <ul>";

    foreach ($errors as $error) {
        echo "<li>" .
            htmlspecialchars($error, ENT_QUOTES, "UTF-8")
            . "</li>";
    }

    echo "</ul>

        <a href='register.html'>Go Back</a>

    </div>

    </body>
    </html>";

    exit;
}

$dataFolder = __DIR__ . DIRECTORY_SEPARATOR . "data";

if (!is_dir($dataFolder)) {

    if (!mkdir($dataFolder, 0777, true)) {
        die("Unable to create data folder.");
    }
}

$file = $dataFolder .
        DIRECTORY_SEPARATOR .
        "registrations.csv";

if (!file_exists($file) || filesize($file) == 0) {

    $handle = fopen($file, "w");

    if ($handle === false) {
        die("Unable to create registrations.csv.");
    }

    fputcsv($handle, [

        "ID",
        "First Name",
        "Last Name",
        "Name",
        "Email",
        "Mobile",
        "Student ID",
        "Course",
        "Semester",
        "Password",
        "Registered At"

    ]);

    fclose($handle);
}

$handle = fopen($file, "r");

if ($handle === false) {
    die("Unable to read registrations.csv.");
}

$lastId = 0;

while (($row = fgetcsv($handle)) !== false) {

    // Skip heading
    if (isset($row[0]) && $row[0] === "ID") {
        continue;
    }

    if (isset($row[0]) && is_numeric($row[0])) {

        if ((int)$row[0] > $lastId) {
            $lastId = (int)$row[0];
        }
    }
}

fclose($handle);

$id = $lastId + 1;

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);

$handle = fopen($file, "a");

if ($handle === false) {

    die(
        "Unable to open CSV file. " .
        "Close registrations.csv from Excel and try again."
    );
}

if (flock($handle, LOCK_EX)) {

    fputcsv($handle, [

        $id,
        $firstName,
        $lastName,
        $name,
        $email,
        $mobile,
        $student_id,
        $course,
        $semester,
        $hashedPassword,
        date("Y-m-d H:i:s")

    ]);

    fflush($handle);

    flock($handle, LOCK_UN);

}
else {

    fclose($handle);
    die("Unable to lock CSV file.");
}

fclose($handle);

echo "<!DOCTYPE html>

<html>

<head>

<meta charset='UTF-8'>
<title>Registration Successful</title>
<link rel='stylesheet' href='style.css'>

<style>

table {
    width: 80%;
    max-width: 700px;
    margin: 20px auto;
    border-collapse: collapse;
}

table, th, td {
    border: 1px solid #ccc;
}

th, td {
    padding: 10px;
    text-align: left;
}

th {
    background-color: #f2f2f2;
}

</style>

</head>

<body>

<div class='message-box success'>

<h2>✅ Registration Successful!</h2>

<p>Your registration has been saved.</p>

<table>

<tr>
    <th>ID</th>
    <td>" . htmlspecialchars((string)$id) . "</td>
</tr>

<tr>
    <th>First Name</th>
    <td>" . htmlspecialchars($firstName) . "</td>
</tr>

<tr>
    <th>Last Name</th>
    <td>" . htmlspecialchars($lastName) . "</td>
</tr>

<tr>
    <th>Email</th>
    <td>" . htmlspecialchars($email) . "</td>
</tr>

<tr>
    <th>Mobile</th>
    <td>" . htmlspecialchars($mobile) . "</td>
</tr>

<tr>
    <th>Student ID</th>
    <td>" . htmlspecialchars($student_id) . "</td>
</tr>

<tr>
    <th>Course</th>
    <td>" . htmlspecialchars($course) . "</td>
</tr>

<tr>
    <th>Semester</th>
    <td>" . htmlspecialchars($semester) . "</td>
</tr>

<tr>
    <th>Registered At</th>
    <td>" . date("Y-m-d H:i:s") . "</td>
</tr>

</table>

<a href='register.html'>Register Another Student</a>

<br><br>

<a href='login.html'>Go to Login</a>

</div>

</body>

</html>";

?>