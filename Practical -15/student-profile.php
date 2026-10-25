<?php
/** Student self-service profile in the integrated portal. */
declare(strict_types=1);
require_once __DIR__.'/auth.php';
requireLogin('student');
$message=''; $error=''; $data=[];
$db=null;
try {
    $db=getMySQLiConnection();
    $id=(int)$_SESSION['user_id'];
    if (($_SERVER['REQUEST_METHOD']??'GET')==='POST') {
        if (!is_string($_POST['csrf']??null) || !hash_equals((string)$_SESSION['csrf'],$_POST['csrf'])) {
            http_response_code(403); $error='Invalid security token. Refresh this page.';
        } else {
            $first=trim((string)($_POST['first_name']??''));
            $last=trim((string)($_POST['last_name']??''));
            $mobile=trim((string)($_POST['mobile']??''));
            if (strlen($first)<2 || strlen($first)>30 || strlen($last)<2 || strlen($last)>30 || !preg_match('/^[0-9]{10}$/D',$mobile)) {
                $error='First and last name must be 2–30 characters, mobile must be 10 digits.';
            } else {
                $u=$db->prepare('UPDATE user_accounts SET first_name=?,last_name=?,mobile=? WHERE user_id=? AND role=\'student\' AND is_active=1');
                $u->bind_param('sssi',$first,$last,$mobile,$id);$u->execute();$u->close();
                $message='Profile saved securely.';
            }
        }
    }
    $q=$db->prepare('SELECT username,first_name,last_name,email,mobile,student_code,course,semester FROM user_accounts WHERE user_id=? AND role=\'student\' AND is_active=1 LIMIT 1');
    $q->bind_param('i',$id);$q->execute();$data=$q->get_result()->fetch_assoc()?:[];$q->close();$db->close();
} catch (Throwable $ex) { error_log('Student profile: '.$ex->getMessage()); $error='Profile temporarily unavailable. Check MySQL.'; }
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>My Profile | Lumiora</title><link rel="stylesheet" href="integration-style.css"><style>.profile-form{max-width:680px;margin:25px auto 60px;border:1px solid #34415b;padding:26px;border-radius:13px;background:#172238}.profile-form label{display:block;margin:14px 0 5px;font-weight:650}.profile-form input{width:100%;padding:12px;background:#0c172b;border:1px solid #4b6078;color:white;border-radius:8px}.profile-form button{margin-top:20px;background:#a5d9e5;color:#102239;padding:13px 25px;border:0;border-radius:8px;font-weight:700;cursor:pointer}.profile-form .read-only{color:#a9bdce;padding:10px;border:1px solid #314258;border-radius:8px}</style></head><body><div class="portal-shell"><header class="portal-header"><a class="portal-brand" href="student-dashboard.php"><span class="brand-tile">L</span><span>Lumiora<small>Student Workspace · My Profile</small></span></a><nav><a href="student-dashboard.php">← Student Dashboard</a></nav></header><main>
<section class="profile-form"><p class="eyebrow">SERVER-AUTHENTICATED STUDENT</p><h1>My Profile</h1><p>Only your own record can be edited here. Email and role are managed by the system.</p><?php if($error): ?><p role="alert"><?=e($error)?></p><?php endif; ?><?php if($message): ?><p role="status"><?=e($message)?></p><?php endif; ?>
<?php if($data): ?><div class="read-only"><strong>Username:</strong> <?=e((string)$data['username'])?> &nbsp; · &nbsp; <strong>Email:</strong> <?=e((string)$data['email'])?> &nbsp; · &nbsp; <strong>Student ID:</strong> <?=e((string)$data['student_code'])?><br><strong>Course:</strong> <?=e((string)$data['course'])?> · <strong>Semester:</strong> <?=e((string)$data['semester'])?></div><form action="student-profile.php" method="post"><input type="hidden" name="csrf" value="<?=e((string)$_SESSION['csrf'])?>"><label for="first">First name</label><input id="first" name="first_name" value="<?=e((string)$data['first_name'])?>" minlength="2" maxlength="30" required><label for="last">Last name</label><input id="last" name="last_name" value="<?=e((string)$data['last_name'])?>" minlength="2" maxlength="30" required><label for="mobile">Mobile</label><input id="mobile" name="mobile" value="<?=e((string)$data['mobile'])?>" pattern="[0-9]{10}" maxlength="10" inputmode="numeric" required><button type="submit">Save profile</button></form><?php endif; ?></section>
</main></div></body></html>
