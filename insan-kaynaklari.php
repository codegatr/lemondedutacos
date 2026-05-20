<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_required();

    if (!rate_limit('jobapp:' . client_ip(), 3, 1800)) {
        flash_set('error', 'Çok sık başvuru. Lütfen 30 dakika sonra tekrar deneyin.');
        header('Location: /insan-kaynaklari.php#basvuru'); exit;
    }

    $first  = clean_multi($_POST['fname'] ?? '');
    $last   = clean_multi($_POST['lname'] ?? '');
    $email  = clean_multi($_POST['email'] ?? '');
    $phone  = clean_multi($_POST['phone'] ?? '');
    $pos    = clean_multi($_POST['position'] ?? '');
    $branch = clean_multi($_POST['branch'] ?? '');
    $msg    = clean_multi($_POST['msg'] ?? '');

    $name = trim($first . ' ' . $last);

    $errors = [];
    if (mb_strlen($name) < 4)  $errors[] = 'Ad-Soyad zorunlu.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli e-posta giriniz.';
    if (mb_strlen($phone) < 10) $errors[] = 'Geçerli telefon giriniz.';

    $cv_path = null;
    if (!$errors && !empty($_FILES['cv']['name'])) {
        $cv_path = upload_file('cv', 'cv', array_merge(ALLOWED_DOC, ['png','jpg','jpeg']));
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
    } else {
        $stmt = db()->prepare(
            "INSERT INTO job_applications (job_id,full_name,email,phone,city,position,message,cv_path,ip)
             VALUES (NULL,?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([$name, $email, $phone, $branch ?: null, $pos ?: null, $msg ?: null, $cv_path, client_ip()]);

        $to = setting('mail_to', MAIL_TO);
        if ($to) {
            $body = "<h3>Yeni İş Başvurusu</h3>"
                  . "<p><b>Ad Soyad:</b> " . e($name) . "</p>"
                  . "<p><b>E-posta:</b> " . e($email) . "</p>"
                  . "<p><b>Telefon:</b> " . e($phone) . "</p>"
                  . ($pos ? "<p><b>Pozisyon:</b> " . e($pos) . "</p>" : "")
                  . ($branch ? "<p><b>Şube:</b> " . e($branch) . "</p>" : "")
                  . ($msg ? "<p><b>Notlar:</b><br>" . nl2br_safe($msg) . "</p>" : "")
                  . ($cv_path ? "<p><b>CV:</b> " . e(SITE_URL . $cv_path) . "</p>" : "");
            send_mail($to, 'İş Başvurusu: ' . $name, $body);
        }

        flash_set('success', 'Başvurunuz alındı.');
        header('Location: /insan-kaynaklari.php?ok=1#basvuru'); exit;
    }
}

$branches_list = db()->query("SELECT title FROM branches WHERE is_active = 1 ORDER BY sort_order")->fetchAll(PDO::FETCH_COLUMN);
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>İnsan Kaynakları – Le Monde Du Tacos</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="/static/fonts/retrim/stylesheet.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
  <style>
    /* ── INDEX NAVBAR/FOOTER VARIABLES ── */
    :root{
      --brand:#3A5F0B;--brand2:#b24545;--ink:#1f2937;--muted:#6b7280;--bg:#ffffff;--shadow:0 10px 30px rgba(0,0,0,.18);--max:1180px;
      /* IK page vars */
      --bg-dark:#1a1a1a;--bg-darker:#111111;--bg-card:#222222;--bg-card-hover:#2a2a2a;
      --red:#c8102e;--red-dark:#a00d24;--yellow:#f5c518;--yellow-light:#ffd940;
      --white:#ffffff;--gray-light:#cccccc;--gray:#999999;--gray-dark:#666666;--text:#e8e8e8;
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    html,body{max-width:100%;overflow-x:hidden;}
    body{font-family:'Poppins',system-ui,sans-serif;color:var(--ink);background:var(--bg);}
    a{color:inherit;text-decoration:none;}
    button{font:inherit;}

    /* ── TOPBAR (index.html'den aynen) ── */
    .topbar{position:sticky;top:0;z-index:999;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.08);}
    .topbar-inner{max-width:var(--max);margin:0 auto;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;gap:16px;}
    .brand{position:relative;display:flex;align-items:center;gap:2px;min-width:220px;}
    .logo-wrapper{width:50px;height:50px;margin-left:80px;position:relative;}
    .brand-logo{position:absolute;height:170px;width:auto;left:50%;transform:translateX(-70%);top:-40px;pointer-events:none;}
    .brand-text{margin-top:20px;display:flex;flex-direction:column;justify-content:center;}
    .brand .logo{font-family:Georgia,serif;font-size:28px;line-height:1;color:#3A5F0B;font-weight:700;}
    .nav{display:flex;align-items:center;gap:12px;}
    .nav a{padding:9px 12px;border-radius:6px;font-family:'Retrim',sans-serif;font-weight:400;font-size:12px;letter-spacing:.6px;text-transform:uppercase;color:#1f2937;white-space:nowrap;position:relative;overflow:hidden;transition:all .25s ease;}
    .nav a.active{background:var(--brand);color:#fff;}
    .nav a:not(.active):hover{background:var(--brand);color:#fff;}
    .hamburger{display:none;width:44px;height:40px;border:1px solid rgba(0,0,0,.12);border-radius:10px;background:#fff;align-items:center;justify-content:center;cursor:pointer;z-index:9999;}
    .hamburger span{display:block;width:18px;height:2px;background:#111827;position:relative;}
    .hamburger span::before,.hamburger span::after{content:"";position:absolute;left:0;width:18px;height:2px;background:#111827;}
    .hamburger span::before{top:-6px;}
    .hamburger span::after{top:6px;}

    /* ── SUBNAV ── */
    .subnav{background:#f9fafb;border-bottom:2px solid #e5e7eb;display:flex;justify-content:center;overflow-x:auto;}
    .subnav-inner{padding:0 18px;display:flex;gap:0;}
    .subnav-inner::-webkit-scrollbar{height:3px;}
    .subnav-inner::-webkit-scrollbar-thumb{background:var(--brand);border-radius:2px;}
    .subnav a{display:flex;align-items:center;gap:8px;padding:14px 20px;font-family:'Retrim',sans-serif;font-size:12px;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);white-space:nowrap;border-bottom:3px solid transparent;transition:all .2s;}
    .subnav a:hover{color:var(--brand);border-bottom-color:var(--brand);}
    .subnav a.active{color:var(--brand);border-bottom-color:var(--brand);font-weight:700;}
    .subnav a i{font-size:13px;}

    /* ── IK PAGE CONTENT (orijinalden) ── */
    .hero{margin-top:0;position:relative;min-height:420px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--bg-darker) 0%,#2a0a0f 50%,var(--bg-darker) 100%);overflow:hidden;text-align:center;}
    .hero::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 20% 50%,rgba(200,16,46,0.15) 0%,transparent 50%),radial-gradient(circle at 80% 50%,rgba(245,197,24,0.08) 0%,transparent 50%);}
    .hero-content{position:relative;z-index:2;padding:60px 20px;}
    .hero-badge{display:inline-block;background:rgba(200,16,46,0.15);border:1px solid rgba(200,16,46,0.4);color:var(--red);font-size:12px;font-weight:700;letter-spacing:3px;text-transform:uppercase;padding:8px 24px;border-radius:30px;margin-bottom:24px;animation:fadeDown .8s ease;}
    .hero h1{font-family:'Playfair Display',serif;font-size:clamp(36px,5vw,60px);font-weight:800;color:var(--white);line-height:1.15;margin-bottom:16px;animation:fadeDown .8s ease .1s both;}
    .hero h1 em{font-style:normal;background:linear-gradient(90deg,var(--yellow),var(--yellow-light));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .hero p{font-size:17px;color:var(--gray-light);max-width:560px;margin:0 auto 32px;line-height:1.7;animation:fadeDown .8s ease .2s both;}
    .hero-cta{display:inline-flex;align-items:center;gap:10px;background:var(--red);color:var(--white);padding:14px 36px;border-radius:50px;font-size:14px;font-weight:700;letter-spacing:1px;text-transform:uppercase;text-decoration:none;transition:all .3s;border:2px solid var(--red);animation:fadeDown .8s ease .3s both;}
    .hero-cta:hover{background:transparent;color:var(--red);transform:translateY(-2px);box-shadow:0 8px 24px rgba(200,16,46,0.3);}
    @keyframes fadeDown{from{opacity:0;transform:translateY(-16px);}to{opacity:1;transform:translateY(0);}}

    section{padding:80px 20px;}
    .container{max-width:1140px;margin:0 auto;}
    .section-label{display:inline-block;font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--yellow);margin-bottom:12px;}
    .section-title{font-family:'Playfair Display',serif;font-size:clamp(28px,3.5vw,42px);font-weight:800;color:var(--white);margin-bottom:16px;line-height:1.2;}
    .section-desc{font-size:16px;color:var(--gray);line-height:1.7;max-width:600px;margin-bottom:48px;}

    .why-section{background:var(--bg-darker);}
    .why-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;}
    .why-card{background:var(--bg-card);border:1px solid rgba(255,255,255,0.05);border-radius:16px;padding:36px 28px;transition:all .4s;position:relative;overflow:hidden;}
    .why-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--red),var(--yellow));transform:scaleX(0);transform-origin:left;transition:transform .4s;}
    .why-card:hover{transform:translateY(-6px);background:var(--bg-card-hover);}
    .why-card:hover::before{transform:scaleX(1);}
    .why-icon{width:52px;height:52px;display:flex;align-items:center;justify-content:center;background:rgba(200,16,46,0.1);border-radius:12px;font-size:24px;margin-bottom:20px;}
    .why-card h3{font-size:17px;font-weight:700;color:var(--white);margin-bottom:10px;}
    .why-card p{font-size:14px;color:var(--gray);line-height:1.7;}
    .position-card p{font-size:14px;color:var(--gray);line-height:1.6;margin-bottom:20px;flex:1;}

    .process-section{background:var(--bg-darker);}
    .process-steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:32px;}
    .step{text-align:center;position:relative;}
    .step-number{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--red),var(--red-dark));color:var(--white);font-size:24px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(200,16,46,0.3);}
    .step h3{font-size:17px;font-weight:700;color:var(--white);margin-bottom:8px;}
    .step p{font-size:14px;color:var(--gray);line-height:1.6;}

    .form-section{background:var(--bg-dark);}
    .form-wrapper{max-width:780px;margin:0 auto;}
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
    .form-group{display:flex;flex-direction:column;gap:8px;}
    .form-group.full{grid-column:1/-1;}
    .form-group label{font-size:13px;font-weight:600;color:var(--gray-light);}
    .form-group input,.form-group select,.form-group textarea{background:var(--bg-card);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:14px 16px;color:var(--white);font-size:14px;font-family:'Poppins',sans-serif;transition:border-color .3s,box-shadow .3s;outline:none;}
    .form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(200,16,46,0.15);}
    .form-group select option{background:var(--bg-card);}
    .form-group textarea{min-height:140px;resize:vertical;}
    .file-upload{border:2px dashed rgba(255,255,255,0.15);border-radius:10px;padding:28px;text-align:center;cursor:pointer;transition:border-color .3s;position:relative;}
    .file-upload:hover{border-color:var(--red);}
    .file-upload svg{width:36px;height:36px;stroke:var(--gray);fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round;margin-bottom:12px;}
    .file-upload p{font-size:14px;color:var(--gray);}
    .file-upload p span{color:var(--red);font-weight:600;}
    .file-upload small{font-size:12px;color:var(--gray-dark);}
    .file-upload input{display:none;}
    .file-name{font-size:13px;color:var(--yellow);margin-top:8px;font-weight:500;}
    .form-submit{grid-column:1/-1;text-align:center;margin-top:8px;}
    .btn-submit{background:var(--red);color:var(--white);border:none;padding:16px 48px;border-radius:50px;font-size:15px;font-weight:700;letter-spacing:.5px;cursor:pointer;transition:all .3s;}
    .btn-submit:hover{background:var(--red-dark);transform:translateY(-2px);box-shadow:0 8px 28px rgba(200,16,46,0.4);}
    .form-success{display:none;text-align:center;padding:60px 20px;}
    .form-success.show{display:block;}
    .form-success svg{width:64px;height:64px;stroke:var(--yellow);fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round;margin-bottom:20px;}
    .form-success h3{font-size:24px;font-weight:700;color:var(--white);margin-bottom:12px;}
    .form-success p{color:var(--gray);}

    /* ── INDEX FOOTER ── */
    .footer{max-width:var(--max);margin:0 auto;padding:18px 18px 26px;display:flex;align-items:center;justify-content:space-between;gap:14px;color:var(--muted);font-size:12px;}
    .social-nav{padding:0;margin:0;list-style:none;display:flex;align-items:center;gap:10px;}
    .social-nav li{display:inline-block;}
    .social-nav a{display:inline-block;width:36px;height:36px;line-height:36px;text-align:center;color:#fff;text-decoration:none;background:#000;border-radius:8px;transition:.35s ease;overflow:hidden;font-size:18px;}
    .model-2 a{font-size:20px;border-radius:10px;}
    .model-2 a:hover{background:#fff;text-shadow:0px 0px #d5d5d5,1px 1px #d5d5d5,2px 2px #d5d5d5,3px 3px #d5d5d5,4px 4px #d5d5d5;}
    .model-2 .facebook{background:#3B579D;}.model-2 .facebook:hover{color:#3B579D;}
    .model-2 .instagram{background:#E1306C;}.model-2 .instagram:hover{color:#E1306C;}
    .model-2 .twitter{background:#111827;}.model-2 .twitter:hover{color:#111827;}
    .model-2 .youtube{background:#FF0000;}.model-2 .youtube:hover{color:#FF0000;}

    .animate-in{opacity:0;transform:translateY(24px);transition:opacity .5s,transform .5s;}
    .animate-in.visible{opacity:1;transform:translateY(0);}

    @media(max-width:940px){
      .logo-wrapper{margin-left:20px;}.brand-logo{height:90px;top:-16px;}.brand .logo{font-size:20px;}.brand-text{margin-top:12px;}.brand{min-width:auto;}
      .hamburger{display:flex;}
      .nav{position:absolute;top:58px;right:12px;left:12px;background:#fff;border:1px solid rgba(0,0,0,.10);border-radius:14px;box-shadow:var(--shadow);padding:10px;display:none;flex-direction:column;align-items:stretch;gap:6px;z-index:9998;}
      .nav.open{display:flex;}.nav a{padding:12px;}
      .form-grid{grid-template-columns:1fr;}
      .footer{flex-direction:column;text-align:center;gap:8px;padding:14px 10px;}
    }
    @media(max-width:860px){
      .logo-wrapper{width:40px!important;height:40px!important;margin-left:30px!important;position:relative!important;flex:0 0 40px!important;}
      .brand-logo{position:absolute!important;height:90px!important;width:auto!important;left:50%!important;top:-16px!important;transform:translateX(-70%)!important;pointer-events:none!important;}
    }
    @media(max-width:600px){
    }
  </style>
</head>
<body>

<!-- TOPBAR (index.html) -->
<header class="topbar">
  <div class="topbar-inner">
    <a class="brand" href="/index.php">
      <div class="logo-wrapper">
        <img class="brand-logo" src="/static/img/logos/LMD LOGOArtboard1.png" alt="TACOS Logo">
      </div>
      <div class="brand-text"><div class="logo" style="font-style:italic">Le Monde Du Tacos</div></div>
    </a>
    <button class="hamburger" id="hamburger" aria-label="Menüyü aç/kapat"><span></span></button>
    <nav class="nav" id="nav">
      <a href="/index.php">ANASAYFA</a>
      <a href="/kurumsal.php">KURUMSAL</a>
      <a href="/subeler.php">ŞUBELER</a>
      <a href="/kampanyalar.php">KAMPANYALAR</a>
      <a href="/franchise.php">FRANCHISE</a>
      <a href="/iletisim.php">İLETİŞİM</a>
    </nav>
  </div>
</header>
<?php foreach (flash_get() as $f):
  $bg = $f['type'] === 'success' ? '#16a34a' : ($f['type'] === 'error' ? '#dc2626' : '#3a5f0b');
?>
<div style="position:fixed;top:20px;right:20px;z-index:9999;background:<?= $bg ?>;color:#fff;padding:14px 20px;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.2);font-weight:600;max-width:360px"><?= e($f['msg']) ?></div>
<?php endforeach; ?>


<!-- SUBNAV -->
<nav class="subnav">
  <div class="subnav-inner">
    <a href="/kurumsal.php"><i class="fa-solid fa-building"></i> Kurumsal</a>
    <a href="/tarihce.php"><i class="fa-solid fa-clock-rotate-left"></i> Tarihçe</a>
    <a href="/medya.php"><i class="fa-solid fa-photo-film"></i> Medya</a>
    <a href="/uretim.php"><i class="fa-solid fa-industry"></i> Üretim</a>
    <a class="active" href="/insan-kaynaklari.php"><i class="fa-solid fa-users"></i> İnsan Kaynakları</a>
  </div>
</nav>

<!-- HERO (orijinal ik sayfa) -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-badge">Kariyer</div>
    <h1>Le Monde'da<br><em>Kariyer Yap</em></h1>
    <p>French tacos tutkusunu paylaşan, enerjik ve gelişime açık bir ekibin parçası ol. Büyüyen bir markada fark yarat.</p>
    <a href="#basvuru" class="hero-cta">Hemen Başvur →</a>
  </div>
</section>

<!-- WHY -->
<section class="why-section">
  <div class="container">
    <span class="section-label">Neden Le Monde?</span>
    <h2 class="section-title">Çalışanlarımıza Değer Veririz</h2>
    <p class="section-desc">Sadece bir iş değil, bir kariyer yolculuğu sunuyoruz.</p>
    <div class="why-grid">
      <div class="why-card animate-in">
        <div class="why-icon">🚀</div>
        <h3>Hızlı Kariyer Gelişimi</h3>
        <p>Şube açılışları ve büyüyen ekiple birlikte liderlik fırsatları yaratılıyor. Performansın ödüllendirilir.</p>
      </div>
      <div class="why-card animate-in">
        <div class="why-icon">🎓</div>
        <h3>Sürekli Eğitim</h3>
        <p>İşe başlamadan önce tam kapsamlı oryantasyon, devam eden ürün ve servis eğitimleri.</p>
      </div>
      <div class="why-card animate-in">
        <div class="why-icon">🤝</div>
        <h3>Güçlü Ekip Kültürü</h3>
        <p>Hiyerarşi değil, ekip anlayışı. Fikriniz dinlenir, katkınız görünür olur.</p>
      </div>
      <div class="why-card animate-in">
        <div class="why-icon">💰</div>
        <h3>Rekabetçi Ücret</h3>
        <p>Sektör ortalamasının üzerinde maaş, prim sistemi ve çalışan yemek-içecek avantajları.</p>
      </div>
    </div>
  </div>
</section>

<!-- PROCESS -->
<section class="process-section">
  <div class="container">
    <span class="section-label">Başvuru Süreci</span>
    <h2 class="section-title">4 Adımda Aramıza Katılın</h2>
    <p class="section-desc">Başvuru sürecimiz hızlı ve şeffaftır. Her adımda sizinle iletişim halinde olacağız.</p>
    <div class="process-steps">
      <div class="step animate-in">
        <div class="step-number">1</div>
        <h3>Online Başvuru</h3>
        <p>Aşağıdaki formu doldurun ve CV'nizi yükleyin.</p>
      </div>
      <div class="step animate-in">
        <div class="step-number">2</div>
        <h3>Ön Değerlendirme</h3>
        <p>İK ekibimiz başvurunuzu 3 iş günü içinde inceler.</p>
      </div>
      <div class="step animate-in">
        <div class="step-number">3</div>
        <h3>Mülakat</h3>
        <p>Uygun adaylarla yüz yüze veya online görüşme yapılır.</p>
      </div>
      <div class="step animate-in">
        <div class="step-number">4</div>
        <h3>Hoş Geldiniz!</h3>
        <p>Seçilen adaylar oryantasyon eğitimi ile işe başlar.</p>
      </div>
    </div>
  </div>
</section>

<!-- FORM -->
<section class="form-section" id="basvuru">
  <div class="container">
    <div style="text-align:center;margin-bottom:40px;">
      <span class="section-label">Başvuru Formu</span>
      <h2 class="section-title">Kariyer Yolculuğunuza Başlayın</h2>
    </div>
    <div class="form-wrapper">
      <form id="applicationForm" class="form-grid" method="post" action="insan-kaynaklari.php#basvuru" enctype="multipart/form-data"><?= csrf_field() ?>
        <div class="form-group"><label>Ad</label><input type="text" name="fname" placeholder="Adınız" required></div>
        <div class="form-group"><label>Soyad</label><input type="text" name="lname" placeholder="Soyadınız" required></div>
        <div class="form-group"><label>E-posta</label><input type="email" name="email" placeholder="ornek@email.com" required></div>
        <div class="form-group"><label>Telefon</label><input type="tel" name="phone" placeholder="+90 5XX XXX XX XX" required></div>
        <div class="form-group">
          <label>Başvurulan Pozisyon</label>
          <select name="position" required>
            <option value="" disabled selected>Pozisyon seçin</option>
            <option>Şube Müdürü</option><option>Kasiyer</option><option>Mutfak Ekip Üyesi</option>
            <option>Kurye / Paketçi</option><option>Sosyal Medya Uzmanı</option><option>Stajyer</option><option>Diğer</option>
          </select>
        </div>
        <div class="form-group">
          <label>Tercih Edilen Şube</label>
          <select name="branch">
            <option value="" disabled selected>Şube seçin (opsiyonel)</option>
            <?php foreach ($branches_list as $bt): ?>
              <option value="<?= e($bt) ?>"><?= e($bt) ?></option>
            <?php endforeach; ?>
            <option value="Fark Etmez">Fark Etmez</option>
          </select>
        </div>
        <div class="form-group full">
          <label>CV / Özgeçmiş Yükle</label>
          <div class="file-upload" onclick="document.getElementById('cvFile').click()">
            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <p><span>Dosya seçmek için tıklayın</span> veya sürükleyip bırakın</p>
            <small>PDF, DOC, DOCX — Maks. 5MB</small>
            <input type="file" id="cvFile" name="cv" accept=".pdf,.doc,.docx">
            <div class="file-name" id="fileName"></div>
          </div>
        </div>
        <div class="form-group full">
          <label>Kendinizi Kısaca Tanıtın</label>
          <textarea name="msg" placeholder="Deneyimleriniz, motivasyonunuz ve neden Le Monde Du Tacos'ta çalışmak istediğiniz hakkında birkaç cümle yazın..."></textarea>
        </div>
        <div class="form-submit"><button type="submit" class="btn-submit">Başvurumu Gönder</button></div>
      </form>
      <div class="form-success" id="formSuccess">
        <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <h3>Başvurunuz Alındı!</h3>
        <p>En kısa sürede İK ekibimiz sizinle iletişime geçecektir. Teşekkür ederiz.</p>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER (index.html'den aynen) -->
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
.footer > .social-nav,.footer > .footer-meta{flex:0 0 auto;display:flex;align-items:center}.footer > .footer-legal{flex:1 1 auto;display:flex;align-items:center;min-width:0}
.footer > .social-nav{justify-content:flex-start}
.footer > .footer-legal{justify-content:center;align-items:center;gap:10px;font-size:12px;white-space:nowrap;flex-wrap:nowrap;overflow:hidden}
.footer > .footer-meta{justify-content:flex-end;flex-direction:column;align-items:flex-end;gap:2px;text-align:right;white-space:nowrap}
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
  const btn=document.getElementById("hamburger");
  const nav=document.getElementById("nav");
  btn?.addEventListener("click",()=>nav.classList.toggle("open"));
  document.addEventListener("click",(e)=>{
    if(!nav.classList.contains("open"))return;
    if(!nav.contains(e.target)&&!btn.contains(e.target))nav.classList.remove("open");
  });
  document.getElementById('cvFile').addEventListener('change',function(){
    document.getElementById('fileName').textContent=this.files[0]?this.files[0].name:'';
  });
  // Form gerçek submit oluyor
  if (window.location.search.includes('ok=1')) {
    const form = document.getElementById('applicationForm');
    const success = document.getElementById('formSuccess');
    if (form && success) {
      form.style.display = 'none';
      success.classList.add('show');
    }
    history.replaceState(null, '', window.location.pathname + '#basvuru');
  }
  const observer=new IntersectionObserver((entries)=>{
    entries.forEach((entry,i)=>{
      if(entry.isIntersecting){setTimeout(()=>entry.target.classList.add('visible'),i*80);observer.unobserve(entry.target);}
    });
  },{threshold:.15});
  document.querySelectorAll('.animate-in').forEach(el=>observer.observe(el));
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
