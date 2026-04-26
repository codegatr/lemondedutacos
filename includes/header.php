<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$page_slug   = $page_slug   ?? 'home';
$page_title  = $page_title  ?? setting('site_name', SITE_NAME);
$page_desc   = $page_desc   ?? setting('site_tagline', '');
$body_class  = $body_class  ?? '';
$extra_css   = $extra_css   ?? '';
$site_logo   = setting('site_logo', '/static/img/logos/LMD LOGOArtboard1.png');

$nav = [
    ['home',       'index.php',       'ANASAYFA'],
    ['kurumsal',   'kurumsal.php',    'KURUMSAL'],
    ['subeler',    'subeler.php',     'ŞUBELER'],
    ['kampanyalar','kampanyalar.php', 'KAMPANYALAR'],
    ['franchise',  'franchise.php',   'FRANCHISE'],
    ['iletisim',   'iletisim.php',    'İLETİŞİM'],
];
?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($page_title) ?> – <?= e(setting('site_name', SITE_NAME)) ?></title>
<?php if ($page_desc): ?>
<meta name="description" content="<?= e($page_desc) ?>">
<?php endif; ?>
<link rel="icon" href="/favicon.ico">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/static/fonts/retrim/stylesheet.css">
<style>
:root{--brand:#3A5F0B;--brand2:#b24545;--ink:#1f2937;--muted:#6b7280;--bg:#ffffff;--shadow:0 10px 30px rgba(0,0,0,.18);--max:1180px}
*{box-sizing:border-box}
body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:var(--ink);background:var(--bg)}
a{color:inherit;text-decoration:none}
button{font:inherit}
.topbar{position:sticky;top:0;z-index:999;background:#fff}
.topbar-inner{max-width:var(--max);margin:0 auto;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.brand{display:flex;align-items:center;gap:14px;min-width:240px}
.logo-wrapper{position:relative;width:60px;height:60px;flex:0 0 60px}
.brand-logo{position:absolute;top:-30px;left:50%;transform:translateX(-50%);height:130px;width:auto;pointer-events:none}
.brand-text{margin-top:20px;display:flex;flex-direction:column;justify-content:center}
.brand .logo{font-size:24px;font-weight:700;color:var(--brand);letter-spacing:.5px}
.nav{display:flex;gap:6px;flex-wrap:wrap}
.nav a{padding:10px 14px;border-radius:8px;font-weight:600;font-size:13px;letter-spacing:.5px;color:var(--ink);transition:.2s}
.nav a:hover,.nav a.active{background:var(--brand);color:#fff}
.hamburger{display:none;width:42px;height:42px;border:0;background:transparent;cursor:pointer;align-items:center;justify-content:center;border-radius:8px}
.hamburger span,.hamburger span:before,.hamburger span:after{content:'';display:block;width:22px;height:2px;background:var(--ink);position:relative}
.hamburger span:before{position:absolute;top:-7px}
.hamburger span:after{position:absolute;top:7px}
.footer{padding:18px 24px;background:#fff;border-top:1px solid rgba(0,0,0,.06);display:flex;align-items:center;justify-content:space-between;font-size:12px;gap:12px;flex-wrap:wrap}
.social-nav{list-style:none;margin:0;padding:0;display:flex;gap:8px}
.social-nav a{display:inline-flex;width:32px;height:32px;align-items:center;justify-content:center;border-radius:8px;background:#f3f4f6;color:var(--ink);transition:.2s}
.social-nav a:hover{background:var(--brand);color:#fff}
.flash{padding:12px 16px;border-radius:8px;margin:14px auto;max-width:900px;font-size:14px;font-weight:500}
.flash-success{background:#d1fae5;border:1px solid #6ee7b7;color:#065f46}
.flash-error{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b}
.flash-info{background:#dbeafe;border:1px solid #93c5fd;color:#1e3a8a}
.flash-warning{background:#fef3c7;border:1px solid #fcd34d;color:#92400e}
@media(max-width:860px){
  .logo-wrapper{width:40px;height:40px;margin-left:30px;flex:0 0 40px}
  .brand-logo{height:90px;top:-16px;transform:translateX(-70%)}
  .brand .logo{font-size:20px}
  .brand-text{margin-top:12px}
  .brand{min-width:auto}
  .hamburger{display:flex}
  .nav{position:absolute;top:64px;right:12px;left:12px;background:#fff;border:1px solid rgba(0,0,0,.10);border-radius:14px;box-shadow:var(--shadow);padding:10px;display:none;flex-direction:column;align-items:stretch;gap:6px;z-index:9998}
  .nav.open{display:flex}
  .nav a{padding:12px}
}
<?= $extra_css ?>
</style>
</head>
<body class="<?= e($body_class) ?>">
<header class="topbar">
  <div class="topbar-inner">
    <a class="brand" href="index.php">
      <div class="logo-wrapper">
        <img class="brand-logo" src="<?= e(asset($site_logo)) ?>" alt="<?= e(setting('site_name', SITE_NAME)) ?>">
      </div>
      <div class="brand-text">
        <div class="logo" style="font-style:italic"><?= e(setting('site_name', SITE_NAME)) ?></div>
      </div>
    </a>
    <button class="hamburger" id="hamburger" aria-label="Menü"><span></span></button>
    <nav class="nav" id="nav">
      <?php foreach ($nav as [$slug, $href, $label]): ?>
        <a class="<?= $page_slug === $slug ? 'active' : '' ?>" href="<?= e($href) ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
</header>
<?= flash_render() ?>
