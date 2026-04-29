<?php
require_once __DIR__ . '/config.php';

$kategoriler = db_all("SELECT * FROM hizmet_kategorileri WHERE aktif=1 ORDER BY sira ASC, id ASC");

$sayfa_baslik   = 'Hizmetlerimiz — Doğalgaz, Kombi, Klima, Tesisat | Azra Doğalgaz İzmir';
$sayfa_aciklama = 'İzmir\'de doğalgaz tesisatı, kombi montajı, klima montajı, yerden ısıtma, sıhhi tesisat ve mekanik tesisat hizmetleri. Mevzuata uygun, garantili kurulum.';
$kanonik_url    = SITE_URL . '/hizmetler';

$schema_jsonld = [
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Hizmetler','item'=>SITE_URL.'/hizmetler'],
    ],
];

require_once __DIR__ . '/inc/header.php';

$ikon_map = [
    'dogalgaz-tesisati' => 'fa-fire-flame-curved',
    'kombi-servisi'     => 'fa-screwdriver-wrench',
    'klima-montaji'     => 'fa-snowflake',
    'tesisat-hizmetleri'=> 'fa-toolbox',
    'yerden-isitma'     => 'fa-temperature-arrow-up',
    'havalandirma'      => 'fa-wind',
    'sihhi-tesisat'     => 'fa-faucet',
    'yangin-tesisati'   => 'fa-fire-extinguisher',
    'isi-pompasi'       => 'fa-arrows-rotate',
];
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span>Hizmetlerimiz</span>
        </div>
        <h1>Hizmetlerimiz</h1>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)">İzmir'de doğalgaz, kombi, klima, yerden ısıtma ve mekanik tesisat alanlarında uçtan uca profesyonel hizmet.</p>
    </div>
</section>

<section class="s">
    <div class="container">
        <?php if ($kategoriler): ?>
        <div class="services">
            <?php foreach ($kategoriler as $k):
                $ikon = $ikon_map[$k['slug']] ?? 'fa-tools';
            ?>
            <a href="<?= SITE_URL ?>/kategori/<?= e($k['slug']) ?>" class="service-card" style="text-decoration:none;color:inherit">
                <div class="service-image"><i class="fas <?= e($ikon) ?>"></i></div>
                <div class="service-body">
                    <h3><?= e($k['ad']) ?></h3>
                    <p><?= e($k['aciklama'] ?: 'Profesyonel ' . mb_strtolower($k['ad'], 'UTF-8') . ' hizmetleri için doğru adres.') ?></p>
                    <span class="service-link">Detayları Gör <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info" style="max-width:680px;margin:0 auto">
            <i class="fas fa-circle-info"></i>
            <div>Hizmet kategorileri yakında eklenecek. Bu süreçte iletişim sayfamızdan bize ulaşabilirsiniz.</div>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="s" style="background:var(--c-bg-alt)">
    <div class="container">
        <div class="s-head">
            <span class="s-tag">Akıllı Araçlar</span>
            <h2>Hesaplama Araçlarımız</h2>
            <p>Kombi ve klima ihtiyacınızı saniyeler içinde hesaplayın.</p>
        </div>
        <div class="services" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr))">
            <a href="<?= SITE_URL ?>/kombi-hesaplama" class="service-card" style="text-decoration:none;color:inherit">
                <div class="service-image" style="background:var(--c-primary-l)"><i class="fas fa-calculator"></i></div>
                <div class="service-body"><h3>Kombi Kapasite Hesaplama</h3><p>m², izolasyon ve bölgenize göre kaç kW kombi alacağınızı bulun.</p><span class="service-link">Hesapla <i class="fas fa-arrow-right"></i></span></div>
            </a>
            <a href="<?= SITE_URL ?>/klima-hesaplama" class="service-card" style="text-decoration:none;color:inherit">
                <div class="service-image" style="background:var(--c-blue-l)"><i class="fas fa-snowflake" style="background:var(--grad-blue);-webkit-background-clip:text;background-clip:text;color:transparent"></i></div>
                <div class="service-body"><h3>Klima BTU Hesaplama</h3><p>Oda büyüklüğüne göre uygun klima kapasitesini öğrenin.</p><span class="service-link">Hesapla <i class="fas fa-arrow-right"></i></span></div>
            </a>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <div>
            <h3>Ücretsiz keşif için bize ulaşın</h3>
            <p>Adresinize gelelim, ihtiyacınızı yerinde analiz edelim.</p>
        </div>
        <a href="<?= SITE_URL ?>/kesif" class="btn btn-lg"><i class="fas fa-clipboard-check"></i> Keşif Talep Et</a>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
