<?php
/** Practical 9: MySQLi connection for registration; keep Practical 8 PDO db.php intact. */
declare(strict_types=1);
function getMySQLiConnection(): mysqli {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $database = getenv('DB_NAME') ?: 'studenthub_pr8_db';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS');
    $db = new mysqli($host, $user, $password === false ? '' : $password, $database);
    $db->set_charset('utf8mb4');
    return $db;
}
