<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$page_slug  = 'gizlilik';
$page_title = 'Gizlilik Politikası';
$page_desc  = 'Kişisel verilerinizin korunması ve gizliliğine ilişkin politikamızdır.';

$company   = e(setting('company_name', 'Tacos Gıda San. ve Tic. A.Ş.'));
$brand     = e(setting('site_name', 'Le Monde Du Tacos'));
$mail      = e(setting('mail_to', 'info@lemondedutacos.com'));
$address   = e(setting('hq_address', 'Bahçelievler, Adnan Kahveci Blv. No:101/B, 34180 Bahçelievler / İstanbul'));

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
CSS;

require __DIR__ . '/includes/header.php';
?>

<section class="legal-hero">
  <h1>Gizlilik Politikası</h1>
  <p><?= $brand ?> olarak, müşterilerimizin ve ziyaretçilerimizin gizliliğine değer veriyoruz. Bu politika; topladığımız bilgileri, nasıl kullandığımızı ve haklarınızı açıklar.</p>
  <div class="legal-meta">Yürürlük: <?= date('d.m.Y') ?></div>
</section>

<main class="legal-wrap">

  <h2>1. Giriş</h2>
  <p>Bu Gizlilik Politikası, <?= $company ?> ("<strong><?= $brand ?></strong>", "biz", "şirket") tarafından işletilen web sitesinde ve dijital iletişim kanallarımızda toplanan kişisel verilerin nasıl işlendiğini açıklamaktadır. Sitemizi kullanmanız bu politikayı kabul ettiğiniz anlamına gelir.</p>
  <p>Gizliliğinize saygı duyuyoruz. Bilgilerinizi 6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) ve diğer ilgili mevzuata uygun olarak işliyoruz.</p>

  <h2>2. Topladığımız Bilgiler</h2>

  <h3>2.1. Doğrudan Tarafımıza İlettiğiniz Bilgiler</h3>
  <ul>
    <li><strong>İletişim formu:</strong> Ad, soyad, e-posta, telefon, mesaj içeriği, şube tercihi, memnuniyet puanı</li>
    <li><strong>Franchise başvurusu:</strong> Ad-soyad, telefon, e-posta, şehir, yaş, yatırım kapasitesi, kvkk/ticari elektronik ileti onayları</li>
    <li><strong>İş başvurusu:</strong> Ad-soyad, e-posta, telefon, başvurulan pozisyon, tercih edilen şube, özgeçmiş (CV) dosyası, motivasyon yazısı</li>
    <li><strong>Sipariş bilgileri:</strong> Şubeye yapılan sipariş ve ödeme bilgileri (online sipariş entegre platformları üzerinden)</li>
  </ul>

  <h3>2.2. Otomatik Toplanan Bilgiler</h3>
  <ul>
    <li>IP adresi ve coğrafi konum (yaklaşık şehir bazlı)</li>
    <li>Tarayıcı türü, dil tercihi, ekran çözünürlüğü</li>
    <li>İşletim sistemi ve cihaz türü</li>
    <li>Ziyaret tarihleri, ziyaret edilen sayfalar, kalış süreleri</li>
    <li>Yönlendirme bilgileri (referrer URL)</li>
    <li>Çerez ve benzeri teknolojiler aracılığıyla toplanan veriler — detay için <a href="/cerez-politikasi.php">Çerez Politikamıza</a> bakınız</li>
  </ul>

  <h3>2.3. Üçüncü Taraflardan Aldığımız Bilgiler</h3>
  <ul>
    <li>Online sipariş platformlarından (Yemeksepeti, Getir, Trendyol Yemek vb.) gelen sipariş bilgileri</li>
    <li>Sosyal medya entegrasyonları üzerinden iletilen mesajlar</li>
    <li>Ödeme kuruluşları üzerinden işlem onayı bilgileri</li>
  </ul>

  <h2>3. Bilgileri Nasıl Kullanıyoruz</h2>
  <p>Topladığımız bilgileri aşağıdaki amaçlarla kullanıyoruz:</p>
  <ul>
    <li>Talep ve sorularınıza yanıt vermek</li>
    <li>Sipariş ve hizmet süreçlerini yürütmek</li>
    <li>Franchise ve iş başvurularınızı değerlendirmek</li>
    <li>Müşteri memnuniyetini sağlamak ve şikayetleri çözmek</li>
    <li>Sitemizin performansını ölçmek ve iyileştirmek</li>
    <li>Pazarlama ve kampanya iletişimi yapmak (yalnızca açık rıza varsa)</li>
    <li>Yasal yükümlülüklerimizi yerine getirmek</li>
    <li>Dolandırıcılığı ve kötüye kullanımı önlemek</li>
    <li>Bilgi güvenliğini sağlamak</li>
  </ul>

  <h2>4. Bilgilerin Paylaşımı</h2>
  <p>Bilgilerinizi <strong>satmıyoruz, kiralamıyoruz veya pazarlama amaçlı üçüncü kişilere açmıyoruz</strong>. Verileriniz yalnızca aşağıdaki durumlarda paylaşılabilir:</p>

  <h3>4.1. Hizmet Sağlayıcılarımız</h3>
  <p>Sitemizin işleyişi için aşağıdaki hizmet sağlayıcılarla sınırlı veri paylaşımı yapılabilir:</p>
  <ul>
    <li><strong>Hosting ve altyapı:</strong> Web sitesinin barındırılması</li>
    <li><strong>E-posta servisi:</strong> Sistem bildirimlerinin gönderilmesi</li>
    <li><strong>Ödeme kuruluşları:</strong> Ödeme işlemlerinin yapılması (yalnızca anlaşmalı, PCI-DSS sertifikalı kuruluşlar)</li>
    <li><strong>Analitik araçlar:</strong> Anonim kullanım istatistikleri</li>
  </ul>

  <h3>4.2. Franchise Şubeleri</h3>
  <p>Belirli bir şubeyle ilgili iletişim, sipariş veya başvuru bilgileri ilgili franchise şubesiyle paylaşılır. Ancak şube, bu bilgileri yalnızca size hizmet vermek amacıyla kullanır.</p>

  <h3>4.3. Yasal Zorunluluklar</h3>
  <p>Mahkeme kararı, savcılık talebi veya yasal zorunluluk halinde yetkili kamu kurum ve kuruluşlarıyla bilgi paylaşılabilir.</p>

  <h2>5. Veri Saklama Süresi</h2>
  <p>Kişisel verilerinizi yalnızca toplama amacının gerektirdiği süre boyunca veya yasal saklama yükümlülükleri çerçevesinde saklarız:</p>
  <ul>
    <li>İletişim formu mesajları: <strong>2 yıl</strong></li>
    <li>İş başvurusu kayıtları: <strong>1 yıl</strong> (başvuru reddedildikten sonra)</li>
    <li>Franchise başvuruları: <strong>3 yıl</strong></li>
    <li>Sipariş ve fatura bilgileri: <strong>10 yıl</strong> (Vergi Usul Kanunu gereği)</li>
    <li>CCTV kayıtları: <strong>30-90 gün</strong> (şube ve olay bazlı)</li>
    <li>Web sunucu logları: <strong>6 ay</strong></li>
  </ul>

  <h2>6. Veri Güvenliği</h2>
  <p>Bilgilerinizi korumak için endüstri standardı güvenlik önlemleri alıyoruz:</p>
  <ul>
    <li>SSL/TLS ile şifrelenmiş veri iletimi (HTTPS)</li>
    <li>Şifrelenmiş veri tabanı yedeklemeleri</li>
    <li>Yetkisiz erişimi önlemek için rol bazlı erişim kontrolü</li>
    <li>Düzenli güvenlik güncellemeleri</li>
    <li>Şifrelerin bcrypt algoritması ile hashlenerek saklanması</li>
    <li>CSRF, XSS ve SQL injection korumaları</li>
    <li>Aktivite logları ve denetim izleri</li>
  </ul>
  <p>Buna rağmen internet üzerinden veri iletiminin %100 güvenli olmadığını hatırlatmak isteriz. Hiçbir sistem mutlak güvenlik garantisi veremez.</p>

  <h2>7. Çocukların Gizliliği</h2>
  <p>Hizmetlerimiz <strong>13 yaş altı çocuklara</strong> yönelik değildir. Bilerek 13 yaş altı çocuklardan kişisel veri toplamayız. Eğer bir çocuğun verilerinin tarafımıza iletildiğini fark ederseniz lütfen bizimle iletişime geçin; bu verileri en kısa sürede sileriz.</p>

  <h2>8. Haklarınız</h2>
  <p>KVKK kapsamında aşağıdaki haklara sahipsiniz:</p>
  <ul>
    <li>Verilerinizin işlenip işlenmediğini öğrenme</li>
    <li>İşleme amacı ve kapsamı hakkında bilgi alma</li>
    <li>Yanlış/eksik verilerin düzeltilmesini talep etme</li>
    <li>Belirli koşullarda verilerin silinmesini isteme</li>
    <li>Otomatik analizlere itiraz etme</li>
    <li>Açık rızayı geri çekme</li>
  </ul>
  <p>Detaylı bilgi ve başvuru yöntemleri için <a href="/kvkk.php">KVKK Aydınlatma Metnimizi</a> inceleyebilirsiniz.</p>

  <h2>9. Pazarlama İletişimi</h2>
  <p>Açık rızanız ile size kampanya, indirim, yeni şube açılışı gibi konularda e-posta veya SMS gönderebiliriz. Bu iletişimi her zaman <a href="mailto:<?= $mail ?>"><?= $mail ?></a> adresine "ABONELİKTEN ÇIK" yazarak veya gönderdiğimiz mesajların alt kısmındaki bağlantıyla durdurabilirsiniz. Sistemsel ve hukuki bildirimler bu kapsam dışındadır.</p>

  <h2>10. Politika Değişiklikleri</h2>
  <p>Bu politikayı zaman zaman güncelleyebiliriz. Önemli değişiklikler olması halinde web sitemizde duyuru yapılacak ve gerekirse e-posta yoluyla bilgilendirme sağlanacaktır. Politikanın yürürlük tarihi yukarıda belirtilmiştir; düzenli olarak gözden geçirmenizi öneririz.</p>

  <h2>11. İletişim</h2>
  <div class="legal-info-box">
    <strong>Veri Sorumlusu:</strong> <?= $company ?><br>
    <strong>Adres:</strong> <?= $address ?><br>
    <strong>E-posta:</strong> <a href="mailto:<?= $mail ?>"><?= $mail ?></a><br><br>
    Gizlilik politikamızla ilgili tüm soru ve talepleriniz için yukarıdaki kanallardan bizimle iletişime geçebilirsiniz.
  </div>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
