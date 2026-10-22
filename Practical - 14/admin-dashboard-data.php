<?php
/** Secure dashboard SQL aggregates and limited read-only activity lists. */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

function dashboardScalar(mysqli $db, string $sql): int {
    $result=$db->query($sql);
    $value=$result->fetch_row(); $result->free();
    return (int)($value[0] ?? 0);
}

function loadAdminDashboard(mysqli $db): array {
    $stats=[
        'users'=>dashboardScalar($db,'SELECT COUNT(*) FROM user_accounts'),
        'active_users'=>dashboardScalar($db,'SELECT COUNT(*) FROM user_accounts WHERE is_active=1'),
        'inactive_users'=>dashboardScalar($db,'SELECT COUNT(*) FROM user_accounts WHERE is_active=0'),
        'students'=>dashboardScalar($db,"SELECT COUNT(*) FROM students WHERE status='active'"),
        'events'=>dashboardScalar($db,'SELECT COUNT(*) FROM events WHERE is_archived=0'),
        'registrations'=>dashboardScalar($db,'SELECT COUNT(*) FROM event_registrations'),
    ];
    $regSql='SELECT r.registration_id,r.registered_at,s.full_name,s.enrollment_no,e.event_name '
        .'FROM event_registrations r JOIN students s ON r.student_id=s.student_id '
        .'JOIN events e ON r.event_id=e.event_id '
        .'ORDER BY r.registered_at DESC,r.registration_id DESC LIMIT 8';
    $recentRegistrations=$db->query($regSql)->fetch_all(MYSQLI_ASSOC);
    $accounts=$db->query('SELECT user_id,username,first_name,last_name,email,role,is_active,created_at,last_login_at '
        .'FROM user_accounts ORDER BY user_id DESC LIMIT 30')->fetch_all(MYSQLI_ASSOC);
    $audit=$db->query('SELECT a.log_id,a.action,a.target_type,a.target_id,a.details,a.created_at,u.username '
        .'FROM audit_logs a JOIN user_accounts u ON a.admin_user_id=u.user_id '
        .'ORDER BY a.log_id DESC LIMIT 8')->fetch_all(MYSQLI_ASSOC);
    $months=[];
    // Last six months, including the current month, with zero-fill for empty months.
    $now=new DateTimeImmutable('first day of this month');
    for($i=5;$i>=0;$i--) { $month=$now->modify('-'.$i.' months'); $months[$month->format('Y-m')]=['label'=>$month->format('M'),'count'=>0]; }
    $firstKey=array_key_first($months);
    $q=$db->prepare("SELECT DATE_FORMAT(registered_at,'%Y-%m') AS month_key, COUNT(*) AS total FROM event_registrations WHERE registered_at>=CONCAT(?,'-01') GROUP BY DATE_FORMAT(registered_at,'%Y-%m') ORDER BY month_key");
    $q->bind_param('s',$firstKey); $q->execute();
    foreach($q->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        if(array_key_exists($row['month_key'],$months)) $months[$row['month_key']]['count']=(int)$row['total'];
    }
    $q->close();
    return ['stats'=>$stats,'registrations'=>$recentRegistrations,'accounts'=>$accounts,'audit'=>$audit,'months'=>$months];
}
