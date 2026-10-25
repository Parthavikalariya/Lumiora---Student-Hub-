<?php
/** Shared append-only administrative audit history (P14). */
declare(strict_types=1);
require_once __DIR__ . '/mysqli-db.php';

function writeAdminAudit(mysqli $db, int $actorId, string $action, string $targetType, ?int $targetId, string $details): void {
    if ($actorId < 1 || !preg_match('/^[a-z_]{3,60}$/D', $action)
        || !preg_match('/^[a-z_]{3,40}$/D', $targetType)) {
        throw new InvalidArgumentException('Invalid audit metadata.');
    }
    $details = substr($details, 0, 255);
    $stmt = $db->prepare('INSERT INTO audit_logs (admin_user_id,action,target_type,target_id,details) VALUES (?,?,?,?,?)');
    $stmt->bind_param('issis', $actorId, $action, $targetType, $targetId, $details);
    $stmt->execute();
    $stmt->close();
}
