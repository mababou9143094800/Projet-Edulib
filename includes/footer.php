
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
    <p class="site-footer__eco">
      Site sobre &mdash; HTML/CSS pur, 0 tracker, &lt;&nbsp;200&nbsp;Ko par page
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
