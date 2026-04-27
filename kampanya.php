<?php
require_once __DIR__ . '/config.php';

$slug = clean($_GET['slug'] ?? '');
if (!$slug) redirect(SITE_URL . '/kampanyalar');

$k = db_get("SELECT * FROM kampanyalar WHERE slug=? AND aktif=1", [$slug]);
if (!$k) { http_response_code(404); require __DIR__ . '/404.php'; exit; }

$diger = db_all("SELECT id,baslik,slug,kisa_aciklama,gorsel,nakit_fiyat,kart_fiyat,taksit_sayisi
    FROM kampanyalar WHERE id<>? AND aktif=1 ORDER BY id DESC LIMIT 3", [$k['id']]);

set_meta([
    'baslik'    => e($k['meta_baslik'] ?: $k['baslik']) . ' | ' . SITE_TITLE,
    'aciklama'  => e($k['meta_aciklama'] ?: meta_aciklama((string)$k['kisa_aciklama'])),
    'canonical' => SITE_URL . '/kampanya/' . e($slug),
    'og_image'  => $k['gorsel'] ? UPLOAD_URL . '/' . e($k['gorsel']) : SITE_URL . '/assets/img/og-default.jpg',
]);

$nakit = (float)$k['nakit_fiyat'];
$kart  = (float)$k['kart_fiyat'];
$ekstra = schema_org([
    '@context'=>'https://schema.org',
    '@type'=>'BreadcrumbList',
    'itemListElement'=>[
        ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Kampanyalar','item'=>SITE_URL.'/kampanyalar'],
        ['@type'=>'ListItem','position'=>3,'name'=>$k['baslik'],'item'=>SITE_URL.'/kampanya/'.$slug],
    ],
]) . schema_org(array_filter([
    '@context'=>'https://schema.org',
    '@type'=>'Product',
    'name'=>$k['baslik'],
    'description'=>$k['kisa_aciklama'],
    'image'=>$k['gorsel'] ? UPLOAD_URL . '/' . $k['gorsel'] : null,
    'brand'=>['@type'=>'Brand','name'=>'Demirdöküm'],
    'offers'=> $nakit>0 ? [
        '@type'=>'Offer',
        'priceCurrency'=>'TRY',
        'price'=>$nakit,
        'availability'=>'https://schema.org/InStock',
        'url'=>SITE_URL.'/kampanya/'.$slug,
        'priceValidUntil'=>$k['bitis'] ?: null,
        'seller'=>['@type'=>'Organization','name'=>SITE_TITLE],
    ] : null,
]));
set_meta(['extra_schema' => $ekstra]);

require_once INC_PATH . '/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= SITE_URL ?>/kampanyalar">Kampanyalar</a>
            <i class="fas fa-chevron-right"></i>
            <span><?= e($k['baslik']) ?></span>
        </nav>
        <h1><?= e($k['baslik']) ?></h1>
        <?php if ($k['kisa_aciklama']): ?><p><?= e($k['kisa_aciklama']) ?></p><?php endif; ?>
    </div>
</section>

<section class="sec">
    <div class="container detail-grid">
        <article class="detail-main">
            <?php if ($k['gorsel']): ?>
                <div class="detail-image">
                    <img src="<?= UPLOAD_URL . '/' . e($k['gorsel']) ?>" alt="<?= e($k['baslik']) ?>" loading="eager">
                </div>
            <?php endif; ?>
            <div class="content">
                <?= $k['icerik'] ?: '<p>Kampanya içeriği yakında eklenecektir.</p>' ?>
            </div>
        </article>

        <aside class="detail-side">
            <div class="side-card cta-side campaign-side">
                <h4>💰 Kampanya Fiyatları</h4>
                <div class="price-row">
                    <?php if ($nakit > 0): ?>
                        <div class="price-box cash">
                            <small>Nakit Fiyat</small>
                            <strong><?= number_format($nakit, 0, ',', '.') ?> ₺</strong>
                        </div>
                    <?php endif; ?>
                    <?php if ($kart > 0): ?>
                        <div class="price-box card">
                            <small>Kredi Kartı</small>
                            <strong><?= number_format($kart, 0, ',', '.') ?> ₺</strong>
                            <?php if ((int)$k['taksit_sayisi'] > 0): ?>
                                <span><?= (int)$k['taksit_sayisi'] ?> Taksit</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_1',FIRMA_TEL_1)) ?>" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:16px">
                    <i class="fas fa-phone-volume"></i> Hemen Ara
                </a>
                <?php $wp = ayar('whatsapp_numara'); if ($wp): ?>
                    <a href="https://wa.me/<?= e($wp) ?>?text=<?= urlencode($k['baslik'].' kampanyası hakkında bilgi almak istiyorum.') ?>" target="_blank" rel="noopener" class="btn btn-green" style="width:100%;justify-content:center;margin-top:10px">
                        <i class="fab fa-whatsapp"></i> WhatsApp ile Sor
                    </a>
                <?php endif; ?>
                <?php if ($k['bitis']): ?>
                    <p class="kampanya-end"><i class="fas fa-clock"></i> Son tarih: <?= tarih_tr($k['bitis']) ?></p>
                <?php endif; ?>
            </div>

            <?php if ($diger): ?>
                <div class="side-card">
                    <h4>Diğer Kampanyalar</h4>
                    <ul class="side-list">
                        <?php foreach ($diger as $d): ?>
                            <li>
                                <a href="<?= SITE_URL ?>/kampanya/<?= e($d['slug']) ?>">
                                    <i class="fas fa-tags"></i> <?= e($d['baslik']) ?>
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
