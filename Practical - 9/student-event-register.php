<?php
declare(strict_types=1);
session_start();
require_once __DIR__.'/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); header('Allow: POST'); exit('Method not allowed'); }
$token=$_POST['csrf']??'';
if (!is_string($token) || !hash_equals($_SESSION['db_csrf']??'', $token)) { http_response_code(403); exit('Invalid request token. Return to the database page.'); }
$student=filter_input(INPUT_POST,'student_id',FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$event=filter_input(INPUT_POST,'event_id',FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$success=false; $message='Please choose a valid student and event.';
if ($student && $event) {
    try {
        $pdo=getDatabaseConnection();
        $stmt=$pdo->prepare('INSERT INTO event_registrations (student_id,event_id) VALUES (:student_id,:event_id)');
        $stmt->execute(['student_id'=>$student,'event_id'=>$event]);
        $success=true; $message='Student registered successfully for the selected event.';
    } catch (PDOException $e) {
        if ($e->getCode()==='23000') { $message='Registration not saved: duplicate registration or invalid student/event selection.'; }
        else { error_log('StudentHub registration: '.$e->getMessage()); $message='Could not save registration. Check database connection.'; }
    }
}
$_SESSION['db_flash']=['success'=>$success,'message'=>$message];
header('Location: database-demo.php',true,303);
exit;
