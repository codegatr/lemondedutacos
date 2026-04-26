<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$group_code = 'burger';
$pdo = db();

$stmt = $pdo->prepare("SELECT id FROM menu_groups WHERE code = ? AND is_active = 1");
$stmt->execute([$group_code]);
$group = $stmt->fetch();

$cats = [];
$items_by_cat = [];
if ($group) {
    $stmt = $pdo->prepare("SELECT id, code, title FROM menu_categories WHERE group_id = ? AND is_active = 1 ORDER BY sort_order, id");
    $stmt->execute([$group['id']]);
    $cats = $stmt->fetchAll();

    if ($cats) {
        $cat_ids = array_column($cats, 'id');
        $place = implode(',', array_fill(0, count($cat_ids), '?'));
        $stmt = $pdo->prepare("SELECT id, category_id, title, description, price, image
            FROM menu_items WHERE category_id IN ($place) AND is_active = 1
            ORDER BY category_id, sort_order, id");
        $stmt->execute($cat_ids);
        foreach ($stmt->fetchAll() as $it) {
            $items_by_cat[$it['category_id']][] = $it;
        }
    }
}

// Render kart fonksiyonu
function render_menu_items(array $items): void {
    foreach ($items as $it) {
        $img = $it['image'] ? '/' . ltrim($it['image'], '/') : '';
        echo '<article class="menu-item">';
        echo '<div class="item-info">';
        echo '<h2 class="item-title">' . e($it['title']) . '</h2>';
        echo '<div class="item-price">' . e($it['price'] ?? '') . '</div>';
        if (!empty($it['description'])) echo '<p class="item-desc">' . e($it['description']) . '</p>';
        echo '</div>';
        echo '<div class="item-media">';
        if ($img) echo '<img src="' . e($img) . '" alt="' . e($it['title']) . '">';
        echo '<button class="add-btn" aria-label="' . e($it['title']) . ' ekle"><i class="fa-solid fa-plus"></i></button>';
        echo '</div>';
        echo '</article>';
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Le Monde Du Tacos – Burger Menüsü</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="/static/fonts/retrim/stylesheet.css">

  <style>
    :root{
      --brand:#3A5F0B;
      --brand2:#b24545;
      --ink:#1f2937;
      --muted:#6b7280;
      --bg:#f6f6f6;
      --surface:#ffffff;
      --line:#e8e8e8;
      --shadow:0 10px 30px rgba(0,0,0,.10);
      --max:1180px;
    }

    *{ box-sizing:border-box; }
    html,body{
      margin:0;
      padding:0;
      font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
      color:var(--ink);
      background:var(--bg);
    }

    a{ color:inherit; text-decoration:none; }
    button{ font:inherit; }

    body{
      min-height:100vh;
      display:flex;
      flex-direction:column;
    }

    .topbar{
      position:sticky;
      top:0;
      z-index:999;
      background:#fff;
      border-bottom:1px solid rgba(0,0,0,.05);
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
      min-width:220px;
    }

    .logo-wrapper{
      width:50px;
      height:50px;
      margin-left:80px;
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
      font-family:Georgia,serif;
      font-size:28px;
      line-height:1;
      color:#3A5F0B;
      font-weight:700;
      font-style:italic;
    }

    .nav{
      display:flex;
      align-items:center;
      gap:12px;
    }

    .nav a{
      padding:9px 12px;
      border-radius:6px;
      font-family:'Retrim',sans-serif;
      font-weight:400;
      font-size:12px;
      letter-spacing:.6px;
      text-transform:uppercase;
      color:#1f2937;
      white-space:nowrap;
      position:relative;
      overflow:hidden;
      transition:all .25s ease;
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

    /* ── Page Shell ── */
    .page{ flex:1 1 auto; width:100%; background:#f7f7f7; }

    .menu-shell{
      max-width:980px;
      margin:0 auto;
      background:#f7f7f7;
      min-height:calc(100vh - 164px);
    }

    /* ── Sticky menu head ── */
    .menu-head{
      position:sticky; top:0; z-index:20;
      background:#fff;
      border-bottom:1px solid #ebebeb;
      box-shadow:0 2px 8px rgba(0,0,0,.06);
    }

    .searchbar{
      display:flex; align-items:center; gap:10px;
      padding:14px 18px 12px;
      border-bottom:1px solid #ebebeb;
    }

    .back-btn, .more-btn{
      width:38px; height:38px; border:none; background:transparent;
      border-radius:50%; display:flex; align-items:center; justify-content:center;
      cursor:pointer; color:#333; flex:0 0 auto; transition:background .15s;
    }
    .back-btn:hover, .more-btn:hover{ background:#f0f0f0; }

    .search-input{
      flex:1; height:42px;
      border:1.5px solid #e0e0e0; background:#fafafa;
      border-radius:8px;
      display:flex; align-items:center; gap:10px;
      padding:0 14px; color:#999; min-width:0;
    }
    .search-input i{ font-size:15px; color:#bbb; flex:0 0 auto; }
    .search-input span{ display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:14px; }

    /* ── Tabs — exact Yemeksepeti style ── */
    .tabs{
      display:flex; align-items:center; justify-content:center; gap:0;
      overflow-x:auto; padding:0 18px;
      background:#fff; scrollbar-width:none;
    }
    .tabs::-webkit-scrollbar{ display:none; }

    .tab-btn{
      position:relative; border:none; background:none;
      padding:15px 18px 13px;
      font-size:14px; font-weight:600;
      color:#8c8c8c; cursor:pointer; white-space:nowrap; flex:0 0 auto;
      transition:color .15s;
    }
    .tab-btn.active{ color:#111; }
    .tab-btn.active::after{
      content:""; position:absolute;
      left:0; right:0; bottom:0;
      height:3px; border-radius:2px 2px 0 0;
      background:#ff6000;
    }

    /* ── Section header (YS style) ── */
    .section-title{
      display:block;
      font-size:13px; font-weight:600;
      color:#555; letter-spacing:.2px;
      padding:16px 16px 10px;
      border-bottom:1px solid #ebebeb;
      background:#fff;
    }

    /* ── Tab panel ── */
    .tab-panel{ display:none; padding:0; background:#f7f7f7; }
    .tab-panel.active{ display:block; }

    /* ── Menu list — 2-column grid ── */
    .menu-list{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:10px;
      padding:10px;
    }

    /* ── Product Card — image left, text right ── */
    .menu-item{
      display:flex;
      flex-direction:row;
      align-items:stretch;
      background:#fff;
      border-radius:12px;
      border:1px solid #ebebeb;
      overflow:hidden;
      cursor:pointer;
      transition:box-shadow .2s, transform .2s;
      min-height:130px;
    }
    .menu-item:hover{
      box-shadow:0 4px 16px rgba(0,0,0,.10);
      transform:translateY(-1px);
    }

    /* right side: text */
    .item-info{
      flex:1;
      padding:14px 14px 14px 12px;
      display:flex; flex-direction:column; gap:5px;
      min-width:0;
      order:2;
    }

    .item-title{
      margin:0;
      font-size:14px; font-weight:700; line-height:1.25;
      color:#1a1a1a;
      white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }

    .item-price{
      font-size:13px; font-weight:600;
      color:#1a1a1a;
      display:block;
    }

    .item-desc{
      font-size:12px; line-height:1.5;
      color:#888;
      display:-webkit-box;
      -webkit-line-clamp:3;
      -webkit-box-orient:vertical;
      overflow:hidden;
      margin:0; flex:1;
    }

    /* item-tags hidden in YS style */
    .item-tags{ display:none; }
    .item-price-pill{ display:none; }

    /* left side: image + add button */
    .item-media{
      position:relative;
      width:120px; min-width:120px;
      background:#e8e8e0;
      flex-shrink:0;
      order:1;
    }
    .item-media img{
      width:100%; height:100%;
      object-fit:cover; display:block;
    }

    .add-btn{
      position:absolute;
      right:8px; bottom:8px;
      width:32px; height:32px;
      border-radius:50%;
      border:none;
      background:#3A5F0B;
      color:#fff;
      font-size:14px;
      display:flex; align-items:center; justify-content:center;
      box-shadow:0 2px 8px rgba(0,0,0,.2);
      cursor:pointer;
      transition:background .15s, transform .15s;
    }
    .add-btn:hover{ background:#2d4a08; transform:scale(1.1); }

    /* ── Promo Bar ── */
    .sticky-promo{
      position:sticky; bottom:0; z-index:25;
      background:#3A5F0B;
      padding:12px 18px calc(12px + env(safe-area-inset-bottom));
      display:flex; align-items:center; gap:12px;
      box-shadow:0 -2px 12px rgba(0,0,0,.15);
    }
    .promo-ico{
      width:30px; height:30px; border-radius:8px;
      background:rgba(255,255,255,.2);
      color:#fff; display:flex; align-items:center; justify-content:center;
      font-size:14px; flex:0 0 auto;
    }
    .promo-text{ font-size:13px; font-weight:700; color:#fff; }

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
      width:100%;
      background:#fff;
    }

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
      text-shadow:0px 0px #d5d5d5, 1px 1px #d5d5d5, 2px 2px #d5d5d5, 3px 3px #d5d5d5, 4px 4px #d5d5d5, 5px 5px #d5d5d5, 6px 6px #d5d5d5, 7px 7px #d5d5d5, 8px 8px #d5d5d5, 9px 9px #d5d5d5, 10px 10px #d5d5d5;
    }

    .model-2 .facebook{ background:#3B579D; }
    .model-2 .facebook:hover{ color:#3B579D; }
    .model-2 .instagram{ background:#E1306C; }
    .model-2 .instagram:hover{ color:#E1306C; }
    .model-2 .twitter{ background:#111827; }
    .model-2 .twitter:hover{ color:#111827; }
    .model-2 .youtube{ background:#FF0000; }
    .model-2 .youtube:hover{ color:#FF0000; }

    @media (max-width:940px){
      html,body{ overflow-x:hidden; }
      .topbar{ flex-shrink:0; z-index:9999; }
      .topbar-inner{ padding:8px 14px; }
      .logo-wrapper{ margin-left:20px; }
      .brand-logo{ height:90px; top:-16px; }
      .brand .logo{ font-size:20px; }
      .brand-text{ margin-top:12px; }
      .brand{ min-width:auto; }
      .hamburger{ display:flex; }
      .nav{
        position:absolute; top:58px; right:12px; left:12px;
        background:#fff; border:1px solid rgba(0,0,0,.10);
        border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.18);
        padding:10px; display:none; flex-direction:column;
        align-items:stretch; gap:6px; z-index:9998;
      }
      .nav.open{ display:flex; }
      .nav a{ padding:12px; }
      .menu-shell{ max-width:none; box-shadow:none; }
      .menu-list{ grid-template-columns:1fr 1fr; gap:8px; padding:8px; }
      .item-media{ width:100px; min-width:100px; }
      .item-title{ font-size:13px; }
      .item-price{ font-size:12px; }
      .item-desc{ font-size:11px; -webkit-line-clamp:2; }
      .add-btn{ width:28px; height:28px; font-size:12px; right:6px; bottom:6px; }
      .footer{ flex-shrink:0; width:100%; margin:0; padding:6px 14px 8px; display:flex; align-items:center; justify-content:space-between; background:#fff; border-top:1px solid rgba(0,0,0,.06); font-size:10px; gap:8px; }
      .social-nav{ gap:6px; }
      .social-nav a{ width:28px; height:28px; line-height:28px; font-size:14px; border-radius:6px; }
      .model-2 a{ font-size:14px; border-radius:6px; }
    }

    @media (max-width:860px){
      .logo-wrapper{ width:40px !important; height:40px !important; margin-left:30px !important; position:relative !important; flex:0 0 40px !important; }
      .brand-logo{ position:absolute !important; height:90px !important; width:auto !important; left:50% !important; top:-16px !important; transform:translateX(-70%) !important; pointer-events:none !important; }
    }

    @media (max-width:540px){
      .menu-list{ grid-template-columns:1fr; gap:8px; padding:8px; }
      .item-media{ width:110px; min-width:110px; }
      .item-info{ padding:12px 10px 12px 12px; }
      .tab-btn{ font-size:13px; padding:13px 14px; }
    }

    @media (max-width:380px){
      .tab-btn{ font-size:12px; padding:12px 10px; }
      .footer{ font-size:9px; padding:5px 10px 6px; }
      .social-nav a,.model-2 a{ width:24px; height:24px; line-height:24px; font-size:12px; }
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

.search-input input{
  flex:1;
  min-width:0;
  border:none;
  outline:none;
  background:transparent;
  color:#333;
  font-size:14px;
  font-family:inherit;
}

.search-input input::placeholder{
  color:#999;
}

.search-input:focus-within{
  border-color:#3A5F0B;
  background:#fff;
  box-shadow:0 0 0 3px rgba(58,95,11,.12);
}

.menu-item.search-hidden{
  display:none !important;
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
          <div class="logo">Le Monde Du Tacos</div>
        </div>
      </a>

      <button class="hamburger" id="hamburger" aria-label="Menüyü aç/kapat">
        <span></span>
      </button>

      <nav class="nav" id="nav">
        <a href="/index.php">ANASAYFA</a>
        <a href="/hakkimizda.php">KURUMSAL</a>
        <a href="/subeler.php">ŞUBELER</a>
        <a href="/kampanyalar.php" class="active">KAMPANYALAR</a>
        <a href="/franchise.php">FRANCHISE</a>
        <a href="/iletisim.php">İLETİŞİM</a>
      </nav>
    </div>
  </header>

  <main class="page">
    <section class="menu-shell">
      <div class="menu-head">
        <div class="searchbar">
          <button class="back-btn" onclick="history.back()" aria-label="Geri">
            <i class="fa-solid fa-arrow-left"></i>
          </button>

<label class="search-input" for="menuSearch">
  <i class="fa-solid fa-magnifying-glass"></i>
  <input
    type="search"
    id="menuSearch"
    placeholder="Ürün adı veya içeriğine göre ara..."
    aria-label="Menüde ara"
    autocomplete="off"
    spellcheck="false"
  >
</label>

          <button class="more-btn" aria-label="Diğer seçenekler">
            <i class="fa-solid fa-ellipsis-vertical"></i>
          </button>
        </div>

        <div class="tabs" role="tablist" aria-label="Burger kategorileri">
          <button class="tab-btn active" role="tab" aria-selected="true" data-tab="gurme-burger">Gurme Burger</button>
          <button class="tab-btn" role="tab" aria-selected="false" data-tab="cocuk-menuler">Çocuk Menüler</button>
        </div>
      </div>

      <!-- GURME BURGER — single column -->
      <section class="tab-panel active" id="tab-gurme-burger">
        <h1 class="section-title">Gurme Burger</h1>
        <div class="menu-list" style="grid-template-columns:1fr;">

          
<?php
$cat_id_for_gurme_burger = null;
foreach ($cats as $c) { if ($c["code"] === "gurme-burger") { $cat_id_for_gurme_burger = $c["id"]; break; } }
if ($cat_id_for_gurme_burger && !empty($items_by_cat[$cat_id_for_gurme_burger])) {
    render_menu_items($items_by_cat[$cat_id_for_gurme_burger]);
}
?>


        </div>
      </section>

      <!-- ÇOCUK MENÜLER — 2-column grid -->
      <section class="tab-panel" id="tab-cocuk-menuler">
        <h1 class="section-title">Çocuk Menüler</h1>
        <div class="menu-list">

          
<?php
$cat_id_for_cocuk_menuler = null;
foreach ($cats as $c) { if ($c["code"] === "cocuk-menuler") { $cat_id_for_cocuk_menuler = $c["id"]; break; } }
if ($cat_id_for_cocuk_menuler && !empty($items_by_cat[$cat_id_for_cocuk_menuler])) {
    render_menu_items($items_by_cat[$cat_id_for_cocuk_menuler]);
}
?>


        </div>
      </section>
            <div class="sticky-promo">
      </div>
    </section>
  </main>

  <footer class="footer">
    <ul class="social-nav model-2" aria-label="Sosyal medya">
      <li><a class="facebook" href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a></li>
      <li><a class="instagram" href="https://www.instagram.com/lemondedutacos__?igsh=MWIzMDRzaWw0azhkbA%3D%3D&utm_source=qr" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a></li>
      <li><a class="twitter" href="#" aria-label="Twitter/X"><i class="fa-brands fa-x-twitter"></i></a></li>
      <li><a class="youtube" href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a></li>
    </ul>
    <div style="font-weight:bold; font-family:'Georgia',serif;">Copyright © 2026 <span style="font-style:italic; text-decoration:underline;">Tüm Hakları Saklıdır</span></div>
  </footer>
<script>
  const btn = document.getElementById('hamburger');
  const nav = document.getElementById('nav');

  btn?.addEventListener('click', () => nav.classList.toggle('open'));

  document.addEventListener('click', (e) => {
    if (!nav.classList.contains('open')) return;
    const within = nav.contains(e.target) || btn.contains(e.target);
    if (!within) nav.classList.remove('open');
  });

  const tabButtons = document.querySelectorAll('.tab-btn');
  const tabPanels = document.querySelectorAll('.tab-panel');
  const searchInput = document.getElementById('menuSearch');

  const VALID_TABS = ['gurme-burger', 'cocuk-menuler'];
  let selectedTab = 'gurme-burger';

  function normalizeText(value = '') {
    return value
      .toString()
      .toLocaleLowerCase('tr-TR')
      .replace(/ç/g, 'c')
      .replace(/ğ/g, 'g')
      .replace(/ı/g, 'i')
      .replace(/ö/g, 'o')
      .replace(/ş/g, 's')
      .replace(/ü/g, 'u')
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .trim();
  }

  function activateTab(tabName, options = {}) {
    const { updateUrl = true, remember = false } = options;

    if (!VALID_TABS.includes(tabName)) return;

    if (remember) {
      selectedTab = tabName;
    }

    tabButtons.forEach(button => {
      const isActive = button.dataset.tab === tabName;
      button.classList.toggle('active', isActive);
      button.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    tabPanels.forEach(panel => {
      panel.classList.toggle('active', panel.id === `tab-${tabName}`);
    });

    if (updateUrl) {
      const url = new URL(window.location.href);
      url.searchParams.set('tab', tabName);
      window.history.replaceState({}, '', url);
    }
  }

  function getItemSearchText(item) {
    const title = item.querySelector('.item-title')?.textContent || '';
    const desc = item.querySelector('.item-desc')?.textContent || '';
    const price = item.querySelector('.item-price')?.textContent || '';
    const alt = item.querySelector('.item-media img')?.alt || '';
    const extra = item.dataset.search || '';

    return normalizeText([title, desc, price, alt, extra].join(' '));
  }

  function updatePanelSections(panel) {
    const title = panel.querySelector('.section-title');
    const list = panel.querySelector('.menu-list');
    if (!title || !list) return;

    const visibleItems = Array.from(list.querySelectorAll('.menu-item'))
      .filter(item => !item.classList.contains('search-hidden'));

    const hasVisible = visibleItems.length > 0;
    title.style.display = hasVisible ? '' : 'none';
    list.style.display = hasVisible ? '' : 'none';
  }

  function applySearch(rawValue = '') {
    const term = normalizeText(rawValue);
    const allItems = document.querySelectorAll('.menu-item');

    allItems.forEach(item => {
      const haystack = getItemSearchText(item);
      const matched = !term || haystack.includes(term);
      item.classList.toggle('search-hidden', !matched);
    });

    tabPanels.forEach(panel => updatePanelSections(panel));

    if (!term) {
      activateTab(selectedTab, { updateUrl: false, remember: false });
      return;
    }

    const firstMatchedPanel = Array.from(tabPanels).find(panel =>
      panel.querySelector('.menu-item:not(.search-hidden)')
    );

    if (firstMatchedPanel) {
      const matchedTab = firstMatchedPanel.id.replace('tab-', '');
      activateTab(matchedTab, { updateUrl: false, remember: false });
    }
  }

  tabButtons.forEach(button => {
    button.addEventListener('click', () => {
      activateTab(button.dataset.tab, { remember: true });
      if (searchInput && searchInput.value.trim()) {
        applySearch(searchInput.value);
      }
    });
  });

  const urlParams = new URLSearchParams(window.location.search);
  const initialTab = urlParams.get('tab');

  if (initialTab && VALID_TABS.includes(initialTab)) {
    activateTab(initialTab, { remember: true });
  } else {
    activateTab('gurme-burger', { remember: true });
  }

  searchInput?.addEventListener('input', (e) => {
    applySearch(e.target.value);
  });
</script>
</body>
</html>
