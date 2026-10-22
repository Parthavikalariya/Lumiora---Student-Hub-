<?php
/** Secure admin-only event INSERT/UPDATE and poster upload. */
declare(strict_types=1);
require_once __DIR__.'/event-records.php';
require_once __DIR__.'/event-poster-upload.php';
requireEventAdmin();
$post=$_SERVER['REQUEST_METHOD'] === 'POST';
$rawId=$post ? ($_POST['event_id'] ?? null) : ($_GET['id'] ?? null);
$id=filter_var($rawId,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
$editing=$id>0;
$event=['event_name'=>'','category'=>'Workshop','event_date'=>'','venue'=>'','description'=>'','event_status'=>'open','poster_path'=>null,'is_archived'=>0];
$errors=[];
$newPoster=null;
$db=null;
try {
    $db=getMySQLiConnection();
    if ($editing) {
        $query=eventQuery($db,'SELECT * FROM events WHERE event_id=? LIMIT 1','i',[$id]);
        $original=$query->get_result()->fetch_assoc(); $query->close();
        if (!$original) { http_response_code(404); exit('Event not found. <a href="event-management.php">Return to events</a>'); }
        $event=$original;
    }
    if ($post) {
        if (!eventCsrfValid()) { http_response_code(403); exit('Invalid form token. Return and try again.'); }
        [$values,$errors]=eventFormData();
        $event=array_merge($event,$values);
        $removePoster=($_POST['remove_poster'] ?? '') === '1';
        $hasNewUpload=isset($_FILES['poster']) && is_array($_FILES['poster']) && ($_FILES['poster']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
        if ($removePoster && $hasNewUpload) $errors[]='Choose either Remove current poster or a new poster, not both.';
        if (!$errors) $newPoster=storeValidatedEventPoster($errors);
        if (!$errors) {
            $posterToSave=$newPoster ?? (($removePoster && $editing) ? null : ($editing ? $original['poster_path'] : null));
            $db->begin_transaction();
            if ($editing) {
                $stmt=eventQuery($db,
                    'UPDATE events SET event_name=?,category=?,event_date=?,venue=?,description=?,event_status=?,poster_path=?,updated_at=NOW() WHERE event_id=?',
                    'sssssssi', [$values['event_name'],$values['category'],$values['event_date'],$values['venue'],$values['description'],$values['event_status'],$posterToSave,$id]);
                $stmt->close();
                eventLog($db,$id,'updated',$newPoster ? 'Details and poster updated' : 'Event details updated');
            } else {
                $stmt=eventQuery($db,
                    'INSERT INTO events (event_name,category,event_date,venue,description,event_status,poster_path,updated_at) VALUES (?,?,?,?,?,?,?,NOW())',
                    'sssssss', [$values['event_name'],$values['category'],$values['event_date'],$values['venue'],$values['description'],$values['event_status'],$posterToSave]);
                $id=(int)$db->insert_id; $stmt->close();
                eventLog($db,$id,'created','Event created by admin');
            }
            $db->commit();
            // Delete replaced/removed old files only after the database commit succeeds.
            if ($editing && $original['poster_path'] !== $posterToSave) deleteOwnedEventPoster($original['poster_path']);
            eventNotice('success',$editing?'Event updated successfully (ID '.$id.').':'Event added successfully (ID '.$id.').');
            redirectEvents();
        }
    }
} catch (Throwable $exception) {
    if ($db) { try {$db->rollback();}catch(Throwable $ignored){} }
    deleteOwnedEventPoster($newPoster);
    error_log('Event form failed: '.$exception->getMessage());
    $errors[]='Unable to save event. Check MySQL and import event_management_schema.sql.';
}
$posterShown=eventPosterUrl($editing ? ($original['poster_path'] ?? null) : null);
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $editing?'Edit Event':'Add Event' ?> | Lumiora</title><link rel="stylesheet" href="event-management-style.css"></head><body><main class="shell">
<nav class="toolbar"><a href="event-management.php">&larr; Back to Event Management</a></nav>
<section class="hero"><h1><?= $editing?'Edit Event':'Add New Event' ?></h1><p><?= $editing?'Update details or change the event poster.':'Add an event with optional validated poster upload.' ?> &bull; PHP MySQLi</p></section>
<section class="panel form-panel">
<?php if ($errors): ?><div class="notice error" role="alert"><b>Please fix the following:</b><ul><?php foreach($errors as $err): ?><li><?=evh($err)?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form action="event-form.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?=evh($_SESSION['csrf'])?>">
<input type="hidden" name="event_id" value="<?=$id?>">
<input type="hidden" name="MAX_FILE_SIZE" value="<?=EVENT_POSTER_MAX_BYTES?>">
<div class="form-grid">
<div class="field"><label for="event_name">Event Name *</label><input id="event_name" name="event_name" required minlength="3" maxlength="150" placeholder="e.g. Cloud Computing Workshop" value="<?=evh($event['event_name'])?>"></div>
<div class="field"><label for="category">Event Category *</label><select id="category" name="category" required><?php foreach(['Workshop','Technical','Cultural','Sports','Seminar','Other'] as $category): ?><option value="<?=evh($category)?>" <?=$event['category']===$category?'selected':''?>><?=evh($category)?></option><?php endforeach; ?></select></div>
<div class="field"><label for="event_date">Event Date *</label><input type="date" id="event_date" name="event_date" min="2020-01-01" max="2100-12-31" required value="<?=evh($event['event_date'])?>"></div>
<div class="field"><label for="venue">Venue *</label><input id="venue" name="venue" required minlength="2" maxlength="120" placeholder="e.g. Computer Lab 1" value="<?=evh($event['venue'])?>"></div>
<div class="field"><label for="event_status">Registration Status *</label><select name="event_status" id="event_status"><option value="open" <?=$event['event_status']==='open'?'selected':''?>>Open</option><option value="closed" <?=$event['event_status']==='closed'?'selected':''?>>Closed</option></select></div>
<div class="field"><label for="poster">Event Poster (optional)</label><input id="poster" name="poster" type="file" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"><p class="help">Only JPG, PNG or WebP &bull; maximum 2 MB &bull; image 100–5000 pixels wide/high</p></div>
<div class="field full"><label for="description">Description</label><textarea id="description" name="description" maxlength="2000" rows="4" placeholder="Event information for students"><?=evh($event['description'])?></textarea></div>
</div>
<?php if ($posterShown): ?><div class="poster-current"><b>Current poster</b><img src="<?=evh($posterShown)?>" alt="Current event poster"><label class="checkbox"><input type="checkbox" name="remove_poster" value="1"> Remove current poster (leave upload field empty)</label></div><?php endif; ?>
<p class="help">Server checks real image MIME type and size; uploads get random filenames. Event data and audit entries are written using MySQLi prepared statements.</p>
<div class="form-actions"><button type="submit"><?=$editing?'Save Changes':'Add Event'?></button><a class="btn secondary" href="event-management.php">Cancel</a></div>
</form></section></main></body></html>
