<?php
/** POST-only event status/soft-delete actions with CSRF validation. */
declare(strict_types=1);
require_once __DIR__.'/event-records.php';
requireEventAdmin();
if (!eventCsrfValid() || ($_POST['confirm_change'] ?? '') !== 'YES') {
    http_response_code(403); exit('Invalid or unconfirmed change.');
}
$id=filter_var($_POST['event_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$action=$_POST['action'] ?? '';
$allowed=['archive','restore','open','close'];
if (!$id || !is_string($action) || !in_array($action,$allowed,true)) {
    eventNotice('error','Invalid event or action.'); redirectEvents();
}
$updates=[
    'archive'=>['SET is_archived=1','is_archived=0','archived','Event archived; old registrations are preserved.'],
    'restore'=>['SET is_archived=0','is_archived=1','restored','Event restored successfully.'],
    'open'=>['SET event_status=\'open\'','is_archived=0 AND event_status=\'closed\'','opened','Registration status changed to Open.'],
    'close'=>['SET event_status=\'closed\'','is_archived=0 AND event_status=\'open\'','closed','Registration status changed to Closed.'],
];
[$set,$where,$auditAction,$notice]=$updates[$action];
$db=null;
try {
    $db=getMySQLiConnection(); $db->begin_transaction();
    $stmt=eventQuery($db,"UPDATE events $set, updated_at=NOW() WHERE event_id=? AND $where",'i',[$id]);
    $changed=$stmt->affected_rows; $stmt->close();
    if ($changed) eventLog($db,$id,$auditAction,'Admin changed event status');
    $db->commit();
    eventNotice($changed?'success':'error',$changed?$notice:'No change: verify the event’s current status.');
} catch(Throwable $exception) {
    if ($db) { try {$db->rollback();}catch(Throwable $ignored){} }
    error_log('Event status change failed: '.$exception->getMessage());
    eventNotice('error','Could not update event status. Check database schema.');
}
redirectEvents();
