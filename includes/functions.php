<?php
declare(strict_types=1);

/**
 * Yardımcı fonksiyonlar – Le Monde Du Tacos
 */

require_once __DIR__ . '/db.php';

/* ================== ESC / SANITIZE ================== */

function e(?string $s): string {
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function clean(?string $s): string {
    return trim(strip_tags((string)($s ?? '')));
}

function clean_multi(?string $s): string {
    return trim(preg_replace('/\s+/u', ' ', strip_tags((string)($s ?? ''))));
}

function nl2br_safe(?string $s): string {
    return nl2br(e($s));
}

/* ================== AYARLAR (settings) ================== */

function settings(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = [];
    try {
        $rows = db()->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
        foreach ($rows as $r) {
            $cache[$r['setting_key']] = $r['setting_value'];
        }
    } catch (Throwable $e) {
        // tablolar henüz oluşmadıysa sessizce geç
    }
    return $cache;
}

function setting(string $key, string $default = ''): string {
    $s = settings();
    return $s[$key] ?? $default;
}

function set_setting(string $key, string $value): void {
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    db()->prepare($sql)->execute([$key, $value]);
}

/* ================== CSRF ================== */

function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function csrf_token(): string {
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): bool {
    start_session();
    $t = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return is_string($t) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

function csrf_required(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_check()) {
        http_response_code(419);
        die('Oturum doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.');
    }
}

/* ================== FLASH MESAJ ================== */

function flash_set(string $type, string $msg): void {
    start_session();
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): array {
    start_session();
    $out = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $out;
}

function flash_render(): string {
    $items = flash_get();
    if (!$items) return '';
    $html = '';
    foreach ($items as $f) {
        $cls = match ($f['type']) {
            'success' => 'flash-success',
            'error'   => 'flash-error',
            'warning' => 'flash-warning',
            default   => 'flash-info',
        };
        $html .= '<div class="flash ' . $cls . '">' . e($f['msg']) . '</div>';
    }
    return $html;
}

/* ================== ADMIN AUTH ================== */

function admin_check(): bool {
    start_session();
    if (empty($_SESSION['admin_id'])) return false;
    if (!empty($_SESSION['admin_last']) && (time() - (int)$_SESSION['admin_last']) > SESSION_LIFETIME) {
        admin_logout();
        return false;
    }
    $_SESSION['admin_last'] = time();
    return true;
}

function admin_require(): void {
    if (!admin_check()) {
        $back = urlencode($_SERVER['REQUEST_URI'] ?? '/' . ADMIN_PATH . '/');
        header('Location: /' . ADMIN_PATH . '/login.php?back=' . $back);
        exit;
    }
}

function admin_login(int $user_id, string $username): void {
    start_session();
    session_regenerate_id(true);
    $_SESSION['admin_id']       = $user_id;
    $_SESSION['admin_user']     = $username;
    $_SESSION['admin_last']     = time();
    $_SESSION['admin_login_at'] = time();
}

function admin_logout(): void {
    start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function admin_user(): ?array {
    if (!admin_check()) return null;
    static $cache = null;
    if ($cache !== null) return $cache;
    $stmt = db()->prepare("SELECT id, username, name, email, role FROM admin_users WHERE id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['admin_id']]);
    $cache = $stmt->fetch() ?: null;
    if ($cache === null) {
        admin_logout();
    }
    return $cache;
}

/* ================== SLUG ================== */

function slugify(string $text): string {
    $tr = ['ç','Ç','ğ','Ğ','ı','İ','ö','Ö','ş','Ş','ü','Ü'];
    $en = ['c','c','g','g','i','i','o','o','s','s','u','u'];
    $text = str_replace($tr, $en, $text);
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return $text ?: 'item-' . substr(md5((string)microtime(true)), 0, 6);
}

/* ================== UPLOAD ================== */

function upload_file(string $field, string $subdir, array $allowed = ALLOWED_IMG): ?string {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        flash_set('error', 'Dosya yükleme hatası (kod: ' . $f['error'] . ').');
        return null;
    }
    if ($f['size'] > MAX_UPLOAD_MB * 1024 * 1024) {
        flash_set('error', 'Dosya çok büyük (max ' . MAX_UPLOAD_MB . ' MB).');
        return null;
    }
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        flash_set('error', 'Geçersiz dosya türü: .' . $ext);
        return null;
    }
    $dir = UPLOAD_DIR . '/' . $subdir;
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $name = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($f['tmp_name'], $dest)) {
        flash_set('error', 'Dosya kaydedilemedi.');
        return null;
    }
    @chmod($dest, 0644);
    return UPLOAD_URL . '/' . $subdir . '/' . $name;
}

function delete_upload(?string $url): void {
    if (!$url) return;
    $rel = ltrim(parse_url($url, PHP_URL_PATH) ?: '', '/');
    if (str_starts_with($rel, 'uploads/')) {
        $path = __DIR__ . '/../' . $rel;
        if (is_file($path)) @unlink($path);
    }
}

/* ================== AKTİVİTE LOG ================== */

function log_activity(string $action, ?int $admin_id = null, ?string $detail = null): void {
    try {
        $stmt = db()->prepare(
            "INSERT INTO activity_log (admin_id, action, detail, ip, user_agent, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $admin_id ?? ($_SESSION['admin_id'] ?? null),
            mb_substr($action, 0, 100),
            $detail ? mb_substr($detail, 0, 500) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    } catch (Throwable $e) {
        // tablolar henüz yoksa sessizce geç
    }
}

/* ================== URL / TARİH ================== */

function url(string $path = ''): string {
    return SITE_URL . '/' . ltrim($path, '/');
}

function format_date(string $datetime, string $fmt = 'd.m.Y H:i'): string {
    try {
        return (new DateTimeImmutable($datetime))->format($fmt);
    } catch (Throwable) {
        return '-';
    }
}

/* ================== ASSETS ================== */

function asset(?string $path): string {
    if (!$path) return '';
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return SITE_URL . '/' . ltrim($path, '/');
}

/* ================== SAYFALAMA ================== */

function paginate(int $total, int $per_page, int $current): array {
    $pages = max(1, (int)ceil($total / $per_page));
    $current = max(1, min($pages, $current));
    return [
        'total'   => $total,
        'pages'   => $pages,
        'current' => $current,
        'offset'  => ($current - 1) * $per_page,
        'per'     => $per_page,
    ];
}

/* ================== E-POSTA ================== */

function send_mail(string $to, string $subject, string $body): bool {
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . mb_encode_mimeheader(MAIL_NAME) . ' <' . MAIL_FROM . '>',
        'Reply-To: ' . MAIL_FROM,
        'X-Mailer: PHP/' . PHP_VERSION,
    ];
    $subject = mb_encode_mimeheader($subject, 'UTF-8');
    return @mail($to, $subject, $body, implode("\r\n", $headers));
}

/* ================== JSON YANIT ================== */

function json_response(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/* ================== IP / RATE LIMIT ================== */

function client_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            return filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';
        }
    }
    return '0.0.0.0';
}

function rate_limit(string $key, int $limit, int $window): bool {
    try {
        $stmt = db()->prepare(
            "SELECT COUNT(*) FROM rate_limits
             WHERE rl_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)"
        );
        $stmt->execute([$key, $window]);
        $cnt = (int)$stmt->fetchColumn();
        if ($cnt >= $limit) return false;
        db()->prepare("INSERT INTO rate_limits (rl_key, ip, created_at) VALUES (?, ?, NOW())")
            ->execute([$key, client_ip()]);
        // eski kayıtları temizle (rastgele %5 ihtimalle)
        if (random_int(1, 20) === 1) {
            db()->prepare("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)")->execute();
        }
        return true;
    } catch (Throwable) {
        return true;
    }
}
