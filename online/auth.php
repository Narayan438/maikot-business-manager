<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax',
        'path' => '/'
    ]);
    session_start();
}

function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['business_id'] = (int)$user['business_id'];
    $_SESSION['name'] = (string)$user['name'];
    $_SESSION['role'] = (string)$user['role'];
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'] ?? '', $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function require_login(): void {
    if (empty($_SESSION['user_id']) || empty($_SESSION['business_id'])) {
        header('Location: login.php');
        exit;
    }
}

function current_business_id(): int {
    return (int)($_SESSION['business_id'] ?? 0);
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
