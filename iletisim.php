<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$branches = db()->query("SELECT id, title FROM branches WHERE is_active = 1 ORDER BY sort_order")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_required();

    if (!rate_limit('contact:' . client_ip(), 5, 600)) {
        flash_set('error', 'Çok sık gönderim. Lütfen 10 dakika sonra tekrar deneyin.');
        header('Location: /iletisim.php#form'); exit;
    }

    $first   = clean_multi($_POST['fname'] ?? '');
    $last    = clean_multi($_POST['lname'] ?? '');
    $email   = clean_multi($_POST['email'] ?? '');
    $phone   = clean_multi($_POST['phone'] ?? '');
    $branch  = (int)($_POST['branch'] ?? 0);
    $subject = clean_multi($_POST['subject'] ?? '');
    $msg     = clean_multi($_POST['msg'] ?? '');
    $rating  = isset($_POST['rating']) ? max(1, min(5, (int)$_POST['rating'])) : null;

    $errors = [];
    if (mb_strlen($first) < 2)  $errors[] = 'Ad en az 2 karakter.';
    if (mb_strlen($last)  < 2)  $errors[] = 'Soyad en az 2 karakter.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli bir e-posta giriniz.';
    if (mb_strlen($subject) < 3) $errors[] = 'Konu zorunludur.';
    if (mb_strlen($msg) < 10)    $errors[] = 'Mesaj en az 10 karakter olmalı.';

    if ($errors) {
        flash_set('error', implode(' ', $errors));
    } else {
        $stmt = db()->prepare(
            "INSERT INTO contact_messages (first_name,last_name,email,phone,branch_id,subject,message,rating,ip)
             VALUES (?,?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $first, $last, $email, $phone ?: null,
            $branch ?: null, $subject, $msg, $rating, client_ip()
        ]);

        // E-posta bildirimi
        $to = setting('mail_to', MAIL_TO);
        if ($to) {
            $body = "<h3>Yeni İletişim Mesajı</h3>"
                  . "<p><b>Ad Soyad:</b> " . e($first . ' ' . $last) . "</p>"
                  . "<p><b>E-posta:</b> " . e($email) . "</p>"
                  . "<p><b>Telefon:</b> " . e($phone) . "</p>"
                  . "<p><b>Konu:</b> " . e($subject) . "</p>"
                  . "<p><b>Mesaj:</b><br>" . nl2br_safe($msg) . "</p>";
            send_mail($to, 'İletişim Formu: ' . $subject, $body);
        }

        flash_set('success', 'Mesajınız iletildi. En kısa sürede dönüş yapacağız.');
        header('Location: /iletisim.php#form'); exit;
    }
}

$page_slug = 'iletisim';
$page_title = 'İletişim';
$page_desc = 'Bize ulaşın';
$extra_css = "
.cnt-wrap{max-width:var(--max);margin:24px auto;padding:0 18px 40px;display:grid;grid-template-columns:1fr 1.2fr;gap:24px}
.cnt-info,.cnt-form{background:#fff;border-radius:14px;padding:24px;box-shadow:0 6px 20px rgba(0,0,0,.04)}
.cnt-info h1{color:var(--brand);font-size:24px;margin:0 0 12px}
.cnt-info p,.cnt-info li{color:var(--muted);line-height:1.6;font-size:14px}
.cnt-info ul{list-style:none;padding:0;margin:14px 0;display:flex;flex-direction:column;gap:10px}
.cnt-info li i{color:var(--brand);width:18px}
.cnt-form h2{color:var(--brand);font-size:18px;margin:0 0 14px}
.f-row{margin-bottom:12px}
.f-row label{display:block;font-size:13px;font-weight:600;margin-bottom:4px}
.f-row input,.f-row select,.f-row textarea{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit}
.f-row input:focus,.f-row select:focus,.f-row textarea:focus{outline:0;border-color:var(--brand);box-shadow:0 0 0 3px rgba(58,95,11,.12)}
.f-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.btn-submit{width:100%;background:var(--brand);color:#fff;border:0;padding:13px;border-radius:8px;font-weight:700;font-size:15px;cursor:pointer;transition:.2s}
.btn-submit:hover{background:#2c4708}
.stars{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:4px;margin-top:6px}
.stars input{display:none}
.stars label{font-size:24px;color:#d1d5db;cursor:pointer;transition:.1s}
.stars input:checked ~ label,.stars label:hover,.stars label:hover ~ label{color:#f59e0b}
@media(max-width:860px){.cnt-wrap{grid-template-columns:1fr}.f-grid{grid-template-columns:1fr}}
";
require __DIR__ . '/includes/header.php';
?>

<main class="cnt-wrap">
  <aside class="cnt-info">
    <h1>Bize Ulaşın</h1>
    <p>Görüş, öneri ve şikayetlerinizi paylaşmak için aşağıdaki formu doldurabilir veya iletişim kanallarımızı kullanabilirsiniz.</p>
    <ul>
      <li><i class="fa-solid fa-envelope"></i> <?= e(setting('contact_email', MAIL_TO)) ?></li>
      <?php if (setting('contact_phone')): ?><li><i class="fa-solid fa-phone"></i> <?= e(setting('contact_phone')) ?></li><?php endif; ?>
      <?php if (setting('contact_address')): ?><li><i class="fa-solid fa-location-dot"></i> <?= e(setting('contact_address')) ?></li><?php endif; ?>
    </ul>
  </aside>

  <form class="cnt-form" id="form" method="post" novalidate>
    <h2>Mesaj Gönderin</h2>
    <?= csrf_field() ?>
    <div class="f-grid">
      <div class="f-row"><label>Ad *</label><input type="text" name="fname" required></div>
      <div class="f-row"><label>Soyad *</label><input type="text" name="lname" required></div>
    </div>
    <div class="f-grid">
      <div class="f-row"><label>E-posta *</label><input type="email" name="email" required></div>
      <div class="f-row"><label>Telefon</label><input type="tel" name="phone" placeholder="+90 5XX XXX XX XX"></div>
    </div>
    <div class="f-row">
      <label>Şube</label>
      <select name="branch">
        <option value="">— Seçiniz —</option>
        <?php foreach ($branches as $b): ?>
          <option value="<?= (int)$b['id'] ?>"><?= e($b['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="f-row"><label>Konu *</label><input type="text" name="subject" required></div>
    <div class="f-row"><label>Mesajınız *</label><textarea name="msg" rows="5" required></textarea></div>
    <div class="f-row">
      <label>Genel Deneyiminizi Değerlendirin</label>
      <div class="stars">
        <input type="radio" name="rating" id="s5" value="5"><label for="s5" title="Mükemmel">★</label>
        <input type="radio" name="rating" id="s4" value="4"><label for="s4" title="İyi">★</label>
        <input type="radio" name="rating" id="s3" value="3"><label for="s3" title="Orta">★</label>
        <input type="radio" name="rating" id="s2" value="2"><label for="s2" title="Kötü">★</label>
        <input type="radio" name="rating" id="s1" value="1"><label for="s1" title="Çok kötü">★</label>
      </div>
    </div>
    <button type="submit" class="btn-submit">Mesaj Gönder</button>
  </form>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
