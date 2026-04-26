<footer class="footer">
  <ul class="social-nav" aria-label="Sosyal medya">
    <?php
    $socials = [
      ['facebook',  setting('social_facebook'),  'fa-facebook-f'],
      ['instagram', setting('social_instagram'), 'fa-instagram'],
      ['twitter',   setting('social_twitter'),   'fa-x-twitter'],
      ['youtube',   setting('social_youtube'),   'fa-youtube'],
    ];
    foreach ($socials as [$name, $url, $icon]):
        if (!$url || $url === '#') continue;
    ?>
      <li><a class="<?= e($name) ?>" href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e($name) ?>"><i class="fa-brands <?= e($icon) ?>"></i></a></li>
    <?php endforeach; ?>
  </ul>
  <div style="font-weight:bold;font-family:'Georgia',serif">
    <?= e(setting('footer_copyright', 'Copyright © ' . date('Y') . ' Tüm Hakları Saklıdır')) ?>
  </div>
</footer>
<script>
const ham=document.getElementById("hamburger"),nv=document.getElementById("nav");
ham?.addEventListener("click",()=>nv.classList.toggle("open"));
document.addEventListener("click",e=>{if(!nv.classList.contains("open"))return;if(!nv.contains(e.target)&&!ham.contains(e.target))nv.classList.remove("open")});
</script>
</body>
</html>
