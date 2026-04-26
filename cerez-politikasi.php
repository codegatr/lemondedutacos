<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$page_slug  = 'cerez';
$page_title = 'Çerez Politikası';
$page_desc  = 'Web sitemizde kullanılan çerezler hakkında bilgilendirmedir.';

$company   = e(setting('company_name', 'Tacos Gıda San. ve Tic. A.Ş.'));
$brand     = e(setting('site_name', 'Le Monde Du Tacos'));
$mail      = e(setting('mail_to', 'info@lemondedutacos.com'));

$extra_css = <<<CSS
.legal-hero{background:linear-gradient(135deg,#1a2e0a 0%,#2d4f11 50%,#1a2e0a 100%);padding:70px 18px 50px;text-align:center;color:#fff}
.legal-hero h1{font-family:Georgia,serif;font-size:clamp(28px,4vw,44px);font-weight:700;font-style:italic;margin-bottom:10px}
.legal-hero p{color:rgba(255,255,255,.75);font-size:14px;max-width:640px;margin:0 auto;line-height:1.7}
.legal-meta{display:inline-block;margin-top:14px;padding:6px 16px;background:rgba(245,197,24,.15);border:1px solid rgba(245,197,24,.4);border-radius:20px;font-size:12px;color:#f5c518;letter-spacing:1px}

.legal-wrap{max-width:880px;margin:0 auto;padding:50px 20px 80px}
.legal-wrap h2{font-family:Georgia,serif;font-size:22px;font-weight:700;font-style:italic;color:var(--brand);margin:34px 0 14px;padding-bottom:8px;border-bottom:2px solid #e5e7eb}
.legal-wrap h2:first-of-type{margin-top:0}
.legal-wrap h3{font-family:Georgia,serif;font-size:17px;font-weight:700;color:var(--ink);margin:20px 0 10px}
.legal-wrap p{font-size:14.5px;line-height:1.8;color:var(--ink);margin-bottom:14px}
.legal-wrap ul,.legal-wrap ol{margin:8px 0 14px 26px;font-size:14.5px;line-height:1.8}
.legal-wrap li{margin-bottom:6px}
.legal-wrap a{color:var(--brand);text-decoration:underline}
.legal-info-box{background:#f9fafb;border-left:4px solid var(--brand);border-radius:6px;padding:18px 22px;margin:18px 0;font-size:14px;line-height:1.7}
.legal-info-box strong{color:var(--brand)}
.legal-table{width:100%;border-collapse:collapse;margin:16px 0;font-size:13px}
.legal-table th,.legal-table td{padding:10px 12px;text-align:left;border:1px solid #e5e7eb;vertical-align:top}
.legal-table th{background:#f9fafb;font-weight:700;color:var(--brand);font-size:12px;text-transform:uppercase;letter-spacing:.5px}
CSS;

require __DIR__ . '/includes/header.php';
?>

<section class="legal-hero">
  <h1>Çerez Politikası</h1>
  <p><?= $brand ?> web sitesini ziyaret ettiğinizde bilgisayarınızda veya mobil cihazınızda saklanabilen çerezler (cookies) hakkında bilgi edinmek için lütfen okuyun.</p>
  <div class="legal-meta">Yürürlük: <?= date('d.m.Y') ?></div>
</section>

<main class="legal-wrap">

  <h2>1. Çerez Nedir?</h2>
  <p>Çerezler (cookies), bir web sitesini ziyaret ettiğinizde tarayıcınız aracılığıyla bilgisayarınızda veya mobil cihazınızda saklanan küçük metin dosyalarıdır. Bu dosyalar; site tercihlerinizi hatırlamak, oturumunuzu açık tutmak, ziyaret deneyiminizi iyileştirmek ve istatistiksel ölçümler yapmak için kullanılır.</p>
  <p>Çerezler kötü amaçlı yazılım değildir; bilgisayarınıza zarar vermez ve virüs içermez. Çerezler kişisel bilgilerinizi otomatik olarak depolamaz; ancak ziyaret bilgilerinizi ve tercihlerinizi içerebilir.</p>

  <h2>2. Çerez Türleri</h2>

  <h3>2.1. Süreye Göre</h3>
  <ul>
    <li><strong>Oturum Çerezleri:</strong> Tarayıcınızı kapattığınızda otomatik olarak silinir. Geçici sepet bilgisi, oturum yönetimi gibi işlevler için kullanılır.</li>
    <li><strong>Kalıcı Çerezler:</strong> Belirlenen süre boyunca cihazınızda saklanır. Tercihlerinizin (dil, gizlilik onayları vb.) hatırlanmasını sağlar.</li>
  </ul>

  <h3>2.2. Sahibine Göre</h3>
  <ul>
    <li><strong>Birinci Taraf Çerezler:</strong> Doğrudan ziyaret ettiğiniz <?= $brand ?> web sitesi tarafından oluşturulan çerezlerdir.</li>
    <li><strong>Üçüncü Taraf Çerezler:</strong> Web sitemizin entegre olduğu hizmetler tarafından oluşturulan çerezlerdir (analitik araçlar, sosyal medya entegrasyonları vb.).</li>
  </ul>

  <h3>2.3. Amacına Göre</h3>
  <ul>
    <li><strong>Zorunlu Çerezler:</strong> Sitenin çalışması için gereklidir. Engellenirse bazı sayfa veya hizmetler kullanılamaz hale gelir. Bu çerezler için izin gerekli değildir.</li>
    <li><strong>İşlevsel Çerezler:</strong> Site tercihlerinizi (dil, kullanıcı arayüzü) hatırlar, ziyaret deneyiminizi kişiselleştirir.</li>
    <li><strong>Performans/Analitik Çerezler:</strong> Hangi sayfaların ne sıklıkta ziyaret edildiği, hangi içeriklerin daha çok ilgi gördüğü gibi anonim kullanım istatistikleri toplar.</li>
    <li><strong>Pazarlama/Hedefleme Çerezleri:</strong> Reklam ve kampanyaların etkinliğini ölçmek, kişiselleştirilmiş içerik göstermek için kullanılır. Yalnızca açık rızanızla aktif olur.</li>
  </ul>

  <h2>3. Sitemizde Kullanılan Çerezler</h2>
  <table class="legal-table">
    <thead>
      <tr>
        <th>Çerez Adı</th>
        <th>Türü</th>
        <th>Amacı</th>
        <th>Süresi</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><code>PHPSESSID</code></td>
        <td>Zorunlu</td>
        <td>Oturum yönetimi, form gönderimi (CSRF korumasında kullanılır)</td>
        <td>Oturum süresi</td>
      </tr>
      <tr>
        <td><code>cookie_consent</code></td>
        <td>Zorunlu</td>
        <td>Çerez izinlerinizin saklanması</td>
        <td>365 gün</td>
      </tr>
      <tr>
        <td><code>_ga, _ga_*</code></td>
        <td>Analitik</td>
        <td>Google Analytics — anonim ziyaretçi istatistikleri (kullanılırsa)</td>
        <td>730 gün</td>
      </tr>
      <tr>
        <td><code>fbp, _fbp</code></td>
        <td>Pazarlama</td>
        <td>Facebook Pixel — reklam ölçümleme (kullanılırsa)</td>
        <td>90 gün</td>
      </tr>
    </tbody>
  </table>

  <p><em>Not: Yukarıdaki tablo örnek niteliğindedir. Kullanılan çerezlerin tamamı tarayıcınızın geliştirici araçlarından (F12 → Application → Cookies) görüntülenebilir.</em></p>

  <h2>4. Çerez Yönetimi</h2>
  <p>Çerez izinlerinizi tarayıcınızdan dilediğiniz zaman değiştirebilirsiniz. Tarayıcı bazlı çerez yönetimi adımları:</p>

  <ul>
    <li><strong>Google Chrome:</strong> Ayarlar → Gizlilik ve Güvenlik → Çerezler ve diğer site verileri</li>
    <li><strong>Mozilla Firefox:</strong> Ayarlar → Gizlilik ve Güvenlik → Çerezler ve Site Verileri</li>
    <li><strong>Safari:</strong> Tercihler → Gizlilik → Çerezler ve web sitesi verileri</li>
    <li><strong>Microsoft Edge:</strong> Ayarlar → Çerezler ve site izinleri → Çerezleri yönet ve sil</li>
    <li><strong>Mobil cihazlar:</strong> Tarayıcının ayarlar menüsünden gizlilik bölümü</li>
  </ul>

  <div class="legal-info-box">
    <strong>Önemli:</strong> Zorunlu çerezleri devre dışı bırakırsanız sitemizin bazı bölümleri (form gönderimi, oturum yönetimi) düzgün çalışmayabilir. Tüm çerezleri reddetmek istiyorsanız tarayıcınızın gizli/incognito modunu kullanmanızı öneririz.
  </div>

  <h2>5. Çerezler ile Toplanan Veriler</h2>
  <p>Çerezler aracılığıyla aşağıdaki veriler toplanabilir:</p>
  <ul>
    <li>IP adresi (anonimleştirilmiş)</li>
    <li>Tarayıcı türü ve sürümü</li>
    <li>İşletim sistemi</li>
    <li>Ekran çözünürlüğü</li>
    <li>Ziyaret edilen sayfalar ve süreleri</li>
    <li>Tıklama davranışları (analitik amaçla, anonim)</li>
    <li>Yönlendirme kaynağı (referrer)</li>
  </ul>
  <p>Bu veriler, kişiyi tek başına tanımlama amacı taşımaz. Toplanan analitik veriler kümülatif (toplu) olarak değerlendirilir.</p>

  <h2>6. Üçüncü Taraf Çerezler</h2>
  <p>Web sitemiz aşağıdaki üçüncü taraf hizmetlerden çerezler içerebilir:</p>
  <ul>
    <li><strong>Google Analytics:</strong> Anonim istatistikler için. <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Gizlilik politikası</a></li>
    <li><strong>Google Maps:</strong> Şube konumu gösterimi için. <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Gizlilik politikası</a></li>
    <li><strong>Sosyal medya butonları:</strong> Facebook, Instagram, YouTube ikonları sosyal medya hesaplarımıza yönlendirir; bu siteler kendi çerezlerini kullanabilir.</li>
  </ul>

  <h2>7. Kişisel Verilerin Korunması</h2>
  <p>Çerezler aracılığıyla işlenen kişisel veriler 6698 sayılı KVKK kapsamında korunmaktadır. Detaylı bilgi için <a href="/kvkk.php">KVKK Aydınlatma Metnimizi</a> inceleyebilirsiniz.</p>

  <h2>8. Politika Değişiklikleri</h2>
  <p>Bu Çerez Politikası, mevzuat değişiklikleri veya kullandığımız çerezlerin değişmesi halinde güncellenebilir. Yapılan değişiklikler bu sayfada yayımlandığı tarihten itibaren geçerlilik kazanır. Politikayı düzenli olarak gözden geçirmenizi öneririz.</p>

  <h2>9. İletişim</h2>
  <p>Çerez politikamız hakkında sorularınız için: <a href="mailto:<?= $mail ?>"><?= $mail ?></a></p>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
