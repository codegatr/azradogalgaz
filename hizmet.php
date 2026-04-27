<?php
require_once __DIR__ . '/config.php';

$slug = clean($_GET['slug'] ?? '');
if (!$slug) redirect(SITE_URL . '/hizmetler');

$h = db_get("SELECT h.*, k.ad kat_ad, k.slug kat_slug
    FROM hizmetler h LEFT JOIN hizmet_kategorileri k ON k.id=h.kategori_id
    WHERE h.slug=? AND h.aktif=1", [$slug]);
if (!$h) { http_response_code(404); require __DIR__ . '/404.php'; exit; }

$benzer = db_all("SELECT id, baslik, slug, kisa_aciklama, gorsel
    FROM hizmetler WHERE kategori_id=? AND id<>? AND aktif=1 ORDER BY id DESC LIMIT 4",
    [$h['kategori_id'], $h['id']]);

set_meta([
    'baslik'    => e($h['meta_baslik'] ?: $h['baslik']) . ' | ' . SITE_TITLE,
    'aciklama'  => e($h['meta_aciklama'] ?: meta_aciklama((string)$h['kisa_aciklama'])),
    'canonical' => SITE_URL . '/hizmet/' . e($slug),
    'og_image'  => $h['gorsel'] ? UPLOAD_URL . '/' . e($h['gorsel']) : SITE_URL . '/assets/img/og-default.jpg',
]);

$ekstra = schema_org([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => array_filter([
        ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Hizmetler','item'=>SITE_URL.'/hizmetler'],
        $h['kat_slug'] ? ['@type'=>'ListItem','position'=>3,'name'=>$h['kat_ad'],'item'=>SITE_URL.'/kategori/'.$h['kat_slug']] : null,
        ['@type'=>'ListItem','position'=>$h['kat_slug']?4:3,'name'=>$h['baslik'],'item'=>SITE_URL.'/hizmet/'.$slug],
    ]),
]) . schema_org([
    '@context'=>'https://schema.org',
    '@type'=>'Service',
    'name'=>$h['baslik'],
    'description'=>$h['kisa_aciklama'],
    'provider'=>['@type'=>'HVACBusiness','name'=>SITE_TITLE,'url'=>SITE_URL,'telephone'=>FIRMA_TEL_1],
    'areaServed'=>['@type'=>'City','name'=>'İzmir'],
]);
set_meta(['extra_schema' => $ekstra]);

require_once INC_PATH . '/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= SITE_URL ?>/hizmetler">Hizmetler</a>
            <?php if ($h['kat_slug']): ?>
                <i class="fas fa-chevron-right"></i>
                <a href="<?= SITE_URL ?>/kategori/<?= e($h['kat_slug']) ?>"><?= e($h['kat_ad']) ?></a>
            <?php endif; ?>
            <i class="fas fa-chevron-right"></i>
            <span><?= e($h['baslik']) ?></span>
        </nav>
        <h1><?= e($h['baslik']) ?></h1>
        <?php if ($h['kisa_aciklama']): ?><p><?= e($h['kisa_aciklama']) ?></p><?php endif; ?>
    </div>
</section>

<section class="sec">
    <div class="container detail-grid">
        <article class="detail-main">
            <?php if ($h['gorsel']): ?>
                <div class="detail-image">
                    <img src="<?= UPLOAD_URL . '/' . e($h['gorsel']) ?>" alt="<?= e($h['baslik']) ?>" loading="lazy">
                </div>
            <?php endif; ?>
            <div class="content">
                <?= $h['icerik'] ?: '<p>İçerik yakında eklenecektir.</p>' ?>
            </div>
        </article>

        <aside class="detail-side">
            <div class="side-card cta-side">
                <h4>Hemen Bilgi Alın</h4>
                <p>Bu hizmet için ücretsiz keşif ve fiyat teklifi alın.</p>
                <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_1',FIRMA_TEL_1)) ?>" class="btn btn-primary" style="width:100%;justify-content:center">
                    <i class="fas fa-phone-volume"></i> <?= e(ayar('firma_telefon_1', FIRMA_TEL_1)) ?>
                </a>
                <?php $wp = ayar('whatsapp_numara'); if ($wp): ?>
                    <a href="https://wa.me/<?= e($wp) ?>?text=<?= urlencode($h['baslik'].' hizmeti hakkında bilgi almak istiyorum.') ?>" class="btn btn-green" style="width:100%;justify-content:center;margin-top:10px" target="_blank" rel="noopener">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($benzer): ?>
            <div class="side-card">
                <h4>Benzer Hizmetler</h4>
                <ul class="side-list">
                    <?php foreach ($benzer as $b): ?>
                        <li>
                            <a href="<?= SITE_URL ?>/hizmet/<?= e($b['slug']) ?>">
                                <i class="fas fa-circle-chevron-right"></i> <?= e($b['baslik']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</section>

<?php require_once INC_PATH . '/footer.php'; ?>
