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
.footer-legal{display:flex;align-items:center;gap:10px;font-size:12px;flex-wrap:wrap}
.footer-legal a{color:var(--muted);text-decoration:none;transition:color .2s}
.footer-legal a:hover{color:var(--brand);text-decoration:underline}
.footer-legal span{color:var(--muted);opacity:.5}
.footer-meta{display:flex !important;flex-direction:column;align-items:flex-end;gap:2px;text-align:right}
</style>

<script>
const ham=document.getElementById("hamburger"),nv=document.getElementById("nav");
ham?.addEventListener("click",()=>nv.classList.toggle("open"));
document.addEventListener("click",e=>{if(!nv.classList.contains("open"))return;if(!nv.contains(e.target)&&!ham.contains(e.target))nv.classList.remove("open")});
</script>

<button class="scroll-top" id="scrollTop" aria-label="Yukarı çık" type="button">
  <i class="fa-solid fa-chevron-up"></i>
</button>
<style>
.scroll-top{position:fixed;bottom:24px;right:24px;width:46px;height:46px;border-radius:50%;background:var(--brand);color:#fff;border:none;cursor:pointer;z-index:1000;display:flex;align-items:center;justify-content:center;font-size:16px;opacity:0;visibility:hidden;transform:translateY(8px);transition:opacity .25s,transform .25s,visibility .25s,background .2s;box-shadow:0 6px 16px rgba(0,0,0,.18)}
.scroll-top.visible{opacity:1;visibility:visible;transform:translateY(0)}
.scroll-top:hover{background:#1a3d0a;transform:translateY(-3px);box-shadow:0 8px 22px rgba(0,0,0,.25)}
@media(max-width:640px){.scroll-top{bottom:18px;right:18px;width:42px;height:42px;font-size:14px}}
</style>
<script>
(function(){
  const btn=document.getElementById('scrollTop');
  if(!btn)return;
  // Anasayfada body overflow:hidden, scroll yok, butonu gösterme
  if(getComputedStyle(document.body).overflow==='hidden'){btn.style.display='none';return;}
  const onScroll=()=>{(window.scrollY||document.documentElement.scrollTop)>320?btn.classList.add('visible'):btn.classList.remove('visible');};
  window.addEventListener('scroll',onScroll,{passive:true});
  btn.addEventListener('click',()=>window.scrollTo({top:0,behavior:'smooth'}));
  onScroll();
})();
</script>
</body>
</html>
