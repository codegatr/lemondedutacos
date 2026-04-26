<?php require_once __DIR__ . '/includes/functions.php'; ?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Medya – Le Monde Du Tacos</title>
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
    .page-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 70% 50%,rgba(178,69,69,.2) 0%,transparent 60%);}
    .page-hero-inner{position:relative;z-index:2;}
    .page-hero h1{font-family:Georgia,serif;font-size:clamp(32px,5vw,56px);color:#fff;font-weight:700;margin-bottom:16px;font-style:italic;}
    .page-hero p{font-size:17px;color:rgba(255,255,255,.75);max-width:560px;margin:0 auto;line-height:1.7;}

    /* TAB NAV */
    .tab-nav{background:#fff;border-bottom:1px solid #e5e7eb;padding:0 18px;display:flex;justify-content:center;}
    .tab-nav-inner{display:flex;gap:4px;}
    .tab-btn{padding:14px 20px;font-family:'Retrim',sans-serif;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:var(--muted);background:none;border:none;border-bottom:3px solid transparent;cursor:pointer;transition:all .2s;}
    .tab-btn.active{color:var(--brand);border-bottom-color:var(--brand);font-weight:700;}
    .tab-btn:hover:not(.active){color:var(--ink);}

    .media-section{padding:60px 18px;background:#fff;}
    .media-inner{max-width:var(--max);margin:0 auto;}
    .tab-panel{display:none;}.tab-panel.active{display:block;}
    .section-eyebrow{font-family:'Retrim',sans-serif;font-size:11px;letter-spacing:3px;text-transform:uppercase;color:var(--brand2);margin-bottom:10px;}
    .section-title{font-family:Georgia,serif;font-size:clamp(22px,3vw,36px);font-weight:700;font-style:italic;color:var(--ink);margin-bottom:36px;}

    /* GALERİ — MASONRY */
    .photo-grid{columns:3;gap:16px;}
    .photo-item{break-inside:avoid;margin-bottom:16px;border-radius:12px;overflow:hidden;position:relative;cursor:pointer;transition:transform .3s,box-shadow .3s;}
    .photo-item:hover{transform:scale(1.02);box-shadow:0 12px 32px rgba(0,0,0,.2);}
    .photo-item img{width:100%;display:block;}
    .photo-caption{position:absolute;bottom:0;left:0;right:0;padding:12px 14px;background:linear-gradient(to top,rgba(0,0,0,.7),transparent);color:#fff;font-family:'Retrim',sans-serif;font-size:11px;letter-spacing:1px;text-transform:uppercase;}

    /* BASIN */
    .press-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;}
    .press-card{border:1px solid #e5e7eb;border-radius:14px;padding:28px;transition:box-shadow .3s,transform .3s;}
    .press-card:hover{box-shadow:0 8px 28px rgba(0,0,0,.1);transform:translateY(-3px);}
    .press-date{font-family:'Retrim',sans-serif;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:10px;}
    .press-card h3{font-family:Georgia,serif;font-size:18px;font-weight:700;font-style:italic;margin-bottom:10px;}
    .press-card p{font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:16px;}
    .press-link{font-family:'Retrim',sans-serif;font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--brand);font-weight:700;display:inline-flex;align-items:center;gap:5px;}

    /* KİT */
    .kit-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;}
    .kit-card{border:1px solid #e5e7eb;border-radius:12px;padding:24px;display:flex;flex-direction:column;gap:12px;transition:box-shadow .3s;}
    .kit-card:hover{box-shadow:0 6px 20px rgba(0,0,0,.1);}
    .kit-icon{width:48px;height:48px;border-radius:10px;background:linear-gradient(135deg,var(--brand),#5a7a10);display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;}
    .kit-card h3{font-size:15px;font-weight:700;}
    .kit-card p{font-size:13px;color:var(--muted);line-height:1.5;}
    .btn-dl{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;background:var(--brand);color:#fff;border-radius:8px;font-family:'Retrim',sans-serif;font-size:10px;letter-spacing:1px;text-transform:uppercase;font-weight:700;transition:background .2s;}
    .btn-dl:hover{background:#2d4f0d;}

    /* LIGHTBOX */
    .lightbox{position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;display:none;align-items:center;justify-content:center;padding:20px;}
    .lightbox.open{display:flex;}
    .lightbox img{max-width:90vw;max-height:90vh;border-radius:8px;object-fit:contain;}
    .lb-close{position:absolute;top:20px;right:24px;color:#fff;font-size:32px;cursor:pointer;background:none;border:none;line-height:1;}

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
      .photo-grid{columns:2;}
      .footer{flex-direction:column;text-align:center;gap:8px;padding:14px 10px;}
    }
    @media(max-width:600px){.photo-grid{columns:1;}}
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
    <a href="tarihce.php"><i class="fa-solid fa-clock-rotate-left"></i> Tarihçe</a>
    <a class="active" href="medya.php"><i class="fa-solid fa-photo-film"></i> Medya</a>
    <a href="uretim.php"><i class="fa-solid fa-industry"></i> Üretim</a>
    <a href="insan-kaynaklari.php"><i class="fa-solid fa-users"></i> İnsan Kaynakları</a>
  </div>
</nav>

<section class="page-hero">
  <div class="page-hero-inner">
    <h1>Medya</h1>
    <p>Kurumsal görsellerimiz, basın materyalleri ve marka kiti.</p>
  </div>
</section>

<div class="tab-nav">
  <div class="tab-nav-inner">
    <button class="tab-btn active" data-tab="foto"><i class="fa-solid fa-images"></i> Fotoğraf Galerisi</button>
    <button class="tab-btn" data-tab="basin"><i class="fa-solid fa-newspaper"></i> Basın</button>
    <button class="tab-btn" data-tab="kit"><i class="fa-solid fa-box-open"></i> Medya Kiti</button>
  </div>
</div>

<section class="media-section">
  <div class="media-inner">

    <!-- FOTO GALERİ -->
    <div class="tab-panel active" id="tab-foto">
      <div class="section-eyebrow">Kurumsal Galeri</div>
      <div class="section-title">Görsel Arşiv</div>
      <div class="photo-grid">

        <div class="photo-item" onclick="openLb(this)">
          <img src="/static/img/yeni/slideranasayfa.png" onerror="this.src='/static/img/yeni/banner2.png'" alt="LMD Tacos – Ekip ve Marka">
          <div class="photo-caption">Marka & Ekip Ruhu</div>
        </div>

        <div class="photo-item" onclick="openLb(this)">
          <img src="/static/img/yeni/banner2.png" onerror="this.src='/static/img/yeni/slideranasayfa.png'" alt="LMD Tacos – Ürünler">
          <div class="photo-caption">Ürün Çekimi</div>
        </div>

        <div class="photo-item" onclick="openLb(this)">
          <img src="/static/img/yeni/seçilmişlezzetler_1x1.png" onerror="this.src='/static/img/yeni/imzalilezzetler1x1.png'" alt="Şube İç Mekan">
          <div class="photo-caption">Şube Konsepti</div>
        </div>

        <div class="photo-item" onclick="openLb(this)">
          <img src="/static/img/yeni/imzalilezzetler1x1.png" onerror="this.src='/static/img/yeni/seçilmişlezzetler_1x1.png'" alt="İmzalı Lezzetler">
          <div class="photo-caption">İmzalı Lezzetler</div>
        </div>

        <div class="photo-item" onclick="openLb(this)">
          <img src="/static/img/yeni/gurmelezzetler1x1.png" onerror="this.src='/static/img/yeni/etbun1x1.png'" alt="Gurme Lezzetler">
          <div class="photo-caption">Gurme Lezzetler</div>
        </div>

        <div class="photo-item" onclick="openLb(this)">
          <img src="/static/img/yeni/etbun1x1.png" onerror="this.src='/static/img/yeni/tavukbun1x1.png'" alt="Et Bun">
          <div class="photo-caption">Et Bun</div>
        </div>

        <div class="photo-item" onclick="openLb(this)">
          <img src="/static/img/yeni/tavukbun1x1.png" onerror="this.src='/static/img/yeni/gurmeburger1x1.png'" alt="Tavuk Bun">
          <div class="photo-caption">Tavuk Bun</div>
        </div>

        <div class="photo-item" onclick="openLb(this)">
          <img src="/static/img/yeni/gurmeburger1x1.png" onerror="this.src='/static/img/yeni/churros1X1.png'" alt="Gurme Burger">
          <div class="photo-caption">Gurme Burger</div>
        </div>

        <div class="photo-item" onclick="openLb(this)">
          <img src="/static/img/yeni/churros1X1.png" onerror="this.src='/static/img/yeni/yanurunler1x1.png'" alt="Churros">
          <div class="photo-caption">Tatlı & Churros</div>
        </div>

      </div>
      <p style="font-size:13px;color:var(--muted);margin-top:24px;text-align:center;">Yüksek çözünürlüklü görseller için Medya Kiti sekmesini ziyaret edin.</p>
    </div>

    <!-- BASIN -->
    <div class="tab-panel" id="tab-basin">
      <div class="section-eyebrow">Haberler</div>
      <div class="section-title">Basın Bültenleri</div>
      <div class="press-grid">
        <div class="press-card">
          <div class="press-date">2024</div>
          <h3>Kapadokya'ya Açılım</h3>
          <p>Türkiye'nin en hızlı büyüyen French Tacos markası Nevşehir'de yeni şubesiyle Anadolu'daki varlığını güçlendirdi.</p>
          <span class="press-link">Bülteni İndir <i class="fa-solid fa-arrow-right"></i></span>
        </div>
        <div class="press-card">
          <div class="press-date">2023</div>
          <h3>Franchise Modeli Hayata Geçti</h3>
          <p>LMD Tacos franchise programı, ilk haftada büyük talep gördü. Yatırımcılarla 4D+1 formülü paylaşıldı.</p>
          <span class="press-link">Bülteni İndir <i class="fa-solid fa-arrow-right"></i></span>
        </div>
        <div class="press-card">
          <div class="press-date">2022</div>
          <h3>İstanbul'da İlk Şube Açıldı</h3>
          <p>Türkiye'de French Tacos segmentinde öncü olmak üzere Yenibosna'da açılan ilk şube kapılarını açtı.</p>
          <span class="press-link">Bülteni İndir <i class="fa-solid fa-arrow-right"></i></span>
        </div>
        <div class="press-card">
          <div class="press-date">2023</div>
          <h3>Türk Patent Tescil Belgesi</h3>
          <p>Özel sos ve marinasyon reçeteleri, Türk Patent ve Marka Kurumu tarafından tescil edildi. Özgün lezzetimiz artık belgelenmiş.</p>
          <span class="press-link">Bülteni İndir <i class="fa-solid fa-arrow-right"></i></span>
        </div>
      </div>
    </div>

    <!-- KİT -->
    <div class="tab-panel" id="tab-kit">
      <div class="section-eyebrow">İndirilebilir Materyaller</div>
      <div class="section-title">Medya Kiti</div>
      <div class="kit-grid">
        <div class="kit-card">
          <div class="kit-icon"><i class="fa-solid fa-image"></i></div>
          <h3>Logo Paketi</h3>
          <p>SVG, PNG (şeffaf), EPS formatlarında renkli ve tek renk logo dosyaları.</p>
          <a href="#" class="btn-dl"><i class="fa-solid fa-download"></i> İndir</a>
        </div>
        <div class="kit-card">
          <div class="kit-icon"><i class="fa-solid fa-palette"></i></div>
          <h3>Marka Kılavuzu</h3>
          <p>Renk paleti, tipografi, kullanım kuralları ve marka sesi rehberi.</p>
          <a href="#" class="btn-dl"><i class="fa-solid fa-download"></i> İndir</a>
        </div>
        <div class="kit-card">
          <div class="kit-icon"><i class="fa-solid fa-images"></i></div>
          <h3>Ürün Fotoğrafları</h3>
          <p>Yüksek çözünürlüklü, editoryal kullanım için lisanslı ürün görselleri.</p>
          <a href="#" class="btn-dl"><i class="fa-solid fa-download"></i> İndir</a>
        </div>
        <div class="kit-card">
          <div class="kit-icon"><i class="fa-solid fa-file-lines"></i></div>
          <h3>Kurumsal Tanıtım</h3>
          <p>Marka hikayesi, kurucu bilgileri, şube ağı ve iş rakamlarını içeren basın dosyası.</p>
          <a href="#" class="btn-dl"><i class="fa-solid fa-download"></i> İndir</a>
        </div>
      </div>
      <div style="margin-top:40px;padding:24px;background:#f9fafb;border-radius:14px;border:1px solid #e5e7eb;">
        <h3 style="font-family:Georgia,serif;font-style:italic;margin-bottom:8px;">Medya İletişim</h3>
        <p style="font-size:14px;color:var(--muted);">Basın soruları ve röportaj talepleri için: <a href="mailto:medya@lemondedutacos.com" style="color:var(--brand);font-weight:700;">medya@lemondedutacos.com</a></p>
      </div>
    </div>

  </div>
</section>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="closeLb()">
  <button class="lb-close" onclick="closeLb()">×</button>
  <img id="lbImg" src="" alt="">
</div>

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
  document.querySelectorAll(".tab-btn").forEach(b=>{
    b.addEventListener("click",()=>{
      const t=b.dataset.tab;
      document.querySelectorAll(".tab-btn").forEach(x=>x.classList.remove("active"));
      document.querySelectorAll(".tab-panel").forEach(x=>x.classList.remove("active"));
      b.classList.add("active");
      document.getElementById("tab-"+t).classList.add("active");
    });
  });
  function openLb(el){
    const img=el.querySelector("img");
    document.getElementById("lbImg").src=img.src;
    document.getElementById("lightbox").classList.add("open");
  }
  function closeLb(){document.getElementById("lightbox").classList.remove("open");}
  document.addEventListener("keydown",(e)=>{if(e.key==="Escape")closeLb();});
</script>
</body>
</html>
