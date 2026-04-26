# Le Monde Du Tacos – PHP CMS

`lemondedutacos.com` için tam veritabanı tabanlı içerik yönetim sistemi.
Statik HTML siteden PHP 8.3 + MySQL'e tam dönüşüm.

## Sürüm

**v1.0.0** — Nisan 2026

## Özellikler

- 4 menü grubu (Tacos / Bun / Burger / Tatlı), tüm ürünler DB'den
- Hero slider, anasayfa pop-up kartları
- Şubeler, Kampanyalar (tarih aralıklı), İş İlanları
- İletişim, Franchise, Kariyer formları (CSRF + rate limit + e-posta bildirimi)
- Statik içerik sayfaları (Hakkımızda, Üretim, Medya, Tarihçe)
- Kurumsal hub (alt sayfalara linkler)
- 13 modüllü yönetim paneli (rol bazlı yetki)
- GitHub Releases tabanlı tek-tıkla güncelleme sistemi
- Aktivite günlüğü, başvuru gelen kutusu, dosya yükleme

## Sistem Gereksinimleri

- PHP 8.1+ (önerilen 8.3)
- MySQL 5.7+ / MariaDB 10.3+
- Apache / LiteSpeed (mod_rewrite, mod_headers, mod_deflate, mod_expires)
- DirectAdmin uyumlu

PHP eklentileri: `pdo_mysql`, `mbstring`, `fileinfo`, `zip`, `openssl`, `gd` (opsiyonel).

## Dizin Yapısı

```
public_html/
├── index.php                    # Anasayfa
├── tacos-menu.php / bun-menu.php / burger-menu.php / tatli-menu.php
├── subeler.php / kampanyalar.php / iletisim.php
├── kurumsal.php / hakkimizda.php / tarihce.php / uretim.php / medya.php
├── franchise.php / insan-kaynaklari.php
├── 404.php
├── install.php                  # KURULUM (tek seferlik)
├── manifest.json                # Sürüm bilgisi
├── version.txt
├── .htaccess
├── includes/
│   ├── config.php               # DB, e-posta, GitHub ayarları
│   ├── db.php                   # PDO bağlantı
│   ├── functions.php            # Yardımcı fonksiyonlar (CSRF, flash, rate limit, vs.)
│   ├── header.php / footer.php  # Site şablonu
│   ├── menu_render.php          # 4 menü sayfası için ortak şablon
│   ├── page_render.php          # Statik sayfa şablonu
│   ├── schema.sql               # 19 tablo
│   └── seed.sql                 # Başlangıç verisi
├── uploads/                     # Yüklenen dosyalar (.htaccess ile PHP yürütme yasak)
├── static/                      # Logolar, fontlar, görseller (orijinal asset'ler)
└── admin/                       # Yönetim paneli (13 modül)
    ├── index.php (dashboard) / login.php / logout.php
    ├── settings.php / slider.php / menu.php / promo-cards.php
    ├── branches.php / campaigns.php / jobs.php
    ├── applications.php / pages.php / timeline.php
    ├── users.php / update.php
    └── _header.php / _footer.php / _crud.php
```

## Kurulum

### 1. Dosyaları yükleyin

ZIP içeriğini DirectAdmin'de `domains/lemondedutacos.com/public_html/` altına çıkarın.

### 2. Veritabanı oluşturun

DirectAdmin → MySQL Management → "Create new database":

- Database adı: `lmdt_main` (örnek)
- Kullanıcı: `lmdt_user`
- Parola: güçlü bir parola

### 3. config.php'yi düzenleyin

`includes/config.php` dosyasını açın ve şu satırları doldurun:

```php
const DB_HOST = 'localhost';
const DB_NAME = 'lmdt_main';
const DB_USER = 'lmdt_user';
const DB_PASS = '••••••••';

const SITE_URL  = 'https://lemondedutacos.com';

// E-posta alıcısı (form mesajları nereye düşsün?)
const MAIL_TO = 'info@lemondedutacos.com';

// GitHub güncelleme sistemi (public repo ise token'ı boş bırakın)
const GITHUB_OWNER = 'codegatr';
const GITHUB_REPO  = 'lemondedutacos';
const GITHUB_TOKEN = '';   // private repo için PAT
```

### 4. Kurulum sayfasını çalıştırın

Tarayıcıdan açın:

```
https://lemondedutacos.com/install.php
```

Form size yönetici hesabı oluşturmanızı isteyecek. Kurulum:

1. `schema.sql` ile 19 tabloyu oluşturur
2. `seed.sql` ile başlangıç verisini yükler (4 menü grubu, 51 ürün, 12 şube, 6 ilan, ...)
3. Admin hesabınızı oluşturur
4. `SECRET_KEY` üretir
5. `install.lock` dosyası oluşturur (yeniden kurulum engellenir)

### 5. Yönetim paneline giriş

```
https://lemondedutacos.com/admin/
```

Kurulumda belirlediğiniz kullanıcı adı ve parola ile giriş yapın.

### 6. (Önerilir) install.php'yi silin

Kurulum bittikten sonra güvenlik için bu dosyayı sunucudan silebilirsiniz.

## GitHub Güncelleme Sistemi

Yunus'un standart pattern'iyle:

1. Yeni bir sürüm GitHub'a push edin
2. Repo'da bir Release oluşturun (örn `v1.0.1`)
3. Release'e `lemondedutacos-v1.0.1.zip` adıyla bir asset ekleyin (veya Source code zip otomatik kullanılır)
4. Yönetim panelinde **Güncelleme** sayfasına girin
5. **Güncellemeyi Şimdi Başlat** butonuna basın

Sistem otomatik olarak:

- ZIP dosyasını indirir
- `/tmp` altında açar
- Tüm dosyaları kopyalar (kopyalama sırasında **`uploads/`, `includes/config.php`, `install.lock`** korunur)
- `version.txt` ve `config.php` içindeki `APP_VERSION` değerini günceller
- `update_history` tablosuna log düşer

Private repo ise `config.php` içindeki `GITHUB_TOKEN` dolu olmalıdır.

## Form Verileri

Tüm form gönderimleri DB'ye kaydedilir + ayarlanan `mail_to` adresine HTML e-posta gönderilir:

- **İletişim formu** → `contact_messages` tablosu
- **Franchise başvurusu** → `franchise_applications` tablosu
- **İş başvurusu** → `job_applications` tablosu (CV `/uploads/cv/` altına yüklenir)

Yönetim panelinde **Başvurular & Mesajlar** sayfasından okunabilir, silinebilir.

## Güvenlik

- Tüm form gönderimleri CSRF token ile korunmaktadır
- IP başına rate limit (iletişim 5/10dk, başvurular 3/30dk, login 10/10dk)
- Şifreler bcrypt ile saklanır
- `uploads/` dizini içinde PHP/script yürütme `.htaccess` ile engellidir
- `config.php`, `schema.sql`, `seed.sql` doğrudan istek ile erişilemez
- Admin paneli aktivite log'u tüm değişiklikleri kayıt altına alır
- XSS koruması (`e()` ile her çıktı escape edilir)
- SQL injection koruması (PDO prepared statements)

## DirectAdmin Notları

- LiteSpeed sunucularda da çalışır (`.htaccess` direktifleri tam uyumludur)
- PHP versiyonunu DirectAdmin'den 8.1+ seçin
- `uploads/` dizini için DirectAdmin'den yazma izni (755) ayarlayın
- mail() çalışmıyorsa SMTP eklentisi gerekir (kod `send_mail()` fonksiyonunu kullanır, gerekirse PHPMailer'a geçilebilir)

## Varsayılan Veriler

Kurulum sonrası şu içerikler önceden yüklenir:

- **4 menü grubu**: Tacos, Bun, Burger, Tatlı
- **11 sekme** (tabs)
- **51 ürün** (orijinal HTML'den çıkarıldı)
- **12 şube** (İstanbul × 6, Antalya × 2, Eskişehir, Ankara, Konya, Çerkezköy)
- **6 iş ilanı**
- **3 örnek kampanya**, **2 slider banner**
- **6 tarihçe maddesi** (1991 → 2025)
- **7 statik sayfa** (kurumsal/hakkimizda/tarihce/uretim/medya/insan-kaynaklari/franchise)

Tüm bu veriler yönetim panelinden düzenlenebilir, silinebilir, eklenebilir.

## Geliştirici Notları

- Tek dosya / minimal dosya PHP mimarisi (MVC framework yok)
- PHP 8 strict_types tüm dosyalarda
- PDO ile prepared statements
- Basit bir generic CRUD class (`admin/_crud.php`) ile DRY admin modülleri
- Tüm CSS sayfa içinde gömülü (asset bundle yok, kolay düzenleme)
- Hiçbir CDN bağımlılığı yok (Font Awesome dışında, opsiyonel)

## Lisans

CODEGA tarafından geliştirilmiştir.

---

**CODEGA** — codega.com.tr
