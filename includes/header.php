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
    ['home',       '/index.php',       'ANASAYFA'],
    ['kurumsal',   '/kurumsal.php',    'KURUMSAL'],
    ['subeler',    '/subeler.php',     'ŞUBELER'],
    ['kampanyalar','/kampanyalar.php', 'KAMPANYALAR'],
    ['franchise',  '/franchise.php',   'FRANCHISE'],
    ['iletisim',   '/iletisim.php',    'İLETİŞİM'],
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
*{box-sizing:border-box;margin:0;padding:0}
html,body{max-width:100%;overflow-x:hidden}
body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:var(--ink);background:var(--bg)}
a{color:inherit;text-decoration:none}
button{font:inherit}

/* ===== TOPBAR ===== */
.topbar{position:sticky;top:0;z-index:999;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.08)}
.topbar-inner{max-width:var(--max);margin:0 auto;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.brand{position:relative;display:flex;align-items:center;gap:2px;min-width:220px}
.logo-wrapper{width:50px;height:50px;margin-left:80px;position:relative}
.brand-logo{position:absolute;height:170px;width:auto;left:50%;transform:translateX(-70%);top:-40px;pointer-events:none}
.brand-text{margin-top:20px;display:flex;flex-direction:column;justify-content:center}
.brand .logo{font-family:Georgia,serif;font-size:28px;line-height:1;color:#3A5F0B;font-weight:700;font-style:italic}
.nav{display:flex;align-items:center;gap:12px}
.nav a{padding:9px 12px;border-radius:6px;font-family:'Retrim',sans-serif;font-weight:400;font-size:12px;letter-spacing:.6px;text-transform:uppercase;color:#1f2937;white-space:nowrap;transition:all .25s ease}
.nav a.active{background:var(--brand);color:#fff}
.nav a:not(.active):hover{background:var(--brand);color:#fff}
.hamburger{display:none;width:44px;height:40px;border:1px solid rgba(0,0,0,.12);border-radius:10px;background:#fff;align-items:center;justify-content:center;cursor:pointer;z-index:9999}
.hamburger span{display:block;width:18px;height:2px;background:#111827;position:relative}
.hamburger span::before,.hamburger span::after{content:"";position:absolute;left:0;width:18px;height:2px;background:#111827}
.hamburger span::before{top:-6px}
.hamburger span::after{top:6px}

/* ===== FOOTER ===== */
.footer{max-width:1280px;margin:0 auto;padding:22px 36px 26px;display:flex;align-items:center;justify-content:space-between;gap:48px;color:var(--muted);font-size:12px;flex-wrap:wrap}
.footer > *{flex:1 1 0;display:flex;align-items:center}
.footer > .social-nav{justify-content:flex-start}
.footer > .footer-legal{justify-content:center;align-items:center;gap:10px;font-size:12px;flex-wrap:wrap}
.footer > .footer-meta{justify-content:flex-end;flex-direction:column;align-items:flex-end;gap:2px;text-align:right}
.footer-legal a{color:var(--muted);text-decoration:none;transition:color .2s}
.footer-legal a:hover{color:var(--brand);text-decoration:underline}
.footer-legal span{color:var(--muted);opacity:.5}
.social-nav{padding:0;margin:0;list-style:none;display:flex;align-items:center;gap:10px}
.social-nav li{display:inline-block}
.social-nav a{display:inline-block;width:36px;height:36px;line-height:36px;text-align:center;color:#fff;text-decoration:none;background:#000;border-radius:8px;transition:.35s ease;overflow:hidden;font-size:18px}
.model-2 a{font-size:20px;border-radius:10px}
.model-2 a:hover{background:#fff;text-shadow:0px 0px #d5d5d5,1px 1px #d5d5d5,2px 2px #d5d5d5,3px 3px #d5d5d5,4px 4px #d5d5d5}
.model-2 .facebook{background:#3B579D}.model-2 .facebook:hover{color:#3B579D}
.model-2 .instagram{background:#E1306C}.model-2 .instagram:hover{color:#E1306C}
.model-2 .twitter{background:#111827}.model-2 .twitter:hover{color:#111827}
.model-2 .youtube{background:#FF0000}.model-2 .youtube:hover{color:#FF0000}

/* ===== FLASH ===== */
.flash{padding:12px 16px;border-radius:8px;margin:14px auto;max-width:1180px;font-size:14px;font-weight:500}
.flash-success{background:#d1fae5;border:1px solid #6ee7b7;color:#065f46}
.flash-error{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b}
.flash-info{background:#dbeafe;border:1px solid #93c5fd;color:#1e3a8a}
.flash-warning{background:#fef3c7;border:1px solid #fcd34d;color:#92400e}

@media(max-width:940px){
  .logo-wrapper{margin-left:20px}
  .brand-logo{height:90px;top:-16px}
  .brand .logo{font-size:20px}
  .brand-text{margin-top:12px}
  .brand{min-width:auto}
  .hamburger{display:flex}
  .nav{position:absolute;top:58px;right:12px;left:12px;background:#fff;border:1px solid rgba(0,0,0,.10);border-radius:14px;box-shadow:var(--shadow);padding:10px;display:none;flex-direction:column;align-items:stretch;gap:6px;z-index:9998}
  .nav.open{display:flex}
  .nav a{padding:12px}
  .footer{flex-direction:column;text-align:center;gap:12px;padding:16px;max-width:100%;flex-wrap:wrap}
  .footer > *{flex:none !important;justify-content:center !important;width:100%;white-space:normal !important}
  .footer > .footer-legal{overflow:visible;flex-wrap:wrap}
  .footer > .footer-meta{align-items:center !important;text-align:center}
}
@media(max-width:860px){
  .logo-wrapper{width:40px!important;height:40px!important;margin-left:30px!important;position:relative!important;flex:0 0 40px!important}
  .brand-logo{position:absolute!important;height:90px!important;width:auto!important;left:50%!important;top:-16px!important;transform:translateX(-70%)!important;pointer-events:none!important}
}

<?= $extra_css ?>
</style>
</head>
<body class="<?= e($body_class) ?>">
<header class="topbar">
  <div class="topbar-inner">
    <a class="brand" href="/index.php">
      <div class="logo-wrapper">
        <img class="brand-logo" src="<?= e(asset($site_logo)) ?>" alt="<?= e(setting('site_name', SITE_NAME)) ?>">
      </div>
      <div class="brand-text">
        <div class="logo"><?= e(setting('site_name', SITE_NAME)) ?></div>
      </div>
    </a>
    <button class="hamburger" id="hamburger" aria-label="Menüyü aç/kapat"><span></span></button>
    <nav class="nav" id="nav">
      <?php foreach ($nav as [$slug, $href, $label]): ?>
        <a<?= $page_slug === $slug ? ' class="active"' : '' ?> href="<?= e($href) ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
</header>
<?= flash_render() ?>
