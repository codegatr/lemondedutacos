<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

if (admin_check()) {
    header('Location: index.php'); exit;
}

$err  = '';
$user = '';
$back = $_GET['back'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_required();
    $user = trim((string)($_POST['username'] ?? ''));
    $pass = (string)($_POST['password'] ?? '');

    if (!rate_limit('login:' . client_ip(), 10, 600)) {
        $err = 'Çok fazla başarısız deneme. 10 dakika bekleyin.';
    } else {
        $stmt = db()->prepare("SELECT id, username, password_hash, name, role, is_active FROM admin_users WHERE username = ?");
        $stmt->execute([$user]);
        $u = $stmt->fetch();

        if ($u && $u['is_active'] && password_verify($pass, $u['password_hash'])) {
            admin_login((int)$u['id'], $u['username']);
            db()->prepare("UPDATE admin_users SET last_login_at=NOW(), last_login_ip=? WHERE id=?")
                ->execute([client_ip(), $u['id']]);
            log_activity('login', (int)$u['id'], 'Başarılı giriş');
            $safe = parse_url($back, PHP_URL_PATH) ?: 'index.php';
            header('Location: ' . (str_starts_with($safe, '/' . ADMIN_PATH) ? $safe : 'index.php')); exit;
        } else {
            $err = 'Kullanıcı adı veya parola hatalı.';
            log_activity('login_failed', null, 'Kullanıcı: ' . $user);
        }
    }
}
?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Yönetici Giriş – <?= e(setting('site_name', SITE_NAME)) ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,Arial,sans-serif;background:linear-gradient(135deg,#3A5F0B,#1f2937);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.box{background:#fff;border-radius:14px;padding:30px 32px;width:100%;max-width:380px;box-shadow:0 20px 50px rgba(0,0,0,.25)}
.box h1{color:#3A5F0B;font-size:20px;margin-bottom:4px;text-align:center}
.box .tag{color:#6b7280;font-size:13px;text-align:center;margin-bottom:20px}
.row{margin-bottom:14px}
.row label{display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px}
.row input{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px}
.row input:focus{outline:0;border-color:#3A5F0B;box-shadow:0 0 0 3px rgba(58,95,11,.12)}
.btn{width:100%;background:#3A5F0B;color:#fff;border:0;padding:11px;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px;margin-top:6px}
.btn:hover{background:#2c4708}
.err{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:10px;border-radius:8px;font-size:13px;margin-bottom:14px}
.foot{text-align:center;margin-top:18px;font-size:11px;color:#9ca3af}
</style>
</head>
<body>
<div class="box">
  <h1>Le Monde Du Tacos</h1>
  <div class="tag">Yönetim Paneli</div>
  <?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
  <form method="post" autocomplete="off">
    <?= csrf_field() ?>
    <div class="row">
      <label>Kullanıcı Adı</label>
      <input type="text" name="username" value="<?= e($user) ?>" required autofocus>
    </div>
    <div class="row">
      <label>Parola</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn">GİRİŞ YAP</button>
  </form>
  <div class="foot">v<?= e(app_version()) ?></div>
</div>
</body>
</html>
