-- LMD Tacos: Bozuk image path'lerini onar
-- Bu SQL'i phpMyAdmin → SQL sekmesinden çalıştırın
-- Veritabanı: lemonded_v2yeni

-- 1. Boş string olan image alanlarını NULL'a çek
UPDATE menu_promo_cards SET image = NULL WHERE image = '';
UPDATE menu_promo_cards SET image_mobile = NULL WHERE image_mobile = '';
UPDATE slider SET image = NULL WHERE image = '';
UPDATE slider SET image_mobile = NULL WHERE image_mobile = '';
UPDATE menu_groups SET icon = NULL WHERE icon = '';
UPDATE branches SET image = NULL WHERE image = '';
UPDATE campaigns SET image = NULL WHERE image = '';
UPDATE campaigns SET image_mobile = NULL WHERE image_mobile = '';
UPDATE menu_items SET image = NULL WHERE image = '';
UPDATE jobs SET image = NULL WHERE image = '';
UPDATE pages SET hero_image = NULL WHERE hero_image = '';
UPDATE timeline SET image = NULL WHERE image = '';

-- 2. Türkçe karakter içeren path'leri ASCII'ye çevir
UPDATE menu_promo_cards SET
  image = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(image,
    'ç','c'),'ğ','g'),'ı','i'),'ö','o'),'ş','s'),'ü','u'),
  image_mobile = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(image_mobile,
    'ç','c'),'ğ','g'),'ı','i'),'ö','o'),'ş','s'),'ü','u')
WHERE image LIKE '%ç%' OR image LIKE '%ğ%' OR image LIKE '%ı%'
   OR image LIKE '%ö%' OR image LIKE '%ş%' OR image LIKE '%ü%'
   OR image_mobile LIKE '%ç%' OR image_mobile LIKE '%ğ%' OR image_mobile LIKE '%ı%'
   OR image_mobile LIKE '%ö%' OR image_mobile LIKE '%ş%' OR image_mobile LIKE '%ü%';

-- 3. Tamamlandı, kontrol et:
SELECT id, title, image, image_mobile FROM menu_promo_cards ORDER BY id;
