</main>

<!-- FOOTER -->
<footer class="footer">
    <div class="container footer-grid">
        <div class="footer-col">
            <a href="<?= SITE_URL ?>/" class="logo">
                <span class="logo-text">
                    <span class="logo-azra">AZRA</span>
                    <span class="logo-doga">DOĞALGAZ</span>
                </span>
            </a>
            <p class="footer-desc">Konforlu yaşam, güvenli gelecek. İzmir'de Demirdöküm yetkili tesisat firması olarak doğalgaz, kombi, klima ve tesisat hizmetleri sunuyoruz.</p>
            <div class="footer-social">
                <?php
                $sosyal = [
                    'facebook'  => ayar('sosyal_facebook'),
                    'instagram' => ayar('sosyal_instagram'),
                    'youtube'   => ayar('sosyal_youtube'),
                    'twitter'   => ayar('sosyal_x'),
                ];
                foreach ($sosyal as $tip => $link) {
                    if ($link) echo '<a href="' . e($link) . '" target="_blank" rel="noopener" aria-label="' . $tip . '"><i class="fab fa-' . $tip . '"></i></a>';
                }
                ?>
            </div>
        </div>

        <div class="footer-col">
            <h4>Kurumsal</h4>
            <ul>
                <li><a href="<?= SITE_URL ?>/hakkimizda">Hakkımızda</a></li>
                <li><a href="<?= SITE_URL ?>/hizmetler">Hizmetlerimiz</a></li>
                <li><a href="<?= SITE_URL ?>/kampanyalar">Kampanyalar</a></li>
                <li><a href="<?= SITE_URL ?>/blog">Blog</a></li>
                <li><a href="<?= SITE_URL ?>/iletisim">İletişim</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Hizmetlerimiz</h4>
            <ul>
                <?php foreach (db_all("SELECT ad, slug FROM hizmet_kategorileri WHERE aktif=1 ORDER BY sira ASC LIMIT 6") as $k): ?>
                    <li><a href="<?= SITE_URL ?>/kategori/<?= e($k['slug']) ?>"><?= e($k['ad']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="footer-col">
            <h4>İletişim</h4>
            <ul class="footer-contact">
                <li><i class="fas fa-phone-volume"></i> <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_1', FIRMA_TEL_1)) ?>"><?= e(ayar('firma_telefon_1', FIRMA_TEL_1)) ?></a></li>
                <li><i class="fas fa-phone-volume"></i> <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_2', FIRMA_TEL_2)) ?>"><?= e(ayar('firma_telefon_2', FIRMA_TEL_2)) ?></a></li>
                <li><i class="fas fa-envelope"></i> <a href="mailto:<?= e(ayar('firma_eposta', FIRMA_EMAIL)) ?>"><?= e(ayar('firma_eposta', FIRMA_EMAIL)) ?></a></li>
                <li><i class="fas fa-map-marker-alt"></i> <?= e(ayar('firma_adres', FIRMA_ADRES)) ?></li>
                <li><i class="fas fa-clock"></i> <?= e(ayar('firma_calisma_saatleri','Pzt-Cmt 08:00-20:00')) ?></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <span>© <?= date('Y') ?> <?= e(SITE_TITLE) ?> — Tüm hakları saklıdır.</span>
            <span>
                <a href="<?= SITE_URL ?>/kvkk">KVKK</a>
                <a href="<?= SITE_URL ?>/gizlilik">Gizlilik</a>
                <a href="https://codega.com.tr" target="_blank" rel="noopener">CODEGA</a>
            </span>
        </div>
    </div>
</footer>

<!-- WHATSAPP DÜĞMESİ -->
<?php $wp = ayar('whatsapp_numara'); if ($wp): ?>
<a href="https://wa.me/<?= e($wp) ?>?text=Merhaba%20Azra%20Do%C4%9Falgaz%2C%20bilgi%20almak%20istiyorum." class="float-wp" target="_blank" rel="noopener" aria-label="WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>
<?php endif; ?>

<!-- ALT MOBİL BAR (Yunus'un imza navigasyonu) -->
<nav class="mobile-bottom-nav">
    <a href="<?= SITE_URL ?>/" class="mb-item"><i class="fas fa-home"></i><span>Ana Sayfa</span></a>
    <a href="<?= SITE_URL ?>/hizmetler" class="mb-item"><i class="fas fa-tools"></i><span>Hizmetler</span></a>
    <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_1', FIRMA_TEL_1)) ?>" class="mb-item mb-call"><i class="fas fa-phone-volume"></i><span>Hemen Ara</span></a>
    <a href="<?= SITE_URL ?>/kampanyalar" class="mb-item"><i class="fas fa-tags"></i><span>Kampanyalar</span></a>
    <a href="<?= SITE_URL ?>/iletisim" class="mb-item"><i class="fas fa-paper-plane"></i><span>İletişim</span></a>
</nav>

<script>
// Sticky header
(function(){
    const h = document.getElementById('header');
    const onScroll = () => h.classList.toggle('scrolled', window.scrollY > 50);
    window.addEventListener('scroll', onScroll, {passive:true});
    onScroll();
})();
// Mobile menu toggle
(function(){
    const t = document.getElementById('menuToggle');
    const n = document.getElementById('mainNav');
    if (!t || !n) return;
    t.addEventListener('click', () => {
        t.classList.toggle('open');
        n.classList.toggle('open');
    });
    n.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
        t.classList.remove('open'); n.classList.remove('open');
    }));
})();
</script>
</body>
</html>
