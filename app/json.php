<?php
declare(strict_types=1);
function wantsJson(): bool {
    return str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')
        || str_contains(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json');
}
function jsonResponse(array $payload, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function jsonError(string $message, int $status): never {
    jsonResponse(['ok'=>false, 'message'=>$message], $status);
}
function beginPage(): void { if (wantsJson()) { ob_start(); } }
function endPage(array $data = []): void {
    if (wantsJson()) {
        jsonResponse(['ok'=>true, 'html'=>ob_get_clean(), 'data'=>$data, 'csrf'=>$_SESSION['csrf'] ?? null]);
    }
}
if (wantsJson()) {
    set_exception_handler(static function (Throwable $exception): void {
        error_log((string)$exception);
        while (ob_get_level() > 0) { ob_end_clean(); }
        jsonError('The request could not be completed. Please try again.', 500);
    });
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && str_contains(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
    try { $input = json_decode(file_get_contents('php://input'), false, 32, JSON_THROW_ON_ERROR); }
    catch (JsonException $exception) { jsonError('Invalid JSON request.', 400); }
    if (!$input instanceof stdClass) { jsonError('Expected a JSON object.', 400); }
    $_POST = [];
    foreach (get_object_vars($input) as $key=>$value) {
        if (!is_scalar($value) && $value !== null) { jsonError('Form fields must contain simple values.', 400); }
        $_POST[$key] = $value === null ? '' : (string)$value;
    }
}
