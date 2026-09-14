<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$config = __DIR__ . '/../config/database.php';
if (!is_file($config)) {
    http_response_code(503);
    echo json_encode([
        'ok' => false,
        'error' => 'Database is not configured yet.',
        'hint' => 'Copy online/config/database.example.php to online/config/database.php on the server and fill in the cPanel database credentials.'
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

require_once $config;

function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function request_json(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') return [];
    $data = json_decode($raw, true);
    if (!is_array($data)) json_response(['ok'=>false,'error'=>'Invalid JSON body.'], 400);
    return $data;
}

function require_method(string $method): void {
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($method)) {
        json_response(['ok'=>false,'error'=>'Method not allowed.'], 405);
    }
}
