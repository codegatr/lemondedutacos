<?php
declare(strict_types=1);

// Output buffering — admin sayfalarında POST sonrası header('Location:')
// redirect'i çalışsın (HTML output'u zaten verilmişse de)
if (!ob_get_level()) ob_start();

require_once __DIR__ . '/../includes/functions.php';
admin_require();

$cu = admin_user();

$nav_items = [
    ['index.php',                  'Pano',                'fa-gauge'],
    ['settings.php',               'Site Ayarları',       'fa-gear'],
    ['slider.php',                 'Hero Slider',         'fa-images'],
    ['menu.php',                   'Menü Yönetimi',       'fa-utensils'],
    ['promo-cards.php',            'Anasayfa Kartları',   'fa-grip'],
    ['branches.php',               'Şubeler',             'fa-location-dot'],
    ['campaigns.php',              'Kampanyalar',         'fa-bullhorn'],
    ['jobs.php',                   'İş İlanları',         'fa-briefcase'],
    ['applications.php',           'Başvurular & Mesajlar','fa-inbox'],
    ['pages.php',                  'Sayfa İçerikleri',    'fa-file-lines'],
    ['timeline.php',               'Tarihçe',             'fa-clock-rotate-left'],
    ['users.php',                  'Yöneticiler',         'fa-user-shield'],
    ['normalize-images.php',       'Görsel Normalize',    'fa-wand-magic-sparkles'],
    ['update.php',                 'Güncelleme',          'fa-cloud-arrow-down'],
];

$current = basename($_SERVER['PHP_SELF']);
$page_h  = $page_h ?? 'Pano';
?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($page_h) ?> – Yönetim</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" href="/favicon.ico">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f6f7f9;color:#1f2937;font-size:14px}
.app{display:flex;min-height:100vh}
.sb{width:240px;background:#1f2937;color:#e5e7eb;flex-shrink:0;display:flex;flex-direction:column}
.sb-head{padding:20px 18px;border-bottom:1px solid #374151}
.sb-head .brand{font-size:16px;font-weight:700;color:#fff;margin-bottom:2px}
.sb-head .sub{font-size:11px;color:#9ca3af}
.sb-nav{flex:1;padding:10px 0;overflow-y:auto}
.sb-nav a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:#d1d5db;text-decoration:none;font-size:13px;border-left:3px solid transparent;transition:.15s}
.sb-nav a:hover{background:#374151;color:#fff}
.sb-nav a.active{background:#374151;color:#fff;border-left-color:#3A5F0B}
.sb-nav a i{width:18px;text-align:center;font-size:13px}
.sb-foot{padding:14px 18px;border-top:1px solid #374151;font-size:12px;color:#9ca3af}
.sb-foot a{color:#fca5a5;text-decoration:none}
.main{flex:1;display:flex;flex-direction:column;min-width:0}
.tb{background:#fff;padding:14px 22px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e5e7eb}
.tb h1{font-size:18px;color:#3A5F0B}
.tb .user{display:flex;align-items:center;gap:12px;font-size:13px}
.tb .user a{color:#3A5F0B;font-weight:600;text-decoration:none}
.cnt{padding:24px;flex:1}
.card{background:#fff;border-radius:12px;padding:22px;box-shadow:0 2px 8px rgba(0,0,0,.04);margin-bottom:18px}
.card h2{font-size:16px;color:#3A5F0B;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #e5e7eb}
.btn{display:inline-block;background:#3A5F0B;color:#fff;border:0;border-radius:6px;padding:9px 16px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:.15s}
.btn:hover{background:#2c4708}
.btn-sm{padding:6px 11px;font-size:12px}
.btn-secondary{background:#6b7280}
.btn-secondary:hover{background:#4b5563}
.btn-danger{background:#dc2626}
.btn-danger:hover{background:#b91c1c}
.btn-warn{background:#d97706}
.btn-warn:hover{background:#b45309}
.btn-line{background:#fff;color:#3A5F0B;border:1px solid #3A5F0B}
.btn-line:hover{background:#3A5F0B;color:#fff}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:10px 12px;font-size:13px;border-bottom:1px solid #e5e7eb;vertical-align:middle}
th{background:#f9fafb;font-weight:700;color:#374151;font-size:12px;text-transform:uppercase;letter-spacing:.4px}
tr:hover td{background:#fafafa}
.row{margin-bottom:12px}
.row label{display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;text-transform:uppercase;letter-spacing:.4px}
.row input[type=text],.row input[type=email],.row input[type=tel],.row input[type=password],.row input[type=number],.row input[type=date],.row input[type=url],.row select,.row textarea{width:100%;padding:9px 11px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;font-family:inherit}
.row input:focus,.row select:focus,.row textarea:focus{outline:0;border-color:#3A5F0B;box-shadow:0 0 0 3px rgba(58,95,11,.12)}
.row textarea{resize:vertical;min-height:80px}
.row .help{color:#6b7280;font-size:11px;margin-top:3px}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
@media(max-width:760px){.grid-2,.grid-3,.grid-4{grid-template-columns:1fr}.sb{width:60px}.sb-head .brand,.sb-head .sub,.sb-nav a span,.sb-foot{display:none}.sb-nav a{justify-content:center;padding:14px;border-left:0;border-bottom:3px solid transparent}.sb-nav a.active{border-bottom-color:#3A5F0B}}
.flash{padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;font-weight:500}
.flash-success{background:#d1fae5;border:1px solid #6ee7b7;color:#065f46}
.flash-error{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b}
.flash-warning{background:#fef3c7;border:1px solid #fcd34d;color:#92400e}
.flash-info{background:#dbeafe;border:1px solid #93c5fd;color:#1e3a8a}
.toggle{display:inline-flex;align-items:center;gap:6px;font-size:12px}
.toggle input{width:32px;height:18px;-webkit-appearance:none;appearance:none;background:#d1d5db;border-radius:9px;position:relative;cursor:pointer;transition:.2s}
.toggle input::before{content:'';position:absolute;width:14px;height:14px;background:#fff;border-radius:50%;top:2px;left:2px;transition:.2s}
.toggle input:checked{background:#3A5F0B}
.toggle input:checked::before{left:16px}
.badge{display:inline-block;padding:3px 8px;border-radius:99px;font-size:10px;font-weight:700;letter-spacing:.4px;text-transform:uppercase}
.b-on{background:#d1fae5;color:#065f46}
.b-off{background:#fee2e2;color:#991b1b}
.b-info{background:#dbeafe;color:#1e3a8a}
.thumb{width:40px;height:40px;border-radius:6px;object-fit:cover;border:1px solid #e5e7eb}
.empty{padding:40px 20px;text-align:center;color:#6b7280;font-size:13px}
.actions{display:flex;gap:6px;flex-wrap:wrap}
.actions form{display:inline}
.pager{display:flex;gap:6px;margin-top:14px}
.pager a,.pager span{padding:6px 11px;border-radius:6px;background:#fff;border:1px solid #e5e7eb;text-decoration:none;color:#374151;font-size:12px}
.pager .active{background:#3A5F0B;color:#fff;border-color:#3A5F0B}
.metric{background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,.04)}
.metric .lbl{font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;font-weight:700}
.metric .val{font-size:28px;color:#3A5F0B;font-weight:700;margin-top:4px}
.metric .delta{font-size:11px;color:#6b7280;margin-top:2px}
</style>
</head>
<body>
<div class="app">
  <aside class="sb">
    <div class="sb-head">
      <div class="brand">Le Monde Du Tacos</div>
      <div class="sub">Yönetim Paneli</div>
    </div>
    <nav class="sb-nav">
      <?php foreach ($nav_items as [$href, $label, $icon]): ?>
        <a href="<?= e($href) ?>" class="<?= $current === $href ? 'active' : '' ?>">
          <i class="fa-solid <?= e($icon) ?>"></i>
          <span><?= e($label) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="sb-foot">
      v<?= APP_VERSION ?> &middot; <a href="logout.php">Çıkış</a>
    </div>
  </aside>

  <div class="main">
    <header class="tb">
      <h1><?= e($page_h) ?></h1>
      <div class="user">
        <span><?= e($cu['name']) ?> (<?= e($cu['role']) ?>)</span>
        <a href="/" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> Site</a>
      </div>
    </header>
    <main class="cnt">
      <?= flash_render() ?>
