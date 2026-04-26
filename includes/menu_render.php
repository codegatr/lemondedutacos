<?php
declare(strict_types=1);

/**
 * Menü sayfaları için ortak şablon.
 * $group_code değişkeni dahil eden sayfa tarafından tanımlanmalıdır:
 *   tacos / bun / burger / tatli
 */

require_once __DIR__ . '/functions.php';

if (!isset($group_code)) {
    http_response_code(500);
    die('group_code eksik.');
}

$stmt = db()->prepare("SELECT id, code, label, page_slug FROM menu_groups WHERE code = ? AND is_active = 1");
$stmt->execute([$group_code]);
$group = $stmt->fetch();
if (!$group) {
    http_response_code(404);
    require __DIR__ . '/../404.php';
    exit;
}

$stmt = db()->prepare(
    "SELECT id, code, title FROM menu_categories
     WHERE group_id = ? AND is_active = 1 ORDER BY sort_order, id"
);
$stmt->execute([$group['id']]);
$categories = $stmt->fetchAll();

if (!$categories) {
    $items_by_cat = [];
} else {
    $cat_ids = array_column($categories, 'id');
    $place = implode(',', array_fill(0, count($cat_ids), '?'));
    $stmt = db()->prepare(
        "SELECT id, category_id, title, description, price, image
         FROM menu_items WHERE category_id IN ($place) AND is_active = 1
         ORDER BY category_id, sort_order, id"
    );
    $stmt->execute($cat_ids);
    $items = $stmt->fetchAll();
    $items_by_cat = [];
    foreach ($items as $it) {
        $items_by_cat[$it['category_id']][] = $it;
    }
}

$active_tab = $_GET['tab'] ?? ($categories[0]['code'] ?? '');

$page_slug  = $group['page_slug'];
$page_title = $group['label'] . ' Menüsü';
$page_desc  = $group['label'] . ' menüsü - ' . setting('site_name', SITE_NAME);
$extra_css = "
body{background:#f6f6f6}
.menu-wrap{max-width:var(--max);margin:24px auto;padding:0 18px 40px}
.menu-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px}
.menu-head h1{font-size:24px;color:var(--brand);margin:0}
.tab-bar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;border-bottom:1px solid #e5e7eb}
.tab-btn{background:transparent;border:0;padding:12px 18px;font-size:14px;font-weight:700;color:var(--muted);cursor:pointer;border-bottom:3px solid transparent;transition:.2s}
.tab-btn:hover{color:var(--brand)}
.tab-btn.active{color:var(--brand);border-bottom-color:var(--brand)}
.tab-panel{display:none}
.tab-panel.active{display:block}
.menu-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.menu-item{background:#fff;border-radius:14px;overflow:hidden;display:flex;flex-direction:row;box-shadow:0 4px 14px rgba(0,0,0,.04);transition:transform .2s,box-shadow .2s}
.menu-item:hover{transform:translateY(-3px);box-shadow:0 10px 28px rgba(0,0,0,.08)}
.item-info{flex:1;padding:16px 14px;display:flex;flex-direction:column;justify-content:center;gap:6px}
.item-title{font-size:16px;color:var(--ink);margin:0;font-weight:700}
.item-price{font-size:13px;color:var(--brand);font-weight:700}
.item-desc{font-size:12.5px;color:var(--muted);margin:0;line-height:1.45}
.item-media{position:relative;width:130px;flex-shrink:0;background:#fafafa;display:flex;align-items:center;justify-content:center}
.item-media img{width:100%;height:100%;object-fit:cover;display:block}
.empty{text-align:center;padding:60px 20px;color:var(--muted);font-size:14px}
@media(max-width:520px){
  .item-media{width:110px}
  .item-title{font-size:15px}
  .item-desc{font-size:12px}
}
";
require __DIR__ . '/header.php';
?>

<main class="menu-wrap">
  <div class="menu-head">
    <h1><?= e($group['label']) ?></h1>
    <a href="index.php" style="font-size:13px;color:var(--brand);font-weight:600">← Anasayfa</a>
  </div>

  <?php if (count($categories) > 1): ?>
    <div class="tab-bar" role="tablist">
      <?php foreach ($categories as $c): ?>
        <button class="tab-btn<?= $c['code'] === $active_tab ? ' active' : '' ?>" data-tab="<?= e($c['code']) ?>" role="tab"><?= e($c['title']) ?></button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php foreach ($categories as $c): $rows = $items_by_cat[$c['id']] ?? []; ?>
    <section class="tab-panel<?= $c['code'] === $active_tab ? ' active' : '' ?>" id="tab-<?= e($c['code']) ?>">
      <?php if (!$rows): ?>
        <div class="empty">Bu kategoride henüz ürün bulunmuyor.</div>
      <?php else: ?>
        <div class="menu-grid">
          <?php foreach ($rows as $it): ?>
            <article class="menu-item">
              <div class="item-info">
                <h2 class="item-title"><?= e($it['title']) ?></h2>
                <?php if ($it['price']): ?><div class="item-price"><?= e($it['price']) ?></div><?php endif; ?>
                <?php if ($it['description']): ?><p class="item-desc"><?= e($it['description']) ?></p><?php endif; ?>
              </div>
              <?php if ($it['image']): ?>
                <div class="item-media">
                  <img src="<?= e(asset($it['image'])) ?>" alt="<?= e($it['title']) ?>" loading="lazy">
                </div>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>
</main>

<script>
document.querySelectorAll(".tab-btn").forEach(btn=>{
  btn.addEventListener("click",()=>{
    const t=btn.dataset.tab;
    document.querySelectorAll(".tab-btn").forEach(b=>b.classList.toggle("active",b.dataset.tab===t));
    document.querySelectorAll(".tab-panel").forEach(p=>p.classList.toggle("active",p.id==="tab-"+t));
    history.replaceState(null,"","?tab="+t);
  });
});
</script>

<?php require __DIR__ . '/footer.php'; ?>
