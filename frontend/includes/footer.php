
</main><!-- /#main -->

<footer class="site-footer">
  <div class="container site-footer__inner">
    <p class="site-footer__brand"><strong>EduLib</strong> — Bibliothèque de ressources étudiantes EFREI</p>
    <nav aria-label="Liens de pied de page">
      <ul class="site-footer__links">
        <li><a href="/index.php">Accueil</a></li>
        <li><a href="/resources.php">Ressources</a></li>
        <li><a href="/register.php">S'inscrire</a></li>
      </ul>
    </nav>
    <?php
  $page_kb = round(ob_get_length() / 1024, 1);
  $color   = $page_kb < 100 ? 'var(--c-success, green)' : ($page_kb < 200 ? 'orange' : 'red');
?>
<p class="site-footer__eco">
  Site sobre — HTML/CSS pur, 0 tracker &mdash;
  <span style="color:<?= $color ?>; font-weight:600;">
    ⚡ <?= $page_kb ?> Ko
  </span> cette page
    </p>
  </div>
</footer>

<script>
function toggleTheme() {
  const html = document.documentElement;
  const isDark = html.getAttribute('data-theme') === 'dark';
  html.setAttribute('data-theme', isDark ? 'light' : 'dark');
  localStorage.setItem('theme', isDark ? 'light' : 'dark');
  document.getElementById('theme-toggle').textContent = isDark ? '🌙' : '☀️';
}
document.addEventListener('DOMContentLoaded', () => {
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  document.getElementById('theme-toggle').textContent = isDark ? '☀️' : '🌙';
});
</script>

</body>
</html>
