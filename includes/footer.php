<footer class="footer">
  <ul class="social-nav model-2" aria-label="Sosyal medya">
    <?php
    // Tüm 4 ikon her zaman görünür. Link yoksa # olarak render edilir (görsel kayıp olmasın).
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
