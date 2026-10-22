<?php
/** Practical 14: admin-only live SQL dashboard, RBAC, users and audit. */
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_once __DIR__.'/role-menu.php';
require_once __DIR__.'/admin-dashboard-data.php';
requireLogin('admin');
$error=null; $data=null;
try { $db=getMySQLiConnection(); $data=loadAdminDashboard($db); $db->close(); }
catch(Throwable $exception) { error_log('Admin dashboard: '.$exception->getMessage()); $error='Unable to load dashboard data. Check MySQL and import admin_control_schema.sql.'; }
$notice=$_SESSION['admin_flash']??null; unset($_SESSION['admin_flash']);
$stats=$data['stats']??['users'=>0,'active_users'=>0,'inactive_users'=>0,'students'=>0,'events'=>0,'registrations'=>0];
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Control Center | Lumiora</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap-grid.min.css">
<link rel="stylesheet" href="admin-control-center.css">
<script src="admin-control-center.js" defer></script>
</head><body>
<div class="workspace">
<?php renderLumioraSidebar('admin'); ?>
<main class="main-content" id="main-content">
  <header class="topbar">
    <div><span class="breadcrumb">WORKSPACE / OVERVIEW</span><div class="topbar-title">Admin Control Center <span class="online-chip"><span></span> Protected</span></div></div>
    <div class="top-actions"><span class="whoami"><span class="avatar"><?=e(strtoupper(substr((string)$_SESSION['username'],0,1)))?></span><span><strong><?=e((string)$_SESSION['username'])?></strong><small>Administrator</small></span></span>
      <form method="post" action="logout.php"><input type="hidden" name="csrf" value="<?=e((string)$_SESSION['csrf'])?>"><button class="logout-button" type="submit">Log out <span aria-hidden="true">↗</span></button></form>
    </div>
  </header>
  <div class="page-body">
    <section class="welcome"><div><p class="eyebrow">PRACTICAL 14 · ROLE-BASED ADMINISTRATION</p><h1>Campus overview<span class="accent">.</span></h1><p>Keep track of your students, events, user accounts and registration activity—all from your MySQL database.</p></div><div class="welcome-decoration" aria-hidden="true"><span class="ring ring-one"></span><span class="ring ring-two"></span><span class="welcome-graphic">↗</span></div></section>
    <?php if($error): ?><div class="alert alert-error" role="alert"><?=e($error)?></div><?php endif; ?>
    <?php if(is_array($notice)): ?><div class="alert <?=($notice['type']??'')==='success'?'alert-success':'alert-error'?>" role="status"><?=e((string)($notice['text']??''))?></div><?php endif; ?>
    <section class="metrics" aria-label="Live database statistics">
      <div class="metric"><span class="metric-symbol blue">◉</span><span class="metric-name">Registered Users</span><strong><?=number_format($stats['users'])?></strong><span class="metric-caption"><?=number_format($stats['active_users'])?> active · <?=number_format($stats['inactive_users'])?> inactive</span></div>
      <div class="metric"><span class="metric-symbol mint">▤</span><span class="metric-name">Active Students</span><strong><?=number_format($stats['students'])?></strong><span class="metric-caption">Current student records</span></div>
      <div class="metric"><span class="metric-symbol violet">▦</span><span class="metric-name">Active Events</span><strong><?=number_format($stats['events'])?></strong><span class="metric-caption">Available campus events</span></div>
      <div class="metric"><span class="metric-symbol orange">↗</span><span class="metric-name">Event Registrations</span><strong><?=number_format($stats['registrations'])?></strong><span class="metric-caption">All recorded registrations</span></div>
    </section>
    <div class="content-grid">
      <section class="panel" aria-labelledby="analytics-title"><div class="panel-head"><div><p class="panel-kicker">ANALYTICS</p><h2 id="analytics-title">Registration activity</h2><p>Monthly student event sign-ups from MySQL</p></div><span class="subtle-pill">Last 6 months</span></div>
        <?php $maximum=max(1,...array_column(array_values($data['months']??[]),'count')); ?>
        <div class="chart" role="img" aria-label="Monthly registration totals">
        <?php foreach(($data['months']??[]) as $month): $count=(int)$month['count']; $height=max(4,round(100*$count/$maximum)); ?>
            <div class="chart-month"><span class="chart-value"><?=$count?></span><div class="chart-track"><div class="chart-bar" style="height:<?=$height?>%" title="<?=e($month['label'])?>: <?=$count?> registrations"></div></div><span class="chart-label"><?=e($month['label'])?></span></div>
        <?php endforeach; ?></div><p class="chart-note">Counts reflect actual event registrations, not demo or hardcoded data.</p>
      </section>
      <section class="panel" aria-labelledby="shortcuts-title"><div class="panel-head"><div><p class="panel-kicker">QUICK ACCESS</p><h2 id="shortcuts-title">Management tools</h2><p>Continue where you left off</p></div></div>
       <div class="quick-links"><a href="student-management.php"><span class="quick-icon icon-blue">▤</span><span><strong>Manage students</strong><small>Add, edit and archive records</small></span><b>→</b></a>
       <a href="event-management.php"><span class="quick-icon icon-purple">▦</span><span><strong>Manage events</strong><small>Events, posters and status</small></span><b>→</b></a>
       <a href="live-event-studio.php"><span class="quick-icon icon-green">↗</span><span><strong>Live Event Studio</strong><small>Fetch API · AJAX CRUD</small></span><b>→</b></a>
       <a href="admin-audit-logs.php"><span class="quick-icon icon-yellow">◷</span><span><strong>Admin audit history</strong><small>Account and event changes</small></span><b>→</b></a></div>
      </section>
    </div>
    <section class="panel" aria-labelledby="accounts-title"><div class="panel-head"><div><p class="panel-kicker">ACCESS MANAGEMENT · INTERMEDIATE EXTENSION</p><h2 id="accounts-title">User accounts</h2><p>Change student account status. Changes are written to audit_logs.</p></div><span class="subtle-pill"><?=count($data['accounts']??[])?> recent accounts</span></div>
       <div class="table-scroller"><table class="data-table"><thead><tr><th>Account</th><th>Email</th><th>Role</th><th>Status</th><th>Last sign-in</th><th>Action</th></tr></thead><tbody>
       <?php foreach(($data['accounts']??[]) as $user): $id=(int)$user['user_id']; $active=(int)$user['is_active']===1; ?>
       <tr><td><div class="user-cell"><span class="small-avatar"><?=e(strtoupper(substr((string)$user['username'],0,1)))?></span><span><strong><?=e((string)$user['username'])?></strong><small>#<?=$id?> · <?=e(trim((string)$user['first_name'].' '.$user['last_name']))?></small></span></div></td><td><?=e((string)$user['email'])?></td><td><span class="role-pill <?=e((string)$user['role'])?>"><?=e(ucfirst((string)$user['role']))?></span></td><td><span class="status-pill <?=$active?'is-active':'is-inactive'?>"><i></i><?=$active?'Active':'Inactive'?></span></td><td><?=e((string)($user['last_login_at']??'Never'))?></td><td>
       <?php if($user['role']==='student'): ?><form class="status-form" method="post" action="user-access-action.php" data-account="<?=e((string)$user['username'])?>" data-action="<?=$active?'Deactivate':'Activate'?>"><input type="hidden" name="csrf" value="<?=e((string)$_SESSION['csrf'])?>"><input type="hidden" name="user_id" value="<?=$id?>"><input type="hidden" name="action" value="<?=$active?'deactivate':'activate'?>"><button class="small-btn <?=$active?'danger':'success'?>" type="submit"><?=$active?'Deactivate':'Activate'?></button></form><?php else: ?><span class="muted">Admin protected</span><?php endif; ?></td></tr>
       <?php endforeach; ?>
       <?php if(empty($data['accounts'])): ?><tr><td colspan="6" class="empty">No accounts found. Create student accounts through the registration page.</td></tr><?php endif; ?>
       </tbody></table></div>
    </section>
    <div class="content-grid bottom-grid">
      <section class="panel"><div class="panel-head"><div><p class="panel-kicker">LIVE DATABASE</p><h2>Latest registrations</h2><p>Most recent student → event records</p></div></div>
        <div class="activity-list"><?php foreach(($data['registrations']??[]) as $reg): ?><div class="activity-row"><span class="activity-icon">◉</span><div><strong><?=e((string)$reg['full_name'])?></strong><p>Registered for <?=e((string)$reg['event_name'])?></p></div><time><?=e((string)$reg['registered_at'])?></time></div><?php endforeach; ?>
        <?php if(empty($data['registrations'])): ?><p class="empty-state">No event registrations yet. New registrations will appear here automatically.</p><?php endif; ?></div>
      </section>
      <section class="panel"><div class="panel-head"><div><p class="panel-kicker">TRACEABILITY</p><h2>Recent audit activity</h2><p>Admin actions saved to audit_logs</p></div><a class="text-link" href="admin-audit-logs.php">View all →</a></div>
        <div class="activity-list"><?php foreach(($data['audit']??[]) as $log): ?><div class="activity-row"><span class="activity-icon audit-icon">↗</span><div><strong><?=e(str_replace('_',' ',ucwords((string)$log['action'],'_')))?></strong><p><?=e((string)$log['username'])?> · <?=e((string)$log['details'])?></p></div><time><?=e((string)$log['created_at'])?></time></div><?php endforeach; ?>
        <?php if(empty($data['audit'])): ?><p class="empty-state">No new admin actions yet. Use an account status toggle to create an audit record.</p><?php endif; ?></div>
      </section>
    </div>
    <footer class="footer">Lumiora · Practical 14 · Live SQL aggregation · Admin-only RBAC · Audit logging</footer>
  </div>
</main>
</div>
</body></html>
