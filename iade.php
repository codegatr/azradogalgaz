<?php
require_once __DIR__ . '/config.php';

$sayfa_baslik   = 'İade Politikası — Azra Doğalgaz';
$sayfa_aciklama = 'Ürün iade ve değişim koşulları, cayma hakkı kullanımı.';
$kanonik_url    = SITE_URL . '/iade';

$icerik = ayar('iade_metni', '');

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>İade Politikası</span>
        </div>
        <h1>İade Politikası</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">İade, değişim ve cayma hakkı uygulamalarımız.</p>
    </div>
</section>

<section class="s">
    <div class="container">
        <div class="prose">
            <?php if ($icerik): ?>
                <?= $icerik ?>
            <?php else: ?>
                <p>İade politikamız hazırlanmaktadır. Detaylı bilgi için lütfen bizimle iletişime geçin: <a href="mailto:<?= e(ayar('firma_eposta', defined('FIRMA_EMAIL')?FIRMA_EMAIL:'')) ?>"><?= e(ayar('firma_eposta', defined('FIRMA_EMAIL')?FIRMA_EMAIL:'')) ?></a></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
