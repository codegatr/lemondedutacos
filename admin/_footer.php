    </main>
  </div>
</div>
<script>
// Otomatik kaybolan flash mesajları
setTimeout(()=>{document.querySelectorAll(".flash").forEach(f=>{f.style.transition="opacity .4s";f.style.opacity="0";setTimeout(()=>f.remove(),400)})},5000);

// Tehlikeli aksiyonlar için onay
document.querySelectorAll("[data-confirm]").forEach(el=>{
  el.addEventListener("submit",e=>{if(!confirm(el.dataset.confirm))e.preventDefault()});
  el.addEventListener("click",e=>{if(el.tagName==="A"&&!confirm(el.dataset.confirm))e.preventDefault()});
});
</script>
</body>
</html>
