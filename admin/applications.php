<?php
declare(strict_types=1);
$page_h = 'Başvurular & Mesajlar';
require __DIR__ . '/_header.php';

$tab = $_GET['tab'] ?? 'contact';
$pdo = db();

$tables = [
    'contact'   => ['contact_messages', 'İletişim Mesajları'],
    'franchise' => ['franchise_applications', 'Franchise Başvuruları'],
    'jobs'      => ['job_applications', 'İş Başvuruları'],
];

if (!isset($tables[$tab])) $tab = 'contact';
[$tbl, $tabLabel] = $tables[$tab];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_required();
    $id = (int)($_POST['id'] ?? 0);
    $act = $_POST['action'] ?? '';
    if ($id) {
        if ($act === 'read') {
            $pdo->prepare("UPDATE $tbl SET is_read = 1 WHERE id=?")->execute([$id]);
        } elseif ($act === 'unread') {
            $pdo->prepare("UPDATE $tbl SET is_read = 0 WHERE id=?")->execute([$id]);
        } elseif ($act === 'delete') {
            // CV varsa sil
            if ($tbl === 'job_applications') {
                $st = $pdo->prepare("SELECT cv_path FROM job_applications WHERE id=?");
                $st->execute([$id]);
                delete_upload((string)$st->fetchColumn());
            }
            $pdo->prepare("DELETE FROM $tbl WHERE id=?")->execute([$id]);
            log_activity($tbl . '_deleted', null, "ID: $id");
            flash_set('success', 'Kayıt silindi.');
        }
    }
    header('Location: applications.php?tab=' . urlencode($tab)); exit;
}

$detail_id = (int)($_GET['view'] ?? 0);
$detail = null;
if ($detail_id) {
    $st = $pdo->prepare("SELECT * FROM $tbl WHERE id=?");
    $st->execute([$detail_id]);
    $detail = $st->fetch() ?: null;
    if ($detail && !$detail['is_read']) {
        $pdo->prepare("UPDATE $tbl SET is_read=1 WHERE id=?")->execute([$detail_id]);
        $detail['is_read'] = 1;
    }
}

$rows = $pdo->query("SELECT * FROM $tbl ORDER BY created_at DESC LIMIT 200")->fetchAll();
?>

<div class="card">
  <h2 style="border:0;padding:0;margin:0">
    <?php foreach ($tables as $code => [$ttbl, $tlabel]):
      $cnt = (int)$pdo->query("SELECT COUNT(*) FROM $ttbl WHERE is_read=0")->fetchColumn();
    ?>
      <a href="?tab=<?= e($code) ?>" class="btn btn-sm <?= $tab === $code ? '' : 'btn-line' ?>">
        <?= e($tlabel) ?> <?php if ($cnt): ?><span class="badge b-info"><?= $cnt ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </h2>
</div>

<?php if ($detail): ?>
  <div class="card">
    <h2 style="display:flex;justify-content:space-between;align-items:center">
      <span><?= e($tabLabel) ?> #<?= (int)$detail['id'] ?></span>
      <a href="applications.php?tab=<?= e($tab) ?>" class="btn btn-sm btn-secondary">← Listeye Dön</a>
    </h2>
    <table>
      <?php
      $hidden = ['id','is_read','ip','created_at','cv_path','message','description'];
      foreach ($detail as $k => $v):
          if (in_array($k, $hidden, true)) continue;
          if ($v === null || $v === '') continue;
      ?>
        <tr><th style="width:200px"><?= e($k) ?></th><td><?= e((string)$v) ?></td></tr>
      <?php endforeach; ?>
      <?php if (!empty($detail['message'])): ?>
        <tr><th>Mesaj</th><td><?= nl2br_safe($detail['message']) ?></td></tr>
      <?php endif; ?>
      <?php if (!empty($detail['cv_path'])): ?>
        <tr><th>CV</th><td><a href="<?= e($detail['cv_path']) ?>" target="_blank" class="btn btn-sm btn-line">CV İndir</a></td></tr>
      <?php endif; ?>
      <tr><th>IP / Tarih</th><td><?= e($detail['ip'] ?? '—') ?> &middot; <?= format_date($detail['created_at']) ?></td></tr>
    </table>

    <div class="actions" style="margin-top:14px">
      <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="<?= $detail['is_read'] ? 'unread' : 'read' ?>"><input type="hidden" name="id" value="<?= (int)$detail['id'] ?>"><button class="btn btn-sm btn-secondary"><?= $detail['is_read'] ? 'Okunmadı işaretle' : 'Okundu işaretle' ?></button></form>
      <form method="post" data-confirm="Bu kayıt silinsin mi?" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$detail['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i> Sil</button></form>
    </div>
  </div>

<?php else: ?>
  <div class="card">
    <h2><?= e($tabLabel) ?> (<?= count($rows) ?>)</h2>
    <?php if (!$rows): ?>
      <div class="empty">Henüz kayıt yok.</div>
    <?php else: ?>
      <table>
        <thead><tr><th>#</th><th>Gönderen</th><th>İletişim</th><?php if ($tab === 'contact'): ?><th>Konu</th><?php endif; ?><th>Tarih</th><th>Durum</th><th>İşlem</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r):
          $name = $tab === 'contact'
            ? trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))
            : ($r['full_name'] ?? '—');
        ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><strong><?= e($name) ?></strong></td>
            <td style="font-size:11px"><?= e($r['email'] ?? '') ?><?php if (!empty($r['phone'])): ?><br><?= e($r['phone']) ?><?php endif; ?></td>
            <?php if ($tab === 'contact'): ?>
              <td><?= e(mb_strimwidth($r['subject'] ?? '', 0, 50, '…')) ?></td>
            <?php endif; ?>
            <td><?= format_date($r['created_at']) ?></td>
            <td>
              <?php if (!$r['is_read']): ?><span class="badge b-info">Yeni</span><?php else: ?><span class="badge b-on">Okundu</span><?php endif; ?>
            </td>
            <td class="actions">
              <a class="btn btn-sm btn-line" href="?tab=<?= e($tab) ?>&view=<?= $r['id'] ?>"><i class="fa-solid fa-eye"></i></a>
              <form method="post" data-confirm="Silinsin mi?" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button></form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
