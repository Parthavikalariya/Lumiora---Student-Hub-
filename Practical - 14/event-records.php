<?php
/** Shared admin-only event CRUD helpers. */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin-audit-helper.php';

function requireEventAdmin(): void { requireLogin('admin'); }
function evh(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function eventCsrfValid(): bool {
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
        && is_string($_POST['csrf'] ?? null)
        && hash_equals((string)($_SESSION['csrf'] ?? ''), $_POST['csrf']);
}
function eventNotice(string $type, string $message): void {
    $_SESSION['event_notice'] = ['type'=>$type === 'success' ? 'success' : 'error', 'text'=>$message];
}
function takeEventNotice(): ?array {
    $notice = $_SESSION['event_notice'] ?? null;
    unset($_SESSION['event_notice']);
    return is_array($notice) ? $notice : null;
}
function redirectEvents(): never { header('Location: event-management.php', true, 303); exit; }
function eventQuery(mysqli $db, string $sql, string $types = '', array $params = []): mysqli_stmt {
    $stmt = $db->prepare($sql);
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt;
}
function eventLog(mysqli $db, int $eventId, string $action, ?string $details=null): void {
    $adminId = (int)($_SESSION['user_id'] ?? 0);
    $stmt = eventQuery($db,
        'INSERT INTO event_change_audit (event_id, admin_user_id, action, details) VALUES (?,?,?,?)',
        'iiss', [$eventId, $adminId, $action, $details]);
    $stmt->close();
    // Same transaction as the event operation: record P14 central audit atomically.
    writeAdminAudit($db, $adminId, 'event_' . $action, 'event', $eventId, $details ?? 'Event ' . $action);
}
function eventSearchFilters(): array {
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $status = $_GET['status'] ?? 'all';
    $archive = $_GET['archive'] ?? 'active';
    return [
        is_string($search) ? substr(trim($search), 0, 100) : '',
        is_string($category) ? substr(trim($category), 0, 40) : '',
        is_string($status) && in_array($status, ['all','open','closed'], true) ? $status : 'all',
        is_string($archive) && in_array($archive, ['active','archived','all'], true) ? $archive : 'active',
    ];
}
function eventWhere(array $filters): array {
    [$search, $category, $status, $archive] = $filters;
    $parts=[]; $params=[]; $types='';
    if ($archive !== 'all') {
        $parts[]='e.is_archived=?'; $params[]=$archive === 'archived' ? 1 : 0; $types.='i';
    }
    if ($status !== 'all') { $parts[]='e.event_status=?'; $params[]=$status; $types.='s'; }
    if ($category !== '') { $parts[]='e.category=?'; $params[]=$category; $types.='s'; }
    if ($search !== '') {
        $parts[]='(e.event_name LIKE ? OR e.venue LIKE ? OR e.description LIKE ?)';
        $like='%'.$search.'%'; array_push($params,$like,$like,$like); $types.='sss';
    }
    return [$parts ? ' WHERE '.implode(' AND ',$parts) : '', $types, $params];
}
function eventLength(string $value): int { return function_exists('mb_strlen') ? mb_strlen($value,'UTF-8') : strlen($value); }
function eventFormData(): array {
    $field=static function(string $key):string {
        $value=$_POST[$key] ?? '';
        return is_string($value) ? trim($value) : '';
    };
    $data=[
        'event_name'=>$field('event_name'), 'category'=>$field('category'),
        'event_date'=>$field('event_date'), 'venue'=>$field('venue'),
        'description'=>$field('description'), 'event_status'=>$field('event_status'),
    ];
    $errors=[];
    if (eventLength($data['event_name'])<3 || eventLength($data['event_name'])>150) $errors[]='Event name must be 3-150 characters.';
    if (!in_array($data['category'],['Workshop','Technical','Cultural','Sports','Seminar','Other'],true)) $errors[]='Choose a valid event category.';
    $date=DateTimeImmutable::createFromFormat('!Y-m-d',$data['event_date']);
    if (!$date || $date->format('Y-m-d') !== $data['event_date'] || $data['event_date']<'2020-01-01' || $data['event_date']>'2100-12-31') $errors[]='Choose a valid event date.';
    if (eventLength($data['venue'])<2 || eventLength($data['venue'])>120) $errors[]='Venue must be 2-120 characters.';
    if (eventLength($data['description'])>2000) $errors[]='Description cannot exceed 2000 characters.';
    if (!in_array($data['event_status'], ['open','closed'],true)) $errors[]='Choose Open or Closed status.';
    return [$data,$errors];
}
function eventPosterUrl(mixed $path): ?string {
    if (!is_string($path) || !preg_match('~^event-posters/[a-f0-9]{32}\.(jpg|png|webp)$~D', $path)) return null;
    // Preserve screenshots/posters from the previous practical's upload folder.
    // Older files are user uploads and are not included in a downloadable source ZIP.
    if (!is_file(__DIR__ . '/' . $path)) {
        $olderRoot = dirname(__DIR__) . '/StudentHub_MySQL_Practical_12/';
        if (is_file($olderRoot . $path)) return '../StudentHub_MySQL_Practical_12/' . $path;
    }
    return $path;
}
