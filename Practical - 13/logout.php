<?php
declare(strict_types=1); require_once __DIR__.'/auth.php'; startAuthSession();
if ($_SERVER['REQUEST_METHOD']!=='POST' || !hash_equals((string)($_SESSION['csrf']??''),(string)($_POST['csrf']??''))) { http_response_code(403); exit('Invalid logout request.'); }
deleteRememberToken();
$_SESSION=[];
if (ini_get('session.use_cookies')) { $params=session_get_cookie_params(); setcookie(session_name(),'', ['expires'=>time()-3600,'path'=>$params['path'],'secure'=>$params['secure'],'httponly'=>true,'samesite'=>'Lax']); }
session_destroy();
header('Location: login.php?status=logout'); exit;
