INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('site_meta_title', 'Le Monde Du Tacos – Orginal Fransız Tacos Lezzeti'),
('site_meta_description', 'Le Monde Du Tacos ile Fransa''nın orijinal tacos lezzetini keşfet! Özel peynir sos ve eşsiz soslu tacos menüleri, hızlı paket servis ve online paket fiyatları ile hemen sipariş ver.'),
('site_logo_alt', 'TACOS Logo'),
('social_tiktok', 'https://www.tiktok.com/@lemondedutacos');
UPDATE settings SET setting_value = 'https://www.facebook.com/lemondedutacoss/' WHERE setting_key = 'social_facebook' AND (setting_value = '#' OR setting_value = '' OR setting_value IS NULL);
UPDATE settings SET setting_value = 'https://www.instagram.com/lemondedutacos__/' WHERE setting_key = 'social_instagram' AND setting_value = 'https://www.instagram.com/lemondedutacos__';
DELETE FROM campaigns WHERE title IN ('Kampanya 1','Kampanya 2','Kampanya 3') AND image LIKE '/static/img/yeni/kampanya%.png';
DELETE FROM campaigns WHERE image LIKE '/static/img/kampanyalar/%';
INSERT INTO campaigns (title, image, image_mobile, sort_order, is_active) VALUES
('2li Efsane Menü',     '/static/img/kampanyalar/2liefsanemenu.png',     '/static/img/kampanyalar/2liefsanemenu_mobil.png',     1, 1),
('Algida Menü',         '/static/img/kampanyalar/algidamenu.png',        '/static/img/kampanyalar/algidamenu_mobil.png',        2, 1),
('Efsane İçecek Menü',  '/static/img/kampanyalar/efsaneicecekmenu.png',  '/static/img/kampanyalar/efsaneicecekmenu_mobil.png',  3, 1),
('Efsane Tatlı Menü',   '/static/img/kampanyalar/efsanetatli.png',       '/static/img/kampanyalar/efsanetatli_mobil.png',       4, 1),
('Extrem Menü',         '/static/img/kampanyalar/extrem.png',            '/static/img/kampanyalar/extrem_mobil.png',            5, 1),
('Kanka Menü',          '/static/img/kampanyalar/kankamenu.png',         '/static/img/kampanyalar/kankamenu_mobil.png',         6, 1);
