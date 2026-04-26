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

/* GitHub'dan en son sürüm bilgisi */
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

$action = $_POST['action'] ?? '';
$current_ver = APP_VERSION;
$latest = null;
$check_error = null;

/* Sürüm bilgisi (her zaman çek) */
$r = fetch_latest_release();
if (isset($r['error'])) {
    $check_error = $r['error'];
} else {
    $latest = $r;
}

/* GÜNCELLEME UYGULA */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'apply') {
    csrf_required();

    @set_time_limit(300);
    @ini_set('memory_limit', '256M');

    $log = [];
    $start = microtime(true);
    $hist_id = null;

    try {
        $log[] = "Sürüm bilgisi alınıyor...";
        if (!$latest) throw new RuntimeException($check_error ?: 'Sürüm bilgisi alınamadı.');
        $newVer = ltrim($latest['tag_name'], 'v');

        if (ver_compare($newVer, $current_ver) <= 0) {
            throw new RuntimeException("Zaten en güncel sürümdesiniz ($current_ver).");
        }

        // History kaydı oluştur (failed default; başarılı olursa update edilecek)
        $pdo->prepare("INSERT INTO update_history (from_version, to_version, status, notes, admin_id) VALUES (?,?,?,?,?)")
            ->execute([$current_ver, $newVer, 'failed', "Başlatıldı...", $cu['id']]);
        $hist_id = (int)$pdo->lastInsertId();

        // ZIP URL'ini bul
        $zipUrl = null;
        foreach (($latest['assets'] ?? []) as $a) {
            if (str_ends_with(strtolower($a['name'] ?? ''), '.zip')) {
                $zipUrl = $a['browser_download_url'];
                break;
            }
        }
        if (!$zipUrl) $zipUrl = $latest['zipball_url'];

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

        // config.php içindeki APP_VERSION güncelle (whitespace toleranslı)
        $cfgPath = __DIR__ . '/../includes/config.php';
        $cfg = @file_get_contents($cfgPath);
        if ($cfg !== false) {
            $cfg = preg_replace("/const\s+APP_VERSION\s*=\s*'[^']*';/", "const APP_VERSION  = '" . $newVer . "';", $cfg, 1);
            @file_put_contents($cfgPath, $cfg);
        }

        $duration = round(microtime(true) - $start, 2);
        $log[] = "Süre: $duration sn";
        $pdo->prepare("UPDATE update_history SET status=?, notes=? WHERE id=?")
            ->execute(['success', implode("\n", $log), $hist_id]);
        log_activity('system_updated', null, "v$current_ver → v$newVer");

        // PRG: yenilenen sayfada başarı ekranını göstermek için redirect
        header('Location: update.php?completed=1&from=' . urlencode($current_ver) . '&to=' . urlencode($newVer) . '&dur=' . $duration);
        exit;
    } catch (Throwable $ex) {
        if ($hist_id) {
            $pdo->prepare("UPDATE update_history SET status=?, notes=? WHERE id=?")
                ->execute(['failed', implode("\n", $log) . "\nHATA: " . $ex->getMessage(), $hist_id]);
        }
        flash_set('error', 'Güncelleme başarısız: ' . $ex->getMessage());
        header('Location: update.php'); exit;
    }
}

/* Başarı ekranı (PRG sonrası) */
$show_success  = !empty($_GET['completed']);
$success_from  = $_GET['from'] ?? '';
$success_to    = $_GET['to']   ?? '';
$success_dur   = $_GET['dur']  ?? '0';

$history = $pdo->query("SELECT * FROM update_history ORDER BY id DESC LIMIT 10")->fetchAll();
$has_update = $latest && ver_compare(ltrim($latest['tag_name'], 'v'), $current_ver) > 0;
?>

<?php if ($show_success): ?>
<div class="card" style="text-align:center;padding:40px 32px;background:linear-gradient(135deg,#ecfdf5,#d1fae5);border:1px solid #6ee7b7;position:relative;overflow:hidden">
  <div style="position:absolute;inset:0;background:radial-gradient(circle at 50% 0%,rgba(34,197,94,.15),transparent 60%);pointer-events:none"></div>
  <div style="position:relative;z-index:1">
    <div style="width:104px;height:104px;margin:0 auto 18px;position:relative">
      <svg viewBox="0 0 100 100" style="width:104px;height:104px">
        <circle cx="50" cy="50" r="44" fill="none" stroke="#a7f3d0" stroke-width="6"/>
        <circle id="success-circle" cx="50" cy="50" r="44" fill="none" stroke="#16a34a" stroke-width="6"
                stroke-dasharray="276" stroke-dashoffset="276" stroke-linecap="round"
                style="transition:stroke-dashoffset 1.2s ease;transform:rotate(-90deg);transform-origin:50% 50%"/>
      </svg>
      <div id="success-tick" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:46px;color:#16a34a;opacity:0;transform:scale(.5);transition:.4s">
        <i class="fa-solid fa-check"></i>
      </div>
    </div>
    <h2 style="font-size:26px;color:#16a34a;margin:0 0 8px;font-weight:800">Güncelleme Tamamlandı!</h2>
    <p style="color:#065f46;font-size:15px;margin:0 0 4px">
      <strong>v<?= e($success_from) ?></strong> → <strong>v<?= e($success_to) ?></strong> sürümüne yükseltildi
    </p>
    <p style="color:#047857;font-size:13px;margin:0 0 22px">İşlem süresi: <?= e($success_dur) ?> saniye</p>

    <div style="display:inline-flex;gap:10px;flex-wrap:wrap;justify-content:center">
      <a href="update.php" class="btn">Güncelleme Sayfası</a>
      <a href="index.php" class="btn btn-secondary">Pano</a>
      <a href="/" target="_blank" class="btn btn-line">Siteyi Aç <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
    </div>
  </div>
</div>

<script>
// Animasyon: dairenin dolması + tick'in fade-in
setTimeout(()=>{
  const c=document.getElementById('success-circle');
  if(c) c.style.strokeDashoffset='0';
},150);
setTimeout(()=>{
  const t=document.getElementById('success-tick');
  if(t){t.style.opacity='1';t.style.transform='scale(1)'}
},800);
</script>
<?php endif; ?>

<?php /* Sürüm bilgisi kartları */ ?>
<div class="grid-2">
  <div class="metric">
    <div class="lbl">Mevcut Sürüm</div>
    <div class="val">v<?= e($current_ver) ?></div>
    <div class="delta">Sunucudaki yerel sürüm</div>
  </div>
  <?php if ($latest): ?>
    <div class="metric">
      <div class="lbl">GitHub'da Mevcut Sürüm</div>
      <div class="val" style="color:<?= $has_update ? '#d97706' : '#16a34a' ?>"><?= e($latest['tag_name']) ?></div>
      <div class="delta"><?= $has_update ? '⚠ Yeni sürüm mevcut' : '✓ Sisteminiz güncel' ?></div>
    </div>
  <?php else: ?>
    <div class="metric">
      <div class="lbl">GitHub Bağlantısı</div>
      <div class="val" style="color:#dc2626;font-size:14px;line-height:1.3">Erişilemedi</div>
      <div class="delta"><?= e($check_error ?? '') ?></div>
    </div>
  <?php endif; ?>
</div>

<?php if ($has_update): ?>
<div class="card" style="margin-top:18px;border:2px solid #fde68a">
  <h2 style="color:#d97706;display:flex;align-items:center;gap:10px">
    <i class="fa-solid fa-cloud-arrow-down"></i>
    Güncellemeyi Uygula
  </h2>

  <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:16px;font-size:13px">
    <div><strong>Sürüm:</strong> <?= e($latest['tag_name']) ?></div>
    <div><strong>Yayın:</strong> <?= format_date($latest['published_at'], 'd.m.Y H:i') ?></div>
    <?php if (!empty($latest['author']['login'])): ?>
      <div><strong>Yayınlayan:</strong> <?= e($latest['author']['login']) ?></div>
    <?php endif; ?>
  </div>

  <?php if (!empty($latest['body'])): ?>
    <details style="margin-bottom:14px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:0">
      <summary style="cursor:pointer;font-weight:700;color:#92400e;padding:10px 14px;font-size:13px">📋 Sürüm Notları</summary>
      <pre style="background:#fff;padding:14px;font-size:12px;line-height:1.6;overflow:auto;margin:0;border-top:1px solid #fde68a;white-space:pre-wrap"><?= e($latest['body']) ?></pre>
    </details>
  <?php endif; ?>

  <div class="flash flash-warning" style="margin-bottom:16px">
    <strong>⚠ Önemli:</strong>
    <ul style="margin:6px 0 0 18px;font-size:12.5px;line-height:1.7">
      <li>Güncelleme sırasında <code>uploads/</code>, <code>includes/config.php</code> ve <code>install.lock</code> korunur</li>
      <li>İşlem 10-30 saniye sürebilir, lütfen sayfayı kapatmayın</li>
      <li>Önemli verileriniz için yedek almanız önerilir</li>
    </ul>
  </div>

  <form method="post" id="update-form" onsubmit="return startUpdate(this)">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="apply">
    <button type="submit" id="update-btn" class="btn btn-warn" style="font-size:15px;padding:12px 24px">
      <i class="fa-solid fa-cloud-arrow-down"></i> Güncellemeyi Şimdi Başlat
    </button>

    <div id="update-progress" style="display:none;margin-top:18px">
      <div style="height:6px;background:#fef3c7;border-radius:99px;overflow:hidden;max-width:560px">
        <div id="update-bar" style="height:6px;width:0;background:linear-gradient(90deg,#f59e0b,#16a34a);border-radius:99px;transition:width 8s linear"></div>
      </div>
      <div id="update-msg" style="font-size:13px;color:#374151;margin-top:10px;font-weight:600;display:flex;align-items:center;gap:8px">
        <span class="spinner" style="display:inline-block;width:14px;height:14px;border:2px solid #f59e0b;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite"></span>
        <span id="update-msg-text">Hazırlanıyor...</span>
      </div>
    </div>
  </form>
</div>

<style>@keyframes spin{to{transform:rotate(360deg)}}</style>

<script>
function startUpdate(form) {
  if (!confirm('Güncellemeyi başlatmak üzeresiniz. Devam edilsin mi?\n\nİşlem 10-30 saniye sürebilir, sayfayı kapatmayın.')) return false;

  const btn = document.getElementById('update-btn');
  btn.disabled = true;
  document.getElementById('update-progress').style.display = 'block';

  // Bar 8 saniyede %95'e gider, sunucu cevabı dönünce yenilenmiş sayfa zaten %100 yeşil banner gösterir
  setTimeout(() => { document.getElementById('update-bar').style.width = '95%'; }, 80);

  const stages = [
    [0,    '🔗 GitHub API\'ye bağlanılıyor...'],
    [1500, '📦 ZIP paketi indiriliyor...'],
    [3500, '📂 Dosyalar açılıp kopyalanıyor...'],
    [6000, '🔧 Sürüm bilgileri güncelleniyor...'],
    [7500, '✅ Tamamlanıyor...'],
  ];
  const txt = document.getElementById('update-msg-text');
  stages.forEach(([t, m]) => setTimeout(() => { txt.textContent = m; }, t));

  return true;
}
</script>
<?php endif; ?>

<?php /* GitHub repo bilgisi */ ?>
<div class="card">
  <h2><i class="fa-brands fa-github"></i> GitHub Repo</h2>
  <table>
    <tr><th style="width:160px">Owner</th><td><code><?= e(GITHUB_OWNER) ?></code></td></tr>
    <tr><th>Repo</th><td><code><?= e(GITHUB_REPO) ?></code></td></tr>
    <tr><th>Token</th><td><?= GITHUB_TOKEN ? '<span class="badge b-on">Tanımlı</span> (private repo desteği aktif)' : '<span class="badge b-info">Yok</span> (public repo)' ?></td></tr>
    <tr><th>Repo URL</th><td><a href="https://github.com/<?= e(GITHUB_OWNER) ?>/<?= e(GITHUB_REPO) ?>" target="_blank">github.com/<?= e(GITHUB_OWNER) ?>/<?= e(GITHUB_REPO) ?></a></td></tr>
    <tr><th>Releases</th><td><a href="https://github.com/<?= e(GITHUB_OWNER) ?>/<?= e(GITHUB_REPO) ?>/releases" target="_blank">Tüm sürümleri gör →</a></td></tr>
  </table>
</div>

<?php /* Geçmiş güncellemeler */ ?>
<div class="card">
  <h2><i class="fa-solid fa-clock-rotate-left"></i> Güncelleme Geçmişi</h2>
  <?php if (!$history): ?>
    <div class="empty">Henüz güncelleme yapılmadı.</div>
  <?php else: ?>
    <table>
      <thead><tr><th>#</th><th>Tarih</th><th>Sürüm</th><th>Durum</th><th>Detay</th></tr></thead>
      <tbody>
      <?php foreach ($history as $h): ?>
        <tr>
          <td><?= (int)$h['id'] ?></td>
          <td style="font-size:11px"><?= format_date($h['created_at']) ?></td>
          <td><strong>v<?= e($h['from_version']) ?></strong> → <strong>v<?= e($h['to_version']) ?></strong></td>
          <td>
            <span class="badge <?= $h['status']==='success'?'b-on':'b-off' ?>">
              <?= $h['status']==='success' ? '✓ Başarılı' : '✗ Başarısız' ?>
            </span>
          </td>
          <td>
            <?php if (!empty($h['notes'])): ?>
              <details>
                <summary style="cursor:pointer;font-size:11px;color:#3A5F0B;font-weight:600">Logu görüntüle</summary>
                <pre style="background:#f9fafb;padding:10px;font-size:10.5px;line-height:1.6;border-radius:6px;margin-top:6px;max-height:280px;overflow:auto;white-space:pre-wrap"><?= e($h['notes']) ?></pre>
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
