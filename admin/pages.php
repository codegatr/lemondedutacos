<?php
declare(strict_types=1);
$page_h = 'Sayfa İçerikleri';
require __DIR__ . '/_header.php';
require __DIR__ . '/_crud.php';

$crud = new Crud([
    'table' => 'pages',
    'label' => 'Sayfa',
    'fields' => [
        'slug'       => ['required' => true],
        'title'      => ['required' => true],
        'subtitle'   => [],
        'seo_title'  => [],
        'seo_desc'   => [],
        'hero_image' => [],
        'body'       => ['type' => 'html'],
        'is_active'  => ['type' => 'bool'],
    ],
    'images' => ['hero_image'],
    'image_dir' => 'sayfa',
]);
$crud->handle();

$action = $_GET['action'] ?? 'list';
$row = ($action === 'edit' && !empty($_GET['id'])) ? $crud->getRow((int)$_GET['id']) : null;

if ($action === 'list'): ?>
  <div class="card">
    <h2 style="display:flex;justify-content:space-between;align-items:center">
      <span>Statik Sayfalar</span>
      <a href="?action=new" class="btn btn-sm">+ Yeni Sayfa</a>
    </h2>
    <p style="color:#6b7280;font-size:12px;margin-bottom:10px">Hakkımızda, üretim, medya gibi metin içerikli sayfalar burada düzenlenir. Her birinin <code>slug</code> değeri aynı isimli .php dosyasıyla eşleşmelidir.</p>
    <?php $rows = $crud->listAll('id'); ?>
    <?php if (!$rows): ?>
      <div class="empty">Henüz sayfa yok.</div>
    <?php else: ?>
      <table>
        <thead><tr><th>#</th><th>Slug</th><th>Başlık</th><th>Durum</th><th>İşlem</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><code><?= e($r['slug']) ?></code></td>
            <td><?= e($r['title']) ?></td>
            <td><span class="badge <?= $r['is_active'] ? 'b-on' : 'b-off' ?>"><?= $r['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
            <td class="actions">
              <a class="btn btn-sm btn-line" href="/<?= e($r['slug']) ?>.php" target="_blank"><i class="fa-solid fa-eye"></i></a>
              <a class="btn btn-sm btn-line" href="?action=edit&id=<?= $r['id'] ?>"><i class="fa-solid fa-pen"></i></a>
              <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-secondary"><i class="fa-solid fa-power-off"></i></button></form>
              <form method="post" data-confirm="Sayfa silinsin mi?" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button></form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php else: ?>
  <div class="card">
    <h2><?= $row ? 'Düzenle: ' . e($row['title']) : 'Yeni Sayfa' ?></h2>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $row['id'] ?? '' ?>">
      <div class="grid-2">
        <?php
          field('slug','URL Slug', $row['slug'] ?? '', 'text', ['required' => true, 'placeholder' => 'hakkimizda', 'help' => 'Aynı isimli .php dosyasıyla eşleşmelidir.']);
          field('title','Başlık', $row['title'] ?? '', 'text', ['required' => true]);
        ?>
      </div>
      <?php
        field('subtitle','Alt Başlık', $row['subtitle'] ?? '', 'text');
        field('hero_image','Hero Görseli', $row['hero_image'] ?? null, 'image');
      ?>
      <div class="grid-2">
        <?php
          field('seo_title','SEO Başlık', $row['seo_title'] ?? '', 'text');
          field('seo_desc','SEO Açıklama', $row['seo_desc'] ?? '', 'text');
        ?>
      </div>
      <?php
        field('body','İçerik (HTML)', $row['body'] ?? '', 'html', ['rows' => 12]);
        field('is_active','Aktif mi?', (string)($row['is_active'] ?? 1), 'bool');
      ?>
      <button type="submit" class="btn">Kaydet</button>
      <a href="pages.php" class="btn btn-secondary">İptal</a>
    </form>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
