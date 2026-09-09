<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
$q=$pdo->query("SELECT (SELECT COUNT(*) FROM products) products,(SELECT COUNT(*) FROM suppliers) suppliers,(SELECT COUNT(*) FROM purchases) purchases,COALESCE((SELECT SUM((qty*purchase_rate)-discount+transport+other_cost) FROM purchases),0) total_purchase");
echo json_encode($q->fetch());