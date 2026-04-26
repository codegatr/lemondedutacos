<?php require_once __DIR__ . '/includes/functions.php'; ?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Le Monde Du Tacos – Le Goût Authentique du French Tacos - Hakkımızda</title>

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

    *{ box-sizing:border-box; }
    body{
      margin:0;
      overflow: hidden;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color:var(--ink);
      background:var(--bg);
    }
    a{ color:inherit; text-decoration:none; }
    button{ font:inherit; }

    /* ======= TOP BAR ======= */
    .topbar{ position: sticky; top:0; z-index:999; background:#fff; }
    .topbar-inner{
      max-width: var(--max);
      margin:0 auto;
      padding: 14px 18px;
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
      margin-top: 20px;
      display:flex;
      flex-direction:column;
      justify-content:center;
    }

    .brand .logo{
      font-family: Georgia, serif;
      font-size:28px;
      line-height:1;
      color:#3A5F0B;
      font-weight:700;
    }

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
    .nav a::after{
      content:"";
      position:absolute;
      top:0;
      left:-75%;
      width:50%;
      height:100%;
      background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,.6) 50%, transparent 100%);
      transform: skewX(-20deg);
    }
    .nav a:not(.active):hover::after{ animation: shine 1.6s ease forwards; }
    @keyframes shine{ 0%{ left:-75%; } 100%{ left:130%; } }
    .nav a.active:hover{ background: var(--brand); color:#fff; }

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
      background: #000;
      overflow:hidden;
      rotate: -2deg;
      scale: 1.02;
      width: 104vw;
      margin-left: calc(50% - 52vw);
      z-index: 999;
      transform-origin:center;
    }

    .hero-video{
      position:absolute;
      inset:0;
      width:100%;
      height:100%;
      object-fit:cover;
      object-position:center center;
      z-index:0;
      pointer-events:none;
    }

    .hero::after{
      content:"";
      position:absolute;
      inset:0;
      z-index:1;
      background: linear-gradient(180deg, rgba(0,0,0,.12) 0%, rgba(0,0,0,.28) 55%, rgba(0,0,0,.40) 100%);
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

    /* ======= ABOUT PANEL (AÇILIŞTA SAĞDAN KAYAR) ======= */
    .about-panel{
      position:absolute;
      left:50%;
      top:50%;
      width: min(940px, 92vw);
      z-index:3;

      opacity:0;
      transform: translate(-50%, -50%) translateX(90px);
      filter: blur(8px);
      pointer-events:none;

      animation: enterFromRight .85s cubic-bezier(.2,.9,.2,1) .15s forwards;
      will-change: transform, opacity, filter;
    }

    @keyframes enterFromRight{
      0%{
        opacity:0;
        transform: translate(-50%, -50%) translateX(90px);
        filter: blur(8px);
      }
      100%{
        opacity:1;
        transform: translate(-50%, -50%) translateX(0);
        filter: blur(0);
      }
    }

    @keyframes enterMobile{
      0%{ opacity:0; transform:translateX(30px); filter:blur(6px); }
      100%{ opacity:1; transform:translateX(0); filter:blur(0); }
    }

    .about-card{
      position: relative;
      overflow: hidden;
      isolation: isolate;
      pointer-events:none;
      padding: 18px 18px;
      border-radius: 18px;
      background: rgba(8,10,14,.86);
      border: 1px solid rgba(255,255,255,.38);
      box-shadow:
        0 28px 80px rgba(0,0,0,.62),
        inset 0 0 0 1px rgba(255,255,255,.10);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
    }

    .about-title{
      margin:0 0 10px 0;
      font-family: Georgia, serif;
      font-style: italic;
      font-weight: 900;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: #ffffff;
      font-size: clamp(24px, 3.6vw, 46px);
      text-shadow:
        0 2px 4px rgba(0,0,0,.9),
        0 8px 24px rgba(0,0,0,.7);
      text-align:center;
    }

    .about-lead{
      margin: 8px auto 0;
      color: #ffffff;
      line-height: 1.72;
      font-size: 14px;
      font-weight: 500;
      text-shadow:
        0 1px 3px rgba(0,0,0,.95),
        0 4px 12px rgba(0,0,0,.8);
    }

    .about-grid{
      display:grid;
      grid-template-columns: 1.15fr .85fr;
      gap: 14px;
      margin-top: 12px;
    }

    .about-block{
      border-radius: 14px;
      background: rgba(0,0,0,.40);
      border: 1px solid rgba(255,255,255,.22);
      padding: 14px 14px;
      box-shadow: inset 0 0 0 1px rgba(0,0,0,.30);
    }

    .about-h{
      margin:0 0 8px 0;
      color: #ffffff;
      font-weight: 900;
      letter-spacing:.4px;
      font-size: 14px;
      text-transform: uppercase;
      text-shadow:
        0 1px 3px rgba(0,0,0,.95),
        0 3px 10px rgba(0,0,0,.8);
    }

    .about-p{
      margin:0;
      color: #ffffff;
      line-height: 1.62;
      font-size: 13px;
      font-weight: 500;
      text-shadow:
        0 1px 3px rgba(0,0,0,.95),
        0 3px 10px rgba(0,0,0,.8);
    }

    .about-bullets{
      margin: 10px 0 0 0;
      padding-left: 18px;
      color: #ffffff;
      line-height: 1.62;
      font-size: 13px;
      font-weight: 500;
      text-shadow:
        0 1px 3px rgba(0,0,0,.95),
        0 3px 10px rgba(0,0,0,.8);
    }
    .about-bullets li{ margin: 6px 0; }

    .hr-shine{
      height:2px;
      width:min(380px, 64vw);
      margin: 10px auto 10px;
      background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,.98), rgba(255,255,255,0));
      opacity:0;
      transform: translateY(10px);
      animation: hrIn .85s ease .35s forwards;
    }

    @keyframes hrIn{
      from{ opacity:0; transform: translateY(10px); }
      to{ opacity:.95; transform: translateY(0); }
    }

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

    /* ======= SOCIAL ======= */
    .social-nav{ padding:0; margin:0; list-style:none; display:flex; align-items:center; gap:10px; }
    .social-nav li{ display:inline-block; }
    .social-nav a{
      display:inline-block;
      width:36px;
      height:36px;
      line-height:36px;
      text-align:center;
      color:#fff;
      text-decoration:none;
      background:#000;
      border-radius:8px;
      position:relative;
      transition: .35s ease;
      overflow:hidden;
      font-size:18px;
    }
    .model-2 a{ overflow:hidden; font-size:20px; border-radius:10px; margin:0; }
    .model-2 a:hover{
      background:#fff;
      text-shadow:
        0px 0px #d5d5d5, 1px 1px #d5d5d5, 2px 2px #d5d5d5, 3px 3px #d5d5d5,
        4px 4px #d5d5d5, 5px 5px #d5d5d5, 6px 6px #d5d5d5, 7px 7px #d5d5d5,
        8px 8px #d5d5d5, 9px 9px #d5d5d5, 10px 10px #d5d5d5;
    }
    .model-2 .facebook{ background:#3B579D; text-shadow: 0 0 #2d4278, 1px 1px #2d4278, 2px 2px #2d4278, 3px 3px #2d4278, 4px 4px #2d4278, 5px 5px #2d4278, 6px 6px #2d4278, 7px 7px #2d4278; }
    .model-2 .facebook:hover{ color:#3B579D; }
    .model-2 .instagram{ background:#E1306C; text-shadow: 0 0 #b12353, 1px 1px #b12353, 2px 2px #b12353, 3px 3px #b12353, 4px 4px #b12353, 5px 5px #b12353, 6px 6px #b12353, 7px 7px #b12353; }
    .model-2 .instagram:hover{ color:#E1306C; }
    .model-2 .twitter{ background:#111827; text-shadow: 0 0 #0b1220, 1px 1px #0b1220, 2px 2px #0b1220, 3px 3px #0b1220, 4px 4px #0b1220, 5px 5px #0b1220, 6px 6px #0b1220, 7px 7px #0b1220; }
    .model-2 .twitter:hover{ color:#111827; }
    .model-2 .youtube{ background:#FF0000; text-shadow: 0 0 #c40000, 1px 1px #c40000, 2px 2px #c40000, 3px 3px #c40000, 4px 4px #c40000, 5px 5px #c40000, 6px 6px #c40000, 7px 7px #c40000; }
    .model-2 .youtube:hover{ color:#FF0000; }

    /* ABOUT CARD – BORDER LIGHT */
    .about-card::before{
      content:"";
      position:absolute;
      inset:-2px;
      border-radius:inherit;
      padding:2px;
      background: conic-gradient(
        from 0deg,
        rgba(255,255,255,0) 0deg,
        rgba(255,255,255,.95) 40deg,
        rgba(255,255,255,0) 90deg,
        rgba(255,255,255,0) 360deg
      );
      -webkit-mask:
        linear-gradient(#000 0 0) content-box,
        linear-gradient(#000 0 0);
      mask:
        linear-gradient(#000 0 0) content-box,
        linear-gradient(#000 0 0);
      -webkit-mask-composite: xor;
              mask-composite: exclude;
      filter: blur(1.4px);
      opacity:.85;
      pointer-events:none;
      z-index:2;
      animation: borderRotate 2.5s linear infinite;
    }

    @keyframes borderRotate{
      to{ transform: rotate(360deg); }
    }

    /* ABOUT CARD – SURFACE SHINE */
    .about-card::after{
      content:"";
      position:absolute;
      top:0;
      left:-80%;
      width:60%;
      height:100%;
      background: linear-gradient(
        120deg,
        transparent 0%,
        rgba(255,255,255,.25) 45%,
        rgba(255,255,255,.75) 50%,
        rgba(255,255,255,.25) 55%,
        transparent 100%
      );
      transform: skewX(-20deg);
      pointer-events:none;
      z-index:3;
      animation: surfaceShine 2.8s linear infinite;
    }

    @keyframes surfaceShine{
      0%{ left:-80%; }
      100%{ left:140%; }
    }

    .about-card > *{
      position:relative;
      z-index:4;
    }

    /* ======= MOBILE ======= */
    @media (max-width: 940px){
      body{ overflow-x:hidden; overflow-y:auto; }
      .brand{ min-width:auto; }
      .hamburger{ display:flex; }
      .topbar{
        flex-shrink:0;
        z-index: 9999;
      }

      .topbar-inner{
        padding:8px 14px;
      }

      .logo-wrapper{
        margin-left:20px;
      }

      .brand-logo{
        height:90px;
        top:-16px;
      }

      .brand .logo{
        font-size:20px;
      }

      .brand-text{
        margin-top:12px;
      }

      .brand{
        min-width:auto;
      }

      .hamburger{
        display:flex;
      }

      .nav{
        position:absolute;
        top:58px;
        right:12px;
        left:12px;
        background:#fff;
        border:1px solid rgba(0,0,0,.10);
        border-radius:14px;
        box-shadow: var(--shadow);
        padding:10px;
        display:none;
        flex-direction:column;
        align-items:stretch;
        gap:6px;
        z-index:9998;
      }

      .nav.open{
        display:flex;
      }

      .nav a{
        padding:12px;
      }

      .hero{
        rotate:0deg;
        scale:1;
        min-height:unset;
        height:auto;
        overflow:visible;
        display:block;
        padding:24px 14px 32px;
      }

      .hero-video{
        object-fit:cover;
      }

      .about-panel{
        position:relative !important;
        top:auto !important;
        left:auto !important;
        transform:none !important;
        width:100% !important;
        max-width:100%;
        padding:0;
        opacity:1;
        filter:none;
        pointer-events:auto;
        animation:enterMobile .85s cubic-bezier(.2,.9,.2,1) .15s both !important;
      }

      .about-card{
        padding:16px;
        border-radius:16px;
      }

      .about-title{
        font-size:clamp(20px,6vw,34px);
        letter-spacing:1px;
      }

      .about-lead{ font-size:13px; line-height:1.65; }

      .about-grid{
        grid-template-columns:1fr;
        gap:10px;
        margin-top:10px;
      }

      .about-p, .about-bullets{ font-size:13px; }

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
        <a class="active" href="/hakkimizda.php">KURUMSAL</a>
        <a href="/subeler.php">ŞUBELER</a>
        <a href="/kampanyalar.php">KAMPANYALAR</a>
        <a href="/franchise.php">FRANCHISE</a>
        <a href="/iletisim.php">İLETİŞİM</a>
      </nav>
    </div>
  </header>

  <main class="hero" role="main" aria-label="Ana görsel alanı" id="hero">
    <video
      class="hero-video"
      muted
      autoplay
      loop
      playsinline
      preload="auto"
    >
      <source src="/static/img/yeni/o.mp4" type="video/mp4" />
    </video>

    <section class="about-panel" id="aboutPanel" aria-hidden="false">
      <div class="about-card" id="aboutCard">
        <h1 class="about-title">HAKKIMIZDA</h1>
        <div class="hr-shine" id="hrShine"></div>

        <p class="about-lead">
          Fransa'da özellikle Lyon çıkışlı olan ve 2007'den itibaren Avrupa'da hızla
          yayılan French Tacos, klasik Meksika Taco'sundan tamamen farklı bir üründür.
          İçinde et, patates, peynir sosu ve özel baharatların bulunduğu; mühürlenerek
          servis edilen bu ürün, Avrupa'da genç kuşaklar arasında adeta bir sokak
          kültürüne dönüşmüştü. Türkiye'de ise bu alanda belirgin bir zincir ve güçlü bir
          marka yoktu.
        </p>

        <p class="about-lead" style="margin-top:10px">
          2019 yılında, kurucularımız, Fransa'da büyük bir fenomen haline gelen ve
          kısa sürede hızla yayılan bu farklı ürünü Türkiye'nin zengin gastronomi
          sahnesiyle buluşturma hayalini hayata geçirdiler.
        </p>

        <div class="about-grid">
          <div class="about-block">
            <div class="about-h">Hedefimiz</div>
            <p class="about-p">
              Yenilikçi ve benzersiz Fransız Tacos lezzetimizi Türkiye'nin dört bir yanında ve
              uluslararası alanda erişilebilir kılmak; her şubemizde aynı kalite ve müşteri
              memnuniyetini sağlayarak markamızı hızlı servis restoran sektöründe lider konuma
              taşımaktır.
            </p>
            <ul class="about-bullets">
              <li>Yatırımcı ve girişimcilerle büyüyen bir aile yapısı oluşturmak</li>
              <li>Sürdürülebilir kârlılık ve yüksek marka değeri yaratmak</li>
              <li>Sektör standartlarının üzerinde kurumsal kaliteyi yaygınlaştırmak</li>
            </ul>
          </div>

          <div class="about-block">
            <div class="about-h">Misyonumuz</div>
            <p class="about-p">
              Müşterilerimize misafir deneyimi yaşatmak, tacos denince akla ilk gelen marka olmak.
            </p>

            <div style="height:12px"></div>

            <div class="about-h">Vizyonumuz</div>
            <p class="about-p">
              Türkiye'de ve yurt dışında orijinal Fransız Tacos deneyiminin lideri ve referans markası
              haline getirmek; inovasyonda öncü, müşteri memnuniyetinde örnek ve franchise ekosisteminde
              sürdürülebilir büyümeyle ilk akla gelen marka olmaktır.
            </p>
          </div>
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

    window.addEventListener("load", () => {
      const card = document.getElementById("aboutCard");
      card?.classList.add("shine-ready");
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