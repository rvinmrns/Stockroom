<?php
declare(strict_types=1);
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
session_start();
header('Cache-Control: no-store');
header('Vary: Accept');
require_once __DIR__ . '/json.php';
date_default_timezone_set('Asia/Manila');
function e(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function redirect(): never {
    if (wantsJson()) {
        $message = $_SESSION['flash'] ?? 'Done.';
        unset($_SESSION['flash']);
        jsonResponse(['ok'=>true, 'message'=>$message, 'url'=>'stockroom.php', 'csrf'=>$_SESSION['csrf'] ?? null]);
    }
    header('Location: stockroom.php'); exit;
}
function numberInput(string $name, bool $decimal = false): float|int {
    $raw = trim((string) ($_POST[$name] ?? ''));
    if (!preg_match($decimal ? '/^\d{1,8}(\.\d{1,2})?$/' : '/^\d{1,8}$/', $raw)) {
        throw new InvalidArgumentException('Enter a valid non-negative ' . str_replace('_', ' ', $name) . '.');
    }
    return $decimal ? (float) $raw : (int) $raw;
}
try {
    require_once __DIR__ . '/database.php';
    $db = mysqlConnection();
} catch (Throwable $exception) {
    error_log((string) $exception); http_response_code(500);
    if (wantsJson()) { jsonError('Unable to connect to MySQL. Check that the database server is running.', 503); }
    exit('Unable to open inventory. Start MySQL in XAMPP and check the database connection settings.');
}
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
