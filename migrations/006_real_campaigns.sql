-- ============================================================
--  006 — Kampanyalar: 6 Gerçek Kampanya (v1.4.2)
--  Eski 3 placeholder'ı (Kampanya 1/2/3) sil, 6 gerçek kampanyayı ekle
--  Anasayfa (lemondedutacos.com/kampanyalar.html) ile birebir aynı sıra
-- ============================================================

-- 1) Eski 3 placeholder'ı temizle (seed'den gelen)
DELETE FROM campaigns
  WHERE title IN ('Kampanya 1','Kampanya 2','Kampanya 3')
    AND image LIKE '/static/img/yeni/kampanya%.png';

-- 2) 6 gerçek kampanyayı ekle (anasayfa sırası)
INSERT INTO campaigns (title, image, image_mobile, sort_order, is_active) VALUES
('2li Efsane Menü',     '/static/img/kampanyalar/2liefsanemenu.png',     '/static/img/kampanyalar/2liefsanemenu_mobil.png',     1, 1),
('Algida Menü',         '/static/img/kampanyalar/algidamenu.png',        '/static/img/kampanyalar/algidamenu_mobil.png',        2, 1),
('Efsane İçecek Menü',  '/static/img/kampanyalar/efsaneicecekmenu.png',  '/static/img/kampanyalar/efsaneicecekmenu_mobil.png',  3, 1),
('Efsane Tatlı Menü',   '/static/img/kampanyalar/efsanetatli.png',       '/static/img/kampanyalar/efsanetatli_mobil.png',       4, 1),
('Extrem Menü',         '/static/img/kampanyalar/extrem.png',            '/static/img/kampanyalar/extrem_mobil.png',            5, 1),
('Kanka Menü',          '/static/img/kampanyalar/kankamenu.png',         '/static/img/kampanyalar/kankamenu_mobil.png',         6, 1);
