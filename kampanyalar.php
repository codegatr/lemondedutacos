<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$campaigns = db()->query(
    "SELECT title, image, image_mobile, link_url FROM campaigns
     WHERE is_active = 1
       AND (starts_on IS NULL OR starts_on <= CURDATE())
       AND (ends_on   IS NULL OR ends_on   >= CURDATE())
     ORDER BY sort_order, id"
)->fetchAll();
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Le Monde Du Tacos – Le Goût Authentique du French Tacos - Kampanyalar</title>

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
      width:18px;
      height:2px;
      background:#111827;
      position:relative;
    }

    .hamburger span::before,
    .hamburger span::after{
      content:"";
      position:absolute;
      left:0;
      width:18px;
      height:2px;
      background:#111827;
    }

    .hamburger span::before{ top:-6px; }
    .hamburger span::after{ top:6px; }

    /* ======= HERO / SLIDER ======= */
    .hero{
      position:relative;
      min-height: calc(100vh - 164px);
      background:#000;
      transform:none;
      transform-origin:center;
      overflow:hidden;
      z-index:99;
    }

    .hero-slider{
      position:absolute;
      inset:0;
      z-index:0;
    }

    .hero-slide{
      position:absolute;
      inset:0;
      opacity:0;
      transition: opacity 1.2s ease-in-out;
      will-change: opacity;
      overflow:hidden;
    }

    .hero-slide.active{
      opacity:1;
      z-index:1;
    }

    .hero-slide picture{
      display:block;
      width:100%;
      height:100%;
    }

    .hero-slide img{
      width:100%;
      height:100%;
      object-fit:cover;
      object-position:center center;
      display:block;
      pointer-events:none;
      user-select:none;
      -webkit-user-drag:none;
    }

    .hero::after{
      content:"";
      position:absolute;
      inset:0;
      z-index:2;
      pointer-events:none;
    }

    .hero::before{
      content:"";
      position:absolute;
      top:0;
      left:0;
      right:0;
      height:6px;
      background: linear-gradient(90deg, rgba(139,45,45,.9), rgba(200,86,86,.8), rgba(139,45,45,.9));
      opacity:.95;
      z-index:3;
      pointer-events:none;
    }

    .slider-dots{
      position:absolute;
      left:50%;
      bottom:22px;
      transform:translateX(-50%);
      display:flex;
      gap:10px;
      z-index:4;
    }

    .slider-dot{
      width:12px;
      height:12px;
      border-radius:999px;
      background:rgba(255,255,255,.45);
      border:1px solid rgba(255,255,255,.65);
      transition:all .35s ease;
      box-shadow:0 2px 8px rgba(0,0,0,.25);
      cursor:pointer;
    }

    .slider-dot.active{
      background:#fff;
      transform:scale(1.15);
    }

    /* ======= SLIDER ARROWS ======= */
    .slider-arrow{
      position:absolute;
      top:50%;
      transform:translateY(-50%);
      z-index:5;
      width:52px;
      height:52px;
      border:none;
      border-radius:999px;
      display:flex;
      align-items:center;
      justify-content:center;
      cursor:pointer;
      background:rgba(255,255,255,.16);
      color:#fff;
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      box-shadow: 0 8px 22px rgba(0,0,0,.28);
      border:1px solid rgba(255,255,255,.28);
      transition: all .25s ease;
    }

    .slider-arrow:hover{
      background:rgba(255,255,255,.28);
      transform:translateY(-50%) scale(1.06);
    }

    .slider-arrow:active{
      transform:translateY(-50%) scale(.97);
    }

    .slider-arrow.prev{
      left:22px;
    }

    .slider-arrow.next{
      right:22px;
    }

    .slider-arrow i{
      font-size:20px;
      pointer-events:none;
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

    /* ======= MODEL-2 (Footer Social) ======= */
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
      transition:.35s ease;
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

    /* =============================================
       MOBİL — TAM EKRAN LAYOUT (≤ 860px)
    ============================================= */
@media (max-width: 940px){

  html, body{
    height: 100%;
    height: 100dvh;
    overflow-x: hidden;
    overflow-y: hidden;
    padding: 0;
    margin: 0;
  }

  body{
    display: flex;
    flex-direction: column;
  }

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
        flex:1 1 0;
        min-height:0;
        position:relative;
      }

      .slider-dots{
        bottom:16px;
        gap:8px;
      }

      .slider-dot{
        width:10px;
        height:10px;
      }

      .slider-arrow{
        width:42px;
        height:42px;
      }

      .slider-arrow.prev{
        left:12px;
      }

      .slider-arrow.next{
        right:12px;
      }

      .slider-arrow i{
        font-size:16px;
      }

      .footer{
        flex-shrink:0;
        width:100%;
        margin:0;
        padding:6px 14px 8px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        background:#fff;
        border-top:1px solid rgba(0,0,0,.06);
        font-size:10px;
        gap:8px;
      }

      .social-nav{
        gap:6px;
      }

      .social-nav a{
        width:28px;
        height:28px;
        line-height:28px;
        font-size:14px;
        border-radius:6px;
      }

      .model-2 a{
        font-size:14px;
        border-radius:6px;
      }
    }

    @media (max-width: 380px){
      .footer{
        font-size:9px;
        padding:5px 10px 6px;
      }

      .social-nav a,
      .model-2 a{
        width:24px;
        height:24px;
        line-height:24px;
        font-size:12px;
      }

      .slider-arrow{
        width:38px;
        height:38px;
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
        <a class="active" href="/kampanyalar.php">KAMPANYALAR</a>
        <a href="/franchise.php">FRANCHISE</a>
        <a href="/iletisim.php">İLETİŞİM</a>
      </nav>
    </div>
  </header>

  <main class="hero" role="main" aria-label="Kampanya görsel alanı">
    <div class="hero-slider" id="heroSlider">
      <?php if (!$campaigns): ?>
        <div class="hero-slide active"><picture><img src="/static/img/yeni/kampanya1.png" alt=""></picture></div>
      <?php endif; ?>
      <?php foreach ($campaigns as $i => $k): ?>
        <div class="hero-slide<?= $i === 0 ? ' active' : '' ?>">
          <picture>
            <?php if ($k['image_mobile']): ?>
              <source media="(max-width: 860px)" srcset="<?= e(asset($k['image_mobile'])) ?>">
            <?php endif; ?>
            <img src="<?= e(asset($k['image'])) ?>" alt="<?= e($k['title'] ?? 'Kampanya') ?>">
          </picture>
        </div>
      <?php endforeach; ?>
    </div>

    <button class="slider-arrow prev" id="prevSlide" aria-label="Önceki görsel">
      <i class="fa-solid fa-chevron-left"></i>
    </button>

    <button class="slider-arrow next" id="nextSlide" aria-label="Sonraki görsel">
      <i class="fa-solid fa-chevron-right"></i>
    </button>

    <div class="slider-dots" id="sliderDots" aria-label="Slider göstergeleri">
      <?php
        // Kampanya sayısı kadar nokta üret (boş ise 1 placeholder)
        $dotCount = max(1, count($campaigns));
        for ($i = 0; $i < $dotCount; $i++):
      ?>
        <span class="slider-dot<?= $i === 0 ? ' active' : '' ?>" data-slide="<?= $i ?>" role="button" aria-label="<?= ($i + 1) ?>. görsele git" tabindex="0"></span>
      <?php endfor; ?>
    </div>
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
    const btn = document.getElementById("hamburger");
    const nav = document.getElementById("nav");

    btn?.addEventListener("click", () => nav.classList.toggle("open"));

    document.addEventListener("click", (e) => {
      if (!nav.classList.contains("open")) return;
      const within = nav.contains(e.target) || btn.contains(e.target);
      if (!within) nav.classList.remove("open");
    });

    const slides = document.querySelectorAll(".hero-slide");
    const dots = document.querySelectorAll(".slider-dot");
    const prevBtn = document.getElementById("prevSlide");
    const nextBtn = document.getElementById("nextSlide");

    let currentSlide = 0;
    let autoSlide;

    function showSlide(index){
      currentSlide = index;

      slides.forEach((slide, i) => {
        slide.classList.toggle("active", i === index);
      });

      dots.forEach((dot, i) => {
        dot.classList.toggle("active", i === index);
      });
    }

    function nextSlide(){
      currentSlide = (currentSlide + 1) % slides.length;
      showSlide(currentSlide);
    }

    function prevSlide(){
      currentSlide = (currentSlide - 1 + slides.length) % slides.length;
      showSlide(currentSlide);
    }

    function startAutoSlide(){
      clearInterval(autoSlide);
      autoSlide = setInterval(nextSlide, 9000);
    }

    prevBtn?.addEventListener("click", () => {
      prevSlide();
      startAutoSlide();
    });

    nextBtn?.addEventListener("click", () => {
      nextSlide();
      startAutoSlide();
    });

    dots.forEach((dot, index) => {
      dot.addEventListener("click", () => {
        showSlide(index);
        startAutoSlide();
      });
    });

    startAutoSlide();
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