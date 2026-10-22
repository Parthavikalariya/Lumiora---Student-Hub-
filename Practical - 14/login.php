<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_once __DIR__.'/admin-audit-helper.php';
startAuthSession();
if (!empty($_SESSION['user_id'])) {
    try { if (refreshSessionIdentity()) { header('Location: '.dashboardUrl()); exit; } }
    catch(Throwable $ex) { error_log('Login identity: '.$ex->getMessage()); }
}
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email=trim((string)($_POST['email']??'')); $password=$_POST['password']??'';
    if (!hash_equals((string)($_SESSION['csrf']??''),(string)($_POST['csrf']??''))) $error='Invalid request token. Reload and try again.';
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL) || !is_string($password)) $error='Invalid email or password.';
    else {
        try {
            $db=getMySQLiConnection();
            $q=$db->prepare('SELECT user_id,username,role,password_hash,is_active FROM user_accounts WHERE email=? LIMIT 1');
            $q->bind_param('s',$email); $q->execute(); $user=$q->get_result()->fetch_assoc(); $q->close();
            if ($user && (int)$user['is_active'] === 1 && password_verify($password,$user['password_hash'])) {
                $uid=(int)$user['user_id'];
                $update=$db->prepare('UPDATE user_accounts SET last_login_at=NOW() WHERE user_id=?');
                $update->bind_param('i',$uid); $update->execute(); $update->close();
                if ($user['role'] === 'admin') {
                    writeAdminAudit($db,$uid,'admin_login','user',$uid,'Administrator signed in to Lumiora');
                }
                signInUser($user);
                if (!empty($_POST['remember'])) issueRememberToken($db,$uid);
                header('Location: '.dashboardUrl()); exit;
            }
            $error='Invalid email or password.';
        } catch(Throwable $ex) { error_log('Login failed: '.$ex->getMessage()); $error='Login service unavailable. Check MySQL setup.'; }
    }
}
$messages=['required'=>'Please log in to access the dashboard.','timeout'=>'Session expired after 15 minutes of inactivity. Log in again.','logout'=>'You have logged out successfully.','disabled'=>'This account is inactive or unavailable.'];
$notice=$messages[$_GET['status']??'']??'';
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Secure Login – Lumiora</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="database-style.css"><style>body{min-height:100vh}.auth-wrap{max-width:510px;margin:10vh auto;padding:28px;background:#192237;border-radius:16px;color:#eff5ff} .auth-wrap input[type=email],.auth-wrap input[type=password]{display:block;width:100%;margin:8px 0 18px;padding:13px;border:1px solid #5a6c89;border-radius:7px;background:#0e1728;color:white}.auth-wrap button{padding:12px 26px;background:#7ccaea;border:0;border-radius:7px;cursor:pointer;font-weight:bold}.auth-wrap a{color:#9ddfff}.notice{padding:12px;background:#273f55}.error{padding:12px;background:#5b2b34}</style></head><body class="page-login"><main class="auth-wrap"><h1>Lumiora – Secure Login</h1><p>StudentHub · Secure sessions and role-based access</p><?php if($notice):?><p class="notice"><?=e($notice)?></p><?php endif;?><?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?><form method="post" action="login.php"><input type="hidden" name="csrf" value="<?=e((string)$_SESSION['csrf'])?>"><label>Email Address<input type="email" name="email" required autocomplete="username" placeholder="Enter registered email"></label><label>Password<input type="password" name="password" required autocomplete="current-password" placeholder="Enter password"></label><p><label><input type="checkbox" name="remember" value="1"> Remember me for 14 days</label></p><button type="submit">Login Securely</button></form><p><a href="register.html">Create account</a> · <a href="home.html">Home</a></p></main></body></html>
