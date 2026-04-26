<?php
declare(strict_types=1);
$page_h = 'Görsel Yollarını Normalize Et';
require __DIR__ . '/_header.php';

$cu = admin_user();
if ($cu['role'] !== 'superadmin') {
    flash_set('error', 'Yalnızca süper yöneticiler erişebilir.');
    header('Location: index.php'); exit;
}

$pdo = db();

// DB'de görsel alanı bulunan tablolar
$image_tables = [
    'slider'           => ['image', 'image_mobile'],
    'menu_promo_cards' => ['image', 'image_mobile'],
    'menu_groups'      => ['icon'],
    'branches'         => ['image'],
    'campaigns'        => ['image', 'image_mobile'],
    'menu_items'       => ['image'],
    'jobs'             => ['image'],
    'pages'            => ['image'],
    'timeline'         => ['image'],
];

/**
 * Bir tablonun bir kolonunda belirtilen kolon var mı (schema'da)
 */
function col_exists(PDO $pdo, string $table, string $col): bool {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$table, $col]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

// Türkçe karakter içeren regex (PHP-side)
$tr_chars = '/[çÇğĞıİöÖşŞüÜ]/u';

// === POST: KAYIP REFERANSLAR TEMİZLE ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cleanup_orphans') {
    csrf_required();

    $cleaned = 0;
    foreach ($image_tables as $table => $cols) {
        foreach ($cols as $col) {
            if (!col_exists($pdo, $table, $col)) continue;
            $rows = $pdo->query("SELECT id, $col AS path FROM $table WHERE $col IS NOT NULL AND $col != ''")->fetchAll();
            foreach ($rows as $r) {
                $path = (string)$r['path'];
                // Remote URL'lere dokunma
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) continue;
                if (!asset_exists($path)) {
                    $pdo->prepare("UPDATE $table SET $col = NULL WHERE id = ?")->execute([(int)$r['id']]);
                    $cleaned++;
                }
            }
        }
    }
    log_activity('orphan_images_cleaned', null, "Cleaned: $cleaned");
    flash_set('success', "$cleaned kayıp görsel referansı DB'den temizlendi. Bu kayıtları admin panelinden düzenleyip görsellerini yeniden yükleyebilirsiniz.");
    header('Location: normalize-images.php'); exit;
}

// === POST: NORMALIZE UYGULA ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'apply') {
    csrf_required();

    $log = [
        'db_updates' => 0,
        'file_renames' => 0,
        'file_failed' => [],
    ];

    foreach ($image_tables as $table => $cols) {
        foreach ($cols as $col) {
            if (!col_exists($pdo, $table, $col)) continue;

            $rows = $pdo->query("SELECT id, $col AS path FROM $table WHERE $col IS NOT NULL AND $col != ''")->fetchAll();
            foreach ($rows as $r) {
                $oldPath = (string)$r['path'];
                if (!preg_match($tr_chars, $oldPath)) continue;

                $newPath = tr_to_ascii($oldPath);
                if ($newPath === $oldPath) continue;

                // DB güncelle
                $up = $pdo->prepare("UPDATE $table SET $col = ? WHERE id = ?");
                $up->execute([$newPath, (int)$r['id']]);
                $log['db_updates']++;

                // Diskten rename dene (tüm path varyantları)
                $root = realpath(__DIR__ . '/..');
                $candidates_old = [
                    $root . $oldPath,                                     // UTF-8 olduğu gibi
                    $root . preg_replace_callback($tr_chars, function ($m) {
                        // Unicode escape sequence (FileZilla #U... formatı)
                        $char = $m[0];
                        $hex = str_pad(strtoupper(dechex(mb_ord($char, 'UTF-8'))), 4, '0', STR_PAD_LEFT);
                        return '#U' . $hex;
                    }, $oldPath),
                ];
                $newDisk = $root . $newPath;

                $renamed = false;
                foreach ($candidates_old as $oldDisk) {
                    if (@file_exists($oldDisk) && !@file_exists($newDisk)) {
                        if (@rename($oldDisk, $newDisk)) {
                            $renamed = true;
                            $log['file_renames']++;
                            break;
                        }
                    }
                }
                if (!$renamed && !@file_exists($newDisk)) {
                    $log['file_failed'][] = $oldPath . ' → ' . $newPath;
                }
            }
        }
    }

    log_activity('image_paths_normalized', null, "DB: {$log['db_updates']}, Rename: {$log['file_renames']}");

    $msg = "Tamamlandı: {$log['db_updates']} DB kaydı güncellendi, {$log['file_renames']} dosya yeniden adlandırıldı.";
    if ($log['file_failed']) {
        $msg .= ' UYARI: ' . count($log['file_failed']) . ' dosya bulunamadı (manuel yükleme gerekebilir).';
        flash_set('error', $msg);
    } else {
        flash_set('success', $msg);
    }
    $_SESSION['_normalize_failed'] = $log['file_failed'];
    header('Location: normalize-images.php'); exit;
}

// === LİSTELE ===
$findings = [];
foreach ($image_tables as $table => $cols) {
    foreach ($cols as $col) {
        if (!col_exists($pdo, $table, $col)) continue;
        try {
            $rows = $pdo->query("SELECT id, $col AS path FROM $table WHERE $col IS NOT NULL AND $col != ''")->fetchAll();
        } catch (Throwable $e) { continue; }

        foreach ($rows as $r) {
            $oldPath = (string)$r['path'];
            if (!preg_match($tr_chars, $oldPath)) continue;
            $newPath = tr_to_ascii($oldPath);
            $root = realpath(__DIR__ . '/..');
            $diskExists = @file_exists($root . $oldPath);
            $newExists  = @file_exists($root . $newPath);

            $findings[] = [
                'table' => $table,
                'col'   => $col,
                'id'    => (int)$r['id'],
                'old'   => $oldPath,
                'new'   => $newPath,
                'disk_old' => $diskExists,
                'disk_new' => $newExists,
            ];
        }
    }
}

$failed = $_SESSION['_normalize_failed'] ?? [];
unset($_SESSION['_normalize_failed']);

// Kayıp görsel referansı tara (Türkçe karakter olmasa bile)
$orphans = [];
foreach ($image_tables as $table => $cols) {
    foreach ($cols as $col) {
        if (!col_exists($pdo, $table, $col)) continue;
        try {
            $rows = $pdo->query("SELECT id, $col AS path FROM $table WHERE $col IS NOT NULL AND $col != ''")->fetchAll();
        } catch (Throwable $e) { continue; }
        foreach ($rows as $r) {
            $p = (string)$r['path'];
            if (str_starts_with($p, 'http')) continue;
            if (!asset_exists($p)) {
                $orphans[] = ['table' => $table, 'col' => $col, 'id' => (int)$r['id'], 'path' => $p];
            }
        }
    }
}
?>

<div class="card">
  <h2><i class="fa-solid fa-wand-magic-sparkles"></i> Görsel Yollarını Normalize Et</h2>
  <p style="color:var(--muted);font-size:13px;line-height:1.7">
    DB'deki görsel yollarında Türkçe karakter (ç, ğ, ı, ö, ş, ü) bulunursa bunları ASCII karşılığıyla değiştirir
    (örn: <code>seçilmiş.png → secilmis.png</code>) ve mümkünse diskte de dosyayı yeniden adlandırır.
    Bu işlem, FTP/FileZilla'nın Türkçe karakterleri Unicode escape (<code>#U00e7</code>) olarak yüklediği
    durumlarda görsellerin bulunamaması sorununu çözer.
  </p>
</div>

<?php
// === Sistem Diagnostik ===
$root = realpath(__DIR__ . '/..');
$uploads_dir = $root . '/uploads';
$uploads_exists = is_dir($uploads_dir);
$uploads_writable = $uploads_exists && is_writable($uploads_dir);
$php_max_upload = ini_get('upload_max_filesize');
$php_max_post = ini_get('post_max_size');
$php_uploads_on = (bool)ini_get('file_uploads');
$max_app = MAX_UPLOAD_MB . 'M';
?>
<div class="card">
  <h2 style="font-size:16px"><i class="fa-solid fa-stethoscope"></i> Sistem Diagnostik</h2>
  <table style="font-size:13px;width:100%;max-width:600px">
    <tr>
      <td style="padding:6px 0">PHP <code>file_uploads</code></td>
      <td>
        <?php if ($php_uploads_on): ?>
          <span class="badge b-on">Açık</span>
        <?php else: ?>
          <span class="badge b-off">KAPALI</span> (php.ini'den açın)
        <?php endif; ?>
      </td>
    </tr>
    <tr>
      <td style="padding:6px 0"><code>uploads/</code> klasörü</td>
      <td>
        <?php if (!$uploads_exists): ?>
          <span class="badge b-off">YOK</span> (oluşturulması gerekiyor)
        <?php elseif (!$uploads_writable): ?>
          <span class="badge b-off">YAZILAMIYOR</span> (chmod 755)
        <?php else: ?>
          <span class="badge b-on">Tamam, yazılabilir</span>
        <?php endif; ?>
      </td>
    </tr>
    <tr>
      <td style="padding:6px 0">PHP <code>upload_max_filesize</code></td>
      <td><strong><?= e($php_max_upload) ?></strong></td>
    </tr>
    <tr>
      <td style="padding:6px 0">PHP <code>post_max_size</code></td>
      <td><strong><?= e($php_max_post) ?></strong></td>
    </tr>
    <tr>
      <td style="padding:6px 0">Uygulama <code>MAX_UPLOAD_MB</code></td>
      <td><strong><?= e($max_app) ?></strong></td>
    </tr>
  </table>
  <?php if (!$uploads_writable || !$php_uploads_on): ?>
    <div style="margin-top:12px;padding:10px 14px;background:#fee2e2;border-left:4px solid #dc2626;border-radius:6px;font-size:13px">
      <strong style="color:#991b1b">⚠ Yükleme yapılamaz durumda!</strong>
      Yukarıdaki kırmızı uyarıları çözmeden Admin panelinden görsel yüklenemez.
    </div>
  <?php endif; ?>
</div>

<?php if ($failed): ?>
<div class="card" style="border-left:4px solid #d97706;background:#fef3c7">
  <h2 style="color:#92400e"><i class="fa-solid fa-triangle-exclamation"></i> Diskte Bulunamayan Dosyalar</h2>
  <p style="margin-bottom:10px">Aşağıdaki dosyalar disk üzerinde bulunamadı. DB güncellendi, ancak dosyaları manuel yüklemeniz gerekecek:</p>
  <ul style="margin:0 0 0 22px;font-size:13px;font-family:monospace">
    <?php foreach ($failed as $f): ?>
      <li><?= e($f) ?></li>
    <?php endforeach; ?>
  </ul>
  <p style="margin-top:12px;font-size:12.5px">Çözüm: İlgili görseli yeniden Admin panelinden yükleyin (admin upload otomatik ASCII filename üretir).</p>
</div>
<?php endif; ?>

<?php if ($orphans): ?>
<div class="card" style="border-left:4px solid #dc2626;background:#fee2e2">
  <h2 style="color:#991b1b"><i class="fa-solid fa-image-slash"></i> Kayıp Görsel Referansları (<?= count($orphans) ?>)</h2>
  <p style="margin-bottom:10px;font-size:13.5px;line-height:1.7">
    Aşağıdaki kayıtlarda DB'de görsel yolu var ama disk üzerinde dosya bulunamıyor.
    Bu durum sitede "broken image" ikonu olarak görünür. Tek tıkla referansları DB'den temizleyebilirsiniz —
    ardından ilgili admin sayfasından görsel yeniden yüklenebilir.
  </p>
  <div style="max-height:240px;overflow-y:auto;background:#fff;padding:10px;border-radius:6px;border:1px solid #fecaca">
    <table style="font-size:12.5px;width:100%">
      <thead>
        <tr><th>Tablo</th><th>Kolon</th><th>ID</th><th>Yol</th></tr>
      </thead>
      <tbody>
        <?php foreach ($orphans as $o): ?>
          <tr>
            <td><code><?= e($o['table']) ?></code></td>
            <td><code><?= e($o['col']) ?></code></td>
            <td><?= $o['id'] ?></td>
            <td style="font-family:monospace;font-size:11.5px;color:#dc2626"><?= e($o['path']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <form method="post" style="margin-top:14px" onsubmit="return confirm('<?= count($orphans) ?> kayıp görsel referansını DB\'den temizlemek üzeresiniz.\n\nBu kayıtların görselleri NULL olacak (kayıt silinmez). Devam edilsin mi?')">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="cleanup_orphans">
    <button type="submit" class="btn btn-danger">
      <i class="fa-solid fa-broom"></i> Kayıp Referansları Temizle
    </button>
  </form>
</div>
<?php endif; ?>

<?php if (!$findings): ?>
  <div class="card">
    <div class="empty" style="padding:40px;text-align:center">
      <i class="fa-solid fa-circle-check" style="font-size:48px;color:#16a34a;margin-bottom:14px"></i>
      <p><strong>Tüm görsel yolları temiz!</strong></p>
      <p style="color:var(--muted);font-size:13px">Veritabanında Türkçe karakterli görsel yolu bulunamadı.</p>
    </div>
  </div>
<?php else: ?>
  <div class="card">
    <h2 style="color:#dc2626">
      <i class="fa-solid fa-circle-exclamation"></i>
      Tespit Edilen <?= count($findings) ?> Türkçe Karakterli Yol
    </h2>
    <table>
      <thead>
        <tr>
          <th>Tablo</th>
          <th>Kolon</th>
          <th>ID</th>
          <th>Eski Yol</th>
          <th>Yeni Yol (ASCII)</th>
          <th>Disk Durumu</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($findings as $f): ?>
          <tr>
            <td><code><?= e($f['table']) ?></code></td>
            <td><code><?= e($f['col']) ?></code></td>
            <td><?= $f['id'] ?></td>
            <td style="font-family:monospace;font-size:12px;color:#dc2626"><?= e($f['old']) ?></td>
            <td style="font-family:monospace;font-size:12px;color:#16a34a"><?= e($f['new']) ?></td>
            <td>
              <?php if ($f['disk_new']): ?>
                <span class="badge b-on">Yeni dosya hazır</span>
              <?php elseif ($f['disk_old']): ?>
                <span class="badge b-info">Rename edilebilir</span>
              <?php else: ?>
                <span class="badge b-off">Disk'te yok</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <form method="post" style="margin-top:20px" onsubmit="return confirm('Görsel yollarını normalize etmek üzeresiniz. Devam edilsin mi?')">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="apply">
      <button type="submit" class="btn">
        <i class="fa-solid fa-wand-magic-sparkles"></i> Tümünü Normalize Et
      </button>
      <a href="index.php" class="btn btn-secondary">İptal</a>
    </form>

    <div style="margin-top:18px;padding:14px;background:#fffbeb;border-left:4px solid #f59e0b;border-radius:6px;font-size:13px;line-height:1.7">
      <strong>İşlem sırası:</strong>
      <ol style="margin:6px 0 0 22px">
        <li>DB'deki yol Türkçe karakterleri ASCII'ye çevrilir (ç→c, ş→s, vb.)</li>
        <li>Diskte eski dosya bulunabilirse yeni isimle yeniden adlandırılır</li>
        <li>Bulunamayan dosyalar listelenir; bunları admin panelinden yeniden yükleyebilirsiniz</li>
      </ol>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
