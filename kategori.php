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

set_meta([
    'baslik'    => e($kat['ad']) . ' — İzmir | ' . SITE_TITLE,
    'aciklama'  => e($kat['ad']) . ' hizmeti İzmir\'de Azra Doğalgaz güvencesiyle. İzmirgaz uyumlu, garantili, hızlı keşif.',
    'kelimeler' => mb_strtolower($kat['ad'], 'UTF-8') . ' izmir, izmir ' . mb_strtolower($kat['ad'], 'UTF-8'),
    'canonical' => SITE_URL . '/kategori/' . e($slug),
]);

$ekstra = schema_org([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Hizmetler','item'=>SITE_URL.'/hizmetler'],
        ['@type'=>'ListItem','position'=>3,'name'=>$kat['ad'],'item'=>SITE_URL.'/kategori/'.$slug],
    ],
]) . schema_org([
    '@context'=>'https://schema.org',
    '@type'=>'Service',
    'serviceType'=>$kat['ad'],
    'provider'=>['@type'=>'HVACBusiness','name'=>SITE_TITLE,'url'=>SITE_URL],
    'areaServed'=>['@type'=>'City','name'=>'İzmir'],
    'description'=>$kat['ad'].' hizmeti İzmir\'de Azra Doğalgaz tarafından sunulmaktadır.',
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
            <i class="fas fa-chevron-right"></i>
            <span><?= e($kat['ad']) ?></span>
        </nav>
        <h1><?= e($kat['ad']) ?></h1>
        <p>İzmir'de <?= e(mb_strtolower($kat['ad'], 'UTF-8')) ?> alanında profesyonel ve garantili hizmet.</p>
    </div>
</section>

<section class="sec">
    <div class="container">
        <?php if ($hizmetler): ?>
            <div class="cards-grid">
                <?php foreach ($hizmetler as $h): ?>
                    <article class="product-card">
                        <div class="thumb">
                            <?php if ($h['gorsel']): ?>
                                <img src="<?= UPLOAD_URL . '/' . e($h['gorsel']) ?>" alt="<?= e($h['baslik']) ?>" loading="lazy">
                            <?php else: ?>
                                <i class="fas fa-tools"></i>
                            <?php endif; ?>
                        </div>
                        <div class="body">
                            <span class="brand"><?= e($kat['ad']) ?></span>
                            <h4><?= e($h['baslik']) ?></h4>
                            <p class="desc"><?= e(mb_substr((string)$h['kisa_aciklama'], 0, 130)) ?></p>
                            <a href="<?= SITE_URL ?>/hizmet/<?= e($h['slug']) ?>" class="btn btn-primary">İncele <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <p>Bu kategoride henüz içerik bulunmamaktadır. En kısa sürede güncellenecektir.</p>
                <a href="<?= SITE_URL ?>/iletisim" class="btn btn-primary">Bize Ulaşın</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once INC_PATH . '/footer.php'; ?>
