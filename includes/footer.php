<footer class="footer">
  <ul class="social-nav model-2" aria-label="Sosyal medya">
    <?php
    // Tüm 4 ikon her zaman görünür. Link yoksa # render edilir (görsel kayıp olmasın).
    $socials = [
      ['facebook',  setting('social_facebook'),  'fa-facebook-f', 'Facebook'],
      ['instagram', setting('social_instagram'), 'fa-instagram',  'Instagram'],
      ['twitter',   setting('social_twitter'),   'fa-x-twitter',  'Twitter/X'],
      ['youtube',   setting('social_youtube'),   'fa-youtube',    'YouTube'],
    ];
    foreach ($socials as [$name, $url, $icon, $label]):
        $href = $url ?: '#';
        $active = $href !== '#';
    ?>
      <li><a class="<?= e($name) ?>" href="<?= e($href) ?>"<?= $active ? ' target="_blank" rel="noopener"' : '' ?> aria-label="<?= e($label) ?>"><i class="fa-brands <?= e($icon) ?>"></i></a></li>
    <?php endforeach; ?>
  </ul>

  <nav class="footer-legal" aria-label="Yasal sayfalar">
    <a href="/kvkk.php">KVKK</a>
    <span>·</span>
    <a href="/cerez-politikasi.php">Çerez Politikası</a>
    <span>·</span>
    <a href="/gizlilik-politikasi.php">Gizlilik Politikası</a>
  </nav>

  <div class="footer-meta">
    <div style="font-weight:bold;font-family:'Georgia',serif">
      <?= e(setting('footer_copyright', 'Copyright © ' . date('Y') . ' Tüm Hakları Saklıdır')) ?>
    </div>
    <div style="font-size:11px;color:var(--muted);margin-top:2px">
      Tasarım &amp; Geliştirme:
      <a href="https://www.codega.com.tr" target="_blank" rel="noopener" style="color:var(--brand);font-weight:700;text-decoration:none">CODEGA</a>
    </div>
  </div>
</footer>

<style>
.footer{flex-wrap:wrap}
.footer-legal{display:flex;align-items:center;gap:8px;font-size:12px;flex-wrap:wrap;justify-content:center}
.footer-legal a{color:var(--muted);text-decoration:none;transition:color .2s}
.footer-legal a:hover{color:var(--brand);text-decoration:underline}
.footer-legal span{color:var(--muted);opacity:.5}
.footer-meta{display:flex;flex-direction:column;align-items:flex-end;text-align:right}
@media(max-width:940px){
  .footer{flex-direction:column;gap:10px;text-align:center}
  .footer-meta{align-items:center;text-align:center}
}
</style>

<script>
const ham=document.getElementById("hamburger"),nv=document.getElementById("nav");
ham?.addEventListener("click",()=>nv.classList.toggle("open"));
document.addEventListener("click",e=>{if(!nv.classList.contains("open"))return;if(!nv.contains(e.target)&&!ham.contains(e.target))nv.classList.remove("open")});
</script>
</body>
</html>
