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
body{overflow:hidden}
.hero{position:relative;flex:1 1 0;min-height:0;height:calc(100vh - 80px)}
.hero-slider{position:relative;width:100%;height:100%;overflow:hidden}
.hero-slide{position:absolute;inset:0;opacity:0;transition:opacity .8s}
.hero-slide.active{opacity:1}
.hero-slide picture,.hero-slide img{width:100%;height:100%;display:block;object-fit:cover}
.hero-nav{position:absolute;inset:0;pointer-events:none}
.hero-arrow{position:absolute;top:50%;transform:translateY(-50%);width:50px;height:50px;border-radius:50%;background:rgba(255,255,255,.85);border:0;cursor:pointer;pointer-events:auto;display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--ink);box-shadow:var(--shadow);transition:.2s}
.hero-arrow:hover{background:#fff}
.hero-arrow.prev{left:20px}
.hero-arrow.next{right:20px}
.strip{position:absolute;bottom:0;left:0;right:0;padding:18px 16px;background:linear-gradient(180deg,rgba(115,139,128,.03),#0f3f2f)}
.strip-inner{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;max-width:var(--max);margin:0 auto;padding:0 8px}
.icon-card{display:flex;flex-direction:column;align-items:center;gap:8px}
.icon-btn{width:clamp(80px,11vw,120px);height:clamp(80px,11vw,120px);border-radius:50%;border:2px solid rgba(255,255,255,.95);background:transparent;display:flex;align-items:center;justify-content:center;overflow:hidden;padding:0;cursor:pointer}
.icon-btn img{width:92%;height:92%;object-fit:contain}
.icon-label{font-size:11px;font-weight:700;color:#fff;letter-spacing:.4px;text-align:center;line-height:1.2}
.fixmenu-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);display:none;align-items:center;justify-content:center;z-index:9999;padding:20px}
.fixmenu-overlay.active{display:flex}
.fixmenu-panel{background:#fff;border-radius:18px;padding:24px;max-width:1000px;width:100%;max-height:92vh;overflow:hidden;box-shadow:var(--shadow)}
.fixmenu-grid{display:grid;grid-template-columns:repeat(3,minmax(220px,320px));justify-content:center;gap:16px;align-items:stretch}
.fix-item{display:flex;flex-direction:column;border-radius:14px;overflow:hidden;background:#f6f6f6;transition:transform .2s}
.fix-item:hover{transform:translateY(-3px)}
.fix-item img{width:100%;height:320px;object-fit:cover;display:block}
.fix-caption{padding:14px;font-weight:700;text-align:center;font-size:14px;color:var(--ink)}
@media(max-width:860px){
  body{overflow:auto}
  .hero{height:auto}
  .hero-slider{height:60vh;min-height:380px}
  .hero-arrow{width:42px;height:42px}
  .strip{position:static;padding:14px 8px}
  .icon-btn{width:clamp(72px,20vw,108px);height:clamp(72px,20vw,108px)}
  .fixmenu-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
  .fix-item img{height:180px}
  .fix-caption{padding:10px;font-size:11px}
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
