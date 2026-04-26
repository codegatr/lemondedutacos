<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$branches = db()->query(
    "SELECT id, title, city, district, address, phone, map_url, map_embed, work_hours, sort_order
     FROM branches WHERE is_active = 1 ORDER BY sort_order, id"
)->fetchAll();

// Şehirlere göre gruplama (filtre için)
$cities = [];
foreach ($branches as $b) {
    $c = trim($b['city'] ?? '');
    if ($c !== '' && !in_array($c, $cities, true)) $cities[] = $c;
}
sort($cities);

$page_title = 'Şubeler';
$page_desc  = 'Le Monde Du Tacos şubelerimiz — size en yakın şubeyi bulun, adres ve iletişim bilgilerine göz atın.';
$page_slug  = 'subeler';
$extra_css  = "
.page-banner{
  position:relative;
  background:linear-gradient(135deg,#3a5f0b 0%,#2a4508 60%,#1a2f06 100%);
  min-height:260px;
  display:flex;
  align-items:center;
  justify-content:center;
  text-align:center;
  color:#fff;
  padding:56px 20px;
  overflow:hidden;
}
.page-banner::before{
  content:'';
  position:absolute;
  inset:0;
  background-image:radial-gradient(circle at 20% 50%,rgba(178,69,69,.18),transparent 50%),radial-gradient(circle at 80% 30%,rgba(255,255,255,.06),transparent 60%);
}
.page-banner::after{
  content:'';
  position:absolute;
  bottom:-40px;
  left:50%;
  transform:translateX(-50%);
  width:120%;
  height:80px;
  background:#fff;
  clip-path:polygon(0 60%,100% 0,100% 100%,0 100%);
}
.page-banner > .pb-inner{ position:relative; z-index:2; max-width:820px; }
.page-banner h1{
  font-family:Georgia,serif;
  font-size:clamp(30px,4.4vw,52px);
  font-weight:700;
  margin-bottom:12px;
  letter-spacing:.5px;
  text-shadow:0 2px 18px rgba(0,0,0,.3);
}
.page-banner p{
  font-size:clamp(14px,1.6vw,17px);
  opacity:.95;
  line-height:1.6;
  max-width:640px;
  margin:0 auto;
}
.page-banner .tag{
  display:inline-block;
  background:#b24545;
  color:#fff;
  padding:7px 16px;
  border-radius:100px;
  font-size:12px;
  font-weight:700;
  letter-spacing:.6px;
  margin-bottom:18px;
  box-shadow:0 4px 14px rgba(178,69,69,.45);
}

/* İstatistik şeridi */
.stats-bar{
  max-width:1180px;
  margin:-44px auto 40px;
  position:relative;
  z-index:5;
  padding:0 20px;
}
.stats-bar-inner{
  background:#fff;
  border-radius:20px;
  padding:28px 24px;
  box-shadow:0 12px 40px rgba(0,0,0,.08);
  display:grid;
  grid-template-columns:repeat(3,1fr);
  gap:8px;
}
@media(max-width:560px){
  .stats-bar-inner{ grid-template-columns:1fr; gap:18px; padding:20px; }
}
.stat-cell{
  text-align:center;
  padding:8px 12px;
  border-right:1px solid #f1f5f9;
}
.stat-cell:last-child{ border-right:none; }
@media(max-width:560px){ .stat-cell{ border-right:none; border-bottom:1px solid #f1f5f9; padding-bottom:18px; } .stat-cell:last-child{border-bottom:none;} }
.stat-num{
  font-family:Georgia,serif;
  font-size:36px;
  font-weight:700;
  color:#3a5f0b;
  line-height:1;
}
.stat-lbl{
  font-size:11px;
  text-transform:uppercase;
  letter-spacing:.7px;
  color:#9ca3af;
  margin-top:6px;
  font-weight:700;
}

/* Şube listesi */
.br-section{
  max-width:1180px;
  margin:0 auto;
  padding:8px 20px 56px;
}
.br-section-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  flex-wrap:wrap;
  gap:14px;
  margin-bottom:24px;
}
.br-section-head h2{
  font-family:Georgia,serif;
  font-size:clamp(22px,2.6vw,30px);
  color:#1f2937;
  font-weight:700;
}
.br-section-head h2 i{ color:#b24545; margin-right:8px; }
.br-search{
  position:relative;
  min-width:260px;
}
.br-search input{
  width:100%;
  padding:10px 14px 10px 40px;
  border:1px solid #d1d5db;
  border-radius:100px;
  font-size:14px;
  font-family:inherit;
  background:#fff;
  transition:border-color .15s,box-shadow .15s;
}
.br-search input:focus{
  outline:none;
  border-color:#3a5f0b;
  box-shadow:0 0 0 3px rgba(58,95,11,.12);
}
.br-search i{
  position:absolute;
  left:14px;
  top:50%;
  transform:translateY(-50%);
  color:#9ca3af;
  font-size:14px;
}

/* Şehir filtreleri */
.city-filter{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
  margin-bottom:28px;
  padding:4px 0;
}
.city-chip{
  padding:7px 14px;
  border-radius:100px;
  background:#f9fafb;
  border:1px solid #e5e7eb;
  font-size:12.5px;
  font-weight:600;
  color:#6b7280;
  cursor:pointer;
  transition:all .15s;
  font-family:inherit;
}
.city-chip:hover{
  border-color:#3a5f0b;
  color:#3a5f0b;
  background:#fff;
}
.city-chip.active{
  background:#3a5f0b;
  border-color:#3a5f0b;
  color:#fff;
  box-shadow:0 4px 12px rgba(58,95,11,.25);
}

/* Şube kartları grid */
.br-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
  gap:22px;
}
.br-card{
  background:#fff;
  border:1px solid #e5e7eb;
  border-radius:18px;
  overflow:hidden;
  display:flex;
  flex-direction:column;
  transition:transform .25s ease,box-shadow .25s ease,border-color .25s ease;
  position:relative;
}
.br-card:hover{
  transform:translateY(-4px);
  box-shadow:0 18px 40px rgba(0,0,0,.10);
  border-color:#b24545;
}
.br-card::before{
  content:'';
  position:absolute;
  top:0; left:0;
  height:4px;
  width:0;
  background:linear-gradient(90deg,#3a5f0b,#b24545);
  transition:width .35s ease;
}
.br-card:hover::before{ width:100%; }

.br-card-img{
  height:140px;
  background:linear-gradient(135deg,#3a5f0b 0%,#2a4508 100%);
  display:flex;
  align-items:center;
  justify-content:center;
  position:relative;
  overflow:hidden;
}
.br-card-img::before{
  content:'';
  position:absolute;
  inset:0;
  background-image:radial-gradient(circle at 30% 70%,rgba(178,69,69,.25),transparent 60%);
}
.br-card-img i{
  font-size:54px;
  color:#fff;
  opacity:.85;
  position:relative;
  z-index:2;
  text-shadow:0 4px 18px rgba(0,0,0,.3);
}
.br-card-city{
  position:absolute;
  top:14px;
  left:14px;
  background:rgba(255,255,255,.95);
  color:#3a5f0b;
  padding:5px 12px;
  border-radius:100px;
  font-size:11px;
  font-weight:800;
  letter-spacing:.5px;
  text-transform:uppercase;
  z-index:3;
  backdrop-filter:blur(4px);
}

.br-card-body{
  padding:22px 20px 18px;
  flex:1;
  display:flex;
  flex-direction:column;
}
.br-card-title{
  font-family:Georgia,serif;
  font-size:18px;
  color:#1f2937;
  margin-bottom:6px;
  font-weight:700;
  line-height:1.3;
}
.br-card-district{
  font-size:12px;
  color:#9ca3af;
  font-weight:600;
  margin-bottom:14px;
  text-transform:uppercase;
  letter-spacing:.5px;
}
.br-detail{
  display:flex;
  align-items:flex-start;
  gap:10px;
  font-size:13px;
  color:#4b5563;
  line-height:1.55;
  margin-bottom:10px;
}
.br-detail i{
  width:14px;
  color:#b24545;
  margin-top:3px;
  font-size:13px;
  flex-shrink:0;
}
.br-detail a{ color:#3a5f0b; font-weight:600; }
.br-detail a:hover{ text-decoration:underline; }
.br-card-foot{
  margin-top:auto;
  padding-top:14px;
  border-top:1px solid #f1f5f9;
  display:flex;
  gap:8px;
}
.br-btn{
  flex:1;
  padding:9px 12px;
  border-radius:8px;
  font-size:12.5px;
  font-weight:700;
  text-transform:uppercase;
  letter-spacing:.4px;
  border:none;
  cursor:pointer;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:6px;
  font-family:inherit;
  transition:transform .12s,box-shadow .15s;
  text-decoration:none;
}
.br-btn-map{
  background:linear-gradient(90deg,#3a5f0b,#16a34a);
  color:#fff;
}
.br-btn-map:hover{ transform:translateY(-2px); box-shadow:0 6px 16px rgba(58,95,11,.3); }
.br-btn-call{
  background:#fff;
  color:#b24545;
  border:1px solid #fecaca;
}
.br-btn-call:hover{ background:#fee2e2; }

/* Boş durumu */
.br-empty{
  text-align:center;
  padding:60px 20px;
  color:#9ca3af;
}
.br-empty i{ font-size:42px; margin-bottom:14px; color:#d1d5db; }
.br-empty h3{ font-size:18px; color:#6b7280; margin-bottom:6px; font-weight:700; }
.br-empty p{ font-size:14px; }

/* CTA: Bize katılın */
.join-cta{
  margin-top:60px;
  background:linear-gradient(135deg,#b24545 0%,#8e3636 100%);
  border-radius:24px;
  padding:48px 40px;
  color:#fff;
  display:grid;
  grid-template-columns:1.4fr 1fr;
  gap:32px;
  align-items:center;
  position:relative;
  overflow:hidden;
}
.join-cta::before{
  content:'';
  position:absolute;
  inset:0;
  background-image:radial-gradient(circle at 90% 20%,rgba(255,255,255,.12),transparent 50%),radial-gradient(circle at 10% 90%,rgba(58,95,11,.25),transparent 60%);
}
@media(max-width:780px){
  .join-cta{ grid-template-columns:1fr; padding:32px 24px; text-align:center; gap:20px; }
}
.join-cta > *{ position:relative; z-index:2; }
.join-cta h2{
  font-family:Georgia,serif;
  font-size:clamp(24px,3vw,34px);
  margin-bottom:12px;
  line-height:1.25;
  font-weight:700;
}
.join-cta p{
  font-size:15px;
  opacity:.95;
  line-height:1.65;
  margin-bottom:20px;
}
.join-cta .badge{
  display:inline-block;
  background:rgba(255,255,255,.2);
  padding:6px 14px;
  border-radius:100px;
  font-size:11px;
  font-weight:700;
  letter-spacing:.6px;
  margin-bottom:14px;
  text-transform:uppercase;
  backdrop-filter:blur(8px);
  border:1px solid rgba(255,255,255,.25);
}
.join-cta-features{
  display:flex;
  flex-wrap:wrap;
  gap:10px 20px;
  margin-bottom:24px;
  font-size:13px;
}
.join-cta-features span{
  display:inline-flex;
  align-items:center;
  gap:6px;
}
.join-cta-features i{ color:#fbbf24; }
.join-cta-actions{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
}
@media(max-width:780px){ .join-cta-actions{ justify-content:center; } }
.join-btn{
  padding:13px 22px;
  border-radius:12px;
  font-size:13px;
  font-weight:700;
  text-transform:uppercase;
  letter-spacing:.5px;
  display:inline-flex;
  align-items:center;
  gap:8px;
  text-decoration:none;
  transition:transform .12s,box-shadow .15s;
  border:none;
  cursor:pointer;
  font-family:inherit;
}
.join-btn-primary{
  background:#fff;
  color:#b24545;
  box-shadow:0 6px 18px rgba(0,0,0,.18);
}
.join-btn-primary:hover{ transform:translateY(-2px); box-shadow:0 10px 26px rgba(0,0,0,.25); }
.join-btn-ghost{
  background:transparent;
  color:#fff;
  border:1.5px solid rgba(255,255,255,.4);
}
.join-btn-ghost:hover{ background:rgba(255,255,255,.12); }

.join-cta-visual{
  position:relative;
  display:flex;
  align-items:center;
  justify-content:center;
}
.join-cta-icon{
  font-size:140px;
  color:rgba(255,255,255,.18);
  text-shadow:0 8px 30px rgba(0,0,0,.2);
}
@media(max-width:780px){
  .join-cta-icon{ font-size:80px; }
}

/* Hover map preview (opsiyonel) */
.br-mappreview{
  position:fixed;
  inset:0;
  background:rgba(15,23,42,.85);
  z-index:9999;
  display:none;
  align-items:center;
  justify-content:center;
  padding:20px;
}
.br-mappreview.show{ display:flex; }
.br-mappreview-box{
  background:#fff;
  border-radius:16px;
  max-width:900px;
  width:100%;
  overflow:hidden;
  box-shadow:0 24px 60px rgba(0,0,0,.5);
}
.br-mappreview-head{
  padding:18px 22px;
  border-bottom:1px solid #e5e7eb;
  display:flex;
  align-items:center;
  justify-content:space-between;
}
.br-mappreview-head h3{
  font-family:Georgia,serif;
  font-size:17px;
  color:#1f2937;
}
.br-mappreview-close{
  width:32px;
  height:32px;
  border-radius:8px;
  background:#fee2e2;
  color:#dc2626;
  border:none;
  cursor:pointer;
  display:flex;
  align-items:center;
  justify-content:center;
}
.br-mappreview-close:hover{ background:#fecaca; }
.br-mappreview-body iframe{
  width:100%;
  height:480px;
  border:none;
  display:block;
}
";
require __DIR__ . '/includes/header.php';
?>

<main role="main">

  <!-- Sayfa banner'ı -->
  <section class="page-banner">
    <div class="pb-inner">
      <span class="tag"><i class="fa-solid fa-location-dot" style="margin-right:6px"></i>ŞUBELERİMİZ</span>
      <h1>Size En Yakın Lezzet Durağı</h1>
      <p>Türkiye'nin dört bir yanında büyüyen Le Monde Du Tacos ailesinin <?= count($branches) ?> şubesinden birini ziyaret edin — özgün French Tacos lezzetimizi tadın.</p>
    </div>
  </section>

  <!-- İstatistik şeridi -->
  <div class="stats-bar">
    <div class="stats-bar-inner">
      <div class="stat-cell">
        <div class="stat-num"><?= count($branches) ?></div>
        <div class="stat-lbl">Aktif Şube</div>
      </div>
      <div class="stat-cell">
        <div class="stat-num"><?= count($cities) ?: 1 ?></div>
        <div class="stat-lbl">Şehir</div>
      </div>
      <div class="stat-cell">
        <div class="stat-num">7/24</div>
        <div class="stat-lbl">Online Sipariş</div>
      </div>
    </div>
  </div>

  <section class="br-section">
    <div class="br-section-head">
      <h2><i class="fa-solid fa-store"></i>Tüm Şubelerimiz</h2>
      <div class="br-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="brSearch" placeholder="Şube veya semt ara…">
      </div>
    </div>

    <?php if (count($cities) > 1): ?>
    <div class="city-filter" id="cityFilter">
      <button class="city-chip active" data-city="">Tümü</button>
      <?php foreach ($cities as $c): ?>
        <button class="city-chip" data-city="<?= e(strtolower($c)) ?>"><?= e($c) ?></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($branches)): ?>
      <div class="br-empty">
        <i class="fa-solid fa-store-slash"></i>
        <h3>Henüz şube yok</h3>
        <p>Yakında bulunduğunuz şehirde de hizmetinizdeyiz.</p>
      </div>
    <?php else: ?>
    <div class="br-grid" id="brGrid">
      <?php foreach ($branches as $b):
        $city     = trim($b['city'] ?? '');
        $district = trim($b['district'] ?? '');
        $address  = trim($b['address'] ?? '');
        $phone    = trim($b['phone'] ?? '');
        $hours    = trim($b['work_hours'] ?? '');
        $mapUrl   = trim($b['map_url'] ?? '');
        $mapEmbed = trim($b['map_embed'] ?? '');
        $phoneTel = $phone ? preg_replace('/[^0-9+]/', '', $phone) : '';
        $searchKey = strtolower($b['title'] . ' ' . $city . ' ' . $district . ' ' . $address);
      ?>
      <article class="br-card" data-search="<?= e($searchKey) ?>" data-city="<?= e(strtolower($city)) ?>">
        <div class="br-card-img">
          <?php if ($city): ?><div class="br-card-city"><?= e($city) ?></div><?php endif; ?>
          <i class="fa-solid fa-utensils"></i>
        </div>
        <div class="br-card-body">
          <h3 class="br-card-title"><?= e($b['title']) ?></h3>
          <?php if ($district): ?>
            <div class="br-card-district"><?= e($district) ?></div>
          <?php endif; ?>

          <?php if ($address): ?>
          <div class="br-detail">
            <i class="fa-solid fa-location-dot"></i>
            <div><?= e($address) ?></div>
          </div>
          <?php endif; ?>

          <?php if ($phone): ?>
          <div class="br-detail">
            <i class="fa-solid fa-phone"></i>
            <div><a href="tel:<?= e($phoneTel) ?>"><?= e($phone) ?></a></div>
          </div>
          <?php endif; ?>

          <?php if ($hours): ?>
          <div class="br-detail">
            <i class="fa-solid fa-clock"></i>
            <div><?= e($hours) ?></div>
          </div>
          <?php endif; ?>

          <div class="br-card-foot">
            <?php if ($mapUrl): ?>
              <a class="br-btn br-btn-map" href="<?= e($mapUrl) ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-map-location-dot"></i> Yol Tarifi
              </a>
            <?php endif; ?>
            <?php if ($phone): ?>
              <a class="br-btn br-btn-call" href="tel:<?= e($phoneTel) ?>">
                <i class="fa-solid fa-phone"></i> Ara
              </a>
            <?php endif; ?>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="br-empty" id="brEmpty" style="display:none">
      <i class="fa-solid fa-circle-exclamation"></i>
      <h3>Sonuç bulunamadı</h3>
      <p>Arama kriterlerine uygun şube yok. Filtreyi değiştirip tekrar deneyin.</p>
    </div>
    <?php endif; ?>

    <!-- Bize Katılın CTA -->
    <div class="join-cta">
      <div>
        <span class="badge"><i class="fa-solid fa-handshake" style="margin-right:6px"></i>FRANCHISE</span>
        <h2>Bir Sonraki Şube Sizin Olabilir mi?</h2>
        <p>
          Türkiye'nin en hızlı büyüyen French Tacos markasına katılın. Güçlü marka desteği,
          kanıtlanmış iş modeli ve kapsamlı eğitim sistemiyle kendi şubenizi açma yolculuğunda yanınızdayız.
        </p>

        <div class="join-cta-features">
          <span><i class="fa-solid fa-check"></i> Lokasyon Analizi</span>
          <span><i class="fa-solid fa-check"></i> Tam Operasyon Desteği</span>
          <span><i class="fa-solid fa-check"></i> Pazarlama Yönetimi</span>
          <span><i class="fa-solid fa-check"></i> Profesyonel Eğitim</span>
        </div>

        <div class="join-cta-actions">
          <a href="/franchise.php" class="join-btn join-btn-primary">
            <i class="fa-solid fa-paper-plane"></i> Hemen Başvuru Yap
          </a>
          <a href="/iletisim.php" class="join-btn join-btn-ghost">
            <i class="fa-solid fa-message"></i> Bilgi Al
          </a>
        </div>
      </div>
      <div class="join-cta-visual">
        <i class="fa-solid fa-store join-cta-icon"></i>
      </div>
    </div>
  </section>
</main>

<!-- Map preview overlay (modal) -->
<div class="br-mappreview" id="brMapModal">
  <div class="br-mappreview-box">
    <div class="br-mappreview-head">
      <h3 id="brMapTitle">Şube Konumu</h3>
      <button class="br-mappreview-close" onclick="document.getElementById('brMapModal').classList.remove('show')">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="br-mappreview-body" id="brMapBody"></div>
  </div>
</div>

<script>
(function(){
  // Arama
  const search = document.getElementById('brSearch');
  const grid   = document.getElementById('brGrid');
  const empty  = document.getElementById('brEmpty');
  const chips  = document.querySelectorAll('.city-chip');
  let activeCity = '';

  function applyFilter(){
    if (!grid) return;
    const q = (search?.value || '').toLowerCase().trim();
    let visible = 0;
    grid.querySelectorAll('.br-card').forEach(card => {
      const matchesSearch = !q || card.dataset.search.includes(q);
      const matchesCity   = !activeCity || card.dataset.city === activeCity;
      const show = matchesSearch && matchesCity;
      card.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    if (empty) empty.style.display = visible === 0 ? 'block' : 'none';
  }

  search?.addEventListener('input', applyFilter);
  chips.forEach(c => c.addEventListener('click', () => {
    chips.forEach(x => x.classList.remove('active'));
    c.classList.add('active');
    activeCity = c.dataset.city;
    applyFilter();
  }));

  // Modal map preview - kart üzerine tıklayınca embed göster (opsiyonel; şu an kapalı, sadece "Yol Tarifi" butonu yeni sekme açıyor)
  // Esc ile modal kapat
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.getElementById('brMapModal')?.classList.remove('show');
    }
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
