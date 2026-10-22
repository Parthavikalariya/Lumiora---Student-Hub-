<?php
/** Read-only admin audit log listing with safe search and action filtering. */
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_once __DIR__.'/role-menu.php';
requireLogin('admin');
$entries=[]; $error=null;
$filter=$_GET['filter']??'all';
$allowed=['all','admin_login','admin_logout','user_activated','user_deactivated','event_created','event_updated','event_archived','event_restored','event_opened','event_closed','student_archived','student_restored','student_created','student_updated'];
if(!is_string($filter) || !in_array($filter,$allowed,true)) $filter='all';
try {
    $db=getMySQLiConnection();
    if ($filter==='all') {
        $q=$db->prepare('SELECT a.log_id,a.action,a.target_type,a.target_id,a.details,a.created_at,u.username FROM audit_logs a JOIN user_accounts u ON a.admin_user_id=u.user_id ORDER BY a.log_id DESC LIMIT 100');
    } else {
        $q=$db->prepare('SELECT a.log_id,a.action,a.target_type,a.target_id,a.details,a.created_at,u.username FROM audit_logs a JOIN user_accounts u ON a.admin_user_id=u.user_id WHERE a.action=? ORDER BY a.log_id DESC LIMIT 100');
        $q->bind_param('s',$filter);
    }
    $q->execute(); $entries=$q->get_result()->fetch_all(MYSQLI_ASSOC); $q->close(); $db->close();
} catch(Throwable $e) { error_log('Admin audit list: '.$e->getMessage()); $error='Audit logs unavailable. Import admin_control_schema.sql.'; }
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Audit Trail | Lumiora</title><link rel="stylesheet" href="admin-control-center.css"></head><body><div class="workspace">
<?php renderLumioraSidebar('admin','audit'); ?>
<main class="main-content"><div class="page-body audit-page"><div class="page-header"><div><span class="breadcrumb">WORKSPACE / AUDIT HISTORY</span><h1>Admin Audit Trail<span class="accent">.</span></h1><p>Last 100 actions from your persistent audit_logs table.</p></div><a class="button-link" href="admin-dashboard.php">← Dashboard</a></div>
<?php if($error): ?><p class="alert alert-error" role="alert"><?=e($error)?></p><?php endif; ?>
<section class="panel"><div class="panel-head"><div><p class="panel-kicker">DATABASE ACTIVITY</p><h2>Logged administrative actions</h2><p>Who changed what, and when</p></div><form class="audit-filter" method="get"><label for="filter">Action filter</label><select id="filter" name="filter" onchange="this.form.submit()"><option value="all">All actions</option><?php foreach(array_slice($allowed,1) as $option): ?><option value="<?=e($option)?>" <?=$filter===$option?'selected':''?>><?=e(ucwords(str_replace('_',' ',$option)))?></option><?php endforeach; ?></select><button type="submit">Filter</button></form></div>
<div class="table-scroller"><table class="data-table"><thead><tr><th>Log ID</th><th>Administrator</th><th>Action</th><th>Target</th><th>Details</th><th>Timestamp</th></tr></thead><tbody>
<?php foreach($entries as $entry): ?><tr><td>#<?=e((string)$entry['log_id'])?></td><td><?=e((string)$entry['username'])?></td><td><span class="role-pill student"><?=e((string)$entry['action'])?></span></td><td><?=e((string)$entry['target_type'])?> #<?=e((string)($entry['target_id']??'–'))?></td><td><?=e((string)$entry['details'])?></td><td><?=e((string)$entry['created_at'])?></td></tr><?php endforeach; ?>
<?php if(!$entries): ?><tr><td class="empty" colspan="6">No matching audit entries yet. Try activating or deactivating a test student account.</td></tr><?php endif; ?>
</tbody></table></div></section><footer class="footer">Lumiora · Practical 14 · RBAC protected audit history</footer></div></main></div></body></html>
