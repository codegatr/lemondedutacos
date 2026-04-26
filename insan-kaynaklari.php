<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$jobs = db()->query(
    "SELECT id, title, employment, location, description FROM jobs
     WHERE is_active = 1 ORDER BY sort_order, id"
)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_required();

    if (!rate_limit('jobapp:' . client_ip(), 3, 1800)) {
        flash_set('error', 'Çok sık başvuru. Lütfen 30 dakika sonra tekrar deneyin.');
        header('Location: /insan-kaynaklari.php#basvuru'); exit;
    }

    $name  = clean_multi($_POST['adsoyad'] ?? '');
    $email = clean_multi($_POST['eposta'] ?? '');
    $phone = clean_multi($_POST['telefon'] ?? '');
    $city  = clean_multi($_POST['sehir'] ?? '');
    $pos   = clean_multi($_POST['pozisyon'] ?? '');
    $msg   = clean_multi($_POST['mesaj'] ?? '');
    $job_id = (int)($_POST['job_id'] ?? 0) ?: null;

    $errors = [];
    if (mb_strlen($name) < 4)  $errors[] = 'Ad-Soyad zorunlu.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli e-posta giriniz.';
    if (mb_strlen($phone) < 10) $errors[] = 'Geçerli telefon giriniz.';

    $cv_path = null;
    if (!$errors) {
        $cv_path = upload_file('cv', 'cv', array_merge(ALLOWED_DOC, ['png','jpg','jpeg']));
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
    } else {
        $stmt = db()->prepare(
            "INSERT INTO job_applications (job_id,full_name,email,phone,city,position,message,cv_path,ip)
             VALUES (?,?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([$job_id, $name, $email, $phone, $city ?: null, $pos ?: null, $msg ?: null, $cv_path, client_ip()]);

        $to = setting('mail_to', MAIL_TO);
        if ($to) {
            $body = "<h3>Yeni İş Başvurusu</h3>"
                  . "<p><b>Ad Soyad:</b> " . e($name) . "</p>"
                  . "<p><b>E-posta:</b> " . e($email) . "</p>"
                  . "<p><b>Telefon:</b> " . e($phone) . "</p>"
                  . ($city ? "<p><b>Şehir:</b> " . e($city) . "</p>" : "")
                  . ($pos ? "<p><b>Pozisyon:</b> " . e($pos) . "</p>" : "")
                  . ($msg ? "<p><b>Notlar:</b><br>" . nl2br_safe($msg) . "</p>" : "")
                  . ($cv_path ? "<p><b>CV:</b> " . e(SITE_URL . $cv_path) . "</p>" : "");
            send_mail($to, 'İş Başvurusu: ' . $name, $body);
        }

        flash_set('success', 'Başvurunuz alındı. Uygun pozisyon olduğunda sizinle iletişime geçeceğiz.');
        header('Location: /insan-kaynaklari.php#basvuru'); exit;
    }
}

$page_slug  = 'kurumsal';
$page_title = 'İnsan Kaynakları';
$page_desc  = 'Açık pozisyonlar ve iş başvurusu';
$extra_css = "
.hr-wrap{max-width:var(--max);margin:0 auto;padding:0 18px 40px}
.hr-hero{background:linear-gradient(135deg,#3A5F0B,#5e8a1a);color:#fff;padding:50px 32px;text-align:center;border-radius:14px;margin:24px 0}
.hr-hero h1{font-size:28px;margin:0 0 8px;line-height:1.2}
.hr-hero p{margin:0;opacity:.95;font-size:14px}
.section-title{font-size:20px;color:var(--brand);text-align:center;margin:30px 0 6px}
.section-desc{text-align:center;color:var(--muted);margin-bottom:18px;font-size:14px}
.value-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:30px}
.value-card{background:#fff;border:1px solid #eef2f7;border-radius:14px;padding:20px;text-align:center}
.value-card h3{color:var(--brand);font-size:15px;margin:0 0 6px}
.value-card p{color:var(--muted);font-size:13px;margin:0;line-height:1.5}
.job-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px;margin-bottom:30px}
.job-card{background:#fff;border:1px solid #eef2f7;border-radius:14px;padding:20px;display:flex;flex-direction:column;gap:8px;cursor:pointer;transition:.2s}
.job-card:hover{transform:translateY(-3px);border-color:var(--brand)}
.job-card .head{display:flex;align-items:center;justify-content:space-between;gap:8px}
.job-card h3{color:var(--brand);font-size:15px;margin:0;flex:1}
.position-badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.4px}
.badge-fulltime{background:#d1fae5;color:#065f46}
.badge-parttime{background:#fef3c7;color:#92400e}
.badge-intern{background:#dbeafe;color:#1e3a8a}
.job-card p{color:var(--muted);font-size:13px;margin:0;line-height:1.5}
.apply-form{background:#fff;border-radius:14px;padding:28px;box-shadow:0 6px 20px rgba(0,0,0,.04)}
.apply-form h2{color:var(--brand);font-size:18px;margin:0 0 16px;text-align:center}
.f-row{margin-bottom:12px}
.f-row label{display:block;font-size:13px;font-weight:600;margin-bottom:4px}
.f-row input,.f-row select,.f-row textarea{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit}
.f-row input:focus,.f-row textarea:focus,.f-row select:focus{outline:0;border-color:var(--brand);box-shadow:0 0 0 3px rgba(58,95,11,.12)}
.f-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.btn-submit{width:100%;background:var(--brand);color:#fff;border:0;padding:13px;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px}
.btn-submit:hover{background:#2c4708}
@media(max-width:680px){.f-grid{grid-template-columns:1fr}}
";
require __DIR__ . '/includes/header.php';
?>

<main class="hr-wrap">
  <div class="hr-hero">
    <h1>Le Monde'da<br><em>Kariyer Yap</em></h1>
    <p>French tacos tutkusunu paylaşan, enerjik ve gelişime açık bir ekibin parçası ol.</p>
  </div>

  <h2 class="section-title">Çalışanlarımıza Değer Veririz</h2>
  <p class="section-desc">Sadece bir iş değil, bir kariyer yolculuğu sunuyoruz.</p>
  <div class="value-grid">
    <div class="value-card"><h3>Hızlı Kariyer Gelişimi</h3><p>Şube açılışları ve büyüyen ekiple birlikte liderlik fırsatları yaratılıyor.</p></div>
    <div class="value-card"><h3>Sürekli Eğitim</h3><p>İşe başlamadan önce tam kapsamlı oryantasyon, devam eden ürün ve servis eğitimleri.</p></div>
    <div class="value-card"><h3>Güçlü Ekip Kültürü</h3><p>Hiyerarşi değil, ekip anlayışı. Fikriniz dinlenir, katkınız görünür olur.</p></div>
    <div class="value-card"><h3>Rekabetçi Ücret</h3><p>Sektör ortalamasının üzerinde maaş, prim sistemi ve çalışan avantajları.</p></div>
  </div>

  <h2 class="section-title">Açık Pozisyonlar</h2>
  <p class="section-desc">Türkiye genelindeki şubelerimizde çeşitli pozisyonlar.</p>
  <?php if ($jobs): ?>
    <div class="job-grid">
      <?php foreach ($jobs as $j): ?>
        <div class="job-card" data-jid="<?= (int)$j['id'] ?>" data-jtitle="<?= e($j['title']) ?>">
          <div class="head">
            <h3><?= e($j['title']) ?></h3>
            <span class="position-badge badge-<?= e($j['employment']) ?>">
              <?= ['fulltime'=>'Tam Zamanlı','parttime'=>'Yarı Zamanlı','intern'=>'Stajyer'][$j['employment']] ?? '' ?>
            </span>
          </div>
          <?php if ($j['location']): ?><div style="font-size:12px;color:var(--muted)"><i class="fa-solid fa-location-dot"></i> <?= e($j['location']) ?></div><?php endif; ?>
          <p><?= e($j['description']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="section-desc">Şu an açık pozisyon bulunmuyor.</p>
  <?php endif; ?>

  <h2 class="section-title" id="basvuru">Başvuru Formu</h2>
  <p class="section-desc">CV'nizi ve iletişim bilgilerinizi paylaşın.</p>
  <form class="apply-form" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="job_id" id="job_id" value="">
    <div class="f-grid">
      <div class="f-row"><label>Ad-Soyad *</label><input type="text" name="adsoyad" required></div>
      <div class="f-row"><label>E-posta *</label><input type="email" name="eposta" required></div>
    </div>
    <div class="f-grid">
      <div class="f-row"><label>Telefon *</label><input type="tel" name="telefon" required></div>
      <div class="f-row"><label>Şehir</label><input type="text" name="sehir"></div>
    </div>
    <div class="f-row"><label>Pozisyon</label><input type="text" name="pozisyon" id="pozisyon" placeholder="Başvurmak istediğiniz pozisyon"></div>
    <div class="f-row"><label>Notunuz</label><textarea name="mesaj" rows="3"></textarea></div>
    <div class="f-row"><label>CV (PDF/DOC/JPG, max <?= MAX_UPLOAD_MB ?> MB)</label><input type="file" name="cv" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"></div>
    <button type="submit" class="btn-submit">Başvuruyu Gönder</button>
  </form>
</main>

<script>
document.querySelectorAll(".job-card").forEach(c=>{
  c.addEventListener("click",()=>{
    document.getElementById("job_id").value=c.dataset.jid;
    document.getElementById("pozisyon").value=c.dataset.jtitle;
    document.getElementById("basvuru").scrollIntoView({behavior:"smooth"});
  });
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
