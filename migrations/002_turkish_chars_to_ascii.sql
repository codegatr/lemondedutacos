-- Migration: 002_turkish_chars_to_ascii.sql
-- Tarih: 2026-04-26
-- Açıklama: DB'deki görsel yollarındaki Türkçe karakterleri ASCII'ye normalize eder.
-- FileZilla #U... escape formatından doğan dosya bulunamama sorunlarını çözer.

UPDATE `menu_promo_cards`
SET image = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(image,
        'ç','c'),'Ç','C'),'ğ','g'),'Ğ','G'),'ı','i'),'İ','I'),
        'ö','o'),'Ö','O'),'ş','s'),'Ş','S'),'ü','u'),'Ü','U'),
    image_mobile = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(image_mobile,
        'ç','c'),'Ç','C'),'ğ','g'),'Ğ','G'),'ı','i'),'İ','I'),
        'ö','o'),'Ö','O'),'ş','s'),'Ş','S'),'ü','u'),'Ü','U')
WHERE image RLIKE '[çÇğĞıİöÖşŞüÜ]' OR image_mobile RLIKE '[çÇğĞıİöÖşŞüÜ]';

UPDATE `slider`
SET image = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(image,
        'ç','c'),'Ç','C'),'ğ','g'),'Ğ','G'),'ı','i'),'İ','I'),
        'ö','o'),'Ö','O'),'ş','s'),'Ş','S'),'ü','u'),'Ü','U'),
    image_mobile = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(image_mobile,
        'ç','c'),'Ç','C'),'ğ','g'),'Ğ','G'),'ı','i'),'İ','I'),
        'ö','o'),'Ö','O'),'ş','s'),'Ş','S'),'ü','u'),'Ü','U')
WHERE image RLIKE '[çÇğĞıİöÖşŞüÜ]' OR image_mobile RLIKE '[çÇğĞıİöÖşŞüÜ]';
