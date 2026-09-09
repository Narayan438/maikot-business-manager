<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
$id=(int)($_GET['product_id']??0); if(!$id){echo json_encode([]);exit;}
$st=$pdo->prepare("SELECT pu.purchase_date,s.supplier_name,pu.qty,pu.purchase_rate,pu.discount,pu.transport,pu.other_cost,(((pu.qty*pu.purchase_rate)-pu.discount+pu.transport+pu.other_cost)/NULLIF(pu.qty,0)) actual_unit_cost FROM purchases pu JOIN suppliers s ON s.id=pu.supplier_id WHERE pu.product_id=? ORDER BY actual_unit_cost ASC,pu.purchase_date DESC");
$st->execute([$id]); echo json_encode($st->fetchAll());