<?php
declare(strict_types=1);
$page_h = 'Anasayfa Kartları';
require __DIR__ . '/_header.php';
require __DIR__ . '/_crud.php';

$crud = new Crud([
    'table' => 'menu_promo_cards',
    'label' => 'Kart',
    'fields' => [
        'group_id'     => ['type' => 'int', 'required' => true],
        'title'        => ['required' => true],
        'image'        => [], 'image_mobile' => [],
        'tab_code'     => [],
        'sort_order'   => ['type' => 'int'],
        'is_active'    => ['type' => 'bool'],
    ],
    'images' => ['image', 'image_mobile'],
    'image_dir' => 'menu',
]);
$crud->handle();

$groups = db()->query("SELECT id, label FROM menu_groups ORDER BY sort_order")->fetchAll();
$opts = [];
foreach ($groups as $g) $opts[$g['id']] = $g['label'];

$action = $_GET['action'] ?? 'list';
$row = ($action === 'edit' && !empty($_GET['id'])) ? $crud->getRow((int)$_GET['id']) : null;

if ($action === 'list'):
    $rows = db()->query("SELECT pc.*, mg.label AS group_label FROM menu_promo_cards pc LEFT JOIN menu_groups mg ON mg.id = pc.group_id ORDER BY pc.group_id, pc.sort_order, pc.id")->fetchAll();
?>
  <div class="card">
    <h2 style="display:flex;justify-content:space-between;align-items:center">
      <span>Anasayfa Pop-up Kartları</span>
      <a href="?action=new" class="btn btn-sm">+ Yeni Kart</a>
    </h2>
    <?php if (!$rows): ?>
      <div class="empty">Henüz kart yok.</div>
    <?php else: ?>
      <table>
        <thead><tr><th>#</th><th>Görsel</th><th>Grup</th><th>Başlık</th><th>Sekme</th><th>Sıra</th><th>Durum</th><th>İşlem</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= $r['image'] ? '<img class="thumb" src="' . e(asset($r['image'])) . '">' : '—' ?></td>
            <td><?= e($r['group_label']) ?></td>
            <td><strong><?= e($r['title']) ?></strong></td>
            <td><code><?= e($r['tab_code'] ?? '—') ?></code></td>
            <td><?= (int)$r['sort_order'] ?></td>
            <td><span class="badge <?= $r['is_active'] ? 'b-on' : 'b-off' ?>"><?= $r['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
            <td class="actions">
              <a class="btn btn-sm btn-line" href="?action=edit&id=<?= $r['id'] ?>"><i class="fa-solid fa-pen"></i></a>
              <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-secondary"><i class="fa-solid fa-power-off"></i></button></form>
              <form method="post" data-confirm="Silinsin mi?" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button></form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php else: ?>
  <div class="card">
    <h2><?= $row ? 'Düzenle' : 'Yeni Kart' ?></h2>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $row['id'] ?? '' ?>">
      <div class="grid-2">
        <?php
          field('group_id','Bağlı Grup', (string)($row['group_id'] ?? ''), 'select', ['options' => $opts, 'required' => true]);
          field('tab_code','Hedef Sekme Kodu', $row['tab_code'] ?? '', 'text', ['placeholder' => 'secilmis', 'help' => 'Tıklandığında açılan menü sayfasındaki sekme kodu.']);
        ?>
      </div>
      <?php
        field('title','Başlık', $row['title'] ?? '', 'text', ['required' => true]);
        field('image','Görsel', $row['image'] ?? null, 'image');
        field('image_mobile','Mobil Görsel', $row['image_mobile'] ?? null, 'image');
      ?>
      <div class="grid-2">
        <?php
          field('sort_order','Sıra', (string)($row['sort_order'] ?? 0), 'number');
          field('is_active','Aktif mi?', (string)($row['is_active'] ?? 1), 'bool');
        ?>
      </div>
      <button type="submit" class="btn">Kaydet</button>
      <a href="promo-cards.php" class="btn btn-secondary">İptal</a>
    </form>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
