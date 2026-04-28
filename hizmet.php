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

$sayfa_baslik   = e($h['meta_baslik'] ?: $h['baslik']) . ' — Azra Doğalgaz';
$sayfa_aciklama = e($h['meta_aciklama'] ?: meta_aciklama((string)$h['kisa_aciklama']));
$kanonik_url    = SITE_URL . '/hizmet/' . e($slug);
$og_resim       = $h['gorsel'] ? e(gorsel_url($h['gorsel'])) : SITE_URL . '/assets/img/og-default.jpg';

$schema_jsonld = [
    [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => array_filter([
            ['@type'=>'ListItem','position'=>1,'name'=>'Ana Sayfa','item'=>SITE_URL.'/'],
            ['@type'=>'ListItem','position'=>2,'name'=>'Hizmetler','item'=>SITE_URL.'/hizmetler'],
            $h['kat_slug'] ? ['@type'=>'ListItem','position'=>3,'name'=>$h['kat_ad'],'item'=>SITE_URL.'/kategori/'.$h['kat_slug']] : null,
            ['@type'=>'ListItem','position'=>$h['kat_slug']?4:3,'name'=>$h['baslik'],'item'=>SITE_URL.'/hizmet/'.$slug],
        ]),
    ],
    [
        '@context' => 'https://schema.org',
        '@type'    => 'Service',
        'name'     => $h['baslik'],
        'description' => $h['kisa_aciklama'],
        'provider' => ['@type'=>'HVACBusiness','name'=>'Azra Doğalgaz','url'=>SITE_URL,'telephone'=>defined('FIRMA_TEL_1')?FIRMA_TEL_1:''],
        'areaServed' => ['@type'=>'City','name'=>'İzmir'],
    ],
];

require_once __DIR__ . '/inc/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Ana Sayfa</a>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <a href="<?= SITE_URL ?>/hizmetler">Hizmetler</a>
            <?php if ($h['kat_slug']): ?>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <a href="<?= SITE_URL ?>/kategori/<?= e($h['kat_slug']) ?>"><?= e($h['kat_ad']) ?></a>
            <?php endif; ?>
            <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
            <span><?= e($h['baslik']) ?></span>
        </div>
        <h1><?= e($h['baslik']) ?></h1>
        <?php if (!empty($h['kisa_aciklama'])): ?>
        <p style="max-width:680px;margin:0 auto;color:var(--c-muted)"><?= e($h['kisa_aciklama']) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="s">
    <div class="container">
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:36px;align-items:start" class="hizmet-grid">

            <article>
                <?php if (!empty($h['gorsel'])): ?>
                <div style="border-radius:var(--r-lg);overflow:hidden;margin-bottom:30px;aspect-ratio:16/9">
                    <img src="<?= e(gorsel_url($h['gorsel'])) ?>" alt="<?= e($h['baslik']) ?>" style="width:100%;height:100%;object-fit:cover">
                </div>
                <?php endif; ?>

                <div class="prose" style="margin:0">
                    <?php if (!empty($h['icerik'])): ?>
                        <?= $h['icerik'] ?>
                    <?php else: ?>
                        <p><?= nl2br(e($h['kisa_aciklama'] ?: 'Bu hizmet için detaylı içerik yakında eklenecek. Daha fazla bilgi için bize ulaşabilirsiniz.')) ?></p>
                    <?php endif; ?>
                </div>
            </article>

            <aside>
                <div class="card" style="background:var(--c-primary-l);border-color:#fed7aa;margin-bottom:20px">
                    <h4 style="font-family:var(--font-display);font-size:1.1rem;margin-bottom:12px"><i class="fas fa-clipboard-check" style="color:var(--c-primary);margin-right:8px"></i>Ücretsiz Keşif</h4>
                    <p style="color:var(--c-text-2);font-size:.92rem;line-height:1.6;margin-bottom:14px">Adresinize ücretsiz keşfe gelelim. Yerinde inceleme, net teklif.</p>
                    <a href="<?= SITE_URL ?>/kesif" class="btn btn-primary btn-block"><i class="fas fa-clipboard-check"></i> Keşif Talep Et</a>
                </div>

                <div class="card" style="margin-bottom:20px">
                    <h4 style="font-family:var(--font-display);font-size:1.1rem;margin-bottom:12px"><i class="fas fa-phone" style="color:var(--c-green);margin-right:8px"></i>Hemen Arayın</h4>
                    <p style="font-family:var(--font-display);font-weight:800;color:var(--c-text);font-size:1.2rem;margin-bottom:6px"><?= e(ayar('firma_telefon_1', defined('FIRMA_TEL_1')?FIRMA_TEL_1:'')) ?></p>
                    <p style="color:var(--c-muted);font-size:.85rem;margin-bottom:14px"><?= e(ayar('firma_calisma_saatleri','Pzt-Cmt 08:00-20:00')) ?></p>
                    <a href="https://wa.me/<?= e(ayar('whatsapp_numara', defined('FIRMA_WHATSAPP')?FIRMA_WHATSAPP:'')) ?>" target="_blank" class="btn btn-green btn-block"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                </div>

                <?php if ($benzer): ?>
                <div class="card">
                    <h4 style="font-family:var(--font-display);font-size:1.1rem;margin-bottom:14px">Benzer Hizmetler</h4>
                    <ul style="list-style:none;padding:0;margin:0">
                        <?php foreach ($benzer as $b): ?>
                        <li style="padding:8px 0;border-bottom:1px solid var(--c-line)">
                            <a href="<?= SITE_URL ?>/hizmet/<?= e($b['slug']) ?>" style="color:var(--c-text);font-weight:600;font-size:.92rem;display:flex;justify-content:space-between;align-items:center">
                                <span><?= e($b['baslik']) ?></span>
                                <i class="fas fa-arrow-right" style="color:var(--c-primary);font-size:.75rem"></i>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>

<style>
@media (max-width:880px) { .hizmet-grid { grid-template-columns: 1fr !important; } }
</style>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
