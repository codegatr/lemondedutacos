<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$campaigns = db()->query(
    "SELECT title, description, image, image_mobile, link_url
     FROM campaigns
     WHERE is_active = 1
       AND (starts_on IS NULL OR starts_on <= CURDATE())
       AND (ends_on   IS NULL OR ends_on   >= CURDATE())
     ORDER BY sort_order, id DESC"
)->fetchAll();

$page_slug  = 'kampanyalar';
$page_title = 'Kampanyalar';
$page_desc  = 'Güncel kampanyalarımız';
$extra_css = "
.kmp-wrap{max-width:var(--max);margin:24px auto;padding:0 18px 40px}
.kmp-wrap h1{color:var(--brand);font-size:24px;margin-bottom:18px}
.kmp-list{display:flex;flex-direction:column;gap:16px}
.kmp-item{display:block;border-radius:14px;overflow:hidden;box-shadow:0 6px 20px rgba(0,0,0,.06);transition:.2s}
.kmp-item:hover{transform:translateY(-3px);box-shadow:0 14px 30px rgba(0,0,0,.10)}
.kmp-item img{width:100%;height:auto;display:block}
.kmp-meta{padding:14px 18px;background:#fff}
.kmp-meta h3{margin:0 0 4px;color:var(--brand);font-size:16px}
.kmp-meta p{margin:0;color:var(--muted);font-size:13px;line-height:1.5}
.empty{text-align:center;padding:60px 20px;color:var(--muted);font-size:14px;background:#fff;border-radius:14px}
";
require __DIR__ . '/includes/header.php';
?>

<main class="kmp-wrap">
  <h1>Kampanyalar</h1>

  <?php if (!$campaigns): ?>
    <div class="empty">Şu an aktif kampanya bulunmuyor. Yakında yeni kampanyalarla görüşmek üzere!</div>
  <?php else: ?>
    <div class="kmp-list">
      <?php foreach ($campaigns as $k):
        $tag = $k['link_url'] ? 'a' : 'div';
        $href = $k['link_url'] ? ' href="' . e($k['link_url']) . '"' : '';
      ?>
        <<?= $tag ?> class="kmp-item"<?= $href ?>>
          <picture>
            <?php if ($k['image_mobile']): ?>
              <source media="(max-width: 860px)" srcset="<?= e(asset($k['image_mobile'])) ?>">
            <?php endif; ?>
            <img src="<?= e(asset($k['image'])) ?>" alt="<?= e($k['title'] ?? 'Kampanya') ?>" loading="lazy">
          </picture>
          <?php if ($k['title'] || $k['description']): ?>
            <div class="kmp-meta">
              <?php if ($k['title']): ?><h3><?= e($k['title']) ?></h3><?php endif; ?>
              <?php if ($k['description']): ?><p><?= nl2br_safe($k['description']) ?></p><?php endif; ?>
            </div>
          <?php endif; ?>
        </<?= $tag ?>>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
