<?php
declare(strict_types=1);

/**
 * Le Monde Du Tacos – Kurulum
 * Çalıştırma: tarayıcıdan /install.php
 *
 * Bu dosya:
 *  1. Veritabanı bağlantısını test eder
 *  2. Şemayı kurar (schema.sql)
 *  3. Tohum verisini yükler (seed.sql)
 *  4. SECRET_KEY otomatik üretilir
 *  5. Admin hesabını kontrol eder ve yeni şifre belirlemenize izin verir
 *  6. Kurulum sonrası kendini KAPATIR (install.lock)
 */

require_once __DIR__ . '/includes/config.php';

$lockFile = __DIR__ . '/install.lock';
if (file_exists($lockFile)) {
    die('<h2>Kurulum kilitli.</h2><p>Sistem zaten kurulu. Yeniden kurmak için <code>install.lock</code> dosyasını silin.</p>');
}

$step    = $_POST['step'] ?? '1';
$errors  = [];
$success = [];

function h($s) { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }

// Adım 2: form gönderildi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === '2') {
    $admin_user = trim((string)($_POST['admin_user'] ?? ''));
    $admin_pass = (string)($_POST['admin_pass'] ?? '');
    $admin_pass2 = (string)($_POST['admin_pass2'] ?? '');
    $admin_name = trim((string)($_POST['admin_name'] ?? ''));
    $admin_mail = trim((string)($_POST['admin_mail'] ?? ''));

    if (mb_strlen($admin_user) < 3) $errors[] = 'Yönetici kullanıcı adı en az 3 karakter olmalı.';
    if (mb_strlen($admin_pass) < 8) $errors[] = 'Parola en az 8 karakter olmalı.';
    if ($admin_pass !== $admin_pass2) $errors[] = 'Parolalar eşleşmiyor.';
    if ($admin_name === '') $errors[] = 'Yönetici adı zorunludur.';

    if (!$errors) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);

            // Şema
            $schema = file_get_contents(__DIR__ . '/includes/schema.sql');
            if ($schema === false) throw new RuntimeException('schema.sql okunamadı.');
            $pdo->exec($schema);
            $success[] = 'Veritabanı şeması oluşturuldu.';

            // Tohum
            $seed = file_get_contents(__DIR__ . '/includes/seed.sql');
            if ($seed !== false) {
                $pdo->exec($seed);
                $success[] = 'Başlangıç verisi yüklendi.';
            }

            // Admin kullanıcı (varsayılan id=1 olanı güncelle veya yeni ekle)
            $hash = password_hash($admin_pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                "INSERT INTO admin_users (id, username, password_hash, name, email, role, is_active)
                 VALUES (1, ?, ?, ?, ?, 'superadmin', 1)
                 ON DUPLICATE KEY UPDATE
                    username = VALUES(username),
                    password_hash = VALUES(password_hash),
                    name = VALUES(name),
                    email = VALUES(email),
                    role = 'superadmin',
                    is_active = 1"
            );
            $stmt->execute([$admin_user, $hash, $admin_name, $admin_mail ?: null]);
            $success[] = 'Yönetici hesabı oluşturuldu.';

            // SECRET_KEY otomatik
            $secret = bin2hex(random_bytes(32));
            $cfg = file_get_contents(__DIR__ . '/includes/config.php');
            $cfg = preg_replace(
                "/const SECRET_KEY = '[^']*';/",
                "const SECRET_KEY = '" . $secret . "';",
                $cfg,
                1
            );
            file_put_contents(__DIR__ . '/includes/config.php', $cfg);
            $success[] = 'Güvenlik anahtarı üretildi.';

            // Lock
            file_put_contents($lockFile, "Kurulum: " . date('Y-m-d H:i:s'));
            $success[] = 'Kurulum tamamlandı.';
        } catch (Throwable $e) {
            $errors[] = 'Hata: ' . $e->getMessage();
        }
    }
}
?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kurulum – <?= h(SITE_NAME) ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f6f7f9;color:#1f2937;padding:40px 16px;min-height:100vh}
.box{max-width:560px;margin:0 auto;background:#fff;border-radius:14px;padding:28px 32px;box-shadow:0 10px 30px rgba(0,0,0,.06);border:1px solid #eef2f7}
h1{font-size:22px;color:#3A5F0B;margin-bottom:6px}
.tag{font-size:13px;color:#6b7280;margin-bottom:20px}
.row{margin-bottom:14px}
label{display:block;font-size:13px;font-weight:600;margin-bottom:6px}
input[type=text],input[type=password],input[type=email]{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px}
input:focus{outline:0;border-color:#3A5F0B;box-shadow:0 0 0 3px rgba(58,95,11,.12)}
.btn{display:inline-block;background:#3A5F0B;color:#fff;border:0;border-radius:8px;padding:11px 20px;font-size:14px;font-weight:600;cursor:pointer;width:100%}
.btn:hover{background:#2c4708}
.note{background:#fef9c3;border:1px solid #fde68a;color:#854d0e;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px}
.ok{background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:8px}
.err{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:8px}
.steps{display:flex;gap:8px;margin-bottom:18px}
.steps span{flex:1;padding:8px;text-align:center;background:#f3f4f6;color:#6b7280;border-radius:6px;font-size:12px;font-weight:600}
.steps span.active{background:#3A5F0B;color:#fff}
.muted{color:#6b7280;font-size:12px;margin-top:8px}
ul{padding-left:20px;font-size:13px;line-height:1.7}
code{background:#f3f4f6;padding:2px 6px;border-radius:4px;font-size:12px}
.actions a{display:inline-block;margin-top:8px;color:#3A5F0B;font-weight:600;text-decoration:none}
</style>
</head>
<body>
<div class="box">
  <h1>Le Monde Du Tacos – Kurulum</h1>
  <div class="tag">PHP <?= PHP_VERSION ?> • DirectAdmin uyumlu kurulum</div>

  <div class="steps">
    <span class="<?= $success ? '' : 'active' ?>">1 · Yapılandırma</span>
    <span class="<?= $success ? 'active' : '' ?>">2 · Yönetici</span>
    <span class="<?= count($success) > 3 ? 'active' : '' ?>">3 · Tamam</span>
  </div>

  <?php foreach ($errors as $er): ?>
    <div class="err"><?= h($er) ?></div>
  <?php endforeach; ?>
  <?php foreach ($success as $sc): ?>
    <div class="ok">✓ <?= h($sc) ?></div>
  <?php endforeach; ?>

  <?php if (count($success) > 3): ?>
    <div class="note">
      <strong>Kurulum başarılı.</strong> Artık siteyi ziyaret edebilir, yönetici paneline giriş yapabilirsiniz.
    </div>
    <ul>
      <li>Site: <a href="/"><?= h(SITE_URL) ?></a></li>
      <li>Yönetim: <a href="/<?= h(ADMIN_PATH) ?>/">/<?= h(ADMIN_PATH) ?>/</a></li>
      <li>Güvenlik için <code>install.php</code> dosyasını sunucudan silmeniz önerilir.</li>
    </ul>
  <?php else: ?>
    <div class="note">
      <strong>Önce <code>includes/config.php</code> içindeki veritabanı bilgilerini doldurun.</strong>
      Mevcut: <code><?= h(DB_USER) ?>@<?= h(DB_HOST) ?>/<?= h(DB_NAME) ?></code>
    </div>

    <form method="post" autocomplete="off">
      <input type="hidden" name="step" value="2">

      <div class="row">
        <label>Yönetici Kullanıcı Adı</label>
        <input type="text" name="admin_user" value="<?= h($_POST['admin_user'] ?? 'admin') ?>" required minlength="3">
      </div>
      <div class="row">
        <label>Yönetici Adı Soyadı</label>
        <input type="text" name="admin_name" value="<?= h($_POST['admin_name'] ?? '') ?>" required>
      </div>
      <div class="row">
        <label>E-posta</label>
        <input type="email" name="admin_mail" value="<?= h($_POST['admin_mail'] ?? '') ?>">
      </div>
      <div class="row">
        <label>Parola (en az 8 karakter)</label>
        <input type="password" name="admin_pass" required minlength="8">
      </div>
      <div class="row">
        <label>Parola (Tekrar)</label>
        <input type="password" name="admin_pass2" required minlength="8">
      </div>
      <button type="submit" class="btn">Kurulumu Başlat</button>
      <div class="muted">Bu işlem birkaç saniye sürer. Kurulum tamamlandığında sistem otomatik olarak kilitlenir.</div>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
