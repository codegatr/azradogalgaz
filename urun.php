<?php
require_once __DIR__ . '/config.php';

$slug = clean($_GET['slug'] ?? '');
if (!$slug) redirect(SITE_URL . '/urunler');

$u = db_get(
    "SELECT u.*, m.ad marka_ad, m.slug marka_slug, k.ad kat_ad, k.slug kat_slug
     FROM urunler u
     LEFT JOIN markalar m ON m.id=u.marka_id
     LEFT JOIN urun_kategorileri k ON k.id=u.kategori_id
     WHERE u.slug=? AND u.aktif=1", [$slug]);
if (!$u) { http_response_code(404); require __DIR__ . '/404.php'; exit; }

$benzer = db_all(
    "SELECT id, ad, slug, kisa_aciklama, gorsel, fiyat, indirimli_fiyat
     FROM urunler WHERE kategori_id=? AND id<>? AND aktif=1 ORDER BY one_cikan DESC, id DESC LIMIT 4",
    [$u['kategori_id'], $u['id']]);

// Galeri JSON çözümle
$galeri = [];
if ($u['galeri']) {
    $tmp = json_decode((string)$u['galeri'], true);
    if (is_array($tmp)) $galeri = $tmp;
}

$fiyat = (float)($u['indirimli_fiyat'] ?: $u['fiyat']);

set_meta([
    'baslik'    => e($u['meta_baslik'] ?: ($u['ad'] . ' — ' . ($u['marka_ad'] ?? ''))) . ' | ' . SITE_TITLE,
    'aciklama'  => e($u['meta_aciklama'] ?: meta_aciklama((string)$u['kisa_aciklama'])),
    'canonical' => SITE_URL . '/urun/' . e($slug),
    'og_image'  => $u['gorsel'] ? UPLOAD_URL . '/' . e($u['gorsel']) : SITE_URL . '/assets/img/og-default.jpg',
]);

$ekstra = schema_org([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => array_filter([
        ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Ürünler','item'=>SITE_URL.'/urunler'],
        ['@type'=>'ListItem','position'=>3,'name'=>$u['ad'],'item'=>SITE_URL.'/urun/'.$slug],
    ]),
]) . schema_org(array_filter([
    '@context'=>'https://schema.org',
    '@type'=>'Product',
    'name'=>$u['ad'],
    'description'=>$u['kisa_aciklama'] ?: $u['meta_aciklama'],
    'image'=>$u['gorsel'] ? UPLOAD_URL . '/' . $u['gorsel'] : null,
    'sku'=>$u['sku'] ?: null,
    'brand'=>$u['marka_ad'] ? ['@type'=>'Brand','name'=>$u['marka_ad']] : null,
    'offers'=> $fiyat>0 ? [
        '@type'=>'Offer',
        'priceCurrency'=>'TRY',
        'price'=>$fiyat,
        'availability'=> ((int)$u['stok'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/PreOrder'),
        'url'=>SITE_URL.'/urun/'.$slug,
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
            <a href="<?= SITE_URL ?>/urunler">Ürünler</a>
            <i class="fas fa-chevron-right"></i>
            <span><?= e($u['ad']) ?></span>
        </nav>
    </div>
</section>

<section class="sec">
    <div class="container product-detail">
        <div class="product-gallery">
            <div class="main-img" id="mainImg">
                <?php if ($u['gorsel']): ?>
                    <img src="<?= UPLOAD_URL . '/' . e($u['gorsel']) ?>" alt="<?= e($u['ad']) ?>" loading="eager">
                <?php else: ?>
                    <i class="fas fa-fire-flame-curved"></i>
                <?php endif; ?>
            </div>
            <?php if ($galeri): ?>
                <div class="thumbs">
                    <?php if ($u['gorsel']): ?>
                        <button class="thumb-btn active" onclick="document.getElementById('mainImg').firstElementChild.src='<?= UPLOAD_URL . '/' . e($u['gorsel']) ?>'">
                            <img src="<?= UPLOAD_URL . '/' . e($u['gorsel']) ?>" alt="">
                        </button>
                    <?php endif; ?>
                    <?php foreach ($galeri as $g): ?>
                        <button class="thumb-btn" onclick="document.getElementById('mainImg').firstElementChild.src='<?= UPLOAD_URL . '/' . e($g) ?>'">
                            <img src="<?= UPLOAD_URL . '/' . e($g) ?>" alt="">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <?php if ($u['marka_ad']): ?>
                <span class="brand-tag"><?= e($u['marka_ad']) ?></span>
            <?php endif; ?>
            <h1><?= e($u['ad']) ?></h1>
            <?php if ($u['kisa_aciklama']): ?>
                <p class="lead"><?= e($u['kisa_aciklama']) ?></p>
            <?php endif; ?>

            <?php if ($fiyat > 0): ?>
                <div class="price-block">
                    <?php if ((float)$u['indirimli_fiyat'] > 0 && $u['indirimli_fiyat'] < $u['fiyat']): ?>
                        <span class="old"><?= number_format((float)$u['fiyat'], 0, ',', '.') ?> ₺</span>
                    <?php endif; ?>
                    <strong><?= number_format($fiyat, 0, ',', '.') ?> ₺</strong>
                    <span class="kdv">KDV Dahil</span>
                </div>
            <?php endif; ?>

            <div class="info-meta">
                <?php if ($u['sku']): ?><div><strong>Stok Kodu:</strong> <?= e($u['sku']) ?></div><?php endif; ?>
                <?php if ($u['kat_ad']): ?><div><strong>Kategori:</strong> <?= e($u['kat_ad']) ?></div><?php endif; ?>
                <?php if ((int)$u['stok'] > 0): ?>
                    <div><strong>Durum:</strong> <span class="in-stock">Stokta</span></div>
                <?php else: ?>
                    <div><strong>Durum:</strong> <span class="out-stock">Sipariş Üzerine</span></div>
                <?php endif; ?>
            </div>

            <div class="info-actions">
                <a href="tel:<?= preg_replace('/\s/','',ayar('firma_telefon_1',FIRMA_TEL_1)) ?>" class="btn btn-primary">
                    <i class="fas fa-phone-volume"></i> Hemen Ara
                </a>
                <?php $wp = ayar('whatsapp_numara'); if ($wp): ?>
                    <a href="https://wa.me/<?= e($wp) ?>?text=<?= urlencode($u['ad'].' ürünü hakkında bilgi almak istiyorum.') ?>" target="_blank" rel="noopener" class="btn btn-green">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                <?php endif; ?>
            </div>

            <ul class="info-trust">
                <li><i class="fas fa-shield-halved"></i> Yetkili bayi garantisi</li>
                <li><i class="fas fa-truck-fast"></i> Hızlı keşif & montaj</li>
                <li><i class="fas fa-headset"></i> 7/24 teknik destek</li>
            </ul>
        </div>
    </div>

    <div class="container">
        <div class="tabs" id="urunTabs">
            <div class="tab-heads">
                <button class="th active" data-tab="aciklama">Açıklama</button>
                <?php if ($u['ozellikler']): ?><button class="th" data-tab="ozellikler">Teknik Özellikler</button><?php endif; ?>
            </div>
            <div class="tab-bodies">
                <div class="tb active" data-tab="aciklama">
                    <?= $u['aciklama'] ?: '<p>'.e((string)$u['kisa_aciklama']).'</p>' ?>
                </div>
                <?php if ($u['ozellikler']): ?>
                    <div class="tb" data-tab="ozellikler">
                        <?= $u['ozellikler'] ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($benzer): ?>
        <div class="container">
            <h2 style="margin:40px 0 24px">Benzer Ürünler</h2>
            <div class="cards-grid">
                <?php foreach ($benzer as $b): ?>
                    <article class="product-card">
                        <div class="thumb">
                            <?php if ($b['gorsel']): ?>
                                <img src="<?= UPLOAD_URL . '/' . e($b['gorsel']) ?>" alt="<?= e($b['ad']) ?>" loading="lazy">
                            <?php else: ?>
                                <i class="fas fa-fire-flame-curved"></i>
                            <?php endif; ?>
                        </div>
                        <div class="body">
                            <h4><?= e($b['ad']) ?></h4>
                            <p class="desc"><?= e(mb_substr((string)$b['kisa_aciklama'], 0, 100)) ?></p>
                            <?php $bf = (float)($b['indirimli_fiyat'] ?: $b['fiyat']); if ($bf > 0): ?>
                                <div class="price"><?= number_format($bf, 0, ',', '.') ?> ₺</div>
                            <?php endif; ?>
                            <a href="<?= SITE_URL ?>/urun/<?= e($b['slug']) ?>" class="btn btn-primary">İncele <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<script>
// sekme + galeri
(function(){
    const tabs = document.getElementById('urunTabs');
    if (!tabs) return;
    tabs.querySelectorAll('.th').forEach(b => b.addEventListener('click', () => {
        const t = b.dataset.tab;
        tabs.querySelectorAll('.th').forEach(x => x.classList.toggle('active', x===b));
        tabs.querySelectorAll('.tb').forEach(x => x.classList.toggle('active', x.dataset.tab===t));
    }));
    document.querySelectorAll('.thumb-btn').forEach(btn => btn.addEventListener('click', e => {
        document.querySelectorAll('.thumb-btn').forEach(x => x.classList.remove('active'));
        btn.classList.add('active');
    }));
})();
</script>

<?php require_once INC_PATH . '/footer.php'; ?>
