<?php
require_once __DIR__ . '/bootstrap.php';

try {
    $dbOk = (bool)$pdo->query('SELECT 1')->fetchColumn();
    $tables = [];
    foreach (['products','suppliers','purchases','parties','sales','sale_items','payments','users','app_settings'] as $table) {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);
        $tables[$table] = (bool)$stmt->fetchColumn();
    }
    json_response([
        'ok' => $dbOk,
        'database' => $dbOk ? 'connected' : 'not connected',
        'tables' => $tables,
        'php_version' => PHP_VERSION,
        'time' => date('c')
    ]);
} catch (Throwable $e) {
    json_response(['ok'=>false,'error'=>'Health check failed.'], 500);
}
