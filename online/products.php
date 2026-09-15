<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_login();
$config=__DIR__.'/config/database.php';
if(!is_file($config)){http_response_code(503);exit('Database configuration is not ready.');}
require $config;
$bid=current_business_id();
$notice='';$error='';$edit=null;

if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!verify_csrf((string)($_POST['csrf']??''))) $error='Session expired. Please try again.';
 else{
  $action=(string)($_POST['action']??'save');
  $id=(int)($_POST['id']??0);
  if($action==='deactivate' && $id>0){
   $q=$pdo->prepare('SELECT COUNT(*) FROM purchases WHERE business_id=? AND product_id=?');$q->execute([$bid,$id]);$pc=(int)$q->fetchColumn();
   $q=$pdo->prepare("SELECT COUNT(*) FROM sale_items si JOIN sales s ON s.id=si.sale_id WHERE s.business_id=? AND si.product_id=?");$q->execute([$bid,$id]);$sc=(int)$q->fetchColumn();
   $q=$pdo->prepare('UPDATE products SET is_active=0 WHERE id=? AND business_id=?');$q->execute([$id,$bid]);
   $notice=($pc||$sc)?'Product is in use, so it was safely made inactive instead of deleting history.':'Product made inactive.';
  } else {
   $name=trim((string)($_POST['product_name']??''));$main=trim((string)($_POST['main_category']??''));$cat=trim((string)($_POST['category']??''));$code=strtoupper(trim((string)($_POST['product_code']??'')));
   $barcode=trim((string)($_POST['barcode']??''));$brand=trim((string)($_POST['brand_model']??''));$unit=trim((string)($_POST['unit']??'Piece'));$price=(float)($_POST['selling_price']??0);$reorder=(float)($_POST['reorder_level']??0);
   if($name===''||$main===''||$cat===''||$code==='') $error='Product Name, Main Category, Sub-category and Product Code are required.';
   elseif($price<0||$reorder<0)$error='Price and reorder level cannot be negative.';
   else try{
    if($id>0){$q=$pdo->prepare('UPDATE products SET product_code=?,product_name=?,main_category=?,category=?,barcode=?,brand_model=?,unit=?,selling_price=?,reorder_level=?,is_active=1 WHERE id=? AND business_id=?');$q->execute([$code,$name,$main,$cat,$barcode?:null,$brand?:null,$unit,$price,$reorder,$id,$bid]);$notice='Product updated.';}
    else{$q=$pdo->prepare('INSERT INTO products(business_id,product_code,product_name,main_category,category,barcode,brand_model,unit,selling_price,reorder_level) VALUES(?,?,?,?,?,?,?,?,?,?)');$q->execute([$bid,$code,$name,$main,$cat,$barcode?:null,$brand?:null,$unit,$price,$reorder]);$notice='Product saved.';}
   }catch(PDOException $e){$error=($e->getCode()==='23000')?'Product code or barcode already exists in this business.':'Product could not be saved.';}
  }
 }
}
if(isset($_GET['edit'])){$q=$pdo->prepare('SELECT * FROM products WHERE id=? AND business_id=?');$q->execute([(int)$_GET['edit'],$bid]);$edit=$q->fetch()?:null;}
$q=$pdo->prepare('SELECT * FROM products WHERE business_id=? ORDER BY is_active DESC,product_name');$q->execute([$bid]);$products=$q->fetchAll();
$categories=[
 'Stationery'=>['Pen','Pencil','Marker','Eraser','Sharpener','Ruler','Geometry','File','Adhesive','Other Stationery'],
 'Paper'=>['Photocopy Paper','Colour Paper','Cardboard Paper','Other Paper'],
 'Notebook'=>['Exercise Copy','Spiral Copy','Register','Other Notebook'],
 'Books'=>['Course Book','Practice Book','Question Collection','Other Book'],
 'Art & Craft'=>['Colour','Brush','Craft Material','Tape','Other Art & Craft'],
 'Sports'=>['Volleyball','Football','Badminton','Shuttlecock','Net','Table Tennis','Other Sports'],
 'Toys'=>['Toy','Educational Toy','Other Toy'],
 'Office Accessories'=>['Name Plate','Office Accessory','Other Office'],
 'ID/Card Accessories'=>['Licence Holder','ID Card Accessory','Other Card Accessory'],
 'Electrical'=>['Multi-Plug','Electrical Accessory','Other Electrical'],
 'Machine & Tools'=>['Stitch Machine','Hot Glue Machine','Tool','Other Machine'],
 'Ladies Accessories'=>['Ribbon','Other Ladies Accessory'],
 'Ink & Refill'=>['Ink','Refill','Other Ink'],
 'Computer Accessories'=>['Computer Accessory','Cable','Other Computer'],
 'Other'=>['Other']
];
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Products • MeroKhata</title><link rel="stylesheet" href="assets/css/style.css"><style>.accountbar{font-size:12px;margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.18)}.accountbar a{color:#fff}.msg{padding:10px;border-radius:8px;margin-bottom:12px}.ok{background:#ecfdf3;color:#166534}.err{background:#fff0f0;color:#991b1b}.inactive{opacity:.55}.smallbtn{display:inline-block;padding:6px 9px;border-radius:6px;text-decoration:none;border:1px solid #ccd3dd;color:#223;background:#fff;font-size:12px}.danger{color:#991b1b}</style></head><body><aside class="sidebar"><div class="brand">MeroKhata</div><div class="subbrand">Online Database</div><div class="nav"><button onclick="location.href='index.php'">Dashboard</button><button class="active">Products</button><button>Suppliers</button><button>Purchase Entry</button><button>Party / Customer</button><button>Sales & Billing</button><button>Sales Register</button><button>Sales Reports</button><button>Stock / Inventory</button><button>Price Comparison</button></div><div class="accountbar"><?=htmlspecialchars($_SESSION['name']??'User')?> · <?=htmlspecialchars(ucfirst($_SESSION['role']??''))?><br><a href="logout.php">Logout</a></div></aside><main class="main"><div class="topbar"><h1>Products</h1><div class="muted">Same product workflow as LocalStorage master</div></div><section class="panel active"><?php if($notice):?><div class="msg ok"><?=htmlspecialchars($notice)?></div><?php endif;?><?php if($error):?><div class="msg err"><?=htmlspecialchars($error)?></div><?php endif;?><div class="box"><h3><?=$edit?'Edit Product':'Add Product'?></h3><div class="notice"><b>Category System:</b> Select Main Category, then Sub-category. Product code can be entered now; auto-code will be matched with the master workflow next.</div><form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token())?>"><input type="hidden" name="id" value="<?=htmlspecialchars((string)($edit['id']??0))?>"><input type="hidden" name="action" value="save"><div class="grid"><label>Product Name<input name="product_name" required value="<?=htmlspecialchars((string)($edit['product_name']??''))?>"></label><label>Main Category<select id="mainCategory" name="main_category" required><option value="">Select Main Category</option><?php foreach($categories as $m=>$subs):?><option <?=($edit['main_category']??'')===$m?'selected':''?>><?=htmlspecialchars($m)?></option><?php endforeach;?></select></label><label>Sub-category<select id="subCategory" name="category" required data-current="<?=htmlspecialchars((string)($edit['category']??''))?>"><option value="">Select Sub-category</option></select></label><label>Product Code<input name="product_code" required value="<?=htmlspecialchars((string)($edit['product_code']??''))?>"></label><label>Barcode<input name="barcode" value="<?=htmlspecialchars((string)($edit['barcode']??''))?>"></label><label>Brand / Model<input name="brand_model" value="<?=htmlspecialchars((string)($edit['brand_model']??''))?>"></label><label>Base Stock Unit<select name="unit"><?php foreach(['Piece','Packet','Box','Ream','Set','Pair','Bundle'] as $u):?><option <?=($edit['unit']??'Piece')===$u?'selected':''?>><?=$u?></option><?php endforeach;?></select></label><label>Selling Price / Base Unit<input name="selling_price" type="number" step="0.01" min="0" value="<?=htmlspecialchars((string)($edit['selling_price']??0))?>"></label><label>Reorder Level<input name="reorder_level" type="number" step="0.01" min="0" value="<?=htmlspecialchars((string)($edit['reorder_level']??0))?>"></label></div><div class="actions"><button class="primary">Save Product</button><?php if($edit):?><button type="button" onclick="location.href='products.php'">Cancel Edit</button><?php endif;?></div></form></div><div class="box"><h3>Product List</h3><table><thead><tr><th>Code</th><th>Product</th><th>Main Category</th><th>Sub-category</th><th>Barcode</th><th>Brand/Model</th><th>Base Unit</th><th>Selling Price</th><th>Reorder</th><th>Action</th></tr></thead><tbody><?php foreach($products as $p):?><tr class="<?=$p['is_active']?'':'inactive'?>"><td><?=htmlspecialchars($p['product_code'])?></td><td><?=htmlspecialchars($p['product_name'])?><?=$p['is_active']?'':' (Inactive)'?></td><td><?=htmlspecialchars((string)$p['main_category'])?></td><td><?=htmlspecialchars((string)$p['category'])?></td><td><?=htmlspecialchars((string)$p['barcode'])?></td><td><?=htmlspecialchars((string)$p['brand_model'])?></td><td><?=htmlspecialchars($p['unit'])?></td><td>Rs. <?=number_format((float)$p['selling_price'],2)?></td><td><?=number_format((float)$p['reorder_level'],2)?></td><td><a class="smallbtn" href="?edit=<?=$p['id']?>">Edit</a><?php if($p['is_active']):?> <form method="post" style="display:inline" onsubmit="return confirm('Make this product inactive? Purchase and sales history will remain safe.');"><input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token())?>"><input type="hidden" name="action" value="deactivate"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="smallbtn danger">Inactive</button></form><?php endif;?></td></tr><?php endforeach;?></tbody></table></div></section></main><script>const cats=<?=json_encode($categories,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;const main=document.getElementById('mainCategory'),sub=document.getElementById('subCategory');function fill(){const cur=sub.dataset.current||'';sub.innerHTML='<option value="">Select Sub-category</option>';(cats[main.value]||[]).forEach(v=>{const o=document.createElement('option');o.value=o.textContent=v;if(v===cur)o.selected=true;sub.appendChild(o)});sub.dataset.current=''}main.addEventListener('change',fill);fill();</script></body></html>