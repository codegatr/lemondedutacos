<?php
declare(strict_types=1);
$page_h = 'Sistem Güncelleme';
require __DIR__ . '/_header.php';

$cu = admin_user();
if ($cu['role'] !== 'superadmin') {
    flash_set('error', 'Bu sayfaya yalnızca süper yöneticiler erişebilir.');
    header('Location: index.php'); exit;
}

$pdo = db();

// GitHub'dan en son sürüm
function fetch_latest_release(): array
{
    $url = "https://api.github.com/repos/" . GITHUB_OWNER . "/" . GITHUB_REPO . "/releases/latest";
    $headers = ["User-Agent: lemondedutacos-updater", "Accept: application/vnd.github+json"];
    if (GITHUB_TOKEN !== '') {
        $headers[] = "Authorization: Bearer " . GITHUB_TOKEN;
    }
    $ctx = stream_context_create(['http' => ['method'=>'GET','header'=>implode("\r\n",$headers),'timeout'=>15,'ignore_errors'=>true]]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) return ['error' => 'GitHub API erişilemedi.'];
    $data = json_decode($body, true);
    if (!is_array($data) || !isset($data['tag_name'])) {
        return ['error' => 'API yanıtı geçersiz: ' . ($data['message'] ?? 'bilinmiyor')];
    }
    return $data;
}

function ver_compare(string $a, string $b): int
{
    return version_compare(ltrim($a, 'v'), ltrim($b, 'v'));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$current_ver = APP_VERSION;
$latest = null;
$check_error = null;

if ($action === '') {
    $r = fetch_latest_release();
    if (isset($r['error'])) {
        $check_error = $r['error'];
    } else {
        $latest = $r;
    }
}

// Güncelleme uygula
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'apply') {
    csrf_required();

    @set_time_limit(300);
    @ini_set('memory_limit', '256M');

    $log = [];
    $start = microtime(true);
    $hist_id = null;

    try {
        $log[] = "Sürüm bilgisi alınıyor...";
        $r = fetch_latest_release();
        if (isset($r['error'])) throw new RuntimeException($r['error']);
        $newVer = ltrim($r['tag_name'], 'v');

        if (ver_compare($newVer, $current_ver) <= 0) {
            throw new RuntimeException("Zaten en güncel sürümdesiniz ($current_ver).");
        }

        // history kaydı oluştur
        $pdo->prepare("INSERT INTO update_history (from_version, to_version, source, status) VALUES (?,?,?,?)")
            ->execute([$current_ver, $newVer, 'github', 'started']);
        $hist_id = (int)$pdo->lastInsertId();

        // ZIP URL'ini bul (release assets içinde .zip ara, yoksa zipball_url)
        $zipUrl = null;
        foreach (($r['assets'] ?? []) as $a) {
            if (str_ends_with(strtolower($a['name'] ?? ''), '.zip')) {
                $zipUrl = $a['browser_download_url'];
                break;
            }
        }
        if (!$zipUrl) $zipUrl = $r['zipball_url'];

        $log[] = "İndiriliyor: " . parse_url($zipUrl, PHP_URL_PATH);

        // İndir
        $tmpZip = sys_get_temp_dir() . "/lmdt_upd_" . bin2hex(random_bytes(4)) . ".zip";
        $headers = ["User-Agent: lemondedutacos-updater"];
        if (GITHUB_TOKEN !== '') $headers[] = "Authorization: Bearer " . GITHUB_TOKEN;
        $ctx = stream_context_create(['http' => ['method'=>'GET','header'=>implode("\r\n",$headers),'timeout'=>120,'follow_location'=>1]]);
        $bytes = @file_get_contents($zipUrl, false, $ctx);
        if ($bytes === false) throw new RuntimeException("ZIP indirilemedi.");
        file_put_contents($tmpZip, $bytes);
        $log[] = "İndirilen boyut: " . round(strlen($bytes)/1024, 1) . " KB";

        // Çıkar
        $tmpDir = sys_get_temp_dir() . "/lmdt_ext_" . bin2hex(random_bytes(4));
        @mkdir($tmpDir, 0755, true);
        $zip = new ZipArchive();
        if ($zip->open($tmpZip) !== true) throw new RuntimeException("ZIP açılamadı.");
        $zip->extractTo($tmpDir);
        $zip->close();
        @unlink($tmpZip);

        // Tek bir kök klasör varsa onu kullan
        $contents = array_values(array_diff(scandir($tmpDir), ['.', '..']));
        $extractRoot = $tmpDir;
        if (count($contents) === 1 && is_dir($tmpDir . '/' . $contents[0])) {
            $extractRoot = $tmpDir . '/' . $contents[0];
        }

        // Korunacak dizinler/dosyalar
        $protect = ['uploads', 'install.lock', 'includes/config.php'];
        $isProtected = function (string $rel) use ($protect): bool {
            foreach ($protect as $p) {
                if ($rel === $p || str_starts_with($rel, $p . '/')) return true;
            }
            return false;
        };

        $rootDir = realpath(__DIR__ . '/..');
        $copied = 0;
        $copyDir = function (string $src, string $dst, string $rel = '') use (&$copyDir, &$copied, $isProtected) {
            if (!is_dir($dst)) @mkdir($dst, 0755, true);
            $items = array_diff(scandir($src), ['.', '..']);
            foreach ($items as $item) {
                $sp = $src . '/' . $item;
                $dp = $dst . '/' . $item;
                $r2 = $rel === '' ? $item : ($rel . '/' . $item);
                if ($isProtected($r2)) continue;
                if (is_dir($sp)) {
                    $copyDir($sp, $dp, $r2);
                } else {
                    if (@copy($sp, $dp)) $copied++;
                }
            }
        };
        $log[] = "Dosyalar kopyalanıyor...";
        $copyDir($extractRoot, $rootDir);
        $log[] = "Toplam kopyalanan: $copied dosya";

        // Geçici dizini temizle
        $rmDir = function (string $d) use (&$rmDir) {
            if (!is_dir($d)) return;
            foreach (array_diff(scandir($d), ['.', '..']) as $i) {
                $p = $d . '/' . $i;
                is_dir($p) ? $rmDir($p) : @unlink($p);
            }
            @rmdir($d);
        };
        $rmDir($tmpDir);

        // version.txt güncelle
        @file_put_contents(__DIR__ . '/../version.txt', $newVer);

        // config.php içindeki APP_VERSION güncelle
        $cfgPath = __DIR__ . '/../includes/config.php';
        $cfg = @file_get_contents($cfgPath);
        if ($cfg !== false) {
            $cfg = preg_replace("/const APP_VERSION = '[^']*';/", "const APP_VERSION = '" . $newVer . "';", $cfg, 1);
            @file_put_contents($cfgPath, $cfg);
        }

        $duration = round(microtime(true) - $start, 2);
        $pdo->prepare("UPDATE update_history SET status=?, log=?, duration_s=? WHERE id=?")
            ->execute(['success', implode("\n", $log), $duration, $hist_id]);
        log_activity('system_updated', null, "v$current_ver → v$newVer");
        flash_set('success', "Güncelleme tamamlandı: v$current_ver → v$newVer ($duration sn)");
    } catch (Throwable $ex) {
        if ($hist_id) {
            $pdo->prepare("UPDATE update_history SET status=?, log=? WHERE id=?")
                ->execute(['failed', implode("\n", $log) . "\nHATA: " . $ex->getMessage(), $hist_id]);
        }
        flash_set('error', 'Güncelleme başarısız: ' . $ex->getMessage());
    }

    header('Location: update.php'); exit;
}

$history = $pdo->query("SELECT * FROM update_history ORDER BY id DESC LIMIT 10")->fetchAll();
?>

<div class="grid-2">
  <div class="metric"><div class="lbl">Mevcut Sürüm</div><div class="val">v<?= e($current_ver) ?></div><div class="delta">Yerel</div></div>
  <?php if ($latest): ?>
    <?php $up = ver_compare(ltrim($latest['tag_name'], 'v'), $current_ver) > 0; ?>
    <div class="metric">
      <div class="lbl">Sunucu Sürümü</div>
      <div class="val" style="color:<?= $up ? '#d97706' : '#3A5F0B' ?>"><?= e($latest['tag_name']) ?></div>
      <div class="delta"><?= $up ? '⚠ Güncelleme mevcut' : '✓ Güncelsiniz' ?></div>
    </div>
  <?php else: ?>
    <div class="metric"><div class="lbl">Sunucu Sürümü</div><div class="val" style="color:#dc2626;font-size:14px">Erişilemedi</div><div class="delta"><?= e($check_error ?? '') ?></div></div>
  <?php endif; ?>
</div>

<div class="card" style="margin-top:18px">
  <h2>GitHub Repo Bilgisi</h2>
  <p><strong>Owner:</strong> <code><?= e(GITHUB_OWNER) ?></code></p>
  <p><strong>Repo:</strong> <code><?= e(GITHUB_REPO) ?></code></p>
  <p><strong>Token:</strong> <?= GITHUB_TOKEN ? 'Ayarlanmış (private repo)' : 'Yok (public repo)' ?></p>
  <p style="font-size:12px;color:#6b7280;margin-top:8px">
    Repo URL: <a href="https://github.com/<?= e(GITHUB_OWNER) ?>/<?= e(GITHUB_REPO) ?>" target="_blank">github.com/<?= e(GITHUB_OWNER) ?>/<?= e(GITHUB_REPO) ?></a>
  </p>
</div>

<?php if ($latest && ver_compare(ltrim($latest['tag_name'], 'v'), $current_ver) > 0): ?>
  <div class="card">
    <h2>Güncellemeyi Uygula</h2>
    <p style="margin-bottom:10px"><strong>Yeni Sürüm:</strong> <?= e($latest['tag_name']) ?> &middot; <strong>Yayın:</strong> <?= e($latest['published_at']) ?></p>
    <?php if (!empty($latest['body'])): ?>
      <details style="margin-bottom:14px">
        <summary style="cursor:pointer;font-weight:600">Sürüm Notları</summary>
        <pre style="background:#f9fafb;padding:12px;border-radius:6px;font-size:12px;line-height:1.5;overflow:auto;margin-top:8px"><?= e($latest['body']) ?></pre>
      </details>
    <?php endif; ?>
    <div class="flash flash-warning">
      <strong>Önemli:</strong> Güncelleme sırasında <code>uploads/</code>, <code>includes/config.php</code> ve <code>install.lock</code> dosyaları korunur. Yine de güncellemeden önce yedek almanız önerilir.
    </div>
    <form method="post" data-confirm="Güncellemeyi başlatmak istediğinize emin misiniz? İşlem sırasında site geçici olarak yavaşlayabilir.">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="apply">
      <button type="submit" class="btn btn-warn"><i class="fa-solid fa-cloud-arrow-down"></i> Güncellemeyi Şimdi Başlat</button>
    </form>
  </div>
<?php endif; ?>

<div class="card">
  <h2>Geçmiş Güncellemeler</h2>
  <?php if (!$history): ?>
    <div class="empty">Henüz güncelleme yapılmadı.</div>
  <?php else: ?>
    <table>
      <thead><tr><th>#</th><th>Tarih</th><th>Sürüm</th><th>Durum</th><th>Süre</th><th>Log</th></tr></thead>
      <tbody>
      <?php foreach ($history as $h): ?>
        <tr>
          <td><?= (int)$h['id'] ?></td>
          <td><?= format_date($h['created_at']) ?></td>
          <td><?= e($h['from_version']) ?> → <?= e($h['to_version']) ?></td>
          <td>
            <span class="badge <?= $h['status']==='success'?'b-on':($h['status']==='failed'?'b-off':'b-info') ?>">
              <?= e($h['status']) ?>
            </span>
          </td>
          <td><?= $h['duration_s'] ? $h['duration_s'] . ' sn' : '—' ?></td>
          <td>
            <?php if (!empty($h['log'])): ?>
              <details><summary style="cursor:pointer;font-size:11px;color:#3A5F0B">Görüntüle</summary>
                <pre style="background:#f9fafb;padding:8px;font-size:10px;border-radius:4px;margin-top:4px;max-width:400px;overflow:auto"><?= e($h['log']) ?></pre>
              </details>
            <?php else: ?>—<?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
