<?php
$host = "localhost";
$db   = "maikot_business";
$user = "YOUR_DB_USER";
$pass = "YOUR_DB_PASSWORD";
try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  die("Database connection failed.");
}
?>