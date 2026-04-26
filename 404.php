<?php
declare(strict_types=1);
http_response_code(404);
require_once __DIR__ . '/includes/functions.php';
$page_slug  = '404';
$page_title = 'Sayfa Bulunamadı';
$page_desc  = '';
$extra_css  = '
.err-wrap{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;text-align:center;padding:40px 20px}
.err-wrap h1{font-size:120px;color:var(--brand);margin:0;line-height:1}
.err-wrap p{font-size:18px;color:var(--muted);margin:8px 0 24px}
.err-wrap a{display:inline-block;padding:12px 24px;background:var(--brand);color:#fff;border-radius:8px;font-weight:600}
';
require __DIR__ . '/includes/header.php';
?>
<main class="err-wrap">
  <h1>404</h1>
  <p>Aradığınız sayfa bulunamadı.</p>
  <a href="/">Anasayfaya Dön</a>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
