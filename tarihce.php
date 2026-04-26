<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$timeline = db()->query(
    "SELECT year_label, title, description, image FROM timeline
     WHERE is_active = 1 ORDER BY sort_order, id"
)->fetchAll();

$page_slug  = 'kurumsal';
$page_title = 'Tarihçe';
$page_desc  = 'Le Monde Du Tacos tarihçesi';
$extra_css = "
.tl-wrap{max-width:880px;margin:24px auto;padding:0 18px 40px}
.tl-wrap h1{color:var(--brand);text-align:center;font-size:26px;margin-bottom:10px}
.tl-wrap .lead{text-align:center;color:var(--muted);margin-bottom:30px;font-size:14px;line-height:1.6}
.tl{position:relative;padding-left:32px}
.tl::before{content:'';position:absolute;top:0;bottom:0;left:8px;width:2px;background:#e5e7eb}
.tl-item{position:relative;margin-bottom:30px}
.tl-item::before{content:'';position:absolute;left:-32px;top:6px;width:18px;height:18px;border-radius:50%;background:var(--brand);border:3px solid #fff;box-shadow:0 0 0 2px var(--brand)}
.tl-year{font-size:18px;color:var(--brand);font-weight:700;margin-bottom:4px}
.tl-title{font-size:16px;font-weight:600;margin-bottom:6px}
.tl-desc{color:var(--muted);font-size:14px;line-height:1.6}
";
require __DIR__ . '/includes/header.php';
?>
<main class="tl-wrap">
  <h1>Tarihçemiz</h1>
  <p class="lead">Markamızın yolculuğu — ilk şubeden bugüne uzanan adımlarımız.</p>
  <?php if (!$timeline): ?>
    <p style="text-align:center;color:var(--muted)">Tarihçe içeriği henüz eklenmedi.</p>
  <?php else: ?>
    <div class="tl">
      <?php foreach ($timeline as $t): ?>
        <div class="tl-item">
          <div class="tl-year"><?= e($t['year_label']) ?></div>
          <div class="tl-title"><?= e($t['title']) ?></div>
          <?php if ($t['description']): ?><div class="tl-desc"><?= nl2br_safe($t['description']) ?></div><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
