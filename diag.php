<?php
// Geçici teşhis aracı. Sonuçtan sonra SİL!
// URL: https://v2.lemondedutacos.com/diag.php

error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/html; charset=utf-8');
echo '<!doctype html><html><head><meta charset="utf-8"><title>Diag</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#111;color:#0f0;font-size:13px;line-height:1.6}';
echo '.ok{color:#0f0}.fail{color:#f55}.warn{color:#fc0}h2{color:#fff;border-bottom:1px solid #333;margin-top:20px}';
echo 'pre{background:#000;padding:10px;border:1px solid #333;overflow:auto}</style></head><body>';

function row($label, $ok, $detail = '') {
    $cls = $ok === true ? 'ok' : ($ok === false ? 'fail' : 'warn');
    $sym = $ok === true ? '✓' : ($ok === false ? '✗' : '!');
    echo "<div class='$cls'>$sym <strong>$label:</strong> " . htmlspecialchars($detail) . "</div>";
}

echo "<h2>1. PHP Ortamı</h2>";
row("PHP Sürümü", version_compare(PHP_VERSION, '8.1', '>=') ? true : false, PHP_VERSION);
row("Sapi", true, PHP_SAPI);
foreach (['pdo_mysql', 'mbstring', 'fileinfo', 'zip', 'openssl', 'session'] as $ext) {
    row("Extension: $ext", extension_loaded($ext));
}

echo "<h2>2. Dosyalar</h2>";
$files = [
    __DIR__ . '/includes/config.php',
    __DIR__ . '/includes/db.php',
    __DIR__ . '/includes/functions.php',
    __DIR__ . '/includes/schema.sql',
    __DIR__ . '/includes/seed.sql',
    __DIR__ . '/install.lock',
];
foreach ($files as $f) {
    $base = basename($f);
    if (file_exists($f)) {
        row($base, true, 'Var (' . filesize($f) . ' bayt)');
    } else {
        $required = $base !== 'install.lock';
        row($base, $required ? false : 'warn', $required ? 'YOK!' : 'Yok (kurulum henüz tamamlanmamış)');
    }
}

echo "<h2>3. Yazma İzinleri</h2>";
foreach (['/uploads', '/uploads/slider', '/uploads/menu', '/uploads/kampanya', '/uploads/cv', '/uploads/sayfa'] as $d) {
    $full = __DIR__ . $d;
    if (!is_dir($full)) {
        row($d, 'warn', 'Dizin yok');
        continue;
    }
    row($d, is_writable($full), is_writable($full) ? 'yazılabilir' : 'yazılamıyor (chmod 755)');
}

echo "<h2>4. Config Yüklenebiliyor mu?</h2>";
try {
    require_once __DIR__ . '/includes/config.php';
    row("config.php yüklendi", true);
    row("DB_HOST",  defined('DB_HOST'),  defined('DB_HOST')  ? DB_HOST  : '');
    row("DB_NAME",  defined('DB_NAME'),  defined('DB_NAME')  ? DB_NAME  : '');
    row("DB_USER",  defined('DB_USER'),  defined('DB_USER')  ? DB_USER  : '');
    row("DB_PASS",  defined('DB_PASS'),  defined('DB_PASS')  ? '(set, ' . strlen(DB_PASS) . ' karakter)' : '');
    row("SITE_URL", defined('SITE_URL'), defined('SITE_URL') ? SITE_URL : '');
    row("APP_VERSION", defined('APP_VERSION'), defined('APP_VERSION') ? APP_VERSION : '');
} catch (Throwable $e) {
    row("config.php", false, $e->getMessage());
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h2>5. Veritabanı Bağlantısı</h2>";
if (defined('DB_HOST')) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        row("PDO bağlantısı", true, "Bağlandı");

        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        row("Tablo sayısı", count($tables) >= 19, count($tables) . ' tablo');

        $expected = ['settings','admin_users','menu_groups','menu_items','branches','campaigns','jobs','contact_messages','franchise_applications','job_applications','pages','timeline'];
        $missing = array_diff($expected, $tables);
        if ($missing) {
            row("Eksik tablolar", false, implode(', ', $missing));
        } else {
            row("Temel tablolar", true, 'Tümü mevcut');

            // Test query
            try {
                $st = $pdo->query("SELECT COUNT(*) FROM settings");
                $n = $st->fetchColumn();
                row("settings tablosu", true, "$n kayıt");

                $st = $pdo->query("SELECT COUNT(*) FROM menu_items");
                $n = $st->fetchColumn();
                row("menu_items tablosu", true, "$n kayıt");

                $st = $pdo->query("SELECT COUNT(*) FROM admin_users");
                $n = $st->fetchColumn();
                row("admin_users tablosu", $n > 0, "$n kayıt");
            } catch (Throwable $e) {
                row("Tablo sorgusu", false, $e->getMessage());
            }
        }
    } catch (Throwable $e) {
        row("PDO bağlantısı", false, $e->getMessage());
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    }
}

echo "<h2>6. functions.php Yüklenebiliyor mu?</h2>";
try {
    require_once __DIR__ . '/includes/functions.php';
    row("functions.php yüklendi", true);

    // Test setting()
    try {
        $val = setting('site_name', 'DEFAULT');
        row("setting() çalışıyor", true, "site_name = '$val'");
    } catch (Throwable $e) {
        row("setting()", false, $e->getMessage());
    }
} catch (Throwable $e) {
    row("functions.php", false, $e->getMessage());
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h2>7. Sunucu</h2>";
row("Software", true, $_SERVER['SERVER_SOFTWARE'] ?? '?');
row("Document Root", true, $_SERVER['DOCUMENT_ROOT'] ?? '?');
row("Script Path",  true, __DIR__);

if (function_exists('apache_get_modules')) {
    $mods = apache_get_modules();
    row("mod_rewrite", in_array('mod_rewrite', $mods));
    row("mod_headers", in_array('mod_headers', $mods));
}

echo '<hr style="margin:30px 0;border:1px solid #333">';
echo '<p style="color:#fc0">⚠ Bu sayfa hassas bilgi içerir. Sorunu çözdükten sonra <code>diag.php</code>\'yi mutlaka SİL.</p>';
echo '</body></html>';
