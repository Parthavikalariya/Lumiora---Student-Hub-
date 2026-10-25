<?php
/** Lumiora Live Event Studio: JSON API for async GET/POST/PATCH/DELETE. */
declare(strict_types=1);
require_once __DIR__ . '/event-api-support.php';

try {
    requireEventApiAdmin();
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!in_array($method, ['GET', 'POST', 'PATCH', 'DELETE'], true)) {
        header('Allow: GET, POST, PATCH, DELETE');
        throw new EventApiError('HTTP method not allowed.', 405);
    }

    // GET: paginated, searchable, filterable event listing. No page navigation.
    if ($method === 'GET') {
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? 'all';
        $archive = $_GET['archive'] ?? 'active';
        if (!is_string($search) || eventLength($search) > 100 || !is_string($category)
            || !in_array($category, ['', 'Workshop','Technical','Cultural','Sports','Seminar','Other'], true)
            || !is_string($status) || !in_array($status, ['all','open','closed'], true)
            || !is_string($archive) || !in_array($archive, ['active','archived'], true)) {
            throw new EventApiError('Invalid search or filter.', 422);
        }
        $rawPage = $_GET['page'] ?? '1';
        if (!is_string($rawPage) || !ctype_digit($rawPage)) {
            throw new EventApiError('Invalid page number.', 422);
        }
        $page = max(1, min(100000, (int)$rawPage));
        $filters = [trim($search), $category, $status, $archive];
        [$where, $types, $values] = eventWhere($filters);
        $db = getMySQLiConnection();
        $q = eventQuery($db, 'SELECT COUNT(*) AS total FROM events e' . $where, $types, $values);
        $total = (int)$q->get_result()->fetch_assoc()['total'];
        $q->close();
        $perPage = 6;
        $pages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $pages);
        $offset = ($page - 1) * $perPage;
        $q = eventQuery($db,
            'SELECT e.event_id,e.event_name,e.category,e.event_date,e.venue,e.description,e.poster_path,e.event_status,e.is_archived, '
            . '(SELECT COUNT(*) FROM event_registrations r WHERE r.event_id=e.event_id) AS registrations '
            . 'FROM events e' . $where . ' ORDER BY e.event_id DESC LIMIT ? OFFSET ?',
            $types . 'ii', array_merge($values, [$perPage, $offset]));
        $rows = $q->get_result()->fetch_all(MYSQLI_ASSOC);
        $q->close();
        $q = eventQuery($db, 'SELECT COUNT(*) AS total, '
            . 'COALESCE(SUM(is_archived=0),0) AS active, '
            . 'COALESCE(SUM(is_archived=0 AND event_status=\'open\'),0) AS open_count, '
            . 'COALESCE(SUM(is_archived=1),0) AS archived FROM events');
        $counts = $q->get_result()->fetch_assoc();
        $q->close();
        $db->close();
        jsonReply(200, [
            'ok' => true, 'message' => 'Event list loaded.',
            'data' => array_map('eventPublicRow', $rows),
            'pagination' => ['page'=>$page, 'pages'=>$pages, 'per_page'=>$perPage, 'total'=>$total],
            'counts' => ['total'=>(int)$counts['total'], 'active'=>(int)$counts['active'],
                         'open'=>(int)$counts['open_count'], 'archived'=>(int)$counts['archived']],
        ]);
    }

    verifyEventApiCsrf();
    $input = jsonRequestBody();
    $db = getMySQLiConnection();

    if ($method === 'POST' && ($input['action'] ?? 'create') === 'create') {
        $data = validateEventJson($input);
        $db->begin_transaction();
        try {
            $stmt = eventQuery($db,
                'INSERT INTO events (event_name,category,event_date,venue,description,event_status) VALUES (?,?,?,?,?,?)',
                'ssssss', [$data['event_name'],$data['category'],$data['event_date'],
                           $data['venue'],$data['description'],$data['event_status']]);
            $newId = (int)$db->insert_id;
            $stmt->close();
            eventLog($db, $newId, 'created', 'Event created through Live Event Studio');
            $db->commit();
        } catch (Throwable $error) { $db->rollback(); throw $error; }
        jsonReply(201, ['ok'=>true,'message'=>'Event added successfully without a page reload.','event_id'=>$newId]);
    }

    if ($method === 'PATCH') {
        $id = apiPositiveId($input['event_id'] ?? null);
        $data = validateEventJson($input);
        $db->begin_transaction();
        try {
            $stmt = eventQuery($db,
                'UPDATE events SET event_name=?,category=?,event_date=?,venue=?,description=?,event_status=?,updated_at=NOW() '
                . 'WHERE event_id=? AND is_archived=0',
                'ssssssi', [$data['event_name'],$data['category'],$data['event_date'],
                            $data['venue'],$data['description'],$data['event_status'],$id]);
            $changed = $stmt->affected_rows; $stmt->close();
            if (!$changed) {
                $db->rollback();
                throw new EventApiError('Event not found, archived, or details are unchanged.', 409);
            }
            eventLog($db, $id, 'updated', 'Event updated through Live Event Studio');
            $db->commit();
        } catch (Throwable $error) { try { $db->rollback(); } catch (Throwable $ignored) {} throw $error; }
        jsonReply(200, ['ok'=>true,'message'=>'Event updated successfully without a page reload.','event_id'=>$id]);
    }

    if ($method === 'DELETE') {
        $id = apiPositiveId($input['event_id'] ?? null);
        // Soft delete avoids breaking foreign key references from event registrations.
        $db->begin_transaction();
        try {
            $stmt = eventQuery($db,
                'UPDATE events SET is_archived=1,updated_at=NOW() WHERE event_id=? AND is_archived=0', 'i', [$id]);
            $changed = $stmt->affected_rows; $stmt->close();
            if (!$changed) throw new EventApiError('Event not found or already archived.', 409);
            eventLog($db, $id, 'archived', 'Soft delete through Live Event Studio');
            $db->commit();
        } catch (Throwable $error) { try { $db->rollback(); } catch (Throwable $ignored) {} throw $error; }
        jsonReply(200, ['ok'=>true,'message'=>'Event archived. Existing registrations are preserved.','event_id'=>$id]);
    }

    if ($method === 'POST' && ($input['action'] ?? '') === 'restore') {
        $id = apiPositiveId($input['event_id'] ?? null);
        $db->begin_transaction();
        try {
            $stmt = eventQuery($db, 'UPDATE events SET is_archived=0,updated_at=NOW() WHERE event_id=? AND is_archived=1', 'i', [$id]);
            $changed = $stmt->affected_rows; $stmt->close();
            if (!$changed) throw new EventApiError('Event not found or already active.', 409);
            eventLog($db, $id, 'restored', 'Restored through Live Event Studio');
            $db->commit();
        } catch (Throwable $error) { try { $db->rollback(); } catch (Throwable $ignored) {} throw $error; }
        jsonReply(200, ['ok'=>true,'message'=>'Event restored successfully.','event_id'=>$id]);
    }

    if ($method === 'POST' && ($input['action'] ?? '') === 'change_status') {
        $id = apiPositiveId($input['event_id'] ?? null);
        $status = apiField($input, 'event_status');
        if (!in_array($status, ['open', 'closed'], true)) throw new EventApiError('Invalid event status.');
        $db->begin_transaction();
        try {
            $stmt = eventQuery($db,
                'UPDATE events SET event_status=?,updated_at=NOW() WHERE event_id=? AND is_archived=0 AND event_status<>?',
                'sis', [$status, $id, $status]);
            $changed = $stmt->affected_rows; $stmt->close();
            if (!$changed) throw new EventApiError('Event not found or status already selected.', 409);
            eventLog($db, $id, $status === 'open' ? 'opened' : 'closed', 'Status updated through Live Event Studio');
            $db->commit();
        } catch (Throwable $error) { try { $db->rollback(); } catch (Throwable $ignored) {} throw $error; }
        jsonReply(200, ['ok'=>true,'message'=>'Event status changed to ' . ucfirst($status) . '.','event_id'=>$id]);
    }
    throw new EventApiError('Unsupported API action.', 400);
} catch (EventApiError $error) {
    jsonReply($error->status, ['ok'=>false,'message'=>$error->getMessage()]);
} catch (Throwable $error) {
    error_log('Lumiora Event API: ' . $error->getMessage());
    jsonReply(500, ['ok'=>false,'message'=>'Database operation failed. Check XAMPP MySQL and event tables.']);
}
