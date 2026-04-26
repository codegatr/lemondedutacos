<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$page_slug  = 'kvkk';
$page_title = 'KVKK Aydınlatma Metni';
$page_desc  = '6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında aydınlatma metnidir.';

$company   = e(setting('company_name', 'Tacos Gıda San. ve Tic. A.Ş.'));
$brand     = e(setting('site_name', 'Le Monde Du Tacos'));
$mail      = e(setting('mail_to', 'info@lemondedutacos.com'));
$address   = e(setting('hq_address', 'Bahçelievler, Adnan Kahveci Blv. No:101/B, 34180 Bahçelievler / İstanbul'));
$phone     = e(setting('phone', '+90 212 444 12 34'));

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
  <h1>KVKK Aydınlatma Metni</h1>
  <p>6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında, kişisel verilerinizin işlenmesine ilişkin aydınlatma yükümlülüğümüz dahilinde hazırlanmıştır.</p>
  <div class="legal-meta">Yürürlük: <?= date('d.m.Y') ?></div>
</section>

<main class="legal-wrap">

  <h2>1. Veri Sorumlusu</h2>
  <p>6698 sayılı Kişisel Verilerin Korunması Kanunu ("<strong>KVKK</strong>") uyarınca veri sorumlusu sıfatıyla:</p>
  <div class="legal-info-box">
    <strong>Unvan:</strong> <?= $company ?><br>
    <strong>Marka:</strong> <?= $brand ?><br>
    <strong>Adres:</strong> <?= $address ?><br>
    <strong>E-posta:</strong> <a href="mailto:<?= $mail ?>"><?= $mail ?></a><br>
    <strong>Telefon:</strong> <?= $phone ?>
  </div>

  <h2>2. İşlenen Kişisel Veriler</h2>
  <p>Sizinle olan ilişkimizin niteliğine göre aşağıdaki kişisel verileriniz işlenebilmektedir:</p>
  <ul>
    <li><strong>Kimlik Bilgileri:</strong> Ad, soyad, T.C. kimlik numarası (yalnızca yasal zorunluluk hallerinde)</li>
    <li><strong>İletişim Bilgileri:</strong> Telefon numarası, e-posta adresi, adres bilgileri</li>
    <li><strong>Müşteri İşlem Bilgileri:</strong> Sipariş geçmişi, ödeme bilgileri, hizmet kullanım kayıtları</li>
    <li><strong>İşlem Güvenliği:</strong> IP adresi, tarayıcı bilgisi, çerez kayıtları, log kayıtları</li>
    <li><strong>Pazarlama:</strong> Kampanya, tercih ve memnuniyet anketi yanıtları</li>
    <li><strong>Görsel/İşitsel Kayıtlar:</strong> Şubelerimizdeki güvenlik kameraları (CCTV) kayıtları</li>
    <li><strong>İş Başvurusu:</strong> Özgeçmiş, eğitim ve mesleki deneyim bilgileri (başvuru yapanlar için)</li>
    <li><strong>Franchise Başvurusu:</strong> Yatırım planı, şehir tercihi, finansal kapasite (başvuru yapanlar için)</li>
  </ul>

  <h2>3. Kişisel Verilerin İşlenme Amaçları</h2>
  <p>Kişisel verileriniz, KVKK m.5 ve m.6'da belirtilen kişisel veri işleme şartlarına uygun olarak aşağıdaki amaçlarla işlenmektedir:</p>
  <ul>
    <li>Sipariş ve hizmet süreçlerinin yürütülmesi</li>
    <li>Müşteri memnuniyetinin sağlanması, şikayet ve önerilerin değerlendirilmesi</li>
    <li>Pazarlama ve kampanya faaliyetlerinin yürütülmesi (açık rıza halinde)</li>
    <li>Sözleşme süreçlerinin yürütülmesi (franchise, tedarikçi, çalışan)</li>
    <li>Hukuki yükümlülüklerin yerine getirilmesi</li>
    <li>Mali ve muhasebe işlemlerinin yürütülmesi</li>
    <li>Bilgi güvenliği süreçlerinin yürütülmesi</li>
    <li>Talep ve şikayetlerin takibi</li>
    <li>Yetkili kişi, kurum ve kuruluşlara bilgi verilmesi</li>
    <li>İş sürekliliğinin sağlanması</li>
    <li>İnsan kaynakları süreçlerinin yürütülmesi</li>
    <li>Mekan güvenliği (CCTV ile)</li>
  </ul>

  <h2>4. Kişisel Verilerin Aktarılması</h2>
  <p>Kişisel verileriniz, yukarıda sayılan amaçlar dahilinde, KVKK m.8 ve m.9'a uygun olarak aşağıdaki taraflara aktarılabilir:</p>
  <ul>
    <li><strong>Yetkili kamu kurum ve kuruluşları:</strong> Yasal yükümlülükler kapsamında</li>
    <li><strong>Tedarikçiler ve hizmet sağlayıcılar:</strong> Ödeme kuruluşları, kargo firmaları, IT hizmet sağlayıcıları, e-posta servis sağlayıcıları</li>
    <li><strong>İş ortakları:</strong> Franchise şubeleri (yalnızca ilgili şubeyle ilişkili veriler)</li>
    <li><strong>Hukuk ve mali müşavirler:</strong> Hukuki uyuşmazlıkların çözümü kapsamında</li>
    <li><strong>Bağımsız denetçiler:</strong> Yasal denetim faaliyetleri için</li>
  </ul>
  <p>Kişisel verileriniz yurt dışına aktarılmamaktadır. Açık rıza alınması veya KVKK'da öngörülen istisnalar dışında üçüncü kişilerle paylaşılmaz.</p>

  <h2>5. Kişisel Veri Toplama Yöntemleri</h2>
  <p>Kişisel verileriniz aşağıdaki yöntemlerle toplanmaktadır:</p>
  <ul>
    <li>Web sitemizdeki iletişim, franchise ve iş başvuru formları</li>
    <li>Müşteri hizmetleri telefon görüşmeleri</li>
    <li>E-posta yazışmaları</li>
    <li>Şube içi POS sistemleri ve sipariş alma süreçleri</li>
    <li>Üçüncü taraf online sipariş platformları (anlaşmalı olduğumuz)</li>
    <li>Şube ziyaretleriniz sırasında CCTV sistemleri</li>
    <li>Sosyal medya hesaplarımız üzerinden iletilen mesajlar</li>
  </ul>

  <h2>6. Kişisel Verilerin Hukuki Sebepleri</h2>
  <p>Kişisel verileriniz, KVKK m.5/2 kapsamında aşağıdaki hukuki sebeplere dayalı olarak işlenmektedir:</p>
  <ul>
    <li>Sözleşmenin kurulması veya ifası için gerekli olması</li>
    <li>Hukuki yükümlülüğün yerine getirilmesi</li>
    <li>Bir hakkın tesisi, kullanılması veya korunması</li>
    <li>Veri sorumlusunun meşru menfaatleri</li>
    <li>İlgili kişinin açık rızası (yasal zorunlulukların kapsamı dışında kalan haller için)</li>
  </ul>

  <h2>7. Kişisel Veri Sahibinin Hakları (KVKK m.11)</h2>
  <p>KVKK m.11 uyarınca veri sahibi olarak aşağıdaki haklara sahipsiniz:</p>
  <ol>
    <li>Kişisel verilerinizin işlenip işlenmediğini öğrenme</li>
    <li>İşlenmişse buna ilişkin bilgi talep etme</li>
    <li>Kişisel verilerinizin işlenme amacını ve bunların amacına uygun kullanılıp kullanılmadığını öğrenme</li>
    <li>Yurt içinde veya yurt dışında verilerinizin aktarıldığı üçüncü kişileri bilme</li>
    <li>Verilerinizin eksik veya yanlış işlenmiş olması halinde bunların düzeltilmesini isteme</li>
    <li>KVKK m.7'de öngörülen şartlar çerçevesinde kişisel verilerin silinmesini veya yok edilmesini isteme</li>
    <li>Düzeltme/silme/yok etme işlemlerinin verilerin aktarıldığı üçüncü kişilere bildirilmesini isteme</li>
    <li>İşlenen verilerin münhasıran otomatik sistemler aracılığıyla analiz edilmesi suretiyle aleyhinize bir sonucun ortaya çıkmasına itiraz etme</li>
    <li>Kişisel verilerinizin kanuna aykırı işlenmesi sebebiyle zarara uğramanız halinde zararın giderilmesini talep etme</li>
  </ol>

  <h2>8. Başvuru Yöntemi</h2>
  <p>Yukarıda belirtilen haklarınızı kullanmak için başvurularınızı:</p>
  <ul>
    <li><strong>Yazılı olarak:</strong> Yukarıdaki adrese ıslak imzalı dilekçe ile</li>
    <li><strong>Kayıtlı Elektronik Posta (KEP) ile</strong> </li>
    <li><strong>E-posta ile:</strong> <a href="mailto:<?= $mail ?>"><?= $mail ?></a> adresine güvenli elektronik imzalı olarak</li>
  </ul>
  <p>Başvurunuzda kimliğinizi tespit edici belgeler ve KVKK m.13/1 uyarınca açıklamalı talebinizin yer alması gerekmektedir. Veri Sorumlusu, talebinizi en geç 30 (otuz) gün içinde ücretsiz olarak sonuçlandıracaktır. Ancak işlemin maliyeti gerektirmesi halinde Kurul tarafından belirlenen tarifedeki ücret alınabilir.</p>

  <div class="legal-info-box">
    <strong>Şikayet Hakkı:</strong> Başvurunuzun reddedilmesi, verilen yanıtın yetersiz bulunması veya 30 gün içinde yanıt verilmemesi halinde, KVKK m.14 uyarınca Kişisel Verileri Koruma Kuruluna şikayette bulunabilirsiniz.
  </div>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
