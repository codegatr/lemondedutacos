<?php
/**
 * admin/update.php — Güncelleme Merkezi v5
 * CodeGa ERP'deki güncelleme merkezinin LMD Tacos uyarlaması.
 */
declare(strict_types=1);
$page_h = 'Güncelleme Merkezi';

@set_time_limit(0);
@ini_set('max_execution_time', '0');
@ini_set('memory_limit', '512M');
@ignore_user_abort(true);

require_once __DIR__ . '/../includes/functions.php';
admin_require();

$cu = admin_user();
$pdo = db();

define('GH_REPO',   GITHUB_OWNER . '/' . GITHUB_REPO);
define('GH_BRANCH', defined('GITHUB_BRANCH') ? GITHUB_BRANCH : 'main');
define('SITE_ROOT', realpath(__DIR__ . '/..'));
define('BK_DIR',    SITE_ROOT . '/uploads/backups');
define('TOK_FILE',  __DIR__ . '/.gh_token');
define('VER_FILE',  SITE_ROOT . '/version.txt');
define('MANIFEST',  SITE_ROOT . '/manifest.json');
define('MAX_BK',    10);
define('UPD_EXCLUDES', [
    'includes/config.php', '.htaccess', '.gitignore',
    'uploads/', 'cache/', 'sessions/',
    'admin/.gh_token',
    '.git/', 'node_modules/', 'vendor/',
    'install.lock', 'install.php',
    '.env', '.env.example',
    'static/img/yeni/',
    'diagnostic.php', 'fix-it.php', 'fix-it2.php', 'rename-files.php', 'updater.php',
]);

if (!is_dir(BK_DIR)) @mkdir(BK_DIR, 0755, true);
$htaccess = BK_DIR . '/.htaccess';
if (!file_exists($htaccess)) @file_put_contents($htaccess, "Order deny,allow\nDeny from all\n");

function upd_getTok(): string {
    if (file_exists(TOK_FILE)) return trim(preg_replace('/[^a-zA-Z0-9_\-]/', '', file_get_contents(TOK_FILE)));
    return defined('GITHUB_TOKEN') ? GITHUB_TOKEN : '';
}

function upd_curl(string $url, array $hdrs = [], int $to = 30): array {
    // cURL varsa kullan
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => $to,
            CURLOPT_HTTPHEADER => $hdrs, CURLOPT_USERAGENT => 'LMDT-Updater/5.0', CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        return ['code' => $code, 'body' => $body];
    }
    // Fallback: file_get_contents (allow_url_fopen açık olmalı)
    if (!ini_get('allow_url_fopen')) {
        return ['code' => 0, 'body' => 'cURL ve allow_url_fopen ikisi de kapalı'];
    }
    $hdrs[] = 'User-Agent: LMDT-Updater/5.0';
    $ctx = stream_context_create(['http' => [
        'method' => 'GET',
        'header' => implode("\r\n", $hdrs),
        'timeout' => $to,
        'ignore_errors' => true,
    ]]);
    $body = @file_get_contents($url, false, $ctx);
    $code = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $code = (int)$m[1];
    }
    return ['code' => $code, 'body' => $body !== false ? $body : ''];
}

function upd_ghAPI(string $path, string $tok): ?array {
    $r = upd_curl('https://api.github.com/repos/' . GH_REPO . $path,
        ['Authorization: token ' . $tok, 'Accept: application/vnd.github+json', 'X-GitHub-Api-Version: 2022-11-28']);
    if ($r['code'] !== 200) return null;
    return json_decode($r['body'], true);
}

function upd_ghDownload(string $file, string $tok): ?string {
    $d = upd_ghAPI('/contents/' . str_replace('%2F','/',rawurlencode($file)) . '?ref=' . GH_BRANCH, $tok);
    if ($d && !empty($d['content'])) return base64_decode(str_replace(["\n","\r"],'',$d['content']));
    $r = upd_curl('https://raw.githubusercontent.com/' . GH_REPO . '/' . GH_BRANCH . '/' . $file,
        ['Authorization: token ' . $tok], 60);
    if ($r['code'] === 200 && $r['body']) return $r['body'];
    return null;
}

function upd_isExcluded(string $path): bool {
    foreach (UPD_EXCLUDES as $ex) {
        if ($path === $ex) return true;
        if (str_ends_with($ex, '/') && str_starts_with($path, $ex)) return true;
    }
    return false;
}

function upd_repoTree(string $tok): array {
    $tree = upd_ghAPI('/git/trees/' . GH_BRANCH . '?recursive=1', $tok);
    if (!$tree || empty($tree['tree'])) return [];
    $out = [];
    foreach ($tree['tree'] as $i) {
        if ($i['type'] !== 'blob') continue;
        if (!upd_isExcluded($i['path'])) $out[] = ['path'=>$i['path'], 'sha'=>$i['sha'], 'size'=>$i['size']??0];
    }
    usort($out, fn($a,$b) => strcmp($a['path'], $b['path']));
    return $out;
}

function upd_blobSHA(string $c): string { return sha1('blob ' . strlen($c) . "\0" . $c); }

function upd_localVer(): string {
    if (file_exists(MANIFEST)) {
        $m = json_decode(file_get_contents(MANIFEST), true);
        if (!empty($m['version'])) return $m['version'];
    }
    if (file_exists(VER_FILE)) return trim(file_get_contents(VER_FILE));
    return defined('APP_VERSION') ? APP_VERSION : '?';
}

function upd_ghVer(string $tok): string {
    $d = upd_ghAPI('/contents/manifest.json?ref=' . GH_BRANCH, $tok);
    if ($d && !empty($d['content'])) {
        $m = json_decode(base64_decode(str_replace(["\n","\r"],'',$d['content'])), true);
        if (!empty($m['version'])) return $m['version'];
    }
    $d = upd_ghAPI('/contents/version.txt?ref=' . GH_BRANCH, $tok);
    if ($d && !empty($d['content'])) return trim(base64_decode(str_replace(["\n","\r"],'',$d['content'])));
    return '?';
}

function upd_backup(string $label = ''): array {
    if (!class_exists('ZipArchive')) return ['ok'=>false,'error'=>'ZipArchive yok'];
    $tag  = $label ? '_' . preg_replace('/[^a-z0-9]/i','',$label) : '';
    $name = 'bk_' . date('Ymd_His') . $tag . '_v' . upd_localVer() . '.zip';
    $path = BK_DIR . '/' . $name;
    $zip = new ZipArchive();
    if ($zip->open($path, ZipArchive::CREATE) !== true) return ['ok'=>false,'error'=>'ZIP açılamadı'];
    $kritik = ['admin', 'includes', 'migrations'];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(SITE_ROOT, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    $cnt = 0;
    foreach ($it as $f) {
        if ($f->isDir()) continue;
        $rel = str_replace(SITE_ROOT . '/', '', $f->getPathname());
        if (upd_isExcluded($rel)) continue;
        $seg = explode('/', $rel)[0];
        if (strpos($rel, '/') !== false && !in_array($seg, $kritik, true)) continue;
        if ($f->getSize() > 10 * 1024 * 1024) continue;
        $zip->addFile($f->getPathname(), $rel);
        $cnt++;
        if ($cnt > 2000) break;
    }
    $zip->close();
    $bks = glob(BK_DIR . '/bk_*.zip') ?: [];
    usort($bks, fn($a,$b) => filemtime($a) <=> filemtime($b));
    foreach (array_slice($bks, 0, max(0, count($bks) - MAX_BK)) as $old) @unlink($old);
    return ['ok'=>true, 'name'=>$name, 'size'=>filesize($path), 'files'=>$cnt];
}

function upd_runMigrations(): array {
    $log = []; global $pdo;
    $migDir = SITE_ROOT . '/migrations';
    if (!is_dir($migDir)) return ['ok'=>true, 'log'=>['ℹ️ migrations/ klasörü yok'], 'new'=>0];
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `_migrations` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL UNIQUE,
            `applied_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $applied = array_flip($pdo->query("SELECT name FROM _migrations")->fetchAll(PDO::FETCH_COLUMN));
        $files = array_filter(scandir($migDir), fn($f) => preg_match('/^\d+_.+\.sql$/', $f));
        sort($files);
        $ok = 0; $skip = 0; $fail = 0;
        foreach ($files as $mf) {
            if (isset($applied[$mf])) { $skip++; continue; }
            $sql = file_get_contents("$migDir/$mf");
            // FIX: Önce yorum satırlarını sil, sonra statement'lara böl.
            // Eski bug: explode(';',$sql) sonrası başında yorum olan parçaları
            // tüm statement'ı yorum sanıp atıyordu.
            $sql = preg_replace('/^\s*--[^\r\n]*$/m', '', $sql);
            $stmts = array_filter(array_map('trim', explode(';', $sql)), fn($s) => $s !== '');
            try {
                foreach ($stmts as $s) if (trim($s)) $pdo->exec($s);
                $pdo->prepare("INSERT INTO _migrations (name) VALUES (?)")->execute([$mf]);
                $log[] = '✅ ' . $mf; $ok++;
            } catch (Throwable $e) {
                $log[] = '❌ ' . $mf . ' — ' . substr($e->getMessage(), 0, 100); $fail++;
            }
        }
        $log[] = "🎉 $ok yeni, $skip atlandı, $fail hata";
        return ['ok'=>$fail===0, 'log'=>$log, 'new'=>$ok];
    } catch (Throwable $e) {
        return ['ok'=>false, 'log'=>['❌ ' . $e->getMessage()], 'new'=>0];
    }
}

// ══ AJAX Handler ══
if (isset($_GET['upd_ajax'])) {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');

    // Tüm PHP hatalarını JSON olarak yakala (HTML hata sayfası dönmesin)
    set_error_handler(function($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) return;
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
    set_exception_handler(function($e) {
        echo json_encode(['ok'=>false, 'error'=>$e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine()]);
        exit;
    });

    try {
        $aj  = $_GET['upd_ajax'];
        $tok = upd_getTok();

    if ($aj === 'status') {
        if (!$tok) { echo json_encode(['ok'=>false,'error'=>'Token yok. Ayarlar sekmesinden ekleyin.']); exit; }
        try {
            $rf = upd_repoTree($tok);
            if (empty($rf)) { echo json_encode(['ok'=>false,'error'=>'Repo ağacı okunamadı']); exit; }
            $stats = ['ok'=>0,'diff'=>0,'missing'=>0]; $fs = [];
            foreach ($rf as $rec) {
                $lp = SITE_ROOT . '/' . $rec['path'];
                if (!file_exists($lp)) { $fs[$rec['path']] = ['status'=>'missing','size'=>$rec['size']]; $stats['missing']++; }
                else {
                    $sha = upd_blobSHA(file_get_contents($lp));
                    if ($sha === $rec['sha']) { $fs[$rec['path']] = ['status'=>'ok','size'=>$rec['size']]; $stats['ok']++; }
                    else { $fs[$rec['path']] = ['status'=>'diff','size'=>$rec['size']]; $stats['diff']++; }
                }
            }
            echo json_encode(['ok'=>true,'local_ver'=>upd_localVer(),'remote_ver'=>upd_ghVer($tok),
                'stats'=>$stats,'total'=>count($rf),'files'=>$fs,
                'needs_update'=>($stats['diff']+$stats['missing'])>0]);
        } catch(Throwable $e) { echo json_encode(['ok'=>false,'error'=>$e->getMessage()]); }
        exit;
    }

    if ($aj === 'sync' || $aj === 'force_sync') {
        if (!$tok) { echo json_encode(['ok'=>false,'error'=>'Token yok']); exit; }
        $log = []; $updated = 0; $errors = []; $force = $aj === 'force_sync';
        try {
            $bk = upd_backup($force ? 'force' : 'sync');
            if ($bk['ok']) $log[] = '📦 Yedek: ' . $bk['name'];
            $rf = upd_repoTree($tok);
            $log[] = ($force ? '🔥 TÜM ' : '📋 ') . count($rf) . ' dosya ' . ($force ? 'yeniden indiriliyor' : 'kontrol ediliyor');
            foreach ($rf as $rec) {
                $lp = SITE_ROOT . '/' . $rec['path'];
                if (upd_isExcluded($rec['path'])) continue;
                if (!$force && file_exists($lp) && upd_blobSHA(file_get_contents($lp)) === $rec['sha']) continue;
                $c = upd_ghDownload($rec['path'], $tok);
                if ($c === null) { $errors[] = '❌ ' . $rec['path']; continue; }
                $dir = dirname($lp);
                if (!is_dir($dir)) @mkdir($dir, 0755, true);
                if (@file_put_contents($lp, $c) !== false) { $log[] = '✅ ' . $rec['path']; $updated++; }
                else $errors[] = '❌ ' . $rec['path'] . ' yazma hatası';
            }
            $log[] = '🎉 ' . $updated . ' dosya güncellendi';
            if ($updated > 0) {
                $mig = upd_runMigrations();
                if (!empty($mig['log'])) { $log[] = ''; $log[] = '🗄️ DB Migration:'; $log = array_merge($log, $mig['log']); }
                if (function_exists('opcache_reset')) @opcache_reset();
            }
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS update_history(id INT AUTO_INCREMENT PRIMARY KEY,version VARCHAR(20),prev_version VARCHAR(20),release_notes TEXT,log_data TEXT,success TINYINT(1) DEFAULT 1,installed_at DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
                $pdo->prepare("INSERT INTO update_history(version,prev_version,release_notes,log_data,success) VALUES(?,?,?,?,?)")
                    ->execute([upd_localVer(), upd_localVer(), ($force?'Force Sync':'Smart Sync').': '.$updated.' dosya', json_encode($log,JSON_UNESCAPED_UNICODE), $updated>0?1:0]);
            } catch(Throwable $e) {}
        } catch(Throwable $e) { $errors[] = '❌ ' . $e->getMessage(); }
        echo json_encode(['ok'=>empty($errors) || $updated>0, 'log'=>$log, 'errors'=>$errors, 'updated'=>$updated, 'version'=>upd_localVer()]);
        exit;
    }

    if ($aj === 'update_file') {
        $file = trim($_POST['file'] ?? '');
        if (!$file || !$tok) { echo json_encode(['ok'=>false,'error'=>'Eksik parametre']); exit; }
        if (upd_isExcluded($file)) { echo json_encode(['ok'=>false,'error'=>'Korumalı dosya']); exit; }
        $c = upd_ghDownload($file, $tok);
        if ($c === null) { echo json_encode(['ok'=>false,'error'=>'İndirme hatası']); exit; }
        $lp = SITE_ROOT . '/' . $file;
        if (!is_dir(dirname($lp))) @mkdir(dirname($lp), 0755, true);
        $w = @file_put_contents($lp, $c);
        echo json_encode($w !== false ? ['ok'=>true,'bytes'=>$w] : ['ok'=>false,'error'=>'Yazma hatası']);
        exit;
    }

    if ($aj === 'commits') {
        if (!$tok) { echo json_encode(['ok'=>false,'error'=>'Token yok']); exit; }
        $d = upd_ghAPI('/commits?sha=' . GH_BRANCH . '&per_page=20', $tok);
        if (!$d) { echo json_encode(['ok'=>false,'error'=>'Commit alınamadı']); exit; }
        $out = [];
        foreach ($d as $c) $out[] = [
            'sha' => substr($c['sha'],0,7), 'message' => $c['commit']['message'] ?? '',
            'author' => $c['commit']['author']['name'] ?? '', 'date' => $c['commit']['author']['date'] ?? '',
            'url' => $c['html_url'] ?? '',
        ];
        echo json_encode(['ok'=>true,'commits'=>$out]);
        exit;
    }

    if ($aj === 'backups') {
        $bks = glob(BK_DIR . '/bk_*.zip') ?: [];
        usort($bks, fn($a,$b) => filemtime($b) <=> filemtime($a));
        echo json_encode(['ok'=>true, 'backups'=>array_map(fn($p)=>[
            'name' => basename($p), 'size' => filesize($p), 'time' => filemtime($p),
            'ver' => preg_match('/_v([\d.]+)\.zip$/', basename($p), $m) ? $m[1] : '?',
        ], $bks)]);
        exit;
    }

    if ($aj === 'restore') {
        $name = basename(trim($_POST['backup'] ?? ''));
        if (!$name) { echo json_encode(['ok'=>false,'error'=>'Yedek yok']); exit; }
        $path = BK_DIR . '/' . $name;
        if (!file_exists($path)) { echo json_encode(['ok'=>false,'error'=>'Dosya yok']); exit; }
        $safeBk = upd_backup('pre_restore');
        $log = []; if ($safeBk['ok']) $log[] = '📦 Güvenlik yedeği: ' . $safeBk['name'];
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) { echo json_encode(['ok'=>false,'error'=>'ZIP açılamadı']); exit; }
        $r = 0;
        for ($i=0; $i<$zip->numFiles; $i++) {
            $f = $zip->getNameIndex($i);
            $c = $zip->getFromIndex($i);
            if ($c === false || upd_isExcluded($f)) continue;
            $t = SITE_ROOT . '/' . $f;
            if (!is_dir(dirname($t))) @mkdir(dirname($t), 0755, true);
            if (@file_put_contents($t, $c) !== false) { $log[] = '✅ ' . $f; $r++; }
        }
        $zip->close();
        if (function_exists('opcache_reset')) @opcache_reset();
        $log[] = '🎉 ' . $r . ' dosya geri yüklendi';
        echo json_encode(['ok'=>true,'log'=>$log,'restored'=>$r,'version'=>upd_localVer()]);
        exit;
    }

    if ($aj === 'delete_backup') {
        $name = basename(trim($_POST['backup'] ?? ''));
        if (file_exists(BK_DIR . '/' . $name)) @unlink(BK_DIR . '/' . $name);
        echo json_encode(['ok'=>true]);
        exit;
    }

    if ($aj === 'save_token') {
        $t = preg_replace('/[^a-zA-Z0-9_\-]/','',trim($_POST['token']??''));
        if (strlen($t) < 20) { echo json_encode(['ok'=>false,'error'=>'Token çok kısa']); exit; }
        $ok = @file_put_contents(TOK_FILE, $t) !== false;
        if ($ok) @chmod(TOK_FILE, 0600);
        echo json_encode(['ok'=>$ok]);
        exit;
    }

    if ($aj === 'test_token') {
        if (!$tok) { echo json_encode(['ok'=>false,'error'=>'Token yok']); exit; }
        $d = upd_ghAPI('', $tok);
        if ($d && !empty($d['full_name'])) echo json_encode(['ok'=>true,'repo'=>$d['full_name'],'private'=>$d['private']??false]);
        else echo json_encode(['ok'=>false,'error'=>'Geçersiz token']);
        exit;
    }

    if ($aj === 'migrate') {
        $r = upd_runMigrations();
        echo json_encode(['ok'=>$r['ok'],'log'=>$r['log'],'new'=>$r['new']??0]);
        exit;
    }

    if ($aj === 'history') {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS update_history(id INT AUTO_INCREMENT PRIMARY KEY,version VARCHAR(20),prev_version VARCHAR(20),release_notes TEXT,log_data TEXT,success TINYINT(1) DEFAULT 1,installed_at DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
            $rows = $pdo->query("SELECT version,prev_version,release_notes,success,installed_at FROM update_history ORDER BY installed_at DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['ok'=>true,'history'=>$rows]);
        } catch(Throwable $e) { echo json_encode(['ok'=>false,'error'=>$e->getMessage()]); }
        exit;
    }

    echo json_encode(['ok'=>false,'error'=>'Bilinmeyen işlem']);
    exit;
    } catch (Throwable $e) {
        echo json_encode(['ok'=>false, 'error'=>$e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine()]);
        exit;
    }
}

$tok_tan = upd_getTok();
$cur_ver = upd_localVer();

require __DIR__ . '/_header.php';
?>

<style>
.upd-wrap{font-family:system-ui,-apple-system,sans-serif}
.upd-wrap *{box-sizing:border-box}
.upd-head{margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap}
.upd-head .sub{font-size:13px;color:#6b7280;font-weight:600}
.upd-repo{font-size:11px;color:#6b7280;font-family:'SF Mono',Menlo,monospace}
.upd-tabs{display:flex;border-bottom:1px solid #e5e7eb;margin-bottom:18px;overflow-x:auto;background:#fff;border-radius:10px 10px 0 0;padding:0 4px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.upd-tab{padding:12px 18px;font-size:13px;font-weight:600;border:none;background:none;color:#6b7280;border-bottom:2px solid transparent;cursor:pointer;font-family:inherit;white-space:nowrap;display:flex;align-items:center;gap:7px;transition:all .15s}
.upd-tab:hover{color:#3a5f0b;background:rgba(58,95,11,.05)}
.upd-tab.on{color:#3a5f0b;border-bottom-color:#3a5f0b;background:rgba(58,95,11,.07)}
.upd-body{display:none}.upd-body.on{display:block}
.upd-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.upd-card-h{padding:14px 20px;border-bottom:1px solid #e5e7eb;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;display:flex;align-items:center;gap:8px}
.upd-card-b{padding:20px}
.upd-ver{display:flex;align-items:center;gap:14px;margin-bottom:22px;flex-wrap:wrap}
.upd-vbox{background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px 28px;min-width:150px;text-align:center}
.upd-vlbl{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;font-weight:700;margin-bottom:8px}
.upd-vval{font-size:30px;font-weight:800;font-family:'SF Mono',Menlo,monospace;color:#1f2937;letter-spacing:-.03em;line-height:1}
.upd-arrow{font-size:24px;color:#9ca3af}
.upd-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
.upd-stat{background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:22px 16px;text-align:center;transition:all .15s}
.upd-stat:hover{border-color:#3a5f0b;background:#fff}
.upd-stat-v{font-size:36px;font-weight:800;font-family:'SF Mono',Menlo,monospace;letter-spacing:-.03em;line-height:1;color:#1f2937}
.upd-stat-l{font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;margin-top:8px;font-weight:700}
.upd-act{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.upd-btn{padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:1px solid transparent;font-family:inherit;display:inline-flex;align-items:center;gap:7px;transition:all .15s}
.upd-btn:disabled{opacity:.5;cursor:not-allowed}
.upd-btn-ghost{background:#fff;border-color:#d1d5db;color:#374151}
.upd-btn-ghost:hover{border-color:#3a5f0b;color:#3a5f0b}
.upd-btn-blue{background:#3a5f0b;color:#fff;box-shadow:0 2px 6px rgba(58,95,11,.25)}
.upd-btn-blue:hover{background:#2a4508}
.upd-btn-orange{background:#f59e0b;color:#fff}
.upd-btn-orange:hover{background:#d97706}
.upd-btn-red{background:#fee2e2;color:#dc2626;border-color:#fecaca}
.upd-btn-sm{padding:6px 12px;font-size:11px}
.upd-btn-xs{padding:4px 8px;font-size:10px}
.upd-badge{padding:6px 14px;border-radius:100px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:6px}
.upd-b-green{background:#d1fae5;color:#065f46;border:1px solid #a7f3d0}
.upd-b-blue{background:rgba(58,95,11,.1);color:#3a5f0b;border:1px solid rgba(58,95,11,.3)}
.upd-b-warn{background:#fef3c7;color:#92400e;border:1px solid #fde68a}
.upd-b-red{background:#fee2e2;color:#991b1b;border:1px solid #fecaca}
.upd-fgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:6px;max-height:640px;overflow-y:auto;padding:4px 2px}
.upd-fitem{display:flex;align-items:center;gap:7px;padding:8px 12px;border-radius:6px;background:#f9fafb;border:1px solid transparent;font-size:11px;font-family:'SF Mono',Menlo,monospace;color:#374151}
.upd-fitem.f-ok{border-color:#a7f3d0;background:#ecfdf5}
.upd-fitem.f-diff{border-color:#fecaca;background:#fef2f2}
.upd-fitem.f-miss{border-color:#fde68a;background:#fffbeb}
.upd-fitem .fn{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;min-width:0}
.upd-commit{padding:14px 0;border-bottom:1px solid #e5e7eb}
.upd-commit:last-child{border-bottom:none}
.upd-csha{font-family:'SF Mono',Menlo,monospace;font-size:12px;color:#3a5f0b;font-weight:700}
.upd-cmsg{font-size:14px;font-weight:600;color:#1f2937;margin:4px 0}
.upd-cmeta{font-size:11px;color:#9ca3af}
.upd-bk{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px;border-bottom:1px solid #e5e7eb}
.upd-bk-name{font-family:'SF Mono',Menlo,monospace;font-size:12px;color:#1f2937;word-break:break-all}
.upd-bk-meta{font-size:11px;color:#9ca3af;margin-top:2px}
.upd-inp{padding:10px 14px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;font-family:'SF Mono',Menlo,monospace;width:100%;color:#1f2937}
.upd-inp:focus{outline:none;border-color:#3a5f0b;box-shadow:0 0 0 3px rgba(58,95,11,.1)}
.upd-sect{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;font-weight:700;margin:18px 0 10px}
#upd-ov{position:fixed;inset:0;background:rgba(15,23,42,.85);z-index:9999;display:none;align-items:center;justify-content:center;padding:20px}
#upd-ov.show{display:flex}
.upd-ovbox{background:#fff;border-radius:14px;padding:28px;max-width:560px;width:100%;box-shadow:0 24px 60px rgba(0,0,0,.5)}
.upd-ov-head{display:flex;align-items:center;gap:14px;margin-bottom:18px}
.upd-ovicon{font-size:36px;width:60px;height:60px;background:rgba(58,95,11,.1);border-radius:14px;display:flex;align-items:center;justify-content:center}
.upd-ovtitle{font-size:18px;font-weight:800;color:#1f2937}
.upd-ovsub{font-size:13px;color:#6b7280;margin-top:2px}
.upd-pbar{height:6px;background:#e5e7eb;border-radius:3px;overflow:hidden;margin-bottom:14px}
.upd-pfill{height:100%;background:linear-gradient(90deg,#3a5f0b,#16a34a);width:5%;border-radius:3px;transition:width .6s}
#upd-ovlog{font-family:'SF Mono',Menlo,monospace;font-size:11.5px;background:#0f172a;color:#cbd5e1;padding:14px;border-radius:8px;max-height:280px;overflow-y:auto;margin-bottom:14px;line-height:1.7}
#upd-ovlog .ol-ok{color:#86efac}
#upd-ovlog .ol-err{color:#fca5a5}
#upd-ovlog .ol-info{color:#93c5fd}
.upd-ovact{display:none}
.upd-toast{position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;box-shadow:0 8px 28px rgba(0,0,0,.2);z-index:10000}
</style>

<div class="upd-wrap">
  <div class="upd-head">
    <div><div class="sub">Güncelleme Merkezi v5.0 — Git SHA Senkronizasyon</div></div>
    <div class="upd-repo"><?= GH_REPO ?> · <?= GH_BRANCH ?></div>
  </div>

  <div class="upd-tabs">
    <button class="upd-tab on" onclick="updTab('overview',this)"><i class="fa-solid fa-satellite-dish"></i> Genel Durum</button>
    <button class="upd-tab" onclick="updTab('files',this)"><i class="fa-solid fa-folder-tree"></i> Dosyalar</button>
    <button class="upd-tab" onclick="updTab('commits',this)"><i class="fa-solid fa-code-commit"></i> Commits</button>
    <button class="upd-tab" onclick="updTab('backups',this)"><i class="fa-solid fa-box-archive"></i> Yedekler</button>
    <button class="upd-tab" onclick="updTab('database',this)"><i class="fa-solid fa-database"></i> Database</button>
    <button class="upd-tab" onclick="updTab('settings',this)"><i class="fa-solid fa-gear"></i> Ayarlar</button>
  </div>

  <div id="upd-overview" class="upd-body on">
    <div class="upd-card">
      <div class="upd-card-h"><i class="fa-solid fa-code-branch"></i> REPOSITORY STATUS — <?= GH_BRANCH ?> BRANCH</div>
      <div class="upd-card-b">
        <div class="upd-ver">
          <div class="upd-vbox"><div class="upd-vlbl">LOCAL</div><div class="upd-vval" id="upd-vlocal"><?= htmlspecialchars($cur_ver) ?></div></div>
          <span class="upd-arrow">→</span>
          <div class="upd-vbox"><div class="upd-vlbl">GITHUB</div><div class="upd-vval" id="upd-vremote">…</div></div>
          <div id="upd-vbadge"><span class="upd-badge upd-b-blue">Kontrol ediliyor…</span></div>
        </div>
        <div class="upd-stats">
          <div class="upd-stat"><div class="upd-stat-v" id="upd-sok" style="color:#16a34a">—</div><div class="upd-stat-l">Up to Date</div></div>
          <div class="upd-stat"><div class="upd-stat-v" id="upd-sdiff" style="color:#dc2626">—</div><div class="upd-stat-l">Changed</div></div>
          <div class="upd-stat"><div class="upd-stat-v" id="upd-smiss" style="color:#f59e0b">—</div><div class="upd-stat-l">Missing</div></div>
          <div class="upd-stat"><div class="upd-stat-v" id="upd-stot" style="color:#6b7280">—</div><div class="upd-stat-l">Total</div></div>
        </div>
        <div class="upd-act">
          <button id="upd-btn-check" class="upd-btn upd-btn-ghost" onclick="updCheck()"><i class="fa-solid fa-satellite-dish"></i> Check Status</button>
          <button id="upd-btn-sync" class="upd-btn upd-btn-blue" onclick="updSync()"><i class="fa-solid fa-arrow-up"></i> Smart Update</button>
          <button id="upd-btn-force" class="upd-btn upd-btn-orange" onclick="updForce()"><i class="fa-solid fa-fire"></i> Force Update</button>
          <?php if (!$tok_tan): ?>
          <span class="upd-badge upd-b-warn" style="margin-left:auto"><i class="fa-solid fa-triangle-exclamation"></i> Token ayarlanmamış</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div id="upd-files" class="upd-body">
    <div class="upd-card">
      <div class="upd-card-h"><i class="fa-solid fa-file-code"></i> DOSYA DURUMU</div>
      <div class="upd-card-b">
        <p id="upd-fmsg" style="color:#9ca3af;font-size:13px;margin-bottom:12px">Önce "Check Status" çalıştırın.</p>
        <div class="upd-fgrid" id="upd-fgrid"></div>
      </div>
    </div>
  </div>

  <div id="upd-commits" class="upd-body">
    <div class="upd-card">
      <div class="upd-card-h"><i class="fa-solid fa-code-commit"></i> SON COMMITLER</div>
      <div class="upd-card-b"><div id="upd-clist"><p style="color:#9ca3af;padding:14px;text-align:center">Yükleniyor…</p></div></div>
    </div>
  </div>

  <div id="upd-backups" class="upd-body">
    <div class="upd-card">
      <div class="upd-card-h"><i class="fa-solid fa-box-archive"></i> YEDEKLER <span id="upd-bkcnt" style="margin-left:auto;color:#9ca3af;font-weight:500"></span></div>
      <div class="upd-card-b">
        <p style="color:#9ca3af;font-size:12px;margin-bottom:12px">Her güncelleme öncesi otomatik yedek alınır. Son <?= MAX_BK ?> yedek tutulur.</p>
        <div id="upd-blist"><p style="color:#9ca3af;padding:14px;text-align:center">Yükleniyor…</p></div>
      </div>
    </div>
  </div>

  <div id="upd-database" class="upd-body">
    <div class="upd-card">
      <div class="upd-card-h"><i class="fa-solid fa-database"></i> DATABASE MIGRATION</div>
      <div class="upd-card-b">
        <p style="color:#9ca3af;font-size:13px;margin-bottom:14px">Kod güncellemesinde otomatik çalışır. Manuel için butonu kullanın.</p>
        <div class="upd-act" style="margin-bottom:18px">
          <button class="upd-btn upd-btn-blue" onclick="updMigrate(this)"><i class="fa-solid fa-play"></i> Migration'ları Çalıştır</button>
        </div>
        <div id="upd-mlog" style="display:none;background:#0f172a;color:#cbd5e1;border-radius:8px;padding:12px;font-family:'SF Mono',Menlo,monospace;font-size:12px;max-height:300px;overflow-y:auto;line-height:1.8"></div>
      </div>
    </div>
    <div class="upd-card">
      <div class="upd-card-h"><i class="fa-solid fa-clock-rotate-left"></i> GÜNCELLEME GEÇMİŞİ</div>
      <div class="upd-card-b"><div id="upd-hist"><p style="color:#9ca3af;padding:14px;text-align:center">Yükleniyor…</p></div></div>
    </div>
  </div>

  <div id="upd-settings" class="upd-body">
    <div class="upd-card">
      <div class="upd-card-h"><i class="fa-solid fa-gear"></i> AYARLAR</div>
      <div class="upd-card-b">
        <div class="upd-sect">GitHub Personal Access Token</div>
        <p style="color:#6b7280;font-size:13px;line-height:1.7;margin-bottom:12px">
          Yüksek rate-limit ve private repo erişimi için gerekli.<br>
          GitHub → Settings → Developer settings → Personal access tokens → Fine-grained.<br>
          Yetki: <code style="background:#f3f4f6;padding:2px 6px;border-radius:4px;font-size:12px">Contents: Read-only</code>
        </p>
        <div style="margin-bottom:10px">
          <input type="password" id="upd-tok" class="upd-inp" placeholder="ghp_xxxxxxxxxxxxxxxxxxxx"
                 value="<?= $tok_tan ? str_repeat('•',8) . substr($tok_tan,-4) : '' ?>">
        </div>
        <div class="upd-act">
          <button class="upd-btn upd-btn-blue upd-btn-sm" onclick="updSaveTok()"><i class="fa-solid fa-save"></i> Kaydet</button>
          <button class="upd-btn upd-btn-ghost upd-btn-sm" onclick="updTestTok()"><i class="fa-solid fa-flask"></i> Test Et</button>
          <span id="upd-tokmsg" style="font-size:12px;margin-left:8px"></span>
        </div>
        <div class="upd-sect">Korumalı Dosyalar</div>
        <p style="color:#6b7280;font-size:12px;margin-bottom:10px">Bu yollar hiçbir güncellemede overwrite edilmez:</p>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:3px;font-family:'SF Mono',Menlo,monospace;font-size:11px;color:#6b7280">
          <?php foreach (UPD_EXCLUDES as $ex): ?><div>• <?= htmlspecialchars($ex) ?></div><?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="upd-ov"><div class="upd-ovbox">
  <div class="upd-ov-head">
    <div class="upd-ovicon" id="upd-ovicon">⚙️</div>
    <div><div class="upd-ovtitle" id="upd-ovtitle">İşleniyor…</div><div class="upd-ovsub" id="upd-ovsub"></div></div>
  </div>
  <div class="upd-pbar"><div class="upd-pfill" id="upd-pfill"></div></div>
  <div id="upd-ovlog"></div>
  <div class="upd-ovact" id="upd-ovact"><button class="upd-btn upd-btn-blue" onclick="updOvClose()">Kapat</button></div>
</div></div>

<script>
function $u(id){return document.getElementById(id);}
function updToast(msg,c){var t=document.createElement('div');t.className='upd-toast';t.textContent=msg;
  var cs={green:'background:#d1fae5;color:#065f46',red:'background:#fee2e2;color:#991b1b',blue:'background:#dbeafe;color:#1e40af'};
  t.style.cssText+=cs[c]||cs.blue;document.body.appendChild(t);setTimeout(()=>t.remove(),2500);}
function updTab(n,b){document.querySelectorAll('.upd-tab').forEach(t=>t.classList.remove('on'));document.querySelectorAll('.upd-body').forEach(t=>t.classList.remove('on'));if(b)b.classList.add('on');$u('upd-'+n)?.classList.add('on');
  if(n==='commits')updLoadCommits();if(n==='backups')updLoadBackups();if(n==='database')updLoadHistory();}
function updOv(i,t,s){$u('upd-ovicon').textContent=i;$u('upd-ovtitle').textContent=t;$u('upd-ovsub').textContent=s||'';$u('upd-ovlog').innerHTML='';$u('upd-pfill').style.width='5%';$u('upd-ovact').style.display='none';$u('upd-ov').className='show';}
function updBar(p){$u('upd-pfill').style.width=Math.min(100,p)+'%';}
function updOvLine(t,c){var d=document.createElement('div');d.className='ol-'+(c||'info');d.textContent=t;$u('upd-ovlog').appendChild(d);$u('upd-ovlog').scrollTop=99999;}
function updOvDone(i,t,s){$u('upd-ovicon').textContent=i;$u('upd-ovtitle').textContent=t;$u('upd-ovsub').textContent=s||'';updBar(100);$u('upd-ovact').style.display='block';}
function updOvClose(){$u('upd-ov').className='';}
async function updFJ(u,o){var r=await fetch(u,Object.assign({credentials:'same-origin'},o||{}));var t=await r.text();if(!r.ok)throw new Error('HTTP '+r.status);try{return JSON.parse(t);}catch(e){throw new Error('JSON: '+t.substring(0,200));}}
function updU(a){return '?upd_ajax='+a;}

async function updCheck(){
  $u('upd-btn-check').disabled=true;$u('upd-vbadge').innerHTML='<span class="upd-badge upd-b-blue"><i class="fa-solid fa-spinner fa-spin"></i> Kontrol…</span>';
  try{
    var d=await updFJ(updU('status'));
    if(!d.ok){$u('upd-vbadge').innerHTML='<span class="upd-badge upd-b-red">❌ '+d.error+'</span>';return;}
    $u('upd-vremote').textContent=d.remote_ver||'?';
    $u('upd-sok').textContent=d.stats.ok;$u('upd-sdiff').textContent=d.stats.diff;$u('upd-smiss').textContent=d.stats.missing;$u('upd-stot').textContent=d.total;
    $u('upd-vbadge').innerHTML=d.needs_update?'<span class="upd-badge upd-b-warn"><i class="fa-solid fa-triangle-exclamation"></i> '+(d.stats.diff+d.stats.missing)+' dosya güncel değil</span>':'<span class="upd-badge upd-b-green"><i class="fa-solid fa-check-circle"></i> Fully up to date</span>';
    updRenderFiles(d.files);
  }catch(e){$u('upd-vbadge').innerHTML='<span class="upd-badge upd-b-red">❌ '+e.message+'</span>';}
  finally{$u('upd-btn-check').disabled=false;}
}
function updRenderFiles(files){
  if(!files||!Object.keys(files).length){$u('upd-fgrid').innerHTML='';$u('upd-fmsg').textContent='Dosya yok';return;}
  $u('upd-fmsg').style.display='none';
  var I={ok:'✅',diff:'🔴',missing:'🟡'},C={ok:'f-ok',diff:'f-diff',missing:'f-miss'};
  $u('upd-fgrid').innerHTML=Object.entries(files).map(function(e){
    var f=e[0],s=e[1];
    var b=(s.status==='diff'||s.status==='missing')?'<button class="upd-btn upd-btn-xs upd-btn-blue" onclick="updUpdOne(\''+f+'\',this)">Güncelle</button>':'';
    return '<div class="upd-fitem '+C[s.status]+'" title="'+f+'">'+I[s.status]+'<span class="fn">'+f+'</span>'+b+'</div>';
  }).join('');
}
async function updUpdOne(f,b){b.disabled=true;b.textContent='…';var fd=new FormData();fd.append('file',f);
  try{var d=await updFJ(updU('update_file'),{method:'POST',body:fd});
  if(d.ok){b.textContent='✅';updToast(f+' güncellendi','green');}else{b.textContent='❌';updToast(d.error,'red');}}
  catch(e){b.textContent='❌';updToast(e.message,'red');}}

async function updSync(){
  if(!confirm('Akıllı Güncelleme:\nSadece değişen dosyalar indirilecek. Önce otomatik yedek alınır.\nDB migration varsa otomatik çalıştırılır.\n\nDevam?'))return;
  updOv('⬆','Akıllı Güncelleme','GitHub ile senkronize ediliyor');$u('upd-btn-sync').disabled=true;updBar(20);updOvLine('🔗 GitHub bağlanıyor…','info');
  try{var d=await updFJ(updU('sync'));updBar(85);
    (d.log||[]).forEach(l=>updOvLine(l,l.startsWith('✅')?'ok':l.startsWith('❌')?'err':'info'));
    (d.errors||[]).forEach(l=>updOvLine(l,'err'));
    if(d.updated>0){updOvDone('✅','Tamamlandı!',d.updated+' dosya · v'+(d.version||'?'));$u('upd-vlocal').textContent=d.version||'?';
      setTimeout(()=>{updOvClose();updCheck();updLoadBackups();},2500);}
    else if((d.errors||[]).length>0){updOvDone('⚠️','Hatalarla tamamlandı','Log\'u kontrol edin');}
    else{updOvDone('✨','Zaten Güncel','Tüm dosyalar GitHub ile eşleşiyor');setTimeout(updOvClose,2000);}
  }catch(e){updOvLine('❌ '+e.message,'err');updOvDone('❌','Başarısız',e.message);}
  finally{$u('upd-btn-sync').disabled=false;}
}
async function updForce(){
  if(!confirm('⚠️ ZORLA GÜNCELLE\nTÜM dosyalar değişecek. Yerel değişiklikler kaybolur (önce yedek alınır).\nKorumalı dosyalar (config.php, uploads/) güvende.\n\nDevam?'))return;
  if(!confirm('🔥 SON UYARI\nTüm yerel değişiklikler silinecek. Devam?'))return;
  updOv('🔥','Zorla Güncelle','TÜM dosyalar yeniden indiriliyor');$u('upd-btn-force').disabled=true;updBar(15);updOvLine('🔗 GitHub…','info');
  try{var d=await updFJ(updU('force_sync'));updBar(85);
    (d.log||[]).forEach(l=>updOvLine(l,l.startsWith('✅')?'ok':l.startsWith('❌')?'err':'info'));
    (d.errors||[]).forEach(l=>updOvLine(l,'err'));
    updOvDone('✅','Tamamlandı!',d.updated+' dosya · v'+(d.version||'?'));
    $u('upd-vlocal').textContent=d.version||'?';
    setTimeout(()=>{updOvClose();updCheck();updLoadBackups();},2800);
  }catch(e){updOvLine('❌ '+e.message,'err');updOvDone('❌','Başarısız',e.message);}
  finally{$u('upd-btn-force').disabled=false;}
}

var _updCL=false;
async function updLoadCommits(){
  if(_updCL)return;
  try{var d=await updFJ(updU('commits'));
  if(!d.ok){$u('upd-clist').innerHTML='<div style="color:#dc2626;padding:14px">'+d.error+'</div>';return;}
  if(!d.commits.length){$u('upd-clist').innerHTML='<div style="color:#9ca3af;padding:14px">Commit yok</div>';return;}
  $u('upd-clist').innerHTML=d.commits.map(function(c){
    var dt=c.date?new Date(c.date).toLocaleString('tr-TR',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}):'';
    var L=c.message.split('\n');var T=L[0];var B=L.slice(1).join('\n').trim();
    return '<div class="upd-commit"><div style="display:flex;justify-content:space-between;gap:8px"><span class="upd-csha">'+c.sha+'</span><span class="upd-cmeta">'+dt+'</span></div><div class="upd-cmsg">'+T.replace(/</g,'&lt;')+'</div>'+(B?'<div style="font-size:12px;color:#6b7280;margin-top:3px;white-space:pre-wrap;line-height:1.6">'+B.replace(/</g,'&lt;')+'</div>':'')+'<div class="upd-cmeta">'+c.author+'</div></div>';
  }).join('');_updCL=true;}
  catch(e){$u('upd-clist').innerHTML='<div style="color:#dc2626;padding:14px">'+e.message+'</div>';}
}

async function updLoadBackups(){
  try{var d=await updFJ(updU('backups'));var B=d.backups||[];
  $u('upd-bkcnt').textContent='('+B.length+')';
  if(!B.length){$u('upd-blist').innerHTML='<p style="color:#9ca3af;padding:14px;text-align:center">Henüz yedek yok</p>';return;}
  $u('upd-blist').innerHTML=B.map(function(b){
    var dt=new Date(b.time*1000).toLocaleString('tr-TR',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
    var sz=b.size>1048576?(b.size/1048576).toFixed(1)+' MB':(b.size/1024).toFixed(0)+' KB';
    return '<div class="upd-bk"><div style="min-width:0"><div class="upd-bk-name">'+b.name+'</div><div class="upd-bk-meta">'+dt+' · '+sz+' · v'+(b.ver||'?')+'</div></div><div style="display:flex;gap:5px;flex-shrink:0"><button class="upd-btn upd-btn-xs upd-btn-blue" onclick="updRestore(\''+b.name+'\')"><i class="fa-solid fa-rotate-left"></i> Geri Yükle</button><button class="upd-btn upd-btn-xs upd-btn-red" onclick="updDelBk(\''+b.name+'\',this)"><i class="fa-solid fa-trash"></i></button></div></div>';
  }).join('');}
  catch(e){$u('upd-blist').innerHTML='<div style="color:#dc2626;padding:14px">'+e.message+'</div>';}
}
async function updRestore(name){
  if(!confirm('♻️ '+name+' yedeğinden geri yüklenecek. Mevcut durumun güvenlik yedeği alınır.\n\nDevam?'))return;
  updOv('♻️','Geri Yükleniyor',name);updBar(15);updOvLine('📦 Güvenlik yedeği…','info');
  try{var fd=new FormData();fd.append('backup',name);var d=await updFJ(updU('restore'),{method:'POST',body:fd});updBar(90);
  (d.log||[]).forEach(l=>updOvLine(l,l.startsWith('✅')?'ok':l.startsWith('❌')?'err':'info'));
  if(d.ok){updOvDone('✅','Tamam!',d.restored+' dosya');setTimeout(()=>{updOvClose();updCheck();updLoadBackups();},2500);}
  else updOvDone('❌','Başarısız',d.error||'Hata');}
  catch(e){updOvLine('❌ '+e.message,'err');updOvDone('❌','Başarısız',e.message);}
}
async function updDelBk(name,btn){if(!confirm('Silinsin mi?\n'+name))return;btn.disabled=true;var fd=new FormData();fd.append('backup',name);await updFJ(updU('delete_backup'),{method:'POST',body:fd});updToast('Silindi','red');updLoadBackups();}

async function updMigrate(btn){
  if(!confirm('DB migration\'ları çalıştırılacak. Devam?'))return;
  btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Çalışıyor…';
  $u('upd-mlog').style.display='block';$u('upd-mlog').innerHTML='<div style="color:#9ca3af">🔄 Çalışıyor…</div>';
  try{var d=await updFJ(updU('migrate'));
  $u('upd-mlog').innerHTML=(d.log||[]).map(l=>'<div>'+l+'</div>').join('');
  updToast(d.ok?(d.new+' yeni migration uygulandı'):'Hata',d.ok?'green':'red');}
  catch(e){$u('upd-mlog').innerHTML='<div style="color:#fca5a5">❌ '+e.message+'</div>';updToast(e.message,'red');}
  finally{btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-play"></i> Migration\'ları Çalıştır';}
}
async function updLoadHistory(){
  try{var d=await updFJ(updU('history'));
  if(!d.ok||!d.history.length){$u('upd-hist').innerHTML='<div style="color:#9ca3af;padding:14px;text-align:center">Henüz güncelleme geçmişi yok</div>';return;}
  $u('upd-hist').innerHTML=d.history.map(function(h){
    var dt=new Date(h.installed_at.replace(' ','T')).toLocaleString('tr-TR',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
    return '<div style="padding:10px 0;border-bottom:1px solid #e5e7eb"><div style="font-size:12px;font-family:\'SF Mono\',Menlo,monospace;color:'+(h.success==1?'#16a34a':'#dc2626')+';font-weight:700">v'+h.version+' · '+h.prev_version+' → '+h.version+'</div><div style="font-size:13px;margin-top:2px;color:#1f2937">'+(h.release_notes||'').replace(/</g,'&lt;')+'</div><div style="font-size:11px;color:#9ca3af">'+dt+'</div></div>';
  }).join('');}
  catch(e){$u('upd-hist').innerHTML='<div style="color:#dc2626;padding:14px">'+e.message+'</div>';}
}
async function updSaveTok(){
  var v=$u('upd-tok').value.trim();
  if(!v||v.includes('•')){updToast('Önce token girin','red');return;}
  var fd=new FormData();fd.append('token',v);
  try{var d=await updFJ(updU('save_token'),{method:'POST',body:fd});
  $u('upd-tokmsg').innerHTML=d.ok?'<span style="color:#16a34a"><i class="fa-solid fa-check-circle"></i> Kaydedildi</span>':'<span style="color:#dc2626"><i class="fa-solid fa-xmark-circle"></i> Hata</span>';
  if(d.ok)setTimeout(()=>location.reload(),1200);}
  catch(e){updToast(e.message,'red');}
}
async function updTestTok(){
  $u('upd-tokmsg').innerHTML='<span style="color:#3a5f0b"><i class="fa-solid fa-spinner fa-spin"></i> Test…</span>';
  try{var d=await updFJ(updU('test_token'));
  $u('upd-tokmsg').innerHTML=d.ok?'<span style="color:#16a34a"><i class="fa-solid fa-check-circle"></i> '+d.repo+'</span>':'<span style="color:#dc2626"><i class="fa-solid fa-xmark-circle"></i> '+d.error+'</span>';}
  catch(e){$u('upd-tokmsg').innerHTML='<span style="color:#dc2626">❌ '+e.message+'</span>';}
}
updCheck();updLoadBackups();
</script>

<?php require __DIR__ . '/_footer.php'; ?>
