<?php
declare(strict_types=1);
require_once __DIR__ . '/mysqli-db.php';
const SESSION_IDLE_LIMIT = 900; // 15 minutes
const REMEMBER_COOKIE = 'studenthub_pr11_remember';
function isHttps(): bool { return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'; }
// Keep Practical 11's cookies separate from Practical 10 when both folders run on localhost.
function projectCookiePath(): string { return rtrim(str_replace('\\','/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/'))), '/') . '/'; }
function startAuthSession(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    session_name('STUDENTHUB11_SESSID');
    session_set_cookie_params(['lifetime'=>0,'path'=>projectCookiePath(),'secure'=>isHttps(),'httponly'=>true,'samesite'=>'Lax']);
    session_start();
    if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
}
function cookieOptions(int $expires): array { return ['expires'=>$expires,'path'=>projectCookiePath(),'secure'=>isHttps(),'httponly'=>true,'samesite'=>'Lax']; }
function forgetRememberCookie(): void { setcookie(REMEMBER_COOKIE, '', cookieOptions(time()-3600)); unset($_COOKIE[REMEMBER_COOKIE]); }
function deleteRememberToken(): void {
    $cookie = $_COOKIE[REMEMBER_COOKIE] ?? '';
    if (is_string($cookie) && preg_match('/^[a-f0-9]{24}:[a-f0-9]{64}$/D', $cookie)) {
        [$selector] = explode(':', $cookie, 2);
        try { $db=getMySQLiConnection(); $q=$db->prepare('DELETE FROM remember_login_tokens WHERE selector=?'); $q->bind_param('s',$selector); $q->execute(); $q->close(); } catch(Throwable $e) { error_log($e->getMessage()); }
    }
    forgetRememberCookie();
}
function issueRememberToken(mysqli $db, int $userId): void {
    $selector=bin2hex(random_bytes(12)); $validator=bin2hex(random_bytes(32));
    $hash=hash('sha256', $validator); $exp=time()+14*86400; $expiry=gmdate('Y-m-d H:i:s',$exp);
    $q=$db->prepare('INSERT INTO remember_login_tokens (selector,token_hash,user_id,expires_at) VALUES (?,?,?,?)');
    $q->bind_param('ssis',$selector,$hash,$userId,$expiry); $q->execute(); $q->close();
    setcookie(REMEMBER_COOKIE, $selector.':'.$validator, cookieOptions($exp));
}
function signInUser(array $user): void {
    startAuthSession();
    session_regenerate_id(true);
    $_SESSION['user_id']=(int)$user['user_id'];
    $_SESSION['username']=(string)$user['username'];
    $_SESSION['role']= $user['role']==='admin'?'admin':'student';
    $_SESSION['last_activity']=time();
    $_SESSION['csrf']=bin2hex(random_bytes(24));
}
function restoreRememberSession(): bool {
    $cookie=$_COOKIE[REMEMBER_COOKIE] ?? '';
    if (!is_string($cookie) || !preg_match('/^[a-f0-9]{24}:[a-f0-9]{64}$/D',$cookie)) return false;
    [$selector,$validator]=explode(':',$cookie,2);
    try {
        $db=getMySQLiConnection();
        $q=$db->prepare('SELECT t.token_hash,t.expires_at,u.user_id,u.username,u.role FROM remember_login_tokens t JOIN user_accounts u ON t.user_id=u.user_id WHERE t.selector=? LIMIT 1');
        $q->bind_param('s',$selector); $q->execute(); $record=$q->get_result()->fetch_assoc(); $q->close();
        if (!$record || strtotime($record['expires_at'].' UTC') < time() || !hash_equals($record['token_hash'],hash('sha256',$validator))) { deleteRememberToken(); return false; }
        // Rotate token on each successful automatic sign-in.
        $d=$db->prepare('DELETE FROM remember_login_tokens WHERE selector=?'); $d->bind_param('s',$selector); $d->execute(); $d->close();
        signInUser($record); issueRememberToken($db,(int)$record['user_id']); return true;
    } catch(Throwable $e) { error_log('remember token restore: '.$e->getMessage()); return false; }
}
function requireLogin(?string $role=null): void {
    startAuthSession();
    if (isset($_SESSION['user_id'],$_SESSION['last_activity']) && (time()-(int)$_SESSION['last_activity'])>SESSION_IDLE_LIMIT) {
        // Inactivity ends the SESSION; remember-me is not auto-applied in this request.
        unset($_SESSION['user_id'],$_SESSION['role'],$_SESSION['username'],$_SESSION['last_activity']);
        session_regenerate_id(true);
        header('Location: login.php?status=timeout'); exit;
    }
    if (empty($_SESSION['user_id']) && !restoreRememberSession()) { header('Location: login.php?status=required'); exit; }
    $_SESSION['last_activity']=time();
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    if ($role !== null && ($_SESSION['role'] ?? '') !== $role) { http_response_code(403); echo '<h2>403 Forbidden: this dashboard is for '.htmlspecialchars($role).' users only.</h2><a href="student-dashboard.php">Student dashboard</a>'; exit; }
}
function dashboardUrl(): string { return ($_SESSION['role']??'student')==='admin'?'admin-dashboard.php':'student-dashboard.php'; }
function e(string $s): string { return htmlspecialchars($s,ENT_QUOTES,'UTF-8'); }
