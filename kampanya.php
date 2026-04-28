<?php
require_once __DIR__ . '/config.php';

$slug = clean($_GET['slug'] ?? '');
if (!$slug) redirect(SITE_URL . '/kampanyalar');

$k = db_get("SELECT * FROM kampanyalar WHERE slug=? AND aktif=1", [$slug]);
if (!$k) { http_response_code(404); require __DIR__ . '/404.php'; exit; }

$diger = db_all("SELECT id,baslik,slug,kisa_aciklama,gorsel,nakit_fiyat,kart_fiyat,taksit_sayisi
    FROM kampanyalar WHERE id<>? AND aktif=1 ORDER BY id DESC LIMIT 3", [$k['id']]);

$nakit  = (float)$k['nakit_fiyat'];
$kart   = (float)$k['kart_fiyat'];
$taksit = (int)($k['taksit_sayisi'] ?? 0);
$indirim_orani = ($kart > $nakit && $nakit > 0) ? round((($kart - $nakit) / $kart) * 100) : 0;

$sayfa_baslik   = e($k['meta_baslik'] ?: $k['baslik']) . ' | Azra Doğalgaz';
$sayfa_aciklama = e($k['meta_aciklama'] ?: meta_aciklama((string)$k['kisa_aciklama']));
$kanonik_url    = SITE_URL . '/kampanya/' . e($slug);
$og_resim       = $k['gorsel'] ? e(gorsel_url($k['gorsel'])) : SITE_URL . '/assets/img/og-default.jpg';

$schema_jsonld = [
    [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
            ['@type'=>'ListItem','position'=>2,'name'=>'Kampanyalar','item'=>SITE_URL.'/kampanyalar'],
            ['@type'=>'ListItem','position'=>3,'name'=>$k['baslik'],'item'=>SITE_URL.'/kampanya/'.$slug],
        ],
    ],
    array_filter([
        '@context' => 'https://schema.org',
        '@type'    => 'Product',
        'name'     => $k['baslik'],
        'description' => $k['kisa_aciklama'],
        'image'    => $k['gorsel'] ? gorsel_url($k['gorsel']) : null,
        'brand'    => ['@type'=>'Brand','name'=>'Demirdöküm'],
        'offers'   => $nakit > 0 ? [
            '@type'=>'Offer',
            'priceCurrency'=>'TRY',
            'price'=>$nakit,
            'availability'=>'https://schema.org/InStock',
            'url'=>SITE_URL.'/kampanya/'.$slug,
            'priceValidUntil'=>$k['bitis'] ?? null,
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
            <a href="<?= SITE_URL ?>/kampanyalar">Kampanyalar</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span><?= e(mb_strimwidth($k['baslik'],0,40,'…','UTF-8')) ?></span>
        </div>
        <h1><?= e($k['baslik']) ?></h1>
    </div>
</section>

<section class="s">
    <div class="container">
        <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:36px;align-items:start" class="kampanya-grid">

            <!-- Sol: görsel + içerik -->
            <div>
                <div style="border-radius:var(--r-lg);overflow:hidden;margin-bottom:24px;aspect-ratio:16/9;background:var(--c-bg-alt);display:flex;align-items:center;justify-content:center">
                    <?php if (!empty($k['gorsel'])): ?>
                        <img src="<?= e(gorsel_url($k['gorsel'])) ?>" alt="<?= e($k['baslik']) ?>" style="width:100%;height:100%;object-fit:cover">
                    <?php else: ?>
                        <i class="fas fa-fire-flame-curved" style="font-size:5rem;color:var(--c-primary);opacity:.4"></i>
                    <?php endif; ?>
                </div>

                <?php if (!empty($k['kisa_aciklama'])): ?>
                <div class="alert alert-info" style="margin-bottom:24px"><i class="fas fa-circle-info"></i><div><?= nl2br(e($k['kisa_aciklama'])) ?></div></div>
                <?php endif; ?>

                <?php if (!empty($k['aciklama'])): ?>
                <div class="prose" style="margin:0">
                    <?= $k['aciklama'] ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sağ: fiyat & CTA -->
            <aside style="position:sticky;top:100px">
                <?php if ($nakit > 0): ?>
                <div class="hero-card">
                    <span class="hero-card-tag">🔥 Kampanya Fiyatı</span>
                    <h3 style="font-size:1.2rem"><?= e($k['baslik']) ?></h3>

                    <div class="price-block">
                        <div class="price-card">
                            <div class="label">Peşin / Nakit</div>
                            <div class="num"><?= tl($nakit) ?></div>
                            <div class="small">Tek ödeme</div>
                        </div>
                        <?php if ($kart > 0): ?>
                        <div class="price-card alt">
                            <div class="label">Kredi Kartı</div>
                            <div class="num"><?= tl($kart) ?></div>
                            <div class="small"><?= $taksit ? $taksit . ' Taksit' : 'Tek Çekim' ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($indirim_orani > 0): ?>
                    <div style="text-align:center;background:var(--c-green-l);color:#15803d;padding:10px;border-radius:8px;margin-bottom:16px;font-weight:700;font-size:.92rem">💰 Peşin alımda %<?= $indirim_orani ?> avantaj</div>
                    <?php endif; ?>

                    <a href="<?= SITE_URL ?>/kesif" class="btn btn-primary btn-block btn-lg" style="margin-bottom:8px"><i class="fas fa-clipboard-check"></i> Şimdi Talep Et</a>
                    <a href="https://wa.me/<?= e(ayar('whatsapp_numara', defined('FIRMA_WHATSAPP')?FIRMA_WHATSAPP:'')) ?>?text=<?= urlencode($k['baslik'] . ' kampanyası hakkında bilgi almak istiyorum.') ?>" target="_blank" class="btn btn-green btn-block"><i class="fab fa-whatsapp"></i> WhatsApp ile Sor</a>

                    <?php if (!empty($k['bitis'])): ?>
                    <p style="text-align:center;color:var(--c-muted);font-size:.82rem;margin-top:14px"><i class="fas fa-clock"></i> Kampanya bitişi: <?= date('d.m.Y', strtotime((string)$k['bitis'])) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </aside>
        </div>

        <?php if ($diger): ?>
        <div style="margin-top:60px">
            <h2 style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;margin-bottom:24px">Diğer Kampanyalar</h2>
            <div class="services">
                <?php foreach ($diger as $d): ?>
                <a href="<?= SITE_URL ?>/kampanya/<?= e($d['slug']) ?>" class="service-card" style="text-decoration:none;color:inherit">
                    <div class="service-image">
                        <?php if (!empty($d['gorsel'])): ?>
                            <img src="<?= e(gorsel_url($d['gorsel'])) ?>" alt="<?= e($d['baslik']) ?>" style="width:100%;height:100%;object-fit:cover">
                        <?php else: ?>
                            <i class="fas fa-fire-flame-curved"></i>
                        <?php endif; ?>
                    </div>
                    <div class="service-body">
                        <h3 style="font-size:1rem"><?= e($d['baslik']) ?></h3>
                        <?php if ($d['nakit_fiyat'] > 0): ?>
                        <p style="font-family:var(--font-display);font-weight:800;color:var(--c-primary-d);margin-bottom:8px"><?= tl((float)$d['nakit_fiyat']) ?></p>
                        <?php endif; ?>
                        <span class="service-link">İncele <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
@media (max-width:880px) { .kampanya-grid { grid-template-columns: 1fr !important; } .kampanya-grid aside { position:static !important; } }
</style>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
