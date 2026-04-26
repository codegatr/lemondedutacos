<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$branches = db()->query(
    "SELECT title, city, district, address, phone, map_url, work_hours
     FROM branches WHERE is_active = 1 ORDER BY sort_order, id"
)->fetchAll();

$page_slug  = 'subeler';
$page_title = 'Şubelerimiz';
$page_desc  = setting('site_name', SITE_NAME) . ' şube adresleri';
$extra_css = "
.subeler-wrap{max-width:var(--max);margin:24px auto;padding:0 18px 40px}
.subeler-wrap h1{color:var(--brand);font-size:24px;margin-bottom:6px}
.subeler-wrap .lead{color:var(--muted);margin-bottom:22px;font-size:14px}
.branch-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px}
.branch{background:#fff;border:1px solid #eef2f7;border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:10px;transition:.2s;text-decoration:none;color:inherit}
.branch:hover{transform:translateY(-3px);box-shadow:var(--shadow);border-color:var(--brand)}
.branch .pin{width:36px;height:36px;border-radius:50%;background:var(--brand);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px}
.branch strong{color:var(--brand);font-size:15px;line-height:1.3;display:block}
.branch .addr{font-size:13px;color:var(--muted);line-height:1.5}
.branch .hint{font-size:12px;color:var(--brand);font-weight:600;margin-top:auto;display:flex;align-items:center;gap:6px}
.empty{text-align:center;padding:60px 20px;color:var(--muted);font-size:14px}
";
require __DIR__ . '/includes/header.php';
?>

<main class="subeler-wrap">
  <h1>Şubelerimiz</h1>
  <p class="lead"><?= count($branches) ?> şubeyle Türkiye'nin dört bir yanında French tacos lezzetini sunuyoruz.</p>

  <?php if (!$branches): ?>
    <div class="empty">Henüz şube eklenmemiş.</div>
  <?php else: ?>
    <div class="branch-grid">
      <?php foreach ($branches as $b): ?>
        <a class="branch" href="<?= e($b['map_url'] ?: '#') ?>" target="_blank" rel="noopener">
          <div class="pin"><i class="fa-solid fa-location-dot"></i></div>
          <div>
            <strong><?= e($b['title']) ?></strong>
          </div>
          <?php if ($b['address']): ?>
            <div class="addr"><?= nl2br_safe($b['address']) ?></div>
          <?php endif; ?>
          <?php if ($b['phone']): ?>
            <div class="addr"><i class="fa-solid fa-phone"></i> <?= e($b['phone']) ?></div>
          <?php endif; ?>
          <?php if ($b['work_hours']): ?>
            <div class="addr"><i class="fa-regular fa-clock"></i> <?= e($b['work_hours']) ?></div>
          <?php endif; ?>
          <div class="hint">Haritada Aç <i class="fa-solid fa-arrow-right"></i></div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
