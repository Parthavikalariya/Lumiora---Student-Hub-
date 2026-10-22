<?php
// Run only in a terminal (not browser): php create-admin-cli.php admin@example.test
// Password is entered privately at prompt on supported terminals; do not commit real credentials.
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
require_once __DIR__.'/mysqli-db.php';
$email=$argv[1]??'admin@example.test';
if (!filter_var($email,FILTER_VALIDATE_EMAIL)) exit("Invalid admin email\n");
echo "Enter a NEW admin password (input visible; run locally): "; $password=trim((string)fgets(STDIN));
if (strlen($password)<12) exit("Password must be at least 12 characters\n");
$db=getMySQLiConnection(); $name='admin_'.bin2hex(random_bytes(3)); $first='Site'; $last='Admin'; $mobile='9999999999'; $student='ADMIN'.strtoupper(bin2hex(random_bytes(3))); $course='Administration'; $sem=1; $hash=password_hash($password,PASSWORD_DEFAULT); $role='admin';
$q=$db->prepare('INSERT INTO user_accounts (username,first_name,last_name,email,mobile,student_code,course,semester,password_hash,role) VALUES (?,?,?,?,?,?,?,?,?,?)');
$q->bind_param('sssssssiss',$name,$first,$last,$email,$mobile,$student,$course,$sem,$hash,$role); $q->execute(); echo "Created admin: $email\n";
