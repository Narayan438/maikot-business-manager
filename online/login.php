<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$error='';$config=__DIR__.'/config/database.php';$hasBusinesses=false;
if(is_file($config)){require $config;$hasBusinesses=(int)$pdo->query('SELECT COUNT(*) FROM businesses')->fetchColumn()>0;}
if ($_SERVER['REQUEST_METHOD']==='POST') {
 if (!verify_csrf((string)($_POST['csrf']??''))) $error='Session expired. Please try again.';
 elseif (!is_file($config)) $error='Hosting database is not configured yet.';
 else {
  $company=trim((string)($_POST['company_code']??''));$username=trim((string)($_POST['username']??''));$password=(string)($_POST['password']??'');
  $stmt=$pdo->prepare('SELECT u.*,b.business_name,b.slug AS company_code FROM users u JOIN businesses b ON b.id=u.business_id WHERE b.slug=? AND u.username=? AND u.is_active=1 AND b.is_active=1 LIMIT 1');
  $stmt->execute([$company,$username]);$user=$stmt->fetch();
  if($user&&password_verify($password,$user['password_hash'])){login_user($user);$pdo->prepare('UPDATE users SET last_login_at=NOW() WHERE id=?')->execute([$user['id']]);header('Location: index.php');exit;}
  $error='Invalid Company Code, username or password.';
 }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login • MeroKhata</title><style>*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;background:#f3f6fa;color:#172033;min-height:100vh;display:grid;place-items:center;padding:20px}.card{width:min(420px,100%);background:#fff;border-radius:18px;padding:30px;box-shadow:0 12px 40px #1720331c}.logo{font-size:30px;font-weight:800;text-align:center}.tag{text-align:center;color:#667085;margin:7px 0 26px}.err,.note{padding:11px;border-radius:9px;margin-bottom:15px}.err{background:#fff0f0;color:#9b1c1c}.note{background:#eef5ff;color:#174f9b}label{display:block;font-weight:700;margin:13px 0 6px}input{width:100%;padding:13px;border:1px solid #cfd5df;border-radius:9px;font-size:16px}button{width:100%;padding:13px;margin-top:20px;border:0;border-radius:9px;background:#174f9b;color:#fff;font-size:16px;font-weight:700;cursor:pointer}.foot{text-align:center;color:#7a8495;font-size:12px;margin-top:20px}.foot a{color:#174f9b}</style></head><body><form class="card" method="post" autocomplete="on"><div class="logo">MeroKhata</div><div class="tag">Business Management System</div><?php if(!$hasBusinesses):?><div class="note">No company account exists yet. The system administrator must create the first company before login.</div><?php endif;?><?php if($error):?><div class="err"><?=htmlspecialchars($error)?></div><?php endif;?><input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token())?>"><label>Company Code</label><input name="company_code" required autofocus placeholder="e.g. maikot"><label>Username</label><input name="username" autocomplete="username" required><label>Password</label><input name="password" type="password" autocomplete="current-password" required><button>Login</button><div class="foot">Each company has a separate account and database scope.</div></form></body></html>