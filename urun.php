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

$galeri = [];
if ($u['galeri']) {
    $tmp = json_decode((string)$u['galeri'], true);
    if (is_array($tmp)) $galeri = $tmp;
}

$fiyat = (float)($u['indirimli_fiyat'] ?: $u['fiyat']);
$eski  = (float)$u['fiyat'];
$indirim = ($u['indirimli_fiyat'] && $eski > $fiyat);

$sayfa_baslik   = e($u['meta_baslik'] ?: ($u['ad'] . ($u['marka_ad'] ? ' — ' . $u['marka_ad'] : ''))) . ' | Azra Doğalgaz';
$sayfa_aciklama = e($u['meta_aciklama'] ?: meta_aciklama((string)$u['kisa_aciklama']));
$kanonik_url    = SITE_URL . '/urun/' . e($slug);
$og_resim       = $u['gorsel'] ? e(gorsel_url($u['gorsel'])) : SITE_URL . '/assets/img/og-default.jpg';

$schema_jsonld = [
    [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
            ['@type'=>'ListItem','position'=>2,'name'=>'Ürünler','item'=>SITE_URL.'/urunler'],
            ['@type'=>'ListItem','position'=>3,'name'=>$u['ad'],'item'=>SITE_URL.'/urun/'.$slug],
        ],
    ],
    array_filter([
        '@context' => 'https://schema.org',
        '@type'    => 'Product',
        'name'     => $u['ad'],
        'description' => $u['kisa_aciklama'] ?: $u['meta_aciklama'],
        'image'    => $u['gorsel'] ? gorsel_url($u['gorsel']) : null,
        'sku'      => $u['sku'] ?: null,
        'brand'    => $u['marka_ad'] ? ['@type'=>'Brand','name'=>$u['marka_ad']] : null,
        'offers'   => $fiyat > 0 ? [
            '@type'=>'Offer',
            'priceCurrency'=>'TRY',
            'price'=>$fiyat,
            'availability'=>'https://schema.org/InStock',
            'url'=>SITE_URL.'/urun/'.$slug,
            'seller'=>['@type'=>'Organization','name'=>'Azra Doğalgaz'],
        ] : null,
    ]),
];

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <a href="<?= SITE_URL ?>/urunler">Ürünler</a>
            <?php if ($u['kat_slug']): ?>
                <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
                <span><?= e($u['kat_ad']) ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="s">
    <div class="container">
        <div style="display:grid;grid-template-columns:1.1fr 1fr;gap:40px;align-items:start" class="urun-grid">

            <!-- Görseller -->
            <div>
                <div style="background:#fff;border:1px solid var(--c-line);border-radius:var(--r-lg);padding:30px;display:flex;align-items:center;justify-content:center;aspect-ratio:1/1;overflow:hidden">
                    <?php if ($u['gorsel']): ?>
                        <img src="<?= e(gorsel_url($u['gorsel'])) ?>" alt="<?= e($u['ad']) ?>" style="max-width:90%;max-height:90%;object-fit:contain" id="ana-gorsel">
                    <?php else: ?>
                        <i class="fas fa-fire-flame-curved" style="font-size:6rem;color:var(--c-primary);opacity:.3"></i>
                    <?php endif; ?>
                </div>

                <?php if ($galeri): ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:8px;margin-top:12px">
                    <?php foreach ($galeri as $g): ?>
                    <button type="button" onclick="document.getElementById('ana-gorsel').src='<?= e(gorsel_url($g)) ?>'" style="aspect-ratio:1;background:#fff;border:1px solid var(--c-line);border-radius:8px;padding:6px;cursor:pointer;overflow:hidden">
                        <img src="<?= e(gorsel_url($g)) ?>" style="width:100%;height:100%;object-fit:contain">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Detay -->
            <div>
                <?php if (!empty($u['marka_ad'])): ?>
                <div style="margin-bottom:8px"><span class="tag tag-primary"><?= e($u['marka_ad']) ?></span></div>
                <?php endif; ?>

                <h1 style="font-family:var(--font-display);font-size:1.8rem;font-weight:800;margin-bottom:14px"><?= e($u['ad']) ?></h1>

                <?php if (!empty($u['kisa_aciklama'])): ?>
                <p style="color:var(--c-muted);font-size:1rem;margin-bottom:20px;line-height:1.7"><?= e($u['kisa_aciklama']) ?></p>
                <?php endif; ?>

                <?php if ($fiyat > 0): ?>
                <div style="background:var(--c-primary-l);border:1px solid #fed7aa;border-radius:var(--r);padding:22px;margin-bottom:24px">
                    <div style="font-size:.78rem;color:var(--c-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Fiyat</div>
                    <?php if ($indirim): ?>
                    <div style="text-decoration:line-through;color:var(--c-muted);font-size:1rem"><?= tl($eski) ?></div>
                    <?php endif; ?>
                    <div style="font-family:var(--font-display);font-size:2rem;font-weight:900;color:var(--c-primary-d);line-height:1.1"><?= tl($fiyat) ?></div>
                    <?php if ($indirim): ?>
                    <div style="margin-top:6px"><span class="tag tag-green">İndirimli Fiyat</span></div>
                    <?php endif; ?>
                    <p style="color:var(--c-muted);font-size:.82rem;margin-top:8px">* Fiyatlara KDV dahildir, montaj hariç. Net fiyat keşif sonrası belirlenir.</p>
                </div>
                <?php endif; ?>

                <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px">
                    <a href="<?= SITE_URL ?>/kesif" class="btn btn-primary btn-lg"><i class="fas fa-clipboard-check"></i> Keşif & Teklif İste</a>
                    <a href="https://wa.me/<?= e(ayar('whatsapp_numara', defined('FIRMA_WHATSAPP')?FIRMA_WHATSAPP:'')) ?>?text=<?= urlencode($u['ad'] . ' hakkında bilgi almak istiyorum.') ?>" target="_blank" class="btn btn-green btn-lg"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                </div>

                <?php if (!empty($u['ozellikler'])):
                    $ozellikler = json_decode((string)$u['ozellikler'], true);
                ?>
                <div class="card" style="background:var(--c-bg-alt)">
                    <h4 style="font-family:var(--font-display);font-size:1.05rem;margin-bottom:14px"><i class="fas fa-list-check" style="color:var(--c-primary);margin-right:8px"></i>Teknik Özellikler</h4>
                    <table style="width:100%;font-size:.92rem">
                        <?php foreach ((array)$ozellikler as $key => $val): ?>
                        <tr style="border-bottom:1px solid var(--c-line)"><td style="padding:8px 0;color:var(--c-muted)"><?= e($key) ?></td><td style="padding:8px 0;text-align:right;font-weight:600"><?= e($val) ?></td></tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>

        </div>

        <?php if (!empty($u['aciklama'])): ?>
        <div class="prose" style="margin-top:50px">
            <h2>Ürün Açıklaması</h2>
            <?= $u['aciklama'] ?>
        </div>
        <?php endif; ?>

        <?php if ($benzer): ?>
        <div style="margin-top:60px">
            <h2 style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;margin-bottom:24px">Benzer Ürünler</h2>
            <div class="products">
                <?php foreach ($benzer as $b):
                    $bf = (float)($b['indirimli_fiyat'] ?: $b['fiyat']);
                ?>
                <a href="<?= SITE_URL ?>/urun/<?= e($b['slug']) ?>" class="product-card" style="text-decoration:none;color:inherit">
                    <div class="product-image">
                        <?php if ($b['gorsel']): ?>
                            <img src="<?= e(gorsel_url($b['gorsel'])) ?>" alt="<?= e($b['ad']) ?>" loading="lazy">
                        <?php else: ?>
                            <i class="fas fa-fire-flame-curved" style="font-size:3rem;color:var(--c-primary);opacity:.4"></i>
                        <?php endif; ?>
                    </div>
                    <div class="product-body">
                        <h4><?= e($b['ad']) ?></h4>
                        <?php if ($bf > 0): ?><div class="product-price"><?= tl($bf) ?></div><?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
@media (max-width: 880px) { .urun-grid { grid-template-columns: 1fr !important; } }
</style>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
