<?php
declare(strict_types=1);
$page_h = 'İş İlanları';
require __DIR__ . '/_header.php';
require __DIR__ . '/_crud.php';

$crud = new Crud([
    'table' => 'jobs',
    'label' => 'İş İlanı',
    'fields' => [
        'title'       => ['label' => 'Pozisyon', 'required' => true],
        'employment'  => ['label' => 'İstihdam'],
        'location'    => ['label' => 'Konum'],
        'description' => ['label' => 'Açıklama', 'required' => true],
        'sort_order'  => ['type' => 'int'],
        'is_active'   => ['type' => 'bool'],
    ],
]);
$crud->handle();

$action = $_GET['action'] ?? 'list';
$row = ($action === 'edit' && !empty($_GET['id'])) ? $crud->getRow((int)$_GET['id']) : null;

$emp_opts = ['fulltime' => 'Tam Zamanlı', 'parttime' => 'Yarı Zamanlı', 'intern' => 'Stajyer'];

if ($action === 'list'): ?>
  <div class="card">
    <h2 style="display:flex;justify-content:space-between;align-items:center">
      <span>Açık Pozisyonlar</span>
      <a href="?action=new" class="btn btn-sm">+ Yeni İlan</a>
    </h2>
    <?php $rows = $crud->listAll(); ?>
    <?php if (!$rows): ?>
      <div class="empty">Henüz iş ilanı yok.</div>
    <?php else: ?>
      <table>
        <thead><tr><th>#</th><th>Pozisyon</th><th>Tip</th><th>Konum</th><th>Sıra</th><th>Durum</th><th>İşlem</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><strong><?= e($r['title']) ?></strong></td>
            <td><span class="badge b-info"><?= e($emp_opts[$r['employment']] ?? $r['employment']) ?></span></td>
            <td><?= e($r['location'] ?? '—') ?></td>
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
    <h2><?= $row ? 'Düzenle: ' . e($row['title']) : 'Yeni İş İlanı' ?></h2>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $row['id'] ?? '' ?>">
      <?php
        field('title','Pozisyon', $row['title'] ?? '', 'text', ['required' => true]);
      ?>
      <div class="grid-2">
        <?php
          field('employment','İstihdam Türü', $row['employment'] ?? 'fulltime', 'select', ['options' => $emp_opts]);
          field('location','Konum', $row['location'] ?? '', 'text', ['placeholder' => 'İstanbul / Şube']);
        ?>
      </div>
      <?php
        field('description','Açıklama', $row['description'] ?? '', 'textarea', ['rows' => 4, 'required' => true]);
      ?>
      <div class="grid-2">
        <?php
          field('sort_order','Sıra', (string)($row['sort_order'] ?? 0), 'number');
          field('is_active','Aktif mi?', (string)($row['is_active'] ?? 1), 'bool');
        ?>
      </div>
      <button type="submit" class="btn">Kaydet</button>
      <a href="jobs.php" class="btn btn-secondary">İptal</a>
    </form>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
