-- Migration: 001_image_nullable.sql
-- Tarih: 2026-04-26
-- Açıklama: Image kolonlarını NULL atanabilir hale getirir.
--
-- KÖK SORUN: image kolonları NOT NULL tanımlanmıştı. Bu yüzden Crud save'de
-- görsel yokken NULL atanmaya çalışıldığında MySQL '' (boş string) atıyordu.
-- Sonra asset_exists('') false döndüğü için frontend'de hiçbir şey görünmüyordu
-- ama if($value) check'i de fail oluyor -> kafa karışıklığı.
--
-- Çözüm: Tüm görsel kolonlarını DEFAULT NULL yap.

ALTER TABLE `menu_promo_cards` MODIFY COLUMN `image` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `slider` MODIFY COLUMN `image` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `campaigns` MODIFY COLUMN `image` VARCHAR(255) DEFAULT NULL;

-- Boş string olanları temiz NULL'a çevir
UPDATE `menu_promo_cards` SET `image` = NULL WHERE `image` = '';
UPDATE `menu_promo_cards` SET `image_mobile` = NULL WHERE `image_mobile` = '';
UPDATE `slider` SET `image` = NULL WHERE `image` = '';
UPDATE `slider` SET `image_mobile` = NULL WHERE `image_mobile` = '';
UPDATE `campaigns` SET `image` = NULL WHERE `image` = '';
UPDATE `campaigns` SET `image_mobile` = NULL WHERE `image_mobile` = '';
UPDATE `menu_groups` SET `icon` = NULL WHERE `icon` = '';
UPDATE `menu_items` SET `image` = NULL WHERE `image` = '';
UPDATE `pages` SET `hero_image` = NULL WHERE `hero_image` = '';
UPDATE `timeline` SET `image` = NULL WHERE `image` = '';
