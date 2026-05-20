<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
admin_require();

// POST handler header.php'den ÖNCE — header HTML render etmeden redirect çalışsın
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_required();
    $fields = [
        'site_name','site_tagline','site_logo','site_logo_alt',
        'site_meta_title','site_meta_description',
        'contact_email','contact_phone','contact_address','contact_hours',
        'social_facebook','social_instagram','social_twitter','social_youtube','social_tiktok',
        'footer_copyright','kvkk_text','commercial_text','mail_to',
    ];
    foreach ($fields as $f) {
        $val = trim((string)($_POST[$f] ?? ''));
        set_setting($f, $val);
    }

    // Logo yükle
    if (!empty($_FILES['logo_upload']['name'])) {
        $url = upload_file('logo_upload', 'sayfa', ALLOWED_IMG);
        if ($url) {
            set_setting('site_logo', $url);
        }
    }

    log_activity('settings_updated', null, 'Site ayarları güncellendi');
    flash_set('success', 'Ayarlar kaydedildi.');
    header('Location: settings.php'); exit;
}

$page_h = 'Site Ayarları';
require __DIR__ . '/_header.php';
?>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="card">
    <h2>Genel</h2>
    <div class="grid-2">
      <div class="row">
        <label>Site Adı</label>
        <input type="text" name="site_name" value="<?= e(setting('site_name')) ?>" required>
      </div>
      <div class="row">
        <label>Slogan / Alt Başlık</label>
        <input type="text" name="site_tagline" value="<?= e(setting('site_tagline')) ?>">
      </div>
    </div>
    <div class="row">
      <label>Logo URL</label>
      <input type="text" name="site_logo" value="<?= e(setting('site_logo')) ?>" placeholder="/static/img/logos/...">
      <div class="help">Mevcut: <?= setting('site_logo') ? '<img src="' . e(asset(setting('site_logo'))) . '" style="height:40px;vertical-align:middle">' : '—' ?></div>
    </div>
    <div class="row">
      <label>Logo Yükle (yeni)</label>
      <input type="file" name="logo_upload" accept="image/*">
    </div>
    <div class="row">
      <label>Logo Alt Metni (erişilebilirlik / SEO)</label>
      <input type="text" name="site_logo_alt" value="<?= e(setting('site_logo_alt', 'TACOS Logo')) ?>" placeholder="TACOS Logo">
    </div>
  </div>

  <div class="card">
    <h2>SEO (Anasayfa)</h2>
    <div class="row">
      <label>Meta Başlık (Anasayfa &lt;title&gt;)</label>
      <input type="text" name="site_meta_title" value="<?= e(setting('site_meta_title')) ?>" placeholder="Le Monde Du Tacos – Orginal Fransız Tacos Lezzeti">
      <div class="help">Anasayfada tarayıcı sekmesinde ve Google sonuçlarında görünür. Boş bırakırsanız Site Adı kullanılır.</div>
    </div>
    <div class="row">
      <label>Meta Açıklama (description &amp; og:description)</label>
      <textarea name="site_meta_description" rows="3" placeholder="Le Monde Du Tacos ile Fransa'nın orijinal tacos lezzetini keşfet!..."><?= e(setting('site_meta_description')) ?></textarea>
      <div class="help">Arama motoru sonuçlarında görünen açıklama. 150-160 karakter ideal.</div>
    </div>
  </div>

  <div class="card">
    <h2>İletişim</h2>
    <div class="grid-3">
      <div class="row">
        <label>E-posta (gösterilen)</label>
        <input type="email" name="contact_email" value="<?= e(setting('contact_email')) ?>">
      </div>
      <div class="row">
        <label>Telefon</label>
        <input type="tel" name="contact_phone" value="<?= e(setting('contact_phone')) ?>">
      </div>
      <div class="row">
        <label>Form mesajları (alıcı e-posta)</label>
        <input type="email" name="mail_to" value="<?= e(setting('mail_to')) ?>">
      </div>
    </div>
    <div class="row">
      <label>Çalışma Saatleri</label>
      <input type="text" name="contact_hours" value="<?= e(setting('contact_hours')) ?>" placeholder="Örn: Her Gün 10:00 – 23:00">
    </div>
    <div class="row">
      <label>Adres</label>
      <textarea name="contact_address" rows="2"><?= e(setting('contact_address')) ?></textarea>
    </div>
  </div>

  <div class="card">
    <h2>Sosyal Medya</h2>
    <div class="grid-2">
      <div class="row"><label><i class="fa-brands fa-tiktok"></i> TikTok</label><input type="url" name="social_tiktok" value="<?= e(setting('social_tiktok')) ?>" placeholder="https://www.tiktok.com/@lemondedutacos"></div>
      <div class="row"><label><i class="fa-brands fa-facebook"></i> Facebook</label><input type="url" name="social_facebook" value="<?= e(setting('social_facebook')) ?>"></div>
      <div class="row"><label><i class="fa-brands fa-instagram"></i> Instagram</label><input type="url" name="social_instagram" value="<?= e(setting('social_instagram')) ?>"></div>
      <div class="row"><label><i class="fa-brands fa-x-twitter"></i> Twitter / X</label><input type="url" name="social_twitter" value="<?= e(setting('social_twitter')) ?>"></div>
      <div class="row"><label><i class="fa-brands fa-youtube"></i> YouTube</label><input type="url" name="social_youtube" value="<?= e(setting('social_youtube')) ?>"></div>
    </div>
    <div class="help">Boş veya <code>#</code> bırakılan ikonlar tıklanabilir olmaz (link gitmez). Footer'da görünmeleri için gerçek URL girin.</div>
  </div>

  <div class="card">
    <h2>Yasal Metinler</h2>
    <div class="row"><label>Telif (footer)</label><input type="text" name="footer_copyright" value="<?= e(setting('footer_copyright')) ?>"></div>
    <div class="row"><label>KVKK Onay Metni</label><textarea name="kvkk_text" rows="2"><?= e(setting('kvkk_text')) ?></textarea></div>
    <div class="row"><label>Ticari İleti Metni</label><textarea name="commercial_text" rows="2"><?= e(setting('commercial_text')) ?></textarea></div>
  </div>

  <button type="submit" class="btn">Ayarları Kaydet</button>
</form>

<?php require __DIR__ . '/_footer.php'; ?>
