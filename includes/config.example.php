<?php
declare(strict_types=1);

/**
 * Le Monde Du Tacos – Yapılandırma
 * --------------------------------
 * Veritabanı ve site genelindeki sabitler bu dosyadadır.
 * GitHub güncelleme sistemi de buradan beslenir.
 */

// ============== VERİTABANI ==============
const DB_HOST = 'localhost';
const DB_NAME = 'lemondedutacos';
const DB_USER = 'lemondedutacos';
const DB_PASS = 'CHANGE_ME_DB_PASSWORD';
const DB_CHARSET = 'utf8mb4';

// ============== SİTE ==============
const SITE_URL  = 'https://lemondedutacos.com';
const SITE_NAME = 'Le Monde Du Tacos';
const TIMEZONE  = 'Europe/Istanbul';

// ============== YÖNETİM PANELİ ==============
const ADMIN_PATH = 'admin';                     // /admin
const SESSION_NAME = 'lmdt_admin';
const SESSION_LIFETIME = 7200;                  // 2 saat

// ============== GÜVENLİK ==============
// Çerez ve oturum imzası için rastgele bir değer (kurulumda otomatik üretilir).
const SECRET_KEY = 'CHANGE_ME_SECRET_KEY_RANDOM_STRING';

// ============== YÜKLEMELER ==============
const UPLOAD_DIR    = __DIR__ . '/../uploads';
const UPLOAD_URL    = '/uploads';
const MAX_UPLOAD_MB = 10;
const ALLOWED_IMG   = ['jpg','jpeg','png','webp','gif','svg'];
const ALLOWED_DOC   = ['pdf','doc','docx'];

// ============== E-POSTA (form bildirimleri) ==============
const MAIL_TO   = 'info@lemondedutacos.com';
const MAIL_FROM = 'noreply@lemondedutacos.com';
const MAIL_NAME = 'Le Monde Du Tacos';

// ============== GITHUB GÜNCELLEME ==============
// Sürüm dosyaları: manifest.json (yerel) + GitHub Releases API
const GITHUB_OWNER = 'codegatr';                // GitHub kullanıcı/organizasyon
const GITHUB_REPO  = 'lemondedutacos';          // Depo adı
const GITHUB_TOKEN = '';                        // Özel depo ise PAT (önerilen: boş bırakın, public release kullanın)
const APP_VERSION  = '1.2.6';                   // manifest.json ile senkron
const UPDATE_CHECK_TTL = 3600;                  // 1 saatte bir kontrol

// ============== HATA AYIKLAMA ==============
const DEBUG = false;                            // Production: false

// Saat dilimi
date_default_timezone_set(TIMEZONE);

// Hata raporlama
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
