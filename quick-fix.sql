-- LMD Tacos: ACİL HIZLI ÇÖZÜM
-- Diagnostic raporundan kesin tespit:
-- Diskte dosyalar MEVCUT, sadece DB'deki path boş string.
-- Bu SQL'i phpMyAdmin → SQL sekmesinden çalıştır.

-- 1. Schema'yı düzelt (NOT NULL → DEFAULT NULL)
ALTER TABLE `menu_promo_cards` MODIFY COLUMN `image` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `slider`           MODIFY COLUMN `image` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `campaigns`        MODIFY COLUMN `image` VARCHAR(255) DEFAULT NULL;

-- 2. Seçilmiş Lezzetler kartını ONAR (diskte zaten var olan Türkçe karakterli dosyalarla)
UPDATE `menu_promo_cards`
SET image        = '/static/img/yeni/seçilmişlezzetler_1x1.png',
    image_mobile = '/static/img/yeni/seçilmişlezzetler_1x1_5_mobile.png'
WHERE tab_code = 'secilmis';

-- 3. Sonucu kontrol et
SELECT id, title, image, image_mobile FROM menu_promo_cards WHERE tab_code = 'secilmis';
