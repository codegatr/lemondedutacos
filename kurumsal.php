<?php require_once __DIR__ . '/includes/functions.php'; ?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Kurumsal – Le Monde Du Tacos</title>
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

    /* HERO */
    .page-hero{background:linear-gradient(135deg,#0a1a04 0%,#1a2e0a 60%,#0a1a04 100%);padding:90px 18px;text-align:center;position:relative;overflow:hidden;}
    .page-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% 50%,rgba(245,197,24,.08) 0%,transparent 60%),radial-gradient(circle at 70% 50%,rgba(178,69,69,.12) 0%,transparent 60%);}
    .page-hero-inner{position:relative;z-index:2;}
    .hero-tagline{display:inline-block;border:1px solid rgba(245,197,24,.5);color:#f5c518;font-family:'Retrim',sans-serif;font-size:11px;letter-spacing:3px;text-transform:uppercase;padding:8px 24px;border-radius:30px;margin-bottom:24px;}
    .page-hero h1{font-family:Georgia,serif;font-size:clamp(32px,5vw,58px);color:#fff;font-weight:700;margin-bottom:16px;font-style:italic;}
    .page-hero p{font-size:16px;color:rgba(255,255,255,.72);max-width:640px;margin:0 auto;line-height:1.8;}

    /* SLOGAN */
    .slogan-strip{background:#3A5F0B;padding:24px 18px;text-align:center;}
    .slogan-strip p{font-family:Georgia,serif;font-size:clamp(13px,1.8vw,17px);color:#fff;font-style:italic;max-width:800px;margin:0 auto;line-height:1.6;}
    .slogan-strip strong{color:#f5c518;}

    /* CARDS */
    .cards-section{padding:80px 18px;background:#fff;}
    .cards-inner{max-width:var(--max);margin:0 auto;}
    .section-eyebrow{font-family:'Retrim',sans-serif;font-size:11px;letter-spacing:3px;text-transform:uppercase;color:var(--brand2);margin-bottom:10px;}
    .section-title{font-family:Georgia,serif;font-size:clamp(24px,3vw,38px);font-weight:700;font-style:italic;color:var(--ink);margin-bottom:40px;}
    .corp-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:28px;}
    .corp-card{border-radius:18px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,.10);transition:transform .3s,box-shadow .3s;background:#fff;border:1px solid #e5e7eb;text-decoration:none;color:inherit;display:block;}
    .corp-card:hover{transform:translateY(-6px);box-shadow:0 18px 40px rgba(0,0,0,.16);}
    .corp-card-img{height:180px;display:flex;align-items:center;justify-content:center;font-size:52px;}
    .corp-card-img.green{background:linear-gradient(135deg,#1a2e0a,#3A5F0B);}
    .corp-card-img.red{background:linear-gradient(135deg,#6b1414,#b24545);}
    .corp-card-img.olive{background:linear-gradient(135deg,#2d3a0a,#5a7a10);}
    .corp-card-img.dark{background:linear-gradient(135deg,#111827,#374151);}
    .corp-card-body{padding:24px;}
    .corp-card-body h3{font-family:Georgia,serif;font-size:20px;font-weight:700;margin-bottom:8px;font-style:italic;}
    .corp-card-body p{font-size:14px;color:var(--muted);line-height:1.6;margin-bottom:16px;}
    .corp-card-link{font-family:'Retrim',sans-serif;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:var(--brand);font-weight:700;display:inline-flex;align-items:center;gap:6px;}
    .corp-card-link i{font-size:10px;transition:transform .2s;}
    .corp-card:hover .corp-card-link i{transform:translateX(4px);}

    /* MİSYON / VİZYON / HEDEF */
    .mvh-section{background:#f9fafb;padding:80px 18px;border-top:1px solid #e5e7eb;}
    .mvh-inner{max-width:var(--max);margin:0 auto;}
    .mvh-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:28px;margin-top:40px;}
    .mvh-card{background:#fff;border-radius:18px;padding:36px 28px;border:1px solid #e5e7eb;border-top:4px solid var(--brand);box-shadow:0 4px 18px rgba(0,0,0,.06);transition:transform .3s;}
    .mvh-card:hover{transform:translateY(-4px);}
    .mvh-card.red-top{border-top-color:var(--brand2);}
    .mvh-card.gold-top{border-top-color:#c49b0a;}
    .mvh-icon{font-size:34px;margin-bottom:16px;}
    .mvh-card h3{font-family:Georgia,serif;font-size:22px;font-weight:700;font-style:italic;color:var(--brand);margin-bottom:14px;}
    .mvh-card.red-top h3{color:var(--brand2);}
    .mvh-card.gold-top h3{color:#c49b0a;}
    .mvh-card p{font-size:14px;color:var(--muted);line-height:1.8;}

    /* 4D+1 */
    .formula-section{background:#1a2e0a;padding:80px 18px;}
    .formula-inner{max-width:var(--max);margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:start;}
    .formula-eyebrow{font-family:'Retrim',sans-serif;font-size:11px;letter-spacing:3px;text-transform:uppercase;color:#f5c518;margin-bottom:10px;}
    .formula-text h2{font-family:Georgia,serif;font-size:clamp(24px,3vw,38px);color:#fff;font-weight:700;font-style:italic;margin-bottom:16px;}
    .formula-text p{color:rgba(255,255,255,.65);font-size:15px;line-height:1.7;margin-bottom:28px;}
    .formula-badge{display:inline-block;background:#f5c518;color:#1a2e0a;font-family:'Retrim',sans-serif;font-size:22px;letter-spacing:2px;font-weight:900;padding:10px 28px;border-radius:12px;margin-bottom:28px;}
    .formula-list{list-style:none;padding:0;display:flex;flex-direction:column;gap:12px;}
    .formula-list li{display:flex;align-items:center;gap:14px;color:#fff;font-size:15px;}
    .num{width:32px;height:32px;border-radius:50%;background:#f5c518;color:#1a2e0a;font-weight:800;font-size:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .formula-plus{margin-top:16px;display:flex;align-items:center;gap:14px;color:#f5c518;font-weight:700;font-size:15px;}
    .formula-visual{background:rgba(255,255,255,.06);border-radius:20px;padding:36px;border:1px solid rgba(255,255,255,.1);}
    .formula-visual h3{font-family:Georgia,serif;font-size:18px;color:#f5c518;margin-bottom:22px;font-style:italic;}
    .fv-item{display:flex;align-items:flex-start;gap:12px;margin-bottom:18px;}
    .fv-dot{width:8px;height:8px;border-radius:50%;background:#f5c518;flex-shrink:0;margin-top:6px;}
    .fv-item p{color:rgba(255,255,255,.7);font-size:14px;line-height:1.6;}

    /* FOOTER */
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

    @media(max-width:940px){
      .logo-wrapper{margin-left:20px;}.brand-logo{height:90px;top:-16px;}.brand .logo{font-size:20px;}.brand-text{margin-top:12px;}.brand{min-width:auto;}
      .hamburger{display:flex;}
      .nav{position:absolute;top:58px;right:12px;left:12px;background:#fff;border:1px solid rgba(0,0,0,.10);border-radius:14px;box-shadow:var(--shadow);padding:10px;display:none;flex-direction:column;align-items:stretch;gap:6px;z-index:9998;}
      .nav.open{display:flex;}.nav a{padding:12px;}
      .formula-inner{grid-template-columns:1fr;}
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
      <a class="active" href="/kurumsal.php">KURUMSAL</a>
      <a href="/subeler.php">ŞUBELER</a>
      <a href="/kampanyalar.php">KAMPANYALAR</a>
      <a href="/franchise.php">FRANCHISE</a>
      <a href="/iletisim.php">İLETİŞİM</a>
    </nav>
  </div>
</header>

<nav class="subnav">
  <div class="subnav-inner">
    <a class="active" href="/kurumsal.php"><i class="fa-solid fa-building"></i> Kurumsal</a>
    <a href="/tarihce.php"><i class="fa-solid fa-clock-rotate-left"></i> Tarihçe</a>
    <a href="/medya.php"><i class="fa-solid fa-photo-film"></i> Medya</a>
    <a href="/uretim.php"><i class="fa-solid fa-industry"></i> Üretim</a>
    <a href="/insan-kaynaklari.php"><i class="fa-solid fa-users"></i> İnsan Kaynakları</a>
  </div>
</nav>

<section class="page-hero">
  <div class="page-hero-inner">
    <div class="hero-tagline">Taklidi Değil Ta Kendisi</div>
    <h1>LMD Tacos Kurumsal</h1>
    <p>Bir tabeladan, bir menüden, bir lokasyondan ibaret değildir.<br>Bir fikrin, bir inanışın altında toplanan ekibin yolculuğudur.</p>
  </div>
</section>

<div class="slogan-strip">
  <p>Orjinal Fransız Tacosun Tek Adresi &nbsp;·&nbsp; <strong>"Taklidi Değil Ta Kendisi"</strong></p>
</div>

<section class="cards-section">
  <div class="cards-inner">
    <div class="section-eyebrow">Kurumsal Sayfalar</div>
    <div class="section-title">Her Şeyi Burada Keşfedin</div>
    <div class="corp-grid">
      <a class="corp-card" href="/tarihce.php">
        <div class="corp-card-img green">🏛️</div>
        <div class="corp-card-body">
          <h3>Tarihçe</h3>
          <p>2007'den bu yana Avrupa'da yayılan French Tacos'u 2019'da Türkiye ile buluşturma hikayemiz.</p>
          <span class="corp-card-link">Keşfet <i class="fa-solid fa-arrow-right"></i></span>
        </div>
      </a>
      <a class="corp-card" href="/medya.php">
        <div class="corp-card-img red">📸</div>
        <div class="corp-card-body">
          <h3>Medya</h3>
          <p>Şube görselleri, ürün fotoğrafları ve kurumsal marka materyalleri.</p>
          <span class="corp-card-link">Keşfet <i class="fa-solid fa-arrow-right"></i></span>
        </div>
      </a>
      <a class="corp-card" href="/uretim.php">
        <div class="corp-card-img olive">🏭</div>
        <div class="corp-card-body">
          <h3>Üretim</h3>
          <p>Özel soslar, marinasyonlar ve Türk Patent belgeli ürünlerimizle lojistik altyapımız.</p>
          <span class="corp-card-link">Keşfet <i class="fa-solid fa-arrow-right"></i></span>
        </div>
      </a>
      <a class="corp-card" href="/insan-kaynaklari.php">
        <div class="corp-card-img dark">👥</div>
        <div class="corp-card-body">
          <h3>İnsan Kaynakları</h3>
          <p>Açık pozisyonlar, başvuru formu ve büyüyen ekibimize katılma fırsatları.</p>
          <span class="corp-card-link">Keşfet <i class="fa-solid fa-arrow-right"></i></span>
        </div>
      </a>
    </div>
  </div>
</section>

<section class="mvh-section">
  <div class="mvh-inner">
    <div class="section-eyebrow">Değerlerimiz</div>
    <div class="section-title">Hedefimiz, Misyonumuz, Vizyonumuz</div>
    <div class="mvh-grid">
      <div class="mvh-card gold-top">
        <div class="mvh-icon">🎯</div>
        <h3>Hedefimiz</h3>
        <p>Yenilikçi ve benzersiz Fransız Tacos lezzetimizi Türkiye'nin dört bir yanında ve uluslararası alanda erişilebilir kılmak; her şubemizde aynı kalite ve müşteri memnuniyetini sağlayarak markamızı hızlı servis restoran sektöründe lider konuma taşımaktır. Yatırımcı ve girişimcilerle büyüyen bir aile yapısı oluşturarak, sürdürülebilir kârlılık ve sektör standartlarının üzerinde marka değeri yaratmayı amaçlıyoruz.</p>
      </div>
      <div class="mvh-card">
        <div class="mvh-icon">🚀</div>
        <h3>Misyonumuz</h3>
        <p>Müşterilerimize misafir deneyimi yaşatmak, tacos denince akla ilk gelen marka olmak.</p>
      </div>
      <div class="mvh-card red-top">
        <div class="mvh-icon">🌍</div>
        <h3>Vizyonumuz</h3>
        <p>Türkiye'de ve yurt dışında orijinal Fransız Tacos deneyiminin lideri ve referans markası haline getirmek; inovasyonda öncü, müşteri memnuniyetinde örnek ve franchise ekosisteminde sürdürülebilir büyümeyle ilk akla gelen marka olmaktır.</p>
      </div>
    </div>
  </div>
</section>

<section class="formula-section">
  <div class="formula-inner">
    <div class="formula-text">
      <div class="formula-eyebrow">Sistemimiz</div>
      <h2>Neyi Benimsiyoruz?</h2>
      <p>Başarının arkasındaki yapı rastlantı değildir. LMD Tacos olarak her şubemizde uyguladığımız kanıtlanmış bir formül benimsemekteyiz.</p>
      <div class="formula-badge">4D + 1</div>
      <ul class="formula-list">
        <li><span class="num">1</span> Doğru Lokasyon</li>
        <li><span class="num">2</span> Doğru Kiralama</li>
        <li><span class="num">3</span> Doğru Ürün</li>
        <li><span class="num">4</span> Doğru Konsept</li>
        <li class="formula-plus"><span class="num" style="background:var(--brand2);">+1</span> Doğru Operasyon</li>
      </ul>
    </div>
    <div class="formula-visual">
      <h3>Bu formül ne sağlar?</h3>
      <div class="fv-item"><div class="fv-dot"></div><p>Yüksek müşteri trafiği olan noktalarda stratejik konumlanma</p></div>
      <div class="fv-item"><div class="fv-dot"></div><p>Maliyet optimizasyonu ile sürdürülebilir kârlılık</p></div>
      <div class="fv-item"><div class="fv-dot"></div><p>Tescilli ürün ve özgün reçetelerle rakipsiz lezzet standardı</p></div>
      <div class="fv-item"><div class="fv-dot"></div><p>Tutarlı marka kimliği ve müşteri deneyimi</p></div>
      <div class="fv-item"><div class="fv-dot"></div><p>Merkezi destek ve eğitimle güçlü operasyonel altyapı</p></div>
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
</script>
</body>
</html>
