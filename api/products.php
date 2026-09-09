<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  echo json_encode($pdo->query("SELECT * FROM products ORDER BY product_name")->fetchAll()); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $d = json_decode(file_get_contents('php://input'), true) ?? $_POST;
  try {
    $st=$pdo->prepare("INSERT INTO products(product_code,product_name,category,brand_model,unit,selling_price) VALUES(?,?,?,?,?,?)");
    $st->execute([trim($d['product_code']??''),trim($d['product_name']??''),trim($d['category']??''),trim($d['brand_model']??''),trim($d['unit']??'Piece'),(float)($d['selling_price']??0)]);
    echo json_encode(['success'=>true]);
  } catch(Throwable $e){http_response_code(400);echo json_encode(['success'=>false,'message'=>'Could not save product.']);}
  exit;
}
http_response_code(405);