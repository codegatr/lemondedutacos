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

    .media-section{padding:60px 18px;background:#fff;}
    .media-inner{max-width:var(--max);margin:0 auto;}
    .section-eyebrow{font-family:'Retrim',sans-serif;font-size:11px;letter-spacing:3px;text-transform:uppercase;color:var(--brand2);margin-bottom:10px;}
    .section-title{font-family:Georgia,serif;font-size:clamp(22px,3vw,36px);font-weight:700;font-style:italic;color:var(--ink);margin-bottom:36px;}

    /* GALERİ — MASONRY */

    /* BASIN */
    .press-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;}
    .press-card{border:1px solid #e5e7eb;border-radius:14px;padding:28px;transition:box-shadow .3s,transform .3s;}
    .press-card:hover{box-shadow:0 8px 28px rgba(0,0,0,.1);transform:translateY(-3px);}
    .press-date{font-family:'Retrim',sans-serif;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:10px;}
    .press-card h3{font-family:Georgia,serif;font-size:18px;font-weight:700;font-style:italic;margin-bottom:10px;}
    .press-card p{font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:16px;}
    .press-link{font-family:'Retrim',sans-serif;font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--brand);font-weight:700;display:inline-flex;align-items:center;gap:5px;}

    /* KİT */

    /* LIGHTBOX */

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
    @media(max-width:600px){}
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
    <a class="active" href="/medya.php"><i class="fa-solid fa-photo-film"></i> Medya</a>
    <a href="/uretim.php"><i class="fa-solid fa-industry"></i> Üretim</a>
    <a href="/insan-kaynaklari.php"><i class="fa-solid fa-users"></i> İnsan Kaynakları</a>
  </div>
</nav>

<section class="page-hero">
  <div class="page-hero-inner">
    <h1>Medya</h1>
    <p>Kurumsal görsellerimiz, basın materyalleri ve marka kiti.</p>
  </div>
</section>

<section class="media-section">
  <div class="media-inner">

    <!-- BASIN -->
    <div class="media-block">
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

      <div style="margin-top:40px;padding:24px;background:#f9fafb;border-radius:14px;border:1px solid #e5e7eb;">
        <h3 style="font-family:Georgia,serif;font-style:italic;margin-bottom:8px;">Medya İletişim</h3>
        <p style="font-size:14px;color:var(--muted);">Basın soruları ve röportaj talepleri için: <a href="mailto:medya@lemondedutacos.com" style="color:var(--brand);font-weight:700;">medya@lemondedutacos.com</a></p>
      </div>

  </div>
</section>

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
