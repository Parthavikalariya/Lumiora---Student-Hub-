<?php
declare(strict_types=1); require_once __DIR__.'/auth.php'; require_once __DIR__.'/admin-audit-helper.php'; startAuthSession();
if ($_SERVER['REQUEST_METHOD']!=='POST' || !hash_equals((string)($_SESSION['csrf']??''),(string)($_POST['csrf']??''))) { http_response_code(403); exit('Invalid logout request.'); }
if (($_SESSION['role'] ?? '') === 'admin' && !empty($_SESSION['user_id'])) {
    try { $db=getMySQLiConnection(); $id=(int)$_SESSION['user_id'];
        writeAdminAudit($db,$id,'admin_logout','user',$id,'Administrator signed out of Lumiora'); $db->close(); }
    catch(Throwable $err) { error_log('Logout audit: '.$err->getMessage()); }
}
deleteRememberToken();
$_SESSION=[];
if (ini_get('session.use_cookies')) { $params=session_get_cookie_params(); setcookie(session_name(),'', ['expires'=>time()-3600,'path'=>$params['path'],'secure'=>$params['secure'],'httponly'=>true,'samesite'=>'Lax']); }
session_destroy();
header('Location: login.php?status=logout'); exit;
