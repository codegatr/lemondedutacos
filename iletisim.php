<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

// Form gönderildi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    public_form_csrf_required();

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
    if (mb_strlen($first) < 2)  $errors[] = 'Ad en az 2 karakter olmalı.';
    if (mb_strlen($last) < 2)   $errors[] = 'Soyad en az 2 karakter olmalı.';
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

        $branchTitle = '';
        if ($branch) {
            $branchStmt = db()->prepare("SELECT title FROM branches WHERE id = ?");
            $branchStmt->execute([$branch]);
            $branchTitle = (string)($branchStmt->fetchColumn() ?: '');
        }

        $to = form_notification_email();
        if ($to) {
            $body = "<h2>Yeni İletişim Formu Mesajı</h2>"
                  . form_mail_table([
                      'Ad' => $first,
                      'Soyad' => $last,
                      'Ad Soyad' => trim($first . ' ' . $last),
                      'E-posta' => $email,
                      'Telefon' => $phone,
                      'Şube' => $branchTitle ?: ($branch ? 'ID: ' . $branch : ''),
                      'Konu' => $subject,
                      'Puan' => $rating ? $rating . ' / 5' : '',
                      'Mesaj' => $msg,
                      'IP' => client_ip(),
                      'Tarih' => date('d.m.Y H:i'),
                  ]);
            if (!send_mail($to, 'İletişim Formu: ' . $subject, $body, $email)) {
                log_activity('mail_failed', null, 'İletişim formu bildirimi gönderilemedi: ' . $to);
            }
        }

        flash_set('success', 'Mesajınız iletildi. En kısa sürede dönüş yapacağız.');
        header('Location: /iletisim.php?ok=1#form'); exit;
    }
}

$branches = db()->query("SELECT id, title FROM branches WHERE is_active = 1 ORDER BY sort_order")->fetchAll();

$page_title = 'İletişim';
$page_desc  = 'Bize ulaşın — sorularınız, önerileriniz veya şikayetleriniz için iletişim formu, telefon ve e-posta bilgileri.';
$page_slug  = 'iletisim';
$extra_css  = "
/* SADECE bu sayfaya özel ek CSS — header.php genel stilleri zaten yüklüyor */
.page-banner{
  position:relative;
  background:url('/static/img/yeni/o.png') center center / cover no-repeat;
  min-height:200px;
  display:flex;
  align-items:center;
  justify-content:center;
  text-align:center;
  color:#fff;
  padding:48px 20px;
  overflow:hidden;
}
.page-banner::before{
  content:'';
  position:absolute;
  inset:0;
  background:linear-gradient(180deg,rgba(0,0,0,.35),rgba(0,0,0,.65));
}
.page-banner > .pb-inner{ position:relative; z-index:2; max-width:760px; }
.page-banner h1{
  font-family:Georgia,serif;
  font-size:clamp(28px,4vw,44px);
  font-weight:700;
  margin-bottom:10px;
  letter-spacing:.5px;
}
.page-banner p{
  font-size:clamp(14px,1.6vw,16px);
  opacity:.92;
  line-height:1.6;
}
.page-banner .tag{
  display:inline-block;
  background:#b24545;
  color:#fff;
  padding:6px 14px;
  border-radius:100px;
  font-size:12px;
  font-weight:700;
  letter-spacing:.5px;
  margin-bottom:16px;
}

.contact-section{
  max-width:1180px;
  margin:0 auto;
  padding:48px 20px 64px;
  display:grid;
  grid-template-columns:1fr 1.4fr;
  gap:32px;
  align-items:start;
}
@media(max-width:900px){
  .contact-section{ grid-template-columns:1fr; gap:24px; padding:32px 16px 48px; }
}

.info-card,.form-card{
  background:#fff;
  border:1px solid #e5e7eb;
  border-radius:16px;
  box-shadow:0 4px 18px rgba(0,0,0,.06);
  overflow:hidden;
}

/* Sol panel */
.info-card{ display:flex; flex-direction:column; }
.info-head{ padding:28px 26px 22px; border-bottom:1px solid #f1f5f9; }
.info-head h2{
  font-family:Georgia,serif;
  font-size:24px;
  color:#1f2937;
  margin-bottom:8px;
}
.info-head p{ color:#6b7280; font-size:14px; line-height:1.6; }

.detail-list{ padding:8px 0; }
.detail-item{
  display:flex; align-items:flex-start; gap:14px;
  padding:14px 26px;
  border-bottom:1px solid #f9fafb;
}
.detail-item:last-child{ border-bottom:none; }
.d-icon{
  width:38px; height:38px; flex-shrink:0;
  background:#b24545; color:#fff;
  border-radius:10px;
  display:flex; align-items:center; justify-content:center;
  font-size:14px;
}
.d-content{ min-width:0; flex:1; }
.d-label{
  font-size:11px;
  text-transform:uppercase;
  letter-spacing:.6px;
  color:#9ca3af;
  font-weight:700;
  margin-bottom:3px;
}
.d-value{ font-size:14px; color:#1f2937; line-height:1.5; }
.d-value a{ color:#3a5f0b; font-weight:600; }
.d-value a:hover{ text-decoration:underline; }

/* Sağ panel - Form */
.form-card{ padding:28px 26px; }
.form-card h2{
  font-family:Georgia,serif;
  font-size:24px;
  color:#1f2937;
  margin-bottom:6px;
}
.form-card .form-sub{
  color:#6b7280;
  font-size:13px;
  margin-bottom:22px;
}

.form-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:14px 16px;
}
.form-grid .full{ grid-column:1 / -1; }
@media(max-width:560px){
  .form-grid{ grid-template-columns:1fr; }
}

.field label{
  display:block;
  font-size:11px;
  font-weight:700;
  text-transform:uppercase;
  letter-spacing:.5px;
  color:#6b7280;
  margin-bottom:6px;
}
.field input[type=text],
.field input[type=email],
.field input[type=tel],
.field select,
.field textarea{
  width:100%;
  padding:11px 14px;
  border:1px solid #d1d5db;
  border-radius:8px;
  font-size:14px;
  font-family:inherit;
  background:#fff;
  color:#1f2937;
  transition:border-color .15s, box-shadow .15s;
}
.field input:focus,
.field select:focus,
.field textarea:focus{
  outline:none;
  border-color:#3a5f0b;
  box-shadow:0 0 0 3px rgba(58,95,11,.12);
}
.field textarea{ resize:vertical; min-height:120px; }

/* Yıldız puanlama */
.rate{
  display:inline-flex;
  flex-direction:row-reverse;
  gap:4px;
  margin-top:4px;
}
.rate input{ display:none; }
.rate label{
  font-size:24px;
  color:#d1d5db;
  cursor:pointer;
  transition:color .15s;
  margin:0;
}
.rate input:checked ~ label,
.rate label:hover,
.rate label:hover ~ label{ color:#f59e0b; }

/* Submit butonu */
.btn-submit{
  margin-top:18px;
  width:100%;
  padding:14px 20px;
  background:linear-gradient(90deg,#3a5f0b,#16a34a);
  color:#fff;
  border:none;
  border-radius:10px;
  font-size:14px;
  font-weight:700;
  letter-spacing:.5px;
  text-transform:uppercase;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  cursor:pointer;
  transition:transform .12s, box-shadow .15s;
}
.btn-submit:hover{
  transform:translateY(-2px);
  box-shadow:0 8px 22px rgba(58,95,11,.3);
}
.btn-submit i{ font-size:13px; }

/* Flash mesajları */
.flash{
  padding:12px 16px;
  border-radius:8px;
  font-size:13px;
  font-weight:600;
  margin-bottom:16px;
  display:flex;
  align-items:center;
  gap:8px;
}
.flash.success{ background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
.flash.error{ background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
";
require __DIR__ . '/includes/header.php';
?>

<main role="main">

  <!-- Sayfa banner'ı -->
  <section class="page-banner">
    <div class="pb-inner">
      <span class="tag"><i class="fa-solid fa-envelope" style="margin-right:6px"></i>İLETİŞİM</span>
      <h1>Bize Ulaşın</h1>
      <p>Sorularınız, önerileriniz veya şikayetleriniz için aşağıdaki formu doldurun — en kısa sürede geri dönüş yapacağız.</p>
    </div>
  </section>

  <!-- İletişim alanı: 2 sütun (sol bilgi + sağ form) -->
  <section class="contact-section" id="form">

    <!-- SOL: Bilgi paneli -->
    <aside class="info-card">
      <div class="info-head">
        <h2>İletişim Bilgileri</h2>
        <p>Aşağıdaki kanallardan bize her zaman ulaşabilirsiniz.</p>
      </div>
      <div class="detail-list">
        <?php
          $contactPhone   = setting('contact_phone', '+90 212 444 12 34');
          $contactEmail   = setting('contact_email', 'info@lemondedutacos.com');
          $contactHours   = setting('contact_hours', 'Her Gün 10:00 – 23:00');
          $contactAddress = setting('contact_address', 'Bahçelievler, Adnan Kahveci Blv. No:101/B 34180 İstanbul');
          $phoneTel = preg_replace('/[^0-9+]/', '', $contactPhone);
        ?>
        <div class="detail-item">
          <div class="d-icon"><i class="fa-solid fa-phone"></i></div>
          <div class="d-content">
            <div class="d-label">Telefon</div>
            <div class="d-value"><a href="tel:<?= e($phoneTel) ?>"><?= e($contactPhone) ?></a></div>
          </div>
        </div>
        <div class="detail-item">
          <div class="d-icon"><i class="fa-solid fa-envelope"></i></div>
          <div class="d-content">
            <div class="d-label">E-posta</div>
            <div class="d-value"><a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a></div>
          </div>
        </div>
        <div class="detail-item">
          <div class="d-icon"><i class="fa-solid fa-clock"></i></div>
          <div class="d-content">
            <div class="d-label">Çalışma Saatleri</div>
            <div class="d-value"><?= e($contactHours) ?></div>
          </div>
        </div>
        <div class="detail-item">
          <div class="d-icon"><i class="fa-solid fa-location-dot"></i></div>
          <div class="d-content">
            <div class="d-label">Genel Merkez</div>
            <div class="d-value"><?= nl2br_safe($contactAddress) ?></div>
          </div>
        </div>
      </div>
    </aside>

    <!-- SAĞ: Form -->
    <div class="form-card">
      <h2>Mesaj Gönderin</h2>
      <div class="form-sub">24 saat içinde yanıtlanacak — bilgileriniz gizli tutulmaktadır.</div>

      <?php foreach (flash_get() as $f): ?>
        <div class="flash <?= e($f['type']) ?>">
          <i class="fa-solid <?= $f['type']==='success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
          <?= e($f['msg']) ?>
        </div>
      <?php endforeach; ?>

      <form id="contactForm" method="post" action="iletisim.php#form" novalidate>
        <?= csrf_field() ?>

        <div class="form-grid">
          <div class="field">
            <label for="fname">Ad</label>
            <input type="text" id="fname" name="fname" placeholder="Adınız" required>
          </div>
          <div class="field">
            <label for="lname">Soyad</label>
            <input type="text" id="lname" name="lname" placeholder="Soyadınız" required>
          </div>
          <div class="field">
            <label for="email">E-posta</label>
            <input type="email" id="email" name="email" placeholder="ornek@eposta.com" required>
          </div>
          <div class="field">
            <label for="phone">Telefon</label>
            <input type="tel" id="phone" name="phone" placeholder="+90 5XX XXX XX XX">
          </div>
          <div class="field full">
            <label for="branch">Şube</label>
            <select id="branch" name="branch">
              <option value="">— Şube seçin (opsiyonel) —</option>
              <?php foreach ($branches as $b): ?>
                <option value="<?= (int)$b['id'] ?>"><?= e($b['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field full">
            <label for="subject">Konu</label>
            <input type="text" id="subject" name="subject" placeholder="Mesajınızın konusu" required>
          </div>
          <div class="field full">
            <label for="msg">Mesajınız</label>
            <textarea id="msg" name="msg" rows="5" placeholder="Görüş, öneri veya şikayetinizi buraya yazın…" required></textarea>
          </div>
          <div class="field full">
            <label>Genel Deneyiminizi Değerlendirin</label>
            <div class="rate">
              <input type="radio" name="rating" id="s5" value="5"><label for="s5" title="Mükemmel">★</label>
              <input type="radio" name="rating" id="s4" value="4"><label for="s4" title="İyi">★</label>
              <input type="radio" name="rating" id="s3" value="3"><label for="s3" title="Orta">★</label>
              <input type="radio" name="rating" id="s2" value="2"><label for="s2" title="Kötü">★</label>
              <input type="radio" name="rating" id="s1" value="1"><label for="s1" title="Çok kötü">★</label>
            </div>
          </div>
        </div>

        <button type="submit" class="btn-submit">
          <i class="fa-solid fa-paper-plane"></i> Gönder
        </button>
      </form>
    </div>

  </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
