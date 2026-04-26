-- ============================================================
--  Le Monde Du Tacos – Veritabanı Şeması
--  PHP 8.3 + MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============== AYARLAR ==============
CREATE TABLE IF NOT EXISTS settings (
    setting_key   VARCHAR(64) NOT NULL PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== YÖNETİCİLER ==============
CREATE TABLE IF NOT EXISTS admin_users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(64) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name          VARCHAR(120) NOT NULL,
    email         VARCHAR(190) NULL,
    role          ENUM('superadmin','admin','editor') NOT NULL DEFAULT 'admin',
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    last_login_ip VARCHAR(45) NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== AKTİVİTE LOG ==============
CREATE TABLE IF NOT EXISTS activity_log (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id    INT UNSIGNED NULL,
    action      VARCHAR(100) NOT NULL,
    detail      VARCHAR(500) NULL,
    ip          VARCHAR(45) NULL,
    user_agent  VARCHAR(255) NULL,
    created_at  DATETIME NOT NULL,
    INDEX idx_admin (admin_id),
    INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== HIZ SINIRI ==============
CREATE TABLE IF NOT EXISTS rate_limits (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rl_key     VARCHAR(120) NOT NULL,
    ip         VARCHAR(45) NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_key (rl_key, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== HERO SLIDER ==============
CREATE TABLE IF NOT EXISTS slider (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200) NULL,
    image        VARCHAR(255) NOT NULL,
    image_mobile VARCHAR(255) NULL,
    link_url     VARCHAR(255) NULL,
    sort_order   INT NOT NULL DEFAULT 0,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== MENÜ ANA KATEGORİLERİ (Anasayfadaki 4 buton) ==============
CREATE TABLE IF NOT EXISTS menu_groups (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(40) NOT NULL UNIQUE,           -- tacos, bun, burger, tatli
    title       VARCHAR(120) NOT NULL,                 -- TACOS
    label       VARCHAR(120) NOT NULL,                 -- TACOS / BUN & CROUSTY ...
    icon        VARCHAR(255) NULL,                     -- /static/img/tacos.png
    page_slug   VARCHAR(60) NOT NULL,                  -- tacos-menu, bun-menu, burger-menu, tatli-menu
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== ANA SAYFA POPUP KARTLARI (her gruba 2-3 kart) ==============
CREATE TABLE IF NOT EXISTS menu_promo_cards (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id    INT UNSIGNED NOT NULL,
    title       VARCHAR(120) NOT NULL,                 -- "Seçilmiş Lezzetler"
    image       VARCHAR(255) NOT NULL,
    image_mobile VARCHAR(255) NULL,
    tab_code    VARCHAR(60) NOT NULL,                  -- secilmis, imzali, gurme...
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    INDEX idx_group (group_id, sort_order),
    CONSTRAINT fk_promo_group FOREIGN KEY (group_id) REFERENCES menu_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== MENÜ SEKMELERİ (alt kategoriler) ==============
CREATE TABLE IF NOT EXISTS menu_categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id    INT UNSIGNED NOT NULL,
    code        VARCHAR(60) NOT NULL,                  -- secilmis, imzali, gurme
    title       VARCHAR(120) NOT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY u_group_code (group_id, code),
    INDEX idx_group_sort (group_id, sort_order),
    CONSTRAINT fk_cat_group FOREIGN KEY (group_id) REFERENCES menu_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== MENÜ ÜRÜNLERİ ==============
CREATE TABLE IF NOT EXISTS menu_items (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id  INT UNSIGNED NOT NULL,
    title        VARCHAR(160) NOT NULL,
    description  TEXT NULL,
    price        VARCHAR(40) NULL,
    image        VARCHAR(255) NULL,
    sort_order   INT NOT NULL DEFAULT 0,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cat_sort (category_id, sort_order, is_active),
    CONSTRAINT fk_item_cat FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== ŞUBELER ==============
CREATE TABLE IF NOT EXISTS branches (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(160) NOT NULL,
    city        VARCHAR(80) NULL,
    district    VARCHAR(120) NULL,
    address     TEXT NULL,
    phone       VARCHAR(40) NULL,
    map_url     VARCHAR(500) NULL,
    map_embed   TEXT NULL,
    work_hours  VARCHAR(120) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== KAMPANYALAR ==============
CREATE TABLE IF NOT EXISTS campaigns (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(200) NULL,
    description   TEXT NULL,
    image         VARCHAR(255) NOT NULL,
    image_mobile  VARCHAR(255) NULL,
    link_url      VARCHAR(255) NULL,
    starts_on     DATE NULL,
    ends_on       DATE NULL,
    sort_order    INT NOT NULL DEFAULT 0,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== AÇIK POZİSYONLAR ==============
CREATE TABLE IF NOT EXISTS jobs (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(160) NOT NULL,
    employment   ENUM('fulltime','parttime','intern') NOT NULL DEFAULT 'fulltime',
    location     VARCHAR(160) NULL,
    description  TEXT NULL,
    sort_order   INT NOT NULL DEFAULT 0,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== İŞ BAŞVURULARI ==============
CREATE TABLE IF NOT EXISTS job_applications (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id      INT UNSIGNED NULL,
    full_name   VARCHAR(160) NOT NULL,
    email       VARCHAR(190) NULL,
    phone       VARCHAR(40) NULL,
    city        VARCHAR(80) NULL,
    position    VARCHAR(160) NULL,
    message     TEXT NULL,
    cv_path     VARCHAR(255) NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    ip          VARCHAR(45) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_read (is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== FRANCHISE BAŞVURULARI ==============
CREATE TABLE IF NOT EXISTS franchise_applications (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(160) NOT NULL,
    phone       VARCHAR(40) NOT NULL,
    email       VARCHAR(190) NOT NULL,
    city        VARCHAR(80) NOT NULL,
    age         INT NULL,
    investment  VARCHAR(40) NULL,
    message     TEXT NULL,
    kvkk        TINYINT(1) NOT NULL DEFAULT 0,
    commercial  TINYINT(1) NOT NULL DEFAULT 0,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    ip          VARCHAR(45) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_read (is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== İLETİŞİM MESAJLARI ==============
CREATE TABLE IF NOT EXISTS contact_messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name  VARCHAR(80) NOT NULL,
    last_name   VARCHAR(80) NOT NULL,
    email       VARCHAR(190) NOT NULL,
    phone       VARCHAR(40) NULL,
    branch_id   INT UNSIGNED NULL,
    subject     VARCHAR(200) NOT NULL,
    message     TEXT NOT NULL,
    rating      TINYINT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    ip          VARCHAR(45) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_read (is_read, created_at),
    INDEX idx_branch (branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== STATİK SAYFA İÇERİKLERİ ==============
-- HTML editörü ile yönetilebilen genel sayfalar (kurumsal, hakkımızda, tarihçe, üretim, medya vb.)
CREATE TABLE IF NOT EXISTS pages (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug         VARCHAR(80) NOT NULL UNIQUE,
    title        VARCHAR(200) NOT NULL,
    subtitle     VARCHAR(255) NULL,
    seo_title    VARCHAR(255) NULL,
    seo_desc     VARCHAR(500) NULL,
    hero_image   VARCHAR(255) NULL,
    body         LONGTEXT NULL,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== TARİHÇE ZAMAN ÇİZELGESİ ==============
CREATE TABLE IF NOT EXISTS timeline (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    year_label  VARCHAR(40) NOT NULL,
    title       VARCHAR(200) NOT NULL,
    description TEXT NULL,
    image       VARCHAR(255) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== KURUMSAL KARTLAR (kurumsal sayfası) ==============
CREATE TABLE IF NOT EXISTS corporate_cards (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    description TEXT NULL,
    icon        VARCHAR(64) NULL,                       -- font awesome
    link_url    VARCHAR(255) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== GÜNCELLEME GEÇMİŞİ ==============
CREATE TABLE IF NOT EXISTS update_history (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    from_version VARCHAR(32) NOT NULL,
    to_version   VARCHAR(32) NOT NULL,
    status       ENUM('success','failed') NOT NULL,
    notes        TEXT NULL,
    admin_id     INT UNSIGNED NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
