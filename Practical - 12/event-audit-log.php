<?php
/** Read-only admin event change history. */
declare(strict_types=1);
require_once __DIR__.'/event-records.php';
requireEventAdmin();
$error=null; $audit=[];
try {
    $db=getMySQLiConnection();
    $q=eventQuery($db,'SELECT a.audit_id,a.event_id,a.action,a.details,a.created_at,e.event_name,u.username FROM event_change_audit a JOIN events e ON a.event_id=e.event_id JOIN user_accounts u ON a.admin_user_id=u.user_id ORDER BY a.audit_id DESC LIMIT 100');
    $audit=$q->get_result()->fetch_all(MYSQLI_ASSOC); $q->close(); $db->close();
} catch (Throwable $exception) {
    error_log('Event audit unavailable: '.$exception->getMessage());
    $error='Audit log unavailable. Please import event_management_schema.sql.';
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Event Change Audit | Lumiora</title><link rel="stylesheet" href="event-management-style.css"></head><body><main class="shell">
<nav class="toolbar"><a href="event-management.php">&larr; Event Management</a></nav><section class="hero"><h1>Event Change Audit Log</h1><p>Last 100 event changes &bull; admin-only view &bull; records stored securely in MySQL</p></section>
<?php if($error): ?><p class="notice error"><?=evh($error)?></p><?php endif; ?>
<section class="panel"><div class="table-scroller"><table><thead><tr><th>Audit ID</th><th>Event</th><th>Action</th><th>Admin</th><th>Details</th><th>Time</th></tr></thead><tbody>
<?php foreach($audit as $row): ?><tr><td><?=evh($row['audit_id'])?></td><td><?=evh($row['event_name'])?> (<?=evh($row['event_id'])?>)</td><td><span class="pill open"><?=evh($row['action'])?></span></td><td><?=evh($row['username'])?></td><td><?=evh($row['details'])?></td><td><?=evh($row['created_at'])?></td></tr><?php endforeach; ?>
<?php if (!$audit): ?><tr><td class="empty" colspan="6">No event changes have been recorded yet.</td></tr><?php endif; ?></tbody></table></div></section></main></body></html>
