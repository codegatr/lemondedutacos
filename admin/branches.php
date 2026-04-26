<?php
declare(strict_types=1);
$page_h = 'Şubeler';
require __DIR__ . '/_header.php';
require __DIR__ . '/_crud.php';

$crud = new Crud([
    'table' => 'branches',
    'label' => 'Şube',
    'fields' => [
        'title'      => ['label' => 'Başlık', 'required' => true],
        'city'       => ['label' => 'Şehir'],
        'district'   => ['label' => 'İlçe'],
        'address'    => ['label' => 'Adres'],
        'phone'      => ['label' => 'Telefon'],
        'work_hours' => ['label' => 'Çalışma Saatleri'],
        'map_url'    => ['label' => 'Harita Linki'],
        'sort_order' => ['type' => 'int'],
        'is_active'  => ['type' => 'bool'],
    ],
]);
$crud->handle();

$action = $_GET['action'] ?? 'list';
$row = ($action === 'edit' && !empty($_GET['id'])) ? $crud->getRow((int)$_GET['id']) : null;

if ($action === 'list'): ?>
  <div class="card">
    <h2 style="display:flex;justify-content:space-between;align-items:center">
      <span>Tüm Şubeler</span>
      <a href="?action=new" class="btn btn-sm">+ Yeni Şube</a>
    </h2>
    <?php $rows = $crud->listAll(); ?>
    <?php if (!$rows): ?>
      <div class="empty">Henüz şube eklenmemiş.</div>
    <?php else: ?>
      <table>
        <thead><tr><th>#</th><th>Şube</th><th>Şehir/İlçe</th><th>Telefon</th><th>Sıra</th><th>Durum</th><th>İşlem</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><strong><?= e($r['title']) ?></strong></td>
            <td><?= e(trim(($r['city'] ?? '') . ' / ' . ($r['district'] ?? ''), ' /')) ?></td>
            <td><?= e($r['phone'] ?? '—') ?></td>
            <td><?= (int)$r['sort_order'] ?></td>
            <td><span class="badge <?= $r['is_active'] ? 'b-on' : 'b-off' ?>"><?= $r['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
            <td class="actions">
              <a class="btn btn-sm btn-line" href="?action=edit&id=<?= $r['id'] ?>"><i class="fa-solid fa-pen"></i></a>
              <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-secondary"><i class="fa-solid fa-power-off"></i></button></form>
              <form method="post" data-confirm="Şubeyi silmek istediğinize emin misiniz?" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button></form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php else: ?>
  <div class="card">
    <h2><?= $row ? 'Düzenle: ' . e($row['title']) : 'Yeni Şube' ?></h2>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $row['id'] ?? '' ?>">
      <?php
        field('title','Başlık (örn: İstanbul – Yenibosna)', $row['title'] ?? '', 'text', ['required' => true]);
      ?>
      <div class="grid-2">
        <?php
          field('city','Şehir', $row['city'] ?? '');
          field('district','İlçe', $row['district'] ?? '');
        ?>
      </div>
      <?php
        field('address','Adres', $row['address'] ?? '', 'textarea', ['rows' => 2]);
      ?>
      <div class="grid-2">
        <?php
          field('phone','Telefon', $row['phone'] ?? '', 'tel');
          field('work_hours','Çalışma Saatleri', $row['work_hours'] ?? '', 'text', ['placeholder' => 'Hergün 10:00 - 23:00']);
        ?>
      </div>
      <?php
        field('map_url','Google Maps URL', $row['map_url'] ?? '', 'url');
      ?>
      <div class="grid-2">
        <?php
          field('sort_order','Sıra', (string)($row['sort_order'] ?? 0), 'number');
          field('is_active','Aktif mi?', (string)($row['is_active'] ?? 1), 'bool');
        ?>
      </div>
      <button type="submit" class="btn">Kaydet</button>
      <a href="branches.php" class="btn btn-secondary">İptal</a>
    </form>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
