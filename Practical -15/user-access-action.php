<?php
/** CSRF-protected student account activation / deactivation with audit. */
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_once __DIR__.'/admin-audit-helper.php';
requireLogin('admin');
if (($_SERVER['REQUEST_METHOD']??'')!=='POST') { http_response_code(405); header('Allow: POST'); exit('POST only.'); }
if (!is_string($_POST['csrf']??null) || !hash_equals((string)$_SESSION['csrf'],$_POST['csrf'])) {
    http_response_code(403); exit('Invalid security token. Reload and retry.');
}
$id=filter_var($_POST['user_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$action=$_POST['action']??'';
if (!$id || !in_array($action,['activate','deactivate'],true)) {
    $_SESSION['admin_flash']=['type'=>'error','text'=>'Invalid account or action.'];
    header('Location: admin-dashboard.php',true,303); exit;
}
$db=null;
try {
    $db=getMySQLiConnection(); $db->begin_transaction();
    $stmt=$db->prepare('SELECT user_id,username,role,is_active FROM user_accounts WHERE user_id=? FOR UPDATE');
    $stmt->bind_param('i',$id); $stmt->execute(); $target=$stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$target || $target['role']!=='student' || (int)$id===(int)$_SESSION['user_id']) {
        throw new DomainException('Only student accounts can be changed. Admin accounts are protected.');
    }
    $desired=$action==='activate'?1:0;
    if ((int)$target['is_active']===$desired) throw new DomainException('No change: account already has that status.');
    $query=$db->prepare('UPDATE user_accounts SET is_active=? WHERE user_id=? AND role=\'student\'');
    $query->bind_param('ii',$desired,$id); $query->execute(); $query->close();
    if ($desired===0) {
        $token=$db->prepare('DELETE FROM remember_login_tokens WHERE user_id=?');
        $token->bind_param('i',$id); $token->execute(); $token->close();
    }
    writeAdminAudit($db,(int)$_SESSION['user_id'], $desired?'user_activated':'user_deactivated',
        'user',(int)$id, 'Student account '.(string)$target['username'].' changed to '.($desired?'active':'inactive'));
    $db->commit();
    $_SESSION['admin_flash']=['type'=>'success','text'=>($desired?'Activated':'Deactivated').' student account: '.$target['username'].'. Audit entry saved.'];
} catch(DomainException $error) {
    if($db) $db->rollback();
    $_SESSION['admin_flash']=['type'=>'error','text'=>$error->getMessage()];
} catch(Throwable $error) {
    if($db) { try {$db->rollback();} catch(Throwable $ignored) {} }
    error_log('User status action: '.$error->getMessage());
    $_SESSION['admin_flash']=['type'=>'error','text'=>'Could not update account. Verify database migration.'];
}
if($db) $db->close();
header('Location: admin-dashboard.php#accounts-title',true,303); exit;
