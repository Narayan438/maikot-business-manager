<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $sql="SELECT pu.id,pu.purchase_date,p.product_name,p.selling_price,s.supplier_name,pu.qty,pu.purchase_rate,pu.discount,pu.transport,pu.other_cost,((pu.qty*pu.purchase_rate)-pu.discount+pu.transport+pu.other_cost) net_cost,(((pu.qty*pu.purchase_rate)-pu.discount+pu.transport+pu.other_cost)/NULLIF(pu.qty,0)) actual_unit_cost FROM purchases pu JOIN products p ON p.id=pu.product_id JOIN suppliers s ON s.id=pu.supplier_id ORDER BY pu.purchase_date DESC,pu.id DESC";
  echo json_encode($pdo->query($sql)->fetchAll()); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $d=json_decode(file_get_contents('php://input'),true)??$_POST;
  try{$st=$pdo->prepare("INSERT INTO purchases(purchase_date,product_id,supplier_id,qty,purchase_rate,discount,transport,other_cost) VALUES(?,?,?,?,?,?,?,?)");$st->execute([$d['purchase_date']??date('Y-m-d'),(int)($d['product_id']??0),(int)($d['supplier_id']??0),(float)($d['qty']??0),(float)($d['purchase_rate']??0),(float)($d['discount']??0),(float)($d['transport']??0),(float)($d['other_cost']??0)]);echo json_encode(['success'=>true]);}
  catch(Throwable $e){http_response_code(400);echo json_encode(['success'=>false,'message'=>'Could not save purchase.']);} exit;
}
http_response_code(405);