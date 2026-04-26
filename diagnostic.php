<?php
/**
 * LMD Tacos — Sistem Diagnostik
 *
 * Bu dosyayı site root'una yükleyin (örn: /home/lemonded/domains/v2.lemondedutacos.com/public_html/)
 * ve tarayıcıda açın: https://v2.lemondedutacos.com/diagnostic.php
 *
 * GÜVENLİK: Bu dosya GİZLİ BİLGİ İÇERMEZ ama yine de tanı bittikten sonra silinmesi önerilir.
 * Token koruması basit (URL parametresi). Production'da bırakmayın.
 */

// Basit koruma: ?key= parametresi ile çalışır
$KEY = 'lmdt-' . date('Ymd');  // Bugünün tarihi: lmdt-20260426
if (($_GET['key'] ?? '') !== $KEY) {
    http_response_code(403);
    echo "Erişim için: ?key={$KEY}";
    exit;
}

@set_time_limit(60);
@ini_set('display_errors', '1');

function row($k, $v, $color = null) {
    $cs = $color ? "color:$color;font-weight:bold" : "";
    echo "<tr><td style='padding:6px 12px;border-bottom:1px solid #eee'><strong>" . htmlspecialchars($k) . "</strong></td>";
    echo "<td style='padding:6px 12px;border-bottom:1px solid #eee;{$cs}'>" . $v . "</td></tr>";
}

function badge($text, $ok) {
    $bg = $ok ? '#16a34a' : '#dc2626';
    return "<span style='background:{$bg};color:white;padding:3px 10px;border-radius:12px;font-size:11px'>$text</span>";
}

$root = __DIR__;
?><!doctype html>
<html lang="tr"><head><meta charset="utf-8"><title>LMD Tacos Diagnostik</title>
<style>
body{font-family:system-ui,sans-serif;max-width:980px;margin:30px auto;padding:0 20px;color:#1f2937;background:#f9fafb}
h1{color:#3a5f0b;font-style:italic;border-bottom:2px solid #3a5f0b;padding-bottom:6px}
h2{color:#3a5f0b;margin-top:36px;font-size:18px;border-left:4px solid #3a5f0b;padding-left:10px}
table{width:100%;border-collapse:collapse;background:white;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)}
code{background:#f3f4f6;padding:2px 6px;border-radius:3px;font-size:12px}
.warn{background:#fef3c7;border-left:4px solid #d97706;padding:12px;border-radius:6px;margin:14px 0}
.err{background:#fee2e2;border-left:4px solid #dc2626;padding:12px;border-radius:6px;margin:14px 0;color:#991b1b}
.ok{background:#d1fae5;border-left:4px solid #16a34a;padding:12px;border-radius:6px;margin:14px 0;color:#065f46}
</style>
</head><body>

<h1>🩺 LMD Tacos Sistem Diagnostik</h1>
<p style="color:#6b7280;font-size:13px">Bu rapor sunucu yapılandırmasını ve görsel yükleme sorunlarını teşhis eder.</p>

<h2>1. PHP Versiyonu</h2>
<table>
<?php
$php_ver = PHP_VERSION;
$is_php8 = version_compare($php_ver, '8.0.0', '>=');
row('PHP Version', "<code>$php_ver</code> " . badge($is_php8 ? '8.0+' : 'PHP 7.x', $is_php8));
row('SAPI', php_sapi_name());
row('OS', PHP_OS);
?>
</table>
<?php if (!$is_php8): ?>
<div class="err">
<strong>⚠ KRİTİK:</strong> PHP 7.x tespit edildi. Sistem PHP 8.0+ gerektirir (<code>str_starts_with</code>, <code>match()</code>, <code>readonly</code> gibi sözdizimi kullanılıyor).
DirectAdmin → PHP Selector'dan sürümü 8.0+ yapın.
</div>
<?php endif; ?>

<h2>2. Upload Yapılandırması</h2>
<table>
<?php
$file_uploads = (bool)ini_get('file_uploads');
$upload_max = ini_get('upload_max_filesize');
$post_max = ini_get('post_max_size');
$max_file_uploads = ini_get('max_file_uploads');
$tmp_dir = sys_get_temp_dir();
$tmp_writable = is_writable($tmp_dir);
row('file_uploads', badge($file_uploads ? 'Açık' : 'KAPALI', $file_uploads));
row('upload_max_filesize', "<code>$upload_max</code>");
row('post_max_size', "<code>$post_max</code>");
row('max_file_uploads', "<code>$max_file_uploads</code>");
row('upload_tmp_dir', "<code>$tmp_dir</code> " . badge($tmp_writable ? 'Yazılabilir' : 'YAZILAMAZ', $tmp_writable));
?>
</table>

<h2>3. uploads/ Klasörü</h2>
<table>
<?php
$uploads = $root . '/uploads';
$exists = is_dir($uploads);
$writable = $exists && is_writable($uploads);
$perms = $exists ? substr(sprintf('%o', fileperms($uploads)), -4) : 'YOK';
row('Yol', "<code>$uploads</code>");
row('Mevcut mu?', badge($exists ? 'Evet' : 'HAYIR', $exists));
row('Yazılabilir mi?', badge($writable ? 'Evet' : 'HAYIR', $writable));
row('İzinler', "<code>$perms</code>");

if ($exists) {
    // Alt klasörler ve içerik
    $subs = array_filter(scandir($uploads), function ($i) use ($uploads) {
        return $i !== '.' && $i !== '..' && is_dir($uploads . '/' . $i);
    });
    row('Alt klasörler', $subs ? implode(', ', array_map('htmlspecialchars', $subs)) : '(yok)');

    // Test yazma denemesi
    $testFile = $uploads . '/_test_' . time() . '.txt';
    $writeOk = @file_put_contents($testFile, 'test');
    if ($writeOk !== false) {
        @unlink($testFile);
        row('Yazma testi', badge('BAŞARILI', true));
    } else {
        row('Yazma testi', badge('BAŞARISIZ', false));
    }
}
?>
</table>
<?php if (!$exists || !$writable): ?>
<div class="err">
<strong>⚠ uploads/ klasörü sorunu:</strong> Bu, görsel yükleme başarısızlığının ana sebebi olabilir.
FTP'den <code>/uploads/</code> klasörünü oluşturun, izinlerini <code>755</code> yapın.
</div>
<?php endif; ?>

<h2>4. Veritabanı Bağlantısı ve Görsel Path'leri</h2>
<?php
$config = $root . '/includes/config.php';
if (!file_exists($config)) {
    echo "<div class='err'>config.php bulunamadı: $config</div>";
} else {
    require_once $config;
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                       DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "<table>";
        row('DB Bağlantı', badge('BAŞARILI', true));
        row('DB Name', "<code>" . DB_NAME . "</code>");
        row('APP_VERSION', "<code>" . APP_VERSION . "</code>");
        echo "</table>";

        echo "<h2>5. Anasayfa Kartları (menu_promo_cards)</h2>";
        echo "<table><tr style='background:#f3f4f6'><th style='padding:8px'>ID</th><th style='padding:8px'>Başlık</th><th style='padding:8px'>image (DB)</th><th style='padding:8px'>Disk?</th><th style='padding:8px'>image_mobile (DB)</th><th style='padding:8px'>Disk?</th></tr>";

        $stmt = $pdo->query("SELECT id, title, image, image_mobile FROM menu_promo_cards ORDER BY id");
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $i = $r['image'];
            $im = $r['image_mobile'];
            $i_disk = $i ? file_exists($root . '/' . ltrim($i, '/')) : null;
            $im_disk = $im ? file_exists($root . '/' . ltrim($im, '/')) : null;

            $i_show = $i === null ? '<em>NULL</em>' : ($i === '' ? '<em style="color:#dc2626">BOŞ STRING</em>' : '<code style="font-size:11px">' . htmlspecialchars($i) . '</code>');
            $im_show = $im === null ? '<em>NULL</em>' : ($im === '' ? '<em style="color:#dc2626">BOŞ STRING</em>' : '<code style="font-size:11px">' . htmlspecialchars($im) . '</code>');

            $i_status = $i ? badge($i_disk ? '✓' : '✗', $i_disk) : '-';
            $im_status = $im ? badge($im_disk ? '✓' : '✗', $im_disk) : '-';

            echo "<tr><td style='padding:6px 8px;border-bottom:1px solid #eee'>{$r['id']}</td>";
            echo "<td style='padding:6px 8px;border-bottom:1px solid #eee'>" . htmlspecialchars($r['title']) . "</td>";
            echo "<td style='padding:6px 8px;border-bottom:1px solid #eee'>$i_show</td>";
            echo "<td style='padding:6px 8px;border-bottom:1px solid #eee;text-align:center'>$i_status</td>";
            echo "<td style='padding:6px 8px;border-bottom:1px solid #eee'>$im_show</td>";
            echo "<td style='padding:6px 8px;border-bottom:1px solid #eee;text-align:center'>$im_status</td></tr>";
        }
        echo "</table>";

        // Statik klasör tarama
        echo "<h2>6. /static/img/yeni/ İçeriği (gerçek diskte)</h2>";
        $staticDir = $root . '/static/img/yeni';
        if (is_dir($staticDir)) {
            $files = array_filter(scandir($staticDir), function($i) { return $i !== '.' && $i !== '..'; });
            echo "<div style='background:white;padding:14px;border-radius:6px;font-family:monospace;font-size:12px;max-height:400px;overflow-y:auto'>";
            foreach ($files as $f) {
                $full = $staticDir . '/' . $f;
                $sz = is_file($full) ? round(filesize($full)/1024, 1) . ' KB' : 'klasör';
                // Türkçe karakter veya escape görüyor muyuz?
                $hasTr = preg_match('/[çğıöşüÇĞİÖŞÜ]/u', $f);
                $hasEsc = strpos($f, '#U') !== false;
                $marker = '';
                if ($hasTr) $marker = ' <span style="background:#fef3c7;padding:1px 6px;border-radius:3px">UTF-8 Türkçe</span>';
                if ($hasEsc) $marker = ' <span style="background:#fee2e2;padding:1px 6px;border-radius:3px">FileZilla escape</span>';
                echo htmlspecialchars($f) . " ({$sz}){$marker}<br>";
            }
            echo "</div>";
        } else {
            echo "<div class='err'>Klasör bulunamadı: $staticDir</div>";
        }

    } catch (Throwable $e) {
        echo "<div class='err'>DB hatası: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<h2>7. Sonraki Adım</h2>
<div class="warn">
<strong>Bu raporu kaydet ve bana ilet:</strong>
<ol>
<li>Sayfanın tamamını ekran görüntüsü olarak al (PrtSc + ekran kaydet)</li>
<li>Veya sayfaya sağ tık → "Sayfayı Kaydet" → HTML dosyasını gönder</li>
<li>Diagnoz tamamlandıktan sonra bu <code>diagnostic.php</code> dosyasını sunucudan SİL (güvenlik)</li>
</ol>
</div>

</body></html>
