<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_required();

    if (!rate_limit('franchise:' . client_ip(), 3, 1800)) {
        flash_set('error', 'Çok sık başvuru. Lütfen 30 dakika sonra tekrar deneyin.');
        header('Location: /franchise.php#form'); exit;
    }

    $name   = clean_multi($_POST['adsoyad'] ?? '');
    $phone  = clean_multi($_POST['telefon'] ?? '');
    $email  = clean_multi($_POST['eposta'] ?? '');
    $city   = clean_multi($_POST['sehir'] ?? '');
    $age    = (int)($_POST['yas'] ?? 0);
    $invest = clean_multi($_POST['yatirim'] ?? '');
    $msg    = clean_multi($_POST['mesaj'] ?? '');
    $kvkk   = isset($_POST['kvkk']) ? 1 : 0;
    $com    = isset($_POST['ticari']) ? 1 : 0;

    $errors = [];
    if (mb_strlen($name) < 4) $errors[] = 'Ad-Soyad zorunludur.';
    if (mb_strlen($phone) < 10) $errors[] = 'Geçerli telefon giriniz.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli e-posta giriniz.';
    if (mb_strlen($city) < 2) $errors[] = 'Şehir zorunludur.';
    if ($age < 18 || $age > 99) $errors[] = 'Yaş 18-99 arasında olmalı.';
    if (!in_array($invest, ['8-9','9-10','10+'], true)) $errors[] = 'Yatırım aralığı seçiniz.';
    if (!$kvkk) $errors[] = 'KVKK onayı zorunludur.';

    if ($errors) {
        flash_set('error', implode(' ', $errors));
    } else {
        $stmt = db()->prepare(
            "INSERT INTO franchise_applications (full_name,phone,email,city,age,investment,message,kvkk,commercial,ip)
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([$name,$phone,$email,$city,$age,$invest,$msg ?: null,$kvkk,$com,client_ip()]);

        $to = form_notification_email();
        if ($to) {
            $body = "<h2>Yeni Franchise Başvurusu</h2>"
                  . form_mail_table([
                      'Ad Soyad' => $name,
                      'Telefon' => $phone,
                      'E-posta' => $email,
                      'Şehir' => $city,
                      'Yaş' => (string)$age,
                      'Yatırım Aralığı' => $invest . ' Milyon TL',
                      'KVKK Onayı' => $kvkk ? 'Evet' : 'Hayır',
                      'Ticari İleti Onayı' => $com ? 'Evet' : 'Hayır',
                      'Mesaj / Notlar' => $msg,
                      'IP' => client_ip(),
                      'Tarih' => date('d.m.Y H:i'),
                  ]);
            if (!send_mail($to, 'Franchise Başvurusu: ' . $name, $body, $email)) {
                log_activity('mail_failed', null, 'Franchise bildirimi gönderilemedi: ' . $to);
            }
        }

        flash_set('success', 'Başvurunuz alındı. En kısa sürede sizinle iletişime geçeceğiz.');
        header('Location: /franchise.php?ok=1#form'); exit;
    }
}

$franchiseTitle = setting('franchise_title', 'TACOS GIDA Ailesine Katılın');
$franchiseDesc = setting('franchise_description', 'Fast Food kültürünü ve sektörü en iyi bilen markanın desteğiyle kendi işinizin patronu olmak için harika bir fırsat!');
$franchiseInfoTitle = setting('franchise_info_title', 'TACOS GIDA Ailesine Katılmak İster misiniz?');
$franchiseInfoText = setting('franchise_info_text', "Türkiye'nin dört bir yanında büyümeye devam eden TACOS GIDA ekibine siz de katılın!\nGüçlü marka desteği, kârlı iş modeli, tecrübeli operasyon ağı ve kapsamlı eğitim sistemiyle kendi işinizi kurma yolculuğunuzda yanınızdayız.");
$franchiseInfoText2 = setting('franchise_info_text_2', "Siz de bulunduğunuz şehirde TACOS GIDA şubesi açmak isterseniz, formu doldurarak bizimle iletişime geçebilirsiniz. Başvuru sonrasında ekibimiz en kısa sürede size ulaşacak.");
$franchiseMail = setting('franchise_contact_email', setting('contact_email', 'info@lemondedutacos.com'));

$page_title = 'Franchise';
$page_desc  = $franchiseDesc;
$page_slug  = 'franchise';
$extra_css  = "
.page-banner{
  position:relative;
  background:url('/static/img/franchise.jpg') center center / cover no-repeat;
  min-height:240px;
  display:flex;
  align-items:center;
  justify-content:center;
  text-align:center;
  color:#fff;
  padding:56px 20px;
  overflow:hidden;
}
.page-banner::before{
  content:'';
  position:absolute;
  inset:0;
  background:linear-gradient(180deg,rgba(0,0,0,.40),rgba(0,0,0,.65));
}
.page-banner > .pb-inner{ position:relative; z-index:2; max-width:760px; }
.page-banner h1{
  font-family:Georgia,serif;
  font-size:clamp(28px,4vw,46px);
  font-weight:700;
  margin-bottom:10px;
  letter-spacing:.5px;
}
.page-banner p{
  font-size:clamp(14px,1.6vw,17px);
  opacity:.95;
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

.fr-section{
  max-width:1180px;
  margin:0 auto;
  padding:48px 20px 64px;
  display:grid;
  grid-template-columns:1.1fr 1fr;
  gap:32px;
  align-items:start;
}
@media(max-width:980px){
  .fr-section{ grid-template-columns:1fr; gap:24px; padding:32px 16px 48px; }
}

/* SOL: Bilgi tarafı */
.fr-info{
  background:#fff;
  border:1px solid #e5e7eb;
  border-radius:16px;
  padding:32px 30px;
  box-shadow:0 4px 18px rgba(0,0,0,.06);
}
.fr-info .fr-badge{
  display:inline-block;
  color:#b24545;
  font-size:12px;
  font-weight:800;
  letter-spacing:.5px;
  text-transform:uppercase;
  margin-bottom:8px;
}
.fr-info .fr-title{
  font-family:Georgia,serif;
  font-size:clamp(20px,2.4vw,28px);
  color:#1f2937;
  margin-bottom:14px;
  line-height:1.3;
}
.fr-info .fr-desc{
  color:#4b5563;
  font-size:14px;
  line-height:1.7;
  margin-bottom:14px;
}
.fr-list{
  list-style:none;
  margin:18px 0 22px;
  padding:0;
}
.fr-list li{
  display:flex;
  align-items:flex-start;
  gap:10px;
  padding:8px 0;
  font-size:13.5px;
  color:#374151;
}
.fr-list i{
  color:#b24545;
  font-size:14px;
  margin-top:3px;
  width:16px;
}
.fr-cta-line{
  font-weight:900;
  font-size:15px;
  color:#1f2937;
  margin-top:14px;
}
.fr-mail{
  font-family:'SF Mono',Menlo,monospace;
  font-size:13px;
  color:#3a5f0b;
  font-weight:600;
  margin:8px 0 16px;
}
.btn-catalog{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:11px 18px;
  background:#b24545;
  color:#fff;
  border:none;
  border-radius:8px;
  font-size:13px;
  font-weight:700;
  cursor:pointer;
  transition:background .15s, transform .12s;
}
.btn-catalog:hover{ background:#8e3636; transform:translateY(-2px); }

/* SAĞ: Form tarafı */
.fr-form-card{
  background:#fff;
  border:1px solid #e5e7eb;
  border-radius:16px;
  padding:30px 28px;
  box-shadow:0 4px 18px rgba(0,0,0,.06);
}
.fr-form-card h2{
  font-family:Georgia,serif;
  font-size:22px;
  color:#1f2937;
  margin-bottom:6px;
}
.fr-form-card .sub{
  color:#6b7280;
  font-size:13px;
  margin-bottom:22px;
}

.fr-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:14px 16px;
}
.fr-grid .full{ grid-column:1 / -1; }
@media(max-width:560px){
  .fr-grid{ grid-template-columns:1fr; }
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
.field input[type=number]{
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
.field input:focus{
  outline:none;
  border-color:#3a5f0b;
  box-shadow:0 0 0 3px rgba(58,95,11,.12);
}

/* Yatırım radio */
.choices{
  margin-top:6px;
  padding:14px 16px;
  background:#f9fafb;
  border:1px solid #e5e7eb;
  border-radius:10px;
}
.choices-title{
  font-size:11px;
  font-weight:700;
  text-transform:uppercase;
  letter-spacing:.5px;
  color:#6b7280;
  margin-bottom:10px;
}
.choice-row{
  display:flex;
  align-items:center;
  gap:8px;
  padding:6px 0;
  font-size:13px;
  color:#374151;
  cursor:pointer;
}
.choice-row input[type=radio]{ accent-color:#3a5f0b; cursor:pointer; }

/* KVKK link */
.link-row{
  font-size:13px;
  color:#3a5f0b;
  text-decoration:underline;
  cursor:pointer;
  margin:14px 0 6px;
  font-weight:600;
}
.link-row:hover{ color:#2a4508; }

.check{
  display:flex;
  align-items:flex-start;
  gap:10px;
  padding:6px 0;
  font-size:13px;
  color:#374151;
  cursor:pointer;
  line-height:1.5;
}
.check input[type=checkbox]{ accent-color:#3a5f0b; cursor:pointer; margin-top:2px; }

.actions{
  display:flex;
  gap:10px;
  margin-top:20px;
  flex-wrap:wrap;
}
.btn{
  flex:1;
  padding:13px 18px;
  border-radius:10px;
  font-size:14px;
  font-weight:700;
  letter-spacing:.5px;
  text-transform:uppercase;
  cursor:pointer;
  transition:transform .12s, box-shadow .15s;
  border:none;
  font-family:inherit;
}
.btn-send{
  background:linear-gradient(90deg,#3a5f0b,#16a34a);
  color:#fff;
}
.btn-send:hover{ transform:translateY(-2px); box-shadow:0 8px 22px rgba(58,95,11,.3); }
.btn-clear{
  background:#fff;
  color:#6b7280;
  border:1px solid #d1d5db;
}
.btn-clear:hover{ background:#f9fafb; color:#1f2937; }

.help-note{
  font-size:11px;
  color:#9ca3af;
  margin-top:14px;
  font-style:italic;
}

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

  <section class="page-banner">
    <div class="pb-inner">
      <span class="tag"><i class="fa-solid fa-handshake" style="margin-right:6px"></i>FRANCHISE</span>
      <h1><?= e($franchiseTitle) ?></h1>
      <p><?= e($franchiseDesc) ?></p>
    </div>
  </section>

  <section class="fr-section" id="form">

    <!-- SOL: Bilgi paneli -->
    <aside class="fr-info">
      <div class="fr-badge">Franchise Başvuru Formu — TACOS GIDA</div>
      <h2 class="fr-title"><?= e($franchiseInfoTitle) ?></h2>

      <p class="fr-desc">
        <?= nl2br_safe($franchiseInfoText) ?>
      </p>

      <ul class="fr-list">
        <li><i class="fa-solid fa-location-dot"></i> Lokasyon seçimi ve gelir modeli yaratma</li>
        <li><i class="fa-solid fa-gear"></i> Operasyonel destek ve sistem aktarımı</li>
        <li><i class="fa-solid fa-bullhorn"></i> Profesyonel pazarlama ve reklam uygulamaları</li>
        <li><i class="fa-solid fa-briefcase"></i> Sözleşmeden itibaren sürdürülebilir iş yönetimi</li>
        <li><i class="fa-solid fa-flask"></i> Profesyonel URGE ve ARGE desteği</li>
      </ul>

      <p class="fr-desc">
        <?= nl2br_safe($franchiseInfoText2) ?>
      </p>

      <div class="fr-cta-line">👉 Formu doldurun, ilk adımı birlikte atalım.</div>
      <div class="fr-mail"><?= e($franchiseMail) ?></div>

      <button class="btn-catalog" type="button" id="btnCatalog">
        <i class="fa-solid fa-file-pdf"></i> Franchise Kataloğu İçin Tıklayın
      </button>
    </aside>

    <!-- SAĞ: Form -->
    <div class="fr-form-card">
      <h2>Başvuru Formu</h2>
      <div class="sub">Tüm zorunlu alanları (*) eksiksiz doldurunuz.</div>

      <?php foreach (flash_get() as $f): ?>
        <div class="flash <?= e($f['type']) ?>">
          <i class="fa-solid <?= $f['type']==='success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
          <?= e($f['msg']) ?>
        </div>
      <?php endforeach; ?>

      <form id="frForm" method="post" action="franchise.php#form" autocomplete="on" novalidate>
        <?= csrf_field() ?>

        <div class="fr-grid">
          <div class="field full">
            <label for="adsoyad">Ad-Soyad (*)</label>
            <input id="adsoyad" name="adsoyad" type="text" placeholder="Adınız Soyadınız" required>
          </div>
          <div class="field">
            <label for="telefon">İletişim Numarası (*)</label>
            <input id="telefon" name="telefon" type="tel" inputmode="tel" placeholder="5__ ___ __ __" required>
          </div>
          <div class="field">
            <label for="eposta">E-Posta (*)</label>
            <input id="eposta" name="eposta" type="email" placeholder="E-Posta adresiniz" required>
          </div>
          <div class="field">
            <label for="sehir">Şehir (*)</label>
            <input id="sehir" name="sehir" type="text" placeholder="Şehrinizi yazınız" required>
          </div>
          <div class="field">
            <label for="yas">Yaş (*)</label>
            <input id="yas" name="yas" type="number" min="18" max="99" placeholder="Yaşınız" required>
          </div>
        </div>

        <div class="choices">
          <div class="choices-title">Yatırım Yapılmak İstenen Tutar (*)</div>
          <label class="choice-row"><input type="radio" name="yatirim" value="8-9" required> 8 Milyon - 9 Milyon TL</label>
          <label class="choice-row"><input type="radio" name="yatirim" value="9-10"> 9 Milyon - 10 Milyon TL</label>
          <label class="choice-row"><input type="radio" name="yatirim" value="10+"> 10 Milyon ve Üstü</label>
        </div>

        <div class="link-row" id="kvkkLink">KVKK Aydınlatma Metni</div>
        <label class="check">
          <input type="checkbox" id="kvkk" name="kvkk" required>
          <span>KVKK Aydınlatma Metnini kabul ediyorum. (*)</span>
        </label>

        <div class="link-row" id="ticariLink">Ticari Elektronik İleti Onay Metni</div>
        <label class="check">
          <input type="checkbox" id="ticari" name="ticari">
          <span>Ticari Elektronik İleti Onayı veriyorum.</span>
        </label>

        <div class="actions">
          <button class="btn btn-send" type="submit"><i class="fa-solid fa-paper-plane"></i> Gönder</button>
          <button class="btn btn-clear" type="reset">Formu Temizle</button>
        </div>

        <div class="help-note">* Bu alanlar zorunludur.</div>
      </form>
    </div>

  </section>
</main>

<script>
document.getElementById('btnCatalog')?.addEventListener('click', () => {
  alert('Franchise kataloğu yakında eklenecektir.');
});
document.getElementById('kvkkLink')?.addEventListener('click', () => {
  window.open('/kvkk.php', '_blank');
});
document.getElementById('ticariLink')?.addEventListener('click', () => {
  window.open('/cerez-politikasi.php', '_blank');
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
