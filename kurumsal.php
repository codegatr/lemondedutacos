<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$cards = db()->query(
    "SELECT title, description, icon, link_url FROM corporate_cards
     WHERE is_active = 1 ORDER BY sort_order, id"
)->fetchAll();

$page_slug  = 'kurumsal';
$page_title = 'Kurumsal';
$page_desc  = 'Le Monde Du Tacos kurumsal sayfası';
$extra_css = "
.kr-wrap{max-width:var(--max);margin:24px auto;padding:0 18px 40px}
.kr-hero{background:linear-gradient(135deg,#3A5F0B,#5e8a1a);color:#fff;border-radius:14px;padding:48px 32px;text-align:center;margin-bottom:24px}
.kr-hero h1{margin:0 0 8px;font-size:28px}
.kr-hero p{margin:0;opacity:.95;font-size:14px;line-height:1.6}
.kr-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px}
.kr-card{background:#fff;border:1px solid #eef2f7;border-radius:14px;padding:28px 24px;text-align:center;text-decoration:none;color:inherit;transition:.2s;display:flex;flex-direction:column;align-items:center;gap:10px}
.kr-card:hover{transform:translateY(-4px);border-color:var(--brand);box-shadow:var(--shadow)}
.kr-card .ico{width:56px;height:56px;border-radius:50%;background:rgba(58,95,11,.12);color:var(--brand);display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:6px}
.kr-card h3{color:var(--brand);font-size:16px;margin:0}
.kr-card p{color:var(--muted);font-size:13px;margin:0;line-height:1.5}
";
require __DIR__ . '/includes/header.php';
?>
<main class="kr-wrap">
  <div class="kr-hero">
    <h1>LMD Tacos Kurumsal</h1>
    <p>Markamızın hikayesini, üretim altyapımızı ve aramıza katılma fırsatlarını keşfedin.</p>
  </div>
  <div class="kr-grid">
    <?php foreach ($cards as $c): ?>
      <a class="kr-card" href="<?= e($c['link_url'] ?: '#') ?>">
        <?php if ($c['icon']): ?><div class="ico"><i class="fa-solid <?= e($c['icon']) ?>"></i></div><?php endif; ?>
        <h3><?= e($c['title']) ?></h3>
        <?php if ($c['description']): ?><p><?= e($c['description']) ?></p><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
