<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_login();
require __DIR__.'/config/database.php';

$businessId=current_business_id();
$error=''; $success=''; $summary=[];
function n($v): float { return is_numeric($v)?(float)$v:0.0; }
function s($v): string { return trim((string)($v??'')); }

if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!verify_csrf((string)($_POST['csrf']??''))) $error='Session expired. Please refresh and try again.';
 elseif(!isset($_FILES['backup'])||$_FILES['backup']['error']!==UPLOAD_ERR_OK) $error='Please choose a valid MeroKhata JSON backup file.';
 else{
  $data=json_decode((string)file_get_contents($_FILES['backup']['tmp_name']),true);
  $keys=['products','suppliers','purchases','sales','parties','payments'];
  foreach($keys as $k){if(!isset($data[$k])||!is_array($data[$k])){$error='This is not a valid Maikot Business Manager backup.';break;}}
  if(!$error) try{
   $pdo->beginTransaction();
   foreach(['sales','payments','purchases','parties','suppliers','products'] as $table){$q=$pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE business_id=?");$q->execute([$businessId]);if((int)$q->fetchColumn()>0)throw new RuntimeException('Import stopped: this company already contains business data. Import is allowed only into an empty company.');}
   $q=$pdo->prepare('SELECT COUNT(*) FROM sale_items si JOIN sales s ON s.id=si.sale_id WHERE s.business_id=?');$q->execute([$businessId]);if((int)$q->fetchColumn()>0)throw new RuntimeException('Import stopped: sale items already exist for this company.');

   $pm=[];$sm=[];$tm=[];
   $ip=$pdo->prepare('INSERT INTO products(business_id,product_code,product_name,main_category,category,barcode,brand_model,unit,selling_price,reorder_level,is_active) VALUES(?,?,?,?,?,?,?,?,?,?,1)');
   foreach($data['products'] as $x){$ip->execute([$businessId,s($x['product_code']??''),s($x['product_name']??''),s($x['main_category']??'')?:null,s($x['category']??'')?:null,s($x['barcode']??'')?:null,s($x['brand_model']??'')?:null,s($x['unit']??'Piece')?:'Piece',n($x['selling_price']??0),n($x['reorder_level']??0)]);$pm[(string)$x['id']]=(int)$pdo->lastInsertId();}

   $is=$pdo->prepare('INSERT INTO suppliers(business_id,supplier_code,supplier_name,contact_person,phone,address,pan_vat,is_active) VALUES(?,?,?,?,?,?,?,1)');
   foreach($data['suppliers'] as $x){$is->execute([$businessId,s($x['supplier_code']??''),s($x['supplier_name']??''),s($x['contact_person']??'')?:null,s($x['phone']??'')?:null,s($x['address']??'')?:null,s($x['pan_vat']??'')?:null]);$sm[(string)$x['id']]=(int)$pdo->lastInsertId();}

   $it=$pdo->prepare('INSERT INTO parties(business_id,party_code,party_name,phone,address,pan_vat,opening_balance,is_active) VALUES(?,?,?,?,?,?,?,1)');
   foreach($data['parties'] as $x){$it->execute([$businessId,s($x['party_code']??''),s($x['party_name']??''),s($x['phone']??'')?:null,s($x['address']??'')?:null,s($x['pan_vat']??'')?:null,n($x['opening_balance']??0)]);$tm[(string)$x['id']]=(int)$pdo->lastInsertId();}

   $iu=$pdo->prepare('INSERT INTO purchases(business_id,purchase_date,product_id,supplier_id,qty,purchase_unit,units_per_unit,purchase_rate,discount,transport,transport_mode,other_cost,other_cost_mode) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)');
   foreach($data['purchases'] as $x){$pid=$pm[(string)($x['product_id']??'')]??0;$sid=$sm[(string)($x['supplier_id']??'')]??0;if(!$pid||!$sid)throw new RuntimeException('A purchase references a missing product or supplier.');$iu->execute([$businessId,s($x['purchase_date']??''),$pid,$sid,n($x['qty']??0),s($x['purchase_unit']??'Piece')?:'Piece',n($x['units_per_unit']??1)?:1,n($x['purchase_rate']??0),n($x['discount']??0),n($x['transport']??0),s($x['transport_mode']??'total')?:'total',n($x['other_cost']??0),s($x['other_cost_mode']??'total')?:'total']);}

   $isa=$pdo->prepare("INSERT INTO sales(business_id,bill_no,sale_date,party_id,customer_name,customer_phone,customer_address,customer_pan,payment_type,payment_reference,bank_wallet,discount,vat_enabled,vat_rate,vat_amount,paid_amount,due_amount,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'posted')");
   $ii=$pdo->prepare('INSERT INTO sale_items(sale_id,product_id,qty,rate) VALUES(?,?,?,?)');
   foreach($data['sales'] as $x){$subtotal=0;foreach(($x['items']??[]) as $z)$subtotal+=n($z['qty']??0)*n($z['rate']??0);$discount=n($x['discount']??0);$vat=n($x['vat_amount']??0);$grand=max(0,$subtotal-$discount+$vat);$paid=array_key_exists('paid_amount',$x)?n($x['paid_amount']):((s($x['payment_type']??'Cash')==='Credit')?0:$grand);$due=array_key_exists('due_amount',$x)?n($x['due_amount']):max(0,$grand-$paid);$old=(string)($x['party_id']??'');$party=$old!==''?($tm[$old]??null):null;$isa->execute([$businessId,s($x['bill_no']??''),s($x['sale_date']??''),$party,s($x['customer_name']??'Cash Customer')?:'Cash Customer',s($x['customer_phone']??'')?:null,s($x['customer_address']??'')?:null,s($x['customer_pan']??'')?:null,s($x['payment_type']??'Cash')?:'Cash',s($x['payment_reference']??'')?:null,s($x['bank_wallet']??'')?:null,$discount,!empty($x['vat_enabled'])?1:0,n($x['vat_rate']??0),$vat,$paid,$due]);$saleId=(int)$pdo->lastInsertId();foreach(($x['items']??[]) as $z){$pid=$pm[(string)($z['product_id']??'')]??0;if(!$pid)throw new RuntimeException('A sale references a missing product.');$ii->execute([$saleId,$pid,n($z['qty']??0),n($z['rate']??0)]);}}

   $ipy=$pdo->prepare('INSERT INTO payments(business_id,party_id,payment_date,amount,mode,reference,bank_wallet,note) VALUES(?,?,?,?,?,?,?,?)');
   foreach($data['payments'] as $x){$party=$tm[(string)($x['party_id']??'')]??0;if(!$party)throw new RuntimeException('A payment references a missing party.');$ipy->execute([$businessId,$party,s($x['payment_date']??''),n($x['amount']??0),s($x['payment_mode']??$x['mode']??'Cash')?:'Cash',s($x['reference_no']??$x['reference']??'')?:null,s($x['bank_wallet']??'')?:null,s($x['note']??'')?:null]);}

   $pdo->commit();$summary=['Products'=>count($data['products']),'Suppliers'=>count($data['suppliers']),'Purchases'=>count($data['purchases']),'Sales'=>count($data['sales']),'Parties'=>count($data['parties']),'Payments'=>count($data['payments'])];$success='LocalStorage backup imported successfully.';
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error=$e instanceof RuntimeException?$e->getMessage():'Import failed. No data was saved. Please report this error before retrying.';}
 }
}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Import Local Backup • MeroKhata</title><link rel="stylesheet" href="assets/css/style.css"><style>.wrap{max-width:760px;margin:35px auto;padding:20px}.card{background:#fff;padding:25px;border-radius:14px;box-shadow:0 6px 24px #0001}.ok{background:#ecfdf3;color:#166534;padding:12px;border-radius:8px}.err{background:#fff0f0;color:#991b1b;padding:12px;border-radius:8px}.warn{background:#fff8e6;padding:12px;border-radius:8px}.summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:10px;margin:15px 0}.summary div{background:#f3f6fa;padding:12px;border-radius:8px}input[type=file]{width:100%;padding:12px;border:1px solid #ccd3dd;border-radius:8px;margin:12px 0}button{padding:12px 18px;background:#174f9b;color:#fff;border:0;border-radius:8px;font-weight:700}</style></head><body><div class="wrap"><div class="card"><h1>Import LocalStorage Backup</h1><p>This one-time tool moves the JSON backup into the currently logged-in company.</p><div class="warn"><b>Safety:</b> Import works only when this company has no Products, Suppliers, Purchases, Parties, Sales or Payments. Any error rolls back the entire import.</div><?php if($error):?><p class="err"><?=htmlspecialchars($error)?></p><?php endif;?><?php if($success):?><div class="ok"><b><?=htmlspecialchars($success)?></b><div class="summary"><?php foreach($summary as $k=>$v):?><div><b><?=htmlspecialchars($k)?></b><br><?=intval($v)?></div><?php endforeach;?></div><a href="index.php">Return to Dashboard</a></div><?php else:?><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token())?>"><label><b>Choose Maikot Backup JSON</b></label><input type="file" name="backup" accept="application/json,.json" required><button type="submit">Validate & Import</button></form><?php endif;?></div></div></body></html>