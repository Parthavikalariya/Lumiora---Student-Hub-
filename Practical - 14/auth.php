<?php
/** Authentication shared across Practical 10-14, with live account/RBAC checks. */
declare(strict_types=1);
require_once __DIR__ . '/mysqli-db.php';
const SESSION_IDLE_LIMIT = 900; // 15 minutes
const REMEMBER_COOKIE = 'studenthub_pr14_remember';
function isHttps(): bool { return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'; }
function projectCookiePath(): string { return rtrim(str_replace('\\','/',dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/'))), '/') . '/'; }
function startAuthSession(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    ini_set('session.use_strict_mode','1'); ini_set('session.use_only_cookies','1'); ini_set('session.cookie_httponly','1');
    session_name('STUDENTHUB14_SESSID');
    session_set_cookie_params(['lifetime'=>0,'path'=>projectCookiePath(),'secure'=>isHttps(),'httponly'=>true,'samesite'=>'Lax']);
    session_start();
    if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
}
function cookieOptions(int $expires): array { return ['expires'=>$expires,'path'=>projectCookiePath(),'secure'=>isHttps(),'httponly'=>true,'samesite'=>'Lax']; }
function forgetRememberCookie(): void { setcookie(REMEMBER_COOKIE,'',cookieOptions(time()-3600)); unset($_COOKIE[REMEMBER_COOKIE]); }
function deleteRememberToken(): void {
    $cookie=$_COOKIE[REMEMBER_COOKIE] ?? '';
    if (is_string($cookie) && preg_match('/^[a-f0-9]{24}:[a-f0-9]{64}$/D',$cookie)) {
        [$selector]=explode(':',$cookie,2);
        try { $db=getMySQLiConnection(); $q=$db->prepare('DELETE FROM remember_login_tokens WHERE selector=?'); $q->bind_param('s',$selector); $q->execute(); $q->close(); $db->close(); }
        catch (Throwable $error) { error_log('Token removal: '.$error->getMessage()); }
    }
    forgetRememberCookie();
}
function issueRememberToken(mysqli $db, int $userId): void {
    $selector=bin2hex(random_bytes(12)); $validator=bin2hex(random_bytes(32));
    $hash=hash('sha256',$validator); $expiry=gmdate('Y-m-d H:i:s',time()+14*86400);
    $q=$db->prepare('INSERT INTO remember_login_tokens (selector,token_hash,user_id,expires_at) VALUES (?,?,?,?)');
    $q->bind_param('ssis',$selector,$hash,$userId,$expiry); $q->execute(); $q->close();
    setcookie(REMEMBER_COOKIE,$selector.':'.$validator,cookieOptions(time()+14*86400));
}
function signInUser(array $user): void {
    startAuthSession(); session_regenerate_id(true);
    $_SESSION['user_id']=(int)$user['user_id'];
    $_SESSION['username']=(string)$user['username'];
    $_SESSION['role']=$user['role'] === 'admin' ? 'admin':'student';
    $_SESSION['last_activity']=time(); $_SESSION['csrf']=bin2hex(random_bytes(24));
}
function clearAuthIdentity(): void {
    unset($_SESSION['user_id'],$_SESSION['username'],$_SESSION['role'],$_SESSION['last_activity']);
}
/** Recheck the DB on every protected request; demotions/disables take effect immediately. */
function refreshSessionIdentity(): bool {
    if (empty($_SESSION['user_id'])) return false;
    $db=getMySQLiConnection();
    $id=(int)$_SESSION['user_id'];
    $q=$db->prepare('SELECT user_id,username,role,is_active FROM user_accounts WHERE user_id=? LIMIT 1');
    $q->bind_param('i',$id); $q->execute(); $row=$q->get_result()->fetch_assoc(); $q->close(); $db->close();
    if (!$row || (int)$row['is_active'] !== 1) { clearAuthIdentity(); return false; }
    $_SESSION['username']=(string)$row['username'];
    $_SESSION['role']=$row['role'] === 'admin' ? 'admin':'student';
    return true;
}
function restoreRememberSession(): bool {
    $cookie=$_COOKIE[REMEMBER_COOKIE] ?? '';
    if (!is_string($cookie) || !preg_match('/^[a-f0-9]{24}:[a-f0-9]{64}$/D',$cookie)) return false;
    [$selector,$validator]=explode(':',$cookie,2);
    try {
        $db=getMySQLiConnection();
        $q=$db->prepare('SELECT t.token_hash,t.expires_at,u.user_id,u.username,u.role,u.is_active FROM remember_login_tokens t JOIN user_accounts u ON t.user_id=u.user_id WHERE t.selector=? LIMIT 1');
        $q->bind_param('s',$selector); $q->execute(); $row=$q->get_result()->fetch_assoc(); $q->close();
        if (!$row || (int)$row['is_active']!==1 || strtotime($row['expires_at'].' UTC')<time() || !hash_equals($row['token_hash'],hash('sha256',$validator))) {
            $db->close(); deleteRememberToken(); return false;
        }
        $d=$db->prepare('DELETE FROM remember_login_tokens WHERE selector=?');
        $d->bind_param('s',$selector); $d->execute(); $d->close();
        signInUser($row); issueRememberToken($db,(int)$row['user_id']); $db->close(); return true;
    } catch(Throwable $error) { error_log('Remember session: '.$error->getMessage()); return false; }
}
function requireLogin(?string $role=null): void {
    startAuthSession();
    if (isset($_SESSION['user_id'],$_SESSION['last_activity']) && time()-(int)$_SESSION['last_activity']>SESSION_IDLE_LIMIT) {
        clearAuthIdentity(); session_regenerate_id(true); header('Location: login.php?status=timeout'); exit;
    }
    if (empty($_SESSION['user_id']) && !restoreRememberSession()) { header('Location: login.php?status=required'); exit; }
    try { $valid=refreshSessionIdentity(); } catch(Throwable $error) {
        error_log('Session RBAC check: '.$error->getMessage()); http_response_code(503);
        exit('Account verification unavailable. Import admin_control_schema.sql and check MySQL.');
    }
    if (!$valid) { forgetRememberCookie(); header('Location: login.php?status=disabled'); exit; }
    $_SESSION['last_activity']=time();
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    if ($role!==null && ($_SESSION['role']??'')!==$role) {
        http_response_code(403);
        echo '<h2>403 Forbidden: '.htmlspecialchars($role,ENT_QUOTES,'UTF-8').' access only.</h2><a href="'.($_SESSION['role']==='admin'?'admin-dashboard.php':'student-dashboard.php').'">Return to your dashboard</a>';
        exit;
    }
}
function dashboardUrl(): string { return ($_SESSION['role']??'student')==='admin'?'admin-dashboard.php':'student-dashboard.php'; }
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
