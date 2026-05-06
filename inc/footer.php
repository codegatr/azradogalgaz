<?php
$tel1 = ayar('firma_telefon_1', defined('FIRMA_TEL_1')?FIRMA_TEL_1:'');
$tel2 = ayar('firma_telefon_2', defined('FIRMA_TEL_2')?FIRMA_TEL_2:'');
$wa   = ayar('whatsapp_numara', defined('FIRMA_WHATSAPP')?FIRMA_WHATSAPP:'');
$mail = ayar('firma_eposta', defined('FIRMA_EMAIL')?FIRMA_EMAIL:'');
$adres= ayar('firma_adres', 'İzmir, Türkiye');
$fb   = ayar('sosyal_facebook', '');
$ig   = ayar('sosyal_instagram', '');
$yt   = ayar('sosyal_youtube', '');
$tw   = ayar('sosyal_x', '');
?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <span class="azra">AZRA</span>
                <span class="doga">DOĞALGAZ</span>
                <p>İzmir'in güvenilir doğalgaz tesisat ve ısıtma çözümleri firması. Demirdöküm yetkili, mevzuata uygun, garantili işçilik ve 7/24 teknik destek.</p>
                <div class="footer-social">
                    <?php if ($fb): ?><a href="<?= e($fb) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                    <?php if ($ig): ?><a href="<?= e($ig) ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a><?php endif; ?>
                    <?php if ($yt): ?><a href="<?= e($yt) ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a><?php endif; ?>
                    <?php if ($tw): ?><a href="<?= e($tw) ?>" target="_blank" rel="noopener" aria-label="X"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
                    <a href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Hizmetler</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/kategori/dogalgaz-tesisati">Doğalgaz Tesisatı</a></li>
                    <li><a href="<?= SITE_URL ?>/kategori/kombi-servisi">Kombi Servisi</a></li>
                    <li><a href="<?= SITE_URL ?>/kategori/klima-montaji">Klima Montajı</a></li>
                    <li><a href="<?= SITE_URL ?>/kategori/yerden-isitma">Yerden Isıtma</a></li>
                    <li><a href="<?= SITE_URL ?>/kategori/havalandirma">Havalandırma</a></li>
                    <li><a href="<?= SITE_URL ?>/kategori/sihhi-tesisat">Sıhhi Tesisat</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Kurumsal</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/hakkimizda">Hakkımızda</a></li>
                    <li><a href="<?= SITE_URL ?>/projeler">Projelerimiz</a></li>
                    <li><a href="<?= SITE_URL ?>/kampanyalar">Kampanyalar</a></li>
                    <li><a href="<?= SITE_URL ?>/blog">Blog</a></li>
                    <li><a href="<?= SITE_URL ?>/sss">Sık Sorulan Sorular</a></li>
                    <li><a href="<?= SITE_URL ?>/kesif">Ücretsiz Keşif</a></li>
                    <li><a href="<?= SITE_URL ?>/kvkk">KVKK</a></li>
                    <li><a href="<?= SITE_URL ?>/gizlilik">Gizlilik Politikası</a></li>
                    <li><a href="<?= SITE_URL ?>/cerez">Çerez Politikası</a></li>
                    <li><a href="<?= SITE_URL ?>/mesafeli">Mesafeli Satış Sözleşmesi</a></li>
                    <li><a href="<?= SITE_URL ?>/iade">İade Politikası</a></li>
                </ul>
            </div>

            <div class="footer-col footer-contact">
                <h4>İletişim</h4>
                <div class="item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <strong>Telefon</strong>
                        <a href="tel:<?= e(preg_replace('/\s/','',$tel1)) ?>"><?= e($tel1) ?></a><br>
                        <a href="tel:<?= e(preg_replace('/\s/','',$tel2)) ?>"><?= e($tel2) ?></a>
                    </div>
                </div>
                <div class="item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <strong>E-posta</strong>
                        <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a>
                    </div>
                </div>
                <div class="item">
                    <i class="fab fa-whatsapp"></i>
                    <div>
                        <strong>WhatsApp</strong>
                        <a href="https://wa.me/<?= e($wa) ?>" target="_blank">7/24 Hızlı Destek</a>
                    </div>
                </div>
                <div class="item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <strong>Adres</strong>
                        <?= e($adres) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            © <?= date('Y') ?> Azra Doğalgaz Tesisat — Tüm hakları saklıdır. ·
            Web Tasarım: <a href="https://codega.com.tr" target="_blank" rel="noopener">CODEGA</a>
        </div>
    </div>
</footer>

<a href="https://wa.me/<?= e($wa) ?>" target="_blank" class="fab-whatsapp" aria-label="WhatsApp ile iletişim"><i class="fab fa-whatsapp"></i></a>

<button type="button" class="fab-scrollup" id="fabScrollup" aria-label="Yukarı dön" title="Yukarı dön">
    <i class="fas fa-arrow-up ok" aria-hidden="true"></i>
    <span class="alev" aria-hidden="true"><i class="fas fa-fire"></i></span>
</button>

<nav class="mobile-bar" aria-label="Mobil hızlı erişim">
    <div class="container">
        <div class="mobile-bar-grid">
            <a href="tel:<?= e(preg_replace('/\s/','',$tel1)) ?>" class="call"><i class="fas fa-phone"></i>Ara</a>
            <a href="https://wa.me/<?= e($wa) ?>" class="wa" target="_blank"><i class="fab fa-whatsapp"></i>WhatsApp</a>
            <a href="<?= SITE_URL ?>/kesif"><i class="fas fa-clipboard-check"></i>Keşif</a>
            <a href="<?= SITE_URL ?>/iletisim"><i class="fas fa-envelope"></i>İletişim</a>
        </div>
    </div>
</nav>

<script>
document.addEventListener('scroll', () => {
    const h = document.getElementById('siteHeader');
    if (h) h.classList.toggle('scrolled', window.scrollY > 20);
});
(function(){
    const t = document.getElementById('menuToggle');
    const n = document.getElementById('mainNav');
    if (!t || !n) return;
    t.addEventListener('click', () => n.classList.toggle('open'));
    document.addEventListener('click', e => {
        if (window.innerWidth <= 1100 && !n.contains(e.target) && !t.contains(e.target)) {
            n.classList.remove('open');
        }
    });
})();
/* Scroll-up butonu — 400px altta görünür, smooth scroll yukarı */
(function(){
    const btn = document.getElementById('fabScrollup');
    if (!btn) return;
    const esik = 400;
    let zamanlayici = null;
    const guncelle = () => {
        btn.classList.toggle('show', window.scrollY > esik);
    };
    window.addEventListener('scroll', () => {
        if (zamanlayici) return;
        zamanlayici = setTimeout(() => { guncelle(); zamanlayici = null; }, 60);
    }, {passive:true});
    btn.addEventListener('click', () => {
        window.scrollTo({top:0, behavior:'smooth'});
    });
    guncelle();
})();
</script>

</body>
</html>
