<?php
declare(strict_types=1);
$page_h = 'Menü Yönetimi';
require __DIR__ . '/_header.php';
require __DIR__ . '/_crud.php';

$tab = $_GET['tab'] ?? 'items';
$pdo = db();

// POST aksiyonları
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_required();
    $entity = $_POST['entity'] ?? '';
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    $tables = ['group' => 'menu_groups', 'cat' => 'menu_categories', 'item' => 'menu_items'];
    $tbl = $tables[$entity] ?? null;

    if ($tbl) {
        if ($action === 'delete' && $id) {
            // resmi sil
            if ($entity === 'item') {
                $st = $pdo->prepare("SELECT image FROM menu_items WHERE id=?");
                $st->execute([$id]);
                delete_upload((string)$st->fetchColumn());
            }
            $pdo->prepare("DELETE FROM $tbl WHERE id=?")->execute([$id]);
            log_activity($tbl . '_deleted', null, "ID: $id");
            flash_set('success', 'Silindi.');
        } elseif ($action === 'toggle' && $id) {
            $pdo->prepare("UPDATE $tbl SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
            flash_set('success', 'Durum değiştirildi.');
        } elseif ($action === 'save') {
            if ($entity === 'group') {
                $data = [
                    clean_multi($_POST['code'] ?? ''),
                    clean_multi($_POST['title'] ?? ''),
                    clean_multi($_POST['label'] ?? ''),
                    trim((string)($_POST['icon'] ?? '')) ?: null,
                    clean_multi($_POST['page_slug'] ?? ''),
                    (int)($_POST['sort_order'] ?? 0),
                    isset($_POST['is_active']) ? 1 : 0,
                ];
                if ($id) {
                    $pdo->prepare("UPDATE menu_groups SET code=?,title=?,label=?,icon=?,page_slug=?,sort_order=?,is_active=? WHERE id=?")
                        ->execute([...$data, $id]);
                } else {
                    $pdo->prepare("INSERT INTO menu_groups (code,title,label,icon,page_slug,sort_order,is_active) VALUES (?,?,?,?,?,?,?)")
                        ->execute($data);
                }
            } elseif ($entity === 'cat') {
                $data = [
                    (int)($_POST['group_id'] ?? 0),
                    clean_multi($_POST['code'] ?? ''),
                    clean_multi($_POST['title'] ?? ''),
                    (int)($_POST['sort_order'] ?? 0),
                    isset($_POST['is_active']) ? 1 : 0,
                ];
                if ($id) {
                    $pdo->prepare("UPDATE menu_categories SET group_id=?,code=?,title=?,sort_order=?,is_active=? WHERE id=?")
                        ->execute([...$data, $id]);
                } else {
                    $pdo->prepare("INSERT INTO menu_categories (group_id,code,title,sort_order,is_active) VALUES (?,?,?,?,?)")
                        ->execute($data);
                }
            } elseif ($entity === 'item') {
                $img = null;
                if (!empty($_FILES['image']['name'])) {
                    $img = upload_file('image', 'menu', ALLOWED_IMG);
                    if ($id && $img) {
                        $st = $pdo->prepare("SELECT image FROM menu_items WHERE id=?");
                        $st->execute([$id]);
                        delete_upload((string)$st->fetchColumn());
                    }
                }
                $cat = (int)($_POST['category_id'] ?? 0);
                $title = clean_multi($_POST['title'] ?? '');
                $desc = clean_multi($_POST['description'] ?? '') ?: null;
                $price = clean_multi($_POST['price'] ?? '') ?: null;
                $sort = (int)($_POST['sort_order'] ?? 0);
                $act = isset($_POST['is_active']) ? 1 : 0;

                if ($id) {
                    if ($img) {
                        $pdo->prepare("UPDATE menu_items SET category_id=?,title=?,description=?,price=?,image=?,sort_order=?,is_active=? WHERE id=?")
                            ->execute([$cat,$title,$desc,$price,$img,$sort,$act,$id]);
                    } else {
                        $pdo->prepare("UPDATE menu_items SET category_id=?,title=?,description=?,price=?,sort_order=?,is_active=? WHERE id=?")
                            ->execute([$cat,$title,$desc,$price,$sort,$act,$id]);
                    }
                } else {
                    $pdo->prepare("INSERT INTO menu_items (category_id,title,description,price,image,sort_order,is_active) VALUES (?,?,?,?,?,?,?)")
                        ->execute([$cat,$title,$desc,$price,$img,$sort,$act]);
                }
            }
            log_activity($tbl . '_saved', null, "ID: " . ($id ?: 'new'));
            flash_set('success', 'Kaydedildi.');
        }
    }
    header('Location: menu.php?tab=' . urlencode($tab) . (!empty($_POST['back_id']) ? '&edit=' . $_POST['back_id'] : '')); exit;
}

$groups = $pdo->query("SELECT * FROM menu_groups ORDER BY sort_order, id")->fetchAll();
$cats = $pdo->query("SELECT mc.*, mg.label AS group_label FROM menu_categories mc LEFT JOIN menu_groups mg ON mg.id=mc.group_id ORDER BY mc.group_id, mc.sort_order, mc.id")->fetchAll();
$items = $pdo->query("SELECT mi.*, mc.title AS cat_title, mg.label AS group_label
                       FROM menu_items mi
                       LEFT JOIN menu_categories mc ON mc.id = mi.category_id
                       LEFT JOIN menu_groups mg ON mg.id = mc.group_id
                       ORDER BY mg.sort_order, mc.sort_order, mi.sort_order, mi.id")->fetchAll();

$edit = (int)($_GET['edit'] ?? 0);
$editRow = null;
if ($edit) {
    $tableMap = ['groups' => 'menu_groups', 'categories' => 'menu_categories', 'items' => 'menu_items'];
    $tbl = $tableMap[$tab] ?? 'menu_items';
    $st = $pdo->prepare("SELECT * FROM $tbl WHERE id=?");
    $st->execute([$edit]);
    $editRow = $st->fetch() ?: null;
}
?>

<div class="card">
  <h2 style="border:0;padding:0;margin:0">
    <a href="?tab=groups"     class="btn btn-sm <?= $tab==='groups'?'':'btn-line' ?>">Gruplar</a>
    <a href="?tab=categories" class="btn btn-sm <?= $tab==='categories'?'':'btn-line' ?>">Sekmeler</a>
    <a href="?tab=items"      class="btn btn-sm <?= $tab==='items'?'':'btn-line' ?>">Ürünler</a>
  </h2>
</div>

<?php if ($tab === 'groups'): ?>
  <div class="card">
    <h2><?= $editRow ? 'Grup Düzenle' : 'Yeni Grup' ?></h2>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="entity" value="group">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $editRow['id'] ?? '' ?>">
      <div class="grid-3">
        <div class="row"><label>Kod *</label><input type="text" name="code" value="<?= e($editRow['code'] ?? '') ?>" required placeholder="tacos"></div>
        <div class="row"><label>Tam Başlık *</label><input type="text" name="title" value="<?= e($editRow['title'] ?? '') ?>" required></div>
        <div class="row"><label>Sayfa Slug *</label><input type="text" name="page_slug" value="<?= e($editRow['page_slug'] ?? '') ?>" required placeholder="tacos-menu"></div>
      </div>
      <div class="grid-3">
        <div class="row"><label>Kart Etiketi *</label><input type="text" name="label" value="<?= e($editRow['label'] ?? '') ?>" required></div>
        <div class="row"><label>İkon URL</label><input type="text" name="icon" value="<?= e($editRow['icon'] ?? '') ?>"></div>
        <div class="row"><label>Sıra</label><input type="number" name="sort_order" value="<?= (int)($editRow['sort_order'] ?? 0) ?>"></div>
      </div>
      <div class="row"><label class="toggle"><input type="checkbox" name="is_active" value="1"<?= ($editRow['is_active'] ?? 1) ? ' checked':'' ?>> Aktif</label></div>
      <button class="btn">Kaydet</button>
      <?php if ($editRow): ?><a href="menu.php?tab=groups" class="btn btn-secondary">İptal</a><?php endif; ?>
    </form>
  </div>
  <div class="card">
    <h2>Tüm Gruplar</h2>
    <table>
      <thead><tr><th>#</th><th>Kod</th><th>Etiket</th><th>Sayfa</th><th>Sıra</th><th>Durum</th><th>İşlem</th></tr></thead>
      <tbody>
      <?php foreach ($groups as $g): ?>
        <tr>
          <td><?= (int)$g['id'] ?></td>
          <td><code><?= e($g['code']) ?></code></td>
          <td><?= e($g['label']) ?></td>
          <td><?= e($g['page_slug']) ?>.php</td>
          <td><?= (int)$g['sort_order'] ?></td>
          <td><span class="badge <?= $g['is_active']?'b-on':'b-off' ?>"><?= $g['is_active']?'Aktif':'Pasif' ?></span></td>
          <td class="actions">
            <a class="btn btn-sm btn-line" href="?tab=groups&edit=<?= $g['id'] ?>"><i class="fa-solid fa-pen"></i></a>
            <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="entity" value="group"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $g['id'] ?>"><button class="btn btn-sm btn-secondary"><i class="fa-solid fa-power-off"></i></button></form>
            <form method="post" data-confirm="Bu grup ve içindeki tüm sekme/ürünler silinecek. Emin misiniz?" style="display:inline"><?= csrf_field() ?><input type="hidden" name="entity" value="group"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $g['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tab === 'categories'): ?>
  <div class="card">
    <h2><?= $editRow ? 'Sekme Düzenle' : 'Yeni Sekme' ?></h2>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="entity" value="cat">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $editRow['id'] ?? '' ?>">
      <div class="grid-3">
        <div class="row">
          <label>Grup *</label>
          <select name="group_id" required>
            <?php foreach ($groups as $g): ?>
              <option value="<?= $g['id'] ?>" <?= ($editRow['group_id'] ?? 0) == $g['id'] ? 'selected':'' ?>><?= e($g['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row"><label>Kod *</label><input type="text" name="code" value="<?= e($editRow['code'] ?? '') ?>" required placeholder="secilmis"></div>
        <div class="row"><label>Sekme Başlığı *</label><input type="text" name="title" value="<?= e($editRow['title'] ?? '') ?>" required></div>
      </div>
      <div class="grid-2">
        <div class="row"><label>Sıra</label><input type="number" name="sort_order" value="<?= (int)($editRow['sort_order'] ?? 0) ?>"></div>
        <div class="row"><label class="toggle"><input type="checkbox" name="is_active" value="1"<?= ($editRow['is_active'] ?? 1) ? ' checked':'' ?>> Aktif</label></div>
      </div>
      <button class="btn">Kaydet</button>
      <?php if ($editRow): ?><a href="menu.php?tab=categories" class="btn btn-secondary">İptal</a><?php endif; ?>
    </form>
  </div>
  <div class="card">
    <h2>Tüm Sekmeler</h2>
    <table>
      <thead><tr><th>#</th><th>Grup</th><th>Kod</th><th>Başlık</th><th>Sıra</th><th>Durum</th><th>İşlem</th></tr></thead>
      <tbody>
      <?php foreach ($cats as $c): ?>
        <tr>
          <td><?= (int)$c['id'] ?></td>
          <td><?= e($c['group_label']) ?></td>
          <td><code><?= e($c['code']) ?></code></td>
          <td><?= e($c['title']) ?></td>
          <td><?= (int)$c['sort_order'] ?></td>
          <td><span class="badge <?= $c['is_active']?'b-on':'b-off' ?>"><?= $c['is_active']?'Aktif':'Pasif' ?></span></td>
          <td class="actions">
            <a class="btn btn-sm btn-line" href="?tab=categories&edit=<?= $c['id'] ?>"><i class="fa-solid fa-pen"></i></a>
            <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="entity" value="cat"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn btn-sm btn-secondary"><i class="fa-solid fa-power-off"></i></button></form>
            <form method="post" data-confirm="Bu sekme ve içindeki ürünler silinecek. Emin misiniz?" style="display:inline"><?= csrf_field() ?><input type="hidden" name="entity" value="cat"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php else: /* items */ ?>
  <div class="card">
    <h2><?= $editRow ? 'Ürün Düzenle: ' . e($editRow['title']) : 'Yeni Ürün' ?></h2>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="entity" value="item">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $editRow['id'] ?? '' ?>">
      <div class="grid-2">
        <div class="row">
          <label>Sekme *</label>
          <select name="category_id" required>
            <?php foreach ($cats as $c): if (!$c['is_active']) continue; ?>
              <option value="<?= $c['id'] ?>" <?= ($editRow['category_id'] ?? 0) == $c['id'] ? 'selected':'' ?>>
                <?= e($c['group_label']) ?> &raquo; <?= e($c['title']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row"><label>Ürün Adı *</label><input type="text" name="title" value="<?= e($editRow['title'] ?? '') ?>" required></div>
      </div>
      <div class="row"><label>Açıklama</label><textarea name="description" rows="2"><?= e($editRow['description'] ?? '') ?></textarea></div>
      <div class="grid-3">
        <div class="row"><label>Fiyat</label><input type="text" name="price" value="<?= e($editRow['price'] ?? '') ?>" placeholder="₺ 199"></div>
        <div class="row"><label>Sıra</label><input type="number" name="sort_order" value="<?= (int)($editRow['sort_order'] ?? 0) ?>"></div>
        <div class="row"><label class="toggle"><input type="checkbox" name="is_active" value="1"<?= ($editRow['is_active'] ?? 1) ? ' checked':'' ?>> Aktif</label></div>
      </div>
      <div class="row">
        <label>Görsel</label>
        <input type="file" name="image" accept="image/*">
        <?php if (!empty($editRow['image'])): ?><div class="help">Mevcut: <img src="<?= e(asset($editRow['image'])) ?>" style="height:60px;border-radius:4px"></div><?php endif; ?>
      </div>
      <button class="btn">Kaydet</button>
      <?php if ($editRow): ?><a href="menu.php?tab=items" class="btn btn-secondary">İptal</a><?php endif; ?>
    </form>
  </div>
  <div class="card">
    <h2>Tüm Ürünler (<?= count($items) ?>)</h2>
    <table>
      <thead><tr><th>#</th><th>Görsel</th><th>Ürün</th><th>Grup / Sekme</th><th>Fiyat</th><th>Sıra</th><th>Durum</th><th>İşlem</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?= (int)$it['id'] ?></td>
          <td><?= $it['image'] ? '<img class="thumb" src="' . e(asset($it['image'])) . '">' : '—' ?></td>
          <td><strong><?= e($it['title']) ?></strong><?php if ($it['description']): ?><br><small style="color:#9ca3af"><?= e(mb_strimwidth($it['description'],0,60,'…')) ?></small><?php endif; ?></td>
          <td style="font-size:11px"><?= e($it['group_label']) ?> &raquo; <?= e($it['cat_title']) ?></td>
          <td><?= e($it['price'] ?? '—') ?></td>
          <td><?= (int)$it['sort_order'] ?></td>
          <td><span class="badge <?= $it['is_active']?'b-on':'b-off' ?>"><?= $it['is_active']?'Aktif':'Pasif' ?></span></td>
          <td class="actions">
            <a class="btn btn-sm btn-line" href="?tab=items&edit=<?= $it['id'] ?>"><i class="fa-solid fa-pen"></i></a>
            <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="entity" value="item"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $it['id'] ?>"><button class="btn btn-sm btn-secondary"><i class="fa-solid fa-power-off"></i></button></form>
            <form method="post" data-confirm="Silinsin mi?" style="display:inline"><?= csrf_field() ?><input type="hidden" name="entity" value="item"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $it['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
