<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

// Form gönderildi
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
        header('Location: /iletisim.php?ok=1#form'); exit;
    }
}

$branches = db()->query("SELECT id, title FROM branches WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>TACOS Restaurant – İLETİŞİM</title>

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
    }

    *{ box-sizing:border-box; margin:0; padding:0; }
    body{
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color:var(--ink);
      background:var(--bg);
      overflow:hidden;
    }
    a{ color:inherit; text-decoration:none; }
    button{ font:inherit; cursor:pointer; }

    /* ======= TOP BAR ======= */
    .topbar{
      position:sticky;
      top:0;
      z-index:999;
      background:#fff;
    }
    .topbar-inner{
      max-width:var(--max);
      margin:0 auto;
      padding:14px 18px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:16px;
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
    .brand-text{
      margin-top:20px;
      display:flex;
      flex-direction:column;
      justify-content:center;
    }
    .brand .logo{
      font-family:Georgia, serif;
      font-size:28px;
      line-height:1;
      color:#3A5F0B;
      font-weight:700;
    }
    .nav{
      display:flex;
      align-items:center;
      gap:12px;
    }
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
      background:var(--brand);
      color:#fff;
      box-shadow:0 6px 18px rgba(139,45,45,.25);
    }
    .nav a:not(.active):hover{
      background:var(--brand);
      color:#fff;
      box-shadow:0 6px 18px rgba(139,45,45,.35);
    }
    .nav a::after{
      content:"";
      position:absolute;
      top:0;
      left:-75%;
      width:50%;
      height:100%;
      background:linear-gradient(120deg, transparent 0%, rgba(255,255,255,.6) 50%, transparent 100%);
      transform:skewX(-20deg);
    }
    .nav a:not(.active):hover::after{
      animation:shine 1.6s ease forwards;
    }
    @keyframes shine{
      0%{ left:-75%; }
      100%{ left:130%; }
    }
    .nav a.active:hover{
      background:var(--brand);
      color:#fff;
    }

    /* Hamburger */
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
      z-index:9999;
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
      min-height:calc(100vh - 164px);
      background:url("static/img/yeni/o.png") center center / cover no-repeat;
      overflow:hidden;
      rotate:-2deg;
      scale:1.02;
      width:104vw;
      margin-left:calc(50% - 52vw);
      z-index:999;
    }
    .hero::after{
      content:"";
      position:absolute;
      inset:0;
      z-index:1;
      background:linear-gradient(180deg,
        rgba(0,0,0,.25) 0%,
        rgba(0,0,0,.45) 55%,
        rgba(0,0,0,.60) 100%);
      pointer-events:none;
    }
    .hero::before{
      content:"";
      position:absolute;
      top:0; left:0; right:0;
      height:6px;
      background:linear-gradient(90deg, rgba(139,45,45,.9), rgba(200,86,86,.8), rgba(139,45,45,.9));
      opacity:.95;
      z-index:2;
      pointer-events:none;
    }

    /* ======= CONTACT LAYOUT ======= */
    .contact-wrapper{
      position:absolute;
      top:50%;
      left:50%;
      transform:translate(-50%, -50%) rotate(2deg) scale(1.053);
      z-index:5;
      width:min(1060px, 92%);
      display:grid;
      grid-template-columns:1fr 1.55fr;
      gap:28px;
      align-items:start;
    }

    /* ===== GLASS CARD BASE ===== */
    .glass{
      background:#ffffff;
      backdrop-filter:none;
      -webkit-backdrop-filter:none;
      border:1px solid rgba(0,0,0,.08);

      border-radius:20px;
      color:#1f2937;
      box-shadow:
        0 12px 38px rgba(0,0,0,.38),
        inset 0 0 0 1px rgba(255,255,255,.12);
      overflow:hidden;
      position:relative;
    }
    .glass::after{
      content:"";
      position:absolute;
      top:-20%;
      left:-80%;
      width:60%;
      height:150%;
      background:linear-gradient(120deg,
        rgba(255,255,255,0) 0%,
        rgba(255,255,255,.65) 45%,
        rgba(255,255,255,0) 70%);
      transform:skewX(-20deg);
      opacity:0;
      pointer-events:none;
      transition:opacity .2s;
    }
    .glass:hover::after{
      opacity:.85;
      animation:glassShine .85s ease forwards;
    }
    @keyframes glassShine{
      0%{ left:-80%; }
      100%{ left:130%; }
    }

    /* ===== LEFT PANEL: Info + Social ===== */
    .info-panel{
      display:flex;
      flex-direction:column;
      gap:16px;
    }

    .info-head{
      padding:48px 28px 22px;
    }
    .info-head .tag{
      display:inline-block;
      background:rgba(221, 4, 4, 0.75);
      border:1px solid rgba(255,120,120,.35);
      border-radius:30px;
      padding:5px 14px;
      font-size:11px;
      font-weight:700;
      letter-spacing:1.2px;
      text-transform:uppercase;
      margin-bottom:14px;
    }
    .info-head h1{
      font-family:Georgia, serif;
      font-size:32px;
      font-weight:700;
      line-height:1.2;
      letter-spacing:.4px;
      margin-bottom:10px;
    }
.info-head p{
  font-size:14px;
  line-height:1.7;
  opacity:1;
  color:#4b5563;
}

    /* Contact Detail Items */
    .detail-list{
      display:flex;
      flex-direction:column;
      gap:0;
    }
    .detail-item{
      display:flex;
      align-items:flex-start;
      gap:14px;
      padding:16px 28px;
      border-top:1px solid rgba(255,255,255,.10);
      transition:background .2s;
      cursor:default;
    }
    .detail-item:hover{
      background:rgba(255,255,255,.06);
    }
.detail-item a{
  color:#1f2937;
  text-decoration:none;
}
.detail-item a:hover{
  color:#b24545;
}
    .d-icon{
      flex:0 0 auto;
      width:40px;
      height:40px;
      border-radius:12px;
      background:rgba(221, 4, 4, 0.75);
      border:1px solid rgba(178,69,69,.45);
      display:grid;
      place-items:center;
      font-size:16px;
      margin-top:2px;
      transition:transform .2s, background .2s;
    }
    .detail-item:hover .d-icon{
      transform:translateY(-2px);
      background:rgba(139,45,45,.65);
    }
    .d-content{
      flex:1;
    }
.d-label{
  font-size:10px;
  font-weight:700;
  letter-spacing:1px;
  text-transform:uppercase;
  opacity:1;
  color:#6b7280;
  margin-bottom:4px;
}
.d-value{
  font-size:14px;
  font-weight:500;
  line-height:1.5;
  color:#1f2937;
}

    /* Social row */
    .social-glass{
      padding:20px 28px;
    }
.social-glass .s-label{
  font-size:10px;
  font-weight:700;
  letter-spacing:1px;
  text-transform:uppercase;
  opacity:1;
  color:#6b7280;
  margin-bottom:14px;
}
    .social-row{
      display:flex;
      gap:10px;
    }
    .s-btn{
      width:44px;
      height:44px;
      border-radius:12px;
      border:1px solid rgba(255,255,255,.22);
      background:rgba(255,255,255,.10);
      display:grid;
      place-items:center;
      font-size:18px;
      color:#fff;
      transition:transform .18s ease, background .18s ease, border-color .18s ease;
      text-decoration:none;
    }
    .s-btn:hover{
      transform:translateY(-3px) scale(1.1);
      background:rgba(255,255,255,.20);
      border-color:rgba(255,255,255,.50);
    }
    .s-btn.fb:hover{ background:rgba(59,87,157,.55); border-color:rgba(59,87,157,.8); }
    .s-btn.ig:hover{ background:rgba(225,48,108,.55); border-color:rgba(225,48,108,.8); }
    .s-btn.tw:hover{ background:rgba(17,24,39,.70); border-color:rgba(255,255,255,.4); }
    .s-btn.yt:hover{ background:rgba(255,0,0,.55); border-color:rgba(255,0,0,.8); }

    /* ===== RIGHT PANEL: Form ===== */
    .form-panel{
      padding:36px 38px 38px;
    }
    .form-panel h2{
      font-family:Georgia, serif;
      font-size:24px;
      font-weight:700;
      letter-spacing:.4px;
      margin-bottom:6px;
    }
.form-panel .sub{
  font-size:13px;
  opacity:1;
  color:#6b7280;
  margin-bottom:28px;
  line-height:1.5;
}
    .form-grid{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:14px;
    }
    .form-group{
      display:flex;
      flex-direction:column;
      gap:6px;
    }
    .form-group.full{
      grid-column:1 / -1;
    }
.form-group label{
  font-size:11px;
  font-weight:700;
  letter-spacing:.9px;
  text-transform:uppercase;
  opacity:1;
  color:#374151;
}
    .form-group input,
    .form-group select,
    .form-group textarea{
      background:#ffffff;
      border:1px solid #d1d5db;
      color:#1f2937;
      border-radius:10px;

      font:inherit;
      font-size:14px;
      padding:11px 14px;
      outline:none;
      transition:border-color .2s, background .2s, box-shadow .2s;
      resize:none;
      -webkit-appearance:none;
      appearance:none;
    }
    .form-group input::placeholder,
    .form-group textarea::placeholder{
      color:#9ca3af;
    }
    .form-group select option{
      background:#1f2937;
      color:#fff;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus{
      border-color:rgba(200,86,86,.75);
      background:rgba(255,255,255,.13);
      box-shadow:0 0 0 3px rgba(139,45,45,.20);
    }

    /* Rating stars */
    .star-row{
      display:flex;
      gap:6px;
    }
    .star-row label{
      font-size:24px;
      cursor:pointer;
      color:rgba(240, 217, 16, 0.3);
      transition:color .15s, transform .15s;
      user-select:none;
      letter-spacing:2px;
    }
    .star-row input[type=radio]{ display:none; }
    .star-row:hover label{ color:rgba(255,200,50,.55); }
    .star-row label:hover,
    .star-row label:hover ~ label{ color:rgba(255,200,50,.90) !important; }
    /* reverse trick */
    .star-row{
      flex-direction:row-reverse;
      justify-content:flex-end;
    }
    .star-row input:checked ~ label,
    .star-row label:hover,
    .star-row label:hover ~ label{ color:rgba(255,200,50,.90); }

    /* Submit */
    .btn-submit{
      grid-column:1 / -1;
      margin-top:4px;
      padding:14px 28px;
      border-radius:12px;
      border:none;
      background:linear-gradient(135deg, var(--brand) 0%, #c45252 100%);
      color:#fff;
      font-size:14px;
      font-weight:700;
      letter-spacing:.8px;
      text-transform:uppercase;
      cursor:pointer;
      display:flex;
      align-items:center;
      justify-content:center;
      gap:10px;
      box-shadow:0 8px 24px rgba(139,45,45,.40);
      transition:transform .2s ease, box-shadow .2s ease, filter .2s;
      position:relative;
      overflow:hidden;
    }
    .btn-submit::after{
      content:"";
      position:absolute;
      top:-20%;
      left:-80%;
      width:55%;
      height:140%;
      background:linear-gradient(120deg,
        rgba(255,255,255,0) 0%,
        rgba(255,255,255,.55) 45%,
        rgba(255,255,255,0) 70%);
      transform:skewX(-20deg);
      opacity:0;
      pointer-events:none;
    }
    .btn-submit:hover{
      transform:translateY(-2px);
      box-shadow:0 14px 32px rgba(139,45,45,.55);
      filter:brightness(1.08);
    }
    .btn-submit:hover::after{
      opacity:.9;
      animation:btnShine .7s ease forwards;
    }
    @keyframes btnShine{
      0%{ left:-80%; }
      100%{ left:130%; }
    }
    .btn-submit:active{
      transform:translateY(0);
    }

    /* Success toast */
    .toast{
      display:none;
      grid-column:1/-1;
      background:rgba(34,197,94,.18);
      border:1px solid rgba(34,197,94,.45);
      border-radius:10px;
      padding:12px 16px;
      font-size:13px;
      font-weight:600;
      color:#bbf7d0;
      align-items:center;
      gap:10px;
    }
    .toast.show{ display:flex; animation:fadeIn .35s ease; }
    @keyframes fadeIn{ from{opacity:0; transform:translateY(4px)} to{opacity:1; transform:translateY(0)} }

    /* ======= FOOTER ======= */
    .footer{
      max-width:var(--max);
      margin:0 auto;
      padding:18px 18px 26px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:14px;
      color:var(--muted);
      font-size:12px;
    }
    .social-nav{
      padding:0; margin:0; list-style:none;
      display:flex; align-items:center; gap:10px;
    }
    .social-nav li{ display:inline-block; }
    .social-nav a{
      display:inline-block;
      width:36px; height:36px;
      line-height:36px;
      text-align:center;
      color:#fff;
      background:#000;
      border-radius:8px;
      position:relative;
      transition:.35s ease;
      overflow:hidden;
      font-size:18px;
    }
    .model-2 a{
      overflow:hidden; font-size:20px;
      border-radius:10px; margin:0;
    }
    .model-2 a:hover{
      background:#fff;
      text-shadow:
        0px 0px #d5d5d5, 1px 1px #d5d5d5, 2px 2px #d5d5d5, 3px 3px #d5d5d5,
        4px 4px #d5d5d5, 5px 5px #d5d5d5, 6px 6px #d5d5d5, 7px 7px #d5d5d5,
        8px 8px #d5d5d5, 9px 9px #d5d5d5, 10px 10px #d5d5d5;
    }
    .model-2 .facebook{ background:#3B579D; text-shadow:0 0 #2d4278,1px 1px #2d4278,2px 2px #2d4278,3px 3px #2d4278,4px 4px #2d4278,5px 5px #2d4278,6px 6px #2d4278,7px 7px #2d4278; }
    .model-2 .facebook:hover{ color:#3B579D; }
    .model-2 .instagram{ background:#E1306C; text-shadow:0 0 #b12353,1px 1px #b12353,2px 2px #b12353,3px 3px #b12353,4px 4px #b12353,5px 5px #b12353,6px 6px #b12353,7px 7px #b12353; }
    .model-2 .instagram:hover{ color:#E1306C; }
    .model-2 .twitter{ background:#111827; text-shadow:0 0 #0b1220,1px 1px #0b1220,2px 2px #0b1220,3px 3px #0b1220,4px 4px #0b1220,5px 5px #0b1220,6px 6px #0b1220,7px 7px #0b1220; }
    .model-2 .twitter:hover{ color:#111827; }
    .model-2 .youtube{ background:#FF0000; text-shadow:0 0 #c40000,1px 1px #c40000,2px 2px #c40000,3px 3px #c40000,4px 4px #c40000,5px 5px #c40000,6px 6px #c40000,7px 7px #c40000; }
    .model-2 .youtube:hover{ color:#FF0000; }

    /* ======= MOBILE ======= */
    @media (max-width:860px){
      body{ overflow-x:hidden; overflow-y:auto; }
      .brand{ min-width:auto; }
      .hamburger{ display:flex; }
      .nav{
        position:absolute;
        top:70px; right:12px; left:12px;
        background:#fff;
        border:1px solid rgba(0,0,0,.10);
        border-radius:14px;
        box-shadow:var(--shadow);
        padding:10px;
        display:none;
        flex-direction:column;
        align-items:stretch;
        gap:6px;
        z-index:9998;
      }
      .nav.open{ display:flex; }
      .nav a{ padding:12px 12px; }
      .topbar{
        z-index:9999;
      }
      .hero{
        rotate:0deg;
        scale:1;
        min-height:unset;
        height:auto;
        overflow:visible;
        display:block;
        padding:28px 14px 34px;
      }
      .contact-wrapper{
        position:relative;
        top:auto; left:auto;
        transform:none;
        width:100%;
        grid-template-columns:1fr;
        gap:18px;
      }
      .form-panel{
        padding:26px 22px 28px;
      }
      .form-grid{
        grid-template-columns:1fr;
      }
      .info-head{
        padding:22px 22px 16px;
      }
      .detail-item{
        padding:14px 22px;
      }
      .social-glass{
        padding:16px 22px;
      }
      /* ===== HEADER LOGO & YAZI — INDEX İLE AYNI ===== */
.topbar-inner{
  padding: 8px 14px;
}

.logo-wrapper{
  margin-left: 20px;
}

.brand-logo{
  height: 90px;
  top: -16px;
}

.brand .logo{
  font-size: 20px;
}

.brand-text{
  margin-top: 12px;
}
    }
    @media (max-width:480px){
      .topbar-inner{ padding:10px 14px; }
      .info-head h1{ font-size:24px; }
      .form-panel h2{ font-size:20px; }
      .footer{ flex-direction:column; align-items:flex-start; }
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
  </style>
</head>

<body>

  <!-- TOP BAR -->
  <header class="topbar">
    <div class="topbar-inner">
      <a class="brand" href="/index.php">
        <div class="logo-wrapper">
          <img class="brand-logo" src="/static/img/logos/LMD LOGOArtboard1.png" alt="TACOS Logo">
        </div>
        <div class="brand-text">
          <div class="logo" style="font-style:italic">Le Monde Du Tacos</div>
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
        <a href="/franchise.php">FRANCHISE</a>
        <a class="active" href="/iletisim.php">İLETİŞİM</a>
      </nav>
    </div>
  </header>
<?php foreach (flash_get() as $f):
  $bg = $f['type'] === 'success' ? '#16a34a' : ($f['type'] === 'error' ? '#dc2626' : '#3a5f0b');
?>
<div style="position:fixed;top:20px;right:20px;z-index:9999;background:<?= $bg ?>;color:#fff;padding:14px 20px;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.2);font-weight:600;max-width:360px"><?= e($f['msg']) ?></div>
<?php endforeach; ?>


  <!-- HERO -->
  <main class="hero" role="main">

    <div class="contact-wrapper">

      <!-- LEFT: Info Panel -->
      <div class="info-panel">

        <!-- Başlık kartı -->
        <div class="glass info-head">
          <span class="tag"><i class="fa-solid fa-envelope" style="margin-right:6px"></i>İletişim</span>
          <h1>Bize Ulaşın</h1>
          <p>Sorularınız, önerileriniz veya şikayetleriniz için aşağıdaki formu doldurun — en kısa sürede geri dönelim.</p>
        </div>

        <!-- Detaylar -->
        <div class="glass detail-list">

          <div class="detail-item">
            <div class="d-icon"><i class="fa-solid fa-phone"></i></div>
            <div class="d-content">
              <div class="d-label">Telefon</div>
              <div class="d-value">
                <a href="tel:+902124441234">+90 212 444 12 34</a>
              </div>
            </div>
          </div>

          <div class="detail-item">
            <div class="d-icon"><i class="fa-solid fa-envelope"></i></div>
            <div class="d-content">
              <div class="d-label">E-posta</div>
              <div class="d-value">
                <a href="mailto:iletisim@tacosgida.com">info@lemondedutacos.com</a>
              </div>
            </div>
          </div>

          <div class="detail-item">
            <div class="d-icon"><i class="fa-solid fa-clock"></i></div>
            <div class="d-content">
              <div class="d-label">Çalışma Saatleri</div>
              <div class="d-value">Her Gün &nbsp;10:00 – 23:00</div>
            </div>
          </div>

          <div class="detail-item">
            <div class="d-icon"><i class="fa-solid fa-location-dot"></i></div>
            <div class="d-content">
              <div class="d-label">Genel Merkez</div>
              <div class="d-value">Bahçelievler, Adnan Kahveci Blv. No:101/B<br>34180 İstanbul</div>
            </div>
          </div>

        </div>

      </div>

      <!-- RIGHT: Form Panel -->
      <div class="glass form-panel">
        <h2>Mesaj Gönderin</h2>
        <p class="sub">24 saat içinde yanıtlanacak — bilgileriniz gizli tutulmaktadır.</p>

        <form id="contactForm" method="post" action="iletisim.php#form"><?= csrf_field() ?>
          <div class="form-grid">

            <div class="form-group">
              <label for="fname">Ad</label>
              <input type="text" id="fname" name="fname" placeholder="Adınız" required>
            </div>

            <div class="form-group">
              <label for="lname">Soyad</label>
              <input type="text" id="lname" name="lname" placeholder="Soyadınız" required>
            </div>

            <div class="form-group">
              <label for="email">E-posta</label>
              <input type="email" id="email" name="email" placeholder="ornek@eposta.com" required>
            </div>

            <div class="form-group">
              <label for="phone">Telefon</label>
              <input type="tel" id="phone" name="phone" placeholder="+90 5XX XXX XX XX">
            </div>

            <div class="form-group full">
              <label for="branch">Şube</label>
              <select id="branch" name="branch">
                <option value="">— Şube seçin (opsiyonel) —</option>
                <?php foreach ($branches as $b): ?>
                  <option value="<?= (int)$b['id'] ?>"><?= e($b['title']) ?></option>
                <?php endforeach; ?>
                <option value="0">Genel / Tüm Şubeler</option>
              </select>
            </div>

            <div class="form-group full">
              <label for="subject">Konu</label>
              <input type="text" id="subject" name="subject" placeholder="Mesajınızın konusu" required>
            </div>

            <div class="form-group full">
              <label for="msg">Mesajınız</label>
              <textarea id="msg" name="msg" rows="5" placeholder="Görüş, öneri veya şikayetinizi buraya yazın…" required></textarea>
            </div>

            <!-- Değerlendirme -->
            <div class="form-group full">
              <label>Genel Deneyiminizi Değerlendirin</label>
              <div class="star-row" id="starRow">
                <input type="radio" name="rating" id="s5" value="5"><label for="s5" title="Mükemmel">★</label>
                <input type="radio" name="rating" id="s4" value="4"><label for="s4" title="İyi">★</label>
                <input type="radio" name="rating" id="s3" value="3"><label for="s3" title="Orta">★</label>
                <input type="radio" name="rating" id="s2" value="2"><label for="s2" title="Kötü">★</label>
                <input type="radio" name="rating" id="s1" value="1"><label for="s1" title="Çok kötü">★</label>
              </div>
            </div>

            <!-- Toast -->
            <div class="toast" id="toast">
              <i class="fa-solid fa-circle-check" style="font-size:18px;color:#06471e"></i>
              Mesajınız başarıyla iletildi! En kısa sürede geri döneceğiz.
            </div>

            <button type="submit" class="btn-submit">
              <i class="fa-solid fa-paper-plane"></i>
              Gönder
            </button>

          </div>
        </form>
      </div>

    </div>

  </main>

  <!-- FOOTER -->
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
    /* Hamburger */
    const btn = document.getElementById("hamburger");
    const nav = document.getElementById("nav");
    btn?.addEventListener("click", () => nav.classList.toggle("open"));
    document.addEventListener("click", e => {
      if (!nav.classList.contains("open")) return;
      if (!nav.contains(e.target) && !btn.contains(e.target)) nav.classList.remove("open");
    });

    /* Star rating — interactive highlight */
    const stars = document.querySelectorAll('#starRow label');

    /* Form submit */
    // Form gerçek submit oluyor (server-side handler iletisim.php başında)
    // ?ok=1 parametresi varsa toast göster
    if (window.location.search.includes('ok=1')) {
      const toast = document.getElementById('toast');
      if (toast) {
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 5000);
        history.replaceState(null, '', window.location.pathname + '#form');
      }
    }
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