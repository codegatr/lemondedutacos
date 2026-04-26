<?php
declare(strict_types=1);
$page_h = 'Pano';
require __DIR__ . '/_header.php';

$pdo = db();

$counts = [
    'menu_items'  => (int)$pdo->query("SELECT COUNT(*) FROM menu_items WHERE is_active=1")->fetchColumn(),
    'branches'    => (int)$pdo->query("SELECT COUNT(*) FROM branches WHERE is_active=1")->fetchColumn(),
    'campaigns'   => (int)$pdo->query("SELECT COUNT(*) FROM campaigns WHERE is_active=1")->fetchColumn(),
    'jobs'        => (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE is_active=1")->fetchColumn(),
];

$inbox = [
    'contact_unread'   => (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn(),
    'franchise_unread' => (int)$pdo->query("SELECT COUNT(*) FROM franchise_applications WHERE is_read=0")->fetchColumn(),
    'jobapp_unread'    => (int)$pdo->query("SELECT COUNT(*) FROM job_applications WHERE is_read=0")->fetchColumn(),
];

$recent_msgs = $pdo->query(
    "SELECT id, first_name, last_name, subject, created_at, is_read FROM contact_messages
     ORDER BY created_at DESC LIMIT 5"
)->fetchAll();

$recent_log = $pdo->query(
    "SELECT al.action, al.detail, al.created_at, au.username
     FROM activity_log al LEFT JOIN admin_users au ON au.id = al.admin_id
     ORDER BY al.created_at DESC LIMIT 8"
)->fetchAll();
?>

<div class="grid-4">
  <div class="metric"><div class="lbl">Aktif Menü Ürünü</div><div class="val"><?= $counts['menu_items'] ?></div></div>
  <div class="metric"><div class="lbl">Aktif Şube</div><div class="val"><?= $counts['branches'] ?></div></div>
  <div class="metric"><div class="lbl">Aktif Kampanya</div><div class="val"><?= $counts['campaigns'] ?></div></div>
  <div class="metric"><div class="lbl">Açık Pozisyon</div><div class="val"><?= $counts['jobs'] ?></div></div>
</div>

<div class="grid-3" style="margin-top:18px">
  <a href="applications.php?tab=contact" class="metric" style="text-decoration:none;color:inherit">
    <div class="lbl">Okunmamış Mesaj</div>
    <div class="val"><?= $inbox['contact_unread'] ?></div>
    <div class="delta">İletişim formu mesajları</div>
  </a>
  <a href="applications.php?tab=franchise" class="metric" style="text-decoration:none;color:inherit">
    <div class="lbl">Yeni Franchise Başvurusu</div>
    <div class="val"><?= $inbox['franchise_unread'] ?></div>
    <div class="delta">Bayilik başvuruları</div>
  </a>
  <a href="applications.php?tab=jobs" class="metric" style="text-decoration:none;color:inherit">
    <div class="lbl">Yeni İş Başvurusu</div>
    <div class="val"><?= $inbox['jobapp_unread'] ?></div>
    <div class="delta">Kariyer başvuruları</div>
  </a>
</div>

<div class="grid-2" style="margin-top:18px">
  <div class="card">
    <h2>Son İletişim Mesajları</h2>
    <?php if (!$recent_msgs): ?>
      <div class="empty">Henüz mesaj yok.</div>
    <?php else: ?>
      <table>
        <thead><tr><th>Gönderen</th><th>Konu</th><th>Tarih</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recent_msgs as $m): ?>
          <tr>
            <td><?= e($m['first_name'] . ' ' . $m['last_name']) ?></td>
            <td><?= e(mb_strimwidth($m['subject'], 0, 40, '…')) ?></td>
            <td><?= format_date($m['created_at']) ?></td>
            <td><?php if (!$m['is_read']): ?><span class="badge b-info">Yeni</span><?php endif; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2>Son Aktiviteler</h2>
    <?php if (!$recent_log): ?>
      <div class="empty">Henüz kayıt yok.</div>
    <?php else: ?>
      <table>
        <thead><tr><th>Kullanıcı</th><th>İşlem</th><th>Tarih</th></tr></thead>
        <tbody>
        <?php foreach ($recent_log as $l): ?>
          <tr>
            <td><?= e($l['username'] ?? '-') ?></td>
            <td><?= e($l['action']) ?><?= $l['detail'] ? '<br><small style="color:#9ca3af">' . e($l['detail']) . '</small>' : '' ?></td>
            <td><?= format_date($l['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
