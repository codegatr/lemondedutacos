<?php
declare(strict_types=1);
$page_h = 'Kampanyalar';
require __DIR__ . '/_header.php';
require __DIR__ . '/_crud.php';

$crud = new Crud([
    'table' => 'campaigns',
    'label' => 'Kampanya',
    'fields' => [
        'title'        => ['label' => 'Başlık'],
        'description'  => ['label' => 'Açıklama'],
        'image'        => [], 'image_mobile' => [],
        'link_url'     => ['label' => 'Bağlantı'],
        'starts_on'    => [],
        'ends_on'      => [],
        'sort_order'   => ['type' => 'int'],
        'is_active'    => ['type' => 'bool'],
    ],
    'images' => ['image', 'image_mobile'],
    'image_dir' => 'kampanya',
]);
$crud->handle();

$action = $_GET['action'] ?? 'list';
$row = ($action === 'edit' && !empty($_GET['id'])) ? $crud->getRow((int)$_GET['id']) : null;

if ($action === 'list'): ?>
  <div class="card">
    <h2 style="display:flex;justify-content:space-between;align-items:center">
      <span>Kampanyalar</span>
      <a href="?action=new" class="btn btn-sm">+ Yeni Kampanya</a>
    </h2>
    <?php $rows = $crud->listAll(); ?>
    <?php if (!$rows): ?>
      <div class="empty">Henüz kampanya yok.</div>
    <?php else: ?>
      <table>
        <thead><tr><th>#</th><th>Görsel</th><th>Başlık</th><th>Tarih</th><th>Sıra</th><th>Durum</th><th>İşlem</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= $r['image'] ? '<img class="thumb" src="' . e(asset($r['image'])) . '">' : '—' ?></td>
            <td><?= e($r['title'] ?? '—') ?></td>
            <td style="font-size:11px"><?= $r['starts_on'] ? format_date($r['starts_on'],'d.m.Y') : '—' ?> → <?= $r['ends_on'] ? format_date($r['ends_on'],'d.m.Y') : '—' ?></td>
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
    <h2><?= $row ? 'Düzenle' : 'Yeni Kampanya' ?></h2>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $row['id'] ?? '' ?>">
      <?php
        field('title','Başlık', $row['title'] ?? '');
        field('description','Açıklama', $row['description'] ?? '', 'textarea');
        field('image','Masaüstü Görsel', $row['image'] ?? null, 'image');
        field('image_mobile','Mobil Görsel', $row['image_mobile'] ?? null, 'image');
        field('link_url','Yönlendirme URL', $row['link_url'] ?? '', 'url');
      ?>
      <div class="grid-2">
        <?php
          field('starts_on','Başlangıç Tarihi', $row['starts_on'] ?? '', 'date');
          field('ends_on','Bitiş Tarihi', $row['ends_on'] ?? '', 'date');
        ?>
      </div>
      <div class="grid-2">
        <?php
          field('sort_order','Sıra', (string)($row['sort_order'] ?? 0), 'number');
          field('is_active','Aktif mi?', (string)($row['is_active'] ?? 1), 'bool');
        ?>
      </div>
      <button type="submit" class="btn">Kaydet</button>
      <a href="campaigns.php" class="btn btn-secondary">İptal</a>
    </form>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
