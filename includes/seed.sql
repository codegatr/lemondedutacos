-- ==================== TOHUM VERİ ====================

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('site_name', 'Le Monde Du Tacos'),
('site_tagline', 'Le Goût Authentique du French Tacos'),
('site_logo', '/static/img/logos/LMD LOGOArtboard1.png'),
('contact_email', 'info@lemondedutacos.com'),
('contact_phone', '+90 850 000 00 00'),
('contact_address', 'İstanbul / Türkiye'),
('social_facebook', '#'),
('social_instagram', 'https://www.instagram.com/lemondedutacos__'),
('social_twitter', '#'),
('social_youtube', '#'),
('footer_copyright', 'Copyright © 2026 Tüm Hakları Saklıdır'),
('kvkk_text', 'KVKK ve Aydınlatma Metnini okudum, onaylıyorum.'),
('commercial_text', 'Ticari elektronik ileti almayı kabul ediyorum.'),
('mail_to', 'info@lemondedutacos.com');

INSERT IGNORE INTO admin_users (id, username, password_hash, name, email, role, is_active) VALUES
(1, 'admin', '$2y$10$8Uy5sHGKoBkkKWlQEllnQOCJOfVVnLdjWd9unMwIlXBWbbYhcBYsK', 'Süper Yönetici', 'admin@lemondedutacos.com', 'superadmin', 1);

INSERT IGNORE INTO menu_groups (id, code, title, label, icon, page_slug, sort_order, is_active) VALUES
(1, 'tacos', 'TACOS', 'TACOS', '/static/img/tacos.png', 'tacos-menu', 1, 1),
(2, 'bun', 'BUN & CROUSTY', 'BUN & CROUSTY', '/static/img/bun.png', 'bun-menu', 2, 1),
(3, 'burger', 'BURGER & ÇOCUK', 'BURGER & ÇOCUK MENÜ', '/static/img/crousty.png', 'burger-menu', 3, 1),
(4, 'tatli', 'TATLI & YAN', 'TATLI & YAN ÜRÜNLER', '/static/img/tatli1.png', 'tatli-menu', 4, 1);

INSERT IGNORE INTO menu_promo_cards (group_id, title, image, image_mobile, tab_code, sort_order, is_active) VALUES
(1, 'Seçilmiş Lezzetler', '/static/img/yeni/seçilmişlezzetler_1x1.png', '/static/img/yeni/seçilmişlezzetler_1x1_5_mobile.png', 'secilmis', 1, 1),
(1, 'İmzalı Lezzetler', '/static/img/yeni/imzalilezzetler1x1.png', '/static/img/yeni/imzalilezzetler1x1_5_mobile.png', 'imzali', 2, 1),
(1, 'Gurme Lezzetler', '/static/img/yeni/gurmelezzetler1x1.png', '/static/img/yeni/gurmelezzetler_1x1_5_mobile.png', 'gurme', 3, 1),
(2, 'ET BUN', '/static/img/yeni/etbun1x1.png', '/static/img/yeni/etbun1x1_5_mobile.png', 'et-bun', 1, 1),
(2, 'TAVUK BUN', '/static/img/yeni/tavukbun1x1.png', '/static/img/yeni/tavukbun1x1_5_mobile.png', 'tavuk-bun', 2, 1),
(2, 'TENDERS CROUSTY', '/static/img/yeni/tender1x1.png', '/static/img/yeni/tender1x1_5_mobile.png', 'tenders-crousty', 3, 1),
(3, 'GURME BURGER', '/static/img/yeni/gurmeburger1x1.png', '/static/img/yeni/gurmeburger1x1_5_mobile.png', 'gurme-burger', 1, 1),
(3, 'ÇOCUK MENÜLER', '/static/img/yeni/cocukmenu1x1.png', '/static/img/yeni/cocukmenu1x1_5_mobile.png', 'cocuk-menuler', 2, 1),
(4, 'CHURROS', '/static/img/yeni/churros1X1.png', '/static/img/yeni/churros1X1_5_mobile.png', 'tatlilar', 1, 1),
(4, 'YAN ÜRÜNLER', '/static/img/yeni/yanurunler1x1.png', NULL, 'yan-urunler', 2, 1);

INSERT IGNORE INTO menu_categories (group_id, code, title, sort_order, is_active) VALUES
(1, 'secilmis', 'Seçilmiş Lezzetler', 1, 1),
(1, 'imzali', 'İmzalı Lezzetler', 2, 1),
(1, 'gurme', 'Gurme Lezzetler', 3, 1),
(2, 'et-bun', 'Et Bun', 1, 1),
(2, 'tavuk-bun', 'Tavuk Bun', 2, 1),
(2, 'tenders-crousty', 'Tenders Crousty', 3, 1),
(3, 'gurme-burger', 'Gurme Burger', 1, 1),
(3, 'cocuk-menuler', 'Çocuk Menüler', 2, 1),
(4, 'yan-urunler', 'Yan Ürünler', 1, 1),
(4, 'soslar', 'Soslar', 2, 1),
(4, 'tatlilar', 'Tatlılar & Dondurmalar', 3, 1),
(4, 'icecekler', 'İçecekler', 4, 1);

INSERT IGNORE INTO menu_items (category_id, title, description, price, image, sort_order, is_active) VALUES
(1, 'Teriyaki', 'M french tacos, teriyaki tavuk, algerienne sos, andalouse sos, özel peynir sos ve patates.', NULL, '/static/img/menuler/teriyaki1x11.png', 0, 1),
(1, 'Fajita', 'French Tacos, Fajita Soslu Tavuk, Sweet Chilli Sos, Özel Peynir Sos ve patates.', NULL, '/static/img/menuler/fajita1x15.png', 1, 1),
(1, 'Le Tenders', 'M french tacos, çıtır tenders, samourai sos, mayonez, özel peynir sos ve patates.', NULL, '/static/img/menuler/Tenders1x1.png', 2, 1),
(1, 'Kekikli', 'Kekikli tavuk, Andalouse mayonez, patates kızartması.', NULL, '/static/img/menuler/Kekikli1x1.png', 3, 1),
(1, 'Buffalo', 'Buffalo tavuk, Algerienne, Andalouse sos, özel peynir sos ve patates.', NULL, '/static/img/menuler/buffalo1x1.png', 4, 1),
(2, 'Mexicain', 'Buffalo soslu tavuk, algerienne sos, andalouse sos, yeşillik, mor lahana ve özel peynir sos.', NULL, '/static/img/menuler/Mexicain1x1.png', 0, 1),
(2, 'Strasbourg', 'Çıtır tavuk tenders, algerienne sos, yeşillik, mor lahana, mısır, özel peynir sos.', NULL, '/static/img/menuler/strasbourg1x1.png', 1, 1),
(2, 'Monaco', 'Teriyaki soslu tavuk, Andalouse sos, karamelize soğan, yeşillik, mor lahana ve özel peynir sos.', NULL, '/static/img/menuler/monaco1x1.png', 2, 1),
(3, 'Parisien', 'Sebzeli özel marineli kıyılmış et döner, algerienne sos, andalouse sos, özel peynir sos.', NULL, '/static/img/menuler/Parisien1x1.png', 0, 1),
(3, 'Marsellais', 'Et döner, algerienne sos, samourai sos, közlenmiş kapya biber ve özel peynir sos.', NULL, '/static/img/menuler/Marsellais1x1.png', 1, 1),
(3, 'Lyonnais', 'Sebzeli özel marineli kıyılmış et döner, algerienne sos, andalouse sos, yeşillik ve özel peynir sos.', NULL, '/static/img/menuler/Lyonnais1x1.png', 2, 1),
(3, 'Le Turque', 'Sebzeli özel marineli kıyılmış et döner, göbek marul, andalouse sos ve özel peynir sos.', NULL, '/static/img/menuler/Turque1x1.png', 3, 1),
(4, 'Et Bun (Adet)', 'Et bun', NULL, '/static/img/menuler/etlibun.png', 0, 1),
(4, 'Etli Kekikli Bun (Adet)', 'Etli kekikli bun', NULL, '/static/img/menuler/etlikeklibun.png', 1, 1),
(5, 'Tavuk Bun', 'Tavuk Bun', NULL, '/static/img/menuler/tavukbun.png', 0, 1),
(5, 'Kekikli Tavuk Bun', 'Kekikli Tavuk Bun', NULL, '/static/img/menuler/kekiklitavukbun.png', 1, 1),
(6, 'Tenders Crousty', 'Tenders Crousty', NULL, '/static/img/menuler/chursty.png', 0, 1),
(7, 'Gurme Burger', 'Tavuk burger, iceberg marul, cheddar peyniri, özel sos. 100 gr. patates...', NULL, '/static/img/menuler/hamburger.png', 0, 1),
(8, 'Le Kids Kekikli', 'Çıtır Tavuk Bonfile + Ketçap + Mayonez + Patates Kızartması', NULL, '/static/img/menuler/lekidsKekikli.png', 0, 1),
(8, 'Le Junior Menü', 'Gurme Et Döner + Ketçap + Mayonez + Patates Kızartması', NULL, '/static/img/menuler/lejunior.png', 1, 1),
(9, 'Cheddar Soslu Patates Kızartması', 'Ketçap, mayonez ile', NULL, '/static/img/menuler/chedarpatates.png', 0, 1),
(9, 'Çıtır Tavuk Topları (15 Adet)', '15 adet olarak servis edilir.', NULL, '/static/img/menuler/citirtop.png', 1, 1),
(9, 'Nugget (6 Adet)', '6 adet', NULL, '/static/img/menuler/nugget.png', 2, 1),
(9, 'Patates Kızartması', 'Ketçap, mayonez ile', NULL, '/static/img/menuler/patates.png', 3, 1),
(9, 'Patates Kroket (6 Adet)', '6 adet', NULL, '/static/img/menuler/patateskroket.png', 4, 1),
(9, 'Soğan Halkası (8 Adet)', '8 adet', NULL, '/static/img/menuler/soganhalkasi.png', 5, 1),
(9, 'Karışık Çıtır Tabağı', '6 adet çıtır top, 4 adet soğan halkası, 2 adet nugget, 2 adet patates kroket,...', NULL, '/static/img/menuler/karisiktabak.png', 6, 1),
(10, 'Algerienne Sos', 'Paket olarak servis edilmektedir.', NULL, '/static/img/menuler/Algeriennesos.png', 0, 1),
(10, 'Andalouse Sos', 'Paket olarak servis edilmektedir.', NULL, '/static/img/menuler/Andaloussos.png', 1, 1),
(10, 'Colorado Acı Sos', 'Paket olarak servis edilmektedir.', NULL, '/static/img/menuler/colaradosos.png', 2, 1),
(10, 'Colorado Barbekü Sos', 'Paket olarak servis edilmektedir.', NULL, '/static/img/menuler/colaradobarbeku.png', 3, 1),
(10, 'Colorado Ranch Sos', 'Paket olarak servis edilmektedir.', NULL, '/static/img/menuler/rancsos.png', 4, 1),
(10, 'Samourai Sos', 'Paket olarak servis edilmektedir.', NULL, '/static/img/menuler/samurai.png', 5, 1),
(10, 'Jalepon Biber Turşusu', 'Paket olarak servis edilmektedir.', NULL, '/static/img/menuler/jaleponbibertursusu.png', 6, 1),
(10, 'Ketçap&Mayonez', 'Paket olarak servis edilmektedir.', NULL, '/static/img/menuler/ketcapmayonez.png', 7, 1),
(11, 'Churros (3 Adet)', 'Çikolata sos ile', NULL, '/static/img/menuler/churros.png', 0, 1),
(11, 'Churros (6 Adet)', '1 çikolata sos, 1 süsleme seçimi', NULL, '/static/img/menuler/churros.png', 1, 1),
(11, 'Algida Maraş Usulü Cup (100 ml.)', 'Paket', '75 TL', '/static/img/menuler/algida.webp', 2, 1),
(11, 'Carte d\'Or Çikolata Karnavalı (100 ml.)', 'Paket dondurma', '75 TL', '/static/img/menuler/cardorecikolata.webp', 3, 1),
(11, 'Carte d\'Or Meyve Rüyası (100 ml.)', 'Paket', '75 TL', '/static/img/menuler/cardormeyve.jpg', 4, 1),
(12, 'Pepsi (33 cl.)', 'Gazlı içecek', NULL, '/static/img/menuler/pepsi33.png', 0, 1),
(12, 'Pepsi Max (33 cl.)', 'Gazlı içecek', NULL, '/static/img/menuler/pepsimax33.jpg', 1, 1),
(12, 'Yedigün (33 cl.)', 'Gazlı içecek', NULL, '/static/img/menuler/yedigun.jpg', 2, 1),
(12, '7UP (33 cl.)', 'İçecek', NULL, '/static/img/menuler/7up.webp', 3, 1),
(12, 'Lipton Ice Tea (33 cl.)', 'Kutu içecek', NULL, '/static/img/menuler/liptonicetea.jpg', 4, 1),
(12, 'Tropicana (33 cl.)', 'Kutu içecek', NULL, '/static/img/menuler/tropicano.jpg', 5, 1),
(12, 'Susurluk Ayran (30 cl.)', 'Büyük boy', NULL, '/static/img/menuler/susurlukayrani.png', 6, 1),
(12, 'Soda (20 cl.)', 'Cam şişe', NULL, '/static/img/menuler/soda.png', 7, 1),
(12, 'Su (50 cl.)', 'Pet şişe', NULL, '/static/img/menuler/su.webp', 8, 1),
(12, 'Pepsi (1 L.)', 'Pet şişe', NULL, '/static/img/menuler/pepsi1lt.jpg', 9, 1),
(12, 'Pepsi Max (1 L.)', 'Pet şişe', NULL, '/static/img/menuler/pepsimax1lt.jpg', 10, 1);

INSERT IGNORE INTO branches (title, city, district, address, map_url, sort_order, is_active) VALUES
('İstanbul – Yenibosna', 'İstanbul', 'Yenibosna', 'No:11BA, Yenibosna Merkez, Değirmenbahçe Cd. Airporthill Sitesi, 34197 Bahçelievler / İstanbul', 'https://www.google.com/maps?q=No:11BA,+Yenibosna+Merkez,+Değirmenbahçe+Cd.+Airporthill+Sitesi,+34197+Bahçelievler+İstanbul', 0, 1),
('İstanbul – Bahçelievler', 'İstanbul', 'Bahçelievler', 'Bahçelievler, Adnan Kahveci Blv., No:101/B, 34180 Bahçelievler / İstanbul', 'https://www.google.com/maps?q=Bahçelievler,+Adnan+Kahveci+Blv.+No:101/B,+34180+Bahçelievler+İstanbul', 1, 1),
('İstanbul – Halkalı', 'İstanbul', 'Halkalı', 'Halkalı / İstanbul', 'https://www.google.com/maps?q=Halkalı+İstanbul', 2, 1),
('İstanbul – Sefaköy', 'İstanbul', 'Sefaköy', 'Sefaköy / İstanbul', 'https://www.google.com/maps?q=Sefaköy+İstanbul', 3, 1),
('İstanbul – Esenler', 'İstanbul', 'Esenler', 'Esenler / İstanbul', 'https://www.google.com/maps?q=Esenler+İstanbul', 4, 1),
('İstanbul – Bahçeşehir', 'İstanbul', 'Bahçeşehir', 'Bahçeşehir / İstanbul', 'https://www.google.com/maps?q=Bahçeşehir+İstanbul', 5, 1),
('Tekirdağ – Çerkezköy', 'Tekirdağ', 'Çerkezköy', 'Çerkezköy / Tekirdağ', 'https://www.google.com/maps?q=Çerkezköy+Tekirdağ', 6, 1),
('Eskişehir – Tepebaşı', 'Eskişehir', 'Tepebaşı', 'Hoşnudiye, Cengiz Topel Cd. No:61B, 26130 Tepebaşı / Eskişehir', 'https://www.google.com/maps?q=Hoşnudiye,+Cengiz+Topel+Cd.+No:61B,+26130+Tepebaşı+Eskişehir', 7, 1),
('Antalya – Lara', 'Antalya', 'Lara', 'Lara / Antalya', 'https://www.google.com/maps?q=Lara+Antalya', 8, 1),
('Antalya – Muratpaşa', 'Antalya', 'Muratpaşa', 'Muratpaşa / Antalya', 'https://www.google.com/maps?q=Muratpaşa+Antalya', 9, 1),
('Konya – Selçuklu', 'Konya', 'Selçuklu', 'Selçuklu / Konya', 'https://www.google.com/maps?q=Selçuklu+Konya', 10, 1),
('Ankara – Kızılay', 'Ankara', 'Kızılay', 'Kızılay / Ankara', 'https://www.google.com/maps?q=Kızılay+Ankara', 11, 1);

INSERT IGNORE INTO jobs (title, employment, description, sort_order, is_active) VALUES
('Şube Müdürü', 'fulltime', 'Şube operasyonlarını yönetmek, ekip liderliği yapmak, satış hedeflerini gerçekleştirmek. 2+ yıl F&B yönetim deneyimi aranıyor.', 0, 1),
('Kasiyer / Müşteri Temsilcisi', 'fulltime', 'Sipariş alma, ödeme işlemleri ve müşteri memnuniyetini ön planda tutarak pozitif satış deneyimi sunmak.', 1, 1),
('Mutfak Ekip Üyesi', 'fulltime', 'Tacos, bun ve diğer ürünlerin standart reçeteler dahilinde hazırlanması. Hijyen kurallarına dikkat eden adaylar tercih edilir.', 2, 1),
('Kurye / Paketçi', 'parttime', 'Online siparişlerin zamanında ve düzgün teslimatı. Ehliyet sahibi (B veya A2 sınıfı) adaylar tercih edilir.', 3, 1),
('Sosyal Medya Uzmanı', 'fulltime', 'Marka iletişimi, içerik üretimi, kampanya yönetimi ve sosyal medya stratejisi. Yaratıcı ve trendleri takip eden adaylar.', 4, 1),
('Stajyer (Çeşitli Departmanlar)', 'parttime', 'Üniversite öğrencileri için operasyon, pazarlama ve insan kaynakları departmanlarında staj fırsatları.', 5, 1);

INSERT IGNORE INTO slider (title, image, image_mobile, sort_order, is_active) VALUES
('Hoş Geldiniz', '/static/img/yeni/slideranasayfa.png', '/static/img/yeni/banner-mobile.jpg', 1, 1),
('Banner 2', '/static/img/yeni/banner2.png', '/static/img/yeni/banner2-mobile.png', 2, 1);

INSERT IGNORE INTO campaigns (title, image, image_mobile, sort_order, is_active) VALUES
('Kampanya 1', '/static/img/yeni/kampanya1.png', '/static/img/yeni/kampanya1-mobile.png', 1, 1),
('Kampanya 2', '/static/img/yeni/kampanya2.png', '/static/img/yeni/kampanya2-mobile.png', 2, 1),
('Kampanya 3', '/static/img/yeni/kampanya3.png', '/static/img/yeni/kampanya3-mobile.png', 3, 1);

INSERT IGNORE INTO pages (slug, title, subtitle, body, is_active) VALUES
('kurumsal', 'LMD Tacos Kurumsal', NULL, NULL, 1),
('hakkimizda', 'HAKKIMIZDA', 'Le Monde Du Tacos – Hakkımızda', '<p>Türk damak tadıyla Fransız sokak lezzetini buluşturuyoruz.</p>', 1),
('tarihce', 'Tarihçemiz', NULL, NULL, 1),
('uretim', 'Üretim ve Lojistik', 'Özgün Lezzet, Güvenilir Zincir', NULL, 1),
('medya', 'Medya', NULL, NULL, 1),
('insan-kaynaklari', 'Le Monde\'da Kariyer Yap', 'French tacos tutkusunu paylaşan, enerjik bir ekibin parçası ol.', NULL, 1),
('franchise', 'TACOS GIDA Ailesine Katılmak İster misiniz?', NULL, NULL, 1);

INSERT IGNORE INTO timeline (year_label, title, description, sort_order, is_active) VALUES
('1991', 'French Tacos\'un Doğuşu', 'Fransa Lyon\'da geleneksel sokak yemeği olarak doğdu.', 0, 1),
('2018', 'Türkiye\'ye Getirme Kararı', 'Ekibimiz French Tacos kültürünü Türkiye\'ye taşıma kararı aldı.', 1, 1),
('2019', 'Yenibosna\'da Açılış', 'İlk şubemizi İstanbul Yenibosna\'da hizmete açtık.', 2, 1),
('2021', '4 Şubeye Ulaşıldı', 'İstanbul\'da hızlı büyüme ile 4 şubeye ulaştık.', 3, 1),
('2023', 'Anadolu\'ya Açılım', 'Eskişehir, Antalya ve Ankara şubelerimizi açtık.', 4, 1),
('2025', 'Türkiye\'nin Her Köşesine', '12+ şube ile yurt çapında hizmet veriyoruz.', 5, 1);

INSERT IGNORE INTO corporate_cards (title, description, icon, link_url, sort_order, is_active) VALUES
('Tarihçe', 'Markamızın 1991\'den bugüne uzanan yolculuğu.', 'fa-clock-rotate-left', 'tarihce.php', 0, 1),
('Medya', 'Basında biz, görseller ve güncel haberler.', 'fa-newspaper', 'medya.php', 1, 1),
('Üretim', 'Özgün lezzet ve güvenilir tedarik zinciri.', 'fa-industry', 'uretim.php', 2, 1),
('İnsan Kaynakları', 'Açık pozisyonlar ve aramıza katılma fırsatları.', 'fa-users', 'insan-kaynaklari.php', 3, 1);
