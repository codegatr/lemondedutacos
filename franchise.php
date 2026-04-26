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
        header('Location: /franchise.php?ok=1#form'); exit;
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Le Monde Du Tacos – Le Goût Authentique du French Tacos - Franchise</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    :root{
      --brand:#3A5F0B;
      --brand2:#b24545;
      --ink:#1f2937;
      --muted:#6b7280;
      --bg:#ffffff;
      --shadow: 0 10px 30px rgba(0,0,0,.18);
      --max: 1180px;

      --ok:#1f8a4c;
      --danger:#e23b3b;
      --line:#e5e7eb;
      --soft:#f3f4f6;
    }

    *{ box-sizing:border-box; }
    body{
      margin:0;
      overflow:hidden; /* desktop: görsel sabit */
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color:var(--ink);
      background:var(--bg);
    }
    a{ color:inherit; text-decoration:none; }
    button{ font:inherit; }

    /* ======= TOP BAR ======= */
.topbar{
  position: sticky;
  top: 0;
  z-index: 999;
  background: #fff;
  width: 100%;
}

.topbar-inner{
  max-width: var(--max);
  margin: 0 auto;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  width: 100%;
}

    .brand{
      position:relative;
      display:flex;
      align-items:center;
      gap:2px;
      min-width: 220px;
    }

    .logo-wrapper{
      width:50px;
      height:50px;
      margin-left: 80px;
      position:relative;
    }

    .brand-logo{
      position:absolute;
      height:170px;
      width:auto;
      left:50%;
      transform:translateX(-70%);
      top:-40px;
      pointer-events:none;
    }
    .brand-text{ margin-top: 20px; display:flex; flex-direction:column; justify-content:center; }
    .brand .logo{ font-family: Georgia, serif; font-size:28px; line-height:1; color:#3A5F0B; font-weight:700; }

    .nav{ display:flex; align-items:center; gap:12px; }
.nav a{
  padding: 9px 12px;
  border-radius: 6px;
  font-family: 'Retrim', sans-serif;
  font-weight: 400;
  font-size: 12px;
  letter-spacing:.6px;
  text-transform: uppercase;
  color:#1f2937;
  white-space: nowrap;
  position: relative;
  overflow: hidden;
  transition: all .25s ease;
}
    .nav a.active{
      background: var(--brand);
      color:#fff;
      box-shadow: 0 6px 18px rgba(139,45,45,.25);
    }
    .nav a:not(.active):hover{
      background: var(--brand);
      color:#fff;
      box-shadow: 0 6px 18px rgba(139,45,45,.35);
    }

    /* Mobil hamburger */
    .hamburger{
      display:none;
      width:44px;
      height:40px;
      border:1px solid rgba(0,0,0,.12);
      border-radius:10px;
      background:#fff;
      align-items:center;
      justify-content:center;
      cursor:pointer;
      z-index: 9999;
    }
    .hamburger span{
      display:block;
      width:18px; height:2px;
      background:#111827;
      position:relative;
    }
    .hamburger span::before,
    .hamburger span::after{
      content:"";
      position:absolute; left:0;
      width:18px; height:2px;
      background:#111827;
    }
    .hamburger span::before{ top:-6px; }
    .hamburger span::after{ top:6px; }

    /* ======= HERO ======= */
    .hero{
      position:relative;
      min-height: calc(100vh - 164px);
      background: url("static/img/franchise.jpg") center center / cover no-repeat;
      transform-origin:center;
      overflow:hidden;
      rotate:-2deg;
      scale:.95;
      z-index:999;
    }
    .hero::after{
      content:"";
      position:absolute;
      inset:0;
      z-index:1;
      background: linear-gradient(180deg,
        rgba(0,0,0,.12) 0%,
        rgba(0,0,0,.25) 55%,
        rgba(0,0,0,.35) 100%);
      pointer-events:none;
    }
    .hero::before{
      content:"";
      position:absolute;
      top:0; left:0; right:0;
      height:6px;
      background: linear-gradient(90deg, rgba(139,45,45,.9), rgba(200,86,86,.8), rgba(139,45,45,.9));
      opacity:.95;
      z-index:2;
      pointer-events:none;
    }

    /* ======= FRANCHISE ======= */
    .franchise-wrap{
      position:absolute;
      top:50%;
      left:50%;
      transform: translate(-52%, -50%) rotate(2deg); /* hafif sola = sağ alt kurtulur */
      z-index:5;
      width: min(1380px, 98%);
    }

    .franchise-card{
      background: rgba(255,255,255,.96);
      border:1px solid rgba(255,255,255,.65);
      border-radius: 22px;
      box-shadow: 0 24px 60px rgba(0,0,0,.35);
      overflow:hidden;
      display:grid;
      grid-template-columns: 1.15fr 1.05fr; /* sağ biraz geniş */
      min-height: 560px;
    }

    .f-left{
      padding: 124px 24px 18px;  /* artık 90px değil */
      border-right: 1px solid rgba(17,24,39,.08);
      background: linear-gradient(180deg, rgba(255,255,255,1), rgba(255,255,255,.95));
    }
    .f-right{
      padding: 16px 16px 14px;
      background: linear-gradient(180deg, rgba(255,255,255,1), rgba(250,250,250,1));
    }

    .f-badge{ color:#d11b1b; font-size: 13px; font-weight: 800; letter-spacing:.3px; margin-bottom: 8px; }
    .f-title{ font-size: 18px; font-weight: 900; margin: 0 0 10px; color:#111827; }
    .f-desc{ color:#111827; font-size: 13px; line-height: 1.6; margin: 0 0 10px; }

    .f-list{
      margin: 10px 0 10px;
      padding: 0;
      list-style: none;
      display:grid;
      gap: 6px;
    }
    .f-list li{
      display:flex;
      gap:10px;
      align-items:flex-start;
      color:#111827;
      font-size: 13px;
      line-height: 1.5;
    }
    .f-list i{ width: 18px; margin-top: 3px; color: #8b2d2d; opacity: .95; }

    .f-mail{ margin-top: 10px; font-weight: 800; color:#111827; }
    .f-cta{ margin-top: 14px; display:flex; gap: 10px; align-items:center; flex-wrap:wrap; }

    .btn-cta{
      border:0;
      background: #0b4f3a;
      color:#fff;
      padding: 12px 16px;
      border-radius: 10px;
      font-weight: 900;
      cursor:pointer;
      box-shadow: 0 14px 30px rgba(11,79,58,.25);
      transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
    }
    .btn-cta:hover{ transform: translateY(-1px); filter: brightness(1.03); box-shadow: 0 18px 36px rgba(11,79,58,.28); }

    .form{ display:flex; flex-direction:column; gap: 10px; }

    /* Label solda - input sağda (desktop) */
    .field{
      display:grid;
      grid-template-columns: 160px 1fr;
      align-items:center;
      gap: 10px;
      padding: 8px 10px;
      border-radius: 12px;
      border: 1px solid var(--line);
      background: #fff;
    }
    .field label{
      font-size: 12px;
      font-weight: 900;
      color:#111827;
      margin:0;
      text-align:left;
      white-space:nowrap;
    }
    .field input{
      width:100%;
      height: 38px;
      border-radius: 10px;
      border: 1px solid #cfd6df;
      padding: 0 12px;
      outline: none;
      font-size: 13.5px;
      background: #fff;
      transition: box-shadow .15s ease, border-color .15s ease;
    }
    .field input:focus{
      border-color: rgba(139,45,45,.45);
      box-shadow: 0 0 0 4px rgba(139,45,45,.10);
    }
    .field.soft{ background: var(--soft); border-color: var(--soft); }
    .field.soft input{ background:#fff; }

    .choices{
      padding: 8px 10px;
      border-radius: 12px;
      border: 1px solid var(--line);
      background:#fff;
    }
    .choices .choices-title{ font-size: 12px; font-weight: 900; color:#111827; margin-bottom: 6px; text-align:left; }
    .choice-row{ display:flex; align-items:center; gap: 10px; margin: 6px 0; font-size: 12.5px; color:#111827; }
    .choice-row input{ width: 18px; height: 18px; }

    .link-row{
      padding: 8px 10px;
      border-radius: 12px;
      border: 1px solid var(--line);
      background: #fff;
      font-size: 12.5px;
      font-weight: 800;
      color:#1f4ed8;
      cursor:pointer;
      user-select:none;
      text-align:left;
    }
    .link-row:hover{ text-decoration: underline; }

    .checks{
      padding: 8px 10px;
      border-radius: 12px;
      border: 1px solid var(--line);
      background:#fff;
    }
    .checks .check{ display:flex; align-items:flex-start; gap: 10px; font-size: 12.5px; color:#111827; line-height: 1.4; }
    .checks input{ width: 18px; height: 18px; margin-top: 2px; }

    .actions{
      margin-top: 6px;
      display:flex;
      gap: 10px;
      justify-content:flex-end;
      flex-wrap:wrap;
    }
    .btn{
      border:0;
      height: 38px;
      padding: 0 14px;
      border-radius: 10px;
      font-weight: 900;
      cursor:pointer;
      transition: transform .12s ease, filter .12s ease;
      font-size: 13px;
    }
    .btn:active{ transform: translateY(1px); }
    .btn-send{ background: var(--ok); color:#fff; }
    .btn-clear{ background: var(--danger); color:#fff; }

    .help-note{ margin-top: 6px; color: #6b7280; font-size: 12px; line-height: 1.5; }

    /* ======= FOOTER ======= */
    .footer{
      max-width: var(--max);
      margin: 0 auto;
      padding: 18px 18px 26px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:14px;
      color: var(--muted);
      font-size: 12px;
    }

    .social-nav{ padding:0; margin:0; list-style:none; display:flex; align-items:center; gap:10px; }
    .social-nav li{ display:inline-block; }
    .social-nav a{
      display:inline-block;
      width:36px; height:36px; line-height:36px;
      text-align:center;
      color:#fff;
      text-decoration:none;
      background:#000;
      border-radius:10px;
      transition: .35s ease;
      overflow:hidden;
      font-size:18px;
    }
    .model-2 .facebook{ background:#3B579D; }
    .model-2 .instagram{ background:#E1306C; }
    .model-2 .twitter{ background:#111827; }
    .model-2 .youtube{ background:#FF0000; }

    /* =========================
       MOBİL DÜZEN (en önemli kısım)
       ========================= */
    @media (max-width: 860px){
      body{ overflow:auto; } /* mobilde sayfa kayabilir */
      .hamburger{ display:flex; }

  .topbar{
    flex-shrink: 0;
    z-index: 9999;
    background: #fff;
  }

  .topbar-inner{
    padding: 8px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
  }

  .brand{
    min-width: 0;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    white-space: nowrap;
  }


  .brand-text{
    margin-top: 12px;
    margin-left: 0;
    display: flex;
    align-items: center;
    min-width: 0;
  }

  .brand .logo{
    font-size: 20px;
    line-height: 1;
    white-space: nowrap;
  }

  .hamburger{
    display: flex;
    flex-shrink: 0;
  }

  .nav{
    position: absolute;
    top: 58px;
    right: 12px;
    left: 12px;
    background: #fff;
    border: 1px solid rgba(0,0,0,.10);
    border-radius: 14px;
    box-shadow: var(--shadow);
    padding: 10px;
    display: none;
    flex-direction: column;
    align-items: stretch;
    gap: 6px;
    z-index: 9998;
  }

  .nav.open{
    display: flex;
  }

  .nav a{
    padding: 12px 12px;
  }


      /* mobilde hero düz olsun, kart tam otursun */
      .hero{
        rotate: 0deg;
        scale: 1;
        min-height: auto;
        padding: 16px 0 18px; /* kart için nefes */
      }

      .franchise-wrap{
        position: relative;
        top: 0; left: 0;
        transform: none;     /* kritik */
        width: min(980px, 94%);
        margin: 0 auto;
      }

      .franchise-card{
        grid-template-columns: 1fr;
        min-height: unset;
      }

      .f-left{
        border-right:0;
        border-bottom: 1px solid rgba(17,24,39,.08);
        padding: 20px 16px 14px;
      }
      .f-right{
        padding: 14px 14px 14px;
      }

      .footer{
        flex-direction:column;
        align-items:flex-start;
      }
    }
@media (max-width: 860px){
  .logo-wrapper{
    width: 40px !important;
    height: 40px !important;
    margin-left: 30px !important;
    position: relative !important;
    flex: 0 0 40px !important;
  }

  .brand-logo{
    position: absolute !important;
    height: 90px !important;
    width: auto !important;
    left: 50% !important;
    top: -16px !important;
    transform: translateX(-70%) !important;
    pointer-events: none !important;
  }
}
    /* 680px altı: label ve input alt alta */

  </style>
</head>

<body>
  <header class="topbar">
    <div class="topbar-inner">
      <a class="brand" href="/index.php">
        <div class="logo-wrapper">
          <img class="brand-logo" src="/static/img/logos/LMD LOGOArtboard1.png" alt="TACOS Logo">
        </div>
        <div class="brand-text">
          <div class="logo" style="font-style: italic">Le Monde Du Tacos</div>
        </div>
      </a>

      <button class="hamburger" id="hamburger" aria-label="Menüyü aç/kapat">
        <span></span>
      </button>

      <nav class="nav" id="nav">
        <a href="/index.php">ANASAYFA</a>
        <a href="/kurumsal.php">KURUMSAL</a>
        <a href="/subeler.php">ŞUBELER</a>
        <a href="/kampanyalar.php">KAMPANYALAR</a>
        <a class="active" href="/franchise.php">FRANCHISE</a>
        <a href="/iletisim.php">İLETİŞİM</a>
      </nav>
    </div>
  </header>
<?php foreach (flash_get() as $f):
  $bg = $f['type'] === 'success' ? '#16a34a' : ($f['type'] === 'error' ? '#dc2626' : '#3a5f0b');
?>
<div style="position:fixed;top:20px;right:20px;z-index:9999;background:<?= $bg ?>;color:#fff;padding:14px 20px;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.2);font-weight:600;max-width:360px"><?= e($f['msg']) ?></div>
<?php endforeach; ?>


  <main class="hero" role="main" aria-label="Ana görsel alanı">
    <section class="franchise-wrap" aria-label="Franchise Başvuru Formu">
      <div class="franchise-card">

        <div class="f-left">
          <div class="f-badge">Franchise Başvuru Formu - TACOS GIDA</div>
          <h2 class="f-title">TACOS GIDA Ailesine Katılmak İster misiniz?</h2>

          <p class="f-desc">
            Fast Food kültürünü ve sektörü en iyi bilen markanın desteğiyle kendi işinizin patronu olmak için harika bir fırsat!
          </p>
          <p class="f-desc">
            Türkiye’nin dört bir yanında büyümeye devam eden TACOS GIDA ekibine siz de katılın!
            Güçlü marka desteği, kârlı iş modeli, tecrübeli operasyon ağı ve kapsamlı eğitim sistemiyle
            kendi işinizi kurma yolculuğunuzda yanınızdayız.
          </p>

          <ul class="f-list">
            <li><i class="fa-solid fa-location-dot"></i> Lokasyon seçimi ve gelir modeli yaratma</li>
            <li><i class="fa-solid fa-gear"></i> Operasyonel destek ve sistem aktarımı</li>
            <li><i class="fa-solid fa-bullhorn"></i> Profesyonel pazarlama ve reklam uygulamaları</li>
            <li><i class="fa-solid fa-briefcase"></i> Sözleşmeden itibaren sürdürülebilir iş yönetimi</li>
            <li><i class="fa-solid fa-flask"></i> Profesyonel URGE ve ARGE desteği</li>
          </ul>

          <p class="f-desc">
            Siz de bulunduğunuz şehirde TACOS GIDA şubesi açmak isterseniz, aşağıdaki formu doldurarak bizimle iletişime geçebilirsiniz.
            Başvuru sonrasında ekibimiz en kısa sürede size ulaşacak.
          </p>

          <div class="f-desc" style="font-weight:900; margin-top:10px;">👉 Formu doldurun, ilk adımı birlikte atalım.</div>
          <div class="f-mail">info@lemondedutacos.com </div>

          <div class="f-cta">
            <button class="btn-cta" type="button" id="btnCatalog">Franchise Kataloğu İçin Tıklayınız.</button>
          </div>
        </div>

        <div class="f-right">
          <form class="form" id="frForm" method="post" action="franchise.php#form" autocomplete="on"><?= csrf_field() ?>
            <div class="field soft">
              <label for="adsoyad">Ad-Soyad(*)</label>
              <input id="adsoyad" name="adsoyad" type="text" placeholder="Adınız Soyadınız" required />
            </div>

            <div class="field soft">
              <label for="telefon">İletişim Numarası (*)</label>
              <input id="telefon" name="telefon" type="tel" inputmode="tel" placeholder="5_________" required />
            </div>

            <div class="field soft">
              <label for="eposta">E-Posta (*)</label>
              <input id="eposta" name="eposta" type="email" placeholder="E-Posta adresiniz" required />
            </div>

            <div class="field soft">
              <label for="sehir">Şehir (*)</label>
              <input id="sehir" name="sehir" type="text" placeholder="Şehrinizi yazınız" required />
            </div>

            <div class="field soft">
              <label for="yas">Yaş (*)</label>
              <input id="yas" name="yas" type="number" min="18" max="99" placeholder="Yaşınızı yazınız" required />
            </div>

            <div class="choices">
              <div class="choices-title">Yatırım Yapılmak İstenen Tutar? (*)</div>
              <label class="choice-row"><input type="radio" name="yatirim" value="8-9" required> 8 Milyon - 9 Milyon TL</label>
              <label class="choice-row"><input type="radio" name="yatirim" value="9-10"> 9 Milyon - 10 Milyon TL</label>
              <label class="choice-row"><input type="radio" name="yatirim" value="10+"> 10 Milyon ve Üstü</label>
            </div>

            <div class="link-row" id="kvkkLink">KVKK Aydınlatma Metni</div>

            <div class="checks">
              <label class="check">
                <input type="checkbox" id="kvkk" required>
                <span>Kvkk Aydınlatma metnini Kabul ediyorum.</span>
              </label>
            </div>

            <div class="link-row" id="ticariLink">Ticari Elektronik İleti Onayı kabul ediyorum.</div>

            <div class="checks">
              <label class="check">
                <input type="checkbox" id="ticari">
                <span>Ticari Elektronik İleti Onayı Veriyorum.</span>
              </label>
            </div>

            <div class="actions">
              <button class="btn btn-send" type="submit">GÖNDER</button>
              <button class="btn btn-clear" type="button" id="btnClear">FORMU TEMİZLE</button>
            </div>

            <div class="help-note">
              * Bu alanlar zorunludur.
            </div>
          </form>
        </div>

      </div>
    </section>
  </main>

  <footer class="footer">
  <ul class="social-nav model-2" aria-label="Sosyal medya">
    <li><a class="facebook" href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a></li>
    <li><a class="instagram" href="https://www.instagram.com/lemondedutacos__?igsh=MWIzMDRzaWw0azhkbA%3D%3D&utm_source=qr" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a></li>
    <li><a class="twitter" href="#" aria-label="Twitter/X"><i class="fa-brands fa-x-twitter"></i></a></li>
    <li><a class="youtube" href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a></li>
  </ul>
  <nav class="footer-legal" aria-label="Yasal sayfalar">
    <a href="/kvkk.php">KVKK</a>
    <span>·</span>
    <a href="/cerez-politikasi.php">Çerez Politikası</a>
    <span>·</span>
    <a href="/gizlilik-politikasi.php">Gizlilik Politikası</a>
  </nav>
  <div class="footer-meta">
    <div style="font-weight:bold;font-family:'Georgia',serif;">
      Copyright © 2026 <span style="font-style:italic;text-decoration:underline;">Tüm Hakları Saklıdır</span>
    </div>
    <div style="font-size:11px;color:var(--muted);margin-top:2px;">
      Tasarım &amp; Geliştirme: <a href="https://www.codega.com.tr" target="_blank" rel="noopener" style="color:var(--brand);font-weight:700;text-decoration:none;">CODEGA</a>
    </div>
  </div>
</footer>
<style>
.footer{max-width:1280px !important;margin:0 auto !important;padding:22px 36px 26px !important;display:flex !important;align-items:center !important;justify-content:space-between !important;gap:48px !important;flex-wrap:wrap}
.footer > *{flex:1 1 0;display:flex;align-items:center}
.footer > .social-nav{justify-content:flex-start}
.footer > .footer-legal{justify-content:center;align-items:center;gap:10px;font-size:12px;flex-wrap:wrap}
.footer > .footer-meta{justify-content:flex-end;flex-direction:column;align-items:flex-end;gap:2px;text-align:right}
.footer-legal a{color:var(--muted);text-decoration:none;transition:color .2s}
.footer-legal a:hover{color:var(--brand);text-decoration:underline}
.footer-legal span{color:var(--muted);opacity:.5}
@media(max-width:940px){
  .footer{flex-direction:column !important;gap:14px !important;padding:18px 16px !important;text-align:center}
  .footer > *{flex:none !important;justify-content:center !important;width:100%}
  .footer > .footer-meta{align-items:center !important;text-align:center}
}
</style>

  <script>
    const btn = document.getElementById("hamburger");
    const nav = document.getElementById("nav");

    btn?.addEventListener("click", () => nav.classList.toggle("open"));
    document.addEventListener("click", (e) => {
      if (!nav.classList.contains("open")) return;
      const within = nav.contains(e.target) || btn.contains(e.target);
      if (!within) nav.classList.remove("open");
    });

    document.getElementById("btnCatalog")?.addEventListener("click", () => {
      alert("Katalog linkini buraya bağlayacağız.");
    });

    document.getElementById("kvkkLink")?.addEventListener("click", () => {
      alert("KVKK metni sayfasına/linkine yönlendireceğiz.");
    });
    document.getElementById("ticariLink")?.addEventListener("click", () => {
      alert("Ticari ileti onayı metni sayfasına/linkine yönlendireceğiz.");
    });

    document.getElementById("btnClear")?.addEventListener("click", () => {
      document.getElementById("frForm").reset();
    });

    const tel = document.getElementById("telefon");
    tel?.addEventListener("input", () => {
      tel.value = tel.value.replace(/[^\d]/g, "").slice(0, 10);
    });

    document.getElementById("frForm")?.addEventListener("submit", (e) => {
      e.preventDefault();
      alert("Başvurunuz alındı (demo).");
      e.target.reset();
    });
  </script>

<button class="scroll-top" id="scrollTop" aria-label="Yukarı çık" type="button">
  <i class="fa-solid fa-chevron-up"></i>
</button>
<style>
.scroll-top{position:fixed;bottom:24px;right:24px;width:46px;height:46px;border-radius:50%;background:var(--brand);color:#fff;border:none;cursor:pointer;z-index:1000;display:flex;align-items:center;justify-content:center;font-size:16px;opacity:0;visibility:hidden;transform:translateY(8px);transition:opacity .25s,transform .25s,visibility .25s,background .2s;box-shadow:0 6px 16px rgba(0,0,0,.18)}
.scroll-top.visible{opacity:1;visibility:visible;transform:translateY(0)}
.scroll-top:hover{background:#1a3d0a;transform:translateY(-3px);box-shadow:0 8px 22px rgba(0,0,0,.25)}
@media(max-width:640px){.scroll-top{bottom:18px;right:18px;width:42px;height:42px;font-size:14px}}
</style>
<script>
(function(){
  const btn=document.getElementById('scrollTop');
  if(!btn)return;
  if(getComputedStyle(document.body).overflow==='hidden'){btn.style.display='none';return;}
  const onScroll=()=>{(window.scrollY||document.documentElement.scrollTop)>320?btn.classList.add('visible'):btn.classList.remove('visible');};
  window.addEventListener('scroll',onScroll,{passive:true});
  btn.addEventListener('click',()=>window.scrollTo({top:0,behavior:'smooth'}));
  onScroll();
})();
</script>
</body>

</html>