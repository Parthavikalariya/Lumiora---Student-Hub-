<?php
/** Shared JSON API validation, authentication, and HTTP responses. */
declare(strict_types=1);
require_once __DIR__ . '/event-records.php';

final class EventApiError extends RuntimeException {
    public function __construct(string $message, public int $status = 422) {
        parent::__construct($message);
    }
}

function jsonReply(int $status, array $payload): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, private, max-age=0');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** API endpoints must return JSON, never a redirected HTML login page. */
function requireEventApiAdmin(): void {
    startAuthSession();
    $last = $_SESSION['last_activity'] ?? null;
    if (isset($_SESSION['user_id']) && (!is_int($last) || (time() - $last) > SESSION_IDLE_LIMIT)) {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'], $_SESSION['last_activity']);
        session_regenerate_id(true);
        throw new EventApiError('Session expired. Sign in again.', 401);
    }
    if (empty($_SESSION['user_id'])) {
        throw new EventApiError('Please sign in as an administrator.', 401);
    }
    try { $valid=refreshSessionIdentity(); }
    catch(Throwable $error) { throw new EventApiError('Account verification unavailable.',503); }
    if (!$valid) { throw new EventApiError('Account disabled or unavailable.',401); }
    if (($_SESSION['role'] ?? '') !== 'admin') {
        throw new EventApiError('Administrator access required.', 403);
    }
    $_SESSION['last_activity'] = time();
}

function verifyEventApiCsrf(): void {
    $given = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($given) || !is_string($_SESSION['csrf'] ?? null)
        || !hash_equals($_SESSION['csrf'], $given)) {
        throw new EventApiError('Security token invalid. Refresh the page and retry.', 403);
    }
}

function jsonRequestBody(): array {
    $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
    if ($contentType !== 'application/json') {
        throw new EventApiError('Send JSON with Content-Type: application/json.', 415);
    }
    if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 16384) {
        throw new EventApiError('Request is too large.', 413);
    }
    $raw = file_get_contents('php://input', false, null, 0, 16385);
    if (!is_string($raw) || strlen($raw) > 16384) {
        throw new EventApiError('Request is too large.', 413);
    }
    try {
        $decoded = json_decode($raw, false, 16, JSON_THROW_ON_ERROR);
    } catch (JsonException $error) {
        throw new EventApiError('Invalid JSON request.', 400);
    }
    if (!$decoded instanceof stdClass) {
        throw new EventApiError('JSON object required.', 400);
    }
    return get_object_vars($decoded);
}

function apiPositiveId(mixed $value): int {
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) throw new EventApiError('Valid event ID is required.');
    return (int)$id;
}

function apiField(array $payload, string $key): string {
    $value = $payload[$key] ?? '';
    if (!is_string($value)) throw new EventApiError('Invalid ' . $key . ' value.');
    return trim($value);
}

/** Mirrors the validation rules of existing event-form.php. */
function validateEventJson(array $input): array {
    $fields = [];
    foreach (['event_name', 'category', 'event_date', 'venue', 'description', 'event_status'] as $field) {
        $fields[$field] = apiField($input, $field);
    }
    $errors = [];
    if (eventLength($fields['event_name']) < 3 || eventLength($fields['event_name']) > 150) {
        $errors[] = 'Event name must contain 3–150 characters.';
    }
    if (!in_array($fields['category'], ['Workshop', 'Technical', 'Cultural', 'Sports', 'Seminar', 'Other'], true)) {
        $errors[] = 'Choose a valid event category.';
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $fields['event_date']);
    if (!$date || $date->format('Y-m-d') !== $fields['event_date']
        || $fields['event_date'] < '2020-01-01' || $fields['event_date'] > '2100-12-31') {
        $errors[] = 'Choose a valid event date between 2020 and 2100.';
    }
    if (eventLength($fields['venue']) < 2 || eventLength($fields['venue']) > 120) {
        $errors[] = 'Venue must contain 2–120 characters.';
    }
    if (eventLength($fields['description']) > 2000) {
        $errors[] = 'Description cannot exceed 2000 characters.';
    }
    if (!in_array($fields['event_status'], ['open', 'closed'], true)) {
        $errors[] = 'Choose an Open or Closed status.';
    }
    if ($errors) throw new EventApiError(implode(' ', $errors), 422);
    return $fields;
}

/** Only server-generated poster filenames are shared through JSON. */
function eventPublicRow(array $row): array {
    return [
        'event_id' => (int)$row['event_id'],
        'event_name' => (string)$row['event_name'],
        'category' => (string)$row['category'],
        'event_date' => (string)$row['event_date'],
        'venue' => (string)$row['venue'],
        'description' => (string)($row['description'] ?? ''),
        'poster_path' => eventPosterUrl($row['poster_path'] ?? null),
        'event_status' => (string)$row['event_status'],
        'is_archived' => (int)$row['is_archived'],
        'registrations' => (int)($row['registrations'] ?? 0),
    ];
}
