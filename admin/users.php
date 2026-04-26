<?php
declare(strict_types=1);
$page_h = 'Yöneticiler';
require __DIR__ . '/_header.php';

$cu = admin_user();
if ($cu['role'] !== 'superadmin') {
    flash_set('error', 'Bu sayfaya yalnızca süper yöneticiler erişebilir.');
    header('Location: index.php'); exit;
}

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_required();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $username = clean_multi($_POST['username'] ?? '');
        $name     = clean_multi($_POST['name'] ?? '');
        $email    = clean_multi($_POST['email'] ?? '');
        $role     = $_POST['role'] === 'superadmin' ? 'superadmin' : 'editor';
        $active   = isset($_POST['is_active']) ? 1 : 0;
        $pass     = (string)($_POST['password'] ?? '');

        if (mb_strlen($username) < 3) { flash_set('error','Kullanıcı adı en az 3 karakter.'); }
        elseif (mb_strlen($name) < 2) { flash_set('error','Ad-Soyad zorunlu.'); }
        elseif ($id === 0 && mb_strlen($pass) < 8) { flash_set('error','Yeni kullanıcı için parola en az 8 karakter olmalı.'); }
        elseif ($pass !== '' && mb_strlen($pass) < 8) { flash_set('error','Parola en az 8 karakter olmalı.'); }
        else {
            if ($id) {
                if ($pass) {
                    $hash = password_hash($pass, PASSWORD_BCRYPT);
                    $pdo->prepare("UPDATE admin_users SET username=?,password_hash=?,name=?,email=?,role=?,is_active=? WHERE id=?")
                        ->execute([$username,$hash,$name,$email ?: null,$role,$active,$id]);
                } else {
                    $pdo->prepare("UPDATE admin_users SET username=?,name=?,email=?,role=?,is_active=? WHERE id=?")
                        ->execute([$username,$name,$email ?: null,$role,$active,$id]);
                }
                log_activity('user_updated', null, "ID: $id");
                flash_set('success','Kullanıcı güncellendi.');
            } else {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $pdo->prepare("INSERT INTO admin_users (username,password_hash,name,email,role,is_active) VALUES (?,?,?,?,?,?)")
                    ->execute([$username,$hash,$name,$email ?: null,$role,$active]);
                log_activity('user_created', null, "Username: $username");
                flash_set('success','Kullanıcı eklendi.');
            }
            header('Location: users.php'); exit;
        }
    } elseif ($action === 'delete' && $id) {
        if ($id === (int)$cu['id']) {
            flash_set('error','Kendinizi silemezsiniz.');
        } else {
            $pdo->prepare("DELETE FROM admin_users WHERE id=?")->execute([$id]);
            log_activity('user_deleted', null, "ID: $id");
            flash_set('success','Kullanıcı silindi.');
        }
        header('Location: users.php'); exit;
    } elseif ($action === 'toggle' && $id) {
        if ($id === (int)$cu['id']) { flash_set('error','Kendinizi devre dışı bırakamazsınız.'); }
        else { $pdo->prepare("UPDATE admin_users SET is_active = 1 - is_active WHERE id=?")->execute([$id]); }
        header('Location: users.php'); exit;
    }
}

$action = $_GET['action'] ?? 'list';
$row = null;
if ($action === 'edit' && !empty($_GET['id'])) {
    $st = $pdo->prepare("SELECT * FROM admin_users WHERE id=?");
    $st->execute([(int)$_GET['id']]);
    $row = $st->fetch() ?: null;
}

$rows = $pdo->query("SELECT * FROM admin_users ORDER BY id")->fetchAll();
?>

<?php if ($action === 'list'): ?>
  <div class="card">
    <h2 style="display:flex;justify-content:space-between;align-items:center">
      <span>Yönetici Hesapları</span>
      <a href="?action=new" class="btn btn-sm">+ Yeni Yönetici</a>
    </h2>
    <table>
      <thead><tr><th>#</th><th>Kullanıcı</th><th>Ad-Soyad</th><th>E-posta</th><th>Rol</th><th>Son Giriş</th><th>Durum</th><th>İşlem</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><strong><?= e($r['username']) ?></strong><?php if ($r['id'] == $cu['id']): ?> <small style="color:#3A5F0B">(siz)</small><?php endif; ?></td>
          <td><?= e($r['name']) ?></td>
          <td><?= e($r['email'] ?? '—') ?></td>
          <td><span class="badge b-info"><?= e($r['role']) ?></span></td>
          <td style="font-size:11px"><?= $r['last_login_at'] ? format_date($r['last_login_at']) : '—' ?></td>
          <td><span class="badge <?= $r['is_active'] ? 'b-on' : 'b-off' ?>"><?= $r['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
          <td class="actions">
            <a class="btn btn-sm btn-line" href="?action=edit&id=<?= $r['id'] ?>"><i class="fa-solid fa-pen"></i></a>
            <?php if ($r['id'] != $cu['id']): ?>
              <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-secondary"><i class="fa-solid fa-power-off"></i></button></form>
              <form method="post" data-confirm="Bu kullanıcı silinsin mi?" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button></form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php else: ?>
  <div class="card">
    <h2><?= $row ? 'Düzenle: ' . e($row['username']) : 'Yeni Yönetici' ?></h2>
    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $row['id'] ?? '' ?>">
      <div class="grid-2">
        <div class="row"><label>Kullanıcı Adı *</label><input type="text" name="username" value="<?= e($row['username'] ?? '') ?>" required minlength="3"></div>
        <div class="row"><label>Ad-Soyad *</label><input type="text" name="name" value="<?= e($row['name'] ?? '') ?>" required></div>
      </div>
      <div class="grid-2">
        <div class="row"><label>E-posta</label><input type="email" name="email" value="<?= e($row['email'] ?? '') ?>"></div>
        <div class="row">
          <label>Rol</label>
          <select name="role">
            <option value="editor"     <?= ($row['role'] ?? '') === 'editor'     ? 'selected':'' ?>>Editör</option>
            <option value="superadmin" <?= ($row['role'] ?? '') === 'superadmin' ? 'selected':'' ?>>Süper Yönetici</option>
          </select>
        </div>
      </div>
      <div class="row"><label>Parola <?= $row ? '(değiştirmek için doldurun)' : '*' ?></label><input type="password" name="password" <?= $row ? '' : 'required minlength="8"' ?> minlength="8" autocomplete="new-password"></div>
      <div class="row"><label class="toggle"><input type="checkbox" name="is_active" value="1"<?= ($row['is_active'] ?? 1) ? ' checked':'' ?>> Aktif</label></div>
      <button type="submit" class="btn">Kaydet</button>
      <a href="users.php" class="btn btn-secondary">İptal</a>
    </form>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
