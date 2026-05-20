-- ============================================================
--  005 — Anasayfa SEO + Sosyal Medya Düzeltmeleri (v1.4.1)
--  Anasayfa (lemondedutacos.com) ile birebir aynı görünüm için
-- ============================================================

-- Yeni SEO ayarları
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('site_meta_title',       'Le Monde Du Tacos – Orginal Fransız Tacos Lezzeti'),
('site_meta_description', 'Le Monde Du Tacos ile Fransa''nın orijinal tacos lezzetini keşfet! Özel peynir sos ve eşsiz soslu tacos menüleri, hızlı paket servis ve online paket fiyatları ile hemen sipariş ver.'),
('site_logo_alt',         'TACOS Logo'),
('social_tiktok',         'https://www.tiktok.com/@lemondedutacos');

-- Mevcut sosyal medya değerlerini gerçek linklerle güncelle
-- (sadece varsayılan/boş '#' olanları güncelle, kullanıcı elle değiştirmişse dokunma)
UPDATE settings SET setting_value = 'https://www.facebook.com/lemondedutacoss/'
  WHERE setting_key = 'social_facebook' AND (setting_value = '#' OR setting_value = '' OR setting_value IS NULL);

UPDATE settings SET setting_value = 'https://www.instagram.com/lemondedutacos__/'
  WHERE setting_key = 'social_instagram' AND (setting_value = 'https://www.instagram.com/lemondedutacos__' OR setting_value = '#' OR setting_value = '' OR setting_value IS NULL);
