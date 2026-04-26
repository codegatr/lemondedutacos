-- Migration: 003_relink_secilmis_lezzetler.sql
-- Tarih: 2026-04-26
-- Açıklama: Diagnostic raporu gösterdi ki:
--   - Diskte mevcut: /static/img/yeni/seçilmişlezzetler_1x1.png (Türkçe karakterli, UTF-8)
--   - DB'de:        '' (boş string)
--
-- Bu kayıt önceki Crud save bug'ı + NOT NULL kolon yüzünden boş kalmıştı.
-- Disk'te dosya MEVCUT olduğu için DB path'ini set ediyoruz.
-- (frontend'de asset() fonksiyonu Türkçe karakterleri otomatik URL-encode eder)

UPDATE `menu_promo_cards`
SET image = '/static/img/yeni/seçilmişlezzetler_1x1.png',
    image_mobile = '/static/img/yeni/seçilmişlezzetler_1x1_5_mobile.png'
WHERE tab_code = 'secilmis'
  AND (image IS NULL OR image = '');
