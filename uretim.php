<?php require_once __DIR__ . '/includes/functions.php'; ?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Üretim – Le Monde Du Tacos</title>
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
    .page-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 60% 40%,rgba(58,95,11,.4) 0%,transparent 60%);}
    .page-hero-inner{position:relative;z-index:2;}
    .page-hero h1{font-family:Georgia,serif;font-size:clamp(32px,5vw,56px);color:#fff;font-weight:700;margin-bottom:16px;font-style:italic;}
    .page-hero p{font-size:17px;color:rgba(255,255,255,.75);max-width:560px;margin:0 auto;line-height:1.7;}

    .section-eyebrow{font-family:'Retrim',sans-serif;font-size:11px;letter-spacing:3px;text-transform:uppercase;color:var(--brand2);margin-bottom:10px;}
    .section-title{font-family:Georgia,serif;font-size:clamp(24px,3vw,38px);font-weight:700;font-style:italic;color:var(--ink);margin-bottom:28px;}

    /* ANA GİRİŞ BLOĞU */
    .main-block{padding:80px 18px;background:#fff;}
    .main-inner{max-width:var(--max);margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:start;}
    .main-text h2{font-family:Georgia,serif;font-size:clamp(24px,3vw,36px);font-weight:700;font-style:italic;color:var(--ink);margin-bottom:20px;}
    .main-text p{font-size:15px;color:var(--muted);line-height:1.9;margin-bottom:0;}
    .cert-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .cert-card{background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:20px;text-align:center;transition:box-shadow .3s;}
    .cert-card:hover{box-shadow:0 6px 20px rgba(0,0,0,.08);}
    .cert-icon{font-size:28px;margin-bottom:10px;}
    .cert-card h4{font-size:13px;font-weight:700;color:var(--ink);margin-bottom:4px;}
    .cert-card p{font-size:11px;color:var(--muted);}
    .cert-badge{display:inline-block;margin-top:8px;padding:3px 10px;background:#e8f5e0;color:var(--brand);border-radius:6px;font-size:10px;font-weight:700;letter-spacing:.5px;}

    /* SÜREÇ */
    .process-section{padding:80px 18px;background:#f9fafb;border-top:1px solid #e5e7eb;}
    .process-inner{max-width:var(--max);margin:0 auto;}
    .process-flow{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:0;position:relative;}
    .process-flow::before{content:'';position:absolute;top:40px;left:8%;right:8%;height:2px;background:linear-gradient(90deg,var(--brand),var(--brand2));z-index:0;}
    .process-step{text-align:center;position:relative;z-index:1;padding:0 10px;opacity:0;transform:translateY(20px);transition:opacity .5s,transform .5s;}
    .process-step.visible{opacity:1;transform:translateY(0);}
    .step-circle{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--brand),#5a7a10);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:#fff;box-shadow:0 8px 24px rgba(58,95,11,.3);position:relative;}
    .step-n{position:absolute;top:-4px;right:-4px;width:22px;height:22px;border-radius:50%;background:var(--brand2);color:#fff;font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center;}
    .process-step h3{font-size:14px;font-weight:700;margin-bottom:6px;}
    .process-step p{font-size:12px;color:var(--muted);line-height:1.5;}

    /* KALİTE */
    .quality-section{padding:70px 18px;background:#fff;border-top:1px solid #e5e7eb;}
    .quality-inner{max-width:var(--max);margin:0 auto;}
    .quality-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px;margin-top:40px;}
    .quality-card{background:#f9fafb;border-radius:16px;padding:30px 24px;border:1px solid #e5e7eb;transition:transform .3s,box-shadow .3s;}
    .quality-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.09);}
    .quality-icon{width:50px;height:50px;border-radius:12px;background:linear-gradient(135deg,var(--brand),#5a7a10);display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;margin-bottom:18px;}
    .quality-card h3{font-size:15px;font-weight:700;margin-bottom:8px;}
    .quality-card p{font-size:13px;color:var(--muted);line-height:1.6;}
    .q-badge{display:inline-block;margin-top:12px;padding:4px 10px;background:#e8f5e0;color:var(--brand);border-radius:6px;font-size:10px;font-weight:700;}

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
      .main-inner{grid-template-columns:1fr;}
      .process-flow::before{display:none;}
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

<nav class="subnav">
  <div class="subnav-inner">
    <a href="/kurumsal.php"><i class="fa-solid fa-building"></i> Kurumsal</a>
    <a href="/tarihce.php"><i class="fa-solid fa-clock-rotate-left"></i> Tarihçe</a>
    <a href="/medya.php"><i class="fa-solid fa-photo-film"></i> Medya</a>
    <a class="active" href="/uretim.php"><i class="fa-solid fa-industry"></i> Üretim</a>
    <a href="/insan-kaynaklari.php"><i class="fa-solid fa-users"></i> İnsan Kaynakları</a>
  </div>
</nav>

<section class="page-hero">
  <div class="page-hero-inner">
    <h1>Üretim ve Lojistik</h1>
    <p>Özel reçetelerimiz, tescilli ürünlerimiz ve kesintisiz tedarik zinciri altyapımız.</p>
  </div>
</section>

<!-- ANA BLOK -->
<section class="main-block">
  <div class="main-inner">
    <div class="main-text">
      <div class="section-eyebrow">Üretim & Lojistik</div>
      <h2>Özgün Lezzet, Güvenilir Zincir</h2>
      <p>Ürünlerimizdeki soslar, tavuk marinasyonları ve bir çok ürün tamamen bize özel ve kendi üretimimizdir; bu da markamıza özgün bir tat ve kalite kazandırmaktadır. Üretim süreçlerimizde yüksek hijyen ve kalite standartlarına bağlı kalınırken, sorunsuz ve güvenilir lojistik altyapımız sayesinde tedarik zinciri kesintisiz çalışmakta ve malzemeler franchise şubelerimize zamanında ulaşmaktadır. Böylece hem üretimde hem de dağıtımda müşteri memnuniyetini en üst seviyede tutmaktayız. Bu detaylı ve güçlü yapı, markamızın lezzet ve hizmette sürekliliğini sağlar.</p>
    </div>
    <div class="cert-grid">
      <div class="cert-card">
        <div class="cert-icon">🏅</div>
        <h4>Türk Patent Tescil Belgesi</h4>
        <p>LMD Tacos markası ve O'Bun ürünü Türk Patent Kurumu tarafından tescil edilmiştir.</p>
        <span class="cert-badge">✓ Tescilli</span>
      </div>
      <div class="cert-card">
        <div class="cert-icon">📜</div>
        <h4>Helal Gıda Sertifikası</h4>
        <p>Üretim tesisimiz ve ürünlerimiz helal gıda sertifikasına sahiptir.</p>
        <span class="cert-badge">✓ Sertifikalı</span>
      </div>
      <div class="cert-card">
        <div class="cert-icon">🔬</div>
        <h4>Üretim Tescili</h4>
        <p>Özel soslar ve marinasyonlar tescilli reçetelerle üretilmektedir.</p>
        <span class="cert-badge">✓ Tescilli Reçete</span>
      </div>
      <div class="cert-card">
        <div class="cert-icon">🚛</div>
        <h4>Lojistik Altyapı</h4>
        <p>Kesintisiz soğuk zincir ve zamanında teslimat sistemi ile tüm şubelere hizmet.</p>
        <span class="cert-badge">✓ Aktif</span>
      </div>
    </div>
  </div>
</section>

<!-- SÜREÇ AKIŞI -->
<section class="process-section">
  <div class="process-inner">
    <div class="section-eyebrow">Üretim Süreci</div>
    <div class="section-title">Tabağınıza Giden Yol</div>
    <div class="process-flow">
      <div class="process-step">
        <div class="step-circle">🌾<div class="step-n">1</div></div>
        <h3>Hammadde Seçimi</h3>
        <p>Onaylı tedarikçilerden taze ve yerel malzeme temini</p>
      </div>
      <div class="process-step">
        <div class="step-circle">🧪<div class="step-n">2</div></div>
        <h3>Merkezi Üretim</h3>
        <p>Tescilli reçetelerle sos, marinat ve ekmek üretimi</p>
      </div>
      <div class="process-step">
        <div class="step-circle">🧫<div class="step-n">3</div></div>
        <h3>Kalite Kontrolü</h3>
        <p>Hijyen testleri, sıcaklık takibi, mikrobiyolojik analiz</p>
      </div>
      <div class="process-step">
        <div class="step-circle">❄️<div class="step-n">4</div></div>
        <h3>Soğuk Zincir</h3>
        <p>Kesintisiz soğuk zincir ile şubelere zamanında teslimat</p>
      </div>
      <div class="process-step">
        <div class="step-circle">🏪<div class="step-n">5</div></div>
        <h3>Şube & Servis</h3>
        <p>Son pişirme ve montaj şubede, müşteriye taze servis</p>
      </div>
    </div>
  </div>
</section>

<!-- KALİTE -->
<section class="quality-section">
  <div class="quality-inner">
    <div class="section-eyebrow">Kalite Güvencesi</div>
    <div class="section-title">Standartlarımız</div>
    <div class="quality-grid">
      <div class="quality-card">
        <div class="quality-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <h3>Özel Üretim</h3>
        <p>Soslar ve marinasyonlar tamamen bize özel ve kendi üretimimizdir. Bu durum markamıza özgün tat ve kalite kazandırır.</p>
        <span class="q-badge">✓ Tescilli</span>
      </div>
      <div class="quality-card">
        <div class="quality-icon"><i class="fa-solid fa-temperature-low"></i></div>
        <h3>Soğuk Zincir</h3>
        <p>Üretimden teslimata kadar kesintisiz soğuk zincir. Tüm malzemeler franchise şubelerimize zamanında ulaşır.</p>
        <span class="q-badge">✓ Kesintisiz</span>
      </div>
      <div class="quality-card">
        <div class="quality-icon"><i class="fa-solid fa-leaf"></i></div>
        <h3>Hijyen Standartları</h3>
        <p>Üretim süreçlerimizde yüksek hijyen ve kalite standartlarına bağlı kalınarak müşteri memnuniyeti en üst seviyede tutulmaktadır.</p>
        <span class="q-badge">✓ Belgelenmiş</span>
      </div>
      <div class="quality-card">
        <div class="quality-icon"><i class="fa-solid fa-handshake"></i></div>
        <h3>Franchise Desteği</h3>
        <p>Güçlü lojistik altyapı sayesinde franchise şubelerimiz her zaman doğru ürünü doğru zamanda müşterisine sunar.</p>
        <span class="q-badge">✓ Aktif Destek</span>
      </div>
      <div class="quality-card">
        <div class="quality-icon"><i class="fa-solid fa-award"></i></div>
        <h3>Helal Sertifikası</h3>
        <p>Tüm ürünlerimiz helal gıda sertifikası kapsamında üretilmektedir. Müşterilerimiz gönül rahatlığıyla tüketebilir.</p>
        <span class="q-badge">✓ Sertifikalı</span>
      </div>
      <div class="quality-card">
        <div class="quality-icon"><i class="fa-solid fa-repeat"></i></div>
        <h3>Süreklilik</h3>
        <p>Bu detaylı ve güçlü yapı, markamızın lezzet ve hizmette sürekliliğini sağlar. Her şubede, her gün aynı kalite.</p>
        <span class="q-badge">✓ Tutarlı</span>
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
    entries.forEach((e,i)=>{if(e.isIntersecting){setTimeout(()=>e.target.classList.add("visible"),i*150);obs.unobserve(e.target);}});
  },{threshold:.15});
  document.querySelectorAll(".process-step").forEach(el=>obs.observe(el));
</script>
</body>
</html>
