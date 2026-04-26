<?php require_once __DIR__ . '/includes/functions.php'; ?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Tarihçe – Le Monde Du Tacos</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="/static/fonts/retrim/stylesheet.css">
  <style>
    :root{--brand:#3A5F0B;--brand2:#b24545;--ink:#1f2937;--muted:#6b7280;--bg:#ffffff;--shadow:0 10px 30px rgba(0,0,0,.18);--max:1180px;}
    *{box-sizing:border-box;margin:0;padding:0;}
    html,body{max-width:100%;overflow-x:hidden;}
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:var(--ink);background:var(--bg);}
    a{color:inherit;text-decoration:none;}
    button{font:inherit;}

    .topbar{position:sticky;top:0;z-index:999;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.08);}
    .topbar-inner{max-width:var(--max);margin:0 auto;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;gap:16px;}
    .brand{position:relative;display:flex;align-items:center;gap:2px;min-width:220px;}
    .logo-wrapper{width:50px;height:50px;margin-left:80px;position:relative;}
    .brand-logo{position:absolute;height:170px;width:auto;left:50%;transform:translateX(-70%);top:-40px;pointer-events:none;}
    .brand-text{margin-top:20px;display:flex;flex-direction:column;justify-content:center;}
    .brand .logo{font-family:Georgia,serif;font-size:28px;line-height:1;color:#3A5F0B;font-weight:700;}
    .nav{display:flex;align-items:center;gap:12px;}
    .nav a{padding:9px 12px;border-radius:6px;font-family:'Retrim',sans-serif;font-weight:400;font-size:12px;letter-spacing:.6px;text-transform:uppercase;color:#1f2937;white-space:nowrap;transition:all .25s ease;}
    .nav a.active{background:var(--brand);color:#fff;}
    .nav a:not(.active):hover{background:var(--brand);color:#fff;}
    .hamburger{display:none;width:44px;height:40px;border:1px solid rgba(0,0,0,.12);border-radius:10px;background:#fff;align-items:center;justify-content:center;cursor:pointer;z-index:9999;}
    .hamburger span{display:block;width:18px;height:2px;background:#111827;position:relative;}
    .hamburger span::before,.hamburger span::after{content:"";position:absolute;left:0;width:18px;height:2px;background:#111827;}
    .hamburger span::before{top:-6px;}
    .hamburger span::after{top:6px;}

    .subnav{background:#f9fafb;border-bottom:2px solid #e5e7eb;display:flex;justify-content:center;overflow-x:auto;}
    .subnav-inner{padding:0 18px;display:flex;gap:0;}
    .subnav-inner::-webkit-scrollbar{height:3px;}
    .subnav-inner::-webkit-scrollbar-thumb{background:var(--brand);border-radius:2px;}
    .subnav a{display:flex;align-items:center;gap:8px;padding:14px 20px;font-family:'Retrim',sans-serif;font-size:12px;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);white-space:nowrap;border-bottom:3px solid transparent;transition:all .2s;}
    .subnav a:hover{color:var(--brand);border-bottom-color:var(--brand);}
    .subnav a.active{color:var(--brand);border-bottom-color:var(--brand);font-weight:700;}
    .subnav a i{font-size:13px;}

    .page-hero{background:linear-gradient(135deg,#1a2e0a 0%,#2d4f11 50%,#1a2e0a 100%);padding:80px 18px;text-align:center;position:relative;overflow:hidden;}
    .page-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% 50%,rgba(178,69,69,.2) 0%,transparent 60%);}
    .page-hero-inner{position:relative;z-index:2;}
    .page-hero h1{font-family:Georgia,serif;font-size:clamp(32px,5vw,56px);color:#fff;font-weight:700;margin-bottom:16px;font-style:italic;}
    .page-hero p{font-size:17px;color:rgba(255,255,255,.75);max-width:560px;margin:0 auto;line-height:1.7;}

    /* GİRİŞ BLOĞU */
    .intro-section{background:#fff;padding:70px 18px;}
    .intro-inner{max-width:860px;margin:0 auto;text-align:center;}
    .section-eyebrow{font-family:'Retrim',sans-serif;font-size:11px;letter-spacing:3px;text-transform:uppercase;color:var(--brand2);margin-bottom:10px;}
    .section-title{font-family:Georgia,serif;font-size:clamp(24px,3vw,38px);font-weight:700;font-style:italic;color:var(--ink);margin-bottom:28px;}
    .intro-text{font-size:16px;color:var(--muted);line-height:1.9;max-width:720px;margin:0 auto;}
    .intro-text p{margin-bottom:18px;}
    .intro-text p:last-child{margin-bottom:0;}

    /* TİMLİNE */
    .timeline-section{padding:80px 18px;background:#f9fafb;border-top:1px solid #e5e7eb;}
    .timeline-inner{max-width:780px;margin:0 auto;}
    .timeline{position:relative;padding-left:40px;}
    .timeline::before{content:'';position:absolute;left:12px;top:0;bottom:0;width:2px;background:linear-gradient(to bottom,var(--brand),var(--brand2));}
    .tl-item{position:relative;margin-bottom:44px;opacity:0;transform:translateX(-20px);transition:opacity .5s,transform .5s;}
    .tl-item.visible{opacity:1;transform:translateX(0);}
    .tl-dot{position:absolute;left:-34px;top:4px;width:16px;height:16px;border-radius:50%;background:var(--brand);border:3px solid #fff;box-shadow:0 0 0 2px var(--brand);}
    .tl-year{font-family:'Retrim',sans-serif;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--brand2);font-weight:700;margin-bottom:6px;}
    .tl-item h3{font-family:Georgia,serif;font-size:20px;font-weight:700;font-style:italic;margin-bottom:8px;}
    .tl-item p{font-size:14px;color:var(--muted);line-height:1.7;}

    /* STATS */
    .stats-section{background:#fff;padding:70px 18px;border-top:1px solid #e5e7eb;}
    .stats-inner{max-width:var(--max);margin:0 auto;}
    .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:32px;margin-top:40px;}
    .stat-card{text-align:center;}
    .stat-num{font-family:Georgia,serif;font-size:48px;font-weight:700;color:var(--brand);line-height:1;}
    .stat-label{font-family:'Retrim',sans-serif;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-top:8px;}

    .footer{max-width:var(--max);margin:0 auto;padding:18px 18px 26px;display:flex;align-items:center;justify-content:space-between;gap:14px;color:var(--muted);font-size:12px;}
    .social-nav{padding:0;margin:0;list-style:none;display:flex;align-items:center;gap:10px;}
    .social-nav li{display:inline-block;}
    .social-nav a{display:inline-block;width:36px;height:36px;line-height:36px;text-align:center;color:#fff;text-decoration:none;background:#000;border-radius:8px;transition:.35s ease;overflow:hidden;font-size:18px;}
    .model-2 a{font-size:20px;border-radius:10px;}
    .model-2 a:hover{background:#fff;text-shadow:0px 0px #d5d5d5,1px 1px #d5d5d5,2px 2px #d5d5d5,3px 3px #d5d5d5;}
    .model-2 .facebook{background:#3B579D;}.model-2 .facebook:hover{color:#3B579D;}
    .model-2 .instagram{background:#E1306C;}.model-2 .instagram:hover{color:#E1306C;}
    .model-2 .twitter{background:#111827;}.model-2 .twitter:hover{color:#111827;}
    .model-2 .youtube{background:#FF0000;}.model-2 .youtube:hover{color:#FF0000;}

    @media(max-width:940px){
      .logo-wrapper{margin-left:20px;}.brand-logo{height:90px;top:-16px;}.brand .logo{font-size:20px;}.brand-text{margin-top:12px;}.brand{min-width:auto;}
      .hamburger{display:flex;}
      .nav{position:absolute;top:58px;right:12px;left:12px;background:#fff;border:1px solid rgba(0,0,0,.10);border-radius:14px;box-shadow:var(--shadow);padding:10px;display:none;flex-direction:column;align-items:stretch;gap:6px;z-index:9998;}
      .nav.open{display:flex;}.nav a{padding:12px;}
      .footer{flex-direction:column;text-align:center;gap:8px;padding:14px 10px;}
    }
    @media(max-width:860px){
      .logo-wrapper{width:40px!important;height:40px!important;margin-left:30px!important;position:relative!important;flex:0 0 40px!important;}
      .brand-logo{position:absolute!important;height:90px!important;width:auto!important;left:50%!important;top:-16px!important;transform:translateX(-70%)!important;pointer-events:none!important;}
    }
  </style>
</head>
<body>

<header class="topbar">
  <div class="topbar-inner">
    <a class="brand" href="index.php">
      <div class="logo-wrapper">
        <img class="brand-logo" src="/static/img/logos/LMD LOGOArtboard1.png" alt="TACOS Logo">
      </div>
      <div class="brand-text"><div class="logo" style="font-style:italic">Le Monde Du Tacos</div></div>
    </a>
    <button class="hamburger" id="hamburger" aria-label="Menüyü aç/kapat"><span></span></button>
    <nav class="nav" id="nav">
      <a href="index.php">ANASAYFA</a>
      <a href="kurumsal.php">KURUMSAL</a>
      <a href="subeler.php">ŞUBELER</a>
      <a href="kampanyalar.php">KAMPANYALAR</a>
      <a href="franchise.php">FRANCHISE</a>
      <a href="iletisim.php">İLETİŞİM</a>
    </nav>
  </div>
</header>

<nav class="subnav">
  <div class="subnav-inner">
    <a href="kurumsal.php"><i class="fa-solid fa-building"></i> Kurumsal</a>
    <a class="active" href="tarihce.php"><i class="fa-solid fa-clock-rotate-left"></i> Tarihçe</a>
    <a href="medya.php"><i class="fa-solid fa-photo-film"></i> Medya</a>
    <a href="uretim.php"><i class="fa-solid fa-industry"></i> Üretim</a>
    <a href="insan-kaynaklari.php"><i class="fa-solid fa-users"></i> İnsan Kaynakları</a>
  </div>
</nav>

<section class="page-hero">
  <div class="page-hero-inner">
    <h1>Tarihçemiz</h1>
    <p>Fransa'da doğan bir lezzet, Türkiye'de kökleşen bir marka hikayesi.</p>
  </div>
</section>

<!-- AÇIKLAMA METNİ -->
<section class="intro-section">
  <div class="intro-inner">
    <div class="section-eyebrow">Kökenimiz</div>
    <div class="section-title">French Tacos Nedir?</div>
    <div class="intro-text">
      <p>Fransa'da özellikle Lyon çıkışlı olan ve 2007'den itibaren Avrupa'da hızla yayılan French Tacos, klasik Meksika Taco'sundan tamamen farklı bir üründür. İçinde et, patates, peynir sosu ve özel baharatların bulunduğu; mühürlenerek servis edilen bu ürün, Avrupa'da genç kuşaklar arasında adeta bir sokak kültürüne dönüşmüştü. Türkiye'de ise bu alanda belirgin bir zincir ve güçlü bir marka yoktu.</p>
      <p>2019 yılında, kurucularımız, Fransa'da büyük bir fenomen haline gelen ve kısa sürede hızla yayılan bu farklı ürünü Türkiye'nin zengin gastronomi sahnesiyle buluşturma hayalini hayata geçirdiler.</p>
    </div>
  </div>
</section>

<!-- TİMLİNE -->
<section class="timeline-section">
  <div class="timeline-inner">
    <div class="section-eyebrow">Geçmişten Bugüne</div>
    <div class="section-title">Büyüme Hikayemiz</div>
    <div class="timeline">

      <div class="tl-item">
        <div class="tl-dot"></div>
        <div class="tl-year">2007 – Fransa / Lyon</div>
        <h3>French Tacos'un Doğuşu</h3>
        <p>Lyon çıkışlı French Tacos, Avrupa'da hızla yayılmaya başlar. İçinde et, patates, peynir sosu ve özel baharatlar barındıran; mühürlenerek servis edilen bu ürün, genç kuşaklar arasında sokak kültürünün simgesi haline gelir.</p>
      </div>

      <div class="tl-item">
        <div class="tl-dot" style="background:var(--brand2);box-shadow:0 0 0 2px var(--brand2);"></div>
        <div class="tl-year">2019 – İstanbul</div>
        <h3>Türkiye'ye Getirme Kararı</h3>
        <p>Kurucularımız, Türkiye'de bu alanda güçlü bir markanın bulunmadığını fark ederek orijinal French Tacos'u Türkiye'nin zengin gastronomi sahnesiyle buluşturma hayalini hayata geçirmeye karar verdi. Reçete geliştirme, tedarikçi anlaşmaları ve marka kimliği çalışmaları başladı.</p>
      </div>

      <div class="tl-item">
        <div class="tl-dot"></div>
        <div class="tl-year">2022 – İlk Şube</div>
        <h3>Yenibosna'da Açılış</h3>
        <p>İstanbul Yenibosna'da ilk Le Monde Du Tacos şubesi kapılarını açtı. Açılış haftasında rekor sipariş sayısına ulaşıldı; müşteri talebi tüm beklentilerin üzerine çıktı.</p>
      </div>

      <div class="tl-item">
        <div class="tl-dot" style="background:var(--brand2);box-shadow:0 0 0 2px var(--brand2);"></div>
        <div class="tl-year">2023 – Büyüme</div>
        <h3>4 Şubeye Ulaşıldı</h3>
        <p>Bahçelievler, Esenyurt ve Eskişehir Tepebaşı şubeleri hizmete girdi. Online sipariş platformlarına entegrasyon tamamlandı; haftalık sipariş hacmi 3 katına çıktı.</p>
      </div>

      <div class="tl-item">
        <div class="tl-dot"></div>
        <div class="tl-year">2024 – Kapadokya</div>
        <h3>Anadolu'ya Açılım</h3>
        <p>Ürgüp-Nevşehir ve Nevşehir Merkez şubeleri açıldı. Franchise modeli hayata geçirildi. Türk Patent sertifikalı özel sos ve marinasyon üretimi merkezi sisteme entegre edildi.</p>
      </div>

      <div class="tl-item">
        <div class="tl-dot" style="background:var(--brand2);box-shadow:0 0 0 2px var(--brand2);"></div>
        <div class="tl-year">2025–2026 – Vizyon</div>
        <h3>Türkiye'nin Her Köşesine</h3>
        <p>20+ şube hedefiyle franchise ağı genişleme planı aktif. Orijinal Fransız Tacos deneyiminin Türkiye'deki tek referans markası olma yolunda kararlılıkla ilerliyoruz.</p>
      </div>

    </div>
  </div>
</section>

<section class="stats-section">
  <div class="stats-inner">
    <div class="section-eyebrow">Rakamlarla Biz</div>
    <div class="section-title">Büyümenin Kanıtı</div>
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-num">6+</div>
        <div class="stat-label">Aktif Şube</div>
      </div>
      <div class="stat-card">
        <div class="stat-num">2019</div>
        <div class="stat-label">Kuruluş Yılı</div>
      </div>
      <div class="stat-card">
        <div class="stat-num">20+</div>
        <div class="stat-label">Şube Hedefi</div>
      </div>
      <div class="stat-card">
        <div class="stat-num">150+</div>
        <div class="stat-label">Çalışan</div>
      </div>
    </div>
  </div>
</section>

<footer class="footer">
  <ul class="social-nav model-2" aria-label="Sosyal medya">
    <li><a class="facebook" href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a></li>
    <li><a class="instagram" href="https://www.instagram.com/lemondedutacos__?igsh=MWIzMDRzaWw0azhkbA%3D%3D" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a></li>
    <li><a class="twitter" href="#" aria-label="Twitter/X"><i class="fa-brands fa-x-twitter"></i></a></li>
    <li><a class="youtube" href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a></li>
  </ul>
  <div style="font-weight:bold;font-family:'Georgia',serif;">
    Copyright © 2026 <span style="font-style:italic;text-decoration:underline;">Tüm Hakları Saklıdır</span>
  </div>
</footer>

<script>
  const btn=document.getElementById("hamburger");
  const nav=document.getElementById("nav");
  btn?.addEventListener("click",()=>nav.classList.toggle("open"));
  document.addEventListener("click",(e)=>{
    if(!nav.classList.contains("open"))return;
    if(!nav.contains(e.target)&&!btn.contains(e.target))nav.classList.remove("open");
  });
  const obs=new IntersectionObserver((entries)=>{
    entries.forEach((e,i)=>{if(e.isIntersecting){setTimeout(()=>e.target.classList.add("visible"),i*120);obs.unobserve(e.target);}});
  },{threshold:.15});
  document.querySelectorAll(".tl-item").forEach(el=>obs.observe(el));
</script>
</body>
</html>
