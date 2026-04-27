<?php
require_once __DIR__ . '/config.php';

set_meta([
    'baslik'    => 'Gizlilik Politikası | ' . SITE_TITLE,
    'aciklama'  => 'Web sitemizin gizlilik politikası, çerez kullanımı ve kişisel veri uygulamaları.',
    'canonical' => SITE_URL . '/gizlilik',
]);

$icerik = ayar('gizlilik_metni', '');

require_once INC_PATH . '/header.php';
?>
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right"></i>
            <span>Gizlilik</span>
        </nav>
        <h1>Gizlilik Politikası</h1>
    </div>
</section>
<section class="sec">
    <div class="container">
        <article class="legal-doc">
            <?php if ($icerik): ?>
                <?= $icerik ?>
            <?php else: ?>
                <h2>Çerez Kullanımı</h2>
                <p>Web sitemiz, kullanıcı deneyimini iyileştirmek amacıyla zorunlu ve istatistik amaçlı çerezler kullanmaktadır. Tarayıcınızdan çerez tercihlerinizi yönetebilirsiniz.</p>

                <h2>Üçüncü Taraf Hizmetler</h2>
                <p>Sitemiz; Google Analytics, Google Maps ve sosyal medya entegrasyonları içerebilir. Bu hizmetler kendi gizlilik politikalarına tabidir.</p>

                <h2>Veri Güvenliği</h2>
                <p>Kişisel verilerinizin güvenliği için SSL şifreleme, sunucu güvenlik duvarı ve düzenli yedekleme önlemleri uygulanmaktadır.</p>

                <h2>İletişim</h2>
                <p>Gizlilik politikamızla ilgili sorularınız için <a href="mailto:<?= e(FIRMA_EMAIL) ?>"><?= e(FIRMA_EMAIL) ?></a> adresine yazabilirsiniz.</p>

                <p style="margin-top:32px;color:var(--c-muted);font-size:.85rem">Bu metin, yönetim panelinden güncellendiğinde otomatik olarak buraya yansıyacaktır.</p>
            <?php endif; ?>
        </article>
    </div>
</section>
<?php require_once INC_PATH . '/footer.php'; ?>
