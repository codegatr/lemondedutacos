<?php
declare(strict_types=1);
$page_h = 'Hero Slider';
require __DIR__ . '/_header.php';
require __DIR__ . '/_crud.php';

$crud = new Crud([
    'table' => 'slider',
    'label' => 'Slider',
    'fields' => [
        'title'        => ['label' => 'Başlık'],
        'image'        => [], 'image_mobile' => [],
        'link_url'     => ['label' => 'Bağlantı (opsiyonel)'],
        'sort_order'   => ['type' => 'int'],
        'is_active'    => ['type' => 'bool'],
    ],
    'images'    => ['image', 'image_mobile'],
    'image_dir' => 'slider',
]);
$crud->handle();

$action = $_GET['action'] ?? 'list';
$row = ($action === 'edit' && !empty($_GET['id'])) ? $crud->getRow((int)$_GET['id']) : null;

if ($action === 'list'): ?>
  <div class="card">
    <h2 style="display:flex;justify-content:space-between;align-items:center">
      <span>Slider Görselleri</span>
      <a href="?action=new" class="btn btn-sm">+ Yeni Slide</a>
    </h2>
    <?php $rows = $crud->listAll(); ?>
    <?php if (!$rows): ?>
      <div class="empty">Henüz slide yok.</div>
    <?php else: ?>
      <table>
        <thead><tr><th>#</th><th>Görsel</th><th>Başlık</th><th>Sıra</th><th>Durum</th><th>İşlem</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= $r['image'] ? '<img class="thumb" src="' . e(asset($r['image'])) . '">' : '—' ?></td>
            <td><?= e($r['title'] ?? '—') ?></td>
            <td><?= (int)$r['sort_order'] ?></td>
            <td><span class="badge <?= $r['is_active'] ? 'b-on' : 'b-off' ?>"><?= $r['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
            <td class="actions">
              <a class="btn btn-sm btn-line" href="?action=edit&id=<?= $r['id'] ?>"><i class="fa-solid fa-pen"></i></a>
              <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-secondary"><i class="fa-solid fa-power-off"></i></button></form>
              <form method="post" data-confirm="Silmek istediğinize emin misiniz?" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button></form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php else: ?>
  <div class="card">
    <h2><?= $row ? 'Düzenle: ' . e($row['title'] ?? 'Slide #' . $row['id']) : 'Yeni Slide' ?></h2>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $row['id'] ?? '' ?>">
      <?php
        field('title','Başlık (opsiyonel)', $row['title'] ?? '');
        field('image','Masaüstü Görsel (geniş)', $row['image'] ?? null, 'image');
        field('image_mobile','Mobil Görsel (opsiyonel)', $row['image_mobile'] ?? null, 'image');
        field('link_url','Yönlendirme URL (opsiyonel)', $row['link_url'] ?? '', 'url');
        field('sort_order','Sıra', (string)($row['sort_order'] ?? 0), 'number');
        field('is_active','Aktif mi?', (string)($row['is_active'] ?? 1), 'bool');
      ?>
      <button type="submit" class="btn">Kaydet</button>
      <a href="slider.php" class="btn btn-secondary">İptal</a>
    </form>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
