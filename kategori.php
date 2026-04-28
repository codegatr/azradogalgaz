<?php
require_once __DIR__ . '/config.php';

$slug = clean($_GET['slug'] ?? '');
if (!$slug) redirect(SITE_URL . '/hizmetler');

$kat = db_get("SELECT * FROM hizmet_kategorileri WHERE slug=? AND aktif=1", [$slug]);
if (!$kat) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$hizmetler = db_all("SELECT * FROM hizmetler WHERE kategori_id=? AND aktif=1 ORDER BY sira ASC, id DESC", [$kat['id']]);

$sayfa_baslik   = e($kat['ad']) . ' — İzmir | Azra Doğalgaz';
$sayfa_aciklama = !empty($kat['seo_aciklama']) ? e($kat['seo_aciklama']) : (e($kat['ad']) . ' hizmeti İzmir\'de Azra Doğalgaz güvencesiyle. İzmirgaz uyumlu, garantili, hızlı keşif.');
$kanonik_url    = SITE_URL . '/kategori/' . e($slug);

$schema_jsonld = [
    [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
            ['@type'=>'ListItem','position'=>2,'name'=>'Hizmetler','item'=>SITE_URL.'/hizmetler'],
            ['@type'=>'ListItem','position'=>3,'name'=>$kat['ad'],'item'=>SITE_URL.'/kategori/'.$slug],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type'    => 'Service',
        'serviceType' => $kat['ad'],
        'provider' => ['@type'=>'HVACBusiness','name'=>'Azra Doğalgaz','url'=>SITE_URL],
        'areaServed' => ['@type'=>'City','name'=>'İzmir'],
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
$ikon = $ikon_map[$kat['slug']] ?? 'fa-tools';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <a href="<?= SITE_URL ?>/hizmetler">Hizmetler</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span><?= e($kat['ad']) ?></span>
        </div>
        <div style="display:inline-flex;align-items:center;justify-content:center;width:80px;height:80px;border-radius:50%;background:#fff;color:var(--c-primary);font-size:2rem;margin-bottom:16px;box-shadow:var(--sh)"><i class="fas <?= e($ikon) ?>"></i></div>
        <h1><?= e($kat['ad']) ?></h1>
        <?php if (!empty($kat['aciklama'])): ?>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)"><?= e($kat['aciklama']) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="s">
    <div class="container">
        <?php if ($hizmetler): ?>
        <div class="services">
            <?php foreach ($hizmetler as $h): ?>
            <a href="<?= SITE_URL ?>/hizmet/<?= e($h['slug']) ?>" class="service-card" style="text-decoration:none;color:inherit">
                <div class="service-image">
                    <?php if (!empty($h['gorsel'])): ?>
                        <img src="<?= e(gorsel_url($h['gorsel'])) ?>" alt="<?= e($h['baslik']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover">
                    <?php else: ?>
                        <i class="fas <?= e($ikon) ?>"></i>
                    <?php endif; ?>
                </div>
                <div class="service-body">
                    <h3><?= e($h['baslik']) ?></h3>
                    <?php if (!empty($h['kisa_aciklama'])): ?>
                    <p><?= e(mb_strimwidth($h['kisa_aciklama'], 0, 140, '…', 'UTF-8')) ?></p>
                    <?php endif; ?>
                    <span class="service-link">Detayları Gör <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info" style="max-width:680px;margin:0 auto">
            <i class="fas fa-circle-info"></i>
            <div>
                <strong>Bu kategoride henüz detay sayfası eklenmedi.</strong><br>
                Hizmet hakkında bilgi almak için bize ulaşabilirsiniz.
                <div style="margin-top:14px">
                    <a href="<?= SITE_URL ?>/iletisim" class="btn btn-out btn-sm"><i class="fas fa-envelope"></i> İletişim</a>
                    <a href="<?= SITE_URL ?>/kesif" class="btn btn-primary btn-sm" style="margin-left:6px"><i class="fas fa-clipboard-check"></i> Keşif Talep Et</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <div>
            <h3><?= e($kat['ad']) ?> için ücretsiz keşif</h3>
            <p>Profesyonel ekibimiz adresinize gelir, ihtiyacınızı analiz eder, en uygun çözümü sunar.</p>
        </div>
        <a href="<?= SITE_URL ?>/kesif" class="btn btn-lg"><i class="fas fa-clipboard-check"></i> Keşif Talep Et</a>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
