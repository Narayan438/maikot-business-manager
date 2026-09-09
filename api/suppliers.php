<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  echo json_encode($pdo->query("SELECT * FROM suppliers ORDER BY supplier_name")->fetchAll()); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $d = json_decode(file_get_contents('php://input'), true) ?? $_POST;
  try {
    $st=$pdo->prepare("INSERT INTO suppliers(supplier_code,supplier_name,contact_person,phone,address,pan_vat,remarks) VALUES(?,?,?,?,?,?,?)");
    $st->execute([trim($d['supplier_code']??''),trim($d['supplier_name']??''),trim($d['contact_person']??''),trim($d['phone']??''),trim($d['address']??''),trim($d['pan_vat']??''),trim($d['remarks']??'')]);
    echo json_encode(['success'=>true]);
  } catch(Throwable $e){http_response_code(400);echo json_encode(['success'=>false,'message'=>'Could not save supplier.']);}
  exit;
}
http_response_code(405);