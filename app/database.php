<?php
declare(strict_types=1);

function mysqlConnection(bool $selectDatabase = true): PDO {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'inventory_hq';
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) { throw new RuntimeException('Invalid DB_NAME.'); }
    $dsn = 'mysql:host=' . $host . ';port=' . $port . ';charset=utf8mb4' . ($selectDatabase ? ';dbname=' . $name : '');
    return new PDO($dsn, getenv('DB_USER') ?: 'root', getenv('DB_PASSWORD') ?: '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_FOUND_ROWS => true,
    ]);
}
