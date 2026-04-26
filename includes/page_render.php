<?php
declare(strict_types=1);

/**
 * Veritabanından gelen statik sayfa içeriğini render eder.
 * $slug değişkenini dahil eden sayfa belirlemelidir.
 */

require_once __DIR__ . '/functions.php';

if (!isset($slug)) {
    http_response_code(500);
    die('slug eksik.');
}

$stmt = db()->prepare("SELECT * FROM pages WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    http_response_code(404);
    require __DIR__ . '/../404.php';
    exit;
}

$page_slug  = $page_slug ?? $slug;
$page_title = $page['seo_title'] ?: $page['title'];
$page_desc  = $page['seo_desc'] ?: ($page['subtitle'] ?? '');

$extra_css = "
.pg-wrap{max-width:980px;margin:24px auto;padding:0 18px 40px}
.pg-hero{background:#fff;border-radius:14px;padding:30px 32px;margin-bottom:18px;text-align:center;box-shadow:0 6px 20px rgba(0,0,0,.04)}
.pg-hero h1{color:var(--brand);margin:0 0 8px;font-size:26px}
.pg-hero p{color:var(--muted);margin:0;font-size:14px;line-height:1.6}
.pg-hero img{max-width:100%;height:auto;border-radius:12px;margin-top:16px}
.pg-body{background:#fff;border-radius:14px;padding:30px 32px;line-height:1.7;color:#374151;box-shadow:0 6px 20px rgba(0,0,0,.04)}
.pg-body h2{color:var(--brand);font-size:20px;margin:24px 0 8px}
.pg-body h3{color:var(--brand);font-size:16px;margin:18px 0 6px}
.pg-body p{margin:0 0 14px}
.pg-body ul,.pg-body ol{padding-left:22px;margin:0 0 14px}
.pg-body img{max-width:100%;height:auto;border-radius:8px}
@media(max-width:680px){.pg-hero,.pg-body{padding:22px}}
";
require __DIR__ . '/header.php';
?>

<main class="pg-wrap">
  <header class="pg-hero">
    <h1><?= e($page['title']) ?></h1>
    <?php if ($page['subtitle']): ?><p><?= e($page['subtitle']) ?></p><?php endif; ?>
    <?php if ($page['hero_image']): ?>
      <img src="<?= e(asset($page['hero_image'])) ?>" alt="<?= e($page['title']) ?>">
    <?php endif; ?>
  </header>
  <?php if ($page['body']): ?>
    <article class="pg-body">
      <?= $page['body'] /* admin tarafından girilir, HTML kabul edilir */ ?>
    </article>
  <?php endif; ?>
  <?php if (isset($extra_content) && is_callable($extra_content)) $extra_content(); ?>
</main>

<?php require __DIR__ . '/footer.php'; ?>
