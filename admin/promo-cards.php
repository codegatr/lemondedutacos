<?php
/**
 * Anasayfa Pop-up Kartları — Standalone Admin
 *
 * Crud sınıfından bağımsız. Kendi içinde:
 *  - Schema'yı otomatik onarır (image NOT NULL → DEFAULT NULL)
 *  - INSERT/UPDATE/DELETE/TOGGLE kendi handler'larıyla
 *  - Görsel zorunlu DEĞİL (boş bırakılırsa NULL kaydedilir)
 *  - Disk'te dosya yoksa upload error fail olduğunda görsel atılmadan kayıt yapılır
 *  - Net hata mesajları
 */
declare(strict_types=1);
$page_h = 'Anasayfa Kartları';
require __DIR__ . '/_header.php';

$pdo = db();
$cu  = admin_user();

/* === BİR KEZ: SCHEMA ONARIMI ===
 * image NOT NULL ise DEFAULT NULL'a çevir. Bu sadece bir kez gerekli ama
 * her sayfa açılışında IS NULLABLE check ile idempotent. */
try {
    $stmt = $pdo->prepare("SELECT IS_NULLABLE FROM information_schema.COLUMNS
                          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menu_promo_cards' AND COLUMN_NAME = 'image'");
    $stmt->execute();
    $isNullable = $stmt->fetchColumn();
    if ($isNullable === 'NO') {
        $pdo->exec("ALTER TABLE `menu_promo_cards` MODIFY COLUMN `image` VARCHAR(255) DEFAULT NULL");
        $pdo->exec("ALTER TABLE `menu_promo_cards` MODIFY COLUMN `image_mobile` VARCHAR(255) DEFAULT NULL");
        $pdo->exec("ALTER TABLE `menu_promo_cards` MODIFY COLUMN `tab_code` VARCHAR(60) DEFAULT NULL");
        $pdo->exec("UPDATE `menu_promo_cards` SET image = NULL WHERE image = ''");
        $pdo->exec("UPDATE `menu_promo_cards` SET image_mobile = NULL WHERE image_mobile = ''");
        flash_set('success', 'Schema otomatik onarıldı: image kolonları NULL\'able yapıldı.');
    }
} catch (Throwable $e) {
    // Schema onaramazsa devam et, INSERT/UPDATE'te yakalanır
}

/* === POST HANDLERS === */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_required();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save') {
            $id          = (int)($_POST['id'] ?? 0);
            $group_id    = (int)($_POST['group_id'] ?? 0);
            $title       = trim($_POST['title'] ?? '');
            $tab_code    = trim($_POST['tab_code'] ?? '');
            $sort_order  = (int)($_POST['sort_order'] ?? 0);
            $is_active   = !empty($_POST['is_active']) ? 1 : 0;

            // Validation
            if ($group_id <= 0)         throw new RuntimeException('Bağlı Grup seçilmelidir.');
            if ($title === '')          throw new RuntimeException('Başlık zorunludur.');
            if (mb_strlen($title) > 120) throw new RuntimeException('Başlık çok uzun (max 120 karakter).');
            if (mb_strlen($tab_code) > 60) throw new RuntimeException('Sekme kodu çok uzun (max 60 karakter).');

            // Görsel uploadları (opsiyonel)
            $imageUrl       = null;  // yeni dosya URL'si
            $mobileUrl      = null;
            $imageProvided  = !empty($_FILES['image']['name']);
            $mobileProvided = !empty($_FILES['image_mobile']['name']);

            if ($imageProvided) {
                $imageUrl = upload_file('image', 'menu', ALLOWED_IMG);
                if (!$imageUrl) throw new RuntimeException('Ana görsel yüklenemedi (Sistem Diagnostik\'i kontrol edin).');
            }
            if ($mobileProvided) {
                $mobileUrl = upload_file('image_mobile', 'menu', ALLOWED_IMG);
                if (!$mobileUrl) throw new RuntimeException('Mobil görsel yüklenemedi.');
            }

            if ($id > 0) {
                // UPDATE — image alanları sadece yeni dosya yüklendiyse güncellenir
                $existing = $pdo->prepare("SELECT image, image_mobile FROM menu_promo_cards WHERE id = ?");
                $existing->execute([$id]);
                $row = $existing->fetch();
                if (!$row) throw new RuntimeException("Kayıt bulunamadı (ID: $id).");

                $finalImage  = $imageUrl  ?? $row['image'];
                $finalMobile = $mobileUrl ?? $row['image_mobile'];

                // Eski görsel silme (yeni yüklendiyse)
                if ($imageUrl && $row['image'] && $row['image'] !== $finalImage) {
                    delete_upload((string)$row['image']);
                }
                if ($mobileUrl && $row['image_mobile'] && $row['image_mobile'] !== $finalMobile) {
                    delete_upload((string)$row['image_mobile']);
                }

                $stmt = $pdo->prepare("UPDATE menu_promo_cards
                    SET group_id = ?, title = ?, image = ?, image_mobile = ?,
                        tab_code = ?, sort_order = ?, is_active = ?
                    WHERE id = ?");
                $stmt->execute([
                    $group_id, $title, $finalImage, $finalMobile,
                    $tab_code !== '' ? $tab_code : null,
                    $sort_order, $is_active, $id
                ]);
                log_activity('promo_card_updated', null, "ID: $id - $title");
                flash_set('success', "Kart güncellendi: $title");
            } else {
                // INSERT
                $stmt = $pdo->prepare("INSERT INTO menu_promo_cards
                    (group_id, title, image, image_mobile, tab_code, sort_order, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $group_id, $title, $imageUrl, $mobileUrl,
                    $tab_code !== '' ? $tab_code : null,
                    $sort_order, $is_active
                ]);
                $newId = (int)$pdo->lastInsertId();
                log_activity('promo_card_created', null, "ID: $newId - $title");
                flash_set('success', "Kart eklendi: $title");
            }
            header('Location: promo-cards.php'); exit;
        }

        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                // Görsel dosyalarını sil
                $row = $pdo->prepare("SELECT title, image, image_mobile FROM menu_promo_cards WHERE id = ?");
                $row->execute([$id]);
                $r = $row->fetch();
                if ($r) {
                    if ($r['image'])        delete_upload((string)$r['image']);
                    if ($r['image_mobile']) delete_upload((string)$r['image_mobile']);
                    $pdo->prepare("DELETE FROM menu_promo_cards WHERE id = ?")->execute([$id]);
                    log_activity('promo_card_deleted', null, "ID: $id - {$r['title']}");
                    flash_set('success', "Kart silindi: {$r['title']}");
                }
            }
            header('Location: promo-cards.php'); exit;
        }

        if ($action === 'toggle') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare("UPDATE menu_promo_cards SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
                log_activity('promo_card_toggled', null, "ID: $id");
                flash_set('success', 'Kart durumu değiştirildi.');
            }
            header('Location: promo-cards.php'); exit;
        }
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        if (str_contains($msg, 'cannot be null')) {
            preg_match("/Column '([^']+)' cannot be null/", $msg, $m);
            $col = $m[1] ?? 'bilinmeyen';
            $msg = "DB schema hatası: '$col' kolonu NULL kabul etmiyor. phpMyAdmin'de şunu çalıştırın: ALTER TABLE menu_promo_cards MODIFY COLUMN $col VARCHAR(255) DEFAULT NULL";
        }
        flash_set('error', "Hata: $msg");
        $editId = (int)($_POST['id'] ?? 0);
        $back = $editId > 0 ? "?action=edit&id=$editId" : ($action === 'save' ? "?action=new" : '');
        header('Location: promo-cards.php' . $back); exit;
    }
}

/* === DATA FETCH === */
$groups = $pdo->query("SELECT id, label FROM menu_groups ORDER BY sort_order, id")->fetchAll();

$action = $_GET['action'] ?? 'list';
$row = null;
if ($action === 'edit' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM menu_promo_cards WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $row = $stmt->fetch();
    if (!$row) {
        flash_set('error', 'Kayıt bulunamadı.');
        header('Location: promo-cards.php'); exit;
    }
}

/* === LIST VIEW === */
if ($action === 'list'):
    $rows = $pdo->query("SELECT pc.*, mg.label AS group_label
                        FROM menu_promo_cards pc
                        LEFT JOIN menu_groups mg ON mg.id = pc.group_id
                        ORDER BY pc.group_id, pc.sort_order, pc.id")->fetchAll();
?>
  <div class="card">
    <h2 style="display:flex;justify-content:space-between;align-items:center;margin:0 0 16px">
      <span>Anasayfa Pop-up Kartları</span>
      <a href="?action=new" class="btn btn-sm">+ Yeni Kart</a>
    </h2>

    <?php if (!$rows): ?>
      <div class="empty">
        <p>Henüz kart yok. <a href="?action=new" class="btn btn-sm">+ İlk kartı ekle</a></p>
      </div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Görsel</th>
            <th>Grup</th>
            <th>Başlık</th>
            <th>Sekme</th>
            <th>Sıra</th>
            <th>Durum</th>
            <th>İşlem</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td>
              <?php if ($r['image']): ?>
                <img class="thumb" src="<?= e(asset($r['image'])) ?>" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='inline'">
                <span style="display:none;color:#dc2626;font-size:11px">⚠ disk'te yok</span>
              <?php else: ?>
                <span style="color:#9ca3af">—</span>
              <?php endif; ?>
            </td>
            <td><?= e($r['group_label'] ?? '—') ?></td>
            <td><strong><?= e($r['title']) ?></strong></td>
            <td><code><?= e($r['tab_code'] ?? '—') ?></code></td>
            <td><?= (int)$r['sort_order'] ?></td>
            <td>
              <span class="badge <?= $r['is_active'] ? 'b-on' : 'b-off' ?>">
                <?= $r['is_active'] ? 'Aktif' : 'Pasif' ?>
              </span>
            </td>
            <td class="actions">
              <a class="btn btn-sm btn-line" href="?action=edit&id=<?= $r['id'] ?>" title="Düzenle">
                <i class="fa-solid fa-pen"></i>
              </a>
              <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="btn btn-sm btn-secondary" title="Aktif/Pasif yap">
                  <i class="fa-solid fa-power-off"></i>
                </button>
              </form>
              <form method="post" data-confirm="'<?= e($r['title']) ?>' kartı silinsin mi?" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="btn btn-sm btn-danger" title="Sil">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>

      <p style="margin-top:14px;color:var(--muted);font-size:12px">
        Toplam <strong><?= count($rows) ?></strong> kart.
        Görsel hücresinde "⚠ disk'te yok" görünüyorsa, ilgili kartı düzenleyip görseli yeniden yükleyin.
      </p>
    <?php endif; ?>
  </div>

<?php else: /* === EDIT/NEW FORM === */
    require_once __DIR__ . '/_crud.php'; // field() helper için
?>

  <div class="card" style="max-width:900px;margin:0 auto">
    <h2 style="margin:0 0 18px"><?= $row ? '✏️ Kartı Düzenle' : '➕ Yeni Kart Ekle' ?></h2>

    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $row['id'] ?? '' ?>">

      <div class="grid-2">
        <?php
        $groupOpts = ['' => '— Grup seçin —'];
        foreach ($groups as $g) $groupOpts[$g['id']] = $g['label'];
        field('group_id', 'Bağlı Grup', (string)($row['group_id'] ?? ''), 'select', [
            'options'  => $groupOpts,
            'required' => true,
        ]);
        field('tab_code', 'Hedef Sekme Kodu', $row['tab_code'] ?? '', 'text', [
            'placeholder' => 'örn: secilmis',
            'help'        => 'Tıklandığında menü sayfasında açılacak sekmenin kodu (boş bırakılabilir).',
        ]);
        ?>
      </div>

      <?php
      field('title', 'Başlık', $row['title'] ?? '', 'text', [
          'required'    => true,
          'placeholder' => 'örn: Seçilmiş Lezzetler',
      ]);
      ?>

      <div class="grid-2">
        <?php
        field('image', 'Ana Görsel', $row['image'] ?? null, 'image', [
            'help' => 'Opsiyonel — boş bırakılırsa kart görselsiz görünür.',
        ]);
        field('image_mobile', 'Mobil Görsel', $row['image_mobile'] ?? null, 'image', [
            'help' => 'Opsiyonel — mobil cihazlarda gösterilir.',
        ]);
        ?>
      </div>

      <div class="grid-2">
        <?php
        field('sort_order', 'Sıra', (string)($row['sort_order'] ?? 0), 'number');
        field('is_active', 'Aktif mi?', !$row || $row['is_active'] ? '1' : '', 'bool');
        ?>
      </div>

      <div style="margin-top:24px;padding-top:18px;border-top:1px solid #e5e7eb">
        <button type="submit" class="btn">
          <i class="fa-solid fa-floppy-disk"></i>
          <?= $row ? 'Değişiklikleri Kaydet' : 'Kartı Ekle' ?>
        </button>
        <a href="promo-cards.php" class="btn btn-secondary">İptal</a>
      </div>
    </form>
  </div>

<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
