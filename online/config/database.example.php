<?php
// Copy this file to database.php on the hosting server and fill in real credentials.
// Never commit the real database.php file to GitHub.

$host = 'localhost';
$db   = 'YOUR_CPANEL_DATABASE_NAME';
$user = 'YOUR_CPANEL_DATABASE_USER';
$pass = 'YOUR_STRONG_DATABASE_PASSWORD';

$pdo = new PDO(
    "mysql:host={$host};dbname={$db};charset=utf8mb4",
    $user,
    $pass,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);
