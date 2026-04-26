<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$branches = db()->query(
    "SELECT title, address, map_url FROM branches WHERE is_active = 1 ORDER BY sort_order, id"
)->fetchAll();
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Le Monde Du Tacos – Le Goût Authentique du French Tacos - Şubeler</title>

  <!-- Font Awesome (model-2 ikonları için) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    :root{
      --brand:#3A5F0B;
      --brand2:#b24545;
      --ink:#1f2937;
      --muted:#6b7280;
      --bg:#ffffff;
      --shadow: 0 10px 30px rgba(0,0,0,.18);
      --ring: rgba(255,255,255,.7);
      --ring2: rgba(0,0,0,.35);
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
    .topbar{
      position: sticky;
      top:0;
      z-index:999;
      background:#fff;
    }
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
      background: linear-gradient(
        120deg,
        transparent 0%,
        rgba(255,255,255,.6) 50%,
        transparent 100%
      );
      transform: skewX(-20deg);
    }

    .nav a:not(.active):hover::after{
      animation: shine 1.6s ease forwards;
    }

    @keyframes shine{
      0%{ left:-75%; }
      100%{ left:130%; }
    }

    .nav a.active:hover{
      background: var(--brand);
      color:#fff;
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
      background: url("static/img/locations1.jpg") center center / cover no-repeat;
      transform: none;
      transform-origin: center;
      overflow:hidden;
      rotate: -2deg;
      scale: 0.95;
      z-index: 999;
      height: calc(100vh - 160px);
      display: flex;
      align-items: center;
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

    /* ======= BOTTOM ICON STRIP ======= */
    .strip{
      position:absolute;
      left:0; right:0; bottom:0;
      background: linear-gradient(180deg, rgba(178,69,69,.05), rgba(139, 45, 45, 0.904));
      padding: 26px 16px 18px;
      z-index:3;
    }
    .strip-inner{
      max-width: var(--max);
      margin:0 auto;
      display:flex;
      align-items:flex-end;
      justify-content:center;
      gap:34px;
      flex-wrap: wrap;
    }

    .icon-card{
      width: 140px;
      display:flex;
      flex-direction:column;
      align-items:center;
      gap:10px;
      color:#fff;
      text-align:center;
      user-select:none;
    }
    .icon-btn{
      width: 86px;
      height: 86px;
      border-radius: 50%;
      display:grid;
      place-items:center;
      background: rgba(17,24,39,.28);
      border: 2px solid rgba(255,255,255,.25);
      box-shadow:
        inset 0 0 0 3px rgba(0,0,0,.25),
        0 12px 30px rgba(0,0,0,.28);
      transition: transform .15s ease, background .15s ease;
      cursor:pointer;
    }
    .icon-btn:hover{
      transform: translateY(-2px);
      background: rgba(17,24,39,.40);
    }

    .icon-label{
      font-weight:800;
      letter-spacing:.6px;
      font-size: 12px;
      text-transform: uppercase;
      text-shadow: 0 2px 10px rgba(0,0,0,.35);
    }

    .ico{
      width:40px; height:40px;
      opacity:.95;
      filter: drop-shadow(0 4px 12px rgba(0,0,0,.35));
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

    /* ======= MODEL-2 (Footer Social) — only needed CSS ======= */
    .social-nav{
      padding:0;
      margin:0;
      list-style:none;
      display:flex;
      align-items:center;
      gap:10px;
    }
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

    .model-2 a{
      overflow:hidden;
      font-size:20px;
      border-radius:10px;
      margin:0;
    }
    .model-2 a:hover{
      background:#fff;
      text-shadow:
        0px 0px #d5d5d5, 1px 1px #d5d5d5, 2px 2px #d5d5d5, 3px 3px #d5d5d5,
        4px 4px #d5d5d5, 5px 5px #d5d5d5, 6px 6px #d5d5d5, 7px 7px #d5d5d5,
        8px 8px #d5d5d5, 9px 9px #d5d5d5, 10px 10px #d5d5d5;
    }

    .model-2 .facebook{
      background:#3B579D;
      text-shadow: 0 0 #2d4278, 1px 1px #2d4278, 2px 2px #2d4278, 3px 3px #2d4278,
                   4px 4px #2d4278, 5px 5px #2d4278, 6px 6px #2d4278, 7px 7px #2d4278;
    }
    .model-2 .facebook:hover{ color:#3B579D; }

    .model-2 .instagram{
      background:#E1306C;
      text-shadow: 0 0 #b12353, 1px 1px #b12353, 2px 2px #b12353, 3px 3px #b12353,
                   4px 4px #b12353, 5px 5px #b12353, 6px 6px #b12353, 7px 7px #b12353;
    }
    .model-2 .instagram:hover{ color:#E1306C; }

    .model-2 .twitter{
      background:#111827;
      text-shadow: 0 0 #0b1220, 1px 1px #0b1220, 2px 2px #0b1220, 3px 3px #0b1220,
                   4px 4px #0b1220, 5px 5px #0b1220, 6px 6px #0b1220, 7px 7px #0b1220;
    }
    .model-2 .twitter:hover{ color:#111827; }

    .model-2 .youtube{
      background:#FF0000;
      text-shadow: 0 0 #c40000, 1px 1px #c40000, 2px 2px #c40000, 3px 3px #c40000,
                   4px 4px #c40000, 5px 5px #c40000, 6px 6px #c40000, 7px 7px #c40000;
    }
    .model-2 .youtube:hover{ color:#FF0000; }

    /* ======= MOBILE ======= */
    @media (max-width: 860px){
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
        height:auto;
        min-height:100vh;
        overflow:hidden;
        display:flex;
        align-items:center;
        padding:24px 14px 40px;
      }

      .hero-grid{
        position:relative !important;
        top:auto !important;
        left:auto !important;
        transform:none !important;
        width:100% !important;
        grid-template-columns:1fr !important;
        gap:14px;
      }

      .strip{ padding:20px 14px 14px; }
      .strip-inner{ gap:16px; }
      .icon-card{ width:44%; max-width:170px; }
      .icon-btn{ width:78px; height:78px; }

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

    @media (max-width: 480px){
      .topbar-inner{ padding:10px 14px; }
      .hero{ padding:16px 12px 80px; }
      .grid-box{ padding:16px 18px; font-size:14px; }
      .grid-box strong{ font-size:15px; margin-bottom:8px; }
    }

    @media (max-width: 420px){
      .icon-card{ width:48%; }
      .footer{ flex-direction:column; align-items:flex-start; }
    }

    /* =========================
       STRIP ICON: BÜYÜME + PARLAMA + SHINE
       ========================= */

    .icon-card{ position:relative; }

    .icon-btn{
      position: relative;
      overflow: hidden;
      transform: translateZ(0);
      transition: transform .22s ease, box-shadow .22s ease, background .22s ease, border-color .22s ease;
      will-change: transform, box-shadow;
    }

    .icon-btn::before{
      content:"";
      position:absolute;
      inset:-10px;
      border-radius:999px;
      background: radial-gradient(circle at 30% 30%,
        rgba(255,255,255,.45) 0%,
        rgba(255,255,255,.18) 25%,
        rgba(255,255,255,0) 62%);
      opacity:0;
      filter: blur(10px);
      transition: opacity .22s ease;
      pointer-events:none;
    }

    .icon-btn::after{
      content:"";
      position:absolute;
      top:-20%;
      left:-80%;
      width:55%;
      height:140%;
      border-radius:999px;
      background: linear-gradient(120deg,
        rgba(255,255,255,0) 0%,
        rgba(255,255,255,.75) 45%,
        rgba(255,255,255,0) 70%);
      transform: skewX(-20deg);
      opacity:0;
      pointer-events:none;
    }

    .icon-card:hover .icon-btn{
      transform: translateY(-4px) scale(1.10);
      background: rgba(255,255,255,.12);
      border-color: rgba(255,255,255,.55);
      box-shadow:
        0 18px 38px rgba(0,0,0,.38),
        0 0 0 2px rgba(255,255,255,.12),
        0 0 28px rgba(255,255,255,.25);
    }

    .icon-card:hover .icon-btn::before{
      opacity:1;
    }

    .icon-card:hover .icon-btn::after{
      opacity:.95;
      animation: iconShine2 .75s ease forwards;
    }

    @keyframes iconShine2{
      0%   { left:-80%; }
      100% { left:140%; }
    }

    .icon-card:hover .ico{
      transform: scale(1.08);
      transition: transform .22s ease;
    }

    .icon-btn:active{
      transform: translateY(-2px) scale(1.04);
    }

    @media (hover:none){
      .icon-btn:active::before{ opacity:1; }
      .icon-btn:active::after{
        opacity:.95;
        animation: iconShine2 .75s ease forwards;
      }
    }

    /* ===============================
       HERO ORTA ADRES GRID (2x3)
       =============================== */

    .hero{
      position:relative;
    }

    .hero-grid{
      position:relative;
      z-index:5;
      width:min(1050px, 92%);
      margin:0 auto;
      display:grid;
      grid-template-columns:repeat(3, 1fr);
      gap:12px;
    }

    /* ===============================
       KART TASARIMI (Glass Premium)
       =============================== */

    .grid-box{
      position:relative;
      padding:14px 18px;
      border-radius:14px;
      cursor:pointer;
      background:rgba(255,255,255,.10);
      backdrop-filter:blur(14px);
      -webkit-backdrop-filter:blur(14px);
      border:1px solid rgba(255,255,255,.35);
      color:#fff;
      line-height:1.5;
      font-size:13px;
      font-weight:500;
      box-shadow:
        0 15px 35px rgba(0,0,0,.35),
        inset 0 0 0 1px rgba(255,255,255,.15);
      transition:
        transform .25s ease,
        box-shadow .25s ease,
        background .25s ease,
        border-color .25s ease;
      overflow:hidden;
      text-decoration:none;
      cursor:pointer;
    }

    .grid-box strong{
      display:block;
      font-size:15px;
      font-weight:700;
      margin-bottom:6px;
      letter-spacing:.4px;
    }

    .grid-box:hover{
      transform:translateY(-6px) scale(1.03);
      background:rgba(255,255,255,.18);
      border-color:rgba(255,255,255,.55);
      box-shadow:
        0 25px 45px rgba(0,0,0,.45),
        0 0 25px rgba(255,255,255,.25);
    }

    .grid-box::after{
      content:"";
      position:absolute;
      top:-20%;
      left:-80%;
      width:60%;
      height:150%;
      background:linear-gradient(
        120deg,
        rgba(255,255,255,0) 0%,
        rgba(255,255,255,.75) 45%,
        rgba(255,255,255,0) 70%
      );
      transform:skewX(-20deg);
      opacity:0;
      pointer-events:none;
    }

    .grid-box:hover::after{
      opacity:.9;
      animation:gridShine .9s ease forwards;
    }

    @keyframes gridShine{
      0%   { left:-80%; }
      100% { left:130%; }
    }

    @media (max-width:1024px){
      .hero-grid{
        gap:18px;
      }
    }

    @media (max-width:860px){
      .hero-grid{
        grid-template-columns:1fr;
        gap:16px;
      }
    }

    @media (max-width:480px){
      .grid-box{
        padding:18px 20px;
        font-size:14px;
      }

      .grid-box strong{
        font-size:16px;
      }
    }

    .grid-box .box-inner{
      display:flex;
      gap:14px;
      align-items:flex-start;
    }

    .grid-box .pin{
      flex:0 0 auto;
      width:46px;
      height:46px;
      border-radius:14px;
      display:grid;
      place-items:center;
      background: rgba(255,255,255,.16);
      border: 1px solid rgba(255,255,255,.30);
      box-shadow: inset 0 0 0 1px rgba(0,0,0,.12);
      transform: translateY(2px);
    }

    .grid-box .pin svg{
      width:22px;
      height:22px;
      fill:#fff;
      filter: drop-shadow(0 6px 12px rgba(0,0,0,.35));
    }

    .grid-box .box-text{
      min-width:0;
    }

    .grid-box .addr{
      opacity:.95;
    }

    .grid-box .hint{
      margin-top:10px;
      display:inline-flex;
      align-items:center;
      gap:8px;
      font-size:12px;
      font-weight:700;
      letter-spacing:.5px;
      text-transform:uppercase;
      opacity:.92;
    }

    .grid-box .hint i{
      font-size:12px;
      opacity:.9;
    }

    .grid-box:hover .pin{
      background: rgba(255,255,255,.22);
      border-color: rgba(255,255,255,.55);
      transform: translateY(0);
    }

    @keyframes mobileCardShine {
      0%, 72%  { left:-80%; opacity:0; }
      75%       { opacity:.85; }
      90%       { left:140%; opacity:.85; }
      100%      { left:140%; opacity:0; }
    }

    @media (max-width: 860px) {
      .grid-box::after {
        animation: mobileCardShine 5s ease-in-out infinite !important;
        animation-delay: var(--shine-delay, 0s) !important;
        opacity: 0;
      }
      .grid-box:active {
        transform: translateY(-4px) scale(1.02);
        background: rgba(255,255,255,.18);
        border-color: rgba(255,255,255,.55);
      }
    }

    /* ===============================
       DAKTİLO YAZI EFEKTİ
       =============================== */

    .type-target{
      white-space:pre-line;
      min-height: 132px;
    }

    .type-target strong{
      display:block;
      min-height: 30px;
    }

    .type-caret{
      display:inline-block;
      width:10px;
      margin-left:2px;
      color:#fff;
      animation: blinkCaret .8s step-end infinite;
      font-weight:700;
    }

    @keyframes blinkCaret{
      0%, 50%{ opacity:1; }
      50.01%, 100%{ opacity:0; }
    }

    .grid-box.typing-active .type-target{
      text-shadow: 0 1px 8px rgba(0,0,0,.18);
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
@media (max-width: 860px){
  .grid-box{
    padding: 14px 16px !important;
    border-radius: 14px;
    font-size: 13px !important;
    line-height: 1.45 !important;
    min-height: auto !important;
  }

  .grid-box strong{
    font-size: 15px !important;
    margin-bottom: 6px !important;
  }

  .type-target{
    min-height: 78px !important;
  }

  .grid-box .box-inner{
    gap: 10px !important;
  }

  .grid-box .pin{
    width: 38px !important;
    height: 38px !important;
    border-radius: 10px !important;
  }

  .grid-box .pin svg{
    width: 18px !important;
    height: 18px !important;
  }

  .grid-box .hint{
    margin-top: 6px !important;
    font-size: 11px !important;
  }
}

@media (max-width: 480px){
  .grid-box{
    padding: 12px 14px !important;
    font-size: 12px !important;
    line-height: 1.4 !important;
  }

  .grid-box strong{
    font-size: 14px !important;
    margin-bottom: 5px !important;
  }

  .type-target{
    min-height: 66px !important;
  }

  .grid-box .pin{
    width: 34px !important;
    height: 34px !important;
  }

  .grid-box .pin svg{
    width: 16px !important;
    height: 16px !important;
  }
}
  </style>
</head>

<body>
  <!-- TOP -->
  <header class="topbar">
    <div class="topbar-inner">
      <a class="brand" href="index.php">
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
        <a href="index.php">ANASAYFA</a>
        <a href="kurumsal.php">KURUMSAL</a>
        <a class="active" href="subeler.php">ŞUBELER</a>
        <a href="kampanyalar.php">KAMPANYALAR</a>
        <a href="franchise.php">FRANCHISE</a>
        <a href="iletisim.php">İLETİŞİM</a>
      </nav>
    </div>
  </header>

  <!-- HERO -->
  <main class="hero" role="main" aria-label="Ana görsel alanı">
    <div class="hero-grid">

      <?php foreach ($branches as $b):
        $lines = str_replace([',', "\n"], '|', $b['address']);
      ?>
      <div class="grid-box"
           data-map="<?= e($b['map_url'] ?: '#') ?>"
           data-title="<?= e($b['title']) ?>"
           data-lines="<?= e($lines) ?>">
        <div class="type-target"></div>
      </div>
      <?php endforeach; ?>

    </div>
  </main>

  <!-- FOOTER -->
  <footer class="footer">
    <ul class="social-nav model-2" aria-label="Sosyal medya">
      <li><a class="facebook" href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a></li>
      <li><a class="instagram" href="https://www.instagram.com/lemondedutacos__?igsh=MWIzMDRzaWw0azhkbA%3D%3D&utm_source=qr" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a></li>
      <li><a class="twitter" href="#" aria-label="Twitter/X"><i class="fa-brands fa-x-twitter"></i></a></li>
      <li><a class="youtube" href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a></li>
    </ul>

    <div style="font-weight: bold; font-family: 'Georgia', serif;">
      Copyright © 2026 <span style="font-style: italic; text-decoration: underline;">Tüm Hakları Saklıdır</span>
    </div>
  </footer>

  <script>
    const btn = document.getElementById("hamburger");
    const nav = document.getElementById("nav");

    btn?.addEventListener("click", () => nav.classList.toggle("open"));

    document.addEventListener("click", (e) => {
      if (!nav.classList.contains("open")) return;
      const within = nav.contains(e.target) || btn.contains(e.target);
      if (!within) nav.classList.remove("open");
    });
  </script>

  <script>
    document.querySelectorAll(".grid-box").forEach(box => {
      box.addEventListener("click", () => {
        const url = box.getAttribute("data-map");
        if (url) {
          window.open(url, "_blank");
        }
      });
    });
  </script>

  <script>
    function applyShineDelays() {
      if (window.innerWidth <= 860) {
        const boxes = document.querySelectorAll('.grid-box');
        boxes.forEach((box, i) => {
          const delay = (i * (5 / boxes.length)).toFixed(2);
          box.style.setProperty('--shine-delay', delay + 's');
        });
      }
    }
    applyShineDelays();
    window.addEventListener('resize', applyShineDelays);
  </script>

  <script>
    function escapeHtml(text) {
      return text
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
    }

    function buildTypingHtml(title, lines, typedCount) {
      const fullText = title + "\n" + lines.join("\n");
      const visibleText = fullText.slice(0, typedCount);

      let titlePart = "";
      let bodyPart = "";

      if (visibleText.length <= title.length) {
        titlePart = visibleText;
      } else {
        titlePart = title;
        bodyPart = visibleText.slice(title.length + 1);
      }

      const bodyHtml = bodyPart
        .split("\n")
        .filter(Boolean)
        .join("<br>");

      return `
        <strong>${escapeHtml(titlePart)}</strong>
        ${bodyHtml}
      `;
    }

    function typeBox(box, delay = 0) {
      const target = box.querySelector(".type-target");
      const title = box.dataset.title || "";
      const lines = (box.dataset.lines || "").split("|");
      const fullText = title + "\n" + lines.join("\n");

      let i = 0;
      box.classList.add("typing-active");
      target.innerHTML = `<strong></strong><span class="type-caret">|</span>`;

      setTimeout(() => {
        const timer = setInterval(() => {
          i++;
          target.innerHTML = buildTypingHtml(title, lines, i) + `<span class="type-caret">|</span>`;

          if (i >= fullText.length) {
            clearInterval(timer);
            setTimeout(() => {
              target.innerHTML = buildTypingHtml(title, lines, fullText.length);
              box.classList.remove("typing-active");
            }, 500);
          }
        }, 18);
      }, delay);
    }

    function startTypingSequence() {
      const boxes = document.querySelectorAll(".grid-box");
      boxes.forEach((box, index) => {
        typeBox(box, index * 250);
      });
    }

    window.addEventListener("load", startTypingSequence);
  </script>
</body>
</html>