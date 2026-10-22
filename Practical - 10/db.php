<?php
/** Practical 8: reusable secure MySQL PDO connection.
 * Set DB_HOST, DB_NAME, DB_USER, DB_PASS as environment variables if needed.
 * XAMPP local defaults are root with an empty password; production must differ.
 */
declare(strict_types=1);

function getDatabaseConnection(): PDO
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $database = getenv('DB_NAME') ?: 'studenthub_pr8_db';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS');
    if ($password === false) { $password = ''; }
    $dsn = 'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8mb4';
    return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
