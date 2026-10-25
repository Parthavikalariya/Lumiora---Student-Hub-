<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
$success = false;
$message = 'Connection failed. Verify XAMPP MySQL, database import and db.php settings.';
try {
    $pdo = getDatabaseConnection();
    $pdo->query('SELECT 1');
    $required = ['students','events','event_registrations'];
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN (?,?,?)');
    $stmt->execute($required);
    $count = (int)$stmt->fetchColumn();
    $success = $count === 3;
    $message = $success ? 'MySQL PDO connection successful! All 3 StudentHub tables were found.' : 'Database connected, but one or more StudentHub tables are missing. Import studenthub_schema.sql.';
} catch (PDOException $e) {
    error_log('StudentHub connection test: ' . $e->getMessage());
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>MySQL Connection Test | Lumiora</title><link rel="stylesheet" href="database-style.css"></head>
<body><main class="db-shell"><a href="dashboard.html">← StudentHub Dashboard</a><section class="db-panel"><p class="eyebrow">Practical 8 · PHP + MySQL</p><h1>Database Connection Test</h1><div class="status <?= $success ? 'ok' : 'error' ?>"><h2><?= $success ? '✓ Connection Successful' : '⚠ Setup Required' ?></h2><p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p></div><p>Connection uses <code>PDO</code>, exception handling and native prepared statements.</p><a class="db-button" href="database-demo.php">Open MySQL Demo</a></section></main></body></html>
