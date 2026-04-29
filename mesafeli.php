<?php
require_once __DIR__ . '/config.php';

$sayfa_baslik   = 'Mesafeli Satış Sözleşmesi — Azra Doğalgaz';
$sayfa_aciklama = 'Online ürün satışları için mesafeli satış sözleşmesi.';
$kanonik_url    = SITE_URL . '/mesafeli';

$icerik = ayar('mesafeli_metni', '');

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>Mesafeli Satış Sözleşmesi</span>
        </div>
        <h1>Mesafeli Satış Sözleşmesi</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">6502 sayılı Tüketicinin Korunması Hakkında Kanun çerçevesinde online satış koşulları.</p>
    </div>
</section>

<section class="s">
    <div class="container">
        <div class="prose">
            <?php if ($icerik): ?>
                <?= $icerik ?>
            <?php else: ?>
                <p>Mesafeli Satış Sözleşmemiz hazırlanmaktadır. Detaylı bilgi için lütfen bizimle iletişime geçin: <a href="mailto:<?= e(ayar('firma_eposta', defined('FIRMA_EMAIL')?FIRMA_EMAIL:'')) ?>"><?= e(ayar('firma_eposta', defined('FIRMA_EMAIL')?FIRMA_EMAIL:'')) ?></a></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
