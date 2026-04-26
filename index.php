<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

// Slider
$slides = db()->query(
    "SELECT title, image, image_mobile, link_url FROM slider WHERE is_active = 1 ORDER BY sort_order, id"
)->fetchAll();

// Menu groups (4 ana kategori)
$groups = db()->query(
    "SELECT id, code, label, icon, page_slug FROM menu_groups WHERE is_active = 1 ORDER BY sort_order, id"
)->fetchAll();

// Menu promo cards (her grup için popup içeriği)
$promo_rows = db()->query(
    "SELECT group_id, title, image, image_mobile, tab_code FROM menu_promo_cards
     WHERE is_active = 1 ORDER BY group_id, sort_order, id"
)->fetchAll();
$promos_by_group = [];
foreach ($promo_rows as $p) {
    $promos_by_group[$p['group_id']][] = $p;
}

$page_slug  = 'home';
$page_title = setting('site_name', SITE_NAME);
$page_desc  = setting('site_tagline', '');
$extra_css  = "
html,body{max-width:100%;overflow-x:hidden}
body{overflow:hidden;display:flex;flex-direction:column;height:100vh;height:100dvh}
.topbar{flex-shrink:0}
.hero{position:relative;flex:1 1 0;min-height:0;background:#000;overflow:hidden}
.footer{flex-shrink:0}
.hero-slider{position:relative;width:100%;height:100%;overflow:hidden}
.hero-slide{position:absolute;inset:0;opacity:0;transition:opacity .8s}
.hero-slide.active{opacity:1}
.hero-slide picture,.hero-slide img{width:100%;height:100%;display:block;object-fit:cover}
.hero-nav{position:absolute;inset:0;pointer-events:none;z-index:4}
.hero-arrow{position:absolute;top:50%;transform:translateY(-50%);width:54px;height:54px;border-radius:999px;background:rgba(0,0,0,.34);border:1px solid rgba(255,255,255,.20);color:#fff;cursor:pointer;pointer-events:auto;display:flex;align-items:center;justify-content:center;font-size:20px;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);box-shadow:0 10px 26px rgba(0,0,0,.28);transition:transform .22s,background .22s,box-shadow .22s}
.hero-arrow:hover{background:rgba(0,0,0,.52);transform:translateY(-50%) scale(1.06);box-shadow:0 14px 32px rgba(0,0,0,.38)}
.hero-arrow.prev{left:20px}
.hero-arrow.next{right:20px}
.strip{position:absolute;left:0;right:0;bottom:0;background:linear-gradient(180deg,rgba(178,69,69,.05),rgba(139,45,45,.904));padding:26px 16px 18px;z-index:3;overflow:hidden}
.strip-inner{max-width:var(--max);margin:0 auto;display:flex;align-items:flex-start;justify-content:center;gap:34px;flex-wrap:wrap;padding-bottom:4px}
.icon-card{width:140px;display:flex;flex-direction:column;align-items:center;gap:10px;text-align:center;user-select:none;position:relative}
.icon-btn{width:145px;height:145px;display:grid;place-items:center;background:transparent;border:2px solid rgba(255,255,255,.95);border-radius:50%;cursor:pointer;position:relative;transition:transform .22s,background .22s,border-color .22s;overflow:hidden;padding:0}
.icon-btn:hover{transform:translateY(-2px)}
.icon-card:hover .icon-btn{transform:translateY(-4px) scale(1.10)}
.icon-btn img{position:absolute;top:50%;left:50%;width:120%;height:120%;object-fit:contain;transform:translate(-50%,-50%);display:block}
.icon-label{min-height:34px;font-weight:800;letter-spacing:.6px;font-size:12px;color:#fff;text-transform:uppercase;text-shadow:0 2px 10px rgba(0,0,0,.35);line-height:1.2;text-align:center}
.fixmenu-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);display:none;align-items:center;justify-content:center;z-index:9999;padding:20px}
.fixmenu-overlay.active{display:flex}
.fixmenu-panel{background:#fff;border-radius:18px;padding:24px;max-width:1000px;width:100%;max-height:92vh;overflow:hidden;box-shadow:var(--shadow)}
.fixmenu-grid{display:grid;grid-template-columns:repeat(3,minmax(220px,320px));justify-content:center;gap:16px;align-items:stretch}
.fix-item{display:flex;flex-direction:column;border-radius:14px;overflow:hidden;background:#f6f6f6;transition:transform .2s}
.fix-item:hover{transform:translateY(-3px)}
.fix-item img{width:100%;height:320px;object-fit:cover;display:block}
.fix-caption{padding:14px;font-weight:700;text-align:center;font-size:14px;color:var(--ink)}
@media(max-width:860px){
  /* Mobile: tek-sayfa kilitlenmesini iptal et, doğal akış kullan */
  body{overflow:auto !important;display:block !important;height:auto !important}
  .hero{flex:none !important;height:auto !important;min-height:auto !important;overflow:visible !important}
  .hero-slider{height:60vh;min-height:380px}
  .hero-arrow{width:42px;height:42px;font-size:14px}
  /* Strip artık hero'nun altında normal akışta - butonlar kesilmez */
  .strip{position:static !important;padding:24px 12px 20px;background:linear-gradient(180deg,#8b2d2d,#6b1f1f)}
  .strip-inner{gap:18px;flex-wrap:wrap}
  .icon-card{width:auto}
  .icon-btn{width:clamp(72px,20vw,108px);height:clamp(72px,20vw,108px)}
  .icon-label{font-size:11px;color:#fff}
  .fixmenu-panel{max-height:90vh;overflow-y:auto}
  .fixmenu-grid{grid-template-columns:repeat(2,minmax(0,1fr)) !important;gap:12px}
  #croustyOverlay .fixmenu-grid,#tatliOverlay .fixmenu-grid{grid-template-columns:repeat(2,minmax(0,1fr)) !important}
  .fix-item img{height:160px}
  .fix-caption{padding:10px;font-size:12px}
}
@media(max-width:480px){
  .strip-inner{gap:10px;justify-content:space-around}
  .icon-card{flex:1 1 calc(50% - 10px);max-width:140px}
  .fixmenu-grid{grid-template-columns:1fr !important}
  #croustyOverlay .fixmenu-grid,#tatliOverlay .fixmenu-grid{grid-template-columns:1fr !important}
  .fix-item img{height:200px}
}
";
require __DIR__ . '/includes/header.php';
?>

<main class="hero" role="main" aria-label="Ana görsel alanı">
  <div class="hero-slider" id="heroSlider">
    <?php if (!$slides): ?>
      <div class="hero-slide active"><img src="/static/img/yeni/slideranasayfa.png" alt=""></div>
    <?php endif; ?>
    <?php foreach ($slides as $i => $s): ?>
      <div class="hero-slide<?= $i === 0 ? ' active' : '' ?>">
        <picture>
          <?php if ($s['image_mobile']): ?>
            <source media="(max-width: 860px)" srcset="<?= e(asset($s['image_mobile'])) ?>">
          <?php endif; ?>
          <img src="<?= e(asset($s['image'])) ?>" alt="<?= e($s['title'] ?? '') ?>">
        </picture>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (count($slides) > 1): ?>
  <div class="hero-nav" aria-label="Slider kontrol">
    <button class="hero-arrow prev" id="heroPrev" aria-label="Önceki"><i class="fa-solid fa-chevron-left"></i></button>
    <button class="hero-arrow next" id="heroNext" aria-label="Sonraki"><i class="fa-solid fa-chevron-right"></i></button>
  </div>
  <?php endif; ?>

  <section class="strip" aria-label="Hızlı menü">
    <div class="strip-inner">
      <?php foreach ($groups as $g): ?>
        <div class="icon-card">
          <button class="icon-btn" data-group="<?= e($g['code']) ?>" type="button">
            <img src="<?= e(asset($g['icon'])) ?>" alt="<?= e($g['label']) ?>">
          </button>
          <div class="icon-label"><?= e($g['label']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php foreach ($groups as $g): $promos = $promos_by_group[$g['id']] ?? []; ?>
    <div class="fixmenu-overlay" id="overlay-<?= e($g['code']) ?>">
      <div class="fixmenu-panel">
        <div class="fixmenu-grid" style="<?= count($promos) <= 2 ? 'grid-template-columns:repeat(2,minmax(220px,320px))' : '' ?>">
          <?php foreach ($promos as $p): ?>
            <a href="<?= e($g['page_slug']) ?>.php?tab=<?= e($p['tab_code']) ?>" class="fix-item">
              <picture>
                <?php if ($p['image_mobile']): ?>
                  <source media="(max-width: 860px)" srcset="<?= e(asset($p['image_mobile'])) ?>">
                <?php endif; ?>
                <img src="<?= e(asset($p['image'])) ?>" alt="<?= e($p['title']) ?>">
              </picture>
              <span class="fix-caption"><?= e($p['title']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</main>

<script>
// Hero slider
const slides=document.querySelectorAll(".hero-slide");
let cur=0,timer=null;
function show(i){slides.forEach((s,n)=>s.classList.toggle("active",n===i))}
function next(){cur=(cur+1)%slides.length;show(cur)}
function prev(){cur=(cur-1+slides.length)%slides.length;show(cur)}
function start(){stop();if(slides.length>1)timer=setInterval(next,9000)}
function stop(){if(timer){clearInterval(timer);timer=null}}
document.getElementById("heroNext")?.addEventListener("click",()=>{next();start()});
document.getElementById("heroPrev")?.addEventListener("click",()=>{prev();start()});
start();

// Popups
document.querySelectorAll(".icon-btn[data-group]").forEach(btn=>{
  btn.addEventListener("click",()=>{
    const g=btn.dataset.group;
    document.getElementById("overlay-"+g)?.classList.add("active");
  });
});
document.querySelectorAll(".fixmenu-overlay").forEach(o=>{
  o.addEventListener("click",e=>{if(e.target===o)o.classList.remove("active")});
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
