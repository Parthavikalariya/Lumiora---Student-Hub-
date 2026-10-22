<?php
/** Event listing, search/filter, paging, poster preview & management actions. */
declare(strict_types=1);
require_once __DIR__.'/event-records.php';
requireEventAdmin();
$filters=eventSearchFilters();
[$search,$category,$status,$archive]=$filters;
[$where,$types,$values]=eventWhere($filters);
$rawPage=$_GET['page'] ?? '1';
$page=is_string($rawPage)&&ctype_digit($rawPage)?max(1,min(100000,(int)$rawPage)):1;
$perPage=6; $total=0; $pages=1; $events=[]; $categories=[]; $error=null;
try {
    $db=getMySQLiConnection();
    $q=eventQuery($db,'SELECT DISTINCT category FROM events ORDER BY category');
    $categories=array_column($q->get_result()->fetch_all(MYSQLI_ASSOC),'category'); $q->close();
    $q=eventQuery($db,'SELECT COUNT(*) AS total FROM events e'.$where,$types,$values);
    $total=(int)$q->get_result()->fetch_assoc()['total']; $q->close();
    $pages=max(1,(int)ceil($total/$perPage)); $page=min($page,$pages);
    $offset=($page-1)*$perPage;
    $q=eventQuery($db,
        'SELECT e.event_id,e.event_name,e.category,e.event_date,e.venue,e.description,e.poster_path,e.event_status,e.is_archived, '
        .'(SELECT COUNT(*) FROM event_registrations r WHERE r.event_id=e.event_id) AS registrations '
        .'FROM events e'.$where.' ORDER BY e.event_id DESC LIMIT ? OFFSET ?',
        $types.'ii',array_merge($values,[$perPage,$offset]));
    $events=$q->get_result()->fetch_all(MYSQLI_ASSOC); $q->close(); $db->close();
} catch (Throwable $exception) {
    error_log('Event listing failed: '.$exception->getMessage());
    $error='Unable to load events. Import event_management_schema.sql and check MySQL.';
}
$notice=takeEventNotice();
function eventPageUrl(int $next,array $filters):string {
    return 'event-management.php?'.http_build_query(['search'=>$filters[0],'category'=>$filters[1],'status'=>$filters[2],'archive'=>$filters[3],'page'=>$next]);
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Event Management | Lumiora</title><link rel="stylesheet" href="event-management-style.css"></head><body><main class="shell">
<div class="toolbar"><a href="admin-dashboard.php">&larr; Admin Dashboard</a><div class="toolbar-links"><a class="btn" href="event-form.php">+ Add Event</a><a class="btn secondary" href="event-audit-log.php">Audit Log</a></div></div>
<section class="hero"><h1>Event Management</h1><p>Secure MySQLi CRUD &bull; Poster upload &bull; Category, Open/Closed filters &bull; Soft delete</p></section>
<?php if ($notice): ?><div class="notice <?=evh($notice['type'])?>" role="status"><?=evh($notice['text'])?></div><?php endif; ?>
<?php if ($error): ?><div class="notice error" role="alert"><?=evh($error)?></div><?php endif; ?>
<section class="panel"><form method="get" action="event-management.php" class="filters">
<div class="field wide"><label for="search">Search event or venue</label><input id="search" name="search" type="search" maxlength="100" placeholder="e.g. Workshop / Hall" value="<?=evh($search)?>"></div>
<div class="field"><label for="category">Category</label><select id="category" name="category"><option value="">All categories</option><?php foreach($categories as $choice): ?><option value="<?=evh($choice)?>" <?=$category===$choice?'selected':''?>><?=evh($choice)?></option><?php endforeach; ?></select></div>
<div class="field"><label for="status">Event Status</label><select id="status" name="status"><option value="all" <?=$status==='all'?'selected':''?>>Open &amp; Closed</option><option value="open" <?=$status==='open'?'selected':''?>>Open</option><option value="closed" <?=$status==='closed'?'selected':''?>>Closed</option></select></div>
<div class="field"><label for="archive">Record State</label><select id="archive" name="archive"><option value="active" <?=$archive==='active'?'selected':''?>>Active</option><option value="archived" <?=$archive==='archived'?'selected':''?>>Archived</option><option value="all" <?=$archive==='all'?'selected':''?>>All Records</option></select></div>
<div class="filter-buttons"><button type="submit">Search / Filter</button><a class="btn secondary" href="event-management.php">Reset</a></div>
</form></section>
<section class="panel"><div class="toolbar"><h2>Event Records</h2><span class="summary"><?=evh($total)?> matching event(s) &middot; Page <?=evh($page)?> of <?=evh($pages)?></span></div>
<div class="table-scroller"><table><thead><tr><th>Poster</th><th>ID</th><th>Event / Venue</th><th>Category</th><th>Date</th><th>Registrations</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($events as $event): $poster=eventPosterUrl($event['poster_path']); ?>
<tr><td><?php if ($poster): ?><img class="poster-thumb" src="<?=evh($poster)?>" alt="<?=evh($event['event_name'])?> poster"><?php else: ?><div class="poster-placeholder">No poster</div><?php endif; ?></td>
<td><?=evh($event['event_id'])?></td><td><b><?=evh($event['event_name'])?></b><small><?=evh($event['venue'])?></small></td>
<td><?=evh($event['category'])?></td><td><?=evh($event['event_date'])?></td><td><?=evh($event['registrations'])?></td>
<td><span class="pill <?=evh($event['event_status'])?>"><?=evh($event['event_status'])?></span><?php if ((int)$event['is_archived']): ?><span class="pill archived">archived</span><?php endif; ?></td>
<td><div class="actions"><a class="btn tiny secondary" href="event-form.php?id=<?=evh($event['event_id'])?>">Edit</a>
<?php if ((int)$event['is_archived']===0): ?>
<form method="post" action="event-status.php" onsubmit="return confirm('Archive this event? Student registrations will remain saved.');"><input type="hidden" name="csrf" value="<?=evh($_SESSION['csrf'])?>"><input type="hidden" name="event_id" value="<?=evh($event['event_id'])?>"><input type="hidden" name="action" value="archive"><input type="hidden" name="confirm_change" value="YES"><button class="danger tiny" type="submit">Delete</button></form>
<form method="post" action="event-status.php"><input type="hidden" name="csrf" value="<?=evh($_SESSION['csrf'])?>"><input type="hidden" name="event_id" value="<?=evh($event['event_id'])?>"><input type="hidden" name="action" value="<?= $event['event_status']==='open'?'close':'open' ?>"><input type="hidden" name="confirm_change" value="YES"><button class="secondary tiny" type="submit"><?= $event['event_status']==='open'?'Close':'Open' ?></button></form>
<?php else: ?><form method="post" action="event-status.php" onsubmit="return confirm('Restore this event?');"><input type="hidden" name="csrf" value="<?=evh($_SESSION['csrf'])?>"><input type="hidden" name="event_id" value="<?=evh($event['event_id'])?>"><input type="hidden" name="action" value="restore"><input type="hidden" name="confirm_change" value="YES"><button class="tiny secondary" type="submit">Restore</button></form><?php endif; ?>
</div></td></tr><?php endforeach; ?>
<?php if (!$events): ?><tr><td class="empty" colspan="8">No events found. Change filters or add an event.</td></tr><?php endif; ?></tbody></table></div>
<div class="pagination"><?php if ($page>1): ?><a href="<?=evh(eventPageUrl($page-1,$filters))?>">&larr; Previous</a><?php endif; ?><?php for($n=max(1,$page-2);$n<=min($pages,$page+2);$n++): ?><a class="<?=$n===$page?'current':''?>" href="<?=evh(eventPageUrl($n,$filters))?>"><?=$n?></a><?php endfor; ?><?php if($page<$pages): ?><a href="<?=evh(eventPageUrl($page+1,$filters))?>">Next &rarr;</a><?php endif; ?></div>
<p class="footnote">Delete archives an event instead of removing it from MySQL. Existing StudentHub event registrations remain intact. Uploads are checked on the server and renamed randomly.</p></section>
</main></body></html>
