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
            "INSERT INTO franchise_applications
             (full_name,phone,email,city,age,investment,message,kvkk,commercial,ip)
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([$name,$phone,$email,$city,$age,$invest,$msg ?: null,$kvkk,$com,client_ip()]);

        $to = setting('mail_to', MAIL_TO);
        if ($to) {
            $body = "<h3>Yeni Franchise Başvurusu</h3>"
                  . "<p><b>Ad-Soyad:</b> " . e($name) . "</p>"
                  . "<p><b>Telefon:</b> " . e($phone) . "</p>"
                  . "<p><b>E-posta:</b> " . e($email) . "</p>"
                  . "<p><b>Şehir:</b> " . e($city) . "</p>"
                  . "<p><b>Yaş:</b> " . $age . "</p>"
                  . "<p><b>Yatırım:</b> " . e($invest) . " Milyon TL</p>"
                  . ($msg ? "<p><b>Notlar:</b><br>" . nl2br_safe($msg) . "</p>" : "");
            send_mail($to, 'Franchise Başvurusu: ' . $name, $body);
        }

        flash_set('success', 'Başvurunuz alındı. En kısa sürede sizinle iletişime geçeceğiz.');
        header('Location: /franchise.php#form'); exit;
    }
}

$page_slug  = 'franchise';
$page_title = 'Franchise';
$page_desc  = 'TACOS GIDA Ailesine Katılın';
$extra_css = "
.fr-wrap{max-width:980px;margin:24px auto;padding:0 18px 40px}
.fr-hero{background:linear-gradient(135deg,#3A5F0B,#5e8a1a);color:#fff;border-radius:14px;padding:32px;text-align:center;margin-bottom:20px}
.fr-hero h1{font-size:24px;margin:0 0 10px}
.fr-hero p{font-size:14px;line-height:1.6;opacity:.95;margin:6px 0}
.fr-hero .btn-cta{display:inline-block;margin-top:14px;background:#fff;color:var(--brand);padding:11px 22px;border-radius:8px;font-weight:700;border:0;cursor:pointer;font-size:14px}
.fr-form{background:#fff;border-radius:14px;padding:28px;box-shadow:0 6px 20px rgba(0,0,0,.04)}
.fr-form h2{color:var(--brand);font-size:18px;margin:0 0 16px}
.f-row{margin-bottom:12px}
.f-row label{display:block;font-size:13px;font-weight:600;margin-bottom:4px}
.f-row input{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit}
.f-row textarea{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;resize:vertical}
.f-row input:focus,.f-row textarea:focus{outline:0;border-color:var(--brand);box-shadow:0 0 0 3px rgba(58,95,11,.12)}
.f-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.choice-row{display:flex;align-items:center;gap:10px;font-size:14px;margin-bottom:6px;cursor:pointer}
.choice-row input{width:18px;height:18px}
.check{display:flex;align-items:flex-start;gap:8px;font-size:13px;color:var(--muted);line-height:1.4;margin-bottom:8px;cursor:pointer}
.check input{margin-top:3px}
.btn-row{display:flex;gap:10px;margin-top:14px}
.btn-send{flex:1;background:var(--brand);color:#fff;border:0;padding:13px;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px}
.btn-clear{flex:0 0 auto;background:transparent;color:var(--muted);border:1px solid #d1d5db;padding:13px 20px;border-radius:8px;cursor:pointer;font-size:14px}
@media(max-width:680px){.f-grid{grid-template-columns:1fr}}
";
require __DIR__ . '/includes/header.php';
?>

<main class="fr-wrap">
  <div class="fr-hero">
    <h1>TACOS GIDA Ailesine Katılmak İster misiniz?</h1>
    <p>French tacos lezzetiyle Türkiye'nin en hızlı büyüyen markalarından birinin parçası olun.</p>
    <p>Detaylı franchise kataloğumuz için bizimle iletişime geçin.</p>
  </div>

  <form class="fr-form" id="form" method="post" autocomplete="on">
    <h2>Franchise Başvuru Formu</h2>
    <?= csrf_field() ?>
    <div class="f-grid">
      <div class="f-row"><label>Ad-Soyad *</label><input type="text" name="adsoyad" required></div>
      <div class="f-row"><label>İletişim Numarası *</label><input type="tel" name="telefon" required placeholder="5XXXXXXXXX"></div>
    </div>
    <div class="f-grid">
      <div class="f-row"><label>E-Posta *</label><input type="email" name="eposta" required></div>
      <div class="f-row"><label>Şehir *</label><input type="text" name="sehir" required></div>
    </div>
    <div class="f-grid">
      <div class="f-row"><label>Yaş *</label><input type="number" name="yas" min="18" max="99" required></div>
      <div></div>
    </div>
    <div class="f-row">
      <label>Yatırım Aralığı *</label>
      <label class="choice-row"><input type="radio" name="yatirim" value="8-9" required> 8 Milyon - 9 Milyon TL</label>
      <label class="choice-row"><input type="radio" name="yatirim" value="9-10"> 9 Milyon - 10 Milyon TL</label>
      <label class="choice-row"><input type="radio" name="yatirim" value="10+"> 10 Milyon ve Üstü</label>
    </div>
    <div class="f-row">
      <label>Eklemek İstedikleriniz</label>
      <textarea name="mesaj" rows="3"></textarea>
    </div>
    <label class="check"><input type="checkbox" name="kvkk" required> <span><?= e(setting('kvkk_text', 'KVKK Aydınlatma Metnini okudum, onaylıyorum.')) ?></span></label>
    <label class="check"><input type="checkbox" name="ticari"> <span><?= e(setting('commercial_text', 'Ticari elektronik ileti almayı kabul ediyorum.')) ?></span></label>
    <div class="btn-row">
      <button class="btn-send" type="submit">GÖNDER</button>
      <button class="btn-clear" type="reset">FORMU TEMİZLE</button>
    </div>
  </form>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
